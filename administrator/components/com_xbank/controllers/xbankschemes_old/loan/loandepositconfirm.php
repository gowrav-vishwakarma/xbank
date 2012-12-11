<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$xl = new xConfig("Loan");
if (!$xl->getKey("deposit_penalty_in_closing")) {
    $this->load->library("form");
    $this->form->open("legForm", "#")
            ->text("Deposit Penalty/Recovery charges in loan account " . $ac->AccountNumber, "name='penalty' onchange='jQuery(\"#DepositForm_penalty\").val(jQuery(this).val())'");
//                        ->submit("Go");
    $this->jq->getHeader(true);
    echo $this->form->get();
}

$Paid = new Premium();
$Paid->where('accounts_id', $ac->id)->where('Paid <>', 0)->get();
//$Paid = Doctrine::getTable("Premiums")->createQuery()
//                ->where("accounts_id = ? AND Paid <> 0 ", array($ac->id))->execute();
$PaidEMI = $Paid->result_count();       //RETRIEVING THE NUMBER OF PAID EMIs
//CALCULATING PENALTIES
//                    $PenaltiesDeposited = $this->db->query("select Sum(IF((DATEDIFF(p.PaidOn, p.DueDate) * 20) > 600, 600, DATEDIFF(p.PaidOn, p.DueDate) * 20 ) ) as dues from premiums p where p.accounts_id =".$ac->id."  and p.DueDate < '".getNow()."' and p.PaidOn > p.DueDate")->row()->dues;
//RETRIEVING EMI AMOUNT
//$premium = Doctrine::getTable("Premiums")->findOneByAccounts_id($ac->id);
$premium = new Premium();
$premium->where('accounts_id', $ac->id)->get();
$emi = $premium->Amount;

$rate = $ac->scheme->Interest;
$premiums = $ac->scheme->NumberOfPremiums;


//if ($premiums == $PaidEMI && $ac->CurrentBalanceDr - $ac->CurrentBalanceCr <= 0) {
//    echo "<h2>ACCOUNT CLOSED</h2><br>false";
//    return;
//}
//$interest = (($ac->RdAmount * $rate * ($premiums + 1)) / 1200) / $premiums;
$interest = ((($emi * $premiums) - $ac->RdAmount) / $premiums);
$interest = round($interest);

$PremiumAmountAdjusted = $PaidEMI * $emi;
$AmountForPremiums = ($ac->CurrentBalanceCr + inp("Amount")) - $PremiumAmountAdjusted;

$premiumsSubmited = (int) ($AmountForPremiums / $emi);

$interest = ($interest * $premiumsSubmited) == 0 ? $interest : ($interest * $premiumsSubmited);


//CALCULATING EXTRA AMOUNT TO DEPOSIT
//                    $topaid=(($ac->CurrentBalanceCr - ($PaidEMI * $emi) - $PenaltiesDeposited)  + inp("Amount")) % $emi;
//                    //CALCULATING TOTAL AMOUNT DUE TILL DATE
//                    $premiumsduetilldate=$this->db->query("SELECT SUM(Amount) AS Dues FROM premiums WHERE accounts_id=$ac->id AND Paid = 0 ")->row()->Dues;
//                    $msg .="Total amount due = ".$premiumsduetilldate;
// SHOWING CURRENT ACCOUNT POSITION
$msg .="<h2>EMI = " . $emi . " </h2>";
$msg .="<h3>Monthly Interest = $interest</h3>";
$msg .="<h3>Number of EMI Paid = $PaidEMI</h3>";
$msg.="<h3>Current Account position</h3>";
$debitAccount = array(
    $ac->AccountNumber => $ac->CurrentBalanceDr
);
$creditAccount = array(
    $ac->AccountNumber => $ac->CurrentBalanceCr
);
$msg .= formatDrCr($debitAccount, $creditAccount);



$msg.="<h3>New Transactions To Happen</h3>";
//                    $debitAccount=array(
//                        $ac->AccountNumber => $ac->CurrentBalanceDr + $interest
//                    );
//                    $creditAccount=array($ac->AccountNumber => $ac->CurrentBalanceCr + inp("Amount"));

$creditAccount = array($ac->AccountNumber => inp('Amount'));
$debitAccount = array(
    $debitToAccount => inp('Amount')
);

//$creditAccount +=array(
//    INTEREST_RECEIVED_ON => $interest
//);
//$debitAccount +=array(
//    $ac->AccountNumber => $interest
//);

$msg .= formatDrCr($debitAccount, $creditAccount);
$amountDue = $ac->CurrentBalanceDr /* + $interest */ - ($ac->CurrentBalanceCr + inp("Amount"));
//    $msg .="Amount due after depositing this amount : ".$amountDue;
$msg .= "<h2>$premiumsSubmited EMIs more adjusting ... </h2>";
?>
