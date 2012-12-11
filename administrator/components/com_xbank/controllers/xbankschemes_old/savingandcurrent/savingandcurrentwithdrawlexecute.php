<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// $details = Accounts::updateInterest($ac);
        $creditAccounts = array(
            $creditToAccount => inp('Amount')
        );
        $debitAccounts = array(
            $ac->AccountNumber => inp('Amount')
        );

        Transaction::doTransaction($debitAccounts, $creditAccounts, "Amount withdrawl from $ac->AccountNumber", TRA_SAVING_ACCOUNT_AMOUNT_WITHDRAWL, $voucherNo);
//        $msg = "Saving Account Withdrawl- Interest given " . $details['Interest'] . " on " . $details['AmountForInterest'] . " For " . $details['DateDifferance']['days_total'] . " days.";
//        Log::write($msg, $ac->id);

        // send sms to customer
//             $mobile=substr($ac->Member->PhoneNos, 0, 10);
//            if(is_numeric($mobile) && strlen($mobile)==10){
//                $sms=new sms();
//        $msg = "Dear " . $ac->Member->Name . ", you have withdrawn an amount of Rs. " . inp("Amount") . " from your account $ac->AccountNumber on " . getNow("Y-m-d");
//        $this->sendSMS($ac, $msg);
?>
