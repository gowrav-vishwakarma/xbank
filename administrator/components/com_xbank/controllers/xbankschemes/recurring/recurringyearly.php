<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// $do_paid_fill = "
//                 UPDATE 
//                     jos_xpremiums p join (SELECT accounts_id, MAX(Paid) max_paid FROM jos_xpremiums p GROUP BY accounts_id) tmp on p.accounts_id = tmp.accounts_id
//                 SET
//                     p.Paid = tmp.max_paid
//                 WHERE
//                     p.DueDate < '".getNow('Y-m-d')."'
//                     AND p.Paid=0
//                     AND p.PaidOn is null
//                 ";
// echo $do_paid_fill;
// $CI->db->query($do_paid_fill);


/*
SELECT
    jos_xpremiums.accounts_id,
    SUM((Paid * Amount) + IFNULL(interest,0) ) AS toPost
FROM
    jos_xpremiums
    LEFT JOIN
    (
        SELECT 
            tt.accounts_id,
            SUM(tt.amountCr)  interest
        FROM
            jos_xtransactions tt
        WHERE
            tt.transaction_type_id=17
        GROUP BY tt.accounts_id
    ) t on jos_xpremiums.accounts_id = t.accounts_id
WHERE
    Paid <> 0
AND Skipped = 0
AND PaidOn > '2013-03-31'
AND DueDate < '2014-04-01'
GROUP BY
    jos_xpremiums.accounts_id

WOrking COde for all previous interests

SELECT jos_xpremiums.accounts_id, Paid, Amount, t.interest FROM jos_xpremiums join ( SELECT tt.accounts_id , sum(tt.amountCr) interest from jos_xtransactions tt WHERE tt.accounts_id=2514 and tt.transaction_type_id=17  ) t on jos_xpremiums.accounts_id=t.accounts_id WHERE  Paid <> 0 AND Skipped =0 And PaidOn > '2013-03-31' AND DueDate < '2014-04-01' AND jos_xpremiums.accounts_id=2514 

*/

$query = "UPDATE 
            jos_xaccounts as a 
            JOIN 
                jos_xschemes as s on a.schemes_id=s.id 
            join 
                (
                    SELECT 
                        jos_xpremiums.accounts_id,
                        SUM((Paid * Amount) + IFNULL(interest,0) ) AS toPost
                    FROM 
                        jos_xpremiums 
                        LEFT JOIN
                        (
                            SELECT 
                                tt.accounts_id,
                                SUM(tt.amountCr)  interest
                            FROM
                                jos_xtransactions tt
                            WHERE
                                tt.transaction_type_id=17
                            GROUP BY tt.accounts_id
                        ) t on jos_xpremiums.accounts_id = t.accounts_id 
                    WHERE 
                        Paid <> 0 
                        AND Skipped =0 
                        And PaidOn > '2014-03-31' 
                        AND DueDate < '" . getNow("Y-m-d") . "' 
                        GROUP BY accounts_id
                ) as p on p.accounts_id=a.id 
            SET 
                a.CurrentInterest=(p.toPost * s.Interest)/1200 
            WHERE 
                (s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "') 
                and a.ActiveStatus=1 
                and a.MaturedStatus=0 
                and a.created_at < '" . getNow("Y-m-d") . "' 
                and a.branch_id=" . $b->id;
        
        executeQuery($query);


        $schemes = new Scheme();
        $schemes->where("SchemeType",ACCOUNT_TYPE_RECURRING)->get();

        foreach ($schemes as $sc) {
            
            $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id AND a.CurrentInterest > 0 and a.ActiveStatus=1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);
            if ($accounts->num_rows() == 0)
                continue;
            // echo "working recurring for " . $sc->Name. "<br/>";

            $totals = 0;
            $totals = $CI->db->query("select sum(a.CurrentInterest) as CurrentInterest from jos_xaccounts a where a.schemes_id = " . $sc->id . " and a.CurrentInterest > 0 and a.ActiveStatus = 1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id)->row()->CurrentInterest;


            $schemeName = $sc->Name;

//                 echo "<pre>";
//                 print_r($accounts->result_array());
//                 echo "</pre>";

            $creditAccount = array();

            $debitAccount = array(
                $b->Code . SP . INTEREST_PAID_ON . $schemeName => round($totals,ROUND_TO)
            );

            foreach ($accounts->result() as $acc) {
                $creditAccount += array($acc->AccountNumber => round($acc->CurrentInterest,ROUND_TO));
            }

            Transaction::doTransaction($debitAccount, $creditAccount, "Interst posting in Recurring Account", TRA_INTEREST_POSTING_IN_RECURRING, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
        }

//             $this->db->query("UPDATE accounts SET CurrentInterest=0");
//             $q="UPDATE accounts SET CurrentInterest=0 where branch_id = ".$b->id;
        $q = "UPDATE jos_xaccounts as a join jos_xschemes as s SET a.CurrentInterest=0 WHERE s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id;
        executeQuery($q);
?>
