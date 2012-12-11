<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$xl = new xConfig("Loan");
if ($xl->getKey("deposit_penalty_in_closing")) {


$q = "update `jos_xaccounts` as `a` join `jos_xpremiums` as `p` on `p`.`accounts_id`=`a`.`id` join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` = 0 where `s`.`SchemeType`= '" . ACCOUNT_TYPE_LOAN . "' and `a`.`ActiveStatus`=1 and `a`.`created_at` < '" . getNow("Y-m-d") . "' and `a`.`branch_id`=" . $b->id;
executeQuery($q);

//PENALTY TRANSFERING
$loanPenalty = 10;

/*
  $q = "update `accounts` as `a`
  join `premiums` as `p` on `p`.`accounts_id`=`a`.`id`
  join `schemes` as `s` on `a`.`schemes_id`=`s`.`id`
  set a.CurrentInterest = (if(p.PaidOn is null ,DATEDIFF('".getNow("Y-m-d")."', `p`.`DueDate`),if(DATEDIFF(p.PaidOn, `p`.`DueDate`) > 30 ,DATEDIFF('".getNow("Y-m-d")."', `p`.`DueDate`), DATEDIFF(p.PaidOn, `p`.`DueDate`))) * $loanPenalty)
  where `s`.`SchemeType`= '".ACCOUNT_TYPE_LOAN."' and
  DATEDIFF('".getNow("Y-m-d")."', `p`.`DueDate`) <= 30 and
  DATEDIFF('".getNow("Y-m-d")."',`p`.`DueDate`) > 0 and
  if(p.PaidOn is null ,DATEDIFF('".getNow("Y-m-d")."', `p`.`DueDate`),DATEDIFF(p.PaidOn, `p`.`DueDate`)) * $loanPenalty > 0 and
  `a`.`ActiveStatus` = 1 and
  `a`.`branch_id` =" . $b->id;
 *
 */

$q = "update jos_xaccounts as a join (

SELECT temp2.id,temp2.AccountNumber, IF(penaltyLastMonth + penaltyPreviousMonth > 300, 300, penaltyLastMonth + penaltyPreviousMonth) as Penalty
FROM

(
select temp1.id,temp1.AccountNumber, temp1.lastmonthpaidon,temp1.lastmonthDueDate,

IF(DATEDIFF(temp1.lastmonthpaidon,temp1.lastmonthDueDate) < 0,
		0,
		IF(temp1.lastmonthpaidon is NULL,
				if((DATEDIFF('" . getNow("Y-m-d") . "',temp1.lastmonthDueDate) > 30),
						30 * $loanPenalty,
						DATEDIFF('" . getNow("Y-m-d") . "',temp1.lastmonthDueDate) * $loanPenalty ),
			if(DATEDIFF(temp1.lastmonthpaidon,temp1.lastmonthDueDate) > 30,
				30 * $loanPenalty,
				DATEDIFF(temp1.lastmonthpaidon,temp1.lastmonthDueDate) * $loanPenalty )
		))  as penaltyLastMonth,



temp1.previoustolastpaidon,temp1.previoustolastDueDate,
IF((temp1.previoustolastpaidon IS NULL AND temp1.previoustolastDueDate IS NULL)
                                    OR
		(MONTH(temp1.previoustolastpaidon) = MONTH(temp1.previoustolastDueDate)),
		0,
		IF(	MONTH(temp1.previoustolastpaidon)= MONTH(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)),
				IF(temp1.previoustolastpaidon > DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH),IF(DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH),temp1.previoustolastDueDate) > 30,
                                    (IF(DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH ),DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) > 0,DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH ),DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) ,0) * 10),
                                    DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH ),DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) * 10
                                    ),
				DATEDIFF(temp1.previoustolastpaidon, DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) * $loanPenalty),
					IF((temp1.previoustolastpaidon IS NULL AND temp1.previoustolastDueDate IS NOT NULL),
							IF(DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH),temp1.previoustolastDueDate) > 30,
									(IF(DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH ),DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH))  > 0,DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH ),DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) ,0)) * $loanPenalty,
									DATEDIFF(DATE_ADD(temp1.previoustolastDueDate,INTERVAL +1 MONTH ),DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) * $loanPenalty
									),
						0)

			)


	)  as penaltyPreviousMonth

from
(
		select a.id,a.AccountNumber,
		(select DueDate from jos_xpremiums where Month(DueDate) = MONTH(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) and YEAR(DueDate) = Year(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) and accounts_id = a.id) lastmonthDueDate,
		(select PaidOn from jos_xpremiums where Month(DueDate) = MONTH(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) and YEAR(DueDate) = Year(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) and accounts_id = a.id) lastmonthpaidon,
		(select DueDate from jos_xpremiums where Month(DueDate) = MONTH(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -2 MONTH)) and YEAR(DueDate) = Year(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -2 MONTH)) and accounts_id = a.id) previoustolastDueDate,
		(select PaidOn from jos_xpremiums where Month(DueDate) = MONTH(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -2 MONTH)) and YEAR(DueDate) = Year(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -2 MONTH)) and accounts_id = a.id) previoustolastpaidon

		from `jos_xaccounts` as `a`
		join `jos_xpremiums` as `p` on `p`.`accounts_id`=`a`.`id`
		join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id`

		where `s`.`SchemeType`= 'Loan' and
		`a`.`ActiveStatus` = 1 and
		`a`.`branch_id` =". $b->id ." and
		Month(p.DueDate) = MONTH(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH)) AND
		YEAR(DueDate) = Year(DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH))

) as temp1

) as temp2 )


 as t on a.id = t.id
set a.CurrentInterest = t.Penalty";

executeQuery($q);



$schemes = new Scheme();
$schemes->where("SchemeType",ACCOUNT_TYPE_LOAN)->get();

$penaltyTotal = 0;
$creditAccounts = array();
$debitAccounts = array();

//calculating penalty amount for each scheme
foreach ($schemes as $sc) {
//    $q = Doctrine_Query::create()
//                    ->select(" SUM(a.CurrentInterest) as penalty")
//                    ->from("Accounts  a ")
//                    ->where(" a.branch_id = " . $b->id . " and a.schemes_id= '" . $sc->id . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' ");
    $CI = & get_instance();
    $penaltyTotal = $CI->db->query("select sum(a.CurrentInterest) as penalty from jos_xaccounts a where a.branch_id = " . $b->id . " and a.schemes_id= '" . $sc->id . "' and a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' ")->row()->penalty;
//    $penaltyTotal = $q->execute()->getFirst()->penalty;
    $creditAccounts = array($b->Code . SP . PENALTY_DUE_TO_LATE_PAYMENT_ON . $sc->Name => $penaltyTotal);
//    $accounts = Doctrine::getTable("Accounts")->findBySchemes_idAndBranch_id($sc->id, $b->id);
//    $q = Doctrine_Query::create()
//                    ->select("AccountNumber, CurrentInterest")
//                    ->from("Accounts")
//                    ->where("schemes_id = $sc->id and branch_id = $b->id and ActiveStatus=1 and created_at < '" . getNow("Y-m-d") . "' and CurrentInterest > 0");
//    $accounts = $q->execute();
    $accounts = new Account();
    $accounts->where("schemes_id",$sc->id)->where("branch_id",$b->id)->where("ActiveStatus",1)->where("created_at < ",getNow("Y-m-d"))->where("CurrentInterest > ",0)->get();
    if ($accounts->result_count() == 0)
        continue;
    $debitAccounts = array();
    foreach ($accounts as $ac) {
        $debitAccounts += array($ac->AccountNumber => $ac->{FIELD_TEMP_PENALTY});
    }
    $firstDayOfLastMonth = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH"));
    Transaction::doTransaction($debitAccounts, $creditAccounts, "Penalty deposited on Loan Account for ".date("F",strtotime($firstDayOfLastMonth)), TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT, Transaction::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
    $penaltyTotal = 0;
}


$q = "update `jos_xaccounts` as `a` join `jos_xpremiums` as `p` on `p`.`accounts_id`=`a`.`id` join `jos_xschemes` as `s` on `a`.`schemes_id`=`s`.`id` set `a`.`CurrentInterest` = 0 where `s`.`SchemeType`= '" . ACCOUNT_TYPE_LOAN . "' and `a`.`ActiveStatus`=1 and `a`.`created_at` < '" . getNow("Y-m-d") . "' and `a`.`branch_id`=" . $b->id;
executeQuery($q);
}
?>