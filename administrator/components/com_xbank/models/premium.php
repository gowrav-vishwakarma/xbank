<?php

class Premium extends DataMapper {

    var $table = 'xpremiums';
    var $has_one = array(
        'account' => array(
            'class' => 'account',
            'join_other_as' => 'accounts',
            'join_table' => 'jos_xpremiums',
            'other_field' => 'premiums'
        )
    );

    public static function setCommissions($ac, $voucherNo, $transactiondate) {
        

        // $ssddhj = ($ac->id == 5077 ? "sdsds" : "dhdbhj");

        $CI = & get_instance();
        $amount = $CI->db->query("select SUM(Amount * AgentCommissionPercentage / 100.00 ) AS Totals from jos_xpremiums where Paid <> 0 AND Skipped = 0 AND AgentCommissionSend = 0 AND accounts_id = $ac->id ")->row()->Totals;
        $ag = new Agent($ac->agents_id);
        $agentAccount = $ag->AccountNumber;//get()->AccountNumber;
        if ($agentAccount) {
            // echo "-- agent account " . $agentAccount. "<br/>";
            $agent = new Agent();
            $agent->where("AccountNumber",$agentAccount)->get();
            $schemename = $ac->scheme->Name;
            $agentAccBranch = new Account();
            $agentAccBranch->where("AccountNumber",$agent->AccountNumber)->get();
            if ($agentAccBranch->branch_id != Branch::getCurrentBranch()->id) {
                // INTERBRANCH TRANSECTION
                $otherbranch = new Branch($agentAccBranch->branch_id);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $schemename => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    // Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $otherbranch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * 10 / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ".$ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $otherbranch->Code => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                );
                $creditAccount = array(
                    // get agents' account number
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ". $ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate, $otherbranch->id);
            } else {
//                $tdsaccount = Branch::getCurrentBranch()->Code." TDS";
//                $tdsAcc = Doctrine::getTable("Accounts")->findOneByAccountnumberAndBranch_id($tdsaccount,Branch::getCurrentBranch()->id)->AccountNumber;

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $schemename => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    // Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * 10 / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ". $ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);
            }
            executeQuery("UPDATE jos_xpremiums SET AgentCommissionSend=1 WHERE Paid <> 0 AND Skipped = 0 AND AgentCommissionSend = 0 AND accounts_id = " . $ac->id);
//            $AgentSavingAccount=Accounts::getAccountForCurrentBranch(Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id,false);
//            Accounts::updateInterest($agentAccount);
        }
    }

    public static function setCommissions_old($ac, $voucherNo, $transactiondate) {
        

        $ssddhj = ($ac->id == 5077 ? "sdsds" : "dhdbhj");

        $CI = & get_instance();
        $amount = $CI->db->query("select SUM(Amount * AgentCommissionPercentage / 100.00 ) AS Totals from jos_xpremiums where Paid <> 0 AND Skipped = 0 AND AgentCommissionSend = 0 AND accounts_id = $ac->id AND PaidOn >= DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and
        PaidOn < '" . getNow("Y-m-d") . "'")->row()->Totals;
        $scfcsg = $ac->AccountNumber;
        $ag = new Agent($ac->agents_id);
        $agentAccount = $ag->AccountNumber;//get()->AccountNumber;
        $gbngfn =10;
        if ($agentAccount) {
            echo "-- agent account " . $agentAccount. "<br/>";
            $agent = new Agent();
            $agent->where("AccountNumber",$agentAccount)->get();
            $schemename = $ac->scheme->Name;
            $agentAccBranch = new Account();
            $agentAccBranch->where("AccountNumber",$agent->AccountNumber)->get();
            if ($agentAccBranch->branch_id != Branch::getCurrentBranch()->id) {
                // INTERBRANCH TRANSECTION
                $otherbranch = new Branch($agentAccBranch->branch_id);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $schemename => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    // Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $otherbranch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * 10 / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ".$ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $otherbranch->Code => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                );
                $creditAccount = array(
                    // get agents' account number
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ". $ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate, $otherbranch->id);
            } else {
//                $tdsaccount = Branch::getCurrentBranch()->Code." TDS";
//                $tdsAcc = Doctrine::getTable("Accounts")->findOneByAccountnumberAndBranch_id($tdsaccount,Branch::getCurrentBranch()->id)->AccountNumber;

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $schemename => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    // Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * 10 / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ". $ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);
            }
            executeQuery("UPDATE jos_xpremiums SET AgentCommissionSend=1 WHERE Paid <> 0 AND Skipped = 0 AND AgentCommissionSend = 0 AND PaidOn < '" . getNow("Y-m-d") . "' AND accounts_id = " . $ac->id);
//            $AgentSavingAccount=Accounts::getAccountForCurrentBranch(Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id,false);
//            Accounts::updateInterest($agentAccount);
        }
    }

    function reAdjustPaidValue(){

        $CI =&get_instance();
        $tilldate= getNow('Y-m-t');

        $CI->db->query("UPDATE jos_xpremiums SET Paid=0 WHERE accounts_id = $this->accounts_id");
        $due_and_paid_query = $CI->db->query("SELECT GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray, GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray FROM jos_xpremiums WHERE accounts_id = $this->accounts_id AND (PaidOn < '$tilldate' OR DueDate < '$tilldate') ORDER BY id")->row();
        $due_array=explode(",",$due_and_paid_query->DueArray);
        $paid_array=explode(",",$due_and_paid_query->PaidArray);

        
        $account_premiums=new Premium();
        $account_premiums
        ->where('accounts_id',$this->accounts_id)
        ->group_start()
            ->where("PaidOn <= '$tilldate'")
            ->or_where("DueDate <= '$tilldate'")
        ->group_end()
        ->order_by('id')
        ->get();

        // echo $account_premiums->check_last_query();

        $i=0;
        foreach($account_premiums as $p){
            // echo "setting";
            $paid=0;
            for($j=0;$j<=$i;$j++){
                if(isset($paid_array[$j]) AND $paid_array[$j] <= $due_array[$i]) $paid++;
                // if(isset($paid_array[$j]) AND $j==0 AND $paid_array[$j] > $due_array[$i]) $paid++;
            }
            $p->Paid= $paid;
            $p->save();                                
            $i++;
        }

    }

}

?>
