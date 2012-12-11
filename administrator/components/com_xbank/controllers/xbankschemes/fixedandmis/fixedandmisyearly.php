<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// REVERSE ENTRY OF ALL PROVISIONS DONE MONTHLY
$schemes = new Scheme();
$schemes->where("SchemeType","'".ACCOUNT_TYPE_FIXED."'")->get();
        foreach ($schemes as $sc) {

            $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->result();
            if ($accounts->num_rows() == 0)
                continue;

            $totals = 0;
            $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;


            $schemeName = $sc->Name;

            $creditAccount = array();

            $debitAccount = array(
                $b->Code . SP . INTEREST_PROVISION_ON . $schemeName => round($totals)
            );

            foreach ($accounts as $acc) {
                $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
            }

            Transaction::doTransaction($debitAccount, $creditAccount, "Yearly Interst posting to Fixed Accounts", TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
        }

        $q = "UPDATE jos_xaccounts as a join schemes as s SET a.CurrentInterest=0 WHERE s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' and s.InterestToAnotherAccount=0 and s.InterestToAnotherAccountPercent=0 and a.ActiveStatus=1 and a.MaturedStatus=0  and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
        executeQuery($q);
?>
