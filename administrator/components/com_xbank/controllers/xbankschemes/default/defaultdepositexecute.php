<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//Accounts::updateInterest($ac);
$transactiondate = getNow();
if ($ac->branch->id != Branch::getCurrentBranch()->id) {
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->branch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => inp("Amount"),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Amount submited in Default Account $ac->AccountNumber"), TRA_DEFAULT_ACCOUNT_DEPOSIT_ENTRY, $voucherNo);

    $debitAccount = array(
        Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $ac->branch->Code => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount'),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Amount submited in Default Account $ac->AccountNumber"), TRA_DEFAULT_ACCOUNT_DEPOSIT_ENTRY, Transaction::getNewVoucherNumber($ac->branch->id), $transactiondate, $ac->branch->id);
} else {
    $debitAccounts = array(
        $debitToAccount => inp('Amount')
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount')
    );
    Transaction::doTransaction($debitAccounts, $creditAccount, (inp("Narration") ? inp("Narration") : "Amount submited in Default Account $ac->AccountNumber "), TRA_DEFAULT_ACCOUNT_DEPOSIT_ENTRY, $voucherNo);
}
//$msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
//$this->sendSMS($ac, $msg);
