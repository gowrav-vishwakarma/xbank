<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

 $Paid=new Premium();
 $Paid->where('accounts_id',$ac->id)->where('Paid <>',0)->get();
//$Paid = Doctrine::getTable("Premiums")->createQuery()
 //->where("accounts_id = ? AND Paid <> 0 ", array($ac->id))->execute();
$PaidEMI = $Paid->result_count();       //RETRIEVING THE NUMBER OF PAID EMIs

$premium=new Premium();
$premium->where('accounts_id',$ac->id)->get();
//$premium = Doctrine::getTable("Premiums")->findOneByAccounts_id($ac->id);
$emi = $premium->Amount;

$rate = $ac->scheme->Interest;
$premiums = $ac->scheme->NumberOfPremiums;

//$interest = (($ac->RdAmount * $rate * ($premiums + 1)) / 1200) / $premiums;

$interest = ((($emi * $premiums) - $ac->RdAmount) / $premiums);


$interest = round($interest);
$PremiumAmountAdjusted = $PaidEMI * $emi;
$AmountForPremiums = ($ac->CurrentBalanceCr + inp("Amount")) - $PremiumAmountAdjusted;

$premiumsSubmited = (int) ($AmountForPremiums / $emi);
$interest = ($interest * $premiumsSubmited) == 0 ? $interest : ($interest * $premiumsSubmited);
$transactiondate = getNow();

if ($ac->branch->id != Branch::getCurrentBranch()->id) {
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->branch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => inp("Amount"),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Amount submited in Loan Account $ac->AccountNumber"), TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);

    $debitAccount = array(
        Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $ac->branch->Code => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount'),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Amount submited in Loan Account $ac->AccountNumber"), TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber($ac->branch->id), $transactiondate, $ac->branch->id);
} else {



$debitAccount = array(
    $debitToAccount => inp("Amount"),
);
$creditAccount = array($ac->AccountNumber => inp('Amount'));
Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Amount submitted in Loan Account $ac->AccountNumber"), TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo,$transactiondate);
}
//$debitAccount = array(
//    $ac->AccountNumber => $interest
//);
//$creditAccount = array(
//    Branch::getCurrentBranch()->Code . SP . INTEREST_RECEIVED_ON . $ac->Schemes->Name => $interest
//);
//
//Transactions::doTransaction($debitAccount, $creditAccount, "Amount submitted in Loan Account", TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);

/**
 * DO ADJUSTMENT IN PREMIUMS
 * adjusting the remaining EMIs for the Loan account on the money deposited
 * and the date of EMI deposited should be the current date
 */
if ($premiumsSubmited > 0) {
    $q="select p.* from jos_xpremiums where accounts_id=$ac->id and Paid=0 order by id limit $premiumsSubmited";
//    $q = Doctrine_Query::create()
//                    ->select("p.*")
//                    ->from("Premiums p")
//                    ->where("accounts_id=? and Paid = 0 ", array($ac->id))
//                    ->orderBy("id")
//                    ->limit($premiumsSubmited);

    $result = new Premium();
    $result->where("accounts_id",$ac->id)->where("Paid",0)->order_by("id")->limit($premiumsSubmited)->get();
//    $result = $this->db->query($q)->result();
    foreach ($result as $r) {
        $r->PaidOn = $transactiondate;
        $r->Paid = 1;
        $r->save();
    }
}
 $EMIPaid=new Premium();
 $EMIPaid->where('accounts_id',$ac->id)->where('Paid <>',0)->get();
//$EMIPaid = Doctrine::getTable("Premiums")->createQuery()
//                ->where("accounts_id = ? AND Paid <> 0 ", array($ac->id))->execute();
$EMIPaid = $EMIPaid->result_count();
if ($ac->CurrentBalanceDr - $ac->CurrentBalanceCr <= 0) {
    $ac->ActiveStatus = 0;
    $ac->affectsBalanceSheet = 1;
    $ac->save();
}



$xl = new xConfig("Loan");
if (!$xl->getKey("deposit_penalty_in_closing") && inp("penalty")) {
    $creditAccounts = array($ac->branch->Code . SP . PENALTY_DUE_TO_LATE_PAYMENT_ON . $ac->scheme->Name => inp("penalty"));
    $debitAccounts = array($ac->AccountNumber => inp("penalty"));
    Transaction::doTransaction($debitAccounts, $creditAccounts, "Penalty deposited on Loan Account for ".  getNow("Y-m-d"), TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber(), getNow());
}

// send sms to customer
//             $mobile=substr($ac->Member->PhoneNos, 0, 10);
//            if(is_numeric($mobile) && strlen($mobile)==10){
//                $sms=new sms();
$msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
$this->sendSMS($ac, $msg);
?>
