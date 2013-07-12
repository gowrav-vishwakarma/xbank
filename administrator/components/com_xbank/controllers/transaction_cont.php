<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Created on Sep 1, 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

/**
 * Description of transaction_cont
 *
 * @author Xavoc
 */
class transaction_cont extends CI_Controller {

    /**
     * Default function of transaction_cont
     */
    function index() {
        xDeveloperToolBars::getTransactionManagementToolBar();
        $this->load->view("transaction.html");
       $this->jq->getHeader();
    }

    /**
     * Function generates a withdrawal <b>FORM</b>
     * Actual withdrawal is not done
     * Sends the link to {@link DoWithdrawl}
     */
    function withdrawl() {
        

         xDeveloperToolBars::onlyCancel("transaction_cont.index", "cancel", "Withdrawl Amount");
        $this->form->open("WithDrawl", 'index.php?option=com_xbank&task=transaction_cont.DoWithdrawl')
                ->setColumns(2)
// 		->text("Account Number","name='AccountNumber' class='input req-string' onblur='javascript:$(\"#memberDetail\").load(\"index.php?//mod_transaction/transaction_cont/accountDetails/\"+this.value);'")
                ->lookupDB("Account Number", "name='AccountNumber' class='input req-string ui-autocomplete-input'", "index.php?option=com_xbank&task=transaction_cont.AccountNumber&format=raw",
                        array("a"=>"b"),
                        array("AccountNumber", "Name", "Balance", "Scheme"), "AccountNumber")
//                ->div("memberDetail", '', 'Member Details')
                ->text("Amount", "name='Amount' class='input req-numeric'")
                ->lookupDB("Amount Transferred to Account", "name='withdrawlFrom' class='input'", "index.php?option=com_xbank&task=transaction_cont.AmountTransferredtoAccount&format=raw", array("a"=>"b"), array("AccountNumber","MemberName"), "AccountNumber")
                ->textArea("Narration", "name='Narration'")
                ->confirmButton("Confirm", "Confirm Withdrawal", "index.php?option=com_xbank&task=transaction_cont.confirmWithdrawl&format=raw", true)
                ->submit('DO');
        $data['contents'] = $this->form->get();
        $this->jq->addInfo("Withdrawl", "");
        //$this->load->view('template', $data);
        JRequest::setVar('layout','withdrawl');
        $this->load->view('transaction.html', $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @return <type>
     * Confirms the withdrawal from an account
     * Checks the following conditions before actual withdrawal :
     * - whether account exists or not
     * - whether withdrawing amount is not null and is numeric
     * - whether account is active and not locked
     * - whether the scheme type supports withdrawal or not
     */
     function confirmWithdrawl() {
       
        $err=false;
        $msg="";
        $creditAc = "";
        if(inp("withdrawlFrom")){
            $creditAc = Account::getAccountForCurrentBranch(inp("withdrawlFrom"));
        }
        if($creditAc)
            $creditToAccount = inp("withdrawlFrom");
        else
            $creditToAccount = CASH_ACCOUNT;
        $ac = Account::getAccountForCurrentBranch(inp('AccountNumber'), false);
        if (!$ac || inp("Amount") == "" || !is_numeric(inp("Amount")) || ($ac->LockingStatus == 1 /*|| $ac->ActiveStatus == 0 */)) {
            $msg = "No Account found Or Amount not valid.. proceeding may generate error<br>falsefalse";
             echo $msg;
            return;
        }
        try {
                include(xBANKSCHEMEPATH."/".strtolower($ac->scheme->SchemeType) ."/".strtolower($ac->scheme->SchemeType). "withdrawlconfirm.php");
        } catch (Exception $e) {
            $msg = "Not the usual way to make account .. check the form again for errors<br>falsefalse";
        }

        if ($err) {
            $msg .="falsefalse";
           
        }
        $host=$_SERVER['HTTP_HOST'];
        if(file_exists("/var/www/xbank/administrator/components/com_xbank/signatures/sig_".$ac->member->id.".JPG"))
	            echo $msg . "<br/>Specimen Signature <img src='http://$host/xbank" . SIGNATURE_FILE_PATH . "sig_" . $ac->member->id . ".JPG' />";
	        else
	        	echo $msg . "<br/>Specimen Signature <img src='http://$host/xbank" . SIGNATURE_FILE_PATH . "sig_" . $ac->member->id . ".jpg' />";
        
         echo $msg;
    }


    /**
     *
     * @return <type>
     * Actual withdrawal is done
     * New voucher number is generated and based on the scheme type, withdrawal function is called
     */

    function DoWithdrawl() {
         
        try {
            $this->db->trans_begin();
            $this->jq->addInfo("Doing", inp('AccountNumber') . " to " . inp('Amount') . " has been performed");
            $ac = Account::getAccountForCurrentBranch(inp('AccountNumber'), false);
//	 		print_r($ac->toArray());
            $creditAc = "";
        if(inp("withdrawlFrom")){
            $creditAc = Account::getAccountForCurrentBranch(inp("withdrawlFrom"));
        }
        if($creditAc)
            $creditToAccount = inp("withdrawlFrom");
        else
            $creditToAccount = CASH_ACCOUNT;
            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
//                        $voucherNo=Transactions::getNewVoucherNumber();
            include(xBANKSCHEMEPATH."/".strtolower($ac->scheme->SchemeType) ."/".strtolower($ac->scheme->SchemeType). "withdrawlexecute.php");
            $this->db->trans_commit();
            log_message("error", __FILE__ . " " . __FUNCTION__ . "  Withdrawal an amount of " . inp("Amount") . " from account $ac->AccountNumber .Transaction done from  " . $this->input->ip_address());
            re("transaction_cont.withdrawl","Rs. ".inp("Amount")." has been withdrawn from account $ac->AccountNumber");
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
    }


    /**
     * Function generates a deposit <b>FORM</b>
     * Actual deposit is not done
     * Sends the link to {@link DoDeposit}
     */
    function deposit() {
 xDeveloperToolBars::onlyCancel("transaction_cont.index", "cancel", "Deposit Amount");
        $this->form->open('DepositForm', 'index.php?option=com_xbank&task=transaction_cont.DoDeposit')
                ->setColumns(2)
                ->lookupDB("Account Number", "name='AccountNumber' class='input req-string ui-autocomplete-input'", "index.php?option=com_xbank&task=transaction_cont.AccountNumber&format=raw",
                        array("a"=>"b"),
                        array("AccountNumber", "Name", "Balance", "Scheme"), "AccountNumber")
                ->text("Amount", "name='Amount' class='input req-string'")
                ->lookupDB("Account to Debit (Cash/Bank/Saving Account)", "name='AmountTo' class='input'", "index.php?option=com_xbank&task=transaction_cont.AmountTransferredtoAccount&format=raw", array("a"=>"b"),array("AccountNumber","MemberName"), "AccountNumber")
                ->textArea("Narration", "name='Narration'")
                ->hidden("","name='penalty'")
                ->text("", "name='penalty' ", "display:none")

                ->confirmButton("Confirm", "Confirm Deposit", "index.php?option=com_xbank&task=transaction_cont.confirmDeposit&format=raw",true)
                ->submit('DO');

        $data['contents'] = $this->form->get();
//         $this->jq->addInfo("deposit", "");
         JRequest::setVar('layout','deposit');
        $this->load->view('transaction.html', $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @return <type>
     * Confirms the deposit in an account
     * Checks the following conditions before actual deposit :
     * - whether account exists or not
     * - whether amount is not null and is numeric
     * - whether account is active and not locked
     * - whether the scheme type supports deposit or not
     *
     * STEPS TAKEN TO DEPOSIT EMI IN LOAN ACCOUNT
     * - Get the number of EMIs paid
     * - Retrieve the EMI amount
     * - calculate interest on EMI
     * - calculate the number of EMIs adjusting
     *
     * STEPS TAKEN TO DEPOSIT IN RD ACCOUNT
     * - calculate the number of premiums already paid
     * - Check whether account balance after depositing the amount does not exceed the required amount
     * - calculate the number of premiums adjusting
     */
     function confirmDeposit() {
        
//        $ac = Account::getAccountForCurrentBranch(inp('AccountNumber'));
        if(trim(inp('AccountNumber'))==''){
            echo "<h1>Account Number is must, cannot procced</h1> <div class='falsefalse'></div>";
            return;
        }
    	$ac = new Account();
        $ac->where("AccountNumber",inp('AccountNumber'))->get();
        $msg = "you may proceed";
        if (!$ac->exists() || inp("Amount") == "" || !is_numeric(inp("Amount")) || $ac->ActiveStatus == 0) 
            {
            $msg = "<h1>No Account found .. proceeding may generate error </h1><br>falsefalse";
            echo $msg;
            return;
        }

      
        $err = false;
        $msg = "";
       $debitAc = "";
        if(inp("AmountTo")){
            $debitAc = Account::getAccountForCurrentBranch(inp("AmountTo"));
        }
        if($debitAc)
            $debitToAccount = inp("AmountTo");
        else
            $debitToAccount = CASH_ACCOUNT;
        include(xBANKSCHEMEPATH."/".strtolower($ac->scheme->SchemeType) ."/".strtolower($ac->scheme->SchemeType). "depositconfirm.php");
        if (isset($err) AND $err) {
            $msg .= 'falsefalse';
            echo $msg;
        }
        else{
            $host=$_SERVER['HTTP_HOST'];
        	if(file_exists("/var/www/xbank/administrator/components/com_xbank/signatures/sig_".$ac->member->id.".JPG"))
	            echo $msg . "<br/>Specimen Signature <img src='http://$host/xbank" . SIGNATURE_FILE_PATH . "sig_" . $ac->member->id . ".JPG' />";
	        else
	        	echo $msg . "<br/>Specimen Signature <img src='http://$host/xbank" . SIGNATURE_FILE_PATH . "sig_" . $ac->member->id . ".jpg' />";
        }
    }



    /**
     *
     * @return <type>
     * Actual deposit is done
     * New voucher number is generated and based on the scheme type, deposit function is called
     */
     function DoDeposit() {
      
        try {
            $this->db->trans_begin();
            $this->jq->addInfo("Doing", inp('AccountNumber') . " to " . inp('Amount') . " has been performed");
 //           $ac = Account::getAccountForCurrentBranch(inp('AccountNumber'), false);
	    $ac = new Account();
            $ac->where("AccountNumber",inp('AccountNumber'))->get();
            
            if(inp("AmountTo"))
                $debitToAccount = inp("AmountTo");
            else
                $debitToAccount = CASH_ACCOUNT;

            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
            include(xBANKSCHEMEPATH."/".strtolower($ac->scheme->SchemeType) ."/".strtolower($ac->scheme->SchemeType). "depositexecute.php");
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
        re("transaction_cont.deposit","Rs. ".inp('Amount')." has been deposited in account ".inp('AccountNumber'));
    }


    /**
     *
     * @param <type> $ac
     * @param <type> $voucherNo
     * Function to withdraw from Bank type account
     * Credit bank's cash account and debit bank type account
     */
    function withdrawlFromBankTypeAccount($ac, $voucherNo) {
//        Staff::accessibleTo(POWER_USER);


//        $details = Accounts::updateInterest($ac);
        $creditAccounts = array(
            CASH_ACCOUNT => inp('Amount')
        );
        $debitAccounts = array(
            $ac->AccountNumber => inp('Amount')
        );

        Transaction::doTransaction($debitAccounts, $creditAccounts, "Amount withdrawl from Saving Account", TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL, $voucherNo);
        $msg = "Saving Account Withdrawl- Interest given " . $details['Interest'] . " on " . $details['AmountForInterest'] . " For " . $details['DateDifferance']['days_total'] . " days.";
        Log::write($msg, $ac->id);

        $msg = "Dear " . $ac->Member->Name . ", you have withdrawn an amount of Rs. " . inp("Amount") . " from your account $ac->AccountNumber on " . getNow("Y-m-d");
        $this->sendSMS($ac, $msg);
    }

    /**
     *
     * @param <type> $ac
     * @param <type> $voucherNo
     * Function to withdraw from Fixed type account
     */
    function withdrawlFromFixedTypeAccount($ac, $voucherNo) {

    }

    /**
     *
     * @param <type> $ac
     * @param <type> $voucherNo
     * Function to withdraw from Loan type account
     */
    function withdrawlFromLoanTypeAccount($ac, $voucherNo) {
    }

    /**
     *
     * @param <type> $ac
     * @param <type> $voucherNo
     * Function to withdraw from Recurring type account
     */
    function withdrawlFromRecurringTypeAccount($ac, $voucherNo) {
    }

    function withdrawlFromDDSTypeAccount($ac, $voucherNo) {
    }

    /**
     *
     * @param <type> $ac
     * @param <type> $voucherNo
     * Function to withdraw from CC type account
     *
     * STEPS
     * - update interest for CC account
     * - credit bank's cash account and debit CC account by amount withdrawn
     */
    function withdrawlFromCCTypeAccount($ac, $voucherNo) {
        $creditAccounts = array(
            CASH_ACCOUNT => inp('Amount')
        );
        $debitAccounts = array(
            $ac->AccountNumber => inp('Amount')
        );

        Transaction::doTransaction($debitAccounts, $creditAccounts, "Amount withdrawl from CC Account  $ac->AccountNumber", TRA_CC_ACCOUNT_AMOUNT_WITHDRAWL, $voucherNo);
        $msg = "CC Account Withdrawl- Interest given " . $details['Interest'] . " on " . $details['AmountForInterest'] . " For " . $details['DateDifferance']['days_total'] . " days.";
        Log::write($msg, $ac->id);
        $msg = "Dear " . $ac->Member->Name . ", you have withdrawn an amount of Rs. " . inp("Amount") . " from your account $ac->AccountNumber on " . getNow("Y-m-d");
        $this->sendSMS($ac, $msg);
    }

    /**
     * Function to load the JV <b>FORM</b>
     */
    function jv() {
//        Staff::accessibleTo(POWER_USER);
        xDeveloperToolBars::onlyCancel("transaction_cont.index", "cancel", "JV Transactions");
        $tt=new Transaction_type();
        $tt->get();
        $data["formcomponents"] = $this->load->library("formcomponents");
        $data["transaction_types"] = $tt;
        JRequest::setVar("layout","jv");
        $this->load->view("transaction.html", $data);
        $this->jq->getHeader();
    }

    /**
     * Function checks the JV based on the account number
     */
    function checkJV() {
//        Staff::accessibleTo(POWER_USER);

        $msg = "";
        $err = false;
        $cramount = 0;
        $dramount = 0;
        $countcr = 0;
        $countdr = 0;

        echo "<h2>". inp('transaction_type') ."</H2>";
        if(inp('zerotime'))
            echo "<h2> Transaction for date: ".getNow("Y-m-d 00:00:00") ."</h2>";
        else
            echo "<h2> Transaction for date: ".getNow() ."</h2>";


        for ($i = 1; $i <= 20; $i++) {

            if (inp("DRAccount_$i") != "") {
//                $account = Doctrine::getTable("Accounts")->findOneByAccountnumberAndBranch_id(inp("DRAccount_$i"),Branch::getCurrentBranch()->id);
                $account = new Account();
                $account->where("AccountNumber",inp("DRAccount_$i"));
                $account->where("branch_id",Branch::getCurrentBranch()->id)->get();
                if ($account->result_count() == 0) {
                    $msg .="<h2>No Such Account found - " . inp("DRAccount_$i") . "</h2><br/>";
                    $err = true;
                } else {
                    if (inp("dramount_$i") == "") {
                        $msg .="Amount is empty for " . inp("DRAccount_$i") . "<br/>";
                        $err = true;
                    }
                }
                $dramount += inp("dramount_$i");
                $countdr = $i;
            }
            if (inp("CRAccount_$i") != "") {
//                $account = Doctrine::getTable("Accounts")->findOneByAccountnumberAndBranch_id(inp("CRAccount_$i"),Branch::getCurrentBranch()->id);
                $account = new Account();
                $account->where("AccountNumber",inp("CRAccount_$i"));
                $account->where("branch_id",Branch::getCurrentBranch()->id)->get();
                if ($account->result_count() == 0) {
                    $msg .="<h2>No Such Account found - " . inp("CRAccount_$i") . "</h2><br/>";
                    $err = true;
                } else {
                    if (inp("cramount_$i") == "") {
                        $msg .="Amount is empty for " . inp("CRAccount_$i") . "<br/>";
                        $err = true;
                    }
                }
                $cramount += inp("cramount_$i");
                $countcr = $i;
            }

            if ($err) {
                $msg .="falsefalse";
            }
        }

//            if(inp("dramount_1")!=$cramount || inp("cramount_1")!=$dramount){
//                    $msg .="<h3>JV entries support only <br><b>one debit and one credit entry</b> OR<br><b>one debit and many credit entries</b> OR<br><b>many debit and one credit entries.</b><br></h3>false";
//                    $err=true;
//            }

        if (($countcr > 1 && $countdr > 1) || $cramount != $dramount) {
            $msg .="<h3>JV entries support only <br><b>one debit and one credit entry</b> OR<br><b>one debit and many credit entries</b> OR<br><b>many debit and one credit entries.</b><br></h3>falsefalse";
            $err = true;
        }
        echo $msg;
    }

    /**
     *
     * @return <type>
     * Actual JV is run here
     */
    function doJV() {
//        Staff::accessibleTo(POWER_USER);

        try {
             $this->db->trans_begin();
            $debitAccount = array();
            $creditAccount = array();

//            $voucherNo=Transactions::getNewVoucherNumber();

            for ($i = 1; $i <= 20; $i++) {

                if (inp("DRAccount_$i") != "") {
                    $debitAccount +=array(inp("DRAccount_$i") => inp("dramount_$i"));
//                    $this->JVDebitAccount(inp("DRAccount_$i"), inp("dramount_$i"));
                }
                if (inp("CRAccount_$i") != "") {
                    $creditAccount +=array(inp("CRAccount_$i") => inp("cramount_$i"));
//                    $this->JVCreditAccount(inp("CRAccount_$i"), inp("cramount_$i"));
                }
            }

            if(inp('transaction_type')){
                $tr_type=new Transaction_type();
                $tr_type->where('Transaction',inp('transaction_type'));
                $tr_type->get();

                $tr_type=inp('transaction_type');
                $default_narration = $tr_type->Default_Narration. " ";
            }else{
                $tr_type = TRA_JV_ENTRY;
                $default_narration = "";
            }
            $referanceAccount=null;
            if(inp('ReferanceAccount')) {
                $at=new Account();
                $at->where('AccountNumber',inp('ReferanceAccount'));
                $at->get(); 
                $referanceAccount = $at->id;
            }
            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $referanceAccount);
            if(inp('zerotime'))
                $transactionTime = getNow("Y-m-d 00:00:00");
            else
                $transactionTime = getNow();

            Transaction::doTransaction($debitAccount, $creditAccount, $default_narration . inp("Naration"), $tr_type, $voucherNo, $transactionTime);
            
       $this->db->trans_commit();
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->db->trans_rollback();
            return;
        }
        re("transaction_cont.jv","Transaction done successfully");
    }

    function JVDebitAccount($acc, $amt) {
        $conn = Doctrine_Manager::connection();
        try {
            $conn->beginTransaction();
            $ac = Accounts::getAccountForCurrentBranch($acc, false);
            switch ($ac->scheme->SchemeType) {
                case ACCOUNT_TYPE_DEFAULT:
                    break;
                case ACCOUNT_TYPE_BANK:
//                    Accounts::updateInterest($ac);
                    break;
                case ACCOUNT_TYPE_FIXED:
//                    throw new Exception("Fixed Deposit Account cannot be debited");
                    break;
                case ACCOUNT_TYPE_LOAN:
//                    throw new Exception("Loan Account cannot be debited");
                    break;
                case ACCOUNT_TYPE_RECURRING:
//                    throw new Exception("Recurring Account cannot be debited");
                    break;
                case ACCOUNT_TYPE_DDS:
//                    throw new Exception("DDS Account cannot be debited");
                    break;
                case ACCOUNT_TYPE_CC:
//                    Accounts::updateInterestForCC($ac);
                    break;
            }

//	 		echo "code run";
            $conn->commit();
//	 		$this->load->view('template');
        } catch (Doctrine_Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
            return;
        }
    }

    function JVCreditAccount($acc, $amt) {
        $conn = Doctrine_Manager::connection();
        try {
            $conn->beginTransaction();
            $ac = Accounts::getAccountForCurrentBranch($acc, false);
            switch ($ac->Schemes->SchemeType) {
                case ACCOUNT_TYPE_DEFAULT:
                    break;
                case ACCOUNT_TYPE_BANK:
//                    Accounts::updateInterest($ac);
                    break;
                case ACCOUNT_TYPE_FIXED:
//                    throw new Exception("Fixed Deposit Account cannot be credited");
                    break;
                case ACCOUNT_TYPE_LOAN:
//                    $this->creditLoanAccount($ac,$amt);
                    break;
                case ACCOUNT_TYPE_RECURRING:
                	throw new Exception("Recurring Account cannot be credited");
//                    $this->payRDPremiums($ac, $amt);
                    break;
                case ACCOUNT_TYPE_DDS:
                    break;
                case ACCOUNT_TYPE_CC:
//                    Accounts::updateInterestForCC($ac);
                    break;
            }

//	 		echo "code run";
            $conn->commit();
//	 		$this->load->view('template');
        } catch (Doctrine_Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
            return;
        }
    }

    function payRDPremiums($ac, $amt) {
        // $ac=Accounts::getAccountForCurrentBranch($acc,false);
        $PaidPremiums = Doctrine::getTable("Premiums")->createQuery()
                        ->where("accounts_id = ? AND Paid <> 0 AND Skipped = 0", array($ac->id))->execute();
//                            ->findByAccounts_idAndPaidAndSkipped($ac->id,"1","0")->count();
        $PaidPremiums = $PaidPremiums->count();
        $premium = Doctrine::getTable("Premiums")->findOneByAccounts_id($ac->id);
        $p = $premium->Amount;
        $PremiumAmountAdjusted = $PaidPremiums * $p;
        $AmountForPremiums = $ac->CurrentBalanceCr + $amt - $PremiumAmountAdjusted;


        $premiumsSubmited = (int) ($AmountForPremiums / $p);

        /* adjusting the remaining premimum for the RD account on the money deposited
         * and the date of premimum deposited should be the current date
         */

        if ($premiumsSubmited > 0) {
            $q = Doctrine_Query::create()
                            ->select("p.*")
                            ->from("Premiums p")
                            ->where("accounts_id=? and Paid = 0 and Skipped = 0", array($ac->id))
                            ->orderBy("id")
                            ->limit($premiumsSubmited);

            $result = $q->execute();
            foreach ($result as $r) {
                $r->PaidOn = getNow();
                $r->Paid = ++$PaidPremiums;
                $r->save();
            }
        }
        $voucherNo = array('voucherNo' => Transactions::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
//            $voucherNo=Transactions::getNewVoucherNumber();
        if(!SET_COMMISSIONS_IN_MONTHLY)
            Premiums::setCommissions($ac, $voucherNo);
    }

    /**
     * Function generates <b>FORM</b> to do forClose of a loan account
     * Sends the link to {@link doForClose}
     */
    function forClose() {
        xDeveloperToolBars::onlyCancel("transaction_cont.index", "Cancel", "Do For close");
        $this->load->library('form');
        $this->form->open('ForeClauseForm', 'index.php?option=com_xbank&task=transaction_cont.doForClose')
                ->setColumns(2)
                ->lookupDB("Account Number", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=transaction_cont.accountToForClose&format=raw",
                        array("a" => "b"),
                        array("AccountNumber", "Name", "Balance", "Scheme"), "AccountNumber")
                ->text("Amount", "name='Amount' class='input req-string'")
                ->text("Discount", "name='Discount' class='input'")
                ->confirmButton("Confirm", "Do For Close", "index.php?option=com_xbank&task=transaction_cont.confirmForClose&format=raw", true)
                ->submit('DO');

        $data['contents'] =$this->form->get();
        $this->load->view('utility.html',$data);
        $this->jq->getHeader();
    }
    
    
    function accountToForClose(){
        $list = array();
        $q="select a.AccountNumber, m.Name AS Name, a.CurrentBalanceDr - a.CurrentBalanceCr AS Balance, s.Name AS Scheme
            from jos_xaccounts a
            join jos_xmember m on a.member_id=m.id
            left join jos_xschemes s on a.schemes_id=s.id
            join jos_xbranch b on a.branch_id = b.id
            where a.AccountNumber Like '%".$this->input->post("term")."%' and s.SchemeType='" . ACCOUNT_TYPE_LOAN . "' and b.id=" . Branch::getCurrentBranch()->id."
            limit 10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber,'Name'=>$dd->Name,'Balance' => $dd->Balance,'Scheme'=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }
    

    /**
     *
     * @return <type>
     * Confirms the forClose of loan account
     *
     * STEPS
     * - Check whether the account exists and the amount deposited is numeric
     * - calculate the number of Unpaid Premiums till date
     * - calculate sum of Unpaid Premiums till date
     * - calculate the interest and retrieve the penalty
     * - calculate the number of unpaid premiums after date
     * - calculate the forclose amount by the formula (Dr + (interest * Unpaid EMIs till date) ) - Cr  + penalty + (interest * unpaid EMIs after date * 25/100)
     */
    function confirmForClose() {
//        Staff::accessibleTo(POWER_USER);

        $ac = Account::getAccountForCurrentBranch(inp('AccountNumber'));
        $msg = "";
        if (!$ac || inp("Amount") == "" || !is_numeric(inp("Amount"))) {
            $msg = "<h1>No Account found .. proceeding may generate error </h1><br>falsefalse";
            echo $msg;
            return;
        }

        $a=new Account();
        $a->select('*, id as PaneltyDUE, id as OtherCharges');
        $a->where('AccountNumber',inp('AccountNumber'));
        $a->get();

        if($a->ActiveStatus==0){
            echo "<h3>This account is not activated</h3>";
            return;
        }

        $loanAmount=$ac->RdAmount;

        $a->transactions->select_func('sum','[amountDr]','total_interest')->where('transaction_type_id',28)->get();

        $interestGiven = $a->transactions->total_interest;

        $no_of_emis=$a->premiums->count();
        $emi_amount=$a->premiums->get()->Amount;

        $forCloseCharge= round((($no_of_emis * $emi_amount) - ($loanAmount + $interestGiven))*25.0/100,0);

        
        $a->premiums->select_func('max','[DueDate]','last_emi_date')->get();
        $currentDr= $a->getOpeningBalance($a->premiums->last_emi_date,null,'DR');

        $overcharge=0;
        if(getNow('Y-m-d') > $a->premiums->last_emi_date){
            // echo $currentDr . "<br/>";
            $scheme_interest = $a->scheme->Interest;
            // echo $scheme_interest . "<br/>";
            $d=my_date_diff(getNow('Y-m-d'),$a->premiums->last_emi_date);
            // print_r($d);
            $overcharge = round($currentDr * $scheme_interest / 36500 * $d['days_total'],0);
            // return;
        }

        echo "<h3>Loan Amount :".$loanAmount . "</h3><br/>";
        echo "<h3>Monthly Interest : + ".$interestGiven . "</h3><br/>";
        echo "<h3>Total Panelty : + ".$a->PaneltyDUE . "</h3><br/>";
        echo "<h3>Legal / Coveyance / Insurance : + ".$a->OtherCharges . "</h3><br/>";
        echo "<h3>ForClose Charge : + ".$forCloseCharge . "</h3><br/>";
        echo "<h3>Time OverCharge : + ".$overcharge."</h3><br/>";
        echo "<h3>Toal Amount Deposited : - ".$a->CurrentBalanceCr . "</h3><br/>";
        $forCloseAmount = ($loanAmount + $interestGiven + ($a->PaneltyDUE) + $a->OtherCharges + $overcharge+ $forCloseCharge - ($a->CurrentBalanceCr));
        echo "<h3>ForClose Amount : = ".$forCloseAmount. "</h3><br/>";
        echo "<h3>Documents : = ".$a->getDocuments(). "</h3><br/>";

        return;

        
    }

    /**
     *
     * @return <type>
     * Actual forClose is done by calling the function depositInForCloseAccount
     */
    function doForClose() {
//        Staff::accessibleTo(POWER_USER);
        // re("transaction_cont.forClose","For Closed not implemented...");

//        $conn = Doctrine_Manager::connection();
        try {
            $this->db->trans_begin();
//            $this->jq->addInfo("Doing", inp('AccountNumber') . " to " . inp('Amount') . " has been performed");
            $ac = Account::getAccountForCurrentBranch(inp('AccountNumber'));
            
//                        $voucherNo=Transactions::getNewVoucherNumber();

            $this->depositInForCloseAccount($ac);

            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
//        $this->jq->addInfo("Doing", "Amount has been deposited");
        re("transaction_cont.forClose",inp('AccountNumber')." For Closed");
    }

    /**
     *
     * @param <type> $ac
     * @param <type> $voucherNo
     * ForClose amount is deposited by the function
     * Premiums are adjusted and paid
     */
    function depositInForCloseAccount($ac, $voucherNo) {

        $a=new Account();
        $a->select('*, id as PaneltyDUE, id as OtherCharges');
        $a->where('AccountNumber',$ac->AccountNumber);
        $a->get();

        if($a->ActiveStatus==0){
            echo "<h3>This account is not activated</h3>";
            re("transaction_cont.forClose",$ac->AccountNumber." is Not activated");
        }

        $loanAmount=$ac->RdAmount;

        $a->transactions->select_func('sum','[amountDr]','total_interest')->where('transaction_type_id',28)->get();

        $interestGiven = $a->transactions->total_interest;

        $no_of_emis=$a->premiums->count();
        $emi_amount=$a->premiums->get()->Amount;

        $forCloseCharge= round((($no_of_emis * $emi_amount) - ($loanAmount + $interestGiven))*25.0/100,0);

        
        $a->premiums->select_func('max','[DueDate]','last_emi_date')->get();
        $currentDr= $a->getOpeningBalance($a->premiums->last_emi_date,null,'DR');

        $overcharge=0;
        if(getNow('Y-m-d') > $a->premiums->last_emi_date){
            // echo $currentDr . "<br/>";
            $scheme_interest = $a->scheme->Interest;
            // echo $scheme_interest . "<br/>";
            $d=my_date_diff(getNow('Y-m-d'),$a->premiums->last_emi_date);
            // print_r($d);
            $overcharge = round($currentDr * $scheme_interest / 36500 * $d['days_total'],0);
            // return;
        }

        $forCloseAmount = ($loanAmount + $interestGiven + ($a->PaneltyDUE) + $a->OtherCharges + $overcharge+ $forCloseCharge - ($a->CurrentBalanceCr));

        $debitAccount_10 = array(
            CASH_ACCOUNT => ($forCloseAmount - inp("Discount")),
        );
        $creditAccount_10 = array(
            $ac->AccountNumber => ($forCloseAmount - inp("Discount")),
        );
        // TODO Docuemnets in narration
        $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
        Transaction::doTransaction($debitAccount_10, $creditAccount_10, "Being For Close Amount deposited & Documents Given ". $ac->getDocuments(), TRA_FOR_CLOSE_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);

        $debitAccount_20 = array(
            $ac->AccountNumber => ($forCloseCharge),
        );
        $creditAccount_20 = array(
            FOR_CLOSE_ACCOUNT_ON.$ac->Schemes->Name => ($forCloseCharge),
        );
        // TODO Docuemnets in narration
        $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
        Transaction::doTransaction($debitAccount_20, $creditAccount_20, "Being For Close Charge Debit ", TRA_FOR_CLOSE_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);

        if($overcharge != 0){
            $debitAccount_30 = array(
                $ac->AccountNumber => $overcharge
            );
            $creditAccount_30 = array(
               INTEREST_RECEIVED_ON.$ac->Schemes->Name => $overcharge
            );
            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
            Transaction::doTransaction($debitAccount_30, $creditAccount_30, "Being Time Over Charge Debit", TRA_FOR_CLOSE_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);            
        }

        if(inp("Discount")){
            $debitAccount_40 = array(Branch::getCurrentBranch()->Code.SP."Discount Paid" => inp("Discount"));
            $creditAccount_40 = array( $ac->AccountNumber => inp("Discount"));
            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
            Transaction::doTransaction($debitAccount_40, $creditAccount_40, "Being Discount Paid", TRA_FOR_CLOSE_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
        }

        $q = "update `jos_xpremiums` set `PaidOn` = '" . getNow() . "', Paid=1 where Paid = 0 and `accounts_id` =" . $ac->id;
        executeQuery($q);

        $query = "update `jos_xaccounts` set `CurrentInterest` = 0, ActiveStatus=0, affectsBalanceSheet=1 where `id` =" . $ac->id;
        executeQuery($query);

    }

    function sendSMS($ac, $msg) {
        return;
        $mobile = substr($ac->Member->PhoneNos, 0, 10);
        $b = Branch::getCurrentBranch();
        if (is_numeric($mobile) && strlen($mobile) == 10 && $b->SendSMS == 1) {
            $sms = new sms();
            $sms->sendsms($mobile, $msg);
        }
    }


    function deleteTransactionForm(){
//        Staff::accessibleTo(ADMIN);

        $this->load->library('form');
        $this->form->open("TransactionDelete", 'index.php?option=com_xbank&task=transaction_cont.showTransactions')
                ->setColumns(2)
                ->datebox("Enter date of transaction","name='transactionDate' class='input'")
                ->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                ->submit("Go");
        $data['contents'] = $this->form->get();
        $this->load->view("template", $data);
    }

    function showTransactions(){
        $transaction = $this->db->query("select * from transactions where created_at like '".inp("transactionDate")."%' and branch_id = ".inp("BranchId"))->result();
        $data['transaction'] = $transaction;
        JRequest::setVar("layout","transactionview");
        $this->load->view('transaction.html', $data);
        
    }

    function deleteTransaction($voucherno, $branchid){
        $closing = Doctrine::getTable("Closings")->findOneByBranch_id($branchid);
        $transactions = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, $branchid);
         $conn = Doctrine_Manager::connection();
        try{
            $conn->beginTransaction();

            foreach ($transactions as $t) {
                $acc = Doctrine::getTable("Accounts")->find($t->accounts_id);
                include(xBANKSCHEMEPATH."/".strtolower($acc->Schemes->SchemeType) ."/".strtolower($acc->Schemes->SchemeType). "transactionbeforedeleted.php");
                include(xBANKSCHEMEPATH."/".strtolower($acc->Schemes->SchemeType) ."/".strtolower($acc->Schemes->SchemeType). "transactionafterdeleted.php");
                $q = "delete from transactions where id = $t->id";
                executeQuery($q);
            }
            $conn->commit();
            echo "Done";
        } catch (Doctrine_Exception $e) {
            $conn->rollback();
            echo $e->getMessage();
            return;
        }
    }
    
   function AccountNumber() {
        $list = array();     
        $q="select a.AccountNumber,IF(a.CurrentBalanceCr - a.CurrentBalanceDr > 0,a.CurrentBalanceCr - a.CurrentBalanceDr,'') AS Balance,m.Name As Name,m.PanNo As PanNo,s.Name As Scheme from jos_xaccounts a join jos_xmember m on a.member_id = m.id left join jos_xschemes s on a.schemes_id=s.id left join jos_xbranch b on a.branch_id=b.id where a.AccountNumber like '%" . $this->input->post("term") . "%'  and (a.LockingStatus<>1 and a.ActiveStatus<>0) or (m.`Name` like '%" . $this->input->post("term") . "%' ) limit 10 ";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber,'Name'=>$dd->Name,'Balance'=>$dd->Balance,'Scheme'=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }
    
   function AmountTransferredtoAccount() {
        $list = array();
        $q="select a.*,s.`Name`,m.`Name` as MemberName from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id join jos_xbranch b on a.branch_id=b.id join jos_xmember m on a.member_id=m.id where a.AccountNumber like '%" . $this->input->post("term") . "%' and (s.`Name`='Bank Accounts' or s.`Name`='Cash Account' or s.`Name` = 'Saving Account') and b.id like '".Branch::getCurrentBranch()->id."' and (a.LockingStatus<>1 and a.ActiveStatus<>0) limit 10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber,'MemberName'=>$dd->MemberName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }


    function lookupForJV(){
        $list = array();
        $q="select a.AccountNumber, m.Name AS Name, m.PanNo AS PanNo, s.Name AS Scheme from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id join jos_xbranch b on a.branch_id=b.id join jos_xmember m on a.member_id=m.id where ((a.AccountNumber like '%" . $this->input->post("term") . "%' and b.id like '".Branch::getCurrentBranch()->id."') or (m.`Name` like '%" . $this->input->post("term") . "%' and m.branch_id like '".Branch::getCurrentBranch()->id."')) and (a.LockingStatus<>1 and a.ActiveStatus<>0) limit 10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber,'Name'=>$dd->Name,'Scheme'=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }
}

?>