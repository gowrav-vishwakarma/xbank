<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CALCULATING INTEREST FOR FIXED ACCOUNTS
 $CI = & get_instance();

$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_FIXED);
$schemes->where("InterestToAnotherAccount",0);
$schemes->where("InterestToAnotherAccountPercent",0)->get();

//$schemes->check_last_query();
foreach ($schemes as $sc) {
    $query = "UPDATE jos_xaccounts as a SET a.CurrentInterest=round(a.CurrentInterest + (a.CurrentBalanceCr * $sc->Interest * DATEDIFF('" . $i . "', a.LastCurrentInterestUpdatedAt)/36500),2), a.LastCurrentInterestUpdatedAt='" . $i . "' WHERE a.schemes_id=$sc->id and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . $i . "' and a.branch_id = " . $b->id;
    executeQuery($query);
//    $q = Doctrine_Query::create()
//                    ->select("a.AccountNumber, a.CurrentInterest")
//                    ->from("Accounts a")
    $accounts= $CI->db->query("select a.AccountNumber, a.CurrentInterest from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . $i . "' and a.branch_id = " . $b->id);
//    $accounts = $q->execute();

    if ($accounts->num_rows() == 0)
        continue;


//    $t = Doctrine_Query::create()
//                    ->select("a.CurrentInterest")
//                    ->from("Accounts a")
    $totals = 0;
    $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.ActiveStatus = 1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . $i . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;
//    $tot = $t->execute();
//    foreach ($tot as $total)
//        $totals +=$total->CurrentInterest;

    $schemeName = $sc->Name;

    $creditAccount = array(
        $b->Code . SP . INTEREST_PROVISION_ON . $schemeName => $totals
    );

    $debitAccount = array(
        $b->Code . SP . INTEREST_PAID_ON . $schemeName => $totals
    );

    Transaction::doTransaction($debitAccount, $creditAccount, "FD monthly Interest Deposited in $schemeName", TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " +0 day")));

    // INTEREST TRANSFER
    $debitAccount2 = array(
        $b->Code . SP . INTEREST_PROVISION_ON . $schemeName => $totals
    );
	
	$creditAccount2 = array();
    // INTEREST TRANSFER IN FIXED ACCOUNTS
    foreach ($accounts->result() as $acc) {
        $creditAccount2 += array($acc->AccountNumber => $acc->CurrentInterest);
    }

    Transaction::doTransaction($debitAccount2, $creditAccount2, "Maturity Interest posting to Fixed Accounts", TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " +0 day")));



    $q = "UPDATE jos_xaccounts as a join jos_xschemes as s SET a.CurrentInterest=0,a.MaturedStatus=1, a.affectsBalanceSheet=1 WHERE s.id=$sc->id and s.InterestToAnotherAccount=0 and s.InterestToAnotherAccountPercent=0 and a.ActiveStatus=1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . $i . "' and a.branch_id=" . $b->id;
    executeQuery($q);
}


// INTEREST TRANSFER IN MIS ACCOUNTS

$schemes = new Scheme();
$schemes->where("SchemeType", ACCOUNT_TYPE_FIXED );
$schemes->where("InterestToAnotherAccount <>", 0);
$schemes->where("InterestToAnotherAccountPercent", 0)->get();

//$q = Doctrine_Query::create()
//                ->select("*")
//                ->from("Schemes")
//                ->where("SchemeType='" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount != 0 and InterestToAnotherAccountPercent = 0");
//$schemes = $q->execute();

foreach ($schemes as $sc) {
//    $q = Doctrine_Query::create()
//                    ->select("a.AccountNumber, a.CurrentInterest")
//                    ->from("Accounts a")
//                    ->where("schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
//    $accounts = $q->execute();
    $accounts= $CI->db->query("select a.AccountNumber, a.CurrentInterest from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . $i . "' and a.branch_id = " . $b->id);

    if ($accounts->num_rows() == 0)
        continue;

    foreach ($accounts->result() as $acc) {
        $interestToAcc = "";
        if ($acc->InterestToAccount != ""){
            $interestToAcc = new Account();
            $interestToAcc->where("id",$acc->InterestToAccount);
            $interestToAcc->where("ActiveStatus",1)->get();
        }
//            $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
//                     $interestToAcc->CurrentInterest= $interestToAcc->CurrentInterest + $acc->CurrentBalanceCr *
        if ($interestToAcc->result_count()) {
            $days = my_date_diff($i, $acc->LastCurrentInterestUpdatedAt);
            $interest = round(($acc->CurrentBalanceCr * $days['days_total'] * $sc->Interest / 36500),2);
            $creditAccount = array($acc->AccountNumber => $interest);

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => $interest
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " +0 day")));


            $creditAccount = array($interestToAcc->AccountNumber => $interest);

            $debitAccount = array(
                $acc->AccountNumber => $interest
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " +0 day")));

//                     $query="update accounts as a join accounts as b on a.InterestToAccount=b.id set b.CurrentInterest=b.CurrentInterest + ((a.CurrentBalanceCr * DATEDIFF('".getNow("Y-m-d")."',a.LastCurrentInterestUpdatedAt) * $sc->Interest)/36500), a.LastCurrentInterestUpdatedAt = '".getNow("Y-m-d")."' where a.id=$acc->id and b.id=$interestToAcc->id and a.branch_id = ".$b->id;
//                     executeQuery($query);

            $query = "Update jos_xaccounts set MaturedStatus=1 where id=$acc->id";
            executeQuery($query);
        }
    }
}


// HID ACCOUNTS :: :: TODO- SEPERATE IT
//$q = Doctrine_Query::create()
//                        ->select("id, Name, Interest, InterestToAnotherAccountPercent")
//                        ->from("schemes")
//                        ->where("Schemetype = '" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount = 1 and InterestToAnotherAccountPercent != 0");
//        $schemes = $q->execute();


$schemes = new Scheme();
$schemes->where("SchemeType", ACCOUNT_TYPE_FIXED );
$schemes->where("InterestToAnotherAccount ", 1);
$schemes->where("InterestToAnotherAccountPercent <>", 0)->get();


        foreach ($schemes as $sc) {

//            $q = Doctrine_Query::create()
//                            ->select("a.*")
//                            ->from("accounts a")
//                            ->where("a.schemes_id = $sc->id AND a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
//            $accounts = $q->execute();

            $accounts= $CI->db->query("select a.AccountNumber, a.CurrentInterest from jos_xaccounts a where a.schemes_id = $sc->id and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . $i . "' and a.branch_id = " . $b->id);

            if ($accounts->num_rows() == 0)
                continue;

            foreach ($accounts->result() as $acc) {
                $interestToAcc = "";
                if ($acc->InterestToAccount != ""){
            $interestToAcc = new Account();
            $interestToAcc->where("id",$acc->InterestToAccount);
            $interestToAcc->where("ActiveStatus",1)->get();
        }
//                    $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
                if ($interestToAcc->result_count()) {
                    $query = "Update jos_xaccounts set CurrentInterest=CurrentInterest + ((CurrentBalanceCr - CurrentBalanceDr) *  $sc->InterestToAnotherAccountPercent * DATEDIFF('" .$i . "',LastCurrentInterestUpdatedAt)/(12 * 36500)), LastCurrentInterestUpdatedAt = '" . $i . "' where id=$acc->id and branch_id = " . $b->id;
                    executeQuery($query);


                    $interest = round((($sc->InterestToAnotherAccountPercent * $acc->RdAmount) / 100),2);
                    $creditAccount = array($acc->AccountNumber => $acc->CurrentInterest);

                    $debitAccount = array(
                        $b->Code . SP . INTEREST_PAID_ON . $schemeName => $acc->CurrentInterest
                    );
                    Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_HID_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " +0 day")));


                    $creditAccount = array($interestToAcc->AccountNumber => $interest);

                    $debitAccount = array(
                        $acc->AccountNumber => $interest
                    );
                    Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_HID_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " +0 day")));




                    $q = "update jos_xaccounts set CurrentInterest = 0, MaturedStatus=0 where id=$acc->id and branch_id = " . $b->id;
                    executeQuery($q);



                }
            }
        }

