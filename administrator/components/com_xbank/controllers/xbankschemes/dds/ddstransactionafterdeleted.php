<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//$transactions = Doctrine::getTable("Transactions")->findByVoucher_noAndBranch_id($voucherno, $branchid);
//foreach ($transactions as $t) {
//    $acc = Doctrine::getTable("Accounts")->find($t->accounts_id);

    $acc->CurrentBalanceCr -= $t->amountCr;
    $acc->CurrentBalanceDr -= $t->amountDr;
    $acc->save();
//}
?>