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
 $PaidPremiums->where('accounts_id',$ac->id)->where('PaidOn is not null')->get();
//$PaidPremiums = Doctrine::getTable("Premiums")->createQuery()
//                ->where("accounts_id = ? AND Paid <> 0 AND Skipped = 0", array($ac->id))->execute();
//                            ->findByAccounts_idAndPaidAndSkipped($ac->id,"1","0")->count();
$PaidPremiums = $PaidPremiums->result_count();

//--------UPDATING ALL PREMIUMS-----------------------------------

if($PaidPremiums % $ac->RdAmount == 0 ){
    
}

$q = $this->db->query("
SELECT DRTransaction.voucher_no,sum(DRTransaction.amountCr) as interest
FROM jos_xtransactions AS CRTransaction
LEFT JOIN (select t.* from jos_xtransactions t join jos_xaccounts a where a.id = $ac->id) AS DRTransaction ON DRTransaction.voucher_no = CRTransaction.voucher_no
JOIN jos_xaccounts ON CRTransaction.accounts_id = jos_xaccounts.id
JOIN jos_xschemes ON jos_xaccounts.schemes_id = jos_xschemes.id
WHERE
DRTransaction.accounts_id = $ac->id AND
jos_xaccounts.AccountNumber = '" . $ac->branch->Code . SP . INTEREST_PAID_ON . $ac->scheme->Name . "'")->row();
//    $transactions = new Transaction();
//    $transactions->where('accounts_id', $ac->id);
//    $transactions->where("voucher_no <>",'$q->voucher_no');
//    $transactions->order_by("created_at")->get();
//    foreach ($transactions as $tr) {
//
//    }
//----------------------------------------------------

$msg .="<br/>Balance Remaining in Account " . (($ac->CurrentBalanceCr) - ($PaidPremiums * ($ac->RdAmount)) - $q->interest);
$msg .="<h2> $PaidPremiums premiums paid </h2>";

$msg.="<h3>New Transactions To Happen</h3>";

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

//$debitAccount = array(
//    $debitToAccount => inp('Amount')
//);
//$creditAccount = array(
//    $ac->AccountNumber => inp('Amount')
//);
$msg .= formatDrCr($debitAccount, $creditAccount);
$duePremiums = $this->db->query("SELECT COUNT(*) AS Dues FROM jos_xpremiums WHERE accounts_id=$ac->id AND PaidOn is null")->row()->Dues;

if($ac->scheme->CollectorCommissionRate){
            $debitAccount = array(
            Branch::getCurrentBranch()->Code.SP.COLLECTION_PAID_ON.SP.$ac->scheme->Name => inp('Amount') * $ac->scheme->CollectorCommissionRate / 100
        );
        $creditAccount = array(
            Branch::getCurrentBranch()->Code.SP.COLLECTION_PAYABLE_ON.SP.$ac->scheme->Name => inp('Amount') * $ac->scheme->CollectorCommissionRate / 100
        );
        $msg .="<br>". formatDrCr($debitAccount, $creditAccount);
}

if (($ac->CurrentBalanceCr + inp("Amount") - $q->interest) > ($ac->scheme->NumberOfPremiums * $ac->RdAmount)) {
    echo "<h2>Total Deposit is Exeeding then required Deposit</h2>";
    echo "<h2>Total Amount Required : " . ($ac->scheme->NumberOfPremiums * $ac->RdAmount) . "</h2>";
    echo "<h2>Current Amount after Depositing total amount : " . ($ac->CurrentBalanceCr + inp("Amount") - $q->interest) . "</h2>";
    echo "<h2>Premiums Due : " . $duePremiums . "</h2>";
//    echo "false";
    return;
}

$PremiumAmountAdjusted = $PaidPremiums * $ac->RdAmount;
$AmountForPremiums = ($ac->CurrentBalanceCr + inp("Amount")) - $PremiumAmountAdjusted - $q->interest;
$premiumsSubmited = (int) ($AmountForPremiums / $ac->RdAmount);

// $msg .= $PremiumAmountAdjusted;

$this->session->set_userdata('premiums_submitted',$premiumsSubmited);

if ($premiumsSubmited > $duePremiums) {
    $msg .="<h2>Only $duePremiums premiums Due.. Cannot Deposit more amount <h2><br> falsefalse";
    echo $msg;
    return;
}

$msg .= "<h2>$premiumsSubmited Premiums more adjusting ... </h2>";
?>