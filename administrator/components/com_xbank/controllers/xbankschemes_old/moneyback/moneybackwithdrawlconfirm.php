<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if ($ac->MaturedStatus == 1 || $ac->ActiveStatus == 0) {
    if (inp("Amount") != ($ac->CurrentBalanceCr - $ac->CurrentBalanceDr)) {
        echo "<h2>You have Rs. " . ($ac->CurrentBalanceCr - $ac->CurrentBalanceDr) . " left in $ac->AccountNumber to withdraw.</h2>false";
        return;
    }
    else
        $debitAccount = array(
            $ac->AccountNumber => inp("Amount"),
        );
        $creditAccount = array(
            $creditToAccount => inp("Amount"),
        );
        $msg .= formatDrCr($debitAccount, $creditAccount);
        $msg .= "<h2>Withdrawing Rs. " . inp("Amount") . " from $ac->AccountNumber</h2>";
} else {
    echo "WITHDRAWL not supported from this account<br>false";
    return;
}
?>
