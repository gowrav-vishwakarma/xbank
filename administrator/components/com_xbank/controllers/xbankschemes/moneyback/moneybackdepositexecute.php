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
Transaction::doTransaction($debitAccount, $creditAccount, "Initial Recurring Amount Deposit in $ac->AccountNumber", TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo,$transactiondate);
//              FIND REMAINING AMOUNTS

 $PaidPremiums=new Premium();
 $PaidPremiums->where('accounts_id',$ac->id)->where('Paid <>',0)->where('Skipped',0)->get();
//$PaidPremiums = Doctrine::getTable("Premiums")->createQuery()
//                ->where("accounts_id = ? AND Paid <> 0 AND Skipped = 0", array($ac->id))->execute();
//                            ->findByAccounts_idAndPaidAndSkipped($ac->id,"1","0")->count();
$PaidPremiums = $PaidPremiums->count();
$PremiumAmountAdjusted = $PaidPremiums * $ac->RdAmount;
$AmountForPremiums = $ac->CurrentBalanceCr + inp("Amount") - $PremiumAmountAdjusted;

$premiumsSubmited = (int) ($AmountForPremiums / $ac->RdAmount);

/* adjusting the remaining premimum for the RD account on the money deposited
 * and the date of premimum deposited should be the current date
 */

if ($premiumsSubmited > 0) {
    $q="select p.* from jos_xpremiums p where accounts_id=$ac->id and Paid=0 and Skipped=0 order by id limit $premiumsSubmited";
//    $q = Doctrine_Query::create()
//                    ->select("p.*")
//                    ->from("Premiums p")
//                    ->where("accounts_id=? and Paid = 0 and Skipped = 0", array($ac->id))
//                    ->orderBy("id")
//                    ->limit($premiumsSubmited);

    $result = $q->execute();
    foreach ($result as $r) {
        $r->PaidOn = $transactiondate;
        $r->Paid = ++$PaidPremiums;
        $r->save();
    }
}
if(!SET_COMMISSIONS_IN_MONTHLY){
    Premium::setCommissions($ac, $voucherNo,getNow("Y-m-d"));
}
// send sms to customer
//             $mobile=substr($ac->Member->PhoneNos, 0, 10);
//            if(is_numeric($mobile) && strlen($mobile)==10){
//                $sms=new sms();
$msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
$this->sendSMS($ac, $msg);
?>
