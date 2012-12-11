<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//Accounts::updateInterest($ac);
$debitAccounts = array(
    $debitToAccount => inp('Amount')
);
$creditAccount = array(
    $ac->AccountNumber => inp('Amount')
);
Transaction::doTransaction($debitAccounts, $creditAccount, "Amount submited in Saving Account $ac->AccountNumber ", TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo);

//$msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
//$this->sendSMS($ac, $msg);
?>
