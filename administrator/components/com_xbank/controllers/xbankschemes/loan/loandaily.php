<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//PENALTY TRANSFERING
//$loanPenalty = 10;
//$q = "update `accounts` as `a` join `premiums` as `p` on `p`.`accounts_id`=`a`.`id` join `schemes` as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` = `a`.`CurrentInterest` + $loanPenalty  where `s`.`SchemeType`= '" . ACCOUNT_TYPE_LOAN . "' and DATEDIFF('" . getNow("Y-m-d") . "' , `p`.`DueDate`) <= 30 and DATEDIFF('" . getNow("Y-m-d") . "',`p`.`DueDate`) > 0 and `p`.`PaidOn` is null  and `a`.`ActiveStatus` = 1 and `a`.`branch_id` = " . $b->id;
//executeQuery($q);
// DEPOSIT INTEREST ON LOAN ON PREMIUM DUE DATE
//$q = Doctrine_Query::create()
//                ->select("a.*")
//                ->from("Accounts a")
//                ->innerJoin("a.Premiums p")
//                ->innerJoin("a.Schemes s")
//                ->where("s.SchemeType = '" . ACCOUNT_TYPE_LOAN . "' and date(p.DueDate) = '" . $i . "' and a.ActiveStatus=1 and a.branch_id = " . Branch::getCurrentBranch()->id);
//$loanaccounts = $q->execute();
$CI = & get_instance();
$loanaccounts = $CI->db->query("select a.* from jos_xaccounts a join jos_xpremiums p on a.id = p.accounts_id join jos_xschemes s on a.schemes_id = s.id where s.SchemeType = '" . ACCOUNT_TYPE_LOAN . "' and date(p.DueDate) like '" . $i . "%' and a.ActiveStatus=1 and a.branch_id = " . $b->id)->result();
$transactiondate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " -1 day"));
foreach ($loanaccounts as $acc) {
    $a = new Account($acc->id);
    $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $a->id);
    $rate = $a->scheme->Interest;
    $premiums = $a->scheme->NumberOfPremiums;

    if ($a->scheme->ReducingOrFlatRate == REDUCING_RATE) {
        // INTEREST FOR REDUCING RATE OF INTEREST
        $emi = ($a->RdAmount * ($rate / 1200) / (1 - (pow(1 / (1 + ($rate / 1200)), $premiums))));
        $interest = ((($emi * $premiums) - $a->RdAmount) / $premiums);
    }
    if ($a->scheme->ReducingOrFlatRate == FLAT_RATE or $a->scheme->ReducingOrFlatRate == 0) {
//    INTEREST FOR FLAT RATE OF INTEREST
        $interest = (($a->RdAmount * $rate * ($premiums + 1)) / 1200) / $premiums;
    }

    $interest = round($interest);
    $debitAccount = array(
        $a->AccountNumber => $interest
    );
    $creditAccount = array(
        Branch::getCurrentBranch()->Code . SP . INTEREST_RECEIVED_ON . $a->scheme->Name => $interest
    );

    Transaction::doTransaction($debitAccount, $creditAccount, "Interest posting in Loan Account $a->AccountNumber", TRA_INTEREST_POSTING_IN_LOAN, $voucherNo, $transactiondate);
}
?>
