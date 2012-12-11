<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$ac = Account::getAccountForCurrentBranch(inp('AccountNumber'), false);
if (!$ac || inp("Amount") == "" || !is_numeric(inp("Amount")) || ($ac->LockingStatus == 1 || $ac->ActiveStatus == 0)) {
    $msg = "No Account found Or Amount not valid.. proceeding may generate error<br>false";
    return;
}
$balance = $ac->CurrentBalanceCr - $ac->CurrentBalanceDr;
$err = false;
$msg = "";
echo "<h2>".$ac->Member->Name."'s Current Balance is : " . $balance . "<br>Are you sure you want to withdraw Rs. ".inp("Amount")." from $ac->AccountNumber?</h2><br/>Specimen Signature <img src='./signatures/sig_$ac->member_id.JPG' height='100' width='160'>";
if (($balance - $ac->Schemes->MinLimit) < inp("Amount")) {
    $err = true;
    $msg .="<br><h3>Insufficient Balance [$balance]</h3><br>";
}
?>
