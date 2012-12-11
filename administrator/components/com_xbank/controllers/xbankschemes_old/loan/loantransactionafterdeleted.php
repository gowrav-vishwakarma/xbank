<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
    $acc->CurrentBalanceCr -= $t->amountCr;
    $acc->CurrentBalanceDr -= $t->amountDr;
    $acc->save();

    $q="update jos_xpremiums set Paid=0,PaidOn=null where PaidOn='".$t->updated_at."' and accounts_id = $acc->id";
    executeQuery($q);
?>
