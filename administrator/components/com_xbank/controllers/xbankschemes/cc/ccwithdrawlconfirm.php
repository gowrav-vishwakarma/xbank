<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$balance = $ac->CurrentBalanceCr - $ac->CurrentBalanceDr;
$err = false;
$msg = "";
$feePercent = $ac->scheme->ProcessingFees;
$processingFees = $feePercent * $ac->{CC_AMOUNT} / 100;

$ccLimit = $ac->{CC_AMOUNT} ;//- $processingFees;
$ccbalance = $ccLimit - ($ac->CurrentBalanceDr - $ac->CurrentBalanceCr);
if ($ccbalance < inp("Amount")) {
    echo "<h2>You cannot withdraw more than your CC Limit</h2><br>falsefalse";
    return;
}
echo "<h2>Withdrawing " . inp("Amount") . " from CC Account " . $ac->AccountNumber . "...</h2><br/>Specimen Signature <img src='./signatures/sig_$ac->member_id.JPG' height='100' width='160'>";
?>
