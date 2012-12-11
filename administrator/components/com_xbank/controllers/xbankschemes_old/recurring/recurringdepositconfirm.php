<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$msg.="<h3>Current Account position</h3>";
$debitAccount = array(
    $ac->AccountNumber => $ac->CurrentBalanceDr
);
$creditAccount = array(
    $ac->AccountNumber => $ac->CurrentBalanceCr
);
$msg .= formatDrCr($debitAccount, $creditAccount);

 $PaidPremiums=new Premium();
 $PaidPremiums->where('accounts_id',$ac->id)->where('Paid <>',0)->where('Skipped',0)->get();
//$PaidPremiums = Doctrine::getTable("Premiums")->createQuery()
//                ->where("accounts_id = ? AND Paid <> 0 AND Skipped = 0", array($ac->id))->execute();
//                            ->findByAccounts_idAndPaidAndSkipped($ac->id,"1","0")->count();
$PaidPremiums = $PaidPremiums->result_count();
$msg .="<br/>Balance Remaining in Account " . (($ac->CurrentBalanceCr) - ($PaidPremiums * ($ac->RdAmount)));
$msg .="<h2> $PaidPremiums premiums paid </h2>";

$msg.="<h3>New Transactions To Happen</h3>";
$debitAccount = array(
    $debitToAccount => inp('Amount')
);
$creditAccount = array(
    $ac->AccountNumber => inp('Amount')
);
$msg .= formatDrCr($debitAccount, $creditAccount);
$duePremiums = $this->db->query("SELECT COUNT(*) AS Dues FROM jos_xpremiums WHERE accounts_id=$ac->id AND Paid = 0 AND Skipped = 0")->row()->Dues;

if($ac->scheme->CollectorCommissionRate){
            $debitAccount = array(
            Branch::getCurrentBranch()->Code.SP.COLLECTION_PAID_ON.SP.$ac->scheme->Name => inp('Amount') * $ac->scheme->CollectorCommissionRate / 100
        );
        $creditAccount = array(
            Branch::getCurrentBranch()->Code.SP.COLLECTION_PAYABLE_ON.SP.$ac->scheme->Name => inp('Amount') * $ac->scheme->CollectorCommissionRate / 100
        );
        $msg .="<br>". formatDrCr($debitAccount, $creditAccount);
}

if (($ac->CurrentBalanceCr + inp("Amount")) > ($ac->scheme->NumberOfPremiums * $ac->RdAmount)) {
    echo "<h2>Total Deposit is Exeeding then required Deposit</h2>";
    echo "<h2>Total Amount Required : " . ($ac->scheme->NumberOfPremiums * $ac->RdAmount) . "</h2>";
    echo "<h2>Current Amount after Depositing total amount : " . ($ac->CurrentBalanceCr + inp("Amount")) . "</h2>";
    echo "<h2>Premiums Due : " . $duePremiums . "</h2>";
//    echo "false";
    return;
}

$PremiumAmountAdjusted = $PaidPremiums * $ac->RdAmount;
$AmountForPremiums = ($ac->CurrentBalanceCr + inp("Amount")) - $PremiumAmountAdjusted;
$premiumsSubmited = (int) ($AmountForPremiums / $ac->RdAmount);


if ($premiumsSubmited > $duePremiums) {
    $msg .="<h2>Only $duePremiums premiums Due.. Cannot Depoit more amount <h2><br> false";
    echo $msg;
    return;
}

$msg .= "<h2>$premiumsSubmited Premiums more adjusting ... </h2>";
?>
