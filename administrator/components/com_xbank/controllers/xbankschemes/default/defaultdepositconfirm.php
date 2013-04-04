<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$balance = $ac->CurrentBalanceCr - $ac->CurrentBalanceDr;
//echo "<h2>Your Current Balance is : " . $balance . "</h2>";
echo "<h2>Your Current Balance is : " . $balance . "<br>Are you sure you want to deposit Rs. ".inp("Amount")." in $ac->AccountNumber?</h2><br/>";
if ($ac->branch->id != Branch::getCurrentBranch()->id) {
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->branch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => inp("Amount"),
    );

    $debitAccount += array(
        Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $ac->branch->Code => inp("Amount"),
    );
    $creditAccount += array(
        $ac->AccountNumber => inp('Amount'),
    );
} else {
    $debitAccount = array(
        $debitToAccount => inp('Amount')
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount')
    );
}
$msg .= formatDrCr($debitAccount, $creditAccount);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$msg .="DIPOSIT not supported for this account<br>falsefalse";
// return;
?>
