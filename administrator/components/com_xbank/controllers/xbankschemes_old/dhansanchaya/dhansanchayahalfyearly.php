<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// SAVING HALF_YEARLY INTEREST CALCULATION

// SET LASTCURRENTINTERESTUPDATEDAT = NOW() - 6 MONTHS, CURRENTINTEREST  = 0
$q="UPDATE accounts a
        join transactions t on t.accounts_id=a.id
        join schemes s on s.id=a.schemes_id
        SET a.LastCurrentInterestUpdatedAt = DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH),
        a.CurrentInterest = 0
        where t.branch_id=".Branch::getCurrentBranch()->id." and
        t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '".  getNow("Y-m-d")."' and
        s.SchemeType ='".ACCOUNT_TYPE_BANK."' and
        a.ActiveStatus = 1";
executeQuery($q);

$accounts=Doctrine_Query::create()
                ->select(" t.*,a.id, s.Interest as Interest")
                ->from("Transactions t")
                ->innerJoin(" t.Accounts a ")
                ->innerJoin("a.Schemes s")
                ->where("t.branch_id=".Branch::getCurrentBranch()->id." and
                        t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '".  getNow("Y-m-d")."' and
                        s.SchemeType ='".ACCOUNT_TYPE_BANK."' and a.ActiveStatus = 1")
                ->orderBy("t.created_at");
$accounts = $accounts->execute();

foreach($accounts as $a){
    $queryA=Doctrine_Query::create()
                            ->select(" sum(t.amountCr) as CRSum")
                            ->from(" Transactions t")
                            ->innerJoin("t.Accounts a")
                            ->where("t.branch_id=".Branch::getCurrentBranch()->id." and t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and a.id = $a->accounts_id");
    $CRSum = $queryA->execute()->getFirst()->CRSum;
    if($CRSum)
        $CRSum = $CRSum;
    else
        $CRSum = 0;
    $queryB=Doctrine_Query::create()
                            ->select(" sum(t.amountDr) as DRSum")
                            ->from(" Transactions t")
                            ->innerJoin("t.Accounts a")
                            ->where("t.branch_id=".Branch::getCurrentBranch()->id." and t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and a.id = $a->accounts_id");
    $DRSum = $queryB->execute()->getFirst()->DRSum;
    if($DRSum)
        $DRSum = $DRSum;
    else
        $DRSum = 0;
    if($a->accounts_id == 152)
        $asd = "dnbmvfb";
    $q="UPDATE accounts AS a
        SET a.CurrentInterest = a.CurrentInterest + (IF(((a.CurrentBalanceDr - $DRSum) - (a.CurrentBalanceCr - $CRSum)) > 0 ,((a.CurrentBalanceDr - $DRSum) - (a.CurrentBalanceCr - $CRSum)),0) * $a->Interest * DATEDIFF('$a->created_at',a.LastCurrentInterestUpdatedAt)/36500 ) ,
       a.LastCurrentInterestUpdatedAt='".$a->created_at."' WHERE a.id = $a->accounts_id";
    executeQuery($q);
}




// HALF-YEARLY INTEREST POSTING IN SAVING ACCOUNTS
$query = "UPDATE accounts as a JOIN schemes as s on a.schemes_id=s.id SET a.CurrentInterest=round(if(a.CurrentInterest > 0,a.CurrentInterest,0)+((a.CurrentBalanceCr-a.CurrentBalanceDr)*s.Interest*DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500)), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE s.SchemeType='" . ACCOUNT_TYPE_BANK . "' and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and  a.branch_id = " . $b->id;
        Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

        $schemes = Doctrine::getTable("Schemes")->findBySchemetype(ACCOUNT_TYPE_BANK);
//             $this->db->select("id, Name");
//             $this->db->from("schemes");
//             $this->db->where("Schemetype = '". ACCOUNT_TYPE_BANK ."'");
//             $schemes=$this->db->get();

        foreach ($schemes as $sc) {

            $q = Doctrine_Query::create()
                            ->select("a.AccountNumber, a.CurrentInterest")
                            ->from("Accounts a")
                            ->where("a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
            $accounts = $q->execute();
//                 $this->db->select("accounts.AccountNumber, accounts.CurrentInterest");
//                 $this->db->from("accounts");
//                 $this->db->where("schemes_id = $sc->id AND accounts.CurrentInterest > 0 and accounts.ActiveStatus =1 and accounts.LockingStatus = 0 and accounts.branch_id = ".$b->id);
//                 $accounts=$this->db->get();

            if ($accounts->count() == 0)
                continue;


            $t = Doctrine_Query::create()
                            ->select("a.CurrentInterest")
                            ->from("Accounts a")
                            ->where("a.schemes_id = " . $sc->id . " AND a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
            $tot = $t->execute();
            $totals = 0;
            foreach ($tot as $total)
                $totals +=$total->CurrentInterest;

//                 $this->db->select("SUM(accounts.CurrentInterest) As Totals");
//                 $this->db->from("accounts");
//                 $this->db->where("schemes_id = ".$sc->id." and ActiveStatus = 1 and LockingStatus = 0 and branch_id = ".$b->id);
//                 $totals=$this->db->get()->row()->Totals;

            $schemeName = $sc->Name;

//                 echo "<pre>";
//                 print_r($accounts->result_array());
//                 echo "</pre>";

            $creditAccount = array();

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => $totals
            );

            foreach ($accounts as $acc) {
                $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
            }
            $voucherNo = array('voucherNo' => Transactions::getNewVoucherNumber(), 'referanceAccount' => NULL);
            Transactions::doTransaction($debitAccount, $creditAccount, "Saving Account Interst posting", TRA_INTEREST_POSTING_IN_SAVINGS, $voucherNo, date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
        }

//             $this->db->query("UPDATE accounts SET CurrentInterest=0");
//        $q = "update `accounts` as `a` join `schemes` as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` = 0 where `s`.`SchemeType`= '" . ACCOUNT_TYPE_BANK . "' and `a`.`ActiveStatus`=1  and `a`.`created_at` < '" . getNow("Y-m-d") . "' and `a`.`branch_id`=" . $b->id;
//        Doctrine_Manager::getInstance()->getCurrentConnection()->execute($q);
?>
