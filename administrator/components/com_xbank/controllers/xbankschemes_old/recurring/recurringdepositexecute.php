<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$transactiondate = getNow();

$debitAccount = array(
    $debitToAccount => inp("Amount"),
);
$creditAccount = array(
    $ac->AccountNumber => inp("Amount"),
);
Transaction::doTransaction($debitAccount, $creditAccount, "Recurring Amount Deposit in $ac->AccountNumber", TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo, $transactiondate);

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
$PaidPremiums->where('accounts_id', $ac->id);
$PaidPremiums->where('Paid <>', 0);
$PaidPremiums->where('Skipped', 0);
$PaidPremiums->get();

$PaidPremiums = $PaidPremiums->result_count();
$PremiumAmountAdjusted = $PaidPremiums * $ac->RdAmount;
$AmountForPremiums = $ac->CurrentBalanceCr + inp("Amount") - $PremiumAmountAdjusted;

$premiumsSubmited = (int) ($AmountForPremiums / $ac->RdAmount);

/* adjusting the remaining premimum for the RD account on the money deposited
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
    $result->where("Paid", 0);
    $result->where("Skipped", 0);
    $result->order_by("id");
    $result->limit($premiumsSubmited)->get();

    foreach ($result as $r) {
        $r->PaidOn = $transactiondate;
        $r->Paid = ++$PaidPremiums;
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
