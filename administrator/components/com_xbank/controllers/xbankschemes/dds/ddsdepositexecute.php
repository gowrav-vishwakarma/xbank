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
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "DDS Amount Deposit in $ac->AccountNumber"), TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo, $transactiondate);

    $debitAccount = array(
        Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $ac->branch->Code => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp('Amount'),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "DDS Amount Deposit in $ac->AccountNumber"), TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber($ac->branch->id), $transactiondate,$ac->branch->id);
}
else {
    $debitAccount = array(
        $debitToAccount => inp("Amount"),
    );
    $creditAccount = array(
        $ac->AccountNumber => inp("Amount"),
    );
    Transaction::doTransaction($debitAccount, $creditAccount, (inp("Narration") ? inp("Narration") : "DDS Amount Deposit in $ac->AccountNumber"), TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT, $voucherNo, $transactiondate);
}








        if(!SET_COMMISSIONS_IN_MONTHLY){
                if ($ac->Agents !== null) {
                    $monthDifference = my_date_diff(getNow("Y-m-d"), $ac->created_at);
                    $monthDifference = $monthDifference["months_total"]+1;
                    $percent = explode(",", $ac->Schemes->AccountOpenningCommission);
                    $percent = (isset($percent[$monthDifference])) ? $percent[$monthDifference] : $percent[count($percent) - 1];
                    $amount = $amount * $percent /100;
                    $agentAccount = $ac->Agents->AccountNumber;

                    $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
                    $debitAccount = array(
                        Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $ac->Schemes->Name => $amount,
                    );
                    $creditAccount = array(
                        // get agents' account number
        //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                        $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                        Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * TDS_PERCENTAGE / 100),
                    );
                    Transaction::doTransaction($debitAccount, $creditAccount, "DDS Premium Commission", TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo);
        //            Accounts::updateInterest($ac->Agents);
                }
        }
        $msg = "Dear " . $ac->Member->Name . ", you have deposited an amount of Rs. " . inp("Amount") . " in your account $ac->AccountNumber on " . getNow("Y-m-d");
        $this->sendSMS($ac, $msg);
?>
