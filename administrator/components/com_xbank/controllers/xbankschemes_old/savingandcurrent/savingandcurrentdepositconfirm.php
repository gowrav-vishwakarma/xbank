<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$balance = $ac->CurrentBalanceCr - $ac->CurrentBalanceDr;
//echo "<h2>Your Current Balance is : " . $balance . "</h2>";
echo "<h2>Your Current Balance is : " . $balance . "<br>Are you sure you want to deposit Rs. ".inp("Amount")." in $ac->AccountNumber?</h2><br/>";
$debitAccount = array(
    $debitToAccount => inp('Amount')
);
$creditAccount = array(
    $ac->AccountNumber => inp('Amount')
);
$msg .= formatDrCr($debitAccount, $creditAccount);
?>
