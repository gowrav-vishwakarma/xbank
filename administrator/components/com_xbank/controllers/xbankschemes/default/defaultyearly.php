<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// DEPRECIATION CALCULATIONS
//$q = Doctrine_Query::create()
//                        ->select("s.*")
//                        ->from("Schemes s")
//                        ->where("s.isDepriciable = 1");
//        $schemes = $q->execute();

        $schemes = new Scheme();
        $schemes->where("isDepriciable",1)->get();

        foreach ($schemes as $sc) {
//            $query = Doctrine_Query::create()
//                            ->select("a.*")
//                            ->from("Accounts a")
//                            ->where("a.schemes_id = $sc->id AND a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);
//            $accounts = $query->execute();
            
            $CI = & get_instance();
            $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);



            if ($accounts->num_rows() == 0)
                continue;
            foreach ($accounts->result() as $a) {
                if (strtotime($a->created_at) > strtotime((getNow('Y') - 1) . "-09-30")) {
                    $depr = $sc->DepriciationPercentAfterSep;
                } else {
                    $depr = $sc->DepriciationPercentBeforeSep;
                }

                $depAmt = ($a->CurrentBalanceDr - $a->CurrentBalanceCr) * $depr / 100;
//                    echo $depAmt;
                $debitAccount = array(
                    $b->Code . SP . DEPRECIATION_ON_FIXED_ASSETS => round($depAmt)
                );
                $creditAccount = array(
                    $a->AccountNumber => round($depAmt)
                );

                Transaction::doTransaction($debitAccount, $creditAccount, "Depreciation amount calculated", TRA_DEPRICIATION_AMOUNT_CALCULATED, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
            }
        }
?>
