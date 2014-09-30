<?php

class staff_cont extends CI_Controller {

    function dashboard() {
        xDeveloperToolBars::getStaffManagementToolBar();
        Staff::accessibleTo(BRANCH_ADMIN);
        global $com_params;
//        $start = JRequest::getVar("pagestart", 0);
//        $count = JRequest::getVar("pagecount", $com_params->get('RowsInData'));
//        $data['start'] = $start;
//        $data['i'] = $start;
//        $data['count'] = $count;

        $user = JFactory::getUser();
        $data['staff'] = new Staff();
        // $data['staff']->where('jid <>',0);
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

    function swapStatus(){
        Staff::accessibleTo(BRANCH_ADMIN);
        $s= new Staff(inp('id'));
        $s->swapStatus();
        re('staff_cont.dashboard',"Status Changed");
    }

    function createStaffform() {
        $id = JRequest::getVar("id");
        if ($id)
            $s = new Staff($id);
        else
            $s=new Staff();
        xDeveloperToolBars::onlyCancel("staff_cont.dashboard", "cancel", "Create Staff In Current Branch");

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
                $jsaved = $s->saveJoomlaUser(inp('StaffID'), inp('Password'), $sd->Name, STAFF);

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
            re('staff_cont.dashboard', "staff $s->StaffID Edited");
        else
            re('staff_cont.dashboard', 'Staff Successfully created');
    }


    function markAttendance() {
//        Staff::accessibleTo(BRANCH_ADMIN);
        $user = JFactory::getUser();
        $stf = new Staff();
        $stf->where("jid",$user->id)->get();
        if ($stf->AccessLevel < 80){
            re("com_xbank","Only branch admin is authorised to mark staff attendance",'error');
        }
        $b = Branch::getCurrentBranch()->id;
        $staff = new Staff();
        $staff->where("branch_id",$b)->get();

        $this->load->library('form');
        xDeveloperToolBars::onlyCancel("staff_cont.dashboard", "cancel", "Mark Staff Attendance here");
        $form = $this->form->open("one", "index.php?option=com_xbank&task=staff_cont.markStaffAttendance");
        foreach ($staff as $s) {
            if ($s->AccessLevel < 100) {
                $sa = new Staff_attendance();
                $sa->where("staff_id",$s->id);
                $sa->where("Date like '".  getNow("Y-m-d")."%'")->get();
                $status = "";
                if ($sa) {
                    switch ($sa->Attendance) {
                        case "P" :
                            $status = PRESENT;
                            break;
                        case "L" :
                            $status = LEAVE;
                            break;
                        case "A" :
                            $status = ABSENT;
                            break;
                    }
                }

                $form = $form->setColumns(2)
                                ->radio("$s->StaffID", "name='Attendance_$s->id'", array("Present " => PRESENT, "Leave " => LEAVE, "Absent " => ABSENT), $status)
                                ->textArea("Narration", "name='Narration_$s->id'");
//                ->hidden("","name='staffid' value='$s->id'");
            }
        }
        $form = $form->confirmButton("Confirm", "Mark Attendance", "index.php?option=com_xbank&task=staff_cont.confirmMarkAttendance&format=raw", true);
        $form = $form->submit('Mark');
//        echo $this->form->get();
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "showcontents");
        $this->load->view('staff.html', $data);
        $this->jq->getHeader();
    }

    function confirmMarkAttendance() {
//        Staff::accessibleTo(BRANCH_ADMIN);
        $b = Branch::getCurrentBranch()->id;
        $staff = new Staff();
        $staff->where("branch_id",$b)->get();

        foreach ($staff as $s) {
            if (inp("Attendance_$s->id") == "" && $s->AccessLevel < 80) {
                echo "<h3>Please check the attendance you have marked. Attendance not marked for some staff member...</h3><br>falsefalse";
                return;
            }
        }
        echo "<table border='1' width='100%'>";
        echo "<th>Staff ID</th>";
        echo "<th>Attendance</th>";
        foreach ($staff as $s) {
            if ($s->AccessLevel < 80) {
                echo "<tr>";
                echo "<td>$s->StaffID</td>";
                echo "<td>" . inp("Attendance_$s->id") . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function markStaffAttendance() {
//        Staff::accessibleTo(BRANCH_ADMIN);

        try {
            $this->db->trans_begin();
            $b = Branch::getCurrentBranch()->id;
            $staff = new Staff();
            $staff->where("branch_id",$b)->get();

            foreach ($staff as $s) {
                if ($s->AccessLevel > 80)
                    continue;
                $sa = new Staff_attendance();
                $sa->where("staff_id",$s->id);
                $sa->where("Date like '".  getNow("Y-m-d")."%'")->get();
                if (!$sa->result_count()) {
                    $sa = new Staff_attendance();
                }
                $sa->Date = getNow("Y-m-d");
                $sa->Attendance = inp("Attendance_$s->id");
                $sa->Narration = inp("Narration_$s->id");
                $sa->staff_id = $s->id;
                $sa->save();
            }
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
        re('com_xbank',"Staff Attendance Marked");
    }

    function markpaidholidaysForm() {
        $user = JFactory::getUser();
        $stf = new Staff();
        $stf->where("jid",$user->id)->get();
        if ($stf->AccessLevel < 80){
            re("com_xbank","Only branch admin is authorised to mark staff holidays",'error');
        }
        $b = Branch::getCurrentBranch()->id;
        $month = date("n", strtotime(getNow("Y-m-d")));
        $year = date("Y", strtotime(getNow("Y-m-d")));

        $paidHolidays = $this->getHolidays(date("n", strtotime(getNow("Y-m-d"))), date("Y", strtotime(getNow("Y-m-d"))));
        xDeveloperToolBars::onlyCancel("staff_cont.dashboard", "cancel", "Mark Staff Holidays");
        $this->load->library("form");
        $this->form->open("one", "index.php?option=com_xbank&task=staff_cont.markpaidholidays&month=$month&year=$year")
                ->setColumns(2)
                ->dateBoxMultiSelect("Mark Bank Holiday", "name='holidays' ")
                ->submit("Mark");
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "showcontents");
        $this->load->view('staff.html', $data);
        $this->jq->getHeader();
    }

    function getHolidays($month, $year) {
        $b = Branch::getCurrentBranch()->id;
        $paidHolidays = "";
        $holidays = new holiday();
        $holidays->where("year","$year");
        $holidays->where("month","$month");
        $holidays->where("branch_id",$b)->get();

        foreach ($holidays as $h) {
            $paidHolidays .=$h->HolidayDate . ",";
        }

        $paidHolidays = trim($paidHolidays, ",");
        return $paidHolidays;
    }

    function markpaidholidays() {
        $month = JRequest::getVar("month");
        $year = JRequest::getVar("year");

        try {
            $this->db->trans_begin();
            $b = Branch::getCurrentBranch()->id;
            $q = "Delete from jos_xbank_holidays where month=$month and year=$year and branch_id=$b";
            executeQuery($q);
//                        $markedholidays=  Doctrine::getTable("BankHolidays")->findByBranch_idAndMonthAndYear($b,$month,$year);
//                        $holiday=explode(",", inp("holidays"));
            $holiday = explode(",", $this->input->post("holidays"));
//                        $holidays=trim($holiday,",");
            foreach ($holiday as $h) {
                if ($month != date("n", strtotime($h)) || $year != date("Y", strtotime($h)))
                    continue;
                $bankholidays = new holiday();
                $bankholidays->HolidayDate = $h;
                $bankholidays->month = date("n", strtotime($h));
                $bankholidays->year = date("Y", strtotime($h));
                $bankholidays->branch_id = $b;
                $bankholidays->save();
            }
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
        re('staff_cont.dashboard',"Paid Holidays marked successfully");
    }




}

?>
