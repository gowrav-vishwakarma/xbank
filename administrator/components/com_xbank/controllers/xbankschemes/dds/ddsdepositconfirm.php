<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (($ac->CurrentBalanceCr + inp("Amount")) > ($ac->RdAmount)) {
    echo "<h2>Total Deposit is Exeeding then required Deposit</h2>";
    echo "<h2>Total Amount Required : " . ($ac->RdAmount) . "</h2>";
    echo "<h2>Current Amount after Depositing total amount : " . ($ac->CurrentBalanceCr + inp("Amount")) . "</h2>";
    echo "false";
    return;
}

$msg.="<h3>Current Account position</h3>";
$debitAccount = array(
    $ac->AccountNumber => $ac->CurrentBalanceDr
);
$creditAccount = array(
    $ac->AccountNumber => $ac->CurrentBalanceCr
);
$msg .= formatDrCr($debitAccount, $creditAccount);

//                    $PaidPremiums=Doctrine::getTable("Premiums")->createQuery()
//                            ->where("accounts_id = ? AND Paid <> 0 AND Skipped = 0",array($ac->id))->execute();
////                            ->findByAccounts_idAndPaidAndSkipped($ac->id,"1","0")->count();
//                    $PaidPremiums=$PaidPremiums->count();
//                    $msg .="<br/>Balance Remaining in Account " . (($ac->CurrentBalanceCr)-($PaidPremiums*($ac->RdAmount)));
//                    $msg .="<h2> $PaidPremiums premiums paid </h2>";

$msg.="<h3>New Transactions To Happen</h3>";
$debitAccount = array(
    $debitToAccount => inp('Amount')
);
$creditAccount = array(
    $ac->AccountNumber => inp('Amount')
);



$msg .= formatDrCr($debitAccount, $creditAccount);
$AmountForPremiums = $ac->RdAmount - ($ac->CurrentBalanceCr + inp("Amount"));


$msg .= "<h2>Rs. $AmountForPremiums more left to deposit ... </h2>";
?>
