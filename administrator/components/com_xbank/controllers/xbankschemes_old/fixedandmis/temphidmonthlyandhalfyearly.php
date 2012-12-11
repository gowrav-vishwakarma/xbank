<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//        HID SCHEME HERE :: TODO- SEPERATE IT

$q = "update accounts a join schemes s on a.schemes_id = s.id set a.CurrentInterest=0, a.LastCurrentInterestUpdatedAt = DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) where
      s.Schemetype = '" . ACCOUNT_TYPE_FIXED . "' and s.InterestToAnotherAccount = 1 and s.InterestToAnotherAccountPercent != 0 and a.DefaultAC = 0";
executeQuery($q);

$q = Doctrine_Query::create()
                ->select("id, Name, Interest, InterestToAnotherAccountPercent")
                ->from("schemes")
                ->where("Schemetype = '" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount = 1 and InterestToAnotherAccountPercent != 0");
$schemes = $q->execute();

foreach ($schemes as $sc) {

    $q = Doctrine_Query::create()
                    ->select("accounts.* ")
                    ->from("accounts")
                    ->where("schemes_id = $sc->id AND accounts.ActiveStatus =1 and accounts.DefaultAC = 0 and accounts.MaturedStatus=0 and accounts.created_at < '" . getNow("Y-m-d") . "' and accounts.branch_id = " . $b->id);
    $accounts = $q->execute();

    if ($accounts->count() == 0)
        continue;

    foreach ($accounts as $acc) {
        $interestToAcc = "";
        if ($acc->InterestToAccount != "")
            $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
        if ($interestToAcc) {

//            $query = "Update accounts set CurrentInterest=0, LastCurrentInterestUpdatedAt = DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) where id=".$acc->id;
//            executeQuery($query);
            $days = my_date_diff(getNow("Y-m-d"), date("Y-m-d",strtotime($acc->LastCurrentInterestUpdatedAt)));
            if($days['days_total'] < 30)
                $interest = ($acc->CurrentBalanceCr - $acc->CurrentBalanceDr) * $days['days_total'] * $sc->Interest / 36500;
            else
                $interest = ($acc->CurrentBalanceCr - $acc->CurrentBalanceDr) * $sc->Interest * 6 / 1200;
            
            $query = "Update accounts set CurrentInterest= $interest , LastCurrentInterestUpdatedAt = '" . getNow("Y-m-d") . "' where id=$acc->id and branch_id = " . $b->id;
            executeQuery($query);
        }
    }
}





//half-yearly
$q = Doctrine_Query::create()
                        ->select("id, Name, Interest, InterestToAnotherAccountPercent")
                        ->from("Schemes")
                        ->where("Schemetype = '" . ACCOUNT_TYPE_FIXED . "' and InterestToAnotherAccount = 1 and InterestToAnotherAccountPercent != 0");
        $schemes = $q->execute();

        foreach ($schemes as $sc) {

            $q = Doctrine_Query::create()
                            ->select("accounts.* ")
                            ->from("Accounts as accounts")
                            ->where("accounts.schemes_id = $sc->id AND accounts.ActiveStatus =1 and accounts.MaturedStatus=0  and accounts.created_at < '" . getNow("Y-m-d") . "' and accounts.branch_id = " . $b->id);
            $accounts = $q->execute();

            if ($accounts->count() == 0)
                continue;

            foreach ($accounts as $acc) {
                $interestToAcc = "";
                if ($acc->InterestToAccount != "")
                    $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
//                     $interestToAcc->CurrentInterest= $interestToAcc->CurrentInterest + $acc->CurrentBalanceCr *
                if ($interestToAcc) {
//                     $query="update accounts as a join accounts as b on a.InterestToAccount=b.id set a.CurrentBalanceCr= a.CurrentInterest + a.CurrentBalanceCr , a.CurrentBalanceDr = a.CurrentBalanceDr + ($sc->InterestToAnotherAccountPercent * a.RdAmount)/100, a.LastCurrentInterestUpdatedAt = '".getNow("Y-m-d")."', b.CurrentBalanceCr = b.CurrentBalanceCr + ($sc->InterestToAnotherAccountPercent * a.RdAmount)/100 where a.id=$acc->id and b.id=$interestToAcc->id and a.branch_id = ".$b->id;
//                     executeQuery($query);
//                        $days=my_date_diff(getNow("Y-m-d"),$acc->LastCurrentInterestUpdatedAt);
                    $interest = ($sc->InterestToAnotherAccountPercent * $acc->RdAmount) / 100;
                    $creditAccount = array($acc->AccountNumber => $acc->CurrentInterest);

                    $debitAccount = array(
                        $b->Code . SP . INTEREST_PAID_ON . $sc->Name => $acc->CurrentInterest
                    );
                    Transactions::doTransaction($debitAccount, $creditAccount, "HID Interest posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_HID_ACCOUNT, Transactions::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));


                    $creditAccount = array($interestToAcc->AccountNumber => $interest);

                    $debitAccount = array(
                        $acc->AccountNumber => $interest
                    );
                    Transactions::doTransaction($debitAccount, $creditAccount, "HID Interest posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_HID_ACCOUNT, Transactions::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));




                    $q = "update accounts set CurrentInterest = 0 where id=$acc->id and branch_id = " . $b->id;
                    executeQuery($q);
                }
            }
        }
?>
