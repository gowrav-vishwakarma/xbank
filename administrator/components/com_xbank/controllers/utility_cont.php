<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class utility_cont extends CI_Controller{

        function index(){
             xDeveloperToolBars::getUtilityManagementToolBar();
             $this->load->view("utility.html");
             $this->jq->getHeader();
        }

        function backup() {
        set_time_limit(5000);
        // Load the DB utility class
        $this->load->dbutil();

// Backup your entire database and assign it to a variable
        $backup = & $this->dbutil->backup();

// Load the file helper and write the file to your server
//        $this->load->helper('file');
//        write_file('./backups/mybackup.gz', $backup);

// Load the download helper and send the file to your desktop
        $this->load->helper('download');
        force_download('xbanksoft_'.  getNow("Y-m-d").'.gz', $backup);

//        delete_files('./backups/');
         re("com_xbank.index", " Database Backup taken Successfully ");
    }

     function memberRegistrationDateChangeForm() {
        xDeveloperToolBars::onlyCancel("utility_cont.index", "cancle", "Change member registration date here");
        $member = $this->jq->flashMessages(true);
        $this->load->library('form');
        $form = $this->form->open("NewBankAccount", 'index.php?option=com_xbank&task=utility_cont.memberRegistrationDateChange')
                        ->setColumns(2)
                        ->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsL\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);jQuery(\"#accountsDetailsF\").load(\"index.php?option=com_xbank&task=accounts_cont.accountsDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsL", "", $member)
                        ->dateBox("Enter Correct Date of member registration", "name='RegistrationDate' class='input'")
                        ->submit("Correct");
        $data['contents'] = $this->form->get();
        $this->load->view("utility.html",$data);
        $this->jq->getHeader();
    }

    function memberRegistrationDateChange() {
        $m = new Member(inp("UserID"));
        if (!$m->result_count()) {
            re("utility_cont.memberRegistrationDateChangeForm", "Member not found","error");
        }
        try {
            $this->db->trans_begin();
            $m->created_at = inp("RegistrationDate");
            $m->updated_at = inp("RegistrationDate");
            $m->save();

            $narration = "10 (" . inp("UserID") . ")";
            $tran = new Transaction();
            $tran->where("Narration",$narration)->get();
            foreach ($tran as $t) {
                $t->created_at = inp("RegistrationDate") . " 01:00:00";
                $t->updated_at = inp("RegistrationDate") . " 01:00:00";
                $t->save();
            }
            $this->db->trans_commit();
            Log::write( __FILE__ . " " . __FUNCTION__ . " Member Name<b> $m->Name </b><br>Registration Date changed to $t->created_at by Staff ".Staff::getCurrentStaff()->id ." on IP". $this->input->ip_address());
            re("utility_cont.memberRegistrationDateChangeForm", "Member Name<b> $m->Name </b>. Registration Date changed to $t->created_at");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
    }

    function loanEmiChangeForm(){
        xDeveloperToolBars::onlyCancel("utility_cont.index", "cancle", "Change EMI of Loan Account here");
        $form = $this->form->open("frm", 'index.php?option=com_xbank&task=utility_cont.loanEmiChange')
                ->setColumns(2)
                ->lookupDB("Account number", "name='AccountNumber' class='input'", "index.php?option=com_xbank&task=utility_cont.getAccountNumber&format=raw", array("a" => "b"), array("id","AccountNumber","Name","EMI","Amount","created_at"), "id")
                ->submit("Change");
        $data['contents'] = $this->form->get();
        $this->load->view("utility.html",$data);
        $this->jq->getHeader();
    }

    function loanEmiChange(){
        $id = inp("AccountNumber");
        $rollback = FALSE;
        $ac = new Account($id);
        if(!$ac->result_count())
                re("utility_cont.loanEmiChangeForm","Account Number not found","error");
        $sc = new scheme($ac->schemes_id);
        $pr = new Premium();
        $pr->where("accounts_id",$ac->id)->get();

        $emi = (($ac->RdAmount * $sc->Interest * ($sc->NumberOfPremiums + 1)) / 1200 + $ac->RdAmount) / $sc->NumberOfPremiums;
        $emi = round($emi);
        try{
            $this->db->trans_begin();
            foreach($pr as $p){
                $p->Amount = $emi;
                $p->save();

            }
        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("utility_cont.loanEmiChangeForm","EMI not changed..","error");
        }
        $this->db->trans_commit();
        re("utility_cont.loanEmiChangeForm","EMI changed successfully to $emi");
    }

     function getAccountNumber() {
        $list = array();
        //$q = "select a.* from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where  (a.AccountNumber like '%" . $this->input->post("term") . "%' or a.id like '%" . $this->input->post("term") . "%') and s.`Name`='" . SAVING_ACCOUNT_SCHEME . "'";
        $q = "select a.*, m.Name,p.Amount from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id join jos_xmember m on a.member_id = m.id left join (select id,accounts_id,Amount from jos_xpremiums GROUP BY accounts_id) p on a.id=p.accounts_id where a.AccountNumber Like '%" . $this->input->post("term") . "%' or a.id like '%" . $this->input->post("term") . "%' limit 10 ";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->id, 'AccountNumber' => $dd->AccountNumber, 'Name' => $dd->Name,"EMI" => $dd->Amount,"Amount" => $dd->RdAmount,"created_at"=>$dd->created_at);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }


}
?>
