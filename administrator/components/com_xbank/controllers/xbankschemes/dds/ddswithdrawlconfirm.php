<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// WITHDRAWL POSSIBLE ONLY WHEN ACCOUNT IS MATURED OR HAS BEEN MANUALLY DEACTIVATED.
if ($ac->MaturedStatus == 1 || $ac->ActiveStatus == 0) {
    if(inp("Amount") != ($ac->CurrentBalanceCr - $ac->CurrentBalanceDr)){
        echo "<h2>You have Rs. ".($ac->CurrentBalanceCr - $ac->CurrentBalanceDr)." left in $ac->AccountNumber to withdraw.</h2>false";
        return;
    }
    else{
        $creditAccount = array(
            $creditToAccount => inp('Amount')
        );
        $debitAccount = array(
            $ac->AccountNumber => inp('Amount')
        );
        $msg .= "<h2>Withdrawing ".inp("Amount")." from $ac->AccountNumber</h2>";
        $msg .= formatDrCr($debitAccount, $creditAccount);
    }
} else {
    echo "<h2>WITHDRAWL not supported from $ac->AccountNumber</h2><br>false";
    return;
}
?>
