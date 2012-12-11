<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//$details = Accounts::updateInterestForCC($ac);
$transactiondate = getNow();
if ($ac->branch->id != Branch::getCurrentBranch()->id) {
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->branch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => inp("Amount"),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "CC Account Amount Deposit in  $ac->AccountNumber"), TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);

    $debitAccount = array(
        Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $ac->branch->Code => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount'),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "CC Account Amount Deposit in  $ac->AccountNumber"), TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber($ac->branch->id), $transactiondate, $ac->branch->id);
} else {
    $debitAccount = array(
        $debitToAccount => inp('Amount')
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount')
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "CC Account Amount Deposit in $ac->AccountNumber"), TRA_CC_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);
}
//$msg = "CC Account Deposit- Interest given " . $details['Interest'] . " on " . $details['AmountForInterest'] . " For " . $details['DateDifferance']['days_total'] . " days.";
//Log::write($msg, $ac->id);

// send sms to customer
//             $mobile=substr($ac->Member->PhoneNos, 0, 10);
//            if(is_numeric($mobile) && strlen($mobile)==10){
//                $sms=new sms();
$msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
$this->sendSMS($ac, $msg);
?>
