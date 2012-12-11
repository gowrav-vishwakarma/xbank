<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Branch_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function dashboard() {
        xDeveloperToolBars::getBranchManagementToolBar();
        $data['branches'] = new Branch();
        $data['branches']->get();
        $this->load->view('branch.html', $data);
        $this->jq->getHeader();
    }

    function addnewform() {
//        if(JFactory::getUser()->id != )
        xDeveloperToolBars::onlyCancel("branch_cont.dashboard", "cancle", "Add a new Branch here");
        $this->load->library('form');
        $this->form->open("one", 'index.php?option=com_xbank&task=branch_cont.addnewbranch')
                ->setColumns(2)
                ->text("Branch Name", "name='Name' class='input req-string' emsg='Branch Name is must'")
                ->text("Code", "name='Code' class='input req-min' minlength ='3' maxlength='3' emsg='Branch Code Must Not Be Empty' max='3'")
                ->textArea("Address", "name='Address' class='req-string'")
                ->_()
                ->text("Admin Username", "name='Staff' class='input req-string'")
                ->_()
                ->password("Password", "name='Password' class='input req-same' rel='pass'")
                ->password("Re Password", "name='RePassword' class='input req-same' rel='pass'")
                ->submit('Create');

        $data['contents'] = $this->form->get();
        JRequest::setVar('layout', 'addnewbranch');
        $this->load->view('branch.html', $data);
        $this->jq->getHeader();
    }

    function addnewbranch() {
        global $com_params;
        $b=new Branch();
        $b->get();
        if($b->result_count() > 0)
        {
            try {
            $this->db->trans_begin();
            $b = new Branch();
            $b->Name = inp('Name');
            $b->Code = inp("Code");
            $b->Address = inp("Address");
            $b->PerformClosings = 1;
            $b->SendSMS = 1;

            $s = new Staff();
            $s->StaffID = inp('Staff');
            $s->Password = inp('Password');
            $s->AccessLevel = BRANCH_ADMIN;
            $x = $b->save();
            $s->branch_id = $b->id;
            $y = $s->save();

            $m = new Member();
            $m->Name = inp('Code') . SP . "Default";
            $m->branch_id = $b->id;
            $m->staff_id = $s->id;
            $m->IsMember = 1;
            $z = $m->save();
            log_message('error', "Default member for branch $b->Name created.");
            //$jsaved = $m->saveJoomlaUser($m->id, inp('Password'), $m->Name);
            $jsaved = $m->saveJoomlaUser(inp('Staff'), inp('Password'), $m->Name,DEFAULT_STAFF);

            if($jsaved){
                $mm = new Member($m->id);
                $mm->netmember_id = $jsaved;
                $mm->save();

                $st = new staff($s->id);
                $st->jid = $mm->netmember_id;
                $st->save();
            }

            
            

            $b_default = Branch::getDefaultBranch();
//            $sc = Doctrine::getTable('Schemes')->findByBranch_idAndCreatedefaultaccount($b_default->id, 1);
            $sc = new Scheme();
            $sc->where("branch_id", $b_default->id);
            $sc->where("CreateDefaultAccount", 1)->get();
            $i = 1;
            foreach ($sc as $s) {
//                $thisSchemesAllAccounts = Doctrine::getTable("Accounts")->findBySchemes_idAndDefaultacAndBranch_id($s->id, 1, $b_default->id);
                $thisSchemesAllAccounts = new Account();
                $thisSchemesAllAccounts->where("schemes_id", $s->id);
                $thisSchemesAllAccounts->where("DefaultAC", 1);
                $thisSchemesAllAccounts->where("branch_id", $b_default->id)->get();
                foreach ($thisSchemesAllAccounts as $oneAcc) {
                    $ac = new Account();
                    $ac->schemes_id = $s->id;
                    $ac->AccountNumber = inp('Code') . SP . substr($oneAcc->AccountNumber, 4);
                    $ac->ActiveStatus = 1;
                    $ac->member_id = $m->id;
//                    $ac->Agents = $a;
                    $ac->staff_id = Staff::getCurrentStaff()->id;
                    $ac->DefaultAC = 1;
                    $ac->branch_id = $b->id; //Not the default but this NEW BRANCH 
                    $a = $ac->save();
                }
            }

            $branch = Branch::getAllBranches();
            log_message('error', "Default Accounts for the New branch $b->Name created.");
//			TODO- OPEN ANOTHER ACCOUNTS ALSO viz COMMISSION ACCOUNT FOR BRANCH UNDER LIABILITY
        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("branch_cont.dashboard", "Branch Not Added", "error");
        }
        $this->db->trans_commit();
        re("branch_cont.dashboard", "Branch Added");
    }
    else
    {
       try {
            $this->db->trans_begin();
            $b = new Branch();
            $b->Name = 'Default';
            $b->Code = 'DFL';
            //$b->Address = 'Address';
            $b->PerformClosings = 0;
            $b->SendSMS = 0;
            $b->Published = 1;

            $s = new Staff();
            $s->StaffID = 'xadmin';
            $s->Password = 'a';
            $s->AccessLevel = BRANCH_ADMIN;
            $x = $b->save();
            $s->branch_id = $b->id;
            $y = $s->save();

            $m = new Member();
            $m->Name = 'DFL'. SP . "Default";
            $m->branch_id = $b->id;
            $m->staff_id = $s->id;
            $z = $m->save();
            log_message('error', "Default member for branch $b->Name created.");
            //$jsaved = $m->saveJoomlaUser($m->id, inp('Password'), $m->Name);
            $jsaved = $m->saveJoomlaUser('xadmin', 'a', $m->Name,'Administrator');

            if($jsaved){
                $mm = new Member($m->id);
                $mm->netmember_id = $jsaved;
                $mm->save();

                $st = new staff($s->id);
                $st->jid = $mm->netmember_id;
                $st->save();
            }

            $data=array(
                 SAVING_ACCOUNT_SCHEME=>array(LIABILITIES_HEAD,200,-1,4,'Y','HF',0,0,'',ACCOUNT_TYPE_BANK),
                 CASH_ACCOUNT_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,1),
	         BANK_ACCOUNTS_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	         BANK_OD_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 CURRENT_ASSESTS_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	         CAPITAL_ACCOUNT_SCHEME => array(CAPITAL_ACCOUNT_HEAD,0,-1,0,'Y','Y',0,1,array("Share Capital Account")),
	 	 CURRENT_LIABILITIES_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 DEPOSITS_ASSETS_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	 	 DIRECT_EXPENSES_SCHEME => array(EXPENSES_HEAD,0,-1,0,'Y','Y',0,1,array('Interest Paid On Saving Account')),
	 	 //DIRECT_INCOME_SCHEME => array(INCOME_HEAD,0,-1,0,'Y','Y',0,1,array("Admission Fee","Intreset From Loan Ag CC","Intreset From Loan Ag PL","Intreset From Loan Ag VL","File Charge","Processing Fee On Agri loan","Processing Fee On CC")),
                 DIRECT_INCOME_SCHEME => array(INCOME_HEAD,0,-1,0,'Y','Y',0,1,array("Admission Fee")),
	 	 DUTIES_TAXES_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,1,array("TDS","TDS Payable")),
                 //'Expenses(Direct)' => array('Expenses',0,-1,'Y','Y',0,0),
	 	 //'Expenses(Indirect)' => array('Expenses',0,-1,'Y','Y',0,0),
	 	 FIXED_ASSETS => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	 	 //'Income(Direct)' => array('Income',0,-1,'Y','Y',0,0),
	 	 //'Income(Indirect)' => array('Income',0,-1,'Y','Y',0,0),
	 	 INDIRECT_EXPENSES => array(EXPENSES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 INDIRECT_INCOME => array(INCOME_HEAD,0,-1,0,'Y','Y',0,0),
	 	 INVESTMENT_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	 	 LOAN_ADVANCE_ASSETS_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	 	 LOAN_LIABILITIES_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 MISC_EXPENSES_ASSETS_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0), //TODO- remove or check in Asests or Expenses
	 	 PROVISION_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 //'Purchase Account' => array('Assets',0,-1,'Y','Y',0,0),
	 	 RESERVE_SURPULS_SCHEME => array(CAPITAL_ACCOUNT_HEAD,0,-1,0,'Y','Y',0,0),
	 	 RETAINED_EARNINGS_SCHEME => array(CAPITAL_ACCOUNT_HEAD,0,-1,0,'Y','Y',0,0),

	 	 //'Sale Account' => array('Liabilities',0,-1,0,'Y','Y',0,0),
	 	 SECURED_LOAN_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 //'Stock In Hand' => array('Assets',0,-1,0,'Y','Y',0,0),
	 	 SUNDRY_CREDITOR_SCHEME => array(LIABILITIES_HEAD,0,-1,0,'Y','Y',0,0),
	 	 SUNDRY_DEBTOR_SCHEME => array(ASSETS_HEAD,0,-1,0,'Y','Y',0,0),
	 	 SUSPENCE_ACCOUNT_SCHEME => array(SUSPENCE_HEAD,0,-1,0,'Y','Y',0,0),
                 BRANCH_AND_DIVISIONS => array(BRANCH_AND_DIVISIONS_HEAD,0,-1,0,'Y','Y',0,1,BRANCH_AND_DIVISIONS),
	 				);

            foreach($data as $key=>$val){
			 		$sc=new Scheme();
			 		$sc->Name=$key;
			 		$sc->MinLimit=$val[1];
			 		$sc->MaxLimit=$val[2];
			 		$sc->Interest=$val[3];
			 		$sc->InterestMode=$val[4];
			 		$sc->PostingMode=$val[5];
			 		$sc->LoanType=$val[6];
			 		$sc->ActiveStatus=1;
			 		$sc->CreateDefaultAccount=(!$val[7])?0:$val[7];
                                        $sc->SchemeType=(!isset($val[9]))? ACCOUNT_TYPE_DEFAULT : $val[9];
			 		$sc->branch_id=$b->id;
/*  */
			 		$BS=new BalanceSheet();
                                        $BS->where("Head",$val[0])->get();
			 		if($BS->result_count() == 0)
			 			{
			 				$BS=new BalanceSheet();
			 				$BS->Head=$val[0];
			 				$BS->save();
			 			}
			 		$sc->balance_sheet_id= $BS->id;
			 		$sc->save();

                                        if($val[7]==1){ //Create Account
			 			$accounts=(!isset($val[8])) ? $key : $val[8];
			 			if(!is_array($accounts))
			 				$accounts =array($accounts);
			 			foreach($accounts as $acts){
				 			$ac=new Account();
//					 		$ac->Agents=$a;
					 		$ac->ActiveStatus=1;
					 		$ac->member_id=$m->id;
					 		$ac->DefaultAC=1;
					 		$ac->AccountNumber="DFL".SP.$acts;
					 		$ac->schemes_id=$sc->id;
					 		$ac->branch_id=$b->id;
					 		$ac->staff=$s->id;
                                                        //$ac->AccountType=ACCOUNT_TYPE_BANK;
					 		$ac->save();
			 			}
			 		}
				}


        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("branch_cont.dashboard", "Branch Not Added", "error");
        }
        $this->db->trans_commit();
        re("branch_cont.dashboard", "Branch Added");
     }
    }
    function swapbranchstatus() {
        try {
            $b = new Branch();
            $b->where('id', inp('branchid'))->get();
            $b->published = !$b->published;
            $b->save();
        } catch (Exception $e) {
            re('branch_cont.dashboard', "Branch Status not changed", "error");
        }
        re('branch_cont.dashboard', "Branch " . strtoupper($b->Name) . " is set in " . (($b->published == 1) ? "Active" : "InActive") . " Mode");
    }

}