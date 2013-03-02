<?php

class schemes_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function dashboard() {

        xDeveloperToolBars::getSchemesManagementToolBar();
        $data['schemes'] = new Scheme();
        $data['schemes']->get();
        $this->load->view('schemes.html', $data);
        $this->jq->getHeader();
    }

    function addnewform() {
        global $com_params;
        xDeveloperToolBars::onlyCancel("schemes_cont.dashboard", "cancel", "Add a new Scheme here");
        $Heads = new BalanceSheet();
        $Heads->get();
        $arr = array();
        foreach ($Heads as $h) {
            $arr +=array($h->Head => $h->id);
        }
        $accountTypeArray = explode(",", $com_params->get('ACCOUNT_TYPES'));
        $accountTypeArray = array_merge(array("Select Account Type"), $accountTypeArray);
        $accountTypeArray = array_combine($accountTypeArray, $accountTypeArray);

        $xc = new xConfig("agent");

        //TODO- posting columns... Suppose yearly scheme has to be posted in account in every 6 motnhs
        $this->load->library('form');
//        $this->form->open("1", 'index.php?option=com_xbank&task=schemes_cont.newscheme')
//                ->setColumns(2)
//                ->text("Scheme Name", "name='Name' class='input req-string tooltip' title='Type the name for new Scheme'")
//                ->select("Account Type", "name='SchemeType' class='not-req tooltip' title='Select the type of Scheme' not-req-val='Select Account Type'", $accountTypeArray)
//                ->text("Minimum Balance/Amount", "name='MinLimit' class='input req-numeric tooltip' title='Give minimum balance/amount for the scheme. Type 0 in case of no minimum'")
//                ->text("Maximum Limit", "name='MaxLimit' class='input req-numeric tooltip' title='Give the maximum limit for the scheme. In case of no limit type -1'")
//                ->text("Interest (In %)", "name='Interest' class='input req-numeric tooltip' title='Type interest rate in percentage'")
//                ->select("Premium", "name='PremiumMode' class='not-req tooltip' title='Select the mode of paying Premium' not-req-val='-1'", array('Select Premium Mode' => '-1', 'Not Applicable' => '0', 'Yearly' => 'Y', 'Half Yearly' => 'HF', 'Quarterly' => 'Q', 'Monthly' => 'M', 'Weekly' => 'W', 'Daily' => 'D'))
//                ->select("Interest Mode", "name='InterestMode' class='not-req tooltip' title='Select the mode of interest posting' not-req-val='-1'", array('Select Intrest Mode' => '-1', 'Yearly' => 'Y', 'Half Yearly' => 'HF', 'Quarterly' => 'Q', 'Monthly' => 'M', 'Weekly' => 'W', 'Daily' => 'D'))
// 		->select("Posting Mode","name='PostingMode' class='not-req' not-req-val='-1'",array('Select Intrest Mode'=>'-1','Yearly'=>'Y','Half Yearly' => 'HF','Quarterly'=>'Q','Monthly'=>'M','Weekly'=>'W','Daily'=>'D'))
//                ->_()
//                ->text("Account Commissions(in %)", "name='AccountOpenningCommission' class='input req-string tooltip' title='Give account commissions in %. Give comma separated values starting with 0 in RD Scheme. e.g. 0,20,16,9,7' value='0'")
//                ->text("Number Of Premiums", "name='NumberOfPremiums' class='input req-numeric tooltip' title='Type the value for Number of Premiums in RD & number of EMI in Loan Schemes'")
//                ->select("Active Status", "name='ActiveStatus'", array('Active' => '1', 'InActive' => '0'), 1)
//                ->select("Under Head", "name='Head' class='not-req tooltip' title='Select the head to which Scheme belongs' not-req-val='-1'", $arr)
//                ->checkBox("Check if Processing Fee in %", "name='ProcessingFeesinPercent' class='input' value='1' checked")
//                ->text('Processing Fees', "name='ProcessingFees' class='input req-numeric tooltip' title='Give processing fee in % if Checkbox is checked' value='0'")
//                ->checkBox("Interest To Account", "name='InterestToAnother' class='input' value='1'")
//                ->text("Period of Maturity for FD, MIS, RD, DDS(in months)", "name='MaturityPeriod' class='input tooltip' title='Period of Maturity for FD, MIS, RD, DDS in years'")
////                ->text("Interest % (To Saving Account) for HID", "name='InterestPercentToAnother' class='input tooltip' title='Provide rate of interest for saving account in case of HID Scheme'")
//                ->checkbox("Is Depriciable", "name='isDepriciable' class='input' value='1'")
//                ->text("Depriciation % before September", "name='DepriciationPercentBeforeSep' class='input'")
//                ->text("Depriciation % After September", "name='DepriciationPercentAfterSep' class='input'")
//                ->confirmButton("Confirm", "New Scheme to create", "index.php?option=com_xbank&task=schemes_cont.confirmSchemesCreateForm&format=raw", true)
//                ->submit("Create");
//        $data['contents'] = $this->form->get();
//        JRequest::setVar("layout","addnewscheme");
//        $this->load->view('schemes.html', $data);
//        $this->jq->getHeader();

        $schemeTypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
        foreach ($schemeTypes as $st) {
            include(xBANKSCHEMEPATH . "/" . strtolower($st) . "/" . strtolower($st) . "createschemeform.php");
        }
        $data['contents'] = $this->jq->getTab(1);
        JRequest::setVar("layout", "addnewscheme");
        $this->load->view('schemes.html', $data);
        $this->jq->getHeader();
    }

    function confirmSchemesCreateForm() {
//        Staff::accessibleTo(ADMIN);

        if ((inp("PremiumMode") != "-1" AND inp("PremiumMode") != "0") AND inp("NumberOfPremiums") <= 0) {
            showError("Premiums not defined properly<br>falsefalse");
            return;
        }

        if (inp("SchemeType") == ACCOUNT_TYPE_FIXED || inp("SchemeType") == ACCOUNT_TYPE_RECURRING) {
            if (inp("MaturityPeriod") <= 0 || !is_numeric(inp("MaturityPeriod")) || inp("MaturityPeriod") == "") {
                showError("Maturity Period not defined properly....Please define maturity period in number of months.<br>falsefalse");
                return;
            }
        }
        $head = new BalanceSheet();
        $head->get_by_head(FIXED_ASSETS_HEAD);
        if (inp("Head") == $head->id) {
            if (inp("isDepriciable") == 1) {
                if (inp("DepriciationPercentBeforeSep") == "" || inp("DepriciationPercentAfterSep") == "") {
                    showError("You have selected that fixed asset is Depriciable.<br>Please provide the depriciation % to be applied on FIXED ASSETS before SEPTEMBER and after SEPTEMBER.<br>falsefalse");
                    return;
                }
            }
        }
        echo "<h3>Scheme Name  - " . inp('Name') . "<br/>";
        echo "Account Type - " . inp('SchemeType') . "<br/>";
        echo "Minimum Balance - " . inp('MinLimit') . "<br/>";
        echo "Interest - " . inp('Interest') . "%</h3>";
    }

    function newscheme() {
//        Staff::accessibleTo(ADMIN);
        $exp="";
        try {
            $this->db->trans_begin();
            $sc = new Scheme();
            $sc->Name = inp('Name');
            $sc->MinLimit = inp('MinLimit');
            $sc->MaxLimit = inp('MaxLimit');
            $sc->Interest = inp('Interest');
            $sc->InterestMode = inp('InterestMode');
            $sc->PostingMode = inp('PostingMode');
            $sc->PremiumMode = inp("PremiumMode");
            $sc->AccountOpenningCommission = inp('AccountOpenningCommission');
            $sc->Commission = inp('Commission');
            $sc->NumberOfPremiums = inp("NumberOfPremiums");
            $sc->ActiveStatus = inp('ActiveStatus');
            $sc->ProcessingFees = inp('ProcessingFees');
            $sc->LoanType = inp('LoanType');
            $sc->SchemeType = inp('SchemeType');
            $sc->balance_sheet_id = inp('Head');
            $sc->CreateDefaultAccount = 1;
            $sc->InterestToAnotherAccount = inp("InterestToAnother");
            $sc->MaturityPeriod = inp("MaturityPeriod");
            $sc->branch_id = Staff::getCurrentStaff()->branch_id;
            $sc->InterestToAnotherAccountPercent = inp('InterestPercentToAnother');
            $sc->isDepriciable = inp('isDepriciable');
            $sc->DepriciationPercentBeforeSep = inp('DepriciationPercentBeforeSep');
            $sc->DepriciationPercentAfterSep = inp('DepriciationPercentAfterSep');
            $sc->ProcessingFeesinPercent = inp("ProcessingFeesinPercent");
            $sc->SchemePoints = inp('SchemePoints');
            $sc->CollectorCommissionRate = inp("CollectorCommissionRate");
            $sc->ReducingOrFlatRate = inp("ReducingOrFlatRate");
            $xc = new xConfig("agent");
            if ($xc->getKey("number_of_agent_levels")) {
                $AgentCommissionString = array();
                for ($i = 1; $i <= $xc->getKey("number_of_agent_levels"); $i++) {
                    $AgentCommissionString += array($i => inp("AgentCommissionString$i"));
                }
                $agentSponsorCommission = json_encode($AgentCommissionString);
            }
            $sc->AgentSponsorCommission = $agentSponsorCommission;

            $sc->save();
            log_message('error', __FILE__ . " $sc->Name Scheme saved from" . $this->input->ip_address() . " " . __FUNCTION__);

            $allBranches = Branch::getAllBranches();

            foreach ($allBranches as $branch) {
                $ac = new Account();
                $ac->schemes_id = $sc->id;
                $ac->branch_id = $branch->id;
                $ac->AccountNumber = $branch->Code . SP . inp('Name');
                $ac->member_id = Branch::getDefaultMember($branch)->id;
//                $ac->Agents = Branch::getDefaultAgent($branch);
                $ac->InterestToAccount = 0;
                $ac->DefaultAC = 1;
                $ac->staff_id = Staff::getCurrentStaff()->id;
                $ac->save();
                log_message('error', __FILE__ . "  $ac->AccountNumber Created for Branch " . $branch->Code . " from " . $this->input->ip_address() . " " . __FUNCTION__);
            }


            $xc = new xConfig(inp("SchemeType"));
//            $defaultAccounts = explode(",", $xc->getKey("Default_Accounts"));
            $defaultAccounts = json_decode($xc->getKey("Default_Accounts"), true);
            /*
              $bankType = array();
              $bankType[] = array(INDIRECT_EXPENSES => INTEREST_PAID_ON);
              $bankType[] = array(INDIRECT_EXPENSES => COMMISSION_PAID_ON);

              $loanType = array();
              $loanType[] = array(INDIRECT_INCOME => INTEREST_RECEIVED_ON);
              $loanType[] = array(INDIRECT_INCOME => PROCESSING_FEE_RECEIVED);
              $loanType[] = array(INDIRECT_INCOME => PENALTY_DUE_TO_LATE_PAYMENT_ON);
              $loanType[] = array(INDIRECT_INCOME => FOR_CLOSE_ACCOUNT_ON);
              [{"Indirect Income":"Interest Received On"},{"Indirect Income":"Processing Fee Received On"},{"Indirect Income":"Penalty Due To Late Payment On"},{"Indirect Income":"For Close Account On"}]

              $rdType = array();
              $rdType[] = array(INDIRECT_EXPENSES => COMMISSION_PAID_ON);
              $rdType[] = array(INDIRECT_EXPENSES => INTEREST_PAID_ON);
              $rdType[] = array(PROVISION_SCHEME => COMMISSION_PAYABLE_ON);
              $rdType[] = array(PROVISION_SCHEME => INTEREST_PAYABLE_ON);
              [{"Indirect Expenses":"Commission Paid On"},{"Indirect Expenses":"Interest Paid On"},{"Indirect Expenses":"Collection Charges Paid On"},{"Provision":"Commission Payable On"},{"Provision":"Interest Payable On"},{"Provision":"Collection Payable On"}]

              $ddType = array();
              $ddType[] = array(INDIRECT_EXPENSES => COMMISSION_PAID_ON);
              $ddType[] = array(INDIRECT_EXPENSES => INTEREST_PAID_ON);

              $fdType = array();
              $fdType[] = array(INDIRECT_EXPENSES => COMMISSION_PAID_ON);
              $fdType[] = array(INDIRECT_EXPENSES => INTEREST_PAID_ON);
              $fdType[] = array(PROVISION_SCHEME => INTEREST_PROVISION_ON);
              $rdType[] = array(PROVISION_SCHEME => COMMISSION_PAYABLE_ON);


              $ccType[] = array(INDIRECT_INCOME => INTEREST_RECEIVED_ON);
              $ccType[] = array(INDIRECT_INCOME => PROCESSING_FEE_RECEIVED);

              $arr[ACCOUNT_TYPE_BANK] = $bankType;
              $arr[ACCOUNT_TYPE_LOAN] = $loanType;
              $arr[ACCOUNT_TYPE_RECURRING] = $rdType;
              $arr[ACCOUNT_TYPE_DDS] = $ddType;
              $arr[ACCOUNT_TYPE_FIXED] = $fdType;
              $arr[ACCOUNT_TYPE_CC] = $ccType;

              //             $arr[ACCOUNT_TYPE_RECURRING][]=$LiabilityNoLoan;
              //             $arr[ACCOUNT_TYPE_FIXED][]=$LiabilityNoLoan;
             */

//            foreach($defaultAccounts as $key=>$value){           
//                foreach($value as $keykey=>$valuevalue){
//                    echo $keykey."=>".$valuevalue."<br />";
//                }
//            }
//            return;
            
            if (isset($defaultAccounts)) {
                foreach ($defaultAccounts as $d => $a) {

                    $key = array_keys($a);
                    $key = $key[0];
                    $val = array_values($a);
                    $val = $val[0];


                    $sch = new Scheme();
                    $sch->get_by_name($key);

                    $ac = new Account();
                    $ac->schemes_id = $sch->id;
                    $ac->branch_id = Branch::getCurrentBranch()->id;
                    $ac->AccountNumber = Branch::getCurrentBranch()->Code . SP . $val . SP . inp('Name');
                    $ac->member_id = Branch::getDefaultMember()->id;
//                    $ac->Agents = Branch::getDefaultAgent();
                    $ac->DefaultAC = 1;
                    $ac->InterestToAccount = 0;
                    $ac->staff_id = Staff::getCurrentStaff()->id;
                    log_message('error', __FILE__ . "  $ac->AccountNumber Created for Branch " . $branch->Code . " from " . $this->input->ip_address() . " " . __FUNCTION__);
                    $ac->save();
//		 			TODO - These accounts should be made for all branches because the scheme is available to all branches by default					
//					get all the remaining branches
                    $remBranches = $this->db->query("select * from jos_xbranch where Code <> 'DFL'")->result();

                    foreach ($remBranches as $remB) {
                        $ac = new Account();
                        $ac->schemes_id = $sch->id;
                        $ac->branch_id = $remB->id;
                        $ac->AccountNumber = $remB->Code . SP . $val . SP . inp('Name');
                        $ac->InterestToAccount = 0;
                        $ac->member_id = Branch::getDefaultMember($remB)->id;
//                        $ac->Agents = Branch::getDefaultAgent($remB);
                        $ac->DefaultAC = 1;
                        $ac->staff_id = Staff::getCurrentStaff()->id;
                        $ac->save();
                        log_message('error', __FILE__ . "  $ac->AccountNumber Created for Branch " . $remB->Code . " from " . $this->input->ip_address() . " " . __FUNCTION__);
                    }
//					Make the same account as above with their Branch Code
                }
            }

            log_message('error', __FILE__ . " $sc->Name Scheme commited from " . $this->input->ip_address() . " " . __FUNCTION__);
        } catch (Exception $e) {
            $rollback = true;
            $exp=$e;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("schemes_cont.dashboard", "Scheme Not Added " . $e->getMessage(), "error");
        }
        $this->db->trans_commit();
        re("schemes_cont.dashboard", "Scheme Added");
    }

    function swapschemestatus() {
        $u = JFactory::getUser();
        if ($u->usertype == 'Super Administrator' || $u->usertype == 'Administrator') {
            try {
                $this->db->trans_begin();
                $s = new Scheme();
                $s->where('id', inp('schemeid'))->get();
                $s->published = !$s->published;
                $s->save();
            } catch (Exception $e) {
                $rollback = true;
            }
            if ($this->db->trans_status() === false or $rollback == true) {
                $this->db->trans_rollback();
                re("schemes_cont.dashboard", "Scheme Could Not be Swapped", "error");
            }
            $this->db->trans_commit();
            re('schemes_cont.dashboard', "Scheme" . strtoupper($s->Name) . " is set in " . (($s->published == 1) ? "Active" : "InActive") . " Mode");
        }
        else
            re('schemes_cont.dashboard', "You are not authorized to change the scheme status.", "error");
    }

}