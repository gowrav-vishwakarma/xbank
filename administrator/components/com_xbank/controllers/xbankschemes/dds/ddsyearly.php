<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id  SET a.CurrentInterest=((a.CurrentBalanceCr - a.CurrentBalanceDr) * s.Interest)/1200 WHERE  s.SchemeType='" . ACCOUNT_TYPE_DDS . "' and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
        executeQuery($query);


//             $this->db->select("id, Name");
//             $this->db->from("schemes");
//             $this->db->where("Schemetype = '". ACCOUNT_TYPE_RECURRING ."'");
//             $schemes=$this->db->get();

//        $q = Doctrine_Query::create()
//                        ->select("s.id, s.Name")
//                        ->from("Schemes s")
//                        ->where("s.SchemeType = '" . ACCOUNT_TYPE_DDS . "'");
//        $schemes = $q->execute();
        $CI = & get_instance();
        $schemes = new Scheme();
        $schemes->where("SchemeType",ACCOUNT_TYPE_DDS)->get();

        foreach ($schemes as $sc) {

//                 $this->db->select("accounts.AccountNumber, accounts.CurrentInterest");
//                 $this->db->from("accounts");
//                 $this->db->where("schemes_id = $sc->id AND accounts.CurrentInterest > 0 and accounts.ActiveStatus=1 and accounts.LockingStatus=0 and accounts.branch_id=".$b->id);
//                 $accounts=$this->db->get();
//            $query = Doctrine_Query::create()
//                            ->select("a.AccountNumber, a.CurrentInterest")
//                            ->from("Accounts a")
//                            ->where("a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);
//            $accounts = $query->execute();

            $accounts = $CI->db->query("select a.AccountNumber, a.CurrentInterest from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);

            if ($accounts->num_rows() == 0)
                continue;

//                 $this->db->select("SUM(accounts.CurrentInterest) As Totals");
//                 $this->db->from("accounts");
//                 $this->db->where("schemes_id = $sc->id AND accounts.CurrentInterest > 0 and accounts.ActiveStatus=1 and accounts.LockingStatus=0 and accounts.branch_id=".$b->id);
//                 $totals=$this->db->get()->row()->Totals;
//            $t = Doctrine_Query::create()
//                            ->select("a.CurrentInterest")
//                            ->from("Accounts a")
//                            ->where("a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);
//            $tot = $t->execute();
//            $totals = 0;
//            foreach ($tot as $total)
//                $totals +=$total->CurrentInterest;

            $totals = 0;
            $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;


            $schemeName = $sc->Name;

//                 echo "<pre>";
//                 print_r($accounts->result_array());
//                 echo "</pre>";

            $creditAccount = array();

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => $totals
            );

            foreach ($accounts->result() as $acc) {
                $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
            }

            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in DDS Account", TRA_INTEREST_POSTING_IN_DDS, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
        }

//             $this->db->query("UPDATE accounts SET CurrentInterest=0");
//             $q="UPDATE accounts SET CurrentInterest=0 where branch_id = ".$b->id;
        $q = "UPDATE jos_xaccounts as a join jos_xschemes as s SET a.CurrentInterest=0 WHERE s.SchemeType='" . ACCOUNT_TYPE_DDS . "' and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
        executeQuery($q);
?>
