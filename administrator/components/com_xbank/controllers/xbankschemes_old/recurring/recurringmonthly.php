<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (SET_COMMISSIONS_IN_MONTHLY) {
//    $q = Doctrine_Query::create()
//                    ->select("a.*")
//                    ->from("Accounts a")
//                    ->innerJoin("a.Transactions t")
//                    ->innerJoin("a.Schemes s")
//                    ->where("a.id = t.accounts_id and s.SchemeType ='" . ACCOUNT_TYPE_RECURRING . "' and t.created_at >= DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and t.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . Branch::getCurrentBranch()->id);
//    $accounts = $q->execute();

    $CI = & get_instance();
    $accounts = $CI->db->query("select a.* from jos_xaccounts a join  jos_xtransactions t on a.id=t.accounts_id join jos_xschemes s on a.schemes_id = s.id where a.id = t.accounts_id and s.SchemeType ='" . ACCOUNT_TYPE_RECURRING . "' and t.created_at >= DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and t.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . Branch::getCurrentBranch()->id)->result();


    $transactiondate = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
    foreach ($accounts as $ac) {
        $acc = new Account($ac->id);
        $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
         Premium::setCommissions($acc, $voucherNo,$transactiondate);
    }
}


//$query = "UPDATE premiums JOIN (SELECT accounts_id, MAX(Paid) AS mPaid FROM premiums GROUP BY accounts_id) AS forMAX on premiums.accounts_id=forMAX.accounts_id join accounts a on premiums.accounts_id=a.id join schemes s on s.id=a.schemes_id SET Skipped = 1, Paid=forMAX.mPaid WHERE Paid = 0 AND Skipped = 0 AND DueDate < '" . getNow("Y-m-d") . "' AND (s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' ) AND a.ActiveStatus=1 AND a.branch_id=" . $b->id;
//executeQuery($query);
?>
