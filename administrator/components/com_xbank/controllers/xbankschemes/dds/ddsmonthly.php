<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (SET_COMMISSIONS_IN_MONTHLY) {

    /*
      $q = Doctrine_Query::create()
      ->select("t.accounts_id as accounts_id,t.created_at as created_at,sum(t.amountCr) as amountCr")
      ->from("Transactions t")
      ->leftJoin("t.Accounts a")
      ->leftJoin("a.Schemes s")
      ->where("a.id = t.accounts_id and s.SchemeType ='" . ACCOUNT_TYPE_DDS . "' and t.created_at >= DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and t.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . Branch::getCurrentBranch()->id)
      ->groupBy("t.accounts_id")
      ->orderBy("t.created_at ASC");
      $accounts = $q->execute();
     */

    $CI = & get_instance();
    $q = "select t.accounts_id as accounts_id,t.created_at as created_at,sum(t.amountCr) as amountCr
        from jos_xtransactions t
        left Join jos_xaccounts a on t.accounts_id=a.id
        left Join jos_xschemes s on a.schemes_id=s.id
        where a.id = t.accounts_id and
        s.SchemeType ='" . ACCOUNT_TYPE_DDS . "' and
        t.created_at >= DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and
        t.created_at < '" . getNow("Y-m-d") . "' and
        a.branch_id=" . Branch::getCurrentBranch()->id . "
        group By t.accounts_id
        order By t.created_at ASC";
    $accounts = $CI->db->query($q)->result();

    foreach ($accounts as $ac) {
//        $acc = Doctrine::getTable("Accounts")->findOneById($ac->accounts_id);
        $acc = new Account($ac->accounts_id);
        $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $acc->id);
        if ($acc->agents_id !== null && $acc->agents_id != 0) {
            $ag = new Agent($acc->agents_id);
            $agentAccount = $ag->AccountNumber;
//            $amount = $ac->amountCr;

//------------CALCULATING COMMISSION FOR DDS----------------
            $DA = $acc->RdAmount; // DA => Monthly DDS Amount
            $x = $ac->amountCr; // x => Amount Submitted in the current month
            $tA = $acc->CurrentBalanceCr - $x; // tA => Total amount till date given excluding x

            while($x > 0){
                $y = $DA- ($tA - ((int)($tA / $DA)) * $DA);
                $z = $tA / $DA;
                $old = ($x / $DA > 1 ? $y : $x);

                $percent = explode(",", $acc->scheme->AccountOpenningCommission);
                $percent = (isset($percent[$z])) ? $percent[$z] : $percent[count($percent) - 1];
                $amount = $old * $percent / 100;

                $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $acc->id);
                $transactiondate = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $acc->scheme->Name => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * TDS_PERCENTAGE / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "DDS Premium Commission $acc->AccountNumber", TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);

                
                $x = $x - $old;
                $tA = $tA + $old;
            }
                
//----------------------------------------------------------

//            $monthDifference = my_date_diff(getNow("Y-m-d"), $acc->created_at);
//            $monthDifference = $monthDifference["months_total"] + 1;
//            $percent = explode(",", $acc->scheme->AccountOpenningCommission);
//            $percent = (isset($percent[$monthDifference])) ? $percent[$monthDifference] : $percent[count($percent) - 1];
//            $amount = $amount * $percent / 100;
//            $ag = new Agent($acc->agents_id);
//            $agentAccount = $ag->AccountNumber;
//            $agentAccount = $acc->agent->AccountNumber;

//            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $acc->id);
//            $transactiondate = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
//            $debitAccount = array(
//                Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $acc->scheme->Name => $amount,
//            );
//            $creditAccount = array(
//                // get agents' account number
//                //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
//                $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
//                Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * TDS_PERCENTAGE / 100),
//            );
//            Transaction::doTransaction($debitAccount, $creditAccount, "DDS Premium Commission $acc->AccountNumber", TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);
            //            Accounts::updateInterest($ac->Agents);
        }
    }
}
?>
