<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// DDS MATURITY CHECK FOR ACCOUNTS WHICH ARE MATURED TODAY
//$q = Doctrine_Query::create()
//                        ->select("*")
//                        ->from("Schemes")
//                        ->where("SchemeType='".ACCOUNT_TYPE_DDS."'");
//        $schemes = $q->execute();
$CI = & get_instance();
$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_DDS)->get();
// echo $schemes->check_last_query();
if($schemes->result_count()>0){
        foreach ($schemes as $sc) {
            $query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id SET a.CurrentInterest=round(a.CurrentInterest + (a.CurrentBalanceCr * s.Interest * DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500),2), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE s.SchemeType='" . ACCOUNT_TYPE_DDS . "'  and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL +$sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id;
            // echo "<br/>2 = ".$query;
            executeQuery($query);


//            $query = Doctrine_Query::create()
//                            ->select("a.AccountNumber, a.CurrentInterest")
//                            ->from("Accounts a")
//                            ->where("a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d"). "' and a.branch_id=" . $b->id);
//            $accounts = $query->execute();
            $accounts = $CI->db->query("select a.AccountNumber, a.CurrentInterest from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d"). "' and a.branch_id=" . $b->id)->result();
         
            if (!$accounts)
                continue;

//                 $this->db->select("SUM(accounts.CurrentInterest) As Totals");
//                 $this->db->from("accounts");
//                 $this->db->where("schemes_id = $sc->id AND accounts.CurrentInterest > 0 and accounts.ActiveStatus=1 and accounts.LockingStatus=0 and accounts.branch_id=".$b->id);
//                 $totals=$this->db->get()->row()->Totals;
//            $t = Doctrine_Query::create()
//                            ->select("a.CurrentInterest")
//                            ->from("Accounts a")
//                            ->where("a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
//            $tot = $t->execute();
//            $totals = 0;
//            foreach ($tot as $total)
//                $totals +=$total->CurrentInterest;

            $totals = 0;
            $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;


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

            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in DDS Account", TRA_INTEREST_POSTING_IN_DDS, Transactions::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " +0 day")));
        }

//             $this->db->query("UPDATE accounts SET CurrentInterest=0");
//             $q="UPDATE accounts SET CurrentInterest=0 where branch_id = ".$b->id;
        $q = "UPDATE jos_xaccounts as a join jos_xschemes as s on a.schemes_id=s.id SET a.CurrentInterest=0, a.MaturedStatus=1 WHERE s.SchemeType='" . ACCOUNT_TYPE_DDS . "' and a.ActiveStatus=1 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
        // echo "<br/>3=".$q;
        executeQuery($q);
        
}
