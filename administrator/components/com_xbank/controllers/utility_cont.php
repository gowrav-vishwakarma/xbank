<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class utility_cont extends CI_Controller{

        function index(){
             xDeveloperToolBars::getUtilityManagementToolBar();
             $data['contents']="";
             $this->load->view("utility.html",$data);
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

    function errorReport(){
        xDeveloperToolBars::onlyCancel("utility_cont.index", "cancle", "Bug Finder");
        $this->session->set_userdata('fromdate','1970-01-01');
        $this->session->set_userdata('todate',getNow());
        $data['report']="";

        $a=new Account();
        $a->include_related('scheme','Name');
        $a->where('ActiveStatus',0);
        $a->where('affectsBalanceSheet',0);
        $a->get();
        $data['report'] .= "<h2>De Activated Accounts Not Effecting Balance Sheet</h2>";
        $data['report'] .= getReporttable($a,             //model
                array("Account Number", 'Scheme','Balance','Transactions Count'),       //heads
                array('AccountNumber','scheme_Name','~((#OpeningBalanceDr+#CurrentBalanceDr) - (#OpeningBalanceCr+#CurrentBalanceCr))','~#transactions->count()'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "De Activated Accounts Not Effecting Balance Sheet",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array('AccountNumber'=>array(
                                              'task'=>'balancesheet_cont.digin',
                                              'class'=>'alertinwindow',
                                              'title'=>'_blank',
                                              'url_post'=>array('digtype'=>'"AccountNumber"','format'=>'"raw"','digid'=>'#AccountNumber')
                                              )
                      )//Links array('field'=>array('task'=>,'class'=>''))
                );


        $a=new Account();
        $a->where_related('member','Name is null');
        $a->get();
        echo "<h2> Accounts with out Member</h2>";
        foreach($a as $aa){
            echo "<a href='index.php?format=raw&option=com_xbank&task=balancesheet_cont.digin&digtype=AccountNumber&digid=".(urlencode($aa->AccountNumber))."' class='alertinwindow'>" .$aa->AccountNumber . " (". ($aa->transactions->count()) .") ". ($aa->ActiveStatus == 1 ? 'Active' : 'Unactive') . "  [" .($aa->affectsBalanceSheet == 1 && $aa->ActiveStatus==0 ? 'BalanceSheet Effected' :'') ."]</a><br/>";
        }

        echo "<h2> Accounts With same number if space is neglacted</h2>";
        $a=new Account();
        $a->where('AccountNumber',
            'sbjh585')->or_where('AccountNumber',
            'OGN commission paid on SB')->or_where('AccountNumber',
            'RD 150')->or_where('AccountNumber',
            'OGN commission paid on  SB')->or_where('AccountNumber',
            'RD150')->or_where('AccountNumber',
            'RDOGN387')->or_where('AccountNumber',
            'RDOGN 387')->or_where('AccountNumber',
            'sbjh585')->or_where('AccountNumber',
            'VLGOG 108')->or_where('AccountNumber',
            'vlgog108');
        $a->get();
        foreach($a as $aa){
            echo "<a href='index.php?format=raw&option=com_xbank&task=balancesheet_cont.digin&digtype=AccountNumber&digid=".(urlencode($aa->AccountNumber))."' class='alertinwindow'>" .$aa->AccountNumber . " (". ($aa->transactions->count()) .") ". ($aa->ActiveStatus == 1 ? 'Active' : 'Unactive') . "  [" .($aa->affectsBalanceSheet == 1 && $aa->ActiveStatus==0 ? 'BalanceSheet Effected' :'') ."]</a><br/>";
        }

        echo "<h2> Accounts id in premium table which does not exists and premiums are set for</h2>";
        $a=new Premium();
        $a->select('accounts_id')->distinct();
        $a->where_related("account",'id is null');
        // $a->having("prc",0);
        $a->get();

        foreach($a as $aa){
            echo $aa->accounts_id . "<br/>";
        }

        echo "<h2>Agents With Wrong Saving Accounts </h2>";
        $a=$this->db->query("SELECT
                            m.`Name` member_Name, ag.id id, ag.AccountNumber AccountNumber, m.id member_id
                            FROM
                            jos_xagents ag
                            left join jos_xaccounts ac on ag.AccountNumber=ac.AccountNumber
                            left join jos_xmember m on ag.member_id = m.id
                            where ac.id is null")->result();

        foreach($a as $aa){
            $acc=new Account();
            $acc->select_func('count',"*",'counts');
            $acc->where('agents_id',$aa->id);
            $acc->get();
            // echo $acc->check_last_query();
            echo $aa->AccountNumber . " (". ($aa->member_Name . " - " .  $aa->member_id) .") accounts opened [". $acc->counts ."]<br/>";
            // echo $aa->AccountNumber . "<br/>";
        }
        
        echo "<h2>Transactions without Proper AccountNumber</h2>";
        $t = new Transaction();
        $t->where_related('account','id is null');
        $t->get();
        
        foreach($t as $aa){
            echo "<a href='index.php?format=raw&option=com_xbank&task=report_cont.transactionDetails&vn=".(($aa->voucher_no))."&branch_id=".$aa->branch_id."' class='alertinwindow'> Voucher " .$aa->voucher_no . " (". ($aa->display_voucher_no) .") [" . $aa->branch->Name ."]</a><br/>";
        }        
        
        $data['report'].= "<h2>Accounts with (OpCr+TransactionCr - OpDR+TransactionDR) <> (CurrenBalCr - CurrenBalDr) </h2>";
        $a=$this->db->query("SELECT
                            a.AccountNumber,
                            a.OpeningBalanceCr + tr.amountCr TransactionCr,
                            a.OpeningBalanceDr + tr.amountDr TransactionDr,
                            a.CurrentBalanceCr AccountCr,
                            a.CurrentBalanceDr AccountDr
                            FROM
                            jos_xaccounts a join
                            (
                                SELECT sum(amountCr) amountCr, sum(amountDr) amountDr, accounts_id from jos_xtransactions t GROUP BY accounts_id
                            ) tr on tr.accounts_id = a.id
                            having (AccountCR - AccountDR) - (TransactionCR-TransactionDR) > 0.5")->result();

        $data['report'] .= getReporttable($a,             //model
                array("Account Number", 'OpCR + TransactionCr','OpDr + TransactionDr','Curr_AccountDR','Curr_AccountCR'),       //heads
                array('AccountNumber','TransactionCr','TransactionDr','AccountCr','AccountDr'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "header",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array('AccountNumber'=>array(
                                              'task'=>'balancesheet_cont.digin',
                                              'class'=>'alertinwindow',
                                              'title'=>'_blank',
                                              'url_post'=>array('digtype'=>'"AccountNumber"','format'=>'"raw"','digid'=>'#AccountNumber')
                                              )
                      )//Links array('field'=>array('task'=>,'class'=>''))
                );

        
        $data['report'] .= "<h2>Agents with wrong member ID</h2>";
        $a=new Agent();
        $a->where_related('member','id is null');
        $a->include_related_count('accountsopenned','accopen');
        $a->get();

        $data['report'] .= getReporttable($a,             //model
                array("Agent Sb Account", 'TransactionCr','TransactionDr','AccountDR','AccountCR'),       //heads
                array('AccountNumber','TransactionCr','TransactionDr','AccountCr','AccountDr'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "header",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array()//Links array('field'=>array('task'=>,'class'=>''))
                );        

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);

        $this->jq->getHeader();

    }

}
