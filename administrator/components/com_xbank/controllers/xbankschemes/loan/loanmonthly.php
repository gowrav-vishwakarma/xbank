<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$xl = new xConfig("Loan");
if ($xl->getKey("deposit_penalty_in_closing")) {


$q = "update `jos_xaccounts` as `a` join `jos_xpremiums` as `p` on `p`.`accounts_id`=`a`.`id` join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` = 0 where `s`.`SchemeType`= '" . ACCOUNT_TYPE_LOAN . "' and `a`.`ActiveStatus`=1 and `a`.`created_at` < '" . getNow("Y-m-d") . "' and `a`.`branch_id`=" . $b->id;
executeQuery($q);

//PENALTY TRANSFERING
$loanPenalty = 10;

//*********************************************************************

$loanPenalty = 10;
        $thismonth = date("m", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH")); //getNow("m");
        $lastmonth = date("m", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -2 MONTH"));
        $closingdate = getNow("Y-m-d");
        $lastmonthlastdate = date("Y-m-t", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -2 MONTH"));
        $firstdateofthismonth = date("Y-m-01", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH")); //getNow("Y-m-01");
        $penaltyQ = "update jos_xaccounts as a join (
                    select accounts_id, IF(SUM(Penalty) > 300 , 300 , SUM(Penalty)) as Penalty from (


                    /* PREMIUM DUE IN THIS MONTH - NOT PAID */
                    select
                    'A' as nm,p.id,(DATEDIFF('$closingdate',p.DueDate)) * 10 as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $thismonth AND
                    p.PaidOn is NULL and

                    DATEDIFF('$closingdate',p.DueDate) <= 31 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    /* AND p.accounts_id = this->id */

                    UNION



                    /* PREMIUM DUE IN THIS MONTH - LATE PAID IN THIS MONTH */
                    select
                    'B' as nm,p.id,IF((DATEDIFF(p.PaidOn,p.DueDate)) * 10 > 300,300,(DATEDIFF(p.PaidOn,p.DueDate)) * 10) as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $thismonth AND
                    p.PaidOn > p.DueDate AND
                    DATEDIFF('$closingdate',p.DueDate) <= 31 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    /* AND p.accounts_id = this->id */

                    UNION

                    /* PREMIUM DUE IN LAST MONTH - STILL NOT PAID */
                    select
                    'C' as nm,p.id,if(DATEDIFF('$closingdate',p.Duedate)>=30,300,(DATEDIFF('$closingdate',p.DueDate)+1)*10) as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $lastmonth AND
                    p.PaidOn is NULL AND
                    DATEDIFF('$closingdate',p.DueDate) <= 62 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    /* AND p.accounts_id = this->id */

                    UNION

                    /* PREMIUM DUE IN LAST MONTH - PAID IN THIS(NEXT) MONTH */
                    select
                    'D' as nm,p.id, IF(DAY(p.PaidOn) >= DAY(p.DueDate), (300 - (DATEDIFF('$lastmonthlastdate',p.DueDate) * 10)), (DATEDIFF(p.PaidOn,'$firstdateofthismonth')) * 10) as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $lastmonth AND
                    MONTH(p.PaidOn) = $thismonth AND
                    DATEDIFF('$closingdate',p.DueDate) <= 62 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                        /* AND p.accounts_id = this->id */
                    )
                    as t
                    GROUP  BY accounts_id)


                     as temp on a.id = temp.accounts_id
                     join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id`
                     set a.CurrentInterest = temp.Penalty
                     where `s`.`SchemeType`= 'Loan' and
                    `a`.`ActiveStatus` = 1 and
                    `a`.`branch_id` =". $b->id ."
";

        executeQuery($penaltyQ);



//*********************************************************************

$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_LOAN)->get();

$penaltyTotal = 0;
$creditAccounts = array();
$debitAccounts = array();

//calculating penalty amount for each scheme
foreach ($schemes as $sc) {
//    $q = Doctrine_Query::create()
//                    ->select(" SUM(a.CurrentInterest) as penalty")
//                    ->from("Accounts  a ")
//                    ->where(" a.branch_id = " . $b->id . " and a.schemes_id= '" . $sc->id . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' ");
    $CI = & get_instance();
    $penaltyTotal = $CI->db->query("select sum(a.CurrentInterest) as penalty from jos_xaccounts a where a.branch_id = " . $b->id . " and a.schemes_id= '" . $sc->id . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and CurrentInterest > 0")->row()->penalty;
    
    // echo "================================ <br/>";
    // echo "$sc->Name penaltyTotal = " . $penaltyTotal . "<br/>";

    // $accounts_store=array();
    // if($sc->id == 170){
    //     $penaltyTotalAccounts = $CI->db->query("select a.* from jos_xaccounts a where a.branch_id = " . $b->id . " and a.schemes_id= '" . $sc->id . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and CurrentInterest > 0")->result();
    //     foreach ($penaltyTotalAccounts as $acc) {
    //         echo $acc->AccountNumber.  ' CurINt '. $acc->CurrentInterest. '<br/>';
    //         $accounts_store[] = $acc->AccountNumber;
    //     }
    // }
//    $penaltyTotal = $q->execute()->getFirst()->penalty;
    $creditAccounts = array($b->Code . SP . PENALTY_DUE_TO_LATE_PAYMENT_ON . $sc->Name => $penaltyTotal);
//    $accounts = Doctrine::getTable("Accounts")->findBySchemes_idAndBranch_id($sc->id, $b->id);
//    $q = Doctrine_Query::create()
//                    ->select("AccountNumber, CurrentInterest")
//                    ->from("Accounts")
//                    ->where("schemes_id = $sc->id and branch_id = $b->id and ActiveStatus=1 and created_at < '" . getNow("Y-m-d") . "' and CurrentInterest > 0");
//    $accounts = $q->execute();
    $accounts = new Account();
    $accounts->where("schemes_id",$sc->id)->where("branch_id",$b->id)->where("ActiveStatus",1)->where("created_at < ",getNow("Y-m-d"))->where("CurrentInterest > ",0)->get();
    if ($accounts->result_count() == 0)
        continue;
    $debitAccounts = array();
    $sum=0;
    foreach ($accounts as $ac) {
        $sum += $ac->{FIELD_TEMP_PENALTY};
        $debitAccounts += array($ac->AccountNumber => $ac->{FIELD_TEMP_PENALTY});
        // if($sc->id == 170){ // Debug
        //     if(!in_array($ac->AccountNumber, $accounts_store)){
        //         echo $ac->AccountNumber. ' Doubtfull <br/>';
        //     }
        // }
    }
    
    // echo "given in accounts $sum <br/>";
    $firstDayOfLastMonth = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH"));
    Transaction::doTransaction($debitAccounts, $creditAccounts, "Penalty deposited on Loan Account for ".date("F",strtotime($firstDayOfLastMonth)), TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
    $penaltyTotal = 0;
}


$q = "update `jos_xaccounts` as `a` join `jos_xpremiums` as `p` on `p`.`accounts_id`=`a`.`id` join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` = 0 where `s`.`SchemeType`= '" . ACCOUNT_TYPE_LOAN . "' and `a`.`ActiveStatus`=1 and `a`.`created_at` < '" . getNow("Y-m-d") . "' and `a`.`branch_id`=" . $b->id;
executeQuery($q);
}
?>