<?php

class report_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function dashboard() {
        xDeveloperToolBars::getReportManagementToolBar();
        $this->load->view("report.html");
        $this->jq->getHeader();
    }

    /**
     * Function generates a generalized report of all balance sheet heads
     * Shows the Total debit and credit balances under a head.
     * Provides a link to {@link schemesLevelDisplay} on each head to view the schemes' details
     */
    function index() {
        //Staff::accessibleTo(USER);

        $arr = array();
        $Heads = new BalanceSheet();
        $Heads->get();
        //$Heads = Doctrine::getTable("BalanceSheet")->findAll();
        $HeadsToTake = array("Expenses", "Income");
        $i = 1;
        foreach ($Heads as $h) {
// 			if(!in_array($h->Head,$HeadsToTake)) continue;
            $TotalDR = 0;
            $TotalCR = 0;
            $Schemes = new Scheme();
            $Schemes->where('balance_sheet_id', $h->id)->get();
            // $Schemes = Doctrine::getTable("Schemes")->findByBalance_sheet_id($h->id);
            foreach ($Schemes as $s) {
                $Accounts = new Account();
                $Accounts->where('branch_id', Branch::getCurrentBranch()->id)->where('schemes_id', $s->id);
                //$Accounts = Doctrine::getTable("Accounts")->findByBranch_idAndSchemes_id(Branch::getCurrentBranch()->id, $s->id);
                foreach ($Accounts as $a) {
                    $TotalDR += ( $a->CurrentBalanceDr);
                    $TotalCR += ( $a->CurrentBalanceCr);
                }
            }
            $arr[] = array("Head_id" => $h->id, "Sno" => $i++, "Head" => $h->Head, "DR" => $TotalDR, "CR" => $TotalCR);
// 			$arr[] = array("Head_id"=>$h->id,"Head"=>$h->Head,"TotalDR"=>$TotalDR,"TotalCR"=>$TotalCR);
        }
        $data['results'] = $arr;
        $data['results'] = $arr;
//        $data['backURL'] = "";
        $data['contents'] = $this->load->view('pandl', $data, true);
        $this->load->view('template', $data);
    }

    /**
     *
     * @param <type> $Head
     * Function shows details of all schemes under a particular balance sheet head
     * Shows total debit and credit balance of all the accounts under each scheme
     * Provides a link to {@link accountLevelDisplay} on each scheme to view the accounts under a particulsr scheme
     */
    function schemesLevelDisplay($Head='') {
        // Staff::accessibleTo(USER);
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "");
        $h = JRequest::getVar('Head');
        $arr = array();
        $this->session->set_userdata("Head", $h);
        $Head = new BalanceSheet($h);
//        $Head->where('id', $h)->get();
        //$Head->get_by_id($Head);
        //$Head = Doctrine::getTable("BalanceSheet")->find($Head);
        $Schemes = new Scheme();
        $Schemes->where('ActiveStatus',1);
        $Schemes->where('balance_sheet_id', $Head->id)->get();
        //$Schemes->get_by_balance_sheet_id($Head->id);
        //$Schemes = Doctrine::getTable("Schemes")->findByBalance_sheet_id($Head->id);
        $i = 1;
        foreach ($Schemes as $s) {
            $data['TotalDR'] = 0;
            $data['TotalCR'] = 0;
            $Accounts = new Account();
            $Accounts->where('branch_id', Branch::getCurrentBranch()->id)->where('schemes_id', $s->id)->get();
            //$Accounts = Doctrine::getTable("Accounts")->findByBranch_idAndSchemes_id(Branch::getCurrentBranch()->id, $s->id);
            foreach ($Accounts as $a) {
                $data['TotalDR'] += ( $a->CurrentBalanceDr);
                $data['TotalCR'] += ( $a->CurrentBalanceCr);
            }
            $arr[] = array("Head_id" => $s->id, "Sno" => $i++, "Head" => $s->Name, "DR" => $data['TotalDR'], "CR" => $data['TotalCR']);
//			$arr[] = array("Head_id"=>$s->id,"Head"=>$s->Name,"TotalDR"=>$TotalDR,"TotalCR"=>$TotalCR);
        }

        $data['results'] = $arr;
//        $data['backURL'] = "index.php?//mod_pandl/pandl_cont";
// 		print_r($data['results']);
        JRequest::setVar('layout','pandl_schemes');
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $scheme
     * Function to show the details of all the accounts under a particular scheme
     * Provides a link to {@link accountTransactions} to show the transactions of each account
     */
    function accountLevelDisplay() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "");
        //Staff::accessibleTo(USER);
        $scheme = JRequest::getVar("id");
        $arr = array();
        $this->session->set_userdata("Scheme", $scheme);
// 		$Head=Doctrine::getTable("BalanceSheet")->find($Head);
// 		$Schemes=Doctrine::getTable("Schemes")->findByBalance_sheet_id($Head->id);
// 		foreach($Schemes as $s){
        $data['TotalDR'] = 0;
        $data['TotalCR'] = 0;
        $i = 1;
        $Accounts = new Account();
        $Accounts->where('branch_id', Branch::getCurrentBranch()->id);
        $Accounts->where('schemes_id', $scheme)->get();
        $todate = date("Y-m-d", (strtotime($this->session->userdata("todate")) + (60 * 60 * 24)));
        //$Accounts = Doctrine::getTable("Accounts")->findByBranch_idAndSchemes_id(Branch::getCurrentBranch()->id, $scheme);
        foreach ($Accounts as $a) {
            $openingBalances = $a->getOpeningBalance($this->session->userdata("fromdate"));
            $q = $this->db->query("select sum(amountCr) as CR, sum(amountDr) as DR from jos_xtransactions where accounts_id = $a->id and created_at >= '".$this->session->userdata("fromdate")."' and created_at < '$todate' ")->row();
            $data['TotalCR'] += ( $q->CR);
            $data['TotalDR'] += ( $q->DR);
            $arr[] = array("Account" => $a->id, "Sno" => $i++, "Transaction" => $a->AccountNumber . " [ " . $a->Member->Name . " ] ", "DR" =>  $q->DR, "CR" =>  $q->CR, "AccountDisplayName" => $a->AccountDisplayName);
//                                $arr[] = array("Head_id"=>$a->id,"Head"=>$a->AccountNumber,"TotalDR"=>$a->CurrentBalanceDr,"TotalCR"=>$a->CurrentBalanceCr);
        }
//			$arr[] = array("Head_id"=>$a->id,"Head"=>$a->AccountNumber,"TotalDR"=>$a->CurrentBalanceDr,"TotalCR"=>$a->CurrentBalanceCr);
// 		}

        $data['results'] = $arr;
        $data['forScheme'] = $scheme;
//        $data['backURL'] = "index.php?//mod_pandl/pandl_cont/schemesLevelDisplay/" . $this->session->userdata("Head");
// 		print_r($data['results']);
//        $data['contents'] = $this->load->view('pandl_accounts', $data, true);
//        $this->load->view('template', $data);
        JRequest::setVar("layout", "pandl_accounts");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $account
     * Function shows the transactions carried out on each account
     * Provides a link to {@link transactionDetails} on each transaction to show the details of each transaction
     */
    //function accountTransactions($account) {
    function accountTransactions() {
        // Staff::accessibleTo(USER);
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "");
        $account = JRequest::getVar("id");
        $arr = array();
        $this->session->set_userdata("Account", $account);
        //     $Transactions=Doctrine::getTable("Transactions")->findByAccounts_idOrAccounts_id_to($account,$account);
        //$Transactions = Doctrine::getTable("Transactions")->findByAccounts_id($account);
        $Transactions = new Transaction();
        $todate = date("Y-m-d", (strtotime($this->session->userdata("todate")) + (60 * 60 * 24)));
        $Transactions->where('accounts_id', $account)->where("created_at >=", $this->session->userdata("fromdate"))->where("created_at <", $todate)->get();
        //$Acc = Doctrine::getTable("Accounts")->find($account);
        $Acc = new Account();
        $Acc->where('id', $account)->get();
        $data['TotalDR'] = $Acc->OpeningBalanceDr;
        $data['TotalCR'] = $Acc->OpeningBalanceCr;
        $data['AccountNumber'] = $Acc->AccountNumber;
        $openingBalances = $Acc->getOpeningBalance($this->session->userdata("fromdate"));
        $arr[] = array("VoucherNumber" => 0, "Date" => '', "Transaction" => "OPENNING BALANCE", "DR" => $openingBalances["DR"], "CR" => $openingBalances["CR"], "referenceAccount" => "");
        foreach ($Transactions as $t) {
//                if($t->accounts_id == $account){
//                  //  $dr=$t->amount;
//                    $dr=$t->amountDr;
//                    $cr=0;
//                }else{
//                 //   $cr=$t->amount;
//                    $cr=$t->amountCr;
//                    $dr=0;
//                }
            $dr = $t->amountDr;
            $cr = $t->amountCr;
            $data['TotalDR'] += $t->amountDr;
            $data['TotalCR'] += $t->amountCr;
            $refAcc = new Account();
            $refAcc->where('id', $t->reference_account)->get();
            //$refAcc = Doctrine::getTable("Accounts")->find($t->reference_account);
            $refAcc = ($refAcc == null) ? "" : $refAcc->AccountNumber;
            $arr[] = array("VoucherNumber" => $t->voucher_no, "Date" => $t->created_at, "Transaction" => $t->Narration, "DR" => $dr, "CR" => $cr, "referenceAccount" => $refAcc);
        }
        $data['results'] = $arr;
        $data['account'] = $account;
//        $data['backURL'] = "index.php?//mod_pandl/pandl_cont/accountLevelDisplay/" . $this->session->userdata("Scheme");
        JRequest::setVar("layout", "pandl_accountTransactions");
        $data['contents'] = $this->load->view('report.html', $data);
        //$data['contents'] = $this->load->view('pandl_accountTransactions', $data, true);
        echo "<div id='accTRansactiondiv'>" . $data['contents'] . "</div>";
        //$this->jq->getHeader(true, "accTRansactiondiv");
        $this->jq->getHeader();
//        $this->load->view('template', $data);
    }

    /**
     *
     * @param <type> $voucher
     * Function shows transaction details by accepting voucher number as a parameter
     */
    function transactionDetails() {
        //function transactionDetails($voucher, $foraccount) {
        //Staff::accessibleTo(USER);
        $voucher = JRequest::getVar("vn");
       $foraccount = JRequest::getVar("id");
        $arr = array();
        $Transactions = new Transaction();
        $Transactions->where('voucher_no', $voucher);
        $Transactions->where('branch_id', Branch::getCurrentBranch()->id)->get();
        //$Transactions = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucher, Branch::getCurrentBranch()->id);
        $i = 1;
        foreach ($Transactions as $t) {
            /** Find whether the transaction is one to many or many to one
             *
             */
//                  $q=Doctrine::getTable("Transactions")->createQuery()
//                            ->where("amountDr > 0 AND voucher_no = ? ",array($voucher))->execute();
//                  $DrCount=$q->count();
//            $amount = 0;
//            if ($t->amountDr > 0) {
//                $drAccount = Doctrine::getTable("Accounts")->find($t->accounts_id);
//                $dr = $drAccount->AccountNumber;
//                $amount = $t->amountDr;
//            } else {
//                $dr = "";
//
//                //  $amount=0;
//            }
//            if ($t->amountCr > 0) {
//                $crAccount = Doctrine::getTable("Accounts")->find($t->accounts_id);
//                $cr = $crAccount->AccountNumber;
//                $amount = $t->amountCr;
//            } else {
//                $cr = "";
//
//                //     $amount=0;
//            }
//                $drAccount=Doctrine::getTable("Accounts")->find($t->accounts_id);
//                $crAccount=Doctrine::getTable("Accounts")->find($t->accounts_id_to);
            //$account = Doctrine::getTable("Accounts")->find($t->accounts_id);
            $account = new Account($t->accounts_id);
//            $account->where('id',$t->accounts_id)->get();
            $data['accountID'] = $account->id;
            $arr[] = array("Sno" => $i++, "Voucher" => $t->voucher_no, "Account" => $account->AccountNumber, "DR" => $t->amountDr, "CR" => $t->amountCr);
        }
        $data['foraccount'] = $foraccount;
        $data['results'] = $arr;
//        $data['backURL'] = "index.php?//mod_pandl/pandl_cont/accountTransactions/" . $this->session->userdata("Account");
        JRequest::setVar("layout", "pandl_transactionDetails");
        $data['contents'] = $this->load->view('report.html', $data, true);
        //$data['contents'] = $this->load->view('pandl_transactionDetails', $data, true);
        echo "<div id='transactionDetailsDIV'>" . $data['contents'] . "</div>";
        //$this->jq->getHeader(true, "transactionDetailsDIV");
        $this->jq->getHeader();
//        $this->load->view('template', $data);
    }

    /**
     * <b>FORM</b> is created to generate Profit & Loss A/c
     * Sends the link to {@link pandl_report}
     */
    function pandlForm() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "View PandL Reports");
        //Staff::accessibleTo(USER);
        //setInfo("PROFIT & LOSS ACCOUNT", "");
        $this->load->library("form");
        $form = $this->form->open("pandl", "index.php?option=com_xbank&task=report_cont.pandl_report")
                        ->setColumns(2)
                        ->dateBox("P & L From", "name='fromDate' class='input'")
                        ->dateBox("P & L till", "name='toDate' class='input'")
                        ->checkbox("Print To PDF", "name='printToPDF' value=1");
        if (Branch::getCurrentBranch()->Code == "DFL") {
            //                    $branchNames=$this->db->query("select Name from branch")->result();
            $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                            ->_();
        } else {
            $b = Branch::getCurrentBranch()->id;
            $form = $form->hidden("", "name='BranchId' value='$b'");
        }
        $form = $form->submit("Go");
        $data['contents'] = $this->form->get();
        //JRequest::setVar('layout','pandl');
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }

    /**
     * Actual Profit & Loss A/c is generated here
     *
     * STEPS
     * - Get income and expenses heads for P&L A/c
     * - Get all the schemes under each head
     * - Get the sum of credit and debit balance of accounts under each scheme
     * - Generate the Profit & Loss A/c
     */
    function pandl_report() {
        //Staff::accessibleTo(USER);
        xDeveloperToolBars::onlyCancel("report_cont.pandlForm", "cancel", "PandL Report : " . inp('fromDate') . " TO " . inp('toDate'));
        $this->session->set_userdata("fromdate", inp("fromDate"));
        $this->session->set_userdata("todate", inp("toDate"));
        if (inp("toDate") < inp("fromDate")) {
            // showError("To date can't be less than From date");
            $error = "To date can't be less than From date";
            re("report_cont.pandlForm", $error);
        }

        /*          $arr=array();
          $sc_arr=array();
          $Heads=Doctrine::getTable("BalanceSheet")->findAll();
          $HeadsToTake=array("Expenses","Income");
          $i=1;
          $where="";
          foreach($Heads as $h){
          if(!in_array($h->Head,$HeadsToTake)) continue;
          $TotalDR=0;
          $TotalCR=0;
          $Schemes=Doctrine::getTable("Schemes")->findByBalance_sheet_id($h->id);
          foreach($Schemes as $s){
          $arr=array();
          // 					$Accounts=Doctrine::getTable("Accounts")->findByBranch_idAndSchemes_id(Branch::getCurrentBranch()->id ,$s->id);
          $q="select * from accounts a where a.schemes_id=".$s->id." and a.updated_at between '".inp("fromDate")."' and '".inp("toDate")."' and a.ActiveStatus = 1";
          if(inp("BranchId")!="%")
          $where .=" and a.branch_id=".inp("BranchId");
          $q .=$where;
          $Accounts=$this->db->query($q)->result();
          foreach($Accounts as $a){
          $TotalDR = ($a->CurrentBalanceDr);
          $TotalCR = ($a->CurrentBalanceCr);
          $balance = abs($TotalCR - $TotalDR);// ($TotalDR - $TotalCR >= 0) ? ($TotalDR - $TotalCR) : ($TotalCR - $TotalDR);
          $AccountNumber = $a->AccountNumber;
          $arr[]=array("Account"=>$AccountNumber,"DR"=>$TotalDR,"CR"=>$TotalCR,"Balance"=>$balance);
          }
          $sc_arr +=array($s->Name => $arr);
          }

          }
         *
         */
//                $data['scheme']=$Schemes->Name;
        $balance = $this->pandlBalance(inp("fromDate"), inp("toDate"), inp("BranchId"), false,false);
        $data['pandlBalance'] = $balance;
        $data['results'] = $this->balanceSheetarray(inp("fromDate"), inp("toDate"), inp("BranchId"));

        //$data['results']=$sc_arr;
        if (inp("printToPDF") == null) {
            JRequest::setVar("layout", "pandlreportshow");
            $this->load->view('report.html', $data);
            //$data['contents']=$this->load->view('report.html',$data);
            //JRequest::setVar("layout", "pandlreport");
            //$this->load->view('report.html', $data);
            $this->jq->getHeader();
            //$data['contents'] = $this->load->view('pandl_report', $data, true);
            //$this->load->view('template', $data);
        } else {
            JRequest::setVar("layout", "pandlreportshow");
            $this->load->view('report.pdf', $data);
        }
    }

    /**
     * <b>FORM</b> is created to generate a balance sheet
     * Sends the link to {@link balanceSheet}
     */
    function balanceSheetForm() {
        //Staff::accessibleTo(USER);
        //setInfo("BALANCE SHEET", "");
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "View BalanceSheets");

        $this->load->library("form");
        $form = $this->form->open("balanceSheet", "index.php?option=com_xbank&task=report_cont.getBalanceSheet")
                        ->setColumns(2)
                        ->dateBox("Balance Sheet From", "name='fromDate' class='input'")
                        ->dateBox("Balance Sheet till", "name='toDate' class='input'")
                        ->checkbox("Print To PDF", "name='printToPDF' value=1");
        if (Branch::getCurrentBranch()->Code == "DFL") {
            //                    $branchNames=$this->db->query("select Name from branch")->result();
            $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                            ->_();
        } else {
            $b = Branch::getCurrentBranch()->id;
            $form = $form->hidden("", "name='BranchId' value='$b'");
        }
        $form = $form->submit("Go");
        $data['contents'] = $this->form->get();
        //JRequest::setVar('layout','balancesheet');
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    /**
     * Actual balance sheet is generated here
     *
     * STEPS
     * - Get all the heads for balance sheet
     * - Get all the schemes under each head
     * - Get the sum of credit and debit balance of accounts under each scheme
     * - Get the value of Profit & Loss A/c
     * - Generate the balance sheet
     */
    function balanceSheet() {
        //Staff::accessibleTo(USER);
        xDeveloperToolBars::onlyCancel("report_cont.balanceSheetForm", "cancel", "BalanceSheet " . inp('fromDate') . " To " . inp('toDate'));

        if (inp("toDate") < inp("fromDate")) {
            $error = "To date can't be less than From date";
            //setError("To date can't be less than From date", "");
            re("report_cont.balanceSheetForm", $error);
        }

        $this->session->set_userdata("fromdate", inp("fromDate"));
        $this->session->set_userdata("todate", inp("toDate"));
        $balance = $this->pandlBalance(inp("fromDate"), inp("toDate"), inp("BranchId"));
        $data['pandlBalance'] = $balance;
        $data['results'] = $this->balanceSheetarray(inp("fromDate"), inp("toDate"), inp("BranchId"));
//		$data['backURL']="";


        if (inp("printToPDF") == null) {
            JRequest::setVar("layout", "balancesheetview");
            $this->load->view('report.html', $data);
            $this->jq->getHeader();
//            $data['contents'] = $this->load->view('balancesheetview1', $data, true);
//            $this->load->view('template', $data);
        } else {
            JRequest::setVar("layout", "balancesheetview");
            $this->load->view('report.pdf', $data);
        }
    }

    function trialbalanceForm() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "View Trial Balance ");

        // Staff::accessibleTo(USER);
        //setInfo("TRIAL BALANCE", "");
        $this->load->library("form");
        $form = $this->form->open("trialbalance", "index.php?option=com_xbank&task=report_cont.trialbalance")
                        ->setColumns(2)
                        ->dateBox("Trial Balance From", "name='fromDate' class='input'")
                        ->dateBox("Trial Balance Till", "name='toDate' class='input'")
                        ->checkbox("Print To PDF", "name='printToPDF' value=1");
        if (Branch::getCurrentBranch()->Code == "DFL") {
            //                    $branchNames=$this->db->query("select Name from branch")->result();
            $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                            ->_();
        } else {
            $b = Branch::getCurrentBranch()->id;
            $form = $form->hidden("", "name='BranchId' value='$b'");
        }
        $form = $form->submit("Go");
        $data['contents'] = $this->form->get();
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    function trialbalance() {
        xDeveloperToolBars::onlyCancel("report_cont.trialbalanceForm", "cancel", "Trial Balance " . inp('fromDate') . " To " . inp('toDate'));

        //Staff::accessibleTo(USER);

        if (inp("toDate") < inp("fromDate")) {
            $error = "To date can't be less than From date";
            re("report_cont.trialbalanceForm", $error);
        }

        $arr = array();
        $sc_arr = array();
        $Heads = new BalanceSheet();
        $Heads->get();
        //$Heads = Doctrine::getTable("BalanceSheet")->findAll();
        $HeadsToTake = array("Expenses", "Income");
        $i = 1;
        $where = "";
        foreach ($Heads as $h) {
            if (!in_array($h->Head, $HeadsToTake))
                continue;
            $TotalDR = 0;
            $TotalCR = 0;
            $Schemes = new Scheme();
            $Schemes->where('balance_sheet_id', $h->id)->get();
            //$Schemes->get_by_balance_sheet_id($h->id);
            //$Schemes = Doctrine::getTable("Schemes")->findByBalance_sheet_id($h->id);
            foreach ($Schemes as $s) {
                $arr = array();
// 					$Accounts=Doctrine::getTable("Accounts")->findByBranch_idAndSchemes_id(Branch::getCurrentBranch()->id ,$s->id);
                $q = "select * from jos_xaccounts a where a.schemes_id=" . $s->id . " and a.updated_at between '" . inp("fromDate") . "' and '" . inp("toDate") . "' and a.ActiveStatus = 1";
                if (inp("BranchId") != "%")
                    $where .=" and a.branch_id=" . inp("BranchId");
                $q .=$where;
                $Accounts = $this->db->query($q)->result();
                foreach ($Accounts as $a) {
                    $TotalDR = ($a->CurrentBalanceDr);
                    $TotalCR = ($a->CurrentBalanceCr);
                    $balance = ($TotalDR - $TotalCR >= 0) ? ($TotalDR - $TotalCR) : ($TotalCR - $TotalDR);
                    $AccountNumber = $a->AccountNumber;
                    $arr[] = array("Account" => $AccountNumber, "DR" => $TotalDR, "CR" => $TotalCR, "Balance" => $balance);
                }
                $sc_arr +=array($s->Name => $arr);
            }
        }


        foreach ($Heads as $h) {
            $arr = array();
            $Schemes = new Scheme();
            $Schemes->where('balance_sheet_id', $h->id)->get();
            //$Schemes = Doctrine::getTable("Schemes")->findByBalance_sheet_id($h->id);
            foreach ($Schemes as $s) {
                $TotalDR = 0;
                $TotalCR = 0;
                $where = "";

// 					$Accounts=Doctrine::getTable("Accounts")->findByBranch_idAndSchemes_id(Branch::getCurrentBranch()->id ,$s->id);
                $q = "select * from jos_xaccounts a where a.schemes_id=" . $s->id . " and a.updated_at between '" . inp("fromDate") . "' and '" . inp("toDate") . "' and a.ActiveStatus = 1";
                if (inp("BranchId") != "%")
                    $where .=" and a.branch_id=" . inp("BranchId");
                $q .=$where;
                $Accounts = $this->db->query($q)->result();
                foreach ($Accounts as $a) {
                    $TotalDR += ( $a->CurrentBalanceDr);
                    $TotalCR += ( $a->CurrentBalanceCr);
                }
                $arr[] = array("Head" => $h->Head, "SchemeName" => $s->Name, "DR" => $TotalDR, "CR" => $TotalCR);
            }
//                        $arr[]=array("Head_id"=>$h->id,"Sno"=>$i++,"Head"=>$h->Head,"DR"=>$TotalDR,"CR"=>$TotalCR);
// 			$arr[] = array("Head_id"=>$h->id,"Head"=>$h->Head,"TotalDR"=>$TotalDR,"TotalCR"=>$TotalCR);
            $sc_arr +=array($h->Head => $arr);
        }

//                $data['scheme']=$Schemes->Name;


        $data['results'] = $sc_arr;
        if (inp("printToPDF") == null) {
            JRequest::setVar("layout", "trial_balance");
            $this->load->view("report.html", $data);
            $this->jq->getHeader();
//            $data['contents'] = $this->load->view('trial_balance', $data, true);
//            $this->load->view('template', $data);
        } else {
            JRequest::setVar("layout", "trial_balance");
            $this->load->view("report.pdf", $data);
        }
    }

    function pandlBalance($dateFrom, $dateTo, $branch_id, $showOpeningBalance=true,$isBalanceSheet=true) {
        $pnlarr = $this->balanceSheetarray($dateFrom, $dateTo, $branch_id);
        $HeadExpense = "Expenses";
        $HeadIncome = "Income";
        $HeadsToTake = array("Expenses", "Income");
        $balanceIncome = 0;
        $balanceExpenses = 0;
        $balance = 0;

        foreach ($pnlarr as $head => $scheme) {

            if (!in_array($head, $HeadsToTake))
                continue;
            foreach ($scheme as $sc => $account) {
                $total = 0;
                if ($head == $HeadExpense) {
                    foreach ($account["Account"] as $acc) {
                        if ($showOpeningBalance === true && $isBalanceSheet)
                            $total += ( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']);
                        else
                            $total += ( $acc['Debit'] - $acc['Credit']);
                    }
                    $balanceExpenses +=$total;
                }
                if ($head == $HeadIncome) {
                    foreach ($account["Account"] as $acc) {
                        if ($showOpeningBalance === true && $isBalanceSheet)//$dateTo <= "2012-03-31")
                            $total += ( $acc['OpeningBalanceCr'] - $acc['OpeningBalanceDr']) + ($acc['Credit'] - $acc['Debit']);
                        else
                            $total += ( $acc['Debit'] - $acc['Credit']);
                    }
                    $balanceIncome +=$total;
                }
            }
        }
        return abs($balanceIncome) - abs($balanceExpenses);
    }

    function balanceSheetarray($dateFrom, $dateTo, $branch_id) {
        //set_time_limit(5000);
        //Staff::accessibleTo(USER);

        if ($dateTo < $dateFrom) {
//            $error = "To date can't be less than From date";
            //setError("To date can't be less than From date", "");
//            re("report_cont.balanceSheetForm", $error);
            $dateTo = date("Y-m-d");
        }

        $arr = array();
        $acc_arr = array();
        $sc_arr = array();
//                $temparr=array();
        $Heads = new BalanceSheet();
        $Heads->get();
        //$Heads = Doctrine::getTable("BalanceSheet")->findAll();
        $i = 1;
        $balance = 0;
        $where = "";
        foreach ($Heads as $h) {
            $arr = array();
            $Schemes = new Scheme();
            $Schemes->where('balance_sheet_id', $h->id);
            $Schemes->get();
            //$Schemes = Doctrine::getTable("Schemes")->findByBalance_sheet_id($h->id);
            foreach ($Schemes as $s) {
                $TotalDR = 0;
                $TotalCR = 0;
                $where = "";
//                if ($s['id'] == 9) {
//                    $xysss = 234234;
//                }
                $acc_arr = array();

                $q = "select * from jos_xaccounts a where a.schemes_id=" . $s->id . " and (a.ActiveStatus = 1 or a.affectsBalanceSheet = 1)";
                if (inp("BranchId") != "%")
                    $where .=" and a.branch_id=" . $branch_id;
                $q .=$where;
                $Accounts = $this->db->query($q)->result();
                foreach ($Accounts as $a) {
                    $openingBalanceCr = $this->db->query("select sum(t.amountCr) as Balance from jos_xtransactions t where t.accounts_id = $a->id and t.created_at < '" . $dateFrom . "'")->row()->Balance;
                    $openingBalanceCr += $a->OpeningBalanceCr;
                    $openingBalanceDr = $this->db->query("select sum(t.amountDr) as Balance from jos_xtransactions t where t.accounts_id = $a->id and t.created_at < '" . $dateFrom . "'")->row()->Balance;
                    $openingBalanceDr += $a->OpeningBalanceDr;
                    $q = "select sum(t.amountDr) as Debit from jos_xtransactions t where t.accounts_id = $a->id and t.created_at between '" . $dateFrom . "' and DATE_ADD('" . $dateTo . "',INTERVAL +1 DAY) ";
                    $Debit = $this->db->query($q)->row()->Debit;
                    $q = "select  sum(t.amountCr) as Credit from jos_xtransactions t where t.accounts_id = $a->id and t.created_at between '" . $dateFrom . "' and DATE_ADD('" . $dateTo . "',INTERVAL +1 DAY) ";
                    $Credit = $this->db->query($q)->row()->Credit;
                    if (($Debit + $openingBalanceDr) == 0 and ($Credit + $openingBalanceCr) == 0)
                        continue;

                    $acc_arr[] = array("AccountNumber" => $a->AccountNumber, "Debit" => $Debit, "Credit" => $Credit, "OpeningBalanceCr" => $openingBalanceCr, "OpeningBalanceDr" => $openingBalanceDr, "AccountId" => $a->id);
                }
                $arr[] = array("SchemeName" => $s->Name, "SchemeId" => $s->id, "SchemeType" => $s->SchemeType, "Account" => $acc_arr);
            }
            $sc_arr +=array($h->Head => $arr);
        }
        return $sc_arr;


//                echo "<pre>";
//                print_r($temparr);
//                echo "</pre>";
    }

    function accountStatementForm() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "View Account Statement");

        //Staff::accessibleTo(USER);
        $b = Branch::getCurrentBranch();
        // setInfo("ACCOUNT DETAILS", "");
        $this->load->library("form");
        $form = $this->form->open("accountdetails", "index.php?option=com_xbank&task=report_cont.accountStatement")
                        ->setColumns(2)
                        //->lookupDB("Account number", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                        ->lookupDB("Account number : $b->Code - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=report_cont.AccountNumber&format=raw", array("a" => "b"), array("id", "AccountNumber", "MName"), "AccountNumber")
                        ->dateBox("Transactions from", "name='fromDate' class='input'")
                        ->dateBox("Transactions till", "name='toDate' class='input'");
        $form = $form->submit("Go");
        $data['contents'] = $this->form->get();
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
        //$this->load->view("template", $data);
    }

    function accountStatement() {

        xDeveloperToolBars::onlyCancel("report_cont.accountstatementform", "cancel", "Account Statement for " . inp('AccountNumber'));

        $trans_arr = array();
        $ac = new Account();
        $ac->where('AccountNumber', inp("AccountNumber"))->get();
        if (!$ac->result_count())
            re("report_cont.accountstatementform","The Account Number ".inp("AccountNumber")." does not exist. Try Again","error");
        if (inp("fromDate") && inp("toDate")) {

            $query = $this->db->query("select * from jos_xtransactions t where t.accounts_id =" . $ac->id . " and created_at between '" . inp("fromDate") . "' and DATE_ADD('" . inp("toDate") . "',INTERVAL +1 DAY) order by created_at")->result();
        } else {
            $query = $this->db->query("select * from jos_xtransactions t where t.accounts_id =" . $ac->id . " order by created_at")->result();
        }

        $openingBalances = $ac->getOpeningBalance(inp("fromDate"));
        $data['openingbalance'] = $openingBalances["DR"] - $openingBalances["CR"];
        $data['transactions'] = $query;
        JRequest::setVar("layout", "accountstatement");
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    function balancesheetcondensed() {
        $CI = & get_instance();
        $fromdate = $CI->session->userdata('fromdate');
        $todate = $CI->session->userdata('todate');
        $balance = $this->pandlBalance($fromdate, $todate, Branch::getCurrentBranch()->id);
        $data['pandlBalance'] = $balance;
        $data['results'] = $this->balanceSheetarray($fromdate, $todate, Branch::getCurrentBranch()->id);
        JRequest::setVar("layout",'balancesheetcondensed');
        $this->load->view('template', $data);
    }

    function cashBookForm() {
        xDeveloperToolBars::onlyCancel("report_cont.AccountBook", "cancel", "View CashBooks");

        // Staff::accessibleTo(USER);
        //setInfo("CASH BOOK", "");
        $this->load->library("form");
        $form = $this->form->open("one", "index.php?option=com_xbank&task=report_cont.cashBook")
                        ->dateBox("Date From", "name='dateFrom' class='input'")
                        ->dateBox("Date To", "name='dateTo' class='input'")
                        ->checkbox("Print To PDF", "name='printToPDF' value=1")
                        ->submit("go");
        $data['contents'] = $this->form->get();
        //JRequest::setVar("layout","cashbookform");
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    function cashBook() {
        //Staff::accessibleTo(USER);
        $b = Branch::getCurrentBranch()->id;
        xDeveloperToolBars::onlyCancel("report_cont.cashBookForm", "cancel", "CashBook of : " . inp('dateFrom') . " TO  " . inp('dateTo'));

        $b = Branch::getCurrentBranch()->id;
 	$result = $this->db->query(" SELECT DRTransaction.created_at as `Date`,if(DRTransaction.amountDr > 0 ,CONCAT('TO ', if(m.`Name` like '%Default%',a.AccountNumber,m.`Name`)),CONCAT('BY ', if(m.`Name` like '%Default%',a.AccountNumber,m.`Name`))) as Particulars, DRTransaction.Narration as Narration, DRTransaction.voucher_no as Voucher_no, DRTransaction.display_voucher_no as Display_Voucher_no, DRTransaction.amountDr AS Debit, DRTransaction.amountCr as Credit
                FROM jos_xtransactions AS DRTransaction
                JOIN jos_xtransactions AS CRTransaction ON DRTransaction.voucher_no = CRTransaction.voucher_no
                JOIN jos_xaccounts ON DRTransaction.accounts_id = jos_xaccounts.id
                JOIN jos_xaccounts a ON CRTransaction.accounts_id = a.id
                JOIN jos_xmember m ON m.id = jos_xaccounts.member_id
                JOIN jos_xschemes ON jos_xaccounts.schemes_id = jos_xschemes.id
                WHERE
                DRTransaction.branch_id = $b AND
                CRTransaction.branch_id = $b AND
                jos_xschemes.`Name` = 'Cash Account' AND
                DRTransaction.amountDr = CRTransaction.amountCr AND DRTransaction.created_at BETWEEN '".inp("dateFrom")."' AND DATE_ADD('".inp("dateTo")."', INTERVAL +1 DAY) ")->result();

       
        
        /*
        $result = $this->db->query("SELECT * FROM(
                                            SELECT
                                                            DRTransaction.created_at as `Date`,
                                                            CONCAT('TO  ', if(m.`Name` like '%Default%',a.AccountNumber,m.`Name`)) as Particulars,
                                                            DRTransaction.Narration as Narration,
                                                            DRTransaction.voucher_no as Voucher_no,
                                                            DRTransaction.amountDr AS Debit,
                                                            '' as Credit
                                            FROM
                                                    jos_xtransactions AS DRTransaction
                                            INNER JOIN jos_xtransactions AS CRTransaction ON DRTransaction.voucher_no = CRTransaction.voucher_no
                                            INNER JOIN jos_xaccounts ON DRTransaction.accounts_id = jos_xaccounts.id
                                            INNER JOIN jos_xaccounts a ON CRTransaction.accounts_id = a.id
                                            INNER JOIN jos_xmember m ON m.id = a.member_id
                                            INNER JOIN jos_xschemes ON jos_xaccounts.schemes_id = jos_xschemes.id
                                            WHERE
                                                    DRTransaction.branch_id = $b
                                            AND CRTransaction.branch_id = $b
                                            AND jos_xschemes.`Name` = '" . CASH_ACCOUNT_SCHEME . "'
                                            AND DRTransaction.amountDr = CRTransaction.amountCr
                                            AND DRTransaction.amountDr > 0
                                            And DRTransaction.created_at BETWEEN '" . inp("dateFrom") . "' AND '" . inp("dateTo") . "'

                                            UNION
                                                    SELECT
                                                            CRTransaction.created_at as `Date`,
                                                            CONCAT('BY  ', if(m.`Name` like '%Default%',a.AccountNumber,m.`Name`)) as Particulars,
                                                            CRTransaction.Narration as Narration,
                                                            CRTransaction.voucher_no as Voucher_no,
                                                            '' as Debit,
                                                            CRTransaction.amountCr as Credit

                                                    FROM
                                                            jos_xtransactions AS DRTransaction
                                                    INNER JOIN jos_xtransactions AS CRTransaction ON DRTransaction.voucher_no = CRTransaction.voucher_no
                                            INNER JOIN jos_xaccounts ON CRTransaction.accounts_id = jos_xaccounts.id
                                            INNER JOIN jos_xaccounts a ON DRTransaction.accounts_id = a.id
                                            INNER JOIN jos_xmember m ON m.id = a.member_id
                                            INNER JOIN jos_xschemes ON jos_xaccounts.schemes_id = jos_xschemes.id
                                            WHERE
                                                    DRTransaction.branch_id = $b
                                            AND CRTransaction.branch_id = $b
                                            AND jos_xschemes.`Name` = '" . CASH_ACCOUNT_SCHEME . "'
                                            AND DRTransaction.amountCr = CRTransaction.amountDr
                                            AND CRTransaction.amountCr > 0
                                            AND DRTransaction.created_at BETWEEN '" . inp("dateFrom") . "' AND '" . inp("dateTo") . "'

                                            UNION

                                                    SELECT
                                                            DRTransaction.created_at as `Date`,
                                                            CONCAT('TO  ', if(m.`Name` like '%Default%',jos_xaccounts.AccountNumber,m.`Name`)) as Particulars,
                                                            DRTransaction.Narration as Narration,
                                                            DRTransaction.voucher_no as Voucher_no,
                                                            '' as Debit,
                                                            CRTransaction.amountCr as Credit
                                                    FROM
                                                            jos_xtransactions AS DRTransaction
                                                    INNER JOIN jos_xtransactions AS CRTransaction ON CRTransaction.voucher_no = DRTransaction.voucher_no
                                            INNER JOIN jos_xaccounts ON DRTransaction.accounts_id = jos_xaccounts.id
                                            INNER JOIN jos_xmember m ON m.id = jos_xaccounts.member_id
                                            INNER JOIN jos_xschemes ON jos_xaccounts.schemes_id = jos_xschemes.id
                                            WHERE
                                                    DRTransaction.branch_id = $b
                                            AND CRTransaction.branch_id = $b
                                            AND jos_xschemes.`Name` = '" . CASH_ACCOUNT_SCHEME . "'
                                            AND DRTransaction.amountDr > 0
                                            AND CRTransaction.amountCr > DRTransaction.amountDr
                                            AND DRTransaction.transaction_type_id = (select DISTINCT(tt.id) from jos_xtransaction_type tt join jos_xtransactions t on tt.id=t.transaction_type_id where tt.`Transaction` = '" . TRA_JV_ENTRY . "')
                                            AND DRTransaction.created_at BETWEEN '" . inp("dateFrom") . "' AND '" . inp("dateTo") . "'
                                            )
                                            AS cashbook

                                            ORDER BY voucher_no")->result();
                                            
*
*/
                                           
        $data['OpeningBalance'] = $this->db->query("select (a.OpeningBalanceDr - a.OpeningBalanceCr) as OpeningBalance from jos_xaccounts a join jos_xschemes s on s.id=a.schemes_id where s.`Name`='" . CASH_ACCOUNT_SCHEME . "' and a.branch_id=$b")->row()->OpeningBalance;
        $data['transactionOpeningBalance'] = $this->db->query("select IF((select(sum(t.amountDr) - sum(t.amountCr)) from jos_xtransactions t
                                                    join jos_xaccounts a on t.accounts_id=a.id
                                                    join jos_xschemes s on s.id=a.schemes_id
                                                    where s.`Name`='" . CASH_ACCOUNT_SCHEME . "' and t.branch_id=$b and t.created_at < '" . inp("dateFrom") . "') is NULL,0,(select(sum(t.amountDr) - sum(t.amountCr)) from jos_xtransactions t
                                                    join jos_xaccounts a on t.accounts_id=a.id
                                                    join jos_xschemes s on s.id=a.schemes_id
                                                    where s.`Name`='" . CASH_ACCOUNT_SCHEME . "' and t.branch_id=$b and t.created_at < '" . inp("dateFrom") . "')) as transactionOpeningBalance")->row()->transactionOpeningBalance;
        $data['keyandvalues'] = array("Date" => "Date", "Particulars" => "Particulars", "Narration" => "Vch Type", "Voucher_no" => "Vch Number", "Display_Voucher_no" => "Display_Vch Number", "Debit" => "Debit", "Credit" => "Credit");
        $data['results'] = $result;

        JRequest::setVar("layout", "cashbook");
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
//        if (inp("printToPDF") == null) {
//            //setInfo("CASH BOOK", "");
//            $this->load->library("form");
//            $form = $this->form->open("one", "index.php?option=com_xbank&task=report_cont.cashBook")
//                            ->dateBox("Date From", "name='dateFrom' class='input'")
//                            ->dateBox("Date To", "name='dateTo' class='input'")
//                            ->checkbox("Print To PDF", "name='printToPDF' value=1")
//                            ->submit("go");
//            $data['contents'] = $this->form->get();
//            JRequest::setVar("layout","cashbook");
//            $this->load->view("report.html",$data);
//            $this->jq->getHeader();
////            $this->load->view("template", $data);
////            $data['contents'] .= $this->load->view('cashbook', $data, true);
////            $this->load->view('template', $data);
//        } else {
//            $html = $this->load->view('cashbook', $data, true);
//            $this->load->plugin("to_pdf");
//            pdf_create($html, 'chequedata', true);
//        }
    }

    //function confirmTransactionDelete($voucherno, $foraccount=0) {
    function confirmTransactionDelete() {

        $voucherno = JRequest::getVar("vn");
        $foraccount = JRequest::getVar("id");
        $date = "";
        $transaction = new Transaction();
        $transaction->where('voucher_no', $voucherno)->where('branch_id', Branch::getCurrentBranch()->id)->get();
        //$transaction = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, Branch::getCurrentBranch()->id);
        $msg = "";
        $debitbeforeAccount = array();
        $creditbeforeAccount = array();

        $debitTransaction = array();
        $creditTransaction = array();

        $debitafterAccount = array();
        $creditafterAccount = array();
        foreach ($transaction as $t) {
            $acc = new Account();
            $acc->where('id', $t->accounts_id)->get();
            //$acc = Doctrine::getTable("Accounts")->find($t->accounts_id);
            $debitbeforeAccount += array($acc->AccountNumber => $acc->CurrentBalanceDr);
            $creditbeforeAccount += array($acc->AccountNumber => $acc->CurrentBalanceCr);

            $debitTransaction += array($acc->AccountNumber => $t->amountDr);
            $creditTransaction += array($acc->AccountNumber => $t->amountCr);

            $debitafterAccount += array($acc->AccountNumber => ($acc->CurrentBalanceDr - $t->amountDr));
            $creditafterAccount += array($acc->AccountNumber => ($acc->CurrentBalanceCr - $t->amountCr));

            $date = $t->created_at;
        }
        $msg .= "<h3>Current Account Position</h3>";
        $msg .=formatDrCr($debitbeforeAccount, $creditbeforeAccount);

        $msg .= "<h3>Transaction to reverse</h3>";
        $msg .=formatDrCr($debitTransaction, $creditTransaction);

        $msg .= "<h3>Account Position after transaction deletion</h3>";
        $msg .=formatDrCr($debitafterAccount, $creditafterAccount);
        echo $msg;
        $html = "<form method='post' action='index.php?option=com_xbank&task=report_cont.TransactionDelete&vn=" . $voucherno . "&id=$foraccount'>";
        $html .="<table>";
        $html .="<input type='submit' value='DELETE' >";
        $html .="<input type='hidden' value='$date' name='transdate'>";
        $html .="</form>";
//        echo $html;
//        $this->jq->getHeader();
        echo "<div id='transactionDeleteDIV'>" . $html . "</div>";
        $this->jq->getHeader(true, "transactionDeleteDIV");
    }

    //function TransactionDelete($voucherno, $foraccount) {
    function TransactionDelete() {

        //set_time_limit(5000);
        $voucherno = JRequest::getVar("vn");
        $foraccount = JRequest::getVar("id");
        $trans_date = JRequest::getVar("transdate");
        $branchid = Branch::getCurrentBranch()->id;
        $closing = new Closing();
        $closing->where('branch_id', $branchid)->get();
        $xl = new xConfig("transaction");
        
/* == Temporarily provided deleting facility
        if($closing->yearly >= $trans_date || !$xl->getKey("delete_transaction") || Staff::getCurrentStaff()->AccessLevel < BRANCH_ADMIN)
                re("com_xbank.index","You cannot delete a transaction done before last yearly closing date or you are not authorized to delete a transaction. Last yearly closing date is $closing->yearly .","error");
  */      
//        if (!$xl->getKey("delete_transaction") && $closing->daily >= $trans_date)
//                re("com_xbank.index","You cannot delete a transaction done before last closing date. Last closing date is $closing->daily .","error");
        //$closing = Doctrine::getTable("Closings")->findOneByBranch_id($branchid);
        $transactions = new Transaction();
        $transactions->where('voucher_no', $voucherno)->where('branch_id', $branchid)->get();
        //$transactions = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, $branchid);
        //$conn = Doctrine_Manager::connection();
        try {
            // $conn->beginTransaction();
//            if(JFactory::getUser()->gid > 23){
            $this->db->trans_begin();
            foreach ($transactions as $t) {
                $acc = new Account();
                $acc->where('id', $t->accounts_id)->get();
                //$acc = Doctrine::getTable("Accounts")->find($t->accounts_id);
                include(xBANKSCHEMEPATH . "/" . strtolower($acc->scheme->SchemeType) . "/" . strtolower($acc->scheme->SchemeType) . "transactionbeforedeleted.php");
                include(xBANKSCHEMEPATH . "/" . strtolower($acc->scheme->SchemeType) . "/" . strtolower($acc->scheme->SchemeType) . "transactionafterdeleted.php");
                $this->db->query("delete from jos_xtransactions where id = $t->id");
                //$q = "delete from jos_xtransactions where id = $t->id";
                //executeQuery($q);
            }
            
            $this->db->trans_commit();
            //redirect("mod_pandl/pandl_cont/accountTransactions/$foraccount");
            log::write("Transaction with voucher number $voucherno for account $foraccount done on date $trans_date deleted by staff ".Staff::getCurrentStaff()->id);
//            re("report_cont.accountTransactions&id=$foraccount","Transaction Deleted Successfully");
            re("report_cont.dashboard","Transaction Deleted Successfully");
//            }
//            else{
//                re("com_xbank.index","You are not authorized to delete any transaction. Please contact Head Office Admin for this.","error");
//            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
    }

    //function confirmTransactionEdit($voucherno, $foraccount=0) {
    function confirmTransactionEdit() {

        $voucherno = JRequest::getVar("vn");
        $foraccount = JRequest::getVar("id");
        $transaction = new Transaction();
        $transaction->where('voucher_no', $voucherno)->where('branch_id', Branch::getCurrentBranch()->id)->get();
        //$transaction = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, Branch::getCurrentBranch()->id);
        $html = "";
        $html .="<form method='post' action='index.php?option=com_xbank&task=report_cont.TransactionEdit&vn=" . $voucherno . "&id=$foraccount'>";
        $html .="<table>";
        $html .="<th>Voucher No.</th><th>Narration</th><th>Date</th><th>Account Number</th><th>DR</th><th>CR</th><th></th>";
        $i = 1;
        foreach ($transaction as $t) {
            $date = $t->created_at;
            $html .="<tr>";
            $html .="<td>" . $t->voucher_no . "</td>";
            if($t->transaction_type->Transaction != TRA_JV_ENTRY)
                $html .="<td><input type='text' name='Narration_$i'  value='$t->Narration' DISABLED></td>";
            else
                $html .="<td><input type='text' name='Narration_$i'  value='$t->Narration' ></td>";
            $html .="<td><input id='datepicker_$i' type='text' name='created_at_$i' value = ' $t->created_at '/></td>";
            $html .="<td>" . $t->account->AccountNumber . "</td>";
            $html .= "<td><input type='text' name='DR_$i' value='$t->amountDr'></td>";
            $html .= "<td><input type='text' name='CR_$i' value='$t->amountCr'></td>";
            $html .="<input type='hidden' value='$date' name='transdate'>";
            $html .="</tr>";
            $script = "$(function() {
                        $( '#datepicker_$i' ).datepicker({ dateFormat: 'yy-mm-dd',changeMonth: true, changeYear: true});
                });
                \n";
            $this->jq->addDomReadyScript($script);
            $i++;
        }

        $html .="</table>";
        $html .="<input type='submit' value='EDIT' >";
        $html .="</form>";
//        echo $html;
//        $this->jq->getHeader();
        echo "<div id='transactionEditDIV'>" . $html . "</div>";
        $this->jq->getHeader(true, "transactionEditDIV");
    }

    //function TransactionEdit($voucherno, $foraccount) {
    function TransactionEdit() {

        $voucherno = JRequest::getVar("vn");
        $foraccount=JRequest::getVar("id");
        $transaction = new Transaction();
        $transaction->where('voucher_no', $voucherno)->where('branch_id', Branch::getCurrentBranch()->id)->get();

        $closing = new Closing();
        $closing->where('branch_id', Branch::getCurrentBranch()->id)->get();
        $trans_date = JRequest::getVar("transdate");

/* temporarily editing facility given

        if($closing->yearly >= $trans_date)
                re("com_xbank.index","You cannot edit a transaction done before last yearly closing date or you are not authorized to edit a transaction. Last yearly closing date is $closing->yearly .","error");

*/

        // $transaction = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, Branch::getCurrentBranch()->id);
        $i = 1;
        //$conn = Doctrine_Manager::connection();
        $DR = 0;
        $CR = 0;
        $flag = true;
        foreach ($transaction as $t) {
            $DR += $this->input->post("DR_$i");
            $CR += $this->input->post("CR_$i");
            if ($this->input->post("created_at_$i") == '0000-00-00 00:00:00' || $this->input->post("created_at_$i") == "")
                $flag = false;
            $i++;
        }
        if ($DR != $CR || $flag == false) {
            echo "<h2>WARNING : You have made either of the mistakes</h2>
                <br>Credit Balance should be equal to the debit balance for a transaction.<br>
                The date of transaction is not entered correctly.";
        } else {
            try {
                $this->db->trans_begin();
                $i = 1;
                foreach ($transaction as $t) {
                    $account = new Account();
                    $account->where('id', $t->accounts_id)->get();
                    //$account = Doctrine::getTable("Accounts")->find($t->accounts_id);
                    $account->CurrentBalanceDr = $account->CurrentBalanceDr - $t->amountDr + $this->input->post("DR_$i");
                    $account->CurrentBalanceCr = $account->CurrentBalanceCr - $t->amountCr + $this->input->post("CR_$i");
                    $account->save();
                    $t->amountDr = $this->input->post("DR_$i");
                    $t->amountCr = $this->input->post("CR_$i");
                    $t->Narration = ($t->transaction_type->Transaction == TRA_JV_ENTRY ? $this->input->post("Narration_$i") : $t->Narration);
                    $t->created_at = $this->input->post("created_at_$i") . " " . getNow("H:i:s");
                    $t->save();
                    $i++;
                }
                $this->db->trans_commit();
                re("report_cont.dashboard","Transaction Edited Successfully");
                // redirect("mod_pandl/pandl_cont/accountTransactions/$foraccount");
            } catch (Exception $e) {
                $this->db->trans_rollback();
                echo $e->getMessage();
                return;
            }
        }
    }

    function dayBookForm() {
        //Staff::accessibleTo(USER);
        //setInfo("DAY BOOK", "");
        xDeveloperToolBars::onlyCancel("report_cont.AccountBook", "cancel", "View Day Book");

        $this->load->library("form");
        $form = $this->form->open("one", "index.php?option=com_xbank&task=report_cont.dayBook")
                        ->dateBox("Day Book For Date", "name='dateFrom' class='input'")
                        ->checkbox("Print", "name='printToPDF' value=1")
                        ->submit("go");
        $data['contents'] = $this->form->get();
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    function dayBook() {
        //Staff::accessibleTo(USER);
         if(inp("dateFrom"))
                    $this->session->set_userdata("daybook", inp("dateFrom"));
        $date = $this->session->userdata("daybook");
        xDeveloperToolBars::onlyCancel("report_cont.dayBookForm", "cancel", "DayBook of :" . inp('dateFrom'));
        $b = Branch::getCurrentBranch()->id;
        $result = $this->db->query("select t.Narration, t.amountDr, t.amountCr ,
                                    t.created_at, t.voucher_no,t.display_voucher_no, a.AccountNumber,
                                    a.CurrentBalanceCr, a.CurrentBalanceDr
                                    from jos_xtransactions t
                                    join jos_xaccounts a on t.accounts_id=a.id
                                    where t.branch_id=" . $b . "  and
                                    t.created_at like '" . $date . "%'
                                    ORDER BY voucher_no")->result();
        $data['OpeningBalance'] = $this->db->query("select (a.OpeningBalanceDr - a.OpeningBalanceCr) as OpeningBalance from jos_xaccounts a join jos_xschemes s on s.id=a.schemes_id where s.`Name`='" . CASH_ACCOUNT_SCHEME . "' and a.branch_id=$b")->row()->OpeningBalance;
        $data['transactionOpeningBalance'] = $this->db->query("select IF((select(sum(t.amountDr) - sum(t.amountCr)) from jos_xtransactions t
                                                    join jos_xaccounts a on t.accounts_id=a.id
                                                    join jos_xschemes s on s.id=a.schemes_id
                                                    where s.`Name`='" . CASH_ACCOUNT_SCHEME . "' and t.branch_id=$b and t.created_at < '" . $date . "') is NULL,0,(select(sum(t.amountDr) - sum(t.amountCr)) from jos_xtransactions t
                                                    join jos_xaccounts a on t.accounts_id=a.id
                                                    join jos_xschemes s on s.id=a.schemes_id
                                                    where s.`Name`='" . CASH_ACCOUNT_SCHEME . "' and t.branch_id=$b and t.created_at < '" . $date . "')) as transactionOpeningBalance")->row()->transactionOpeningBalance;
        $data['keyandvalues'] = array("Narration" => "Particulars","AccountNumber"=>"Account", "voucher_no" => "Vch Number","display_voucher_no" => "Display Voucher No", "amountDr" => "Debit", "amountCr" => "Credit");
        $data['date']=$date;
        $data['results'] = $result;
        JRequest::setVar("layout", "daybook");
        if (inp("printToPDF") == 1)
            $this->load->view("report.pdf", $data);
        else{
            $this->load->view("report.html", $data);
        $this->jq->getHeader();
        }

//        if (inp("printToPDF") == null) {
//            setInfo("DAY BOOK", "");
//        $this->load->library("form");
//        $form = $this->form->open("one", "index.php?//mod_pandl/pandl_cont/dayBook")
//                        ->dateBox("Day Book For Date", "name='dateFrom' class='input'")
//                        ->checkbox("Print To PDF", "name='printToPDF' value=1")
//                        ->submit("go");
//            $data['contents'] = $this->form->get();
////            $this->load->view("template", $data);
//            $data['contents'] .= $this->load->view('daybook', $data, true);
//            $this->load->view('template', $data);
//        } else {
//            $html = $this->load->view('daybook', $data, true);
//            $this->load->plugin("to_pdf");
//            pdf_create($html, 'chequedata', true);
//        }
    }

    function loanInterestForm() {
        Staff::accessibleTo(USER);
        setInfo("INTEREST ON LOAN ACCOUNTS", "");
        $this->load->library("form");
        $form = $this->form->open("1", "index.php?//mod_pandl/pandl_cont/loanInterestReport")
                        ->setColumns(2)
                        ->dateBox("Interest From", "name='fromDate' class='input'")
                        ->dateBox("Interest till", "name='toDate' class='input'")
                        ->checkbox("Print To PDF", "name='printToPDF' value=1");
        if (Branch::getCurrentBranch()->Code == "DFL") {
            //                    $branchNames=$this->db->query("select Name from branch")->result();
            $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                            ->_();
        } else {
            $b = Branch::getCurrentBranch()->id;
            $form = $form->hidden("", "name='BranchId' value='$b'");
        }
        $form = $form->submit("Go");
        $data['contents'] = $this->form->get();
        $this->load->view("template", $data);
    }

    function loanInterestReport() {
        $data['result'] = $this->db->query("select a.AccountNumber as accnum, ROUND((a.RdAmount * s.Interest * (s.NumberOfPremiums + 1)/1200)/s.NumberOfPremiums) as InterestAmount
                                from accounts a
                                join premiums p on a.id=p.accounts_id
                                join schemes s on s.id=a.schemes_id
                                where p.DueDate >= '" . inp('fromDate') . "'
                                and p.DueDate <= '" . inp('toDate') . "'
                                and s.SchemeType = '" . ACCOUNT_TYPE_LOAN . "'
                                and a.ActiveStatus=1
                                and a.branch_id = " . Branch::getCurrentBranch()->id);
        if (inp("printToPDF") == null) {

            Staff::accessibleTo(USER);
            setInfo("INTEREST ON LOAN ACCOUNTS", "");
            $this->load->library("form");
            $form = $this->form->open("1", "index.php?//mod_pandl/pandl_cont/loanInterestReport")
                            ->setColumns(2)
                            ->dateBox("Interest From", "name='fromDate' class='input'")
                            ->dateBox("Interest till", "name='toDate' class='input'")
                            ->checkbox("Print To PDF", "name='printToPDF' value=1");
            if (Branch::getCurrentBranch()->Code == "DFL") {
                //                    $branchNames=$this->db->query("select Name from branch")->result();
                $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                                ->_();
            } else {
                $b = Branch::getCurrentBranch()->id;
                $form = $form->hidden("", "name='BranchId' value='$b'");
            }
            $form = $form->submit("Go");
            $data['contents'] = $this->form->get();


            $data['contents'] .= $this->load->view('loaninterestreport', $data, true);
            $this->load->view('template', $data);
        } else {
            $html = $this->load->view('loaninterestreport', $data, true);
            $this->load->plugin("to_pdf");
            pdf_create($html, 'chequedata', true);
        }
    }

    function AccountNumber() {
        $list = array();
        $b = Branch::getCurrentBranch()->id;
        //$q="select a.* from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where a.AccountNumber Like '%". $this->input->post("term") . "%' or a.id like '%". $this->input->post("term")."%' limit 10 ";
        //array("select" => "a.*, m.Name as MName", "from" => "Accounts a", "leftJoin" => "a.Branch b", "innerJoin"=>"a.Member m", "where" => "a.AccountNumber Like '%\$term%'", "andWhere" => "b.id='$b->id'", "limit" => "10"), array("id", "AccountNumber","MName"), "id")
        $q = "select a.*,m.Name as MName from jos_xaccounts a left join jos_xbranch b on a.branch_id=b.id inner join jos_xmember m on a.member_id=m.id where (a.AccountNumber Like '%" . $this->input->post("term") . "%' or m.Name Like '%" . $this->input->post("term") . "%') and (b.id='$b')limit 10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->id, 'AccountNumber' => $dd->AccountNumber, 'MName' => $dd->MName);
        }
        echo '{"tags":' . json_encode($list) . '}';
    }

    function AccountBook() {
        xDeveloperToolBars::getAccountbookManagementToolBar();
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    function rdCommissionReportForm() {
        $form = $this->form;
        $form->open("one", "index.php?option=com_xbank&task=report_cont.rdCommissionReport")
                ->dateBox("RD Commission For date", "name='transactionDate' class='input'")
                ->submit("go");
        echo $form;
        $this->jq->getHeader();
    }

    function rdCommissionReport() {
        $b = Branch::getCurrentBranch()->id;
        $result = $this->db->query("select a.AccountNumber,a.RdAmount,s.`Name`,t.amountCr AS Commission
,
			(select amountCr from transactions
			where Narration LIKE '%RD Premium Commission%' and
			created_at =( select LAST_DAY('" . inp('transactionDate') . "')) and
			accounts_id = (select a.id from accounts a
									JOIN schemes s on s.id=a.schemes_id
								  where s.`Name` = '" . DUTIES_TAXES_SCHEME . "'  AND
									a.branch_id = $b) AND
			amountCr <> t.amountCr AND
			voucher_no = t.voucher_no) as TDS

from transactions t
join accounts a on t.reference_account = a.id
join schemes s on s.id=a.schemes_id
WHERE t.Narration LIKE '%RD Premium Commission%' and
t.created_at = (select LAST_DAY('" . inp('transactionDate') . "')) AND
t.amountCr <> 0 AND
t.accounts_id <> (select a.id from accounts a
									JOIN schemes s on s.id=a.schemes_id
								  where s.`Name` = '" . DUTIES_TAXES_SCHEME . "'  AND
									a.branch_id = $b) AND
s.SchemeType = '" . ACCOUNT_TYPE_RECURRING . "' AND
a.branch_id = $b

")->result();

        $data['result'] = $result;
        JRequest::setVar("layout","rdcommissionreport");
        $this->load->view("report.html", $data);
        $this->jq->getHeader();
    }

    function getBalanceSheet(){
        ini_set('memory_limit','2048M');
        xDeveloperToolBars::onlyCancel("report_cont.balanceSheetForm", "cancel", "BalanceSheet " . inp('fromDate') . " To " . inp('toDate'));

        if (inp("toDate") < inp("fromDate")) {
            $error = "To date can't be less than From date";
            //setError("To date can't be less than From date", "");
            re("report_cont.balanceSheetForm", $error);
        }

        $this->session->set_userdata("fromdate", inp("fromDate"));
        $this->session->set_userdata("todate", inp("toDate"));
        $balance = $this->pandlBalance(inp("fromDate"), inp("toDate"), inp("BranchId"),BALANCE_SHEET);

        $data['pandlBalance'] = $balance;
        $data['branch'] = inp("BranchId");
//        $data['results'] = $this->balanceSheetarray(inp("fromDate"), inp("toDate"), inp("BranchId"));
//		$data['backURL']="";


        if (inp("printToPDF") == null) {
            JRequest::setVar("layout", "bs");
            $this->load->view('report.html', $data);
            $this->jq->getHeader();
//            $data['contents'] = $this->load->view('balancesheetview1', $data, true);
//            $this->load->view('template', $data);
        } else {
            JRequest::setVar("layout", "bs");
            $this->load->view('report.pdf', $data);
        }
    }



    function shareCertificateForm() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Get Share Certificate");
        $b = Branch::getCurrentBranch();
        $this->load->library('form');
        $form = $this->form->open("sharecertificate", 'index.php?option=com_xbank&task=report_cont.shareCertificate',"target='dfhghf'")
        ->setColumns(2)
        ->lookupDB("Account Number", "name='AccountId' class='input req-string'", "index.php?option=com_xbank&task=ajax.lookupDBQLShareCretificate&format=raw", array("a"=>"b"), array("id", "AccountNumber", "Name", "CurrentBalanceCr"), "id");
        $form = $form->submit("Submit");
        $contents = $this->form->get();
        echo $contents;
        $this->jq->getHeader();
        }

    function shareCertificate() {
        if(inp('AccountId'))
            $acc = $this->session->set_userdata('account',inp('AccountId'));
        $acc = $this->session->userdata("account");
        $data['sm'] = new Account($acc);
        JRequest::setVar('layout','sharecertificate');
        $this->load->view("report.pdf",$data);
//        $this->jq->getHeader();
    }



    function loan_report(){
        $docs= new Document();
        $docs->where("LoanAccount",1)->get();
	$docsarr=array();
        if($docs)
            $docsarr +=array("None"=>"%");
	foreach($docs as $h){
 		$docsarr +=array($h->Name => $h->id);
 	}


        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.searchLoans")
                ->setColumns(2)
                ->lookupDB("Account Number","name='Account_Number' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_account&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber")
                ->checkBox("","name='AccountNumber' class='input' value='Account Number' CHECKED")

                ->lookupDB("Scheme Name","name='Scheme_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_scheme&format=raw",
 			array("a" => "b"),
			array("Scheme"),"Scheme")
                ->checkBox("","name='SchemeName' class='input' value='Scheme Name' CHECKED")


                ->lookupDB("Member Name","name='Member_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_member&format=raw",
 			array("a" => "b"),
			array("Name","Address","AccountNumber"),"Name")
                ->checkBox("","name='Name' class='input' value='Member Name' CHECKED")

                ->textArea("Permanent Address","name='Permanent_Address' ")
                ->checkBox("","name='PermanentAddress' class='input' value='Permanent Address' CHECKED")

                ->textArea("Phone Numbers","name='Phone_Nos' ")
                ->checkBox("","name='PhoneNos' class='input' value='Phone Numbers' CHECKED")

                ->text("EMI Amount","name='EMI_Amount' ")
                ->checkBox("","name='Amount' class='input' value='EMI' CHECKED")

                ->text("Loan Amount","name='Loan_Amount' ")
                ->checkBox("","name='RdAmount' class='input' value='Loan Amount' CHECKED")

                ->text("Total Interest","name='Interest' ")
                ->checkBox("","name='TotalInterest' class='input' value='Total Interest'")

                ->text("Number Of EMI Due Till Date","name='EMI_Due' ")
                ->checkBox("","name='EMIDue' class='input' value='Number Of EMI Due Till Date'")


                 ->text("Principal Due","name='Principal_Due' ")
                ->checkBox("","name='PrincipalDue' class='input' value='Principal Due'")

                 ->text("Total Interest Due","name='Total_Interest_Due' ")
                ->checkBox("","name='TotalInterestDue' class='input' value='Total Interest Due'")


                ->text("Total Penalty","name='Penalty' ")
                ->checkBox("","name='PenaltyDue' class='input' value='Penalty Due'")

//                 ->text("Gaurantor Name","name='Gaurantor_Name' ")
                ->lookupDB("Gaurantor Name","name='Gaurantor_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_gaurantor&format=raw",
 			array("a" => "b"),
			array("Name","Address","AccountNumber"),"Name")
                ->checkBox("","name='GaurantorName' class='input' value='Gaurantor Name'")

                ->text("Gaurantor Address","name='Gaurantor_Address' ")
                ->checkBox("","name='GaurantorAddress' class='input' value='Gaurantor Address'")

                ->text("Gaurantor Phone Numbers","name='Gaurantor_Numbers' ")
                ->checkBox("","name='GaurantorNumbers' class='input' value='Gaurantor Numbers'")

                ->lookupDB("Dealer Name","name='Dealer_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
 			array("a" => "b"),
			array("id","Name","Address"),"Name")
                ->checkBox("","name='DealerName' class='input' value='Dealer Name' CHECKED")


                ->select("Documents","name='Documents_Submitted'",$docsarr)

                ->checkBox("","name='DocumentsSubmitted' class='input' value='Doucments Submitted'")
                ->textArea("Description for Documents","name='Documents_Description'")

                ->_()
                ->_()
                ->dateBox("Insurance From","name='insfromDate' class='input'")
                ->dateBox("Insurance till","name='instoDate' class='input'")
                ->dateBox("Premiums From","name='fromDate' class='input'")
                ->dateBox("Premiums till","name='toDate' class='input'")
                ->select("Paid Status","name='paidStatus'",array("Any"=>"%","Paid"=>1,"Due"=>"0"))

                ->checkBox("Grouped Results","name='GroupBy' class='input' value='1' CHECKED")

                ->submit("Search");
        echo $this->form->get();
        $this->jq->getHeader();
    }

   /**
    * Search the loan accounts and generates a loan report
    */
    function searchLoans(){
        $start = (JRequest::getVar("start") ? JRequest::getVar("start") : 0);
        $count = (JRequest::getVar("count") ? JRequest::getVar("count") : ROWS_IN_DATA);
        $searchQuery="select a.AccountNumber as `Account Number`, s.Name as `Scheme Name`, m.Name as `Member Name`,
           m.PermanentAddress as `Permanent Address`, m.PhoneNos as `Phone Numbers`, a.RdAmount as `Loan Amount`,p.Amount as `EMI`,
           a.CurrentInterest  + (select SUM(amountDr) from jos_xtransactions tra join jos_xtransaction_type tty on tra.transaction_type_id=tty.id where accounts_id=a.id and tty.Transaction='".TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT."')  as `Penalty Due`,
           ((a.RdAmount * s.Interest * (s.NumberOfPremiums + 1))/1200) as `Total Interest`,
           (select COUNT(*) from jos_xpremiums where Paid=0 and DueDate <= '".getNow()."' and accounts_id = a.id) as `Number Of EMI Due Till Date`,
           ((select SUM(Amount) from jos_xpremiums where Paid=0 and accounts_id = a.id)- round((a.RdAmount * s.Interest * ((select COUNT(*) from jos_xpremiums where Paid=0 and accounts_id = a.id) + 1))/1200)) as `Principal Due`,
           round((a.RdAmount * s.Interest * ((select COUNT(*) from jos_xpremiums where Paid=0 and accounts_id = a.id) + 1 ))/1200) as `Total Interest Due`,
           a.Nominee as `Gaurantor Name`, a.MinorNomineeParentName as `Gaurantor Address`, a.RelationWithNominee as `Gaurantor Numbers` ,
           docs.Name as `Documents Submitted`, ds.Description as `Document Info`,
           d.DealerName as `DealerName`,
           a.LoanInsurranceDate as `LoanInsurranceDate`
           from jos_xpremiums p
           left join jos_xaccounts a on p.accounts_id=a.id
           left join jos_xmember m on m.id=a.member_id
           left join jos_xbranch b on b.id=a.branch_id
           left join jos_xschemes s on a.schemes_id=s.id
           left join jos_xtransactions t on t.accounts_id=a.id
           left join jos_xdocuments_submitted ds on a.id=ds.accounts_id
           left join jos_xdocuments docs on docs.id=ds.documents_id
            join jos_xdealer d on d.id=a.dealer_id
           where b.id='".Branch::getCurrentBranch()->id."' and s.SchemeType='".ACCOUNT_TYPE_LOAN."'
          ";
        $msg="";
        $where="";
        $groupby="";
        $having="";
        $fieldsToShow =array();
        if(inp("Account_Number") != "") {
            $account=new Account();
            $account->where("AccountNumber",(inp("Account_Number")))->get();
            if(!$account->result_count()){
                $msg .= "<h2> No such account found</h2> <br/>";
              //  echo $msg;
                $data['result']="";
            }else{
                $where .= " AND  a.AccountNumber = '".inp("Account_Number") ."'";
                $accountType = $account->scheme->SchemeType;
                $data['accountType']=$accountType;
            }
        }

        if(inp("Member_Name") != ""){
            $where .=" AND m.Name like '%".inp("Member_Name")."%'";
        }

        if(inp("Scheme_Name") != ""){
            $where .=" AND s.Name like '%".inp("Scheme_Name")."%'";
        }

        if(inp("Permanent_Address") != ""){
            $where .=" AND m.PermanentAddress like '%".inp("Permanent_Address")."%'";
        }

        if(inp("Phone_Nos") != ""){
            $where .=" AND m.PhoneNos like '%".inp("Phone_Nos")."%'";
        }

        if(inp("EMI_Amount") != ""){
            $where .=" AND p.Amount ". makeoperator(inp("EMI_Amount"));
        }

        if(inp("Loan_Amount") != ""){
            $where .=" AND a.RdAmount ".makeoperator(inp("Loan_Amount"));
        }

        if(inp("fromDate") != ""){
            $where .= " AND p.DueDate >= '". inp("fromDate")."' ";
        }
        if(inp("toDate") != ""){
            $where .= " AND p.DueDate <= '". inp("toDate")."' ";
        }

        if(inp("paidStatus") != "%"){
            $where .= " AND p.Paid = '". inp("paidStatus")."' ";
        }

        if(inp("Gaurantor_Name") != ""){
            $where .=" AND a.Nominee like '%".inp("Gaurantor_Name")."%'";
        }

        if(inp("Gaurantor_Address") != ""){
            $where .=" AND a.MinorNomineeParentName like '%".inp("Gaurantor_Name")."%'";
        }

        if(inp("Gaurantor_Numbers") != ""){
            $where .=" AND a.RelationWithNominee like '%".inp("Gaurantor_Numbers")."%'";
        }

        if(inp("Dealer_Name") != ""){
            $where .=" AND d.DealerName like '%".inp("Dealer_Name")."%'";
        }

        if(inp('EMI_Due') != ""){
            $having .= " `Number Of EMI Due Till Date` ". makeoperator(inp('EMI_Due'))." AND ";
        }

        if(inp('Penalty') != ""){
            $having .= " `Penalty Due` ". makeoperator(inp('Penalty'))." AND ";
        }

        if(inp('Interest') != ""){
            $having .= " `Total Interest` ". makeoperator(inp('Interest'))." AND ";
        }

        if(inp('Principal_Due') != ""){
            $having .= " `Principal Due` ". makeoperator(inp('Principal_Due'))." AND ";
        }

        if(inp('Total_Interest_Due') != ""){
            $having .= " `Total Interest Due` ". makeoperator(inp('Total_Interest_Due'))." AND ";
        }

        if(inp("Documents_Submitted") !="%"){
            $where .=" AND ds.documents_id = ".inp("Documents_Submitted")." ";
        }
//        else{
//            $where .=" AND ds.documents_id = ".inp("Documents_Submitted")." ";
//        }

        if(inp("insfromDate") != ""){
            $where .= " AND a.LoanInsurranceDate >= '". inp("fromDate")."' ";
        }
        if(inp("instoDate") != ""){
            $where .= " AND a.LoanInsurranceDate <= '". inp("toDate")."' ";
        }

        if(inp("Documents_Description")!=""){
            $where .=" AND ds.Description like '%".inp("Documents_Description")."%'";
        }

        if($having!=""){
            $having=trim($having," AND ");
        }


        if(inp("GroupBy")!=false){
            $groupby=" group by a.id";
        }

        $searchQuery .=  $where;
        $searchQuery .=  $groupby;
        if($having!="")
        $searchQuery .=" having ".  $having;
//        $searchQuery .=  " LIMIT $start, $count";

            $result=$this->db->query($searchQuery)->result();
            $data['result']=$result;

            $fieldsToShow +=array("AccountNumber"=>  inp('AccountNumber'));
            $fieldsToShow +=array("SchemeName"=>  inp('SchemeName'));
            $fieldsToShow +=array("Name"=> inp('Name')) ;
            $fieldsToShow +=array("PermanentAddress"=> inp('PermanentAddress'));
            $fieldsToShow +=array("PhoneNos"=> inp('PhoneNos'));
            $fieldsToShow +=array("Amount"=> inp('Amount'));
            $fieldsToShow +=array("RdAmount"=>inp('RdAmount'));
            $fieldsToShow +=array("TotalInterest"=>inp('TotalInterest'));
            $fieldsToShow +=array("PenaltyDue"=>inp('PenaltyDue'));

            $fieldsToShow +=array("EMIDue"=>inp('EMIDue'));
            $fieldsToShow +=array("PrincipalDue"=>inp('PrincipalDue'));
            $fieldsToShow +=array("TotalInterestDue"=>inp('TotalInterestDue'));
            $fieldsToShow +=array("GaurantorName"=>inp('GaurantorName'));
            $fieldsToShow +=array("GaurantorAddress"=>inp('GaurantorAddress'));
            $fieldsToShow +=array("GaurantorNumbers"=>inp('GaurantorNumbers'));
            $fieldsToShow +=array("DealerName"=>inp('DealerName'));
            $fieldsToShow +=array("LoanInsurranceDate"=>"LoanInsurranceDate");
            if(inp('DocumentsSubmitted') != ""){
            $fieldsToShow +=array("DocumentsSubmitted"=>"Documents Submitted");
            $fieldsToShow +=array("DocumentInfo"=>"Document Info");
            }

       $docs= new Document();
        $docs->where("LoanAccount",1)->get();
	$docsarr=array();
        if($docs)
            $docsarr +=array("None"=>"%");
	foreach($docs as $h){
 		$docsarr +=array($h->Name => $h->id);
 	}


        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.searchLoans")
                ->setColumns(2)
                ->lookupDB("Account Number","name='Account_Number' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_account&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber")
                ->checkBox("","name='AccountNumber' class='input' value='Account Number' CHECKED")

                ->lookupDB("Scheme Name","name='Scheme_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_scheme&format=raw",
 			array("a" => "b"),
			array("Scheme"),"Scheme")
                ->checkBox("","name='SchemeName' class='input' value='Scheme Name' CHECKED")


                ->lookupDB("Member Name","name='Member_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_member&format=raw",
 			array("a" => "b"),
			array("Name","Address","AccountNumber"),"Name")
                ->checkBox("","name='Name' class='input' value='Member Name' CHECKED")

                ->textArea("Permanent Address","name='Permanent_Address' ")
                ->checkBox("","name='PermanentAddress' class='input' value='Permanent Address' CHECKED")

                ->textArea("Phone Numbers","name='Phone_Nos' ")
                ->checkBox("","name='PhoneNos' class='input' value='Phone Numbers' CHECKED")

                ->text("EMI Amount","name='EMI_Amount' ")
                ->checkBox("","name='Amount' class='input' value='EMI' CHECKED")

                ->text("Loan Amount","name='Loan_Amount' ")
                ->checkBox("","name='RdAmount' class='input' value='Loan Amount' CHECKED")

                ->text("Total Interest","name='Interest' ")
                ->checkBox("","name='TotalInterest' class='input' value='Total Interest'")

                ->text("Number Of EMI Due Till Date","name='EMI_Due' ")
                ->checkBox("","name='EMIDue' class='input' value='Number Of EMI Due Till Date'")


                 ->text("Principal Due","name='Principal_Due' ")
                ->checkBox("","name='PrincipalDue' class='input' value='Principal Due'")

                 ->text("Total Interest Due","name='Total_Interest_Due' ")
                ->checkBox("","name='TotalInterestDue' class='input' value='Total Interest Due'")


                ->text("Total Penalty","name='Penalty' ")
                ->checkBox("","name='PenaltyDue' class='input' value='Penalty Due'")

//                 ->text("Gaurantor Name","name='Gaurantor_Name' ")
                ->lookupDB("Gaurantor Name","name='Gaurantor_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_gaurantor&format=raw",
 			array("a" => "b"),
			array("Name","Address","AccountNumber"),"Name")
                ->checkBox("","name='GaurantorName' class='input' value='Gaurantor Name'")

                ->text("Gaurantor Address","name='Gaurantor_Address' ")
                ->checkBox("","name='GaurantorAddress' class='input' value='Gaurantor Address'")

                ->text("Gaurantor Phone Numbers","name='Gaurantor_Numbers' ")
                ->checkBox("","name='GaurantorNumbers' class='input' value='Gaurantor Numbers'")

                 ->lookupDB("Dealer Name","name='Dealer_Name' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
 			array("a" => "b"),
			array("id","Name","Address"),"Name")
                ->checkBox("","name='DealerName' class='input' value='Dealer Name' CHECKED")

                ->select("Documents","name='Documents_Submitted'",$docsarr)

                ->checkBox("","name='DocumentsSubmitted' class='input' value='Doucments Submitted'")
                ->textArea("Description for Documents","name='Documents_Description'")

                ->_()
                ->_()
                ->dateBox("Insurrance From","name='insfromDate' class='input'")
                ->dateBox("Insurrance till","name='instoDate' class='input'")
                ->dateBox("Premiums From","name='fromDate' class='input'")
                ->dateBox("Premiums till","name='toDate' class='input'")
                ->select("Paid Status","name='paidStatus'",array("Any"=>"%","Paid"=>1,"Due"=>"0"))

                ->checkBox("Grouped Results","name='GroupBy' class='input' value='1' CHECKED")

                ->submit("Search");
       echo $this->form->get();

        $data['start'] = $start;
        $data['i'] = $start;
        $data['count'] = $count;

        $data['fieldsToShow']=$fieldsToShow;
        JRequest::setVar("layout","loanreport");
        $this->load->view("report.html",$data);
        $this->jq->getHeader();

    }

    function allschemedetailsform() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Periodic Account Details");
        $this->load->library("form");
        $form = $this->form->open("1", "index.php?option=com_xbank&task=report_cont.allschemedetails")
                        ->setColumns(2)
                        ->dateBox("Accounts Opened From", "name='fromDate' class='input'")
                        ->dateBox("Accounts Opened Till", "name='toDate' class='input'");
        if (Branch::getCurrentBranch()->Code == "DFL") {
            //                    $branchNames=$this->db->query("select Name from branch")->result();
            $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                            ->_();
        } else {
            $b = Branch::getCurrentBranch()->id;
            $form = $form->hidden("", "name='BranchId' value='$b'");
        }
        $form = $form->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    function allschemedetails() {
        xDeveloperToolBars::onlyCancel("report_cont.allschemedetailsform", "cancel", "Periodic Account Details from ".inp("fromDate")." to ".inp("toDate"));
        $q = "select s.SchemeType, count(a.id) as cnt from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where a.created_at between '".inp('fromDate')."' and DATE_ADD('".inp('toDate')."', INTERVAL +1 DAY) and a.DefaultAC=0 ";
        if(inp('BranchId')!='%')
            $q .= "and a.branch_id=" . inp("BranchId")." group by s.SchemeType";
        else {
            $q .= " group by s.SchemeType";
        }
        $a['accountcount'] = $this->db->query($q)->result();
        $a['schemeTypes'] = explode(",", ACCOUNT_TYPES);
        JRequest::setVar("layout","allschemedetails");
        $data['contents'] = $this->load->view('report.html', $a);
        $this->jq->getHeader();
    }


    function premiums_report(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Premiums Report");
        $this->load->library("form");
        $form=$this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.searchPremiums")
                ->setColumns(2)
                ->lookupDB("Account Number","name='AccountNumber' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.get_loan_account&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber")
                ->_()
                ->dateBox("Premiums From","name='fromDate' class='input'")
                ->dateBox("Premiums till","name='toDate' class='input'")
                ->select("Paid Status","name='paidStatus'",array("Any"=>"%","Paid"=>1,"Due"=>"0"))
                ->select("Skipped Status","name='skippedStatus'",array("Any"=>"%","Skipped"=>1,"Not Skipped"=>"0"));
//                if(Branch::getCurrentBranch()->Code == "DFL")
//                {
////                    $branchNames=$this->db->query("select Name from branch")->result();
//                    $form=$form->select("Select Branch","name='BranchId'",  Branch::getAllBranchNames())
//                        ->_();
//                }
                $form=$form->submit("Search");
        echo $this->form->get();
        $this->jq->getHeader();
    }

/**
 * Actaul searching for premiums is done
 */
    function searchPremiums(){
        xDeveloperToolBars::onlyCancel("report_cont.premiums_report", "cancel", "Premiums Report");
        $searchQuery="select p.*, a.AccountNumber from jos_xpremiums p join jos_xaccounts a on a.id=p.accounts_id";
        $msg="";
        $where="";

        if(inp("AccountNumber") != "") {
            $account=new Account();
            $account->where("AccountNumber",inp("AccountNumber"))->get();
            if(!$account->result_count()){
                $msg .= "<h2> No such account found</h2> <br/>";
              //  echo $msg;
                $data['result']="";
            }else{
                $where .= " a.AccountNumber = '".inp("AccountNumber") ."'";
                $accountType = $account->scheme->SchemeType;
                $data['accountType']=$accountType;
//            }
//        }
        if(inp("fromDate") != ""){
            $where .= " AND p.DueDate >= '". inp("fromDate")."' ";
        }
        if(inp("toDate") != ""){
            $where .= " AND p.DueDate <= '". inp("toDate")."' ";
        }

        /**checking for account type and then appending the where condition.
        */

        if($accountType == ACCOUNT_TYPE_LOAN){
                if(inp("paidStatus") != "%"){
                  $where .= " AND p.Paid = '". inp("paidStatus")."' ";
                }

//                if(inp("skippedStatus") != "%"){
//                    $where .= " AND p.Skipped = '". inp("skippedStatus")."' ";
//                }

        }
        if($accountType == ACCOUNT_TYPE_RECURRING){
        switch (inp("paidStatus")) {
            case "1":
                        switch (inp("skippedStatus")) {
                            case "1":
                                $where .= "AND p.Paid > 1 AND p.Skipped > 1 ";
                                break;
                            case "0":
                                $where .= " AND p.Paid > 0 AND p.Skipped = 0 ";
                                break;
                            default:
                                $where .= " AND p.Paid > 0 AND p.Skipped = 0 ";
                                break;
                        }
                break;
            case "0":
                       switch (inp("skippedStatus")) {
                            case "1":
                                $where .= "AND p.Paid > 0 AND p.Skipped = 1 ";
                                break;
                            case "0":
                                $where .= " AND p.Paid =0 AND p.Skipped = 0 ";
                                break;
                            default:
                                $where .= " AND (p.Paid = 0 OR p.Skipped = 1) ";
                                break;
                        }
                break;
            default:
                       switch (inp("skippedStatus")) {
                            case "1":
                                $where .= "AND p.Skipped = 1 ";
                                break;
                            case "0":
                                $where .= " AND ((p.Paid =0 AND p.Skipped = 0) OR (p.Paid > 0 AND p.Skipped = 0)) ";
                                break;
                            default:
                                break;
                        }
               break;
            }
        }



        if($where != ""){
            $where = " where " . trim($where," AND ");
        }
        $searchQuery .=  $where;
        $data['result'] = $this->db->query($searchQuery)->result();

        }
        }
        else{

        }


        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.searchPremiums")
                ->setColumns(2)
                ->lookupDB("Account Number","name='AccountNumber' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.get_loan_account&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber")
                ->_()
                ->dateBox("Premiums From","name='fromDate' class='input'")
                ->dateBox("Premiums till","name='toDate' class='input'")
                ->select("Paid Status","name='paidStatus'",array("Any"=>"%","Paid"=>1,"Due"=>"0"))
                ->select("Skipped Status","name='skippedStatus'",array("Any"=>"%","Skipped"=>1,"Not Skipped"=>"0"))
                ->submit("Search");
        echo $this->form->get();
        JRequest::setVar("layout","searchResultTable");
        $this->load->view("report.html",$data);
        $this->jq->getHeader();

    }




    function loanAccountReportForm() {
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan Account Detailed Report");
        $this->load->library('form');
        $form = $this->form->open("loanreport", 'index.php?option=com_xbank&task=report_cont.loanAccountReport')
                        ->setColumns(2);
//        if ($b->Code == 'DFL')
            $form = $form->lookupDB("Account Number","name='Account' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_account_statement&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber");
//        else
//            $form = $form->lookupDB("Account Number", "name='Account' class='input req-string' ", "index.php?//ajax/lookupDBDQL", array("select" => "a.*", "from" => "Accounts a, a.Schemes s", "where" => "(a.AccountNumber Like '%\$term%' OR a.id Like '%\$term%') AND s.SchemeType = '" . ACCOUNT_TYPE_LOAN . "' AND a.branch_id = " . Branch::getCurrentBranch()->id, "limit" => "10"), array("id", "AccountNumber"), "id");
        $form = $form->submit("Submit");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    function loanAccountReport() {
        xDeveloperToolBars::onlyCancel("report_cont.loanAccountReportForm", "cancel", "Loan Account Detailed Report");
        $account = new Account();
        $account->where("AccountNumber",inp("Account"))->get();
        $acc = new Account();
        $acc->where("member_id",$account->member_id);
         $data['otheraccounts'] = "";
        foreach ($acc as $ac) {
            $data['otheraccounts'] .=  $ac->AccountNumber . "<br>";
        }
        $data['account']=$account;
        JRequest::setVar("layout","loan");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }


    function loan_insurrance_report_form(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan Insurrance Report");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loan_insurrance_report")
                ->setColumns(2)
                
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->select("Select Branch","name='BranchId'",  Branch::getAllBranchNames())
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    function loan_insurrance_report(){
        xDeveloperToolBars::onlyCancel("report_cont.loan_insurrance_report_form", "cancel", "Loan Insurrance Report");
        $q = "select a.*,m.Name from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id join jos_xmember m on a.member_id = m.id where a.LoanInsurranceDate between '".inp("fromDate")."' and '".inp("toDate")."' ";
        if(inp("BranchId") != '%')
            $q .= " and a.branch_id = ".inp("BranchId");
        $data['result'] = $this->db->query($q)->result();
        JRequest::setVar("layout","loaninsurrance");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }
    
    
     function new_reports() {
        xDeveloperToolBars::getNewReportManagementToolBar();
        xDeveloperToolBars::getNewReportSubMenus();
        $this->load->view("report.html");
        $this->jq->getHeader();
    }


    function loan_insurrance_due_report_form(){
            xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan Insurrance Due List");
            $this->load->library("form");
            $docs= new Document();
            $docs->where("LoanAccount",1)->get();
            $docsarr=array();
            if($docs) $docsarr +=array("None"=>"%");
            foreach($docs as $h){
              $docsarr +=array($h->Name => $h->id);
            }
            $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loan_insurrance_due_report")
                    ->setColumns(2)
                    ->lookupDB("Dealer Name","name='DealerName' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
 			array("a" => "b"),
			array("id","Name","Address"),"Name")
                    ->_()
                    ->dateBox("Select Date From","name='fromDate' class='input'")
                    ->dateBox("Select Date till","name='toDate' class='input'")
                    ->select("Select Branch","name='BranchId'", Branch::getAllBranchNames())
                    ->select("Documents","name='Documents_Submitted'",$docsarr)
                    ->submit("Go");
            echo $this->form->get();
            $this->jq->getHeader();
        }

        function loan_insurrance_due_report(){
            xDeveloperToolBars::onlyCancel("report_cont.loan_insurrance_due_report_form", "cancel", "Loan Insurrance Due Report for Dealer ".inp("DealerName")." ");
            $q = "select a.id as aid,a.AccountNumber as accnum,
            a.LoanInsurranceDate as LoanInsurranceDate,
            m.*,
            d.DealerName as dname, d.Address as daddress from 
            jos_xaccounts a  
            join jos_xschemes s on a.schemes_id = s.id  
            join jos_xmember m on a.member_id = m.id  
            join jos_xdealer d on d.id=a.dealer_id 
            where 
            a.LoanInsurranceDate between DATE_ADD('".inp("fromDate")."' , INTERVAL +365 DAY) 
            and 
            DATE_ADD('".inp("toDate")."', INTERVAL +365 DAY)  
            and 
            d.DealerName like '%".inp('DealerName')."%' 
            and 
            (a.LoanInsurranceDate <> '0000-00-00 00:00:00' or a.LoanInsurranceDate is not null) ";

            $a= new Account();
            $a->select_func("DATE_ADD","[LoanInsurranceDate]", "[INTERVAL +365 DAY]","EndInsuranceDate");
            $a->select('*');
            $a->include_related('dealer','DealerName');
            $a->include_related('dealer','Address');
            $a->include_related('scheme','Name');
            $a->include_related('member','Name');
            $a->include_related('member','FatherName');
            $a->include_related('member','PermanentAddress');
            $a->include_related('member','PhoneNos');
            $a->select_subquery('(SELECT Description From jos_xdocuments_submitted doc WHERE doc.accounts_id=${parent}.id AND doc.documents_id='.inp('Documents_Submitted').')','Documents');
            if(inp("BranchId") != '%') $a->where('branch_id',inp('BranchId'));
            $a->where_related('dealer','DealerName like \'%'.inp('DealerName').'%\'');
            $a->having("EndInsuranceDate between '".inp("fromDate")."' and '".inp("toDate")."'");
            $a->get();
            // echo $a->check_last_query();
            $data['report']=getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Mobile", "Loan Insurance End Date",'Documents'),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_PermanentAddress','member_PhoneNos','EndInsuranceDate','Documents'),       //fields
                array(),        //totals_array
                array(
                  "Dealer Name"=>'dealer_DealerName',
                  "From Date" => "~" . inp('fromDate'),
                  "To Date" => "~".inp('toDate')
                  ),        //headers
                array('sno'=>true),     //options
                "<h3>Loan Insurance Due report for $a->dealer_DealerName </h3>",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array()
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        return;
            if(inp("BranchId") != '%')
                $q .= " and a.branch_id = ".inp("BranchId");
            $data['result'] = $this->db->query($q)->result();
            JRequest::setVar("layout","loaninsurranceduelist");
            $this->load->view('report.html', $data);
            $this->jq->getHeader();
        }
        
        function deposit_insurrance_due_report_form(){
            xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Date Wise Account Details");
        $this->load->library("form");
        $form = $this->form->open("1", "index.php?option=com_xbank&task=report_cont.deposit_insurrance_due_report")
                        ->setColumns(2)
                        ->dateBox("Accounts Opened From", "name='fromDate' class='input'")
                        ->dateBox("Accounts Opened Till", "name='toDate' class='input'");
        if (Branch::getCurrentBranch()->Code == "DFL") {
            //                    $branchNames=$this->db->query("select Name from branch")->result();
            $form = $form->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                            ->_();
        } else {
            $b = Branch::getCurrentBranch()->id;
            $form = $form->hidden("", "name='BranchId' value='$b'");
        }
        $form = $form->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
        }


        function deposit_insurrance_due_report() {
        xDeveloperToolBars::onlyCancel("report_cont.deposit_insurrance_due_report_form", "cancel", "Deposit Insurance Due Report from ".inp("fromDate")." to ".inp("toDate"));
        $q = "select s.SchemeType, count(a.id) as cnt from jos_xaccounts a join jos_xschemes s on a.schemes_id = s.id where a.created_at between '".inp('fromDate')."' and DATE_ADD('".inp('toDate')."', INTERVAL +1 DAY) and a.DefaultAC=0 ";
        if(inp('BranchId')!='%')
            $q .= "and a.branch_id=" . inp("BranchId")." group by s.SchemeType";
        else {
            $q .= " group by s.SchemeType";
        }
        $a['accountcount'] = $this->db->query($q)->result();
        $a['schemeTypes'] = explode(",", ACCOUNT_TYPES);
        JRequest::setVar("layout","allschemedetails_modified");
        $data['contents'] = $this->load->view('report.html', $a);
        $this->jq->getHeader();
    }
    
    
    function loanEMIDueListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan EMI Due List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loanEMIDueList")
                ->setColumns(2)
                ->lookupDB("Dealer Name","name='DealerName' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
                    array("a" => "b"),
                    array("id","Name","Address"),"Name")
                ->_()
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }


    function loanEMIDueList(){
        xDeveloperToolBars::onlyCancel("report_cont.loanEMIDueListForm", "cancel", "Loan EMI Due List");
/*        $q ="select a.AccountNumber,m.Name, m.PermanentAddress, m.FatherName, m.PhoneNos,p.Amount,d.DealerName,

(select count(*) as cnt from jos_xpremiums where accounts_id = a.id and DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND PaidOn is NULL) as premiumcount, a.RdAmount, a.Nominee,a.MinorNomineeParentName,a.RelationWithNominee

from jos_xaccounts a join jos_xmember m on a.member_id=m.id
join jos_xpremiums p on a.id=p.accounts_id
join jos_xschemes s on s.id=a.schemes_id
left join jos_xdealer d on a.dealer_id = d.id
where
s.SchemeType = 'Loan' AND
p.DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND
d.DealerName like '%".inp("DealerName")."%' AND
p.PaidOn is NULL

GROUP BY p.accounts_id
HAVING
premiumcount <= 2
";
        $data['result'] = $this->db->query($q)->result();
        JRequest::setVar("layout","loanemiduelist");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        */
        $a= new Account();

        $p = $a->premiums;

        $p->select_func('COUNT', '*', 'count');
        $p->where("PaidOn is null");
        $p->where("DueDate between '".inp("fromDate")."' and '".inp("toDate")."'");
        $p->where_related('account', 'id', '${parent}.id');


        $a->select('*, id as PaneltyDUE');
        $a->include_related('dealer','DealerName');
        $a->include_related('agent/member','Name');
        $a->include_related('agent/member','PhoneNos');

        $a->select_subquery($p,'DuePremiumCount');
        $a->select_subquery('(SELECT MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Amount');
        $a->select_subquery('(SELECT count(*) * MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Total');

        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');

        $a->where_related('scheme','SchemeType like' ,'loan');
        $a->where_related('dealer',"DealerName like '%".inp('DealerName')."%'");

        $a->having("DuePremiumCount <= 2 and DuePremiumCount>0");
        $a->get();
//        echo $a->check_last_query();
        echo getReporttable($a,             //model
                array("Account Number","Scheme","Member Name","Father Name", "Phone Number","Address",'Due Premium Count','EMI Amount',"Due Penalty",'Total',"Dealer Name","Guarantor Name","Guarantor Address","Guarantor Phone"),       //heads
                array('AccountNumber', 'scheme_Name','member_Name','member_FatherName','member_PhoneNos','member_CurrentAddress','DuePremiumCount','Amount','PaneltyDUE',"Total",'dealer_DealerName','Nominee','MinorNomineeParentName','RelationWithNominee'),       //fields
                array('member_PhoneNos','PaneltyDUE','DuePremiumCount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        
        
        
    }


    function plEMIDueListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "PL EMI Due List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.plEMIDueList")
                ->setColumns(2)
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }



    function plEMIDueList(){
        xDeveloperToolBars::onlyCancel("report_cont.plEMIDueListForm", "cancel", "PL EMI Due List");

        $a= new Account();

        $p = $a->premiums;

        $p->select_func('COUNT', '*', 'count');
        $p->where("PaidOn is null");
        $p->where("DueDate between '".inp("fromDate")."' and '".inp("toDate")."'");
        $p->where_related('account', 'id', '${parent}.id');


        $a->select('*, id as PaneltyDUE');
        
        $a->select_subquery($p,'DuePremiumCount');
        $a->select_subquery('(SELECT MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Amount');
        $a->select_subquery('(SELECT count(*) * MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Total');

        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');
        $a->where('branch_id',Branch::getCurrentBranch()->id);

        $a->where('AccountNumber like "pl%"');
        $a->or_where('AccountNumber like "sl%"');

        $a->having("DuePremiumCount > 0");
        $a->get();
//        echo $a->check_last_query();
        echo getReporttable($a,             //model
                array("Account Number","Scheme","Member Name","Father Name", "Phone Number","Address",'Due Premium Count','EMI Amount',"Due Penalty",'Total',"Guarantor Name","Guarantor Address","Guarantor Phone"),       //heads
                array('AccountNumber', 'scheme_Name','member_Name','member_FatherName','member_PhoneNos','member_CurrentAddress','DuePremiumCount','Amount','PaneltyDUE',"~((#DuePremiumCount * #Amount) + # PaneltyDUE)",'Nominee','MinorNomineeParentName','RelationWithNominee'),       //fields
                array('PaneltyDUE','~((#DuePremiumCount * #Amount) + # PaneltyDUE)'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        
        
        
    }



    function loanNPAListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan EMI Due List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loanNPAList")
                ->setColumns(2)
                ->lookupDB("Dealer Name","name='DealerName' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
                    array("a" => "b"),
                    array("id","Name","Address"),"Name")
                ->_()
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }


    function loanNPAList(){
        xDeveloperToolBars::onlyCancel("report_cont.loanNPAListForm", "cancel", "Loan NPA List");
/*        $q ="select a.AccountNumber,m.Name, m.PermanentAddress, m.FatherName, m.PhoneNos,p.Amount,d.DealerName,

(select count(*) as cnt from jos_xpremiums where accounts_id = a.id and DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND PaidOn is NULL) as premiumcount, a.RdAmount, a.Nominee,a.MinorNomineeParentName,a.RelationWithNominee

from jos_xaccounts a join jos_xmember m on a.member_id=m.id
join jos_xpremiums p on a.id=p.accounts_id
join jos_xschemes s on s.id=a.schemes_id
left join jos_xdealer d on a.dealer_id = d.id
where
s.SchemeType = 'Loan' AND
p.DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND
d.DealerName like '%".inp("DealerName")."%' AND
p.PaidOn is NULL

GROUP BY p.accounts_id
HAVING
premiumcount >= 3 and premiumcount <= 4
";
        $data['result'] = $this->db->query($q)->result();
        JRequest::setVar("layout","loanemiduelist");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        */
        
         $a= new Account();

        $p = $a->premiums;

        $p->select_func('COUNT', '*', 'count');
        $p->where("PaidOn is null");
        $p->where("DueDate between '".inp("fromDate")."' and '".inp("toDate")."'");
        $p->where_related('account', 'id', '${parent}.id');


        $a->select('*, id as PaneltyDUE');
        $a->include_related('dealer','DealerName');
        $a->include_related('agent/member','Name');
        $a->include_related('agent/member','PhoneNos');

        $a->select_subquery($p,'DuePremiumCount');
        $a->select_subquery('(SELECT MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Amount');
        $a->select_subquery('(SELECT count(*) * MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id AND DueDate between "'.inp('fromDate').'" and "'. inp('toDate'). '" and PaidOn is null)','Total');
        //$a->select('(DuePremiumCount * Amount) as Total');

        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');

        $a->where_related('scheme','SchemeType like' ,'loan');
        $a->where_related('dealer',"DealerName like '%".inp('DealerName')."%'");

        $a->having("DuePremiumCount >= 3 AND DuePremiumCount <= 4 ");
        $a->get();
//        echo $a->check_last_query();
        $data['report'] =  getReporttable($a,             //model
                array("Account Number","Scheme","Member Name","Father Name", "Phone Number","Address",'Due Premium Count','EMI Amount',"Due Penalty","Total","Dealer Name","Guarantor Name","Guarantor Address","Guarantor Phone"),       //heads
                array('AccountNumber', 'scheme_Name','member_Name','member_FatherName','member_PhoneNos','member_CurrentAddress','DuePremiumCount','Amount','PaneltyDUE','~(#DuePremiumCount * #Amount) + #PaneltyDUE','dealer_DealerName','Nominee','MinorNomineeParentName','RelationWithNominee'),       //fields
                array('PaneltyDUE','DuePremiumCount','~(#DuePremiumCount * #Amount) + #PaneltyDUE'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
        
    }


    function loanHardRecoveryListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan EMI Due List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loanHardRecoveryList")
                ->setColumns(2)
                ->lookupDB("Dealer Name","name='DealerName' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
                    array("a" => "b"),
                    array("id","Name","Address"),"Name")
                ->_()
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    
    function loanHardRecoveryList(){
        xDeveloperToolBars::onlyCancel("report_cont.loanHardRecoveryListForm", "cancel", "Loan Hard Recovery List");
/*        $q ="select a.AccountNumber,m.Name, m.PermanentAddress, m.FatherName, m.PhoneNos,p.Amount,d.DealerName,

(select count(*) as cnt from jos_xpremiums where accounts_id = a.id and DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND PaidOn is NULL) as premiumcount, a.RdAmount, a.Nominee,a.MinorNomineeParentName,a.RelationWithNominee

from jos_xaccounts a join jos_xmember m on a.member_id=m.id
join jos_xpremiums p on a.id=p.accounts_id
join jos_xschemes s on s.id=a.schemes_id
left join jos_xdealer d on a.dealer_id = d.id
where
s.SchemeType = 'Loan' AND
p.DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND
d.DealerName like '%".inp("DealerName")."%' AND
p.PaidOn is NULL

GROUP BY p.accounts_id
HAVING
premiumcount >= 5
";
        $data['result'] = $this->db->query($q)->result();
        JRequest::setVar("layout","loanemiduelist");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
*/      
	 $a= new Account();

        $p = $a->premiums;
        
        $p->select_func('COUNT', '*', 'count');
        $p->where("PaidOn is null");
        $p->where("DueDate between '".inp("fromDate")."' and '".inp("toDate")."'");
        $p->where_related('account', 'id', '${parent}.id');

        
        $a->select('*, id as PaneltyDUE');
        //$a->select('*');
        $a->include_related('dealer','DealerName');
        $a->include_related('agent/member','Name');
        $a->include_related('agent/member','PhoneNos');

        $a->select_subquery($p,'DuePremiumCount');
        $a->select_subquery('(SELECT MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Amount');
        $a->select_subquery('(SELECT count(*) * MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Total');

        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');

        $a->where_related('scheme','SchemeType like' ,'loan');
        $a->where_related('dealer',"DealerName like '%".inp('DealerName')."%'");

        $a->having("DuePremiumCount >=",5);
        $a->get();
        //echo $a->check_last_query();
        echo getReporttable($a,             //model
                array("Account Number","Scheme","Member Name","Father Name", "Phone Number","Address",'Due Premium Count','EMI Amount',"Due Penalty",'Total',"Dealer Name","Guarantor Name","Guarantor Address","Guarantor Phone"),       //heads
                array('AccountNumber', 'scheme_Name','member_Name','member_FatherName','member_PhoneNos','member_CurrentAddress','DuePremiumCount','Amount','PaneltyDUE',"Total",'dealer_DealerName','Nominee','MinorNomineeParentName','RelationWithNominee'),       //fields
                array('member_PhoneNos','PaneltyDUE','DuePremiumCount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();

  
    }

	    function RDPremiumDueListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "RD Premium Due List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.RDPremiumDueList")
                ->setColumns(2)
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    function RDPremiumDueList(){
        xDeveloperToolBars::onlyCancel("report_cont.RDPremiumDueListForm", "cancel", "RD Premium Due List");
        $q ="select a.AccountNumber,m.Name, m.PermanentAddress, m.FatherName, m.PhoneNos,p.Amount,a.agents_id,a.created_at,

(select count(*) as cnt from jos_xpremiums where accounts_id = a.id and DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND PaidOn is NULL) as premiumcount

from jos_xaccounts a join jos_xmember m on a.member_id=m.id
join jos_xpremiums p on a.id=p.accounts_id
join jos_xschemes s on s.id=a.schemes_id
where
s.SchemeType = 'recurring' AND
p.DueDate BETWEEN '".inp('fromDate')."' AND '".inp('toDate')."' AND
p.PaidOn is NULL AND
a.branch_id=".Branch::getCurrentBranch()->id."
GROUP BY p.accounts_id
";
        $data['result'] = $this->db->query($q)->result();
        JRequest::setVar("layout","rdpremiumduelist");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }


 function loanReceiptReportForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan Dispatch Report");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loanReceiptReport")
                ->setColumns(2)
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    function loanReceiptReport(){
        xDeveloperToolBars::onlyCancel("report_cont.loanReceiptReportForm", "cancel", "Loan Dispatch");
        $a= new Account();
        $a->select('*, id as ActualCurrentBalance');
        $a->select('premiums_jos_xpremiums.Amount as Amount, premiums_jos_xpremiums.Amount * jos_xschemes.NumberOfPremiums as Total');
        $a->include_related('dealer','DealerName');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');
        $a->include_related('scheme','NumberOfPremiums');
        $a->where('created_at >=',inp("fromDate"));
        $a->where('created_at <=',inp("toDate"));
        $a->where("DefaultAC",0);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where_related('scheme','SchemeType like' ,'loan');
        $a->where_related('premiums',"id >",0);
        $a->group_by('premiums_jos_xpremiums.accounts_id');
        $a->get();


//        $a->check_last_query();
        $data['report'] = getReporttable($a,             //model
                array("Account Number","Account Opeming Date","Dealer","Member Name","Father Name","Address", "Scheme","Phone Number","No Of EMI","Amount","Total"),       //heads
                array('AccountNumber','created_at','dealer_DealerName','member_Name','member_FatherName','member_CurrentAddress', 'scheme_Name','member_PhoneNos','scheme_NumberOfPremiums','Amount','Total'),       //fields
                array('Total'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }


    function testAgent(){
        xDeveloperToolBars::onlyCancel("report_cont.loanReceiptReportForm", "cancel", "Loan Receipt");
        
        $a= new Premium();
        $a->include_related("member","Name");
        $a->include_related("member","FatherName");
        $a->include_related("member","PermanentAddress");
        $a->include_related("member","PhoneNos");
        $a->where_related("member","branch_id",Branch::getCurrentBranch()->id);
        $a->get();
        

//        $a->check_last_query();
        $data['report'] = getReporttable($a,             //model
                array("Agent Name","Father Name","Address","Phone Nos","AgentCode","AccountNumber"),       //heads
                array('member_Name','member_FatherName',"member_PermanentAddress","member_PhoneNos","AgentCode","AccountNumber"),       //fields
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
    }

    function rdPremiumReceivedListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "RD Premium Received List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.rdPremiumReceivedList")
                ->setColumns(2)
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();
     }    

     function rdPremiumReceivedList(){
         xDeveloperToolBars::onlyCancel("report_cont.rdPremiumReceivedListForm", "cancel", "RD Premium Received List");
         $a=new Premium();
         $a->select('SUM(Amount) as TAmount');
         $a->where('PaidOn >=',inp('fromDate'));
         $a->where('PaidOn <=',inp('toDate'));
         $a->include_related('account/member','Name');
         $a->include_related('account','AccountNumber');
         $a->include_related('account/agent/member','Name');
         $a->include_related('account/agent','id');
         $a->where_related("account","branch_id",Branch::getCurrentBranch()->id);
         $a->where_related('account/scheme','SchemeType','Recurring');
         $a->limit(300,JRequest::getVar('page_start',0)*300);
         $a->group_by('accounts_id');
         $a->get();


         $data['report'] = getReporttable($a,             //model
                array("Account Number",       "Name",               "Amount Deposited","Advisor Name","Advisor Code"),       //heads
                array('account_AccountNumber','account_member_Name',"TAmount",         "account_agent_member_Name", "account_agent_id"),       //fields
                array("TAmount"),        //totals_array
                array(),        //headers
                array('sno'=>true,"sno_start"=>JRequest::getVar('page_start',0)*300,"page"=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }



   function loanEMIReceivedListForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Loan EMI Received List");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.loanEMIReceivedList")
                ->setColumns(2)
                ->lookupDB("Dealer Name","name='DealerName' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_dealer&format=raw",
                    array("a" => "b"),
                    array("id","Name","Address"),"Name")
                ->_()
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();
     }    

     function loanEMIReceivedList(){
         xDeveloperToolBars::onlyCancel("report_cont.loanEMIReceivedListForm", "cancel", "Loan EMI Received List");
         $a=new Premium();
         $a->select('SUM(Amount) as TAmount');
         $a->where('PaidOn >=',inp('fromDate'));
         $a->where('PaidOn <=',inp('toDate'));
         $a->include_related('account/member','Name');
         $a->include_related('account','AccountNumber');
         $a->include_related('account/dealer','DealerName');
         $a->include_related('account/member','FatherName');
         $a->where_related('account/dealer','DealerName like "%'.inp('DealerName') . '%"');
         $a->where_related("account","branch_id",Branch::getCurrentBranch()->id);
         $a->where_related('account/scheme','SchemeType','Loan');
         $a->limit(300,JRequest::getVar('page_start',0)*300);
         $a->group_by('accounts_id');
         $a->get();


         $data['report'] = getReporttable($a,             //model
                array("Account Number",       "Name", "Father Name", "Amount Deposited"),       //heads
                array('account_AccountNumber','account_member_Name',"account_member_FatherName", "TAmount"),       //fields
                array("TAmount"),        //totals_array
                array("Dealer Name" => "account_dealer_DealerName"),        //headers
                array('sno'=>true,"sno_start"=>JRequest::getVar('page_start',0)*300,"page"=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }

     
     function tdsReportForm(){
         xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "TDS Report");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.tdsReport")
                ->setColumns(2)
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();
     }


     function tdsReport(){
         xDeveloperToolBars::onlyCancel("report_cont.tdsReportForm", "cancel", "TDS Report");

         $a= new Transaction();
         //$a->select('SUM(amountDr)',"Commission");
         //$a->select('SUM(amountCr)',"Savings");
         $a->select_subquery('(SELECT SUM(amountCr) FROM jos_xtransactions WHERE voucher_no=${parent}.voucher_no AND amountCr<>0 AND id<>${parent}.id)',"TDS");
         $a->select_subquery('(SELECT jos_xaccounts.AccountNumber FROM jos_xtransactions join jos_xaccounts on jos_xtrasactions.accounts_id=jos_xaccounts.id)',"AccountType");
         $a->include_related('account/member/asagent','id');
         $a->include_related('account/agent/member','Name');
         $a->include_related('account/agent/member','FatherName');
         $a->include_related('account/agent/member','PanNo');
         $a->include_related('account','AccountNumber');

         $a->where('created_at >=', inp('fromDate'));
         $a->where('created_at <=', inp('toDate'));
         $a->where('branch_id',Branch::getCurrentBranch()->id);
         $a->where_related('account/member/asagent','id is not null');
         $a->where_related('account/scheme','SchemeType','SavingAndCurrent');
         $a->having("AccountType = 'UDR TDS'");

         

         $a->get(10);
         
         echo $a->check_last_query();
/*
         $data['report'] = getReporttable($a,             //model
                array("Name",       "Father Name", "Pan No","Agent Code", "Comission", "TDS" ,"AccountNumber"),       //heads
                array('account_agent_member_Name','account_agent_member_FatherName',"account_agent_member_PanNo","account_agent_id", "Commission","TDS","account_AccountNumber"),       //fields
                array("Commission"),        //totals_array
                array("From Date" => "fromDate", "To Date" => 'toDate'),        //headers
                array('sno'=>true,"sno_start"=>JRequest::getVar('page_start',0)*300,"page"=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );*/

         $data['report'] = getReporttable($a,             //model
                array("account ID", "Voucher No", "Amt CR", "Amt DR" ,"AccountNUmber","AgentID","TDS"),       //heads
                array('accounts_id','voucher_no', "amountCr","amountDr","account_AccountNumber","account_member_asagent_id","TDS"),       //fields
                array(),        //totals_array
                array("From Date" => "fromDate", "To Date" => 'toDate'),        //headers
                array('sno'=>true,"sno_start"=>JRequest::getVar('page_start',0)*300,"page"=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );



        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }
     



       function premiumCrudForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Premiums CRUD");
        $this->load->library("form");
        $form=$this->form->open("pSearch","index.php?option=com_xbank&task=report_cont.premiumsCrud")
                ->setColumns(2)
                ->lookupDB("Account Number","name='AccountNumber' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.get_loan_account&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber")
        ->submit("Search");
        echo $this->form->get();
        $this->jq->getHeader();
    }

    function premiumsCrud(){
        xDeveloperToolBars::onlyCancel("report_cont.premiumCrudForm", "cancel", "Premiums CRUD");
        $ac = new Account();
        $ac->where("AccountNumber",inp('AccountNumber'))->get();
        if(!$ac->result_count()){
            echo "No such Account.";
        }else{
            $p = new Premium();
            $p->where("accounts_id",$ac->id)->get();
            $data['p'] = $p;
            $data["formcomponents"] = $this->load->library("formComponents");
            $data['AccountNumber'] = $ac->AccountNumber;
            $data['ac_id'] = $ac->id;
            JRequest::setVar("layout","premiumcrud");
            $this->load->view("report.html", $data);
            $this->jq->getHeader();
        }
                    

    }

    function setPremiums(){
        $ac = new Account(inp("ac_id"));
        $p = new Premium();
        $p->where("accounts_id",inp("ac_id"))->get();
        foreach($p as $pr){
            $pr->Paid = inp("Paid_$pr->id");
            $pr->PaidOn = (inp("PaidOn_$pr->id")?inp("PaidOn_$pr->id"):null);
            $pr->AgentCommissionSend = (inp("AgentCommissionSend_$pr->id")? 1 : 0);
            $pr->save();
        }

        re("report_cont.premiumCrudForm","Premiums of $ac->AccountNumber updated successfully");
    }


}

?>
