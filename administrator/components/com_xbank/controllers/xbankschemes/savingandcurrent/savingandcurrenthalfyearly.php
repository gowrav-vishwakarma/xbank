<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// SAVING HALF_YEARLY INTEREST CALCULATION
$interest = 0 ;
// SET LASTCURRENTINTERESTUPDATEDAT = NOW() - 6 MONTHS, CURRENTINTEREST  = 0
$q="UPDATE jos_xaccounts a
        join jos_xtransactions t on t.accounts_id=a.id
        join jos_xschemes s on s.id=a.schemes_id
        SET a.LastCurrentInterestUpdatedAt = DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH),
        a.CurrentInterest = 0
        where t.branch_id=".Branch::getCurrentBranch()->id." and
        /* t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '".  getNow("Y-m-d")."' and */
        s.SchemeType ='".ACCOUNT_TYPE_BANK."' and
        a.ActiveStatus = 1";
executeQuery($q);
$CI = & get_instance();

$accounts = $CI->db->query("select t.*, a.id as id, s.Interest from jos_xtransactions t
                            join jos_xaccounts a on a.id=t.accounts_id
                            join jos_xschemes s on s.id=a.schemes_id
                            where t.branch_id =".Branch::getCurrentBranch()->id." and
                                t.created_at between DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -6 MONTH) and '".  getNow("Y-m-d")."' and
                                s.SchemeType ='".ACCOUNT_TYPE_BANK."' and
                                a.ActiveStatus = 1 /*and t.accounts_id in (82,10766) */
                                order by t.created_at")->result();

//echo "<table>";
foreach($accounts as $a){

    $queryA = $CI->db->query("select sum(t.amountCr) as CRSum from jos_xtransactions t
                            left join jos_xaccounts a on t.accounts_id=a.id
                            where t.branch_id=".Branch::getCurrentBranch()->id." and
                                t.created_at > a.LastCurrentInterestUpdatedAt and
                                a.id = $a->accounts_id")->row();
    //t.created_at between DATE_ADD(a.LastCurrentInterestUpdatedAt,INTERVAL +1 DAY) and '".getNow("Y-m-d")."' and

    $CRSum = $queryA->CRSum;


    if($CRSum)
        $CRSum = $CRSum;
    else
        $CRSum = 0;

    $queryB = $CI->db->query("select sum(t.amountDr) as DRSum from jos_xtransactions t
                            join jos_xaccounts a on t.accounts_id=a.id
                            where t.branch_id=".Branch::getCurrentBranch()->id." and
                                t.created_at > a.LastCurrentInterestUpdatedAt and
                                a.id = $a->accounts_id")->row();
    $DRSum = $queryB->DRSum;
    if($DRSum)
        $DRSum = $DRSum;
    else
        $DRSum = 0;
//    if($a->accounts_id == 368)
//        $asd = "dnbmvfb";
//    $q="UPDATE jos_xaccounts AS a
//        SET a.CurrentInterest = a.CurrentInterest + (IF(((a.CurrentBalanceCr - $CRSum) - (a.CurrentBalanceDr - $DRSum)) > 0 ,((a.CurrentBalanceCr - $CRSum) - (a.CurrentBalanceDr - $DRSum)),0) * $a->Interest * DATEDIFF('$a->created_at',a.LastCurrentInterestUpdatedAt)/36500 ),
//        a.LastCurrentInterestUpdatedAt = '".$a->created_at."'
//        WHERE a.id = $a->accounts_id";
//    executeQuery($q);

    $account = new Account($a->accounts_id);

//    $interest += $account->CurrentInterest;
    $daydiff = my_date_diff(date("Y-m-d",strtotime($a->created_at)),date("Y-m-d",strtotime($account->LastCurrentInterestUpdatedAt)));
    $intr = ((($account->CurrentBalanceCr - $CRSum) - ($account->CurrentBalanceDr - $DRSum)) > 0 ? (($account->CurrentBalanceCr - $CRSum) - ($account->CurrentBalanceDr - $DRSum)): 0) * $a->Interest * $daydiff['days_total']/36500 ;
//    echo "<tr><td>".date("Y-m-d",strtotime($a->created_at))."</td><td>".(($account->CurrentBalanceCr - $CRSum) - ($account->CurrentBalanceDr - $DRSum))."</td><td>".$intr."</td><td>".$daydiff['days_total']."</td></tr>";
    $account->CurrentInterest += $intr;
    $account->LastCurrentInterestUpdatedAt = $a->created_at;
    $account->save();
}

//echo "</table>";


// HALF-YEARLY INTEREST POSTING IN SAVING ACCOUNTS
$query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.CurrentInterest=round(if(a.CurrentInterest > 0,a.CurrentInterest,0)+((a.CurrentBalanceCr-a.CurrentBalanceDr)*s.Interest*DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500)), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE s.SchemeType='" . ACCOUNT_TYPE_BANK . "' and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and  a.branch_id = " . $b->id;
        executeQuery($query);

        $schemes =  new Scheme();
        $schemes->where("Schemetype",ACCOUNT_TYPE_BANK)->get();

        foreach ($schemes as $sc) {

            $CI = & get_instance();
            $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);

            if ($accounts->num_rows() == 0)
                continue;


            $t = new Account();
            $t->select("CurrentInterest as CurrentInterest");
            $t->where("schemes_id = " . $sc->id . " AND CurrentInterest > 0 and ActiveStatus = 1 and created_at < '" . getNow("Y-m-d") . "' and branch_id = " . $b->id);
            $t->get();
            $totals = 0;
            foreach ($t as $total)
                $totals +=$total->CurrentInterest;

            $schemeName = $sc->Name;

            $creditAccount = array();

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => $totals
            );

            foreach ($accounts->result() as $acc) {
                $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
            }
            $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => NULL);
            Transaction::doTransaction($debitAccount, $creditAccount, "Saving Account Interst posting", TRA_INTEREST_POSTING_IN_SAVINGS, $voucherNo, date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
        }

