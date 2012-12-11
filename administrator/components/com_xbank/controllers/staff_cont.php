<?php

class staff_cont extends CI_Controller {

    function dashboard() {
        xDeveloperToolBars::getStaffManagementToolBar();

        global $com_params;
//        $start = JRequest::getVar("pagestart", 0);
//        $count = JRequest::getVar("pagecount", $com_params->get('RowsInData'));
//        $data['start'] = $start;
//        $data['i'] = $start;
//        $data['count'] = $count;

        $user = JFactory::getUser();
        $data['staff'] = new Staff();
        if ($user->gid < 24)
            $data['staff']->where("branch_id", Branch::getCurrentBranch()->id);
        //$data['staff']->limit(15, JRequest::getVar("limitstart", 0));
        $data['staff']->get();
        $s = new Staff();
        //jimport('joomla.html.pagination');
        $data['page'] = "";//new JPagination($s->count(), JRequest::getVar("limitstart", 0), 15); /* total, limit start, limit */

        $this->load->view("staff.html", $data);
        $this->jq->getHeader();
    }

    function createStaffform() {
        $id = JRequest::getVar("id");
        if ($id)
            $s = new Staff($id);
        xDeveloperToolBars::onlyCancel("staff_cont.dashboard", "cancel", "Create Staff");

        $b = Branch::getCurrentBranch()->id;
        //$this->jq->addInfo("Staff Member Account Details");
//        $staff = $this->jq->flashMessages(true);
//        if($error)
//            setError ("Staff ID already exists", "Create the staff again");
//        else
//            setInfo("ADD NEW STAFF", "");
        $this->load->library('form');
        $this->form->open("one", 'index.php?option=com_xbank&task=staff_cont.newStaff')
                ->setColumns(2)
                ->text("Branch ID", "name='BranchID' class='input' value='$b' READONLY")
                ->_()
                //->lookupDB("Staff Name", "name='StaffMemberName' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->text("Staff Name", "name='StaffMemberName' class='input req-string' value='" . $s->details->Name . "'")
                ->text("Staff ID(User Name)", "name='StaffID' class='input req-string' value='" . $s->StaffID . "'")
                ->password("Password", "name='Password' class='input req-same' rel='pass'")
                ->password("Re Password", "name='RePassword' class='input req-same' rel='pass'")
                ->select("Access Level", "name='AccessLevel'", array("Branch Admin" => BRANCH_ADMIN, "Power User" => POWER_USER, "User" => USER), POWER_USER)
                ->text("Basic Pay", "name='BasicPay' class='input req-string' value='" . $s->details->BasicPay . "' ")
                ->text("PF Amount", "name='PF' class='input' value='" . $s->details->PF . "'")
                ->text("Variable Pay", "name='VariablePay' class='input' value='" . $s->details->VariablePay . "'")
//                ->lookupDB("Staff Saving Account", "name='SavingAccount' class='input'", "index.php?option=com_xbank&task=staff_cont.StaffSavingAccount&format=raw", array("a" => "b"), array("ID", "AccNum", "MName", "BranchName"), "ID")
//                ->div("staffDetailsF", "", $staff)
                ->dateBox("Joining Date", "name='JoiningDate' value='" . $s->details->JoiningDate . "'")
                ->text("Father/Husband Name", "name='FatherName' class='input' value='" . $s->details->FatherName . "'")
                ->textArea("Present Address", "name='PresentAddress'", "", $s->details->PresentAddress)
                ->checkbox("Check if Permanent Address is same as Present Address", "name='samePermanentAddress' value='1'")
                ->textArea("Permanent Address", "name='PermanentAddress'", "", $s->details->PermanentAddress)
                ->_()
                ->text("Mobile Number", "name='MobileNo' class='input' maxlength='10' value='" . $s->details->MobileNo . "'")
                ->text("Landline Number", "name='LandlineNo' class='input' value='" . $s->details->LandlineNo . "'")
                ->dateBox("Date Of Birth", "name='DOB' value='" . $s->details->DOB . "'")
                ->textArea("Other Details if any", "name='OtherDetails'", "", $s->details->OtherDetails)
                ->hidden("", "name='id' value='$id'")
                ->confirmButton("Confirm", "New Staff to create", "index.php?option=com_xbank&task=staff_cont.newStaffConfirm&format=raw", true)
                ->submit('Create');

        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "addstaff");
        $this->load->view('staff.html', $data);
        $this->jq->getHeader();
    }

//    function StaffSavingAccount() {
//        $list = array();
//        // select" => "a.id AS ID, a.AccountNumber AS AccNum, b.Name AS BranchName, m.Name AS MName", "from" => "Accounts a, a.Branch b", "innerJoin" => "a.Member m", "leftJoin" => "a.Schemes s", "where" => "s.SchemeType ='" . ACCOUNT_TYPE_BANK . "' ", "andWhere" => "a.AccountNumber Like '%\$term%'", "orWhere" => "a.id Like '%\$term%'", "orWhere" => "m.Name Like '%\$term%'", "limit" => "10"
//        $q = "select a.id As ID,a.AccountNumber as AccNum,b.Name AS BranchName, m.Name AS MName from jos_xaccounts a join jos_xbranch b on a.branch_id = b.id join jos_xmember m on a.member_id = m.id join jos_xschemes s on a.schemes_id = s.id where s.SchemeType = '" . ACCOUNT_TYPE_BANK . "' and  a.AccountNumber Like '%" . $this->input->post("term") . "%' or a.id Like '%" . $this->input->post("term") . "%' or m.`Name` Like '%" . $this->input->post("term") . "%' limit 0,10";
//        $result = $this->db->query($q)->result();
//        foreach ($result as $dd) {
//            $list[] = array('ID' => $dd->ID, 'AccNum' => $dd->AccNum, 'MName' => $dd->MName, 'BranchName' => $dd->BranchName);
//        }
//        echo '{"tags":' . json_encode($list) . '}';
//    }

    function newStaffConfirm() {
        if (inp('id')) {
            $s = new staff(inp("id"));
            echo "Confirm editing $s->StaffID?";
        } else {
            $s = new Staff();
            $s->where('StaffID', inp('StaffID'))->get();
            //$s = Doctrine::getTable("Staff")->findOneByStaffid(inp('StaffID'));
            if ($s->result_count() > 0) {
                echo "<h2>ID " . inp("StaffID") . " already exists. Please create the staff with new staff ID.</h2>false";
                return;
            }

            echo "Confirm new staff creation";
        }
    }

    function newStaff() {
        //Staff::accessibleTo(BRANCH_ADMIN);
        //$conn = Doctrine_Manager::connection();
        try {
            $this->db->trans_begin();
            if(inp("id")){
                $s = new staff(inp ("id"));
                $sd = new Staff_detail();
                $sd->where("staff_id",inp("id"))->get();
            }
            else {
                $s = new Staff();
                $sd = new Staff_detail();
            }
            $s->StaffID = inp('StaffID');
            $s->Password = inp('Password');
            $s->branch_id = inp('BranchID');
            $s->AccessLevel = inp('AccessLevel');
            $s->save();

            
            $sd->Name = inp("StaffMemberName");
            $sd->BasicPay = inp("BasicPay");
            $sd->PF = inp("PF");
            $sd->VariablePay = inp("VariablePay");
            // $sd->SavingAccount = inp("SavingAccount");
            $sd->staff_id = $s->id;
            $sd->FatherName = inp("FatherName");
            $sd->PresentAddress = inp("PresentAddress");
            if (inp("samePermanentAddress")) {
                $sd->PermanentAddress = inp("PresentAddress");
            } else {
                $sd->PermanentAddress = inp("PermanentAddress");
            }
            $sd->MobileNo = inp("MobileNo");
            $sd->LandlineNo = inp("LandlineNo");
            $sd->DOB = inp("DOB");
            $sd->JoiningDate = inp("JoiningDate");
            $sd->OtherDetails = inp("OtherDetails");
            $sd->save();

            if(inp("id")){
                $q = $this->db->query("update jos_users set name = '$sd->Name',username = '".inp("StaffID")."',password = md5('".inp('Password')."') where id = $s->jid");
            }
            else
                $jsaved = $s->saveJoomlaUser(inp('StaffID'), md5(inp('Password')), $sd->Name, STAFF);

            if ($jsaved) {
                $st = new staff($s->id);
                $st->jid = $jsaved;
                $st->save();
            }

            $this->db->trans_commit();
        } catch (Exeption $e) {
            $this->db->trans_rollback();
            echo 'Code : ';
            print_r($e->errorMessage());
            return;
        }
        if(inp("id"))
            re('staff_cont.dashboard', "staff $s->StaffID Edited created");
        else
            re('staff_cont.dashboard', 'Staff Successfully created');
    }

}

?>
