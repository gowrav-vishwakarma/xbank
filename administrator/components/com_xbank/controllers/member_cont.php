<?php

class Member_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function dashboard() {
        xDeveloperToolBars::getMemberManagementToolBar();

        $a= new Member();
        $a->where("branch_id",Branch::getCurrentBranch()->id);
        $a->limit(100,JRequest::getVar('page_start',0)*100);
        $a->get();
        

//        $a->check_last_query();
        $data['report'] = getReporttable($a,             //model
                array("Name","Father Name","Address","Phone Nos","Created On","Edit"),       //heads
                array('Name','FatherName',"PermanentAddress","PhoneNos","created_at","~\"<a href=index.php?option=com_xbank&task=member_cont.editMemberForm&id=#id>Edit</a>\""),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true,"sno_start"=>JRequest::getVar('page_start',0)*100,"page"=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        return;
        





        global $com_params;
        $m = new Member();
        $m->limit(10,JRequest::getVar("limitstart",0));
        $m->get();
        $mm = new Member();
//        $mm->get();

        $data['member'] = $m;
        jimport('joomla.html.pagination');
 	$data['page']=new JPagination($mm->count(), JRequest::getVar("limitstart",0), 10 ); /*total, limit start, limit */
        $this->load->view("member.html", $data);
        $this->jq->getHeader();
    }

    function addmemberform() {
        xDeveloperToolBars::onlyCancel("com_xbank.index", "cancel", "Add a new Member here");
        global $com_params;
        $xc = new xConfig('member');
        $form = $this->form->open("one", 'index.php?option=com_xbank&task=member_cont.addnewmember')
                        ->setColumns(2)
                        ->text("Name", "name='Name' class='input req-string'")
                        ->checkBox("Is Member", "name='IsMember' class='input' value='1' checked");
        if ($com_params->get('member_has_code')) {
            if (!$com_params->get('member_code_auto_generated')) {
                $form = $form->text("Member Code", "name='MemberCode' class='input req-string'");
            } else {
                $query = "select count(MemberCode) from jos_xmember where MemberCode is not null";
                $member_code = getNextCode($com_params->get("default_member_code"), $query);
                $form = $form->text("Member Code", "name='MemberCode' class='input' DISABLED value='$member_code'");
            }
        }
        $form = $form->text("Father/Husband Name", "name='FatherName' class='input req-string'")
                        ->text("Cast", "name='Cast' class='input req-string'");

        if ($xc->getKey('occupation_entry')) {
            $occupationList = explode(",", $xc->getKey("occupation_dropdown_list"));
            $arr = array();
            foreach ($occupationList as $chunk) {
                $chunk = explode('=>', $chunk);
                $arr[$chunk[0]] = $chunk[0];
            }
            $form = $form->select("Occupation", "name='Occupation' class='input req-string'", $arr);
        } else {
            $form = $form->text("Occupation", "name='Occupation' class='input req-string'");
        }
        $form = $form->textArea("Permanent Address", "name='PermanentAddress' class='req-string'")
                        ->dateBox("DOB", "name='DOB' class='input'")
                        ->text("Age", "name='Age' class='input req-numeric req-min' minlength ='1'")
                        ->text("Nominee", "name='Nominee' class='input'");
//                         $xc = new xConfig('member');
        if ($xc->getKey('relation_entry')) {
            $relationList = explode(",", $xc->getKey("relation_dropdown_list"));
            $arr = array();
            foreach ($relationList as $chunk) {
                $chunk = explode('=>', $chunk);
                $arr[$chunk[0]] = $chunk[0];
            }
            $form = $form->select("Relation With Nominee", "name='RelationWithNominee' class='input req-string'", $arr);
        } else {
            $form = $form->text("Relation With Nominee", "name='RelationWithNominee' class='input'");
        }
        $form = $form->text("Nominee Age", "name='NomineeAge' class='input' minlength='1'")
                        ->textArea("Current Address", "name='CurrentAddress' class='req-string'")
                        ->text("Phone Nos", "name='PhoneNos' class='input req-numeric'")
                        ->_()
                        ->text("PanNo", "name='PanNo' class='input'")
                        ->checkbox("Filled Form 60 (if PAN No. not there)", "name='FilledForm60' class='input' value='1'")
                        ->checkBox("Is Minor", "name='IsMinor' class='input' value='1'")
                        ->dateBox("Minor DOB", "name='MinorDOB' class='input'")
                        ->text("Parent Name", "name='ParentName' class='input'")
                        ->text("Relation With Parent", "name='RelationWithParent' class='input'")
                        ->textArea("Parent Address", "name='ParentAddress' class='input'")
                        ->checkBox("Do Transaction", "name='doTransaction' class='input' value='1' checked")
                        ->checkBox("Has Share Account", "name='hasShareAccount' class='input' value='1'");
        if (!$com_params->get('share_accountnumber_auto_generated')) {
            $form = $form->lookupDB("Share Account number  ", "name='AccountNumber' class='input'", "index.php?option=com_xbank&task=member_cont.shareAccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "");
        } else {
            $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.Name ='" . CAPITAL_ACCOUNT_SCHEME . "' ";
            $share_code = getNextCode($com_params->get("default_share_accountnumber"), $query);
            $form = $form->text("Share Account number  ", "name='AccountNumber' class='input' DISABLED value='$share_code'");
        }
        $form = $form->text("Share Account Amount", "name='shareAccountAmount' class='input'")
                        ->_()
                        ->lookupDB("SAVING Account number ", "name='SavingAccountNumber' class='input'", "index.php?option=com_xbank&task=member_cont.savingAccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "")
                        ->text("SAVING Account Amount", "name='SavingAccountAmount' class='input'")
                        ->fileupload("Upload Signature Specimen", "name='userfile'")
                        ->confirmButton("Confirm", "New Member to create", "index.php?option=com_xbank&task=member_cont.confirmMemberCreateForm&format=raw", true)
                        ->submit('Create');


        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "addmember");
        $this->load->view('member.html', $data);
        $this->jq->getHeader();
    }

    function confirmMemberCreateForm() {
//        $m = new Member();
//        $m->where('PanNo', inp('PanNo'))->get();
//        $m->where('PanNo <>',NULL)->get();
        $q = $this->db->query("select PanNo from jos_xmember where PanNo ='" . inp("PanNo") . "' and PanNo is not null")->row()->PanNo;
        if ($q->PanNo) {
            echo "<h2>Pan Number is a unique value you cannot repeat it ..</h2>falsefalse";
            return;
        }

        if (!inp("PanNo") && inp("FilledForm60") == false && inp("IsMember")) {
            echo "<h2>Please fill the Pan Number or check the check box for FilledForm60 if Pan Number not there</h2>falsefalse";
            return;
        }

        if (inp("hasShareAccount") == 1) {
            if (inp("shareAccountAmount") == "" || !is_numeric(inp("shareAccountAmount"))) {
                echo "<h2>Please check for share account </h2>falsefalse";
                return;
            }
        }
        echo "<h2>Proceed with new member creation...</h2><br>";
    }

    function addnewmember() {
        global $com_params;
        try {
            $s = Staff::getCurrentStaff(); //Current_Staff::staff();
            $b = $s->branch_id;
            $this->db->trans_begin();
            $m = new Member();
            $m->Name = inp('Name');
            $m->FatherName = inp('FatherName');
            $m->Age = inp('Age');
            $m->Cast = inp('Cast');
            $m->Occupation = inp('Occupation');
            $m->PermanentAddress = inp('PermanentAddress');
            $m->Nominee = inp('Nominee');
            $m->RelationWithNominee = inp('RelationWithNominee');
            $m->CurrentAddress = inp('CurrentAddress');
            $m->NomineeAge = inp('NomineeAge');
            $m->PhoneNos = inp('PhoneNos');
            $m->PanNo = inp('PanNo');
            $m->IsMinor = inp('IsMinor');
            $m->ParentName = inp('ParentName');
            $m->RelationWithParent = inp('RelationWithParent');
            $m->ParentAddress = inp('ParentAddress');
            $m->MinorDOB = inp('MinorDOB');
            $m->branch_id = $b;
            $m->staff_id = $s->id;
            $m->created_at = getNow();
            $m->updated_at = getNow();
            $m->IsMember = 1;
            $m->IsCustomer = 1;
            if ($com_params->get('member_has_code') && inp("IsMember")) {
                if (!$com_params->get('member_code_auto_generated')) {
                    $m->MemberCode = inp("MemberCode");
                } else {
                    $query = "select count(MemberCode) from jos_xmember where MemberCode is not null";
                    $member_code = getNextCode($com_params->get("default_member_code"), $query);
                    $m->MemberCode = $member_code;
                }
            }

            $m->DOB = inp('DOB');
            $m->FilledForm60 = inp("FilledForm60");
            $m->save();
            $jsaved = $m->saveJoomlaUser($m->id, '123456', $m->Name);

            if ($jsaved) {
                $mm = new Member($m->id);
                $mm->netmember_id = $jsaved;
                $mm->save();
            }


// Doing file upload
            $fieldName = "userfile";
            $file = $_FILES[$fieldName];


            $fileSize = $_FILES[$fieldName]['size'];
            $avatarOK = false;
            if ($fileSize > 2000000) {
                echo JText::_('FILE BIGGER THAN 2MB');
                $avatarOK = false;
            }

            if ($fileSize <= 0) {
//            echo JText::_( 'FILE NOT FOUND' );
                $avatarOK = false;
            }
//check the file extension is ok
            $fileName = $_FILES[$fieldName]['name'];
            $uploadedFileNameParts = explode('.', $fileName);
            $uploadedFileExtension = array_pop($uploadedFileNameParts);

            $validFileExts = explode(',', 'jpeg,jpg,png,gif');
//assume the extension is false until we know its ok
            $extOk = false;

//go through every ok extension, if the ok extension matches the file extension (case insensitive)
//then the file extension is ok
            foreach ($validFileExts as $key => $value) {
                if (preg_match("/$value/i", $uploadedFileExtension)) {
                    $extOk = true;
                    $avatarOK = true;
                }
            }
            if ($extOk == false) {
//                echo JText::_( 'INVALID EXTENSION' );
                $avatarOK = false;
            }
//the name of the file in PHP's temp directory that we are going to move to our folder
            $fileTemp = $_FILES[$fieldName]['tmp_name'];
            $uploadPath = "";
            if ($avatarOK) {
                $imageinfo = getimagesize($fileTemp);

//we are going to define what file extensions/MIMEs are ok, and only let these ones in (whitelisting), rather than try to scan for bad
//types, where we might miss one (whitelisting is always better than blacklisting)
                $okMIMETypes = 'image/jpeg,image/pjpeg,image/png,image/x-png,image/gif';
                $validFileTypes = explode(",", $okMIMETypes);

//if the temp file does not have a width or a height, or it has a non ok MIME, return
                if (!is_int($imageinfo[0]) || !is_int($imageinfo[1]) || !in_array($imageinfo['mime'], $validFileTypes)) {
                    $avatarOK = false;
                }

//lose any special characters in the filename
                $fileName = ereg_replace("[^A-Za-z0-9.]", "-", $fileName);
                $fileName = 'sig_' . $m->id . "." . $uploadedFileExtension;

//always use constants when making file paths, to avoid the possibilty of remote file inclusion
                $uploadPath = JPATH_SITE . SIGNATURE_FILE_PATH . $fileName;
            }
// echo $uploadPath;
            if (!move_uploaded_file($fileTemp, $uploadPath)) {
                $avatarOK = false;
            } else {
// success, exit with code 0 for Mac users, otherwise they receive an IO Error
                $AvatarFile = $fileName;
                $avatarOK = true;
                $this->load->helper('file');
                $data = read_file($uploadPath);
                write_file(JPATH_SITE . SIGNATURE_FILE_PATH . "sig_" . $m->id . "." . $uploadedFileExtension, $data);

                $config['image_library'] = 'gd2';
                $config['source_image'] = $uploadPath;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 350;
                $config['height'] = 350;
                $this->load->library('image_lib', &$config);
                $this->image_lib->resize();
            }

            if (inp('doTransaction') != '1') {
//setInfo("SAVED", "New Member Registration successfully. No Transaction made");
//redirect('mod_member/member_cont/newMemberForm');
                re("member_cont.addmemberform", "New Member Registered successfully. No Transaction made");
            }

//OPEN A SUND CRED ACCOUNT FOR THIS MEMBER
            if (inp("IsMember")) {
                $ac = Account::getAccountForCurrentBranch(CASH_ACCOUNT);
                $ac1 = Account::getAccountForCurrentBranch(ADMISSION_FEE_ACCOUNT);

                $voucherNo = Transaction::getNewVoucherNumber();
                $xc = new xConfig("member");

                $debitAccount = array(
                    CASH_ACCOUNT => $xc->getKey('MemberRegistrationCharges')
                );
                $creditAccount = array(
                    ADMISSION_FEE_ACCOUNT => $xc->getKey('MemberRegistrationCharges')
                );
                Transaction::doTransaction($debitAccount, $creditAccount, $xc->getKey('MemberRegistrationCharges') . " ($m->id)", TRA_NEW_MEMBER_REGISTRATIO_AMOUNT, $voucherNo);
            }

            if (inp("hasShareAccount")) {
                if (!$com_params->get('share_accountnumber_auto_generated'))
                    $accnum = inp("AccountNumber");
                else {
                    $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.Name ='" . CAPITAL_ACCOUNT_SCHEME . "' ";
                    $accnum = getNextCode($com_params->get("default_share_accountnumber"), $query);
                }
                $s = new Scheme();
                $s->where('Name', CAPITAL_ACCOUNT_SCHEME)->get();
                $shareacc = new Account();
                $shareacc->member_id = $m->id;
                $shareacc->schemes_id = $s->id;
                $shareacc->staff_id = Staff::getCurrentStaff()->id;
                $shareacc->branch_id = Branch::getCurrentBranch()->id;
                $shareacc->ActiveStatus = 1;
                $shareacc->AccountNumber = $accnum;
                $shareacc->created_at = getNow();
                $shareacc->updated_at = getNow();
                $shareacc->DefaultAC = '0';
                $shareacc->LastCurrentInterestUpdatedAt = getNow();
                $shareacc->save();

                $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $shareacc->id);
                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("shareAccountAmount")
                );
                $creditAccount = array(
                    $accnum => inp("shareAccountAmount")
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "Share Account Opened for member $m->Name", TRA_SHARE_ACCOUNT_OPEN, $voucherNo);
            }

            if (inp("SavingAccountNumber") != "") {

                $s = new Scheme();
                $s->where('Name', SAVING_ACCOUNT_SCHEME)->get();
                $sbacc = new Account();
                $sbacc->member_id = $m->id;
                $sbacc->schemes_id = $s->id;
                $sbacc->staff_id = Staff::getCurrentStaff()->id;
                $sbacc->branch_id = Branch::getCurrentBranch()->id;
//			$Ac->OpeningBalance=inp('OpenningBalance');
                $sbacc->ActiveStatus = 1;
                $sbacc->AccountNumber = inp("SavingAccountNumber");

                $sbacc->DefaultAC = '0';
                $sbacc->LastCurrentInterestUpdatedAt = getNow();
                $sbacc->save();

                if (inp("SavingAccountAmount") > 0) {
                    $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $sbacc->id);
                    $debitAccount = array(
                        Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("SavingAccountAmount")
                    );
                    $creditAccount = array(
                        inp("SavingAccountNumber") => inp("SavingAccountAmount")
                    );
                    Transaction::doTransaction($debitAccount, $creditAccount, "Initial Saving Amount Deposit in $sbacc->AccountNumber", TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
                }
            }


//            $conn->commit();
        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("member_cont.addmemberform", " Member Not Added ", "error");
        }
        $this->db->trans_commit();
        $msg2 = "";
        $msg3 = "";
        $msg4 = "";
        $msg1 = "Member";
        if ($com_params->get('member_has_code'))
            $msg2 = " with code " . $m->MemberCode;
        if (inp("hasShareAccount"))
            $msg3 = " and Share Capital Account " . $shareacc->AccountNumber;
        if(inp("SavingAccountNumber"))
            $msg3 = " and Saving Account " . $sbacc->AccountNumber;
        $msg5 = " Added Successfully";
        $msg = $msg1 . $msg2 . $msg3 . $msg4 . $msg5;
        re("member_cont.addmemberform", $msg);
    }

    /**
     *
     * @param <type> $id
     * Function generates a <b>FORM</b> to edit a member
     * Does not actually edit a member
     * Sends the link to {@link editMember}
     */
    function editMemberForm() {
        global $com_params;
        xDeveloperToolBars::onlyCancel("member_cont.dashboard", "cancel", "Edit Details here");
        $b = Branch::getCurrentBranch();
        $id = JRequest::getVar("id");
        $m = new Member($id);
        $scheme = new Scheme();
        $scheme->where('name', CAPITAL_ACCOUNT_SCHEME)->get();
        $hasShareAccount = new Account();
        $hasShareAccount->where('member_id', $id)->where('schemes_id', $scheme->id)->get();

        $ag = new Agent();
        $ag->where('member_id',$id)->get();

        $this->load->library('form');
        $form = $this->form->open("one", "index.php?option=com_xbank&task=member_cont.editMember&id=$id")
                        ->setColumns(2)
                        ->text("Name", "name='Name' class='input req-string' value='$m->Name'")
                        ->text("Father/Husband Name", "name='FatherName' class='input req-string' value='$m->FatherName'")
                        ->text("Cast", "name='Cast' class='input' value='$m->Cast'")
                        ->text("Occupation", "name='Occupation' class='input req-string' value='$m->Occupation'")
                        ->textArea("Permanent Address", "name='PermanentAddress' READONLY", "", "$m->PermanentAddress")
                        ->text("Age", "name='Age' class='input req-numeric req-min' minlength ='1' value='$m->Age' ")
                        ->text("Nominee", "name='Nominee' class='input' value='$m->Nominee'")
                        ->text("Relation With Nominee", "name='RelationWithNominee' class='input' value='$m->RelationWithNominee' ")
                        ->text("Nominee Age", "name='NomineeAge' class='input' minlength='1' value='$m->NomineeAge'")
                        ->textArea("Current Address", "name='CurrentAddress' class='req-string'", "", "$m->CurrentAddress")
                        ->text("Phone Nos", "name='PhoneNos' class='input req-string' value='$m->PhoneNos'")
                        ->text("PanNo", "name='PanNo' class='input' value='$m->PanNo'")
                        ->_()
                        ->checkBox("Is Minor", "name='IsMinor' class='input' value='$m->IsMinor'")
                        ->dateBox("Minor DOB", "name='MinorDOB' class='input' value='$m->MinorDOB'")
                        ->text("Parent Name", "name='ParentName' class='input' value='$m->ParentName'")
                        ->text("Relation With Parent", "name='RelationWithParent' class='input' value='$m->RelationWithParent'")
                        ->textArea("Parent Address", "name='ParentAddress' class=''", "", "$m->ParentAddress")
                        ->checkBox("Do Transaction", "name='doTransaction' class='input' value='1' checked");

        if ($hasShareAccount->result_count() == 0) {
            if (!$com_params->get('share_accountnumber_auto_generated')) {
                $form = $form->lookupDB("Share Account number  ", "name='AccountNumber' class='input'", "index.php?option=com_xbank&task=member_cont.shareAccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "");
            } else {
                $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.Name ='" . CAPITAL_ACCOUNT_SCHEME . "' ";
                $share_code = getNextCode($com_params->get("default_share_accountnumber"), $query);
                $form = $form->text("Share Account number  ", "name='AccountNumber' class='input' DISABLED value='$share_code'");
            }
        } else {
            $form = $form->text("Share Account number : $b->Code - ", "name='AccountNumber' class='input' value='$hasShareAccount->AccountNumber' READONLY");
        }

        if ($ag->result_count() > 0) {
            $form = $form->checkBox("Is Agent", "name='isAgent' value='1' CHECKED")
            ->lookupDB("Agent Saving Account Number", "name='AgentAccount' class='input' value='$ag->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "");
        } else {
            $form = $form->checkBox("Is Agent", "name='isAgent' class='input' value='1'")
                            ->lookupDB("Agent Saving Account Number", "name='AgentAccount' class='input'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "");
        }


        $form = $form->fileupload("Upload Signature Specimen", "name='userfile'")
//                ->confirmButton("Confirm","New Member to create","index.php?//mod_member/member_cont/confirmMemberCreateForm",true)
                        ->_()
//                        ->confirmButton("Confirm", "Edit Member", "index.php?option=com_xbank&task=member_cont.confirmMemberEditForm&id=$id&format=raw", true);
//                        ->resetBtn('Reset');
                        ->submit('Edit');

        $data['contents'] = $this->form->get();

        JRequest::setVar("layout", "editmember");
        $this->load->view('member.html', $data);
        $this->jq->getHeader();
    }

    function confirmMemberEditForm() {
        $id = JRequest::getVar("id");
        $m = new Member($id);
        $m->where('PanNo', inp('PanNo'))->get();
        if ($m->result_count > 0) {
            echo "<h2>Pan Number is a unique value you cannot repeat it ..</h2>falsefalse";
//            return;
        }

//        if (inp("isAgent") == 1) {
//            if (inp("AgentAccount") == "") {
//                echo "<h2>Please give agent's Saving Account Number</h2>false";
//                return;
//            }
//        }
        if (inp("hasShareAccount") == 1) {
            if (inp("shareAccountAmount") == "" || !is_numeric(inp("shareAccountAmount"))) {
                echo "<h2>Please check for share account </h2>falsefalse";
//                return;
            }
        }
//            echo "image path ". inp("userSignature");
        echo "<h2>Proceed with editing member details...</h2>";
    }

    /**
     * Actual member editing is done
     */
    function editMember() {
        global $com_params;
        $id = JRequest::getVar("id");
        try {
            $s = Staff::getCurrentStaff();
//            $b = $s->Branch;
//$conn = Doctrine_Manager::connection();
            $this->db->trans_begin();
            $m = new Member($id);
//$m = Doctrine::getTable('Member')->find($id);
            $m->Name = inp('Name');
            $m->FatherName = inp('FatherName');
            $m->Cast = inp('Cast');
            $m->Occupation = inp('Occupation');
            $m->Age = inp('Age');
            $m->CurrentAddress = inp('CurrentAddress');
            $m->Nominee = inp('Nominee');
            $m->RelationWithNominee = inp('RelationWithNominee');
            $m->NomineeAge = inp('NomineeAge');
            $m->PhoneNos = inp('PhoneNos');
            $m->IsMinor = inp('IsMinor');
            $m->MinorDOB = inp('MinorDOB');
            $m->ParentName = inp('ParentName');
            $m->RelationWithParent = inp('RelationWithParent');
            $m->ParentAddress = inp('ParentAddress');
            $m->PanNo = inp("PanNo");
            $m->save();

// Doing file upload
            $fieldName = "userfile";
            $file = $_FILES[$fieldName];


            $fileSize = $_FILES[$fieldName]['size'];
            $avatarOK = false;
            if ($fileSize > 20000000) {
                echo JText::_('FILE BIGGER THAN 2MB');
                $avatarOK = false;
            }

            if ($fileSize <= 0) {
//            echo JText::_( 'FILE NOT FOUND' );
                $avatarOK = false;
            }
//check the file extension is ok
            $fileName = $_FILES[$fieldName]['name'];
            $uploadedFileNameParts = explode('.', $fileName);
            $uploadedFileExtension = array_pop($uploadedFileNameParts);

            $validFileExts = explode(',', 'jpeg,jpg,png,gif');
//assume the extension is false until we know its ok
            $extOk = false;

//go through every ok extension, if the ok extension matches the file extension (case insensitive)
//then the file extension is ok
            foreach ($validFileExts as $key => $value) {
                if (preg_match("/$value/i", $uploadedFileExtension)) {
                    $extOk = true;
                    $avatarOK = true;
                }
            }
            if ($extOk == false) {
//                echo JText::_( 'INVALID EXTENSION' );
                $avatarOK = false;
            }
//the name of the file in PHP's temp directory that we are going to move to our folder
            $fileTemp = $_FILES[$fieldName]['tmp_name'];
            $uploadPath = "";
            if ($avatarOK) {
                $imageinfo = getimagesize($fileTemp);

//we are going to define what file extensions/MIMEs are ok, and only let these ones in (whitelisting), rather than try to scan for bad
//types, where we might miss one (whitelisting is always better than blacklisting)
                $okMIMETypes = 'image/jpeg,image/pjpeg,image/png,image/x-png,image/gif';
                $validFileTypes = explode(",", $okMIMETypes);

//if the temp file does not have a width or a height, or it has a non ok MIME, return
                if (!is_int($imageinfo[0]) || !is_int($imageinfo[1]) || !in_array($imageinfo['mime'], $validFileTypes)) {
                    $avatarOK = false;
                }

//lose any special characters in the filename
                $fileName = ereg_replace("[^A-Za-z0-9.]", "-", $fileName);
                $fileName = 'sig_' . $m->id . "." . $uploadedFileExtension;

//always use constants when making file paths, to avoid the possibilty of remote file inclusion
                $uploadPath = JPATH_SITE . SIGNATURE_FILE_PATH . $fileName;
            }
// echo $uploadPath;
            if (!move_uploaded_file($fileTemp, $uploadPath)) {
                $avatarOK = false;
            } else {
// success, exit with code 0 for Mac users, otherwise they receive an IO Error
                $AvatarFile = $fileName;
                $avatarOK = true;
                $this->load->helper('file');
                $data = read_file($uploadPath);
                write_file(JPATH_SITE . SIGNATURE_FILE_PATH . "sig_" . $m->id . "." . $uploadedFileExtension, $data);

                $config['image_library'] = 'gd2';
                $config['source_image'] = $uploadPath;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 350;
                $config['height'] = 350;
                $this->load->library('image_lib', &$config);
                $this->image_lib->resize();
            }

           $ag = new Agent();
            $ag->where("member_id",$m->id)->get();
            if (inp('isAgent')) {
                if(!$ag->result_count())
                    $a = new Agent();
                else
                    $a = new Agent($ag->id);
                $a->member_id = $m->id;
                $a->AccountNumber = inp("AgentAccount");
                $a->save();
            }
            /*            $BranchAgent=Branch::getDefaultAgent();
              $ac=new Accounts();
              $ac->Member=$m;
              $ac->Schemes=Doctrine::getTable('Schemes')->findOneByName('Saving Account');
              $ac->Agents=$BranchAgent;
              $ac->Branch=$b;
              $ac->ActiveStatus=1;
              //				$ac->AccountNumber=$b->Code."_Agent_SA_". $m->id;
              $ac->AccountNumber=inp("AgentAccount");
              $ac->ModeOfOperation="Self";
              $ac->Staff=$s;
              $ac->LastCurrentInterestUpdatedAt=getNow();
              $ac->save();
             *
             */
//            }
            if (inp("hasShareAccount")) {
                if (inp("AccountNumber") != "")
                    $accnum = inp("AccountNumber");
                else
                    $accnum = "S.M$m->id $m->Name";

                $shareacc = new Account();
                $shareacc->member_id = $m->id;
                $scheme = new Scheme();
                $scheme->where('Name', CAPITAL_ACCOUNT_SCHEME)->get();
                $shareacc->schemes_id = $scheme->id;
// $shareacc->schemes_id = Doctrine::getTable("Schemes")->findOneByName(CAPITAL_ACCOUNT_SCHEME)->id;
                $shareacc->staff_id = Staff::getCurrentStaff()->id;
                $shareacc->branch_id = Branch::getCurrentBranch()->id;
//			$Ac->OpeningBalance=inp('OpenningBalance');
                $shareacc->ActiveStatus = 1;
                $shareacc->AccountNumber = $accnum;

                $shareacc->DefaultAC = '0';
                $shareacc->LastCurrentInterestUpdatedAt = getNow();
                $shareacc->save();

                $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $shareacc->id);
                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("shareAccountAmount")
                );
                $creditAccount = array(
                    $accnum => inp("shareAccountAmount")
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "Share Account Opened for member $m->Name", TRA_SHARE_ACCOUNT_OPEN, $voucherNo);
            }
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
// 		setInfo("Saved","Your new Category has been sucessfully saved");
//redirect('mod_member/member_cont');
	$msg = "";
	if($file && !$avatarOK )
		$msg = "Image File Not Uploaded successfully.".JPATH_SITE . SIGNATURE_FILE_PATH . "sig_" . $m->id . "." . $uploadedFileExtension;
        re("member_cont.dashboard","Member Edited. $msg");
    }

    function shareAccountNumber() {
        $list = array();
        $q = "select a.* from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where  (a.AccountNumber like '%" . $this->input->post("term") . "%' or a.id like '%" . $this->input->post("term") . "%') and s.`Name`='" . CAPITAL_ACCOUNT_SCHEME . "'";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function savingAccountNumber() {
        $list = array();
        $q = "select a.* from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where  (a.AccountNumber like '%" . $this->input->post("term") . "%' or a.id like '%" . $this->input->post("term") . "%') and s.`Name`='" . SAVING_ACCOUNT_SCHEME . "'";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function shareAccountNumberedit() {
        $list = array();
        $q = "select a.* from jos_xaccounts a left join jos_xbranch b on a.branch_id = b.id where(a.AccountNumber like '%" . $this->input->post("term") . "%' and b.id like '%" . $this->input->post("term") . "%')";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function agentsavingAccountNumber() {
        $list = array();
        $q = "select a.*,m.Name as Name from jos_xaccounts a left join jos_xmember m on a.member_id = m.id where (a.AccountNumber like '%" . $this->input->post("term") . "%' and a.member_id like '%" . $this->input->post("term") . "%')";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber, 'Name' => $dd->Name);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function convertToCustomerForm() {
//        global $com_params;
//        if($com_params->get('customer_code_auto_generated')){
//        $id = JRequest::getVar("id");
//        $query = "select count(CustomerCode) from jos_xmember where CustomerCode is not null";
//        $customer_code = getNextCode($com_params->get("default_customer_code"),$query);
//
//        $m = new Member($id);
//        $m->CustomerCode = $customer_code;
//        $m->IsCustomer = 1;
//        $m->save();
//        return;
//        }
        $id = JRequest::getVar("id");
        $member = new Member($id);
        xDeveloperToolBars::onlyCancel("member_cont.dashboard", "cancel", "Convert $member->Name to Customer here");
        global $com_params;
        $xc = new xConfig('member');
        $form = $this->form->open("one", 'index.php?option=com_xbank&task=member_cont.convertToCustomer')
                        ->setColumns(2)
                        ->text("Name", "name='Name' class='input req-string' value='$member->Name' READONLY");
        if (!$com_params->get('customer_code_auto_generated')) {
            $form = $form->text("Member Code", "name='CustomerCode' class='input req-string'");
        } else {
            $query = "select count(CustomerCode) from jos_xmember where CustomerCode is not null";
            $customer_code = getNextCode($com_params->get("default_customer_code"), $query);
            $form = $form->text("Customer Code", "name='CustomerCode' class='input' DISABLED value='$customer_code'");
        }
        $form = $form->lookupDB("Parent Member (In case of Minor)", "name='ParentMember' class='input' onblur='javascript:$(\"#memberDetailsL\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&id=\"+this.value);$(\"#accountsDetailsF\").load(\"index.php?option=com_xbank&task=accounts_cont.accountsDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->text("Office Address", "name='OfficeAddress' class='input'")
                        ->text("Office Phone Numbers", "name='OfficePhoneNos' class='input'")
                        ->text("Email Id", "name='Email' class='input'")
                        ->select("Blood Group", "name='BloodGroup'", array("A+" => "A+", "B+" => "B+", "A-" => "A-", "B-" => "B-", "AB+" => "AB+", "AB-" => "AB-", "O+" => "O+", "O-" => "O-"))
                        ->select("Marital Status", "name='MaritalStatus'", array("Single" => "Single", "Married" => "Married", "Widow" => "Widow", "Divorcee" => "Divorcee"))
                        ->text("Number of Children", "name='NumberOfChildren' class='input'")
                        ->dateBox("Marriage Date", "name='MarriageDate' class='input'")
                        ->text("Highest Qualification Held", "name='HighestQualification' class='input'")
                        ->textArea("Occupation Details", "name='OccupationDetails' class='input'")
                        ->textArea("Employer Address", "name='EmployerAddress' class='input'")
                        ->textArea("Self Employee Details", "name='SelfEmployeeDetails' class='input'")
                        ->text("Family Monthly Income", "name='FamilyMonthlyIncome' class='input'")
                        ->text("Bank", "name='Bank' class='input'")
                        ->text("Branch", "name='Branch' class='input'")
                        ->text("Account Number", "name='AccountNumber' class='input'")
                        ->text("Debit/Credit Card Number", "name='DebitCreditCardNo' class='input'")
                        ->text("Debit/Credit Card Issuing Bank", "name='DebitCreditCardIssuingBank' class='input'")
                        ->text("Passport No", "name='PassportNo' class='input'")
                        ->text("Passport Issued At", "name='PassportIssuedAt' class='input'")
                        ->setColumns(1)
                        ->div("div3", "align='center' style='font-size: 12px; color: #00C; font-weight: bold;' class='ui-widget-header'", "Verification Documents")
                        ->checkBox("Employer Card", "name='EmployerCard' class='input' value='1'")
                        ->checkBox("Passport", "name='Passport' class='input' value='1'")
                        ->checkBox("Pan Card", "name='PanCard' class='input' value='1'")
                        ->checkBox("Voter Id Card", "name='VoterIdCard' class='input' value='1'")
                        ->checkBox("Driving License", "name='DrivingLicense' class='input' value='1'")
                        ->checkBox("Govt. Army Id Card", "name='GovtArmyIdCard' class='input' value='1'")
                        ->checkBox("Ration Card", "name='RationCard' class='input' value='1'")
                        ->checkBox("Other Document", "name='OtherDocument' class='input' value='1'")
                        ->textArea("Documents Description", "name='DocumentDescription' class='input'")
                        ->setColumns(1)
                        ->div("div3", "align='center' style='font-size: 12px; color: #00C; font-weight: bold;' class='ui-widget-header'", "How Do You Came To Know About Us?")
                        ->checkBox("By Newspaper", "name='CameToKnowByNewspaper' class='input' value='1'")
                        ->checkBox("By Television", "name='CameToKnowByTelevision' class='input' value='1'")
                        ->checkBox("By Advertisement", "name='CameToKnowByAdvertisement' class='input' value='1'")
                        ->checkBox("By Friends", "name='CameToKnowByFriends' class='input' value='1'")
                        ->checkBox("By Fieldworker", "name='CameToKnowByFieldworker' class='input' value='1'")
                        ->textArea("Other Details", "name='OtherDetails' class='input'")
                        ->hidden("", "name='id' value='$id'")
                        ->submit('Convert');


        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "addcustomer");
        $this->load->view('member.html', $data);
        $this->jq->getHeader();
    }

    function convertToCustomer() {
        try {
            $this->db->trans_begin();
            $m = new Member(inp("id"));
            global $com_params;
            if ($com_params->get('customer_code_auto_generated')) {
                $query = "select count(CustomerCode) from jos_xmember where CustomerCode is not null";
                $customer_code = getNextCode($com_params->get("default_customer_code"), $query);
            }
            else
                $customer_code = inp("CustomerCode");
            $m->CustomerCode = $customer_code;
            $m->IsCustomer = 1;
            $m->parent_member_id = inp("ParentMember");
            $m->customer_created_at = getNow("Y-m-d");
            $m->OfficeAddress = inp("OfficeAddress");
            $m->OfficePhoneNos = inp("OfficePhoneNos");
            $m->Email = inp("Email");
            $m->BloodGroup = inp("BloodGroup");
            $m->MaritalStatus = inp("MaritalStatus");
            $m->NumberOfChildren = inp("NumberOfChildren");
            $m->MarriageDate = inp("MarriageDate");
            $m->HighestQualification = inp("HighestQualification");
            $m->OccupationDetails = inp("OccupationDetails");
            $m->EmployerAddress = inp("EmployerAddress");
            $m->SelfEmployeeDetails = inp("SelfEmployeeDetails");
            $m->FamilyMonthlyIncome = inp("FamilyMonthlyIncome");
            $m->Bank = inp("Bank");
            $m->Branch = inp("Branch");
            $m->AccountNumber = inp("AccountNumber");
            $m->DebitCreditCardNo = inp("DebitCreditCardNo");
            $m->DebitCreditCardIssuingBank = inp("DebitCreditCardIssuingBank");
            $m->PassportNo = inp("PassportNo");
            $m->PassportIssuedAt = inp("PassportIssuedAt");
            $m->EmployerCard = inp("EmployerCard");
            $m->Passport = inp("Passport");
            $m->PanCard = inp("PanCard");
            $m->VoterIdCard = inp("VoterIdCard");
            $m->DrivingLicense = inp("DrivingLicense");
            $m->GovtArmyIdCard = inp("GovtArmyIdCard");
            $m->RationCard = inp("RationCard");
            $m->OtherDocument = inp("OtherDocument");
            $m->DocumentDescription = inp("DocumentDescription");
            $m->CameToKnowByNewspaper = inp("CameToKnowByNewspaper");
            $m->CameToKnowByTelevision = inp("CameToKnowByTelevision");
            $m->CameToKnowByAdvertisement = inp("CameToKnowByAdvertisement");
            $m->CameToKnowByFriends = inp("CameToKnowByFriends");
            $m->CameToKnowByFieldworker = inp("CameToKnowByFieldworker");
            $m->OtherDetails = inp("OtherDetails");
            $m->save();
        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("member_cont.dashboard", " Customer Not Added ", "error");
        }
        $this->db->trans_commit();
        re("member_cont.dashboard", "$m->Name added as Customer with Customer Code $m->CustomerCode");
    }


    function dealerform() {
//        Staff::accessibleTo(POWER_USER);
        xDeveloperToolBars::onlyCancel("com_xbank.index", "cancel", "Add a new Dealer here");
        $this->load->library('form');
        $form = $this->form->open("one", "index.php?option=com_xbank&task=member_cont.createDealer")
                        ->setColumns(2)
                        ->text("Dealer Name", "name='dealername' class='input req-string'")
                        ->textArea("Address", "name='address' ")
                        ->submit('Submit');

        echo $this->form->get();

//        $this->load->view('member.html');
        $this->jq->getHeader();
    }

    function createDealer() {
//        Staff::accessibleTo(POWER_USER);

//        $conn = Doctrine_Manager::connection();
        try {
            $this->db->trans_begin();
            $d = new Dealer();
            $d->DealerName = inp("dealername");
            $d->Address = inp("address");
            $d->save();

            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
        re('member_cont.dealerform',inp("dealername")." - Dealer Added Successfully.");
    }




    function test() {
//        echo extractNumberFromString($default)."<br>";
//        echo extractPrefixFromString($default);
//        $nos = 5;
//        echo str_repeat('0',strlen($nos))."<br>";
//        $pr=substr('J0000000','0',strlen('J0000000')- strlen($nos));
//        echo $pr."<br>";
//        $pr .= ($nos + 1);
//        echo $pr;

        global $com_params;
        $query = "select count(MemberCode) from jos_xmember where MemberCode is not null";
        echo getNextCode($com_params->get("default_member_code"), $query);

        

    }

}