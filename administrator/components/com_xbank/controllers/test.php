<?php
/**
 * /tmp/phptidy-sublime-buffer.php
 *
 * @package default
 */


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class test extends CI_Controller {


    /**
     *
     *
     * @param unknown $xdate (optional)
     */
    function index1($xdate='2011-10-31') {
        $date = $xdate;
        $day = date("d", strtotime(date("Y-m-d", strtotime($xdate))));
        for ($i = 1; $i <= 12; $i++) {
            echo $date . "<br>";
            $ndate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . "+1 MONTH"));
            if (date("d", strtotime(date("Y-m-d", strtotime($ndate)))) != $day) {
                $tmp = date("Y-m-28", strtotime(date("Y-m-28", strtotime($date)) . "+1 MONTH"));
                $ndate = $this->db->query("select LAST_DAY('" . $tmp . "') as lastdate")->row()->lastdate;
            }
            $date = $ndate;
        }


        $schemes = new Scheme();
        $schemes->where("SchemeType", ACCOUNT_TYPE_FIXED);
        $schemes->where("InterestToAnotherAccount", 0);
        $schemes->where("InterestToAnotherAccountPercent", 0)->get();

        $schemes->check_last_query();
        $created_at = "2012-09-21";
        if (date("m", strtotime($created_at)) <= 3) {
            $year = date("Y", strtotime($created_at)) - 1;
            $startDate = $year . "-04-01";
            $endDate = date("Y", strtotime($created_at)) . "-03-31";
        }
        if (date("m", strtotime($created_at)) > 3) {
            $year = date("Y", strtotime($created_at)) + 1;
            $startDate = date("Y", strtotime($created_at)) . "-04-01";
            $endDate = $year . "-03-31";
        }
        echo $startDate . "<br>" . $endDate . "<br>";

        echo Agent::getAgentFromAccount(9308) . "<br>";
        $agent = new Agent();
        $agent->where("AccountNumber", "SBJH365")->get();
        echo $agent->member->branch_id;
    }


    /**
     *
     */
    function sbCorrection() {
        set_time_limit(5000);
        $b = Branch::getCurrentBranch();
        $interest = 0;
        // SET LASTCURRENTINTERESTUPDATEDAT = NOW() - 6 MONTHS, CURRENTINTEREST  = 0

        try {
            $this->db->trans_begin();
            $q = "UPDATE jos_xaccounts a
        join jos_xtransactions t on t.accounts_id=a.id
        join jos_xschemes s on s.id=a.schemes_id
        SET a.LastCurrentInterestUpdatedAt = IF(a.created_at > DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) , a.created_at , DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH)),
        a.CurrentInterest = 0
        where t.branch_id=" . Branch::getCurrentBranch()->id . " and
        t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '" . getNow("Y-m-d") . "' and
        s.SchemeType ='" . ACCOUNT_TYPE_BANK . "' and
        a.ActiveStatus = 1";
            executeQuery($q);
            $CI = & get_instance();
            //$accounts=Doctrine_Query::create()
            //                ->select(" t.*,a.id, s.Interest as Interest")
            //                ->from("Transactions t")
            //                ->innerJoin(" t.Accounts a ")
            //                ->innerJoin("a.Schemes s")
            //                ->where("t.branch_id=".Branch::getCurrentBranch()->id." and
            //                        t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '".  getNow("Y-m-d")."' and
            //                        s.SchemeType ='".ACCOUNT_TYPE_BANK."' and a.ActiveStatus = 1")
            //                ->orderBy("t.created_at");
            //$accounts = $accounts->execute();


            $accounts = $CI->db->query("select t.*, a.id as id, s.Interest from jos_xtransactions t
                            join jos_xaccounts a on a.id=t.accounts_id
                            join jos_xschemes s on s.id=a.schemes_id
                            where t.branch_id =" . Branch::getCurrentBranch()->id . " and
                                t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '" . getNow("Y-m-d") . "' and
                                s.SchemeType ='" . ACCOUNT_TYPE_BANK . "' and
                                a.ActiveStatus = 1
                                order by t.created_at")->result();


            foreach ($accounts as $a) {
                //    $queryA=Doctrine_Query::create()
                //                            ->select(" sum(t.amountCr) as CRSum")
                //                            ->from(" Transactions t")
                //                            ->innerJoin("t.Accounts a")
                //                            ->where("t.branch_id=".Branch::getCurrentBranch()->id." and t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and a.id = $a->accounts_id");
                //    $CRSum = $queryA->execute()->getFirst()->CRSum;
                $queryA = $CI->db->query("select sum(t.amountCr) as CRSum from jos_xtransactions t
                            left join jos_xaccounts a on t.accounts_id=a.id
                            where t.branch_id=" . Branch::getCurrentBranch()->id . " and
                                t.created_at > a.LastCurrentInterestUpdatedAt and
                                a.id = $a->accounts_id")->row();
                //t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and

                $CRSum = $queryA->CRSum;


                if ($CRSum)
                    $CRSum = $CRSum;
                else
                    $CRSum = 0;
                //    $queryB=Doctrine_Query::create()
                //                            ->select(" sum(t.amountDr) as DRSum")
                //                            ->from(" Transactions t")
                //                            ->innerJoin("t.Accounts a")
                //                            ->where("t.branch_id=".Branch::getCurrentBranch()->id." and t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and a.id = $a->accounts_id");
                //    $DRSum = $queryB->execute()->getFirst()->DRSum;


                $queryB = $CI->db->query("select sum(t.amountDr) as DRSum from jos_xtransactions t
                            join jos_xaccounts a on t.accounts_id=a.id
                            where t.branch_id=" . Branch::getCurrentBranch()->id . " and
                                t.created_at > a.LastCurrentInterestUpdatedAt and
                                a.id = $a->accounts_id")->row();
                $DRSum = $queryB->DRSum;
                if ($DRSum)
                    $DRSum = $DRSum;
                else
                    $DRSum = 0;
                if ($a->accounts_id == 368)
                    $asd = "dnbmvfb";
                //    $q="UPDATE jos_xaccounts AS a
                //        SET a.CurrentInterest = a.CurrentInterest + (IF(((a.CurrentBalanceCr - $CRSum) - (a.CurrentBalanceDr - $DRSum)) > 0 ,((a.CurrentBalanceCr - $CRSum) - (a.CurrentBalanceDr - $DRSum)),0) * $a->Interest * DATEDIFF('$a->created_at',a.LastCurrentInterestUpdatedAt)/36500 ),
                //        a.LastCurrentInterestUpdatedAt = '".$a->created_at."'
                //        WHERE a.id = $a->accounts_id";
                //    executeQuery($q);

                $account = new Account($a->accounts_id);

                //    $interest += $account->CurrentInterest;
                $daydiff = my_date_diff(date("Y-m-d", strtotime($a->created_at)), date("Y-m-d", strtotime($account->LastCurrentInterestUpdatedAt)));
                $intr = ((($account->CurrentBalanceCr - $CRSum) - ($account->CurrentBalanceDr - $DRSum)) > 0 ? (($account->CurrentBalanceCr - $CRSum) - ($account->CurrentBalanceDr - $DRSum)) : 0) * $a->Interest * $daydiff['days_total'] / 36500;
                //    echo date("Y-m-d",strtotime($a->created_at))."--->".(($account->CurrentBalanceCr - $CRSum) - ($account->CurrentBalanceDr - $DRSum))." - ".$intr." - ".$daydiff['days_total']."<br>";
                $account->CurrentInterest += $intr;
                $account->LastCurrentInterestUpdatedAt = $a->created_at;
                $account->save();
            }




            // HALF-YEARLY INTEREST POSTING IN SAVING ACCOUNTS
            $query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.CurrentInterest=round(if(a.CurrentInterest > 0,a.CurrentInterest,0)+((a.CurrentBalanceCr-a.CurrentBalanceDr)*s.Interest*DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500)), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE s.SchemeType='" . ACCOUNT_TYPE_BANK . "' and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and  a.branch_id = " . $b->id;
            executeQuery($query);

            $schemes = new Scheme();
            $schemes->where("Schemetype", ACCOUNT_TYPE_BANK)->get();
            //             $this->db->select("id, Name");
            //             $this->db->from("schemes");
            //             $this->db->where("Schemetype = '". ACCOUNT_TYPE_BANK ."'");
            //             $schemes=$this->db->get();

            foreach ($schemes as $sc) {

                //            $q = Doctrine_Query::create()
                //                            ->select("a.AccountNumber, a.CurrentInterest")
                //                            ->from("Accounts a")
                //                            ->where("a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
                //            $accounts = $q->execute();
                $CI = & get_instance();
                $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);


                //                 $this->db->select("accounts.AccountNumber, accounts.CurrentInterest");
                //                 $this->db->from("accounts");
                //                 $this->db->where("schemes_id = $sc->id AND accounts.CurrentInterest > 0 and accounts.ActiveStatus =1 and accounts.LockingStatus = 0 and accounts.branch_id = ".$b->id);
                //                 $accounts=$this->db->get();

                if ($accounts->num_rows() == 0)
                    continue;


                $t = new Account();
                $t->select("CurrentInterest as CurrentInterest");
                $t->where("schemes_id = " . $sc->id . " AND CurrentInterest > 0 and ActiveStatus = 1 and created_at < '" . getNow("Y-m-d") . "' and branch_id = " . $b->id);
                $t->get();
                $totals = 0;
                foreach ($t as $total)
                    $totals +=$total->CurrentInterest;

                //                 $this->db->select("SUM(accounts.CurrentInterest) As Totals");
                //                 $this->db->from("accounts");
                //                 $this->db->where("schemes_id = ".$sc->id." and ActiveStatus = 1 and LockingStatus = 0 and branch_id = ".$b->id);
                //                 $totals=$this->db->get()->row()->Totals;

                $schemeName = $sc->Name;

                //                 echo "<pre>";
                //                 print_r($accounts->result_array());
                //                 echo "</pre>";

                $creditAccount = array();

                $debitAccount = array(
                    $b->Code . SP . INTEREST_PAID_ON . $schemeName => $totals
                );

                foreach ($accounts->result() as $acc) {
                    $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
                }
                $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => NULL);
                Transaction::doTransaction($debitAccount, $creditAccount, "Saving Account Interst posting", TRA_INTEREST_POSTING_IN_SAVINGS, $voucherNo, date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
            }

            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
    }


    /**
     *
     */
    function schemeTotal() {
        echo date("Y-m-d") . "<br>";
        $d1 = '2012-04-01';
        $d2 = '2012-04-30';
        $scheme = new Scheme();
        $branch = 2;
        echo $scheme->getSchemeTotal(19, $branch, $d1, $d2) . "<br>";
        $a = new Account($id);
        $total = $a->getAccountTotal($d1, $d2, 82);
        echo $total['DR'] - $total['CR'] . "<br><br>";
        //        $h = new BalanceSheet(3);
        //        echo $h->getHeadTotal($h->id, $d1, $d2);
        //        $a = NULL * 2;
        //        echo $a;
    }


    /**
     *
     */
    function provisionCorrection() {
        $CI = & get_instance();
        //        foreach ($branch as $b) {
        $b = Branch::getCurrentBranch();
        $date = "2012-06-01";
        // FD Prvision of INTEREST
        $q = $CI->db->query("UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.LastCurrentInterestUpdatedAt='2012-05-01' WHERE s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' and s.InterestToAnotherAccount=0 and s.InterestToAnotherAccountPercent=0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . $date . "' and a.branch_id = " . $b->id);
        $query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.CurrentInterest= a.CurrentBalanceCr * s.Interest * DATEDIFF('" . $date . "', a.LastCurrentInterestUpdatedAt)/36500 , a.LastCurrentInterestUpdatedAt='" . $date . "' WHERE s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' and s.InterestToAnotherAccount=0 and s.InterestToAnotherAccountPercent=0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . $date . "' and a.branch_id = " . $b->id;
        executeQuery($query);

        $schemes = new Scheme();
        $schemes->where("SchemeType", ACCOUNT_TYPE_FIXED);
        $schemes->where("InterestToAnotherAccount", 0);
        $schemes->where("InterestToAnotherAccountPercent", 0)->get();
        foreach ($schemes as $sc) {

            $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . $date . "' and a.branch_id = " . $b->id);

            if ($accounts->num_rows() == 0)
                continue;

            $totals = 0;
            $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . $date . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;

            $schemeName = $sc->Name;

            $creditAccount = array(
                $b->Code . SP . INTEREST_PROVISION_ON . $schemeName => round($totals)
            );

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => round($totals)
            );

            Transaction::doTransaction($debitAccount, $creditAccount, "FD monthly Interest Deposited in $schemeName", TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 day")));
        }
        //        }
    }


    /**
     *
     */
    function testSession() {
        $format = "Y-m-d H:i:00";
        date_default_timezone_set('Asia/Calcutta');
        $timeStamp = strtotime('now');
        $timeStamp = date($format, $timeStamp);
        echo $timeStamp . "<br>";
        $CI = & get_instance();
        //        $CI->session->set_userdata('currdate', inp("newDate") . " " . getNow("H:i:00"));
        echo $CI->session->userdata('currdate') . "<br>session is set<br>";
        session_start();
        //        if (isset($_SESSION['currdate']))
        //          $_SESSION['currdate'] = '2012-05-21';
        //        else
        //            $_SESSION['currdate'] = getNow("Y-m-d");

        echo $_SESSION['currdate'];
    }


    /**
     *
     */
    function RD_patch() {
        set_time_limit(5000);
        $sc_id = array(84, 85, 86);
        foreach ($sc_id as $sid) {
            $sc = new Scheme($sid);
            $acc = new Account();
            $acc->where("schemes_id", $sc->id);
            $acc->where("DefaultAC", 0)->get();
            foreach ($acc as $ac) {
                $i = 1;
                $premium = new Premium();
                $premium->where("accounts_id", $ac->id);
                $premium->order_by("id", "asc")->get();
                foreach ($premium as $p) {
                    $p->AgentCommissionPercentage = getComission($sc->AccountOpenningCommission, PREMIUM_COMMISSION, $i);
                    $p->save();
                    $i++;
                }
            }
        }
        echo "done";
    }


    /**
     *
     */
    function loanAccountsCount() {
        $a = new Account();
        $a->include_related('member', 'Name');
        $a->where_related('scheme', 'SchemeType', 'Loan');
        $a->where('branch_id', Branch::getCurrentBranch()->id);
        $a->where('ActiveStatus', 1);
        $a->where('DefaultAC', 0);
        $a->get();
        $data['report'] = getReporttable($a, //model
            array("Account Number", "ActiveStatus", "Member"), //heads
            array('AccountNumber', 'ActiveStatus', 'member_Name'), //fields
            array(), //totals_array
            array(), //headers
            array('sno' => true), //options
            "<b>All Active Loan Accounts </b>", //headerTemplate
            '', //tableFooterTemplate
            "", //footerTemplate,
            array()
        );

        $a = new Account();
        $a->include_related('member', 'Name');
        $a->where_related('scheme', 'SchemeType', 'Loan');
        $a->where('branch_id', Branch::getCurrentBranch()->id);
        $a->where('ActiveStatus', 0);
        $a->where('DefaultAC', 0);
        $a->get();
        $data['report'] .= "<br/><br/><br/>" . getReporttable($a, //model
            array("Account Number", "ActiveStatus", 'Member'), //heads
            array('AccountNumber', 'ActiveStatus', 'member_Name'), //fields
            array(), //totals_array
            array(), //headers
            array('sno' => true), //options
            "<b>All DeActivated Loan Accounts </b>", //headerTemplate
            '', //tableFooterTemplate
            "", //footerTemplate,
            array()
        );

        JRequest::setVar("layout", "generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
    }


    /**
     *
     */
    function setSideEntries() {
        // $this->db->query("ALTER TABLE `bhawani`.`jos_xtransactions` ADD INDEX `voucher_no` ( `voucher_no` ) ");
        $this->db->query("
                ALTER TABLE `jos_xtransactions` ADD `side` VARCHAR( 2 ) NOT NULL DEFAULT '--',
                ADD `accounts_in_side` INT NOT NULL ,
                ADD INDEX ( `side` )
            ");
        $this->db->query("
                UPDATE jos_xtransactions tm join
                (
                SELECT
                id,
                IF(amountCr<>0, 'CR',IF(amountDr<> 0,'DR','--')) side
                FROM
                jos_xtransactions tr
                ) tmp on tm.id=tmp.id

                SET tm.side = tmp.side
            ");

        $this->db->query("
                UPDATE jos_xtransactions tm join (
                    SELECT
                    tr.voucher_no,tr.branch_id,  tr.side, tr.transaction_type_id, count(*) accounts_in_side
                    FROM
                    jos_xtransactions tr
                    GROUP BY tr.voucher_no, tr.branch_id, tr.side, tr.transaction_type_id
                    ) tmp on tm.voucher_no=tmp.voucher_no and tm.branch_id=tmp.branch_id  and tm.side=tmp.side and tm.transaction_type_id=tmp.transaction_type_id

                    SET tm.accounts_in_side = tmp.accounts_in_side
            ");
    }


    /**
     *
     */
    function interestProvision() {
        try {
            $this->db->trans_begin();
            $b = Branch::getCurrentBranch();
            $schemes = new Scheme();
            $schemes->where("SchemeType", ACCOUNT_TYPE_FIXED);
            $schemes->where("InterestToAnotherAccount", 0);
            $schemes->where("InterestToAnotherAccountPercent", 0)->get();
            //$q = Doctrine_Query::create()
            //                ->select("*")
            //                ->from("Schemes")
            //                ->where("SchemeType='" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount=0 and InterestToAnotherAccountPercent=0");
            //$schemes = $q->execute();
            //$schemes = Doctrine::getTable("Schemes")->findBySchemetype(ACCOUNT_TYPE_FIXED);
            foreach ($schemes as $sc) {

                //    $q = Doctrine_Query::create()
                //                    ->select("a.AccountNumber, a.CurrentInterest")
                //                    ->from("Accounts a")
                //                    ->where("a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
                //    $accounts = $q->execute();

                $accounts = $this->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);

                if ($accounts->num_rows() == 0)
                    continue;


                //    $t = Doctrine_Query::create()
                //                    ->select("a.CurrentInterest")
                //                    ->from("Accounts a")
                //                    ->where("a.schemes_id = " . $sc->id . " and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
                //    $tot = $t->execute();
                //    $totals = 0;
                //    foreach ($tot as $total)
                //        $totals +=$total->CurrentInterest;

                $totals = 0;
                $totals = $this->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;



                //                 $this->db->select("SUM(accounts.CurrentInterest) As Totals");
                //                 $this->db->from("accounts");
                //                 $this->db->where("schemes_id = ".$sc->id." and ActiveStatus = 1 and LockingStatus = 0 and branch_id = ".$b->id);
                //                 $totals=$this->db->get()->row()->Totals;

                $schemeName = $sc->Name;

                //                 echo "<pre>";
                //                 print_r($accounts->result_array());
                //                 echo "</pre>";

                $creditAccount = array(
                    $b->Code . SP . INTEREST_PROVISION_ON . $schemeName => round($totals)
                );

                $debitAccount = array(
                    $b->Code . SP . INTEREST_PAID_ON . $schemeName => round($totals)
                );

                //                foreach($accounts as $acc){
                //                    $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
                //                }

                Transaction::doTransaction($debitAccount, $creditAccount, "FD monthly Interest Deposited in $schemeName", TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
            }

            $this->db->trans_commit();
            log_message('error', "Closing done on $dateToday");
            echo "<br/>Done";
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }
    }


    /**
     *
     */
    function maturedRDs() {
        $q="SELECT a.AccountNumber, a.created_at, s.MaturityPeriod, a.MaturedStatus FROM jos_xaccounts a JOIN jos_xschemes s on a.schemes_id=s.id WHERE
                DATE_ADD(DATE(a.created_at), INTERVAL s.MaturityPeriod MONTH) >= '2013-04-01' and a.MaturedStatus =1 and a.branch_id=2";

    }


    /**
     *
     */
    function premiumCorrection_old() {

        // Mark wrongly mature rds 
        $q="UPDATE jos_xaccounts a JOIN jos_xschemes s on a.schemes_id=s.id SET a.MaturedStatus=0, a.affectsBalanceSheet=0, a.ActiveStatus=1 WHERE s.SchemeType = '".ACCOUNT_TYPE_RECURRING. "' AND a.id not in (7050,2527,2557,2556) AND a.MaturedStatus = 1";
        $this->db->query($q);

        $a=new Account();
        $a->where('AccountNumber like', str_replace("-", "%", inp('acc')));
        $a->where_related('scheme', 'SchemeType', ACCOUNT_TYPE_RECURRING);
        // $a->where('scheme','SchemeType',ACCOUNT_TYPE_RECURRING);
        $a->get();
        $total_accounts = $a->count();
        $account_count=1;
        foreach ($a as $acc) {
            // echo "Done " . $account_count++ . " out of " . $total_accounts . "<br/>";
            // ob_end_flush();
            $this->db->query("UPDATE jos_xpremiums SET Paid=0 WHERE accounts_id = $acc->id");
            $account_premiums=$acc->premiums
            ->where('DueDate < "2013-04-01"')
            ->order_by('id')
            ->get();

            $i=1;
            $last_i=1;
            $paid=1;
            $is_first_premium=true;
            $one_ahead_paid=false;
            $month_ahead=array();
            foreach ($account_premiums as $p) {
                if ($is_first_premium) {
                    $last_paid_on=$p->PaidOn;
                }
                // echo "working on premium id " . $p->id. " and last_i is $last_i<br/>";
                if (!$is_first_premium and isSameMonth($last_paid_on, $p->DueDate) ) {
                    $this->say("$p->id 1<br/>");
                    $last_i ++;
                    $paid=$last_i;
                    // $paid--;
                    $one_ahead_paid=true;
                    if (isSameMonth($p->PaidOn, $p->DueDate)) {
                        $this->say("$p->id 1.1<br/>");
                        $paid++;
                        $last_i++;
                    }
                }
                elseif (isMonthAhead($p->PaidOn, $p->DueDate)) {
                    $this->say("$p->id 2<br/>");
                    $paid = $last_i;
                    $month_ahead[date('Ym',strtotime($p->PaidOn))] = isset($month_ahead[date('Ym',strtotime($p->PaidOn))])?$month_ahead[date('Ym',strtotime($p->PaidOn))] + 1 : 1;
                    // echo "For " . $p->DueDate . " setting laset <br/>";
                }
                elseif ($p->PaidOn == null) {
                    $this->say("$p->id 3<br/>");
                    // echo "i am here with current Paid on " . $p->PaidOn;
                    if ($last_paid_on !=null and isSameMonth($last_paid_on, $p->DueDate) ) {
                        $this->say("$p->id 3.1<br/>");
                        // $last_i = $i;
                        $paid=$last_i;
                        // $paid--;
                    }else {
                        $this->say("$p->id 3.2<br/>");
                        $paid=$last_i;
                        if(array_key_exists(date('Ym',strtotime($p->DueDate)), $month_ahead)){
                            // echo "searching".date('Ym',strtotime($p->DueDate));
                            $paid += $month_ahead[date('Ym',strtotime($p->DueDate))];
                            $last_i += $month_ahead[date('Ym',strtotime($p->DueDate))];
                            $month_ahead[date('Ym',strtotime($p->DueDate))]--;
                            if($month_ahead[date('Ym',strtotime($p->DueDate))] == 0)
                                unset($month_ahead[date('Ym',strtotime($p->DueDate))]);
                        }
                        // echo "<br/> one_ahead_paid". $one_ahead_paid . "<br/>";
                        // if($one_ahead_paid and $last_paid_on==null)
                        //     $paid--;
                        // else
                            // $paid++;
                        // if ($last_paid_on == null and $p->PaidOn ==null and $one_ahead_paid==true) {
                        //     $this->say("$p->id 3.2.1<br/>");
                        //     $paid--;
                        // }
                    }
                    // $i++;
                }
                else {
                    $this->say("$p->id 4<br/>");
                    $paid=$i;
                    $last_i = $i;
                    if($last_paid_on==null) $paid--;
                    // $i++;
                }
                $p->Paid=$paid;
                $p->save();
                $last_paid_on=$p->PaidOn;
                $i++;
                $is_first_premium=false;
                print_r($month_ahead);
            }
        }

        $p=new Premium();
        $p->include_related('account', 'AccountNumber');
        $p->where('accounts_id', $a->id);
        $p->get();

        $data['report']= getReporttable($p,             //model
            array("id", 'Amount', "Due Date", 'Paid On', 'Paid'),       //heads
            array('id', 'Amount', 'DueDate', 'PaidOn', 'Paid', ),       //fields
            array(),        //totals_array
            array("Account Number"=>'account_AccountNumber'),        //headers
            array('sno'=>true),     //options
            "",     //headerTemplate
            '',      //tableFooterTemplate
            ""      //footerTemplate
        );

        JRequest::setVar("layout", "generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();

    }

    function premiumCorrection() {

        // Mark wrongly mature rds 
        $q="UPDATE jos_xaccounts a JOIN jos_xschemes s on a.schemes_id=s.id SET a.MaturedStatus=0, a.affectsBalanceSheet=0, a.ActiveStatus=1 WHERE s.SchemeType = '".ACCOUNT_TYPE_RECURRING. "' AND a.id not in (7050,2527,2557,2556) AND a.MaturedStatus = 1";
        $this->db->query($q);

        $a=new Account();
        // $a->where('AccountNumber like', str_replace("-", "%", inp('acc'))); //Comment to run on all
        $a->where_related('scheme', 'SchemeType', ACCOUNT_TYPE_RECURRING);
        $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->get();
        // $total_accounts = $a->count();
        // $account_count=1;
        foreach ($a as $acc) {
            // echo "Done " . $account_count++ . " out of " . $total_accounts . "<br/>";
            // ob_end_flush();
            $this->db->query("UPDATE jos_xpremiums SET Paid=0 WHERE accounts_id = $acc->id");
            $due_and_paid_query = $this->db->query("SELECT GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray, GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray FROM jos_xpremiums WHERE accounts_id = $acc->id AND DueDate < '2013-05-01' ORDER BY id")->row();
            $due_array=explode(",",$due_and_paid_query->DueArray);
            $paid_array=explode(",",$due_and_paid_query->PaidArray);

            // print_r($due_array);
            // print_r($paid_array);
            
            $account_premiums=$acc->premiums
            ->where('DueDate < "2013-05-01"')
            ->order_by('id')
            ->get();

            $i=0;
            foreach($account_premiums as $p){
                $paid=0;
                for($j=0;$j<=$i;$j++){
                    if(isset($paid_array[$j]) AND $paid_array[$j] <= $due_array[$i]) $paid++;
                }
                $p->Paid= $paid;
                $p->save();                                
                $i++;
            }
            // ob_flush();
            // flush();
        }

        // $p=new Premium();
        // $p->include_related('account', 'AccountNumber');
        // $p->where('accounts_id', $a->id);
        // $p->get();

        // $data['report']= getReporttable($p,             //model
        //     array("id", 'Amount', "Due Date", 'Paid On', 'Paid'),       //heads
        //     array('id', 'Amount', 'DueDate', 'PaidOn', 'Paid', ),       //fields
        //     array(),        //totals_array
        //     array("Account Number"=>'account_AccountNumber'),        //headers
        //     array('sno'=>true),     //options
        //     "",     //headerTemplate
        //     '',      //tableFooterTemplate
        //     ""      //footerTemplate
        // );

        // JRequest::setVar("layout", "generalreport");
        // $this->load->view('report.html', $data);
        // $this->jq->getHeader();
        echo "done";
    }


    /**
     *
     *
     * @param unknown $msg
     */
    function say($msg) {
        echo $msg;
    }


    function RDCorrection(){

        $b = Branch::getCurrentBranch();
        $branchid =$b->id;
        $transactions = new Transaction();
        $transactions->where('transaction_type_id', 17)->where('created_at','2013-04-30')->where('branch_id', $branchid)->get();
        //$transactions = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, $branchid);
        //$conn = Doctrine_Manager::connection();
        try {
            // $conn->beginTransaction();
//            if(JFactory::getUser()->gid > 23){
            $this->db->trans_begin();
            $this->premiumCorrection();
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
            // log::write("Transaction with voucher number $voucherno for account $foraccount done on date $trans_date deleted by staff ".Staff::getCurrentStaff()->id);
//            re("report_cont.accountTransactions&id=$foraccount","Transaction Deleted Successfully");
            // re("report_cont.dashboard","Transaction Deleted Successfully");
//            }
//            else{
//                re("com_xbank.index","You are not authorized to delete any transaction. Please contact Head Office Admin for this.","error");
//            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }

        try {
            $this->db->trans_begin();
            



            $query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id join (SELECT accounts_id, SUM(Paid*Amount) AS toPost FROM jos_xpremiums WHERE Paid <> 0 AND Skipped =0 And DueDate > '2013-03-31' AND DueDate < '" . getNow("Y-m-d") . "' GROUP BY accounts_id) as p on p.accounts_id=a.id SET a.CurrentInterest=(p.toPost * s.Interest)/1200 WHERE (s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "') and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
            executeQuery($query);


            $schemes = new Scheme();
            $schemes->where("SchemeType",ACCOUNT_TYPE_RECURRING)->get();

            foreach ($schemes as $sc) {
                
                $accounts = $this->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);
                if ($accounts->num_rows() == 0)
                    continue;
                // echo "working recurring for " . $sc->Name. "<br/>";

                $totals = 0;
                $totals = $this->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;


                $schemeName = $sc->Name;

    //                 echo "<pre>";
    //                 print_r($accounts->result_array());
    //                 echo "</pre>";

                $creditAccount = array();

                $debitAccount = array(
                    $b->Code . SP . INTEREST_PAID_ON . $schemeName => round($totals,ROUND_TO)
                );

                foreach ($accounts->result() as $acc) {
                    $creditAccount += array($acc->AccountNumber => round($acc->CurrentInterest,ROUND_TO));
                }

                Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in Recurring Account", TRA_INTEREST_POSTING_IN_RECURRING, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
            }

    //             $this->db->query("UPDATE accounts SET CurrentInterest=0");
    //             $q="UPDATE accounts SET CurrentInterest=0 where branch_id = ".$b->id;
            $q = "UPDATE jos_xaccounts as a join jos_xschemes as s SET a.CurrentInterest=0 WHERE s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
            executeQuery($q);
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
            return;
        }

    }



}
