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

?>
