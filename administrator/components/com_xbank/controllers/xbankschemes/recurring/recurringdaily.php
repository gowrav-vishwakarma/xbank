<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// INTEREST TRANSFER IN RECURRING ACCOUNTS
$CI = & get_instance();
$schemes = new Scheme();
$schemes->where("SchemeType", ACCOUNT_TYPE_RECURRING )->get();
$c = $schemes->result_count();
foreach ($schemes as $sc) {
    $query = "UPDATE jos_xaccounts as a JOIN jos_xschemes as s on a.schemes_id=s.id join (SELECT accounts_id, SUM(Paid*Amount) AS toPost FROM jos_xpremiums WHERE Paid <> 0 AND Skipped =0 And DueDate < '" . getNow("Y-m-d") . "' GROUP BY accounts_id) as p on p.accounts_id=a.id SET a.CurrentInterest=ROUND((p.toPost * s.Interest)/1200,2) WHERE s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' and a.ActiveStatus=1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
        executeQuery($query);

    $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus =1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);

    if ($accounts->num_rows() == 0)
        continue;


            $totals = 0;
            $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d"). "' and a.branch_id = " . $b->id)->row()->CurrentInterest;

            $schemeName = $sc->Name;

            $creditAccount = array();

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => $totals
            );

            foreach ($accounts->result() as $acc) {
                $creditAccount += array($acc->AccountNumber => $acc->CurrentInterest);
            }

            Transaction::doTransaction($debitAccount, $creditAccount, "Interest posting in Recurring Account", TRA_INTEREST_POSTING_IN_RECURRING, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " +0 day")));

        $q = "UPDATE jos_xaccounts as a join jos_xschemes as s SET a.CurrentInterest=0, a.MaturedStatus=1, a.affectsBalanceSheet = 1 WHERE s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' and a.ActiveStatus=1 and a.MaturedStatus=0 and DATE_ADD(DATE(a.created_at), INTERVAL $sc->MaturityPeriod MONTH) ='" . getNow("Y-m-d"). "' and a.branch_id=" . $b->id;
        executeQuery($q);
}


