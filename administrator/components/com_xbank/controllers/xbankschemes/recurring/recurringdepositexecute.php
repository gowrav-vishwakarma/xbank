<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$transactiondate = getNow();
if($ac->branch->id != Branch::getCurrentBranch()->id){
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->branch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code =>  inp("Amount"),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Recurring Amount Deposit in $ac->AccountNumber"), TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo, $transactiondate);

    $debitAccount = array(
        Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $ac->branch->Code => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount'),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Recurring Amount Deposit in $ac->AccountNumber"), TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber($ac->branch->id), $transactiondate,$ac->branch->id);
}
else {
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp("Amount"),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "Recurring Amount Deposit in $ac->AccountNumber"), TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo, $transactiondate);
}
//  PAY COLLECTION CHARGES
if ($ac->scheme->CollectorCommissionRate) {
    $debitAccount = array(
        Branch::getCurrentBranch()->Code . SP . COLLECTION_PAID_ON . SP . $ac->scheme->Name => inp('Amount') * $ac->scheme->CollectorCommissionRate / 100
    );
    $creditAccount = array(
        Branch::getCurrentBranch()->Code . SP . COLLECTION_PAYABLE_ON . SP . $ac->scheme->Name => inp('Amount') * $ac->scheme->CollectorCommissionRate / 100
    );
    $vchNo1 = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
    Transaction::doTransaction($debitAccount, $creditAccount, "Collection charges Deposited for $ac->AccountNumber", TRA_RECURRING_ACCOUNT_COLLECTION_CHARGES_DEPOSIT, $vchNo1, $transactiondate);

//    SAVE RECORD IN COMMISSION REPORT TABLE FOR COLLECTION CHARGES ALSO
    $agentReport = new Agentcommissionreport();
    $agentReport->collector_id = $ac->collector_id;
    $agentReport->accounts_id = $ac->id;
    $agentReport->Collection = inp('Amount') * $ac->scheme->CollectorCommissionRate / 100;
    $agentReport->CommissionPayableDate = getNow("Y-m-d");
    $agentReport->Narration = "Collection charges Deposited for $ac->AccountNumber";
    $agentReport->save();
}

//              FIND REMAINING AMOUNTS

$PaidPremiums = new Premium();
$PaidPremiums->select_max("Paid");
$PaidPremiums->where('accounts_id', $ac->id);
$PaidPremiums->where('Paid >', 0);
$PaidPremiums->where('PaidOn is not null');
//$PaidPremiums->where('Skipped', 0);
$PaidPremiums->get();

$PaidPremiums = $PaidPremiums->Paid;


$PaidPremiums_count = new Premium();
$PaidPremiums_count->where('accounts_id', $ac->id);
$PaidPremiums_count->where('Paid >', 0);
$PaidPremiums_count->where('PaidOn is not null');
$PaidPremiums_count->get();

//--------UPDATING ALL PREMIUMS-----------------------------------


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
$PremiumAmountAdjusted = $PaidPremiums_count->result_count() * $ac->RdAmount;
$AmountForPremiums = $ac->CurrentBalanceCr + inp("Amount") - $PremiumAmountAdjusted - $q->interest;

$premiumsSubmited = (int) ($AmountForPremiums / $ac->RdAmount);

/* adjusting the remaining premi	mum for the RD account on the money deposited
 * and the date of premimum deposited should be the current date
 */

if ($premiumsSubmited > 0) {
//    $q="select p.* from jos_xpremiums p where accounts_id=$ac->id and Paid=0 and Skipped=0 order by id limit $premiumsSubmited";
//    $q = Doctrine_Query::create()
//                    ->select("p.*")
//                    ->from("Premiums p")
//                    ->where("accounts_id=? and Paid = 0 and Skipped = 0", array($ac->id))
//                    ->orderBy("id")
//                    ->limit($premiumsSubmited);
//    $CI = & get_instance();
//    $result = $CI->db->query($q)->result();
    $result = new Premium();
    $result->where("accounts_id",$ac->id);
    $result->where("PaidOn is null");
//    $result->where("Skipped", 0);
    $result->order_by("id");
    $result->limit($premiumsSubmited)->get();

    foreach ($result as $r) {
    	if(date("Y",strtotime($r->DueDate)) < date("Y",strtotime($transactiondate)))
    		$PaidPremiums;
    	else{
	    	if( date("m",strtotime($r->DueDate)) < date("m",strtotime($transactiondate)))
    			$PaidPremiums;
    		else
	    		++$PaidPremiums;
    	}
        $r->PaidOn = $transactiondate;
        $r->Paid = $PaidPremiums;
        $r->save();
    }
}
if (!SET_COMMISSIONS_IN_MONTHLY) {
    Premium::setCommissions($ac, $voucherNo, getNow("Y-m-d"));
}
// send sms to customer
//             $mobile=substr($ac->Member->PhoneNos, 0, 10);
//            if(is_numeric($mobile) && strlen($mobile)==10){
//                $sms=new sms();
$msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
$this->sendSMS($ac, $msg);
?>
