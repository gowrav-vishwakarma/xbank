<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CC MONTHLY INTEREST CALCULATION
// TODO :- CHANGE LastcurrentInterestUpdatedAt FIELD TO 31 MARCH 2011 BEFORE RUNNING CLOSING IN MAY.

$CI = & get_instance();
$q="UPDATE jos_xaccounts a
        join jos_xtransactions t on t.accounts_id=a.id
        join jos_xschemes s on s.id=a.schemes_id
        SET a.LastCurrentInterestUpdatedAt = DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH),
            a.CurrentInterest = 0
        where t.branch_id=".$b->id." and
        t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and '".  getNow("Y-m-d")."' and
        s.SchemeType ='".ACCOUNT_TYPE_CC."'";
executeQuery($q);

$query="select t.*,a.id as accid, s.Interest as Interest from jos_xtransactions t join jos_xaccounts a on t.accounts_id = a.id
        join jos_xschemes s on a.schemes_id = s.id where t.branch_id = ".$b->id." and
            t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and '".  getNow("Y-m-d")."' and     s.SchemeType ='".ACCOUNT_TYPE_CC."'
                order by t.created_at";
 
 $transactions = $CI->db->query($query);
foreach($transactions->result() as $trans){
    $queryA="select sum(t.amountCr) as CRSum from jos_xtransactions t join jos_xaccounts a on t.accounts_id = a.id
            where t.branch_id=".$b->id." and t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt, INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and a.id = $trans->accid";
    $CRSum = $CI->db->query($queryA)->row()->CRSum;
    $queryB= "select sum(t.amountDr) as DRSum from jos_xtransactions t join jos_xaccounts a on t.accounts_id = a.id
                            where t.branch_id=".$b->id." and t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and a.id = $trans->accid";
    $DRSum = $CI->db->query($queryB)->row()->DRSum;


    
//    $q="UPDATE accounts
//       SET CurrentInterest = CurrentInterest + (IF(((CurrentBalanceDr - $DRSum) - (CurrentBalanceCr - $CRSum)) > 0 ,((CurrentBalanceDr - $DRSum) - (CurrentBalanceCr - $CRSum)),0) * $a->Interest * DATEDIFF('$a->created_at',LastCurrentInterestUpdatedAt)/36500 ) ,
//       LastCurrentInterestUpdatedAt='".$a->created_at."' WHERE id = $a->accounts_id";
//    executeQuery($q);

    $account = new Account($trans->accid);
    $created_at = date("Y-m-d", strtotime(date("Y-m-d", strtotime($trans->created_at))));
    $lastInterestUpdatedAt = date("Y-m-d", strtotime(date("Y-m-d", strtotime($account->LastCurrentInterestUpdatedAt))));
    $days = my_date_diff($created_at,$lastInterestUpdatedAt);
    $interest = round((((($account->CurrentBalanceDr - $DRSum) - ($account->CurrentBalanceCr - $CRSum)) > 0 ? (($account->CurrentBalanceDr - $DRSum) - ($account->CurrentBalanceCr - $CRSum)) : 0) * $trans->Interest * $days['days_total'] / 36500),2);
    $account->CurrentInterest += $interest;
    $account->LastCurrentInterestUpdatedAt = $trans->created_at;
    $account->save();

//    echo "AccountNumber : ".$account->AccountNumber."<br>";
//    echo "Interest on ".(($account->CurrentBalanceDr - $DRSum) - ($account->CurrentBalanceCr - $CRSum))." till $a->created_at : ".$interest."<br><br>";
    
}

// INTEREST CALCULATION FROM LastCurrentInterestUpdatedAt to Last Date of the month
$q="UPDATE jos_xaccounts a
       join jos_xschemes s on s.id = a.schemes_id
       SET a.CurrentInterest = a.CurrentInterest + (IF((a.CurrentBalanceDr - CurrentBalanceCr) > 0 ,(CurrentBalanceDr - CurrentBalanceCr), 0) * s.Interest * DATEDIFF('".getNow("Y-m-d")."',date(a.LastCurrentInterestUpdatedAt))/36500 ) ,
       LastCurrentInterestUpdatedAt='".getNow("Y-m-d")."' WHERE s.SchemeType ='".ACCOUNT_TYPE_CC."' and a.branch_id =".$b->id;
    executeQuery($q);



// CC MONTHLY INTEREST POSTING
$q = "update jos_xaccounts as `a` join jos_xschemes as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` =`a`.`CurrentInterest` +  (IF((`a`.`CurrentBalanceDr` - `a`.`CurrentBalanceCr`)>0,(`a`.`CurrentBalanceDr` - `a`.`CurrentBalanceCr`),0) * `s`.`Interest` * DATEDIFF('" . getNow("Y-m-d") . "', `a`.`LastCurrentInterestUpdatedAt`)/36500), `a`.`LastCurrentInterestUpdatedAt`='" . getNow("Y-m-d") . "' where `s`.`SchemeType`= '" . ACCOUNT_TYPE_CC . "' and `a`.`ActiveStatus` <> 0 and a.created_at < '" . getNow("Y-m-d") . "' and `a`.`branch_id` = " . $b->id;
executeQuery($q);

// do interest posting
$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_CC)->get();
if($schemes->result_count()>0){
foreach ($schemes as $sc) {
//    $query = Doctrine_Query::create()
//                    ->select("a.AccountNumber, a.CurrentInterest")
//                    ->from("Accounts a")
//                    ->where("a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);
//    $accounts = $query->execute();

    $accounts = new Account();
    $accounts->where("schemes_id",$sc->id);
    $accounts->where("ActiveStatus",1);
    $accounts->where("CurrentInterest >",0);
    $accounts->where("created_at < ", "'" . getNow("Y-m-d") . "'");
    $accounts->where("branch_id",$b->id)->get();

    if ($accounts->result_count() == 0)
        continue;

//    $t = Doctrine_Query::create()
//                    ->select("a.CurrentInterest")
//                    ->from("Accounts a")
//                    ->where("a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
//    $tot = $t->execute();
      $totals = 0;
    $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = $sc->id and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;

  
//    foreach ($tot as $total)
//        $totals +=$total->CurrentInterest;


    $schemeName = $sc->Name;


    $debitAccount = array();

    $creditAccount = array(
        $b->Code . SP . INTEREST_RECEIVED_ON . $schemeName => $totals
    );

    foreach ($accounts as $acc) {
        $debitAccount += array($acc->AccountNumber => $acc->CurrentInterest);
    }

    Transaction::doTransaction($debitAccount, $creditAccount, "Interest posting in CC Account", TRA_INTEREST_POSTING_IN_CC_ACCOUNT, Transactions::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
}
}



$query = "update `jos_xaccounts` as `a` join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id` set  `a`.`CurrentInterest` = 0  where `s`.`SchemeType`= '" . ACCOUNT_TYPE_CC . "' and `a`.`ActiveStatus` <> 0 and `a`.`created_at` < '" . getNow("Y-m-d") . "' and `a`.`branch_id` = " . $b->id;
executeQuery($query);

?>