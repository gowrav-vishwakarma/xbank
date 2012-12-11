<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$debitAccount = array(
    $ac->AccountNumber => inp("Amount"),
);
$creditAccount = array(
    $creditToAccount => inp("Amount"),
);
Transaction::doTransaction($debitAccount, $creditAccount, "FD Amount Withdrawl from $ac->AccountNumber", TRA_FD_ACCOUNT_AMOUNT_WITHDRAWL, $voucherNo);

$query = "update jos_xaccounts set ActiveStatus = 0, affectsBalanceSheet = 1 where id = $ac->id";
//executeQuery($query);
$this->db->query($query);

$msg = "Dear " . $ac->Member->Name . ", you have withdrawn an amount of Rs. " . inp("Amount") . " from your account $ac->AccountNumber on " . getNow("Y-m-d");
$this->sendSMS($ac, $msg);
?>
