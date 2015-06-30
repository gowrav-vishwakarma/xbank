<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * mod_accounts/account_cont Controller : module to perform various Accounts based function
 * 
 * This Module is linked with Accounts Menu, by cliecking this you can manage all your Accounts realated functions
 * 
 * 
 * @todo Dashboard Design :: what to show
 * 
 * accounts_cont class extended from CI Controller
 * 
 * @package    xBank
 * @subpackage xModules
 * @author     Gowrav  <GowravVishwakarma@gmail.com>
 * @version    1.0.0
 * @category Timestampable
 */
class accounts_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * default function to called when {@link accounts_cont accounts_cont} is called
     * @todo design the dashboard
     */
    function index() {
        xDeveloperToolBars::getAccountsManagementToolBar();
        $where = (JFactory::getUser()->username == "admin" ? "" : " and a.branch_id=".Branch::getCurrentBranch()->id );
        $data['records'] = $this->db->query("select s.Name as `Name`,count(a.AccountNumber) as Accounts, s.id as ID from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 $where group By s.Name")->result();
        $this->load->view("accounts.html", $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $id
     * function to view the Cr and Dr balance of accounts under scheme with id as $id
     */
    function accountForScheme() {
        xDeveloperToolBars::onlyCancel("accounts_cont.index", "cancel", "Check your Accounts here");
        $id = JRequest::getVar("id");


        $a= new Account();
        $a->select('*, id as ActualCurrentBalance');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
        $a->include_related('scheme','Name');
        $a->where('schemes_id',inp('id'));
        //$a->where('DefaultAC',0);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->get();
        

//        $a->check_last_query();
        $data['report'] = getReporttable($a,             //model
                array("AccountNumber","Name","Phone Nos","Balance"),       //heads
                array("AccountNumber",'member_Name',"member_PhoneNos",
"~(ABS(#CurrentBalanceDr - #CurrentBalanceCr)).
      ((((#CurrentBalanceDr - #CurrentBalanceCr)) < 0 )? 
' <font color=green>CR</font>' : ' <font color=red>DR</font>')"),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();

        return;

        $data['accounts'] = $this->db->query("select a.AccountNumber as AccountNo,a.CurrentBalanceCr as Cr, a.CurrentBalanceDr as Dr from jos_xaccounts a  where a.schemes_id =" . $id . " and a.branch_id=" . Branch::getCurrentBranch()->id)->result();
//        $data['contents'] = $this->load->view("accdashboard", $data);
        JRequest::setVar("layout", "accountsdashboard");
        $this->load->view("accounts.html", $data);
        $this->jq->getHeader();
    }

    /**
     * function to create new Account <B>FORM</b> no new account is actually created by this function just the form
     * sends data to {@link NewAccountCreate NewAccountCreate function}
     *
     * STEPS
     * - gets all schemes by {@link Schemes::getAllSchemesForCurrentBranch}
     * {@source 1 5}
     * - generate Form
     *
     */
    function NewAccountForm() {
        xDeveloperToolBars::onlyCancel("accounts_cont.index", "cancel", "Open Accounts here");
        global $com_params;

        $b = Branch::getCurrentBranch();
        $branchCode = $b->Code;

        $this->jq->addInfo("Agent", "Default branch Agent");
        $defaultAgent = $this->jq->flashMessages(true);

        $this->jq->addInfo("Member", "Member Details");
        $member = $this->jq->flashMessages(true);

        $this->jq->addInfo("Accounts", "Accounts Details");
        $accounts = $this->jq->flashMessages(true);

        $documents = $this->db->query("Select * from jos_xdocuments")->result();
        $i = 1;

        $schemeTypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
        $i = 0;


        foreach ($schemeTypes as $st) {
            include(xBANKSCHEMEPATH . "/" . strtolower($st) . "/" . strtolower($st) . "accountopenform.php");

//            echo xBANKSCHEMEPATH . "/" . strtolower($st) . "/" . strtolower($st) . "accountopenform.php"."<br>";
        }


        $data['tabs'] = "<h3 align='center'>Create Account Here and Then Edit from 'Account Search' to set/update Opening Balances</h3>";
        $data['tabs'] .= $this->jq->getTab(1);
        JRequest::setVar("layout", "accountopenform");
        $this->load->view('accounts.html', $data);
        $this->jq->getHeader();
    }

    function confirmAccountCreateForm() {

//        Staff::accessibleTo(POWER_USER);
        $err = false;
        $msg = "";
        $debitAccount = array();
        $creditAccount = array();
        try {
            include(xBANKSCHEMEPATH . "/" . strtolower(inp("CurrentScheme")) . "/" . strtolower(inp("CurrentScheme")) . "accountconfirm.php");
        } catch (Exception $e) {
            $msg = "Not the usual way to make account .. check the form again for errors<br>falsefalse";
        }
        echo $msg;
    }

    /**
     * Actually creates an Account
     *
     * takes data from NewAccountForm function and performs following action
     *  - get the given agent if not found then getDefault Agent for CURRENT BRANCH AGENT
     *  - set all input data
     *  - save account
     *  - these accounts are not Default so <b>DefaultAC=0</b>
     *
     * @todo what about RD AND Daily deposit type accounts
     */
    function NewAccountCreate() {
        $debitAccount = array();
        $creditAccount = array();
        try {
            $this->db->trans_begin();
            $a = inp('Agents_Id');



            $Acc = new Account();
            $Acc->member_id = inp('UserID');
            $Acc->schemes_id = inp('AccountType');

            $Agent = new Agent($a);
//            $Agent->where('member_id', $a)->get();
            if ($Agent->result_count() == 0) {
                $Agent = null; //Branch::getDefaultAgent();
            }


            $Acc->agents_id = $Agent->id; // TODO - Can any other Agent give you id or only your own agent can give you this ...
            $Acc->staff_id = Staff::getCurrentStaff()->id; //Current_Staff::staff()->id;
            $Acc->branch_id = Branch::getCurrentBranch()->id;
            $Acc->ActiveStatus = inp('ActiveStatus');
            $Acc->Nominee = inp('Nominee');  // IMP : used as guarantor for loan accounts
            $Acc->NomineeAge = inp('NomineeAge');
            $Acc->RelationWithNominee = inp('RelationWithNominee');
            $Acc->MinorNomineeDOB =  inp('MinorNomineeDOB') ;
            $Acc->MinorNomineeParentName = inp('MinorNomineeParentName');   // IMP : used as guarantor Address for loan accounts
            $Acc->ModeOfOperation = (inp('ModeOfOperation') == "") ? "Self" : inp('ModeOfOperation');
            $Acc->AccountNumber = inp('AccountNumber');
            $Acc->RdAmount = inp("rdamount");
            $Acc->DefaultAC = '0';
            $Acc->LastCurrentInterestUpdatedAt = getNow();
            $Acc->created_at = getNow();
            if(inp('AccountDisplayName'))
                $Acc->AccountDisplayName = inp('AccountDisplayName');
            $Acc->save();
            $x = $Acc;
            // echo $x->id;
            $Ac = new Account($x->id);
            // echo $Ac->scheme->SchemeType;

            switch ($Ac->scheme->SchemeType) {
                case ACCOUNT_TYPE_DEFAULT:

                    break;
                case ACCOUNT_TYPE_BANK:
                    $Ac->RdAmount = inp("rdamount");
                    $Ac->save();
                    break;
                case ACCOUNT_TYPE_FIXED:
                    $Ac->RdAmount = inp("initialAmount");

                    $sc = Scheme::getScheme(inp("AccountType"));
                    if ($sc->InterestToAnotherAccount == 1) {
                        $iac = Account::getAccountForCurrentBranch(inp("InterestTo"));
                        $Ac->InterestToAccount = $iac->id;
                    }
                    $Ac->save();
                    break;
                case ACCOUNT_TYPE_LOAN:
                    $Ac->dealer_id = inp('Dealer');
                    $accountAmt = LOAN_AMOUNT;
                    $Ac->$accountAmt = inp('initialAmount');
                    $Ac->LoanInsurranceDate = inp("LoanInsurranceDate");
//                  $Ac->Nominee = inp('Nominee');  // IMP : used as guarantor for loan accounts
//            	    $Ac->RelationWithNominee = inp('RelationWithNominee');  // IMP : Used as Gaurantor Phone nos. for loan accounts
//            	    $Ac->MinorNomineeParentName = inp('MinorNomineeParentName');   // IMP : used as guarantor Address for loan accounts
                    if (inp("LoanAgSecurity") == 1) {
                        $s = inp('SecurityAccount');
                        $account = new Account();
                        $account->where('id', $s)->get();
                        //$account = Doctrine::getTable("Accounts")->findOneById(inp("SecurityAccount"));
                        $account->LockingStatus = true;
                        $account->save();
                        $Ac->LoanAgainstAccount = $account->id;
                    }
                    $Ac->save();                    
                    break;
                case ACCOUNT_TYPE_RECURRING:
                    $Ac->RdAmount = inp("rdamount");
                    $Ac->collector_id = inp("CollectorID");
                    $Ac->CollectorAccountNumber = inp("CollectorAccountNumber");
                    $Ac->save();
                    break;
                case ACCOUNT_TYPE_DDS:
                    $Ac->RdAmount = inp("rdamount");
                    break;
                case ACCOUNT_TYPE_CC:
                    $Ac->{CC_AMOUNT} = inp("initialAmount");
                    $Ac->save();
                    break;
            }

            // CHECK FOR JOINT ACCOUNT ENTRIES
            if(inp('ModeOfOperation') != 'Self'){
                    $Ac->addMember(inp('UserID_2'));
                    $Ac->addMember(inp('UserID_3'));
                    $Ac->addMember(inp('UserID_4'));
                    $Ac->addMember(inp('UserID_5'));
                    // throw new Exception("Error Processing Request", 1);
            }


            log_message('error', __FILE__ . " " . __FUNCTION__ . " $Ac->AccountNumber with id $Ac->id created from " . $this->input->ip_address());

            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $Ac->id);
            $Ac = new Account($Ac->id);



// NOOOO  COMMISSSIONNNN   FROM 2015 1st April



//			Commission transfer		
//             if (($Ac->scheme->AccountOpenningCommission != "" OR $Ac->scheme->AccountOpenningCommission != null) AND ($Ac->agents_id != null OR $Ac->agents_id != 0)) {
//                 switch ($Ac->scheme->SchemeType) {
//                     case ACCOUNT_TYPE_DEFAULT:

//                         break;
//                     case ACCOUNT_TYPE_BANK:

//                         // GIVES TOTAL COMMISSION AMOUNT....THEREAFTER DISTRIBUTE IT TO ALL LEVELS
// //                        $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         break;
//                     case ACCOUNT_TYPE_FIXED:
//                         $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         $commissionAmount = $commissionAmount * inp("initialAmount") / 100.00;
//                         break;
//                     case ACCOUNT_TYPE_LOAN:
//                         $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         break;
//                     case ACCOUNT_TYPE_RECURRING:
//                         $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         $commissionAmount = $commissionAmount * inp("initialAmount") / 100.00;
//                         break;
//                     case ACCOUNT_TYPE_DDS:
//                         $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         break;

//                     case ACCOUNT_TYPE_DHANSANCHAYA:
//                         $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         break;

//                     case ACCOUNT_TYPE_MONEYBACK:
//                         $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
//                         break;
//                 }
//                 $ag = new xConfig("agent");

//                 $Agent = new Agent($a);
//                 $agentCount = $Agent->result_count();
//                 if ($agentCount == 0) {
//                     $Agent = null; //Branch::getDefaultAgent();
//                 } else {

//                     $levels = $ag->getKey("number_of_agent_levels");

//                     if ($levels > 1 && !$ag->getKey("manually_promote_agent"))
//                         $Agent->updateAncestors();
//                     else {
//                         $agentAncestor = $Agent;
//                         while ($agentAncestor->id) {
//                             $agentAncestor->BusinessCreditPoints = $agentAncestor->BusinessCreditPoints + $Ac->scheme->SchemePoints / 100 * $Ac->RdAmount;
//                             $agentAncestor->CumulativeBusinessCreditPoints += $Ac->scheme->SchemePoints / 100 * $Ac->RdAmount;
//                             $agentAncestor->save();
//                             $agentAncestor = $agentAncestor->sponsor;
//                         }
//                     }
//                 }

//                 $agentSponsorCommission = array();
//                 if ($agentCount && $commissionAmount != 0) {
//                     $s = $Agent->AccountNumber;
//                     $agents = new Account();
//                     $agents->where('AccountNumber', $s)->get();
//                     // $agents = Doctrine::getTable("Accounts")->findOneByAccountNumber($Agent->AccountNumber);

//                     $totalCommission = $commissionAmount;
//                     $i = 0;
//                     do {
//                         $i = $i + 1;
//                         $agentSponsorCommission = json_decode($Ac->scheme->AgentSponsorCommission, true);
//                         if ($ag->getKey("number_of_agent_levels") > 0) {
//                             $commissionAmount = 0;
//                             for (; $i <= $Agent->Rank; $i++) {
// //                                    $agentSponsorCommission = $agentSponsorCommission[$i];
//                                 $commissionAmount += ( getComission($agentSponsorCommission[$i], OPENNING_COMMISSION) / 100 * $totalCommission);
//                             }
//                             $i = $Agent->Rank;
//                         } else {
//                             $commissionAmount = $commissionAmount;
//                         }





// //                        if ($Agent->member->branch_id != Branch::getCurrentBranch()->id) {
// //                            $s = $Agent->member->branch_id;
// //                            $otherbranch = new Branch();
// //                            $otherbranch->where('id', $s)->get();
// 			$agentAccBranch = new Account();
//                         $agentAccBranch->where("AccountNumber",$Agent->AccountNumber)->get();
//                         if ($agentAccBranch->branch_id != Branch::getCurrentBranch()->id) {
//                             $otherbranch = new Branch($agentAccBranch->branch_id);


//                             $vchNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $Ac->id);
//                             $debitAccount += array(
//                                 Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON  . $Ac->scheme->Name => $commissionAmount,
//                             );
//                             $creditAccount += array(
//                                 // get agents' account number
//                                 //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
//                                 $otherbranch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
//                                 Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * TDS_PERCENTAGE / 100),
//                             );
//                             Transaction::doTransaction($debitAccount, $creditAccount, "Agent Account openning commision Transaction for account $Ac->AccountNumber", TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $vchNo);



//                             $vchNo1 = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $Ac->id);
//                             $debitAccount = array(
//                                 Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $otherbranch->Code => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
//                             );

//                             if ($ag->getKey("transfer_commission_to_agents_account")) {
//                                 $creditAccount = array(
//                                     // get agents' account number
//                                     $Agent->AccountNumber => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
//                                 );
//                             } else {
//                                 $creditAccount = array(
//                                     // get agents' account number
//                                     Branch::getCurrentBranch()->Code . SP . COMMISSION_PAYABLE_ON . SP . $Ac->scheme->Name => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
//                                 );

//                                 $agentReport = new Agentcommissionreport();
//                                 $agentReport->agents_id = $Agent->id;
//                                 $agentReport->accounts_id = $Ac->id;
//                                 $agentReport->Commission = $commissionAmount;
//                                 $agentReport->CommissionPayableDate = getNow("Y-m-d");
//                                 $agentReport->Narration = "Agent Account opening commission payable for account $Ac->AccountNumber";
//                                 $agentReport->save();
//                             }
//                             Transaction::doTransaction($debitAccount, $creditAccount, "Agent Account opening commission for account $Ac->AccountNumber", TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $vchNo1, false, $otherbranch->id);
//                         } else {
// //                        TO TRANSFER COMMISSION IN LEVELS
//                             $debitAccount = array(Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $Ac->scheme->Name => $commissionAmount);

//                             if ($ag->getKey("transfer_commission_to_agents_account")) {
//                                 $creditAccount = array(
// //                            TRANSFER COMMISSION TO AGENT ACCOUNT
//                                     $Agent->AccountNumber => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
//                                     Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * TDS_PERCENTAGE / 100),
//                                 );
//                             } else {
//                                 $creditAccount = array(
// //                            TRANSFER COMMISSION TO COMMISSION PAYABLE ACCOUNT
//                                     Branch::getCurrentBranch()->Code . SP . COMMISSION_PAYABLE_ON . SP . $Ac->scheme->Name => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
//                                     Branch::getCurrentBranch()->Code . SP . TDS_PAYABLE => ($commissionAmount * TDS_PERCENTAGE / 100),
//                                 );

//                                 $agentReport = new Agentcommissionreport();
//                                 $agentReport->agents_id = $Agent->id;
//                                 $agentReport->accounts_id = $Ac->id;
//                                 $agentReport->Commission = $commissionAmount;
//                                 $agentReport->CommissionPayableDate = getNow("Y-m-d");
//                                 $agentReport->Narration = "Agent Account opening commission payable for account $Ac->AccountNumber";
//                                 $agentReport->save();
//                             }
//                             $vchNo2 = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $Ac->id);
//                             Transaction::doTransaction($debitAccount, $creditAccount, "Agent Account openning commision for $Ac->AccountNumber", TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $vchNo2);
//                         }
//                         $Agent = $Agent->sponsor;
//                     } while ($Agent->id);
//                 }
//             }


            switch ($Ac->scheme->SchemeType) {
                case ACCOUNT_TYPE_DEFAULT:
                    break;
                case ACCOUNT_TYPE_BANK:
                    $this->createBankTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_FIXED:
                    $this->createFixedTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_LOAN:
                    $this->createLoanTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_RECURRING:
                    $this->createRecurringTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_DDS:
                    $this->createDDSTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_CC:
                    $this->createCCTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_DHANSANCHAYA:
                    $this->createDhanSanchayaTypeAccount($Ac, $voucherNo);
                    break;
                case ACCOUNT_TYPE_MONEYBACK:
                    $this->createMoneyBackTypeAccount($Ac, $voucherNo);
                    break;
            }

            // save the documents submitted
            $documents = $this->db->query("Select * from jos_xdocuments")->result();
            $z = 1;
            foreach ($documents as $d) {

                if (inp("Documents_$z") != "") {
                    $docsSubmitted = new Documents_Submitted();
                    $docsSubmitted->accounts_id = $Ac->id;
                    $docsSubmitted->documents_id = inp("Documents_$z");
                    $docsSubmitted->Description = inp("Description_$z");
                    $docsSubmitted->save();
                }
                $z++;
            }
            log_message('error', __FILE__ . " " . __FUNCTION__ . " Documents saved for $Ac->AccountNumber with id $Ac->id from " . $this->input->ip_address());
            $this->db->trans_commit();
            log_message('error', __FILE__ . " " . __FUNCTION__ . " Data commited for $Ac->AccountNumber with id $Ac->id from " . $this->input->ip_address());
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }

        re("accounts_cont.NewAccountForm", "New Account with Account Number $Ac->AccountNumber has been created sucessfully");
    }

    /**
     * Acatual account already created in NewAcccountCreate function
     * @param <type> $ac
     * @param <type> $voucherNo
     *
     * - function is accessible to POWER_USER
     * - Credit the newly created account and debit the Branch's Cash Account with the initial Amount if Amount != 0
     * - Do the transaction
     */
    function createBankTypeAccount($ac, $voucherNo) {
//        Staff::accessibleTo(POWER_USER);

        /*         if(inp("initialAmount") < $ac->Schemes->MinLimit){
          $ac->ActiveStatus=0;
          $ac->save();
          }
         */
        if (inp("initialAmount") != 0) {
            $debitAccount = array(
                Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),
            );
            $creditAccount = array(
                $ac->AccountNumber => inp("initialAmount"),
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Initial Saving Amount Deposit in $ac->AccountNumber", TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
        }
    }

    /**
     * Acatual account already created in NewAcccountCreate function
     * @param <type> $ac
     * @param <type> $voucherNo
     *
     * - function is accessible to POWER_USER
     * - Credit the newly created account and debit the Branch's Cash Account with the initial Amount if Amount != 0
     * - Do the transaction
     */
    function createFixedTypeAccount($ac, $voucherNo) {
//        Staff::accessibleTo(POWER_USER);

        if (inp("initialAmount") != 0) {

            $debitTo = "";
            if (inp("DebitTo")) {
                $debitTo = Account::getAccountForCurrentBranch(inp("DebitTo"));
            }
            if ($debitTo)
                $debitToAccount = inp("DebitTo");
            else
                $debitToAccount = Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT;
            $debitAccount = array(
                $debitToAccount => inp("initialAmount"),
            );
            $creditAccount = array(
                $ac->AccountNumber => inp("initialAmount"),
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Initial Fixed Amount Deposit in $ac->AccountNumber", TRA_FIXED_ACCOUNT_DEPOSIT, $voucherNo);
        }
    }

    /**
     * Acatual account already created in NewAcccountCreate function
     * @param <type> $ac
     * @param <type> $voucherNo
     *
     * - function is accessible to POWER_USER
     * - calculate the processing fees. Processing fee rate retrieved from Schemes table
     * - Debit the newly created account
     * - Credit the Branch's Processing Fee Received Account and one of the Bank's account with the processing fees
     * - Do the transaction
     * - Check for the premiums mode from Schemes
     * - Set the premiums with due date
     */
    function createLoanTypeAccount($ac, $voucherNo) {
//        Staff::accessibleTo(POWER_USER);

        $sc = Scheme::getScheme(inp("AccountType"));
        $schemeName = $sc->Name;
        
        if (inp("initialAmount") != 0) {
            if ($sc->ProcessingFeesinPercent == 1) {
                $processingfee = $sc->ProcessingFees * inp("initialAmount") / 100;
            } else {
                $processingfee = $sc->ProcessingFees;
            }
            $i = inp("InterestTo");
            $loanFromAccount = new Account();
            $loanFromAccount->where('AccountNumber', $i)->get();

            $debitAccount = array(inp("AccountNumber") => inp("initialAmount"),);
            $creditAccount = array(Branch::getCurrentBranch()->Code . SP . PROCESSING_FEE_RECEIVED . $schemeName => $processingfee,);
            $creditAccount +=array(inp("InterestTo") => inp("initialAmount") - $processingfee,); //Interestto is :: loan from account
            Transaction::doTransaction($debitAccount, $creditAccount, "Loan Account Opened " . inp("AccountNumber"), TRA_LOAN_ACCOUNT_OPEN, $voucherNo);
//            }
        }

        switch ($sc->PremiumMode) {
            case RECURRING_MODE_YEARLY:
                $toAdd = " +1 year";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = " +6 month";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = " +3 month";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = " +1 month";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = " +1 day";
                break;
        }

        $lastPremiumPaidDate = getNow("Y-m-d");
        $rate = $sc->Interest;
        $premiums = $sc->NumberOfPremiums;
        if ($a->scheme->ReducingOrFlatRate == REDUCING_RATE) {
//          FOR REDUCING RATE OF INTEREST
            $emi = (inp('initialAmount') * ($rate / 1200) / (1 - (pow(1 / (1 + ($rate / 1200)), $premiums))));
        } else {
//          FOR FLAT RATE OF INTEREST
            $emi = ((inp('initialAmount') * $rate * ($premiums + 1)) / 1200 + inp('initialAmount')) / $premiums;
        }
        $emi = round($emi);
        for ($i = 1; $i <= $sc->NumberOfPremiums; $i++) {
            $prem = new Premium();
            $prem->accounts_id = $ac->id;
            $prem->Amount = $emi;
//                $prem->Paid=($i <= $premiumsSubmited ) ?  $i : 0 ;
            $prem->Paid = 0;
            $prem->Skipped = 0;
//                $prem->PaidOn=($i <= $premiumsSubmited ) ? getNow() : null ;
            $prem->PaidOn = null;
            $prem->AgentCommissionSend = 0;
//                if($i==1){
//                    $prem->DueDate=getNow("Y-m-d");
//                }else{
            $prem->DueDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate)) . $toAdd));
//                }
            $lastPremiumPaidDate = $prem->DueDate;
//                $prem->AgentCommissionPercentage=getComission($ac->Schemes->AccountOpenningCommission, PREMIUM_COMMISSION,$i);
            $prem->save();
        }
    }

    /**
     * Acatual account already created in NewAcccountCreate function
     * @param <type> $ac
     * @param <type> $voucherNo
     *
     * - function is accessible to POWER_USER
     * - calculate the processing fees. Processing fee rate retrieved from Schemes table
     * - Debit the newly created account with the processing fees
     * - Credit the Branch's Cash Account with the processing fees
     * - Do the transaction
     */
    function createCCTypeAccount($ac, $voucherNo) {
//        Staff::accessibleTo(POWER_USER);

        $sc = Scheme::getScheme(inp("AccountType"));
        $schemeName = $sc->Name;

        if (inp("initialAmount") != 0) {
            $processingfee = $sc->ProcessingFees * inp("initialAmount") / 100;
            $creditAccount = array(
                Branch::getCurrentBranch()->Code . SP . PROCESSING_FEE_RECEIVED . $schemeName => $processingfee,
            );
            $debitAccount = array(
                $ac->AccountNumber => $processingfee,
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "CC Account Opened", TRA_CC_ACCOUNT_OPEN, $voucherNo);
        }
    }

    /**
     * Acatual account already created in NewAcccountCreate function
     * @param <type> $ac
     * @param <type> $voucherNo
     *
     * - function is accessible to POWER_USER
     * - Debit the Branch's Cash account with initial amount deposited at the time of account creation
     * - Credit the new Account
     * - Do the transaction
     * - Check for the premiums mode from Schemes
     * - Set the premiums with due date
     */
    function createRecurringTypeAccount($ac, $voucherNo) {
//        Staff::accessibleTo(POWER_USER);

        if (inp("initialAmount") != 0) {
            $debitAccount = array(
                Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),
            );
            $creditAccount = array(
                $ac->AccountNumber => inp("initialAmount"),
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Initial Recurring Amount Deposit in $ac->AccountNumber", TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
        }

        $premiumsSubmited = (int) (inp("initialAmount") / inp("rdamount"));
        $ac_id = $ac->id;

        switch ($ac->scheme->PremiumMode) {
            case RECURRING_MODE_YEARLY:
                $toAdd = " +1 year";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = " +6 month";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = " +3 month";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = " +1 month";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = " +1 day";
                break;
        }

        $lastPremiumPaidDate = getNow("Y-m-d");
        $day = date("d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate))));
        $date = $lastPremiumPaidDate;
        for ($i = 1; $i <= $ac->scheme->NumberOfPremiums; $i++) {
            $prem = new Premium();
            $prem->accounts_id = $ac_id;
            $prem->Amount = inp("rdamount");
            $prem->Paid = ($i <= $premiumsSubmited ) ? $i : 0;
            $prem->Skipped = 0;
            $prem->PaidOn = ($i <= $premiumsSubmited ) ? getNow() : null;
            $prem->AgentCommissionSend = 0;
//            if ($i == 1) {
//                $prem->DueDate = getNow("Y-m-d");
//            } else {

                $prem->DueDate = $date;
                $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate)) . "+$i MONTH"));
                if (date("d", strtotime(date("Y-m-d", strtotime($date)))) != $day) {
                    $tmp = date("Y-m-28", strtotime(date("Y-m-28", strtotime($lastPremiumPaidDate)) . "+$i MONTH"));
                    $date = $this->db->query("select LAST_DAY('".$tmp."') as lastdate")->row()->lastdate;
                }
//                 $prem->DueDate = $date;

//                $prem->DueDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate)) . $toAdd));
//            }
//            $lastPremiumPaidDate = $prem->DueDate;
            $prem->AgentCommissionPercentage = getComission($ac->scheme->AccountOpenningCommission, PREMIUM_COMMISSION, $i);
            $prem->save();
        }
//        if ($ac->Agents)
//            Premiums::setCommissions($ac, $voucherNo);
    }

    function createDDSTypeAccount($ac, $voucherNo) {
        if (inp("initialAmount") != 0) {
            $debitAccount = array(
                Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),
            );
            $creditAccount = array(
                $ac->AccountNumber => inp("initialAmount"),
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Initial DDS Amount Deposit", TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
        }
    }

    function createDhanSanchayaTypeAccount($Ac, $voucherNo) {
        if (inp("initialAmount") != 0) {
            $debitAccount = array(
                Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),
            );
            $creditAccount = array(
                $ac->AccountNumber => inp("initialAmount"),
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Initial Amount Deposit in DhanSanchaya account $ac->AccountNumber", TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
        }
    }

    function createMoneyBackTypeAccount($Ac, $voucherNo) {
        if (inp("initialAmount") != 0) {
            $debitAccount = array(
                Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),
            );
            $creditAccount = array(
                $ac->AccountNumber => inp("initialAmount"),
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Initial Amount Deposit in Money Back Account $ac->AccountNumber", TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
        }

        $premiumsSubmited = (int) (inp("initialAmount") / inp("rdamount"));
        $ac_id = $ac->id;

        switch ($ac->scheme->PremiumMode) {
            case RECURRING_MODE_YEARLY:
                $toAdd = " +1 year";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = " +6 month";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = " +3 month";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = " +1 month";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = " +1 day";
                break;
        }

        $lastPremiumPaidDate = getNow("Y-m-d");
        for ($i = 1; $i <= $ac->scheme->NumberOfPremiums; $i++) {
            $prem = new Premium();
            $prem->accounts_id = $ac_id;
            $prem->Amount = inp("rdamount");
            $prem->Paid = ($i <= $premiumsSubmited ) ? $i : 0;
            $prem->Skipped = 0;
            $prem->PaidOn = ($i <= $premiumsSubmited ) ? getNow() : null;
            $prem->AgentCommissionSend = 0;
            if ($i == 1) {
                $prem->DueDate = getNow("Y-m-d");
            } else {
                $prem->DueDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate)) . $toAdd));
            }
            $lastPremiumPaidDate = $prem->DueDate;
            $prem->AgentCommissionPercentage = getComission($ac->scheme->AccountOpenningCommission, PREMIUM_COMMISSION, $i);
            $prem->save();
        }
    }

    /**
     * function to search for all existing accounts
     * sends the link to {@link searchAccount}
     * - generate form
     */
    function searchAccountForm() {
        $accountTypeArray = explode(",", ACCOUNT_TYPES);
        $accountTypeArray = array_merge(array("Any"), $accountTypeArray);
        $accountTypeArray = array_combine($accountTypeArray, $accountTypeArray);
        $documents = $this->db->query("Select * from jos_xdocuments")->result();
        $i = 1;
        //setInfo("SEARCH ACCOUNT", "");
        $this->load->library('form');
        $form = $this->form->open("one", 'index.php?option=com_xbank&task=accounts_cont.searchAccount')
                        ->setColumns(2)
                        ->text("Account Number", "name='AccountNumber' class='input'")
                        ->select("Account Type", "name='SchemeType'", $accountTypeArray)
                        ->lookupDB("Member ID", "name='UserID' class='input ui-autocomplete-input'", "index.php?//ajax/lookupDBDQL",
                                array("select" => "m.id AS ID, m.Name AS Name, m.PhoneNos",
                                    "from" => "jos_xmember m",
                                    "leftJoin" => "m.Branch b",
                                    "where" => "m.branch_id=" . Branch::getCurrentBranch()->id,
                                    "andWhere" => "m.Name Like '%\$term%'",
                                    "orWhere" => "m.id Like '%\$term%'"),
                                array("ID", "Name"), "ID")
//                ->lookupDB("Agent's Member ID","name='Agents_Id' class='input ui-autocomplete-input'","index.php?//ajax/lookupDBDQL",array("select"=>"m.id AS ID, m.Name AS Name, m.PhoneNos","from"=>"Member m","innerJoin"=>"m.Branch b","innerJoin"=>"m.Agents a","where"=>"b.id=".Branch::getCurrentBranch()->id,"andWhere"=>"m.Name Like '%\$term%'","orWhere"=>"m.id Like '%\$term%'"),array("ID","Name"),"ID")
//                ->lookupDB("Agent's Member ID","name='Agents_Id' class='input ui-autocomplete-input'","index.php?//ajax/lookupDBDQL",array("select"=>"m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos","from"=>"Member m","innerJoin"=>"m.Agents a","where"=>"m.branch_id=".Branch::getCurrentBranch()->id,"andWhere"=>"m.Name Like '%\$term%' or m.id Like '%\$term%'"),array("id","Name","PanNo"),"id")//"orWhere"=>"m.id Like '%\$term%'"),array("id","Name","PanNo"),"id")
                        ->select("Active Status", "name='ActiveStatus'", array("Any" => '%', "Active" => '1', "DeActive" => '0'))
                        ->_()
//                foreach($documents as $d){
//                    $form=$form->checkBox($d->Name,"name='Documents_$i' class='input' value='$d->id'")
//                    ->textArea("Description for $d->Name","name='Description_$i'");
//                    $i++;
//               }
//               $i--;
// 		$form=$form->hidden("","name='ivalue' value='$i'")
                        ->submit('Search');
        $data['contents'] = $this->form->get();
        $this->load->view('template', $data);
    }

    /**
     * function simply search for accounts based on input given in searchAccountForm
     */
    function searchAccount() {
//        Staff::accessibleTo(POWER_USER);

        $query = "select a.*, m.Name as MemberName, s.Name as SchemeName from jos_xaccounts a join jos_xmember m on a.member_id=m.id join jos_xschemes s on s.id=a.schemes_id ";
//                $join=" join documents_submitted ds on a.id=ds.accounts_id join documents d on d.id=ds.documents_id";
        $where = " where a.branch_id = " . Branch::getCurrentBranch()->id;
        if (inp("AccountNumber") != "") {
            $where .=" AND a.AccountNumber like '%" . inp("AccountNumber") . "%' ";
        }
        if (inp("SchemeType") != "Any") {
            $where .=" AND s.SchemeType like '%" . inp("SchemeType") . "%' ";
        }
        if (inp("UserID") != "") {
            $where .=" AND (m.Name like '%" . inp("UserID") . "%'  or m.id =" . (inp("UserID")) . ")";
        }
        if (inp("ActiveStatus") != "%") {
            $where .=" AND a.ActiveStatus =" . inp("ActiveStatus");
        }

        $query .= $where . " ORDER BY id";
        $result = $this->db->query($query)->result();
        $data['accounts'] = $result;


        $accountTypeArray = explode(",", ACCOUNT_TYPES);
        $accountTypeArray = array_merge(array("Any"), $accountTypeArray);
        $accountTypeArray = array_combine($accountTypeArray, $accountTypeArray);
        $documents = $this->db->query("Select * from jos_xdocuments")->result();
        $i = 1;
        //setInfo("SEARCH ACCOUNT", "");
        //$this->load->library('form');
        $form = $this->form->open("one", 'index.php?option=com_xbank&task=accounts_cont.searchAccount')
                        ->setColumns(2)
                        ->text("Account Number", "name='AccountNumber' class='input'")
                        ->select("Account Type", "name='SchemeType' ", $accountTypeArray)
                        ->lookupDB("Member ID", "name='UserID' class='input ui-autocomplete-input'", "index.php?//ajax/lookupDBDQL",
                                array("select" => "m.id AS ID, m.Name AS Name, m.PhoneNos",
                                    "from" => "jos_xmember m",
                                    "leftJoin" => "m.Branch b",
                                    "where" => "m.branch_id=" . Branch::getCurrentBranch()->id,
                                    "andWhere" => "m.Name Like '%\$term%'",
                                    "orWhere" => "m.id Like '%\$term%'"),
                                array("ID", "Name"), "ID")
//                ->lookupDB("Agent's Member ID","name='Agents_Id' class='input ui-autocomplete-input'","index.php?//ajax/lookupDBDQL",array("select"=>"m.id AS ID, m.Name AS Name, m.PhoneNos","from"=>"Member m","innerJoin"=>"m.Branch b","innerJoin"=>"m.Agents a","where"=>"b.id=".Branch::getCurrentBranch()->id,"andWhere"=>"m.Name Like '%\$term%'","orWhere"=>"m.id Like '%\$term%'"),array("ID","Name"),"ID")
//                ->lookupDB("Agent's Member ID","name='Agents_Id' class='input ui-autocomplete-input'","index.php?//ajax/lookupDBDQL",array("select"=>"m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos","from"=>"Member m","innerJoin"=>"m.Agents a","where"=>"m.branch_id=".Branch::getCurrentBranch()->id,"andWhere"=>"m.Name Like '%\$term%' or m.id Like '%\$term%'"),array("id","Name","PanNo"),"id")//"orWhere"=>"m.id Like '%\$term%'"),array("id","Name","PanNo"),"id")
                        ->select("Active Status", "name='ActiveStatus'", array("Any" => '%', "Active" => '1', "DeActive" => '0'))
                        ->_()
//                foreach($documents as $d){
//                    $form=$form->checkBox($d->Name,"name='Documents_$i' class='input' value='$d->id'")
//                    ->textArea("Description for $d->Name","name='Description_$i'");
//                    $i++;
//               }
//               $i--;
// 		$form=$form->hidden("","name='ivalue' value='$i'")
                        ->submit('Search');


        $data['contents'] = $this->form->get();
        $data['contents'] .=$this->load->view("searchAccountsView", $data, true);
        $this->load->view("template", $data);
    }

    /**
     * function generates a form to edit the accounts
     * - sends link to {@link editAccounts}
     */
    function editAccountsForm($id='') {
//        Staff::accessibleTo(POWER_USER);
        
        $schemes = Branch::getAllSchemesForCurrentBranch();


        $id = JRequest::getVar("id");
        $acc = new Account($id);
//        $acc->where('id', $i)->get();
        xDeveloperToolBars::onlyCancel("search_cont.searchAccountForm", "cancel", "Edit Account $acc->AccountNumber");

        $s = $acc->schemes_id;
        $sc = new Scheme();
        $sc->where('id', $s)->get();
        $schemeType = $sc->SchemeType;

        $b = Branch::getCurrentBranch();
        $branchCode = $b->Code;

        $this->jq->addInfo("Agent", $acc->agents_id . " :: " . Agent::getAgentFromAccount($acc->id)->Name );
        $defaultAgent = $this->jq->flashMessages(true);

        $this->jq->addInfo("Member", "Member Details");
        $member = $this->jq->flashMessages(true);

        $this->jq->addInfo("Accounts", "Accounts Details");
        $accounts = $this->jq->flashMessages(true);

        $this->jq->addInfo("Dealer", $acc->dealer->id . " :: " . $acc->dealer->DealerName . " :: " . $acc->dealer->Address);
        $dealer = $this->jq->flashMessages(true);


        $documents = $this->db->query("Select * from jos_xdocuments")->result();




        if ($schemeType == ACCOUNT_TYPE_BANK) {
            $form = $this->form->open("NewBankAccount", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                            ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                            ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")

                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus);
                            
            $form = $form->select("Operation Mode", "name='ModeOfOperation'", array("Select_Mode" => '-1', "Self" => 'Self', "Joint" => 'Joint', "Any One" => 'Any', "Otehr" => 'Other'), $acc->ModeOfOperation);
//            Documents to be submitted
            $i = 1;
            foreach ($documents as $d) {
                if ($d->SavingAccount == "" or $d->SavingAccount == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }

            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');
            $this->jq->addTab(1, "Saving Current Account", $form->get());
        }

        if ($schemeType == ACCOUNT_TYPE_FIXED) {
            $form = $this->form->open("FixedAndMIS", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                           ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                           ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")
                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus);
            $i = 1;
            foreach ($documents as $d) {
                if ($d->FixedMISAccount == "" or $d->FixedMISAccount == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }


            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');
            $this->jq->addTab(1, "Fixed And MIS Accounts", $this->form->get());
        }

        if ($schemeType == ACCOUNT_TYPE_LOAN) {
            $form = $this->form->open("LoanAccounts", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                           ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                           ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")
                            ->text("Loan Amount", "name='initialAmount' class='input req-numeric' value='$acc->RdAmount' DISABLED")
                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->_()
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus)
                            ->lookupDB("Gaurantor","name='Nominee' class='input' value='$acc->Nominee' ","index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw",array("a"=>"b"),array("id","Name"),"Name")
                            ->text("Gaurantor Address", "name='MinorNomineeParentName' class='input' value='$acc->MinorNomineeParentName'")  // IMP : Used as gaurantor address in loan accounts
                            ->text("Gaurantor Phone Nos.", "Name='RelationWithNominee' class='input' value='$acc->RelationWithNominee'")
                            ->dateBox("Loan Insurrance Date", "name='LoanInsurranceDate' value='$acc->LoanInsurranceDate'");
            //                ->selectAjax("Select Account if Loan Ag. Security","name='SecurityAccount' class='req-string not-req' not-req-val='Select_Account'",$this->db->query("select a.id, a.AccountNumber,m.Name,DATE_ADD(a.created_at,INTERVAL s.MaturityPeriod MONTH) as MaturityDate, (a.CurrentBalanceCr - a.CurrentBalanceDr) from accounts a join member m on a.member_id=m.id join schemes s on a.schemes_id=s.id where (s.SchemeType='Deposit' or s.SchemeType='Fixed & Mis' or s.SchemeType='Recurring') and a.branch_id=".Branch::getCurrentBranch()->id." and  a.member_id=".inp("UserID")." and a.ActiveStatus=1")->result())
            //                ->lookupDB("Select Account if Loan Ag. Security","name='SecurityAccount' class='input req-string' onblur='javascript:$(\"#accountsDetails\").load(\"mod_accounts/accounts_cont/accountsDetails/\"+this.value);'","index.php?//ajax/lookupDBDQL",array("select"=>"a.id AS Id,a.AccountNumber AS AccNum, m.Name AS Name, DATE_ADD(a.created_at,INTERVAL s.MaturityPeriod MONTH) AS MaturityDate, (a.CurrentBalanceCr - a.CurrentBalanceDr) AS CurrentBalance","from"=>"Accounts a","leftJoin"=>"a.member m","leftJoin"=>"a.schemes s","where"=>"s.SchemeType='Deposit'","orWhere"=>"s.SchemeType='Fixed & Mis'","orWhere"=>"s.SchemeType='Recurring'","andWhere"=>"a.branch_id='$b->id'","andWhere"=>"a.ActiveStatus =1","andWhere"=>"a.AccountNumber Like '%\$term%'"),array("Id"),"Id")
            //                ->lookupDB("Select Member(if Loan Ag. Security)","name='memberid' class='input req-string' onblur='javascript:$(\"#accountsDetailsF\").load(\"index.php?//mod_accounts/accounts_cont/accountsDetails/\"+this.value);'","index.php?//ajax/lookupDBDQL",array("select"=>"m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos","from"=>"Member m, m.Branch b","where"=>"b.id=$b->id","andWhere"=>"m.Name Like '%\$term%'","orWhere"=>"m.id Like '%\$term%'"),array("id","Name"),"id")
//                        ->checkbox("Loan Ag Security","name='LoanAgSecurity' class='input' value='1'")
//                        ->_()
            $securityAcc = "";

            if ($acc->LoanAgainstAccount != "")
                $securityAcc = $this->db->query("Select AccountNumber as accNum from jos_xaccounts where id=$acc->LoanAgainstAccount")->row()->accNum;
            $form = $form->text("Account Number(if Loan Ag. Security)", "Name='SecurityAccount' class='input' value='$securityAcc' DISABLED")
//                        ->div("accountsDetailsF","",$accounts);
//                            ->_()
                            ->lookupDB("Dealer ID", "name='Dealer' class='input'value='$acc->dealer_id' onblur='javascript:jQuery(\"#DealerDetails\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.dealerDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.getDealer&format=raw", array("a" => "b"), array("id", "DealerName", "Address"), "id")
                            ->div("DealerDetails", "", $dealer);
            $i = 1;
            foreach ($documents as $d) {
                if ($d->LoanAccount == "" or $d->LoanAccount == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }

            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');
            $this->jq->addTab(1, "Loan Account", $this->form->get());
        }

        if ($schemeType == ACCOUNT_TYPE_RECURRING) {
            $form = $this->form->open("RD", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                            ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                           ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")
                            ->text("RECURRING amount", "name='rdamount' class='input req-string' value='$acc->RdAmount' DISABLED")
                            ->_()
                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus);
                            
            $i = 1;
            foreach ($documents as $d) {
                if ($d->RDandDDSAccount == "" or $d->RDandDDSAccount == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }

            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');
            $this->jq->addTab(1, "RD Accounts", $this->form->get());
        }

        if ($schemeType == ACCOUNT_TYPE_DDS) {
            $form = $this->form->open("DDS", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                            ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                           ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")
                            ->text("DDS amount", "name='rdamount' class='input req-string' value='$acc->RdAmount' ")
                            ->_()
                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus);
            $i = 1;
            foreach ($documents as $d) {
                if ($d->RDandDDSAccount == "" or $d->RDandDDSAccount == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }

            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');
            $this->jq->addTab(1, "DDS Accounts", $this->form->get());
        }


        if ($schemeType == ACCOUNT_TYPE_CC) {
            $form = $this->form->open("CCAccounts", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                            ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                           ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")
                            ->text("CC Limit", "name='rdamount' class='input req-string' value='$acc->RdAmount' ")
                            ->_()
                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus)
                            ->_();
            $i = 1;
            foreach ($documents as $d) {
                if ($d->CCAccount == "" or $d->CCAccount == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }

            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');

            $this->jq->addTab(1, "CC Accounts", $this->form->get());
        }

        if ($schemeType == ACCOUNT_TYPE_DEFAULT) {
            $form = $this->form->open("OtherAccounts", "index.php?option=com_xbank&task=accounts_cont.editAccounts&id=$id")
                            ->setColumns(2)
                            ->lookupDB("Account number", "name='AccountNumber' class='input req-string' value='$acc->AccountNumber'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                            ->text("Account Name","name='AccountDisplayName' value='".$acc->AccountDisplayName."'")
                            ->lookupDB("Member Name", "name='UserID' class='input req-string' value='" . $acc->member_id . "'  onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                            ->div("memberDetailsO","",$acc->member->Name)
                            // ->text("Member Name", "name='UserID' class='input req-string' value='" . $acc->member->Name . "' DISABLED")
                            // ->text("Account Under", "name='AccountType' class='input req-string' value='" . $acc->scheme->Name . "' DISABLED")
                            ->selectAjax("Account Under", "name='schemes_id' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_DEFAULT),$acc->scheme->id)
                            ->text("Opening Balance CR","name='initialAmountCR' class='input req-numeric tooltip' value='$acc->OpeningBalanceCr' title='Put the opening CR amount for account'")
                            ->text("Opening Balance DR","name='initialAmountDR' class='input req-numeric tooltip' value='$acc->OpeningBalanceDr' title='Put the opening DR amount for account'")
                            ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' value='$acc->agents_id'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                            ->div("agentDetailsO", "", $defaultAgent)
                            ->text("PAndLGroup", "name='PAndLGroup' value='$acc->PAndLGroup'")
                            ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'), $acc->ActiveStatus);
            $i = 1;
            foreach ($documents as $d) {
                if ($d->OtherAccounts == "" or $d->OtherAccounts == 0) {
                    $i++;
                    continue;
                }
                $documents_submitted = $this->db->query("Select documents_id,Description from jos_xdocuments_submitted where accounts_id=$id and documents_id=$d->id")->row();
                if ($documents_submitted) {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id' CHECKED")
                                    ->textArea("Description for $d->Name", "name='Description_$i'", "", $documents_submitted->Description);
                } else {
                    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                                    ->textArea("Description for $d->Name", "name='Description_$i'");
                }
                $i++;
            }
            $form = $form->_()
                            ->submit('Edit')
                            ->resetBtn('Reset');

            $this->jq->addTab(1, "Other Accounts", $this->form->get());
        }

        $data['tabs'] = $this->jq->getTab(1);
        JRequest::setVar("layout", "accountopenform");
        $this->load->view('accounts.html', $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $id
     * function edits the accounts and redirects the page to the function mod_accounts/accounts_cont/index
     */
    function editAccounts() {
        $id = JRequest::getVar("id");

        $u = JFactory::getUser();
        if ($u->usertype != 'Administrator')
                re("accounts_cont.editAccountsForm&id=$id","You are not authorized to edit accounts","error");
        
        $Ac = new Account($id);
//        CHECK IF ACCOUNT NUMBER ALREADY EXISTS
        if(inp('AccountNumber')){
            $query = "select * from jos_xaccounts where id <> ".$id." and AccountNumber = '".inp('AccountNumber')."'";
            $q=$this->db->query($query);
            if($q->num_rows())
                    re("accounts_cont.editAccountsForm&id=$id","Account Number ".inp("AccountNumber")." Already Exists",'error');
        }


       $u=inp("UserID");
       $m=new Member($u);

       if (!$m->result_count()) {
            re("accounts_cont.editAccountsForm&id=$id","The Member with id $u not found",'error');
       }

        if($Ac->agents_id != 0 && inp("Agents_Id") != ""){
            $ag=new Agent(inp("Agents_Id"));

            if (!$ag->result_count()) {
                 re("accounts_cont.editAccountsForm&id=$id","The Agent with id ".inp("Agents_Id")." not found",'error');
            }
        }


        try {
            $this->db->trans_begin();
            
            if(inp('AccountNumber')){
                $Ac->AccountNumber = inp('AccountNumber');
            }
            $Ac->PAndLGroup = inp('PAndLGroup');
            $Ac->member_id = inp("UserID");
            $Ac->OpeningBalanceCr = (inp("initialAmountCR") ? inp("initialAmountCR") : 0);
            $Ac->OpeningBalanceDr = (inp("initialAmountDR") ? inp("initialAmountDR") : 0);
            $Ac->ActiveStatus = inp('ActiveStatus');
            $Ac->agents_id = inp("Agents_Id");
            if($Ac->RdAmount == 0 && inp("rdamount") != "" || $Ac->RdAmount != 0 && inp("rdamount") != "")
                    $Ac->RdAmount = inp("rdamount");
            $Ac->Nominee = inp('Nominee');  // IMP : used as guarantor for loan accounts
            $Ac->RelationWithNominee = inp('RelationWithNominee');  // IMP : Used as Gaurantor Phone nos. for loan accounts
            $Ac->MinorNomineeParentName = inp('MinorNomineeParentName');   // IMP : used as guarantor Address for loan accounts
            $Ac->ModeOfOperation = (inp('ModeOfOperation') == "") ? "Self" : inp('ModeOfOperation');
            if(inp("AccountDisplayName"))
                $Ac->AccountDisplayName = inp("AccountDisplayName");
            if (inp("Dealer") != "")
                $Ac->dealer_id = inp('Dealer');

            if(inp('schemes_id')){
                $Ac->schemes_id = inp('schemes_id');
            }

            if(inp('LoanInsurranceDate')){
                $Ac->LoanInsurranceDate = inp('LoanInsurranceDate');
            }

            $Ac->save();
            log::write( __FILE__ . " " . __FUNCTION__ . " $Ac->AccountNumber with id $Ac->id edited from " . $this->input->ip_address(),$Ac->id);
            // save the documents submitted
            $documents = $this->db->query("Select * from jos_xdocuments")->result();
            $i = 1;
            foreach ($documents as $d) {

                if (inp("Documents_$i") != "") {

                    //$docs=$this->db->query("select * from jos_xdocuments_submitted where accounts_id=".$id." and documents_id=".inp("Documents_$i"))->result();
//                    $d = inp("Documents_$i");
                    $docs = new Documents_submitted();
                    $docs->where('accounts_id', $id)->where('documents_id', $d->id)->get();
                    //$docs = Doctrine::getTable("DocumentsSubmitted")->findOneByAccounts_idAndDocuments_id($id, inp("Documents_$i"));
                    if (!$docs->result_count()) {
                        $docsSubmitted = new Documents_Submitted();
                        $docsSubmitted->accounts_id = $id;
                        $docsSubmitted->documents_id = inp("Documents_$i");
                        $docsSubmitted->Description = inp("Description_$i");
                        $docsSubmitted->save();
                    } else {

                        $docs->documents_id = inp("Documents_$i");
                        $docs->Description = inp("Description_$i");
                        $docs->save();
                    }
                }
//
                $i++;
            }
            log::write( __FILE__ . " " . __FUNCTION__ . " Documents saved for $Ac->AccountNumber with id $Ac->id from " . $this->input->ip_address());
            
            log::write( __FILE__ . " " . __FUNCTION__ . " Account Editing committed $Ac->AccountNumber with id $Ac->id from " . $this->input->ip_address());
             $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            //$conn->rollback();
            echo $e->getMessage();
            return;
        }

        re('search_cont.searchAccountForm',"$Ac->AccountNumber edited successfully");
    }

    function printToPDF($id='') {
        $id = JRequest::getVar("id");
        $a = new Account();
        $aa = $a->where('id', $id)->get();
        $data['account'] = $aa;
        //$data['account'] = Doctrine::getTable("Accounts")->findOneById($id);
        JRequest::setVar("layout","AccountsPDFView");
        $this->load->view('search.pdf', $data);


//        $html = $this->load->view('search.pdf', $data,true);
//        $this->load->helper('download');
//        $name = 'AccountsPDFView.pdf';
//        force_download($name, $html);

        $this->jq->getHeader();
//        $this->load->plugin("to_pdf");
//        pdf_create($html, 'chequedata', true);
    }

    function accountsInfo($id) {
        $a = new Account();
        $aa = $a->where('id', $id)->get();
        $data['account'] = $aa;
        //$data['account'] = Doctrine::getTable("Accounts")->findOneById($id);
        $data['contents'] = "<a href='index.php?//mod_accounts/accounts_cont/printToPDF/$id' target='_blank'>Click Here</a> to print the receipt of Acccount " . $data['account']->AccountNumber;
        $this->load->view("template", $data);
    }

    /**
     *
     * @param <type> $id
     * Change the status of the account
     */
    function statusChange($id='') {
//        Staff::accessibleTo(BRANCH_ADMIN);
        $id = JRequest::getVar("id");
        $ac = new Account($id);
        xDeveloperToolBars::onlyCancel("search_cont.searchAccountForm", "cancel", "Change status of $ac->AccountNumber here");
        if ($ac->ActiveStatus == 1) {

            $this->load->library('form');
            $this->form->open("one", "index.php?option=com_xbank&task=accounts_cont.statusChangeConfirm&id=$id")
                    ->setColumns(2)
                    ->checkBox("Check if account $ac->AccountNumber affects Balance Sheet", "name='affectsBalanceSheet' class='input' value='1'", "CHECKED")
//                ->confirmButton("Confirm","Change the status of Account Number $ac->AccountNumber","index.php?/mod_accounts/accounts_cont/statusChangeConfirm/$id",true)
                    ->submit('Change');
            echo $this->form->get();
//            $data['contents'] = $this->form->get();
//            $this->load->view('accounts.html', $data);
            $this->jq->getHeader();
        } else {
//
            $ac->ActiveStatus = ($ac->ActiveStatus == 0) ? 1 : 0;
            $ac->affectsBalanceSheet = 0;
            $ac->save();
            log::write( __FILE__ . " " . __FUNCTION__ . " Status of $ac->AccountNumber with id $ac->id changed to $ac->ActiveStatus from " . $this->input->ip_address(),$ac->id);
            re('search_cont.searchAccountForm');
        }
    }

    function statusChangeConfirm($id='') {
//        Staff::accessibleTo(BRANCH_ADMIN);
        $id = JRequest::getVar("id");
        $ac = new Account($id);
//        $ac->where('id', $id)->get();
        //$ac = Doctrine::getTable("Accounts")->findOneById($id);
        $ac->ActiveStatus = ($ac->ActiveStatus == 0) ? 1 : 0;
        if (inp("affectsBalanceSheet") == 1)
            $ac->affectsBalanceSheet = 1;
        $ac->save();
        log::write(__FILE__ . " " . __FUNCTION__ . " Status of $ac->AccountNumber with id $ac->id changed to $ac->ActiveStatus from " . $this->input->ip_address());
        re("search_cont.searchAccountForm");
    }

    function deleteAccountConfirm($id='') {
        $id=  JRequest::getVar("id");
        $ac = new Account($id);
//        $ac->where('id', $id)->get();
        //$ac = Doctrine::getTable("Accounts")->find($id);
        $branchid = Branch::getCurrentBranch()->id;
        $sum = array();
        $transactions = new Transaction();
        $transactions->where('accounts_id', $ac->id)->where('branch_id', $branchid)->get();
        // $transactions = Doctrine::getTable("Transactions")->findByAccounts_idAndBranch_id($ac->id, $branchid);
        foreach ($transactions as $tr) {
            $trans = new Transaction();
            $trans->where('voucher_no', $tr->voucher_no)->where('branch_id', $branchid)->get();
            //$trans = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($tr->voucher_no, $branchid);
//                if($trans->count()==2){
            foreach ($trans as $t) {
                if (!isset($sum[$t->account->AccountNumber])) {
                    $sum[$t->account->AccountNumber]['DR'] = 0;
                    $sum[$t->account->AccountNumber]['CR'] = 0;
                }
                $sum[$t->account->AccountNumber]['DR'] += $t->amountDr;
                $sum[$t->account->AccountNumber]['CR'] += $t->amountCr;
            }
//                    continue;
//                }
        }
        $i = 0;
        $acc = array_keys($sum);
        foreach ($sum as $s) {

            echo $acc[$i] . " :: " . $sum[$acc[$i]]['DR'] . " :: " . $sum[$acc[$i]]['CR'] . "<br>";
            $i++;
        }

            echo "<a href='index.php?option=com_xbank&task=accounts_cont.deleteAccount&id=$id'>OK</a>";
    }

    function deleteAccount($id='') {
//        Staff::accessibleTo(BRANCH_ADMIN);
        if(JFactory::getUser()->usertype != 'Administrator')
                re('accounts_cont.index','You are not authorized to delete an account. Please contact Head Office Admin for this.','error');
        $branchid = Branch::getCurrentBranch()->id;
        $id = JRequest::getVar("id");
        $ac = new Account($id);
//        $ac->where('id', $id)->where('branch_id', $branchid)->get();
        if (!$ac) {
            echo "Account Does not Exists";
            return;
        }
        try {
            $this->db->trans_begin();
            // $conn->beginTransaction();
            $sc = Scheme::getScheme($ac->schemes_id);
            $schemeName = $sc->Name;

            $transactions = new Transaction();
            $transactions->where('accounts_id', $ac->id)->where('branch_id', $branchid)->get();
            // $transactions = Doctrine::getTable("Transactions")->findByAccounts_idAndBranch_id($ac->id, $branchid);
            foreach ($transactions as $tr) {
                $trans = new Transaction();
                $trans->where('voucher_no', $tr->voucher_no)->where('branch_id', $branchid)->get();
                //$trans = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($tr->voucher_no, $branchid);

                foreach ($trans as $t) {
                    if ($t->accounts_id == $ac->id)
                        continue;
                    $otherac = new Account();
                    $otherac->where('id', $t->accounts_id)->get();
                    //$otherac = Doctrine::getTable("Accounts")->findOneById($t->accounts_id);
                    $otherac->CurrentBalanceCr = $otherac->CurrentBalanceCr - $t->amountCr;
                    $otherac->CurrentBalanceDr = $otherac->CurrentBalanceDr - $t->amountDr;
                    $otherac->save();

                    $q = "delete from jos_xtransactions where voucher_no = $t->voucher_no and branch_id = $branchid";
                    executeQuery($q);
                }
            }

//            $q = "delete from transactions where voucher_no = $transactions->voucher_no and branch_id = $branchid";
//            executeQuery($q);
            $query = "delete from jos_xpremiums where accounts_id = $ac->id";
            executeQuery($query);
            $query = "delete from jos_xaccounts where id = $ac->id";
            executeQuery($query);


            $this->db->trans_commit();
            echo "Done";
            log::write( __FILE__ . " " . __FUNCTION__ . " $ac->AccountNumber with id $ac->id deleted by ".Staff::getCurrentStaff()->StaffID . " from " . $this->input->ip_address(),$ac->id);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
    }

    function AccountNumber() {
        $list = array();
        //$q = "select a.* from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where  (a.AccountNumber like '%" . $this->input->post("term") . "%' or a.id like '%" . $this->input->post("term") . "%') and s.`Name`='" . SAVING_ACCOUNT_SCHEME . "'";
        $q = "select a.*, m.Name from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id join jos_xmember m on a.member_id = m.id where a.AccountNumber Like '%" . $this->input->post("term") . "%' or a.id like '%" . $this->input->post("term") . "%' limit 10 ";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->id, 'AccountNumber' => $dd->AccountNumber, 'Name' => $dd->Name);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function Gaurantor() {
        $list = array();
        $q = "select m.id AS ID, m.Name AS Name, m.FatherName as FatherName, m.PanNo AS PanNo, m.PhoneNos, b.Name AS BranchName from jos_xmember m join jos_xbranch b on m.branch_id=b.id where( m.Name Like '%" . $this->input->post("term") . "%' or m.id Like '%" . $this->input->post("term") . "%') and m.IsMember = 1 order by m.id limit 0,10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->ID, 'Name' => $dd->Name,  "BranchName" => $dd->BranchName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }
    
    function MemberID() {
        $list = array();
        $q = "select m.id AS ID, m.Name AS Name, m.FatherName as FatherName, m.PanNo AS PanNo, m.PhoneNos, b.Name AS BranchName from jos_xmember m join jos_xbranch b on m.branch_id=b.id where( m.Name Like '%" . $this->input->post("term") . "%' or m.id Like '%" . $this->input->post("term") . "%') and m.IsMember = 1 order by m.id limit 0,10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->ID, 'Name' => $dd->Name, "FatherName" => $dd->FatherName, "BranchName" => $dd->BranchName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function CustomerID() {
        $list = array();
        $q = "select m.id AS ID, m.Name AS Name, m.FatherName as FatherName, m.PanNo AS PanNo, m.PhoneNos, b.Name AS BranchName from jos_xmember m join jos_xbranch b on m.branch_id=b.id where( m.Name Like '%" . $this->input->post("term") . "%' or m.id Like '%" . $this->input->post("term") . "%') and m.IsCustomer = 1 order by m.id limit 0,10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->ID, 'Name' => $dd->Name, "FatherName" => $dd->FatherName, "BranchName" => $dd->BranchName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function AgentMemberID() {
        $list = array();
        $q = "select a.id as id, m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos, m.FatherName as FatherName,b.Name AS BranchName from jos_xmember m join jos_xagents a on a.member_id = m.id join jos_xbranch b on m.branch_id=b.id where(a.id='%" . $this->input->post("term") . "%' or m.Name Like '%" . $this->input->post("term") . "%' )";
        //$q = "select m.id AS ID, m.Name AS Name, m.FatherName as FatherName, m.PanNo AS PanNo, m.PhoneNos, b.Name AS BranchName from jos_xmember m, m.jos_xbranch b where( m.Name Like '%".$this->input->post("term"). "%' or m.id Like '%".$this->input->post("term")."%')";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->id, 'Name' => $dd->Name, "FatherName" => $dd->FatherName, "BranchName" => $dd->BranchName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function AccountToDebit() {
        $list = array();
        $b = Branch::getCurrentBranch()->id;
        //$q="select m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos from jos_xmember m join jos_xagents a on a.member_id = m.id where(m.id='%".$this->input->post("term")."%' or m.Name Like '%".$this->input->post("term")."%' )";
        $q = "select a.*, s.Name, m.Name as MemberName from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id join jos_xbranch b on a.branch_id=b.id join jos_xmember m on a.member_id=m.id where(a.AccountNumber Like '%" . $this->input->post("term") . "%' AND ((s.Name='Bank Accounts' OR s.Name='Cash Account' OR s.Name='Saving Account' OR s.Name='Branch & Divisions') or a.id Like '%" . $this->input->post("term") . "%') AND a.branch_id = $b and a.LockingStatus<>1 AND a.ActiveStatus<>0)";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber, 'MemberName' => $dd->MemberName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function InterestToAccountnumber() {
        $list = array();
        //$q="select m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos from jos_xmember m join jos_xagents a on a.member_id = m.id where(m.id='%".$this->input->post("term")."%' or m.Name Like '%".$this->input->post("term")."%' )";
        $q = "select a.*, m.Name AS Name, m.PanNo AS Pan, s.Name As Scheme from jos_xaccounts a left join jos_xbranch b on a.branch_id=b.id left join jos_xmember m on a.member_id=m.id left join jos_xschemes s on a.schemes_id=s.id where a.AccountNumber Like '%" . $this->input->post("term") . "%' OR a.id Like '%" . $this->input->post("term") . "%' AND (s.SchemeType='" . ACCOUNT_TYPE_BANK . "')";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('Name' => $dd->Name, 'AccountNumber' => $dd->AccountNumber, 'Scheme' => $dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }


    function getDealer() {
        $list = array();
        $b = Branch::getCurrentBranch()->id;
        //$q="select m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos from jos_xmember m join jos_xagents a on a.member_id = m.id where(m.id='%".$this->input->post("term")."%' or m.Name Like '%".$this->input->post("term")."%' )";
        $q = "select d.* from jos_xdealer d where d.DealerName Like '%" . $this->input->post("term") . "%' OR d.id Like '%" . $this->input->post("term") . "%' ";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->id, 'DealerName' => $dd->DealerName, 'Address' => $dd->Address);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    /**
     * Function generates the member details based on the member id passed as a parameter
     */
    function memberDetails() {
        $id = JRequest::getVar("id");
        $m = new Member();
        $m->where('id', $id)->get();
        if (!$m or $id=='') {
            $this->jq->addError($id . ": ", "Oops Not Found");
            $this->jq->flashMessages();
            return;
        }
        $msg = $m->PanNo;
        if ($msg == "")
            $msg = $m->PhoneNos;
        if ($msg == "")
            $msg = $m->CurrentAddress;
        $this->jq->addInfo($id . ": $m->Name", $msg);
        $this->jq->flashMessages();
    }

    /**
     * Function generates the details of the agent based on the agent id
     */
    function agentDetails() {
        $id = JRequest::getVar("aid");
        $a = new Agent();
        if ($id){
            $a->where('id', $id)->get();
        }
        if (!$a->exists() or trim($id) == '') {
             $this->jq->addError($id . ": ", "No user or User is not Agent <font size='+1'><b>NO COMMISSION GRANTED</b></font>");
            $this->jq->flashMessages();
            return;
        }
        $m = $a->member;
        $msg = $m->PanNo;
        if ($msg == "")
            $msg = $m->PhoneNos;
        if ($msg == "")
            $msg = $m->CurrentAddress;
        $this->jq->addInfo($id . ": $m->Name", $msg);
        $this->jq->flashMessages();
    }

    function accountsDetails($id='') {
        $id = JRequest::getVar("id");
        $a = $this->db->query("select a.id, a.AccountNumber,m.Name,DATE_ADD(a.created_at,INTERVAL s.MaturityPeriod MONTH) as MaturityDate, (a.CurrentBalanceCr - a.CurrentBalanceDr) as CurrentBalance from jos_xaccounts a join jos_xmember m on a.member_id=m.id join jos_xschemes s on a.schemes_id=s.id where (s.SchemeType='" . ACCOUNT_TYPE_BANK . "' or s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' or s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' or s.SchemeType='".ACCOUNT_TYPE_DDS."') and a.branch_id=" . Branch::getCurrentBranch()->id . " and  a.member_id=" . $id . " and a.ActiveStatus=1 and a.LockingStatus=0")->result();
        if (!$a) {
            $this->jq->addError($id . ": ", "Oops Not Found");
            $this->jq->flashMessages();
            return;
        }
        $msg = "";
        foreach ($a as $acc) {
            $msg = $acc->MaturityDate . ":" . $acc->CurrentBalance . "<br/>";
            $this->jq->addInfo($acc->id . ":" . $acc->AccountNumber, $msg);
            $this->jq->flashMessages();
        }
    }


     function dealerDetails($id='') {
//        Staff::accessibleTo(USER);
          $id = JRequest::getVar("id");
//        if ($id)
        $a = new Dealer($id);
        if (!$a or $id == ' ') {
            $this->jq->addError($id . ": ", "No Dealer found");
            $this->jq->flashMessages();
            return;
        }
        $msg = $a->DealerName;
        if ($msg == "")
            $msg = $m->Address;
        $this->jq->addInfo($id, $msg);
        $this->jq->flashMessages();
    }

    function loanFromAccount() {
        $list = array();
        $b = Branch::getCurrentBranch();
        $q = $this->db->query("select a.*,m.Name as MemberName, s.Name from jos_xaccounts a join jos_xmember m on a.member_id = m.id join jos_xschemes s on a.schemes_id = s.id join jos_xbranch b on a.branch_id = b.id where a.AccountNumber Like '%" . $this->input->post("term") . "%' AND (s.Name='Bank Accounts' OR s.Name='Cash Account' OR s.Name='Saving Account' OR s.Name='Bank OD' OR s.Name = '" . BRANCH_AND_DIVISIONS . "' OR s.SchemeType = 'CC') AND b.id='$b->id' AND a.LockingStatus<>1 AND a.ActiveStatus<>0 limit 10")->result();
        foreach ($q as $dd) {
            $list[] = array('AccountNumber' => $dd->AccountNumber, 'MemberName' => $dd->MemberName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function test() {

//        $xc = new Scheme(40);
//        $defaultAccounts = json_decode($xc->AgentSponsorCommission, true);
//        echo "<pre>";
//        print_r($defaultAccounts);
//        echo "</pre>";
//        echo '$defaultAccounts[0] => ' . $defaultAccounts[1] . "<br>";
//        foreach ($defaultAccounts as $d) {
//            echo $d . "<br>";
//        }

        $b = new Branch(3);
        $m = Branch::getDefaultMember($b);
        $u = JFactory::getUser($m->netmember_id);
        print_r($u);
    }

}

?>