<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// FD Prvision of INTEREST
$query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.CurrentInterest=(a.CurrentBalanceCr * s.Interest * DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' and s.InterestToAnotherAccount=0 and s.InterestToAnotherAccountPercent=0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id;
executeQuery($query);

 $CI = & get_instance();

$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_FIXED);
$schemes->where("InterestToAnotherAccount",0);
$schemes->where("InterestToAnotherAccountPercent",0)->get();
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

    $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);

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
    $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;



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

//MIS MONTHLY INTEREST TRANSFER

$query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.LastCurrentInterestUpdatedAt=DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) WHERE s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' and s.InterestToAnotherAccount=1 and s.InterestToAnotherAccountPercent=0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id;
executeQuery($query);

//$q = Doctrine_Query::create()
//                ->select("id, Name, Interest")
//                ->from("schemes")
//                ->where("Schemetype = '" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount = 1 and InterestToAnotherAccountPercent = 0");
//$schemes = $q->execute();

$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_FIXED);
$schemes->where("InterestToAnotherAccount", 1);
$schemes->where("InterestToAnotherAccountPercent", 0)->get();

foreach ($schemes as $sc) {

//    $q = Doctrine_Query::create()
//                    ->select("accounts.* ")
//                    ->from("accounts")
//                    ->where("schemes_id = $sc->id AND accounts.ActiveStatus =1 and accounts.MaturedStatus=0 and accounts.created_at < '" . getNow("Y-m-d") . "' and accounts.branch_id = " . $b->id);
//    $accounts = $q->execute();
//
//    if ($accounts->count() == 0)
//        continue;

    $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);

    if ($accounts->num_rows() == 0)
        continue;

    foreach ($accounts->result() as $acc) {
        $account_count = 0;
        if ($acc->InterestToAccount != ""){
            $interestToAcc = new Account();
            $interestToAcc->where("id",$acc->InterestToAccount);
            $interestToAcc->where("ActiveStatus",1)->get();
            $account_count = $interestToAcc->result_count();
        }
//            $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
//                     $interestToAcc->CurrentInterest= $interestToAcc->CurrentInterest + $acc->CurrentBalanceCr *
        if ($account_count) {
            $days = my_date_diff(getNow("Y-m-d"), date("Y-m-d",strtotime($acc->LastCurrentInterestUpdatedAt)));
            if($days['days_total'] < 30 && date("m",strtotime(getNow("Y-m-d"))!=2))
                $interest = $acc->RdAmount * $days['days_total'] * $sc->Interest / 36500;
            else
                $interest = $acc->RdAmount * $sc->Interest / 1200;
            $creditAccount = array($acc->AccountNumber => $interest);

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $sc->Name => $interest
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));


            $creditAccount = array($interestToAcc->AccountNumber => $interest);

            $debitAccount = array(
                $acc->AccountNumber => $interest
            );
            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));

//                     $query="update accounts as a join accounts as b on a.InterestToAccount=b.id set b.CurrentInterest=b.CurrentInterest + ((a.CurrentBalanceCr * DATEDIFF('".getNow("Y-m-d")."',a.LastCurrentInterestUpdatedAt) * $sc->Interest)/36500), a.LastCurrentInterestUpdatedAt = '".getNow("Y-m-d")."' where a.id=$acc->id and b.id=$interestToAcc->id and a.branch_id = ".$b->id;
//                     executeQuery($query);
        }
    }

//                $query="update accounts as a join accounts as b on a.InterestToAccount=b.id set b.CurrentInterest=b.CurrentInterest + ((a.CurrentBalanceCr * DATEDIFF('".getNow()."',a.LastCurrentInterestUpdatedAt) * $sc->Interest)/36500), a.LastCurrentInterestUpdatedAt = '".getNow()."' where a.ActiveStatus =1 and a.LockingStatus = 0 and b.ActiveStatus =1 and b.LockingStatus = 0 and a.branch_id = ".$b->id;
//                executeQuery($query);
}


/* TODO :: UNCOMMENT AFTER HALFYEARLY CLOSING OF 01-10-2011


//        HID SCHEME HERE :: TODO- SEPERATE IT
$q = Doctrine_Query::create()
                ->select("id, Name, Interest, InterestToAnotherAccountPercent")
                ->from("schemes")
                ->where("Schemetype = '" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount = 1 and InterestToAnotherAccountPercent != 0");
$schemes = $q->execute();

foreach ($schemes as $sc) {

    $q = Doctrine_Query::create()
                    ->select("accounts.* ")
                    ->from("accounts")
                    ->where("schemes_id = $sc->id AND accounts.ActiveStatus =1  and accounts.MaturedStatus=0 and accounts.created_at < '" . getNow("Y-m-d") . "' and accounts.branch_id = " . $b->id);
    $accounts = $q->execute();

    if ($accounts->count() == 0)
        continue;

    foreach ($accounts as $acc) {
        $interestToAcc = "";
        if ($acc->InterestToAccount != "")
            $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
        if ($interestToAcc) {
            $query = "Update accounts set CurrentInterest=CurrentInterest + ((CurrentBalanceCr - CurrentBalanceDr) *  $sc->InterestToAnotherAccountPercent * DATEDIFF('" . getNow("Y-m-d") . "',LastCurrentInterestUpdatedAt)/(12 * 36500)), LastCurrentInterestUpdatedAt = '" . getNow("Y-m-d") . "' where id=$acc->id and branch_id = " . $b->id;
            executeQuery($query);
        }
    }
}
 * 
 */
?>
