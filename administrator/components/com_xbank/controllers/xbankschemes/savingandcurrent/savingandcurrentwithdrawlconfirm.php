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
if(file_exists( SIGNATURE_FILE_PATH . "sig_" . $ac->member->id . ".JPG"))
 $ext = ".JPG";
else
 $ext = ".JPG";
echo "<h2>".$ac->Member->Name."'s Current Balance is : " . $balance . "<br>Are you sure you want to withdraw Rs. ".inp("Amount")." from $ac->AccountNumber?</h2><br/>Specimen Signature <img src='http://www.bhawanicredit.com/soft" . SIGNATURE_FILE_PATH . "sig_" . $ac->member->id . "$ext' />";
if (($balance - $ac->Schemes->MinLimit) < inp("Amount")) {
    $err = true;
    $msg .="<br><h3>Insufficient Balance [$balance]</h3><br>";
}
?>
