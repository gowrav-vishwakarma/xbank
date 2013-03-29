<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// HID SCHEME :: TODO- SEPERATE IT


// TODO :: UNCOMMENT AFTER HALFYEARLY CLOSING OF 01-10-2011

        $schemes = new Scheme();
        $schemes->where("SchemeType",ACCOUNT_TYPE_FIXED);
        $schemes->where("InterestToAnotherAccount",1);
        $schemes->where("InterestToAnotherAccountPercent <>",0)->get();

        foreach ($schemes as $sc) {

//            $q = Doctrine_Query::create()
//                            ->select("accounts.* ")
//                            ->from("Accounts as accounts")
//                            ->where("accounts.schemes_id = $sc->id AND accounts.ActiveStatus =1 and accounts.MaturedStatus=0  and accounts.created_at < '" . getNow("Y-m-d") . "' and accounts.branch_id = " . $b->id);
//            $accounts = $q->execute();
//
//            if ($accounts->count() == 0)
//                continue;

            $accounts = $CI->db->query("select a.* from jos_xaccounts a where a.schemes_id = $sc->id and a.ActiveStatus =1 and a.MaturedStatus=0 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id = " . $b->id);

            if ($accounts->num_rows() == 0)
                continue;



            foreach ($accounts->result() as $acc) {
//                $interestToAcc = "";
                if ($acc->InterestToAccount != ""){
//                    $interestToAcc = Doctrine::getTable('Accounts')->findOneByIdAndActivestatus($acc->InterestToAccount, 1);
                    $interestToAcc = new Account($acc->InterestToAccount);
                    // $interestToAcc->get();
                    
//                     $interestToAcc->CurrentInterest= $interestToAcc->CurrentInterest + $acc->CurrentBalanceCr *
                if ($interestToAcc->result_count()) {
//                     $query="update accounts as a join accounts as b on a.InterestToAccount=b.id set a.CurrentBalanceCr= a.CurrentInterest + a.CurrentBalanceCr , a.CurrentBalanceDr = a.CurrentBalanceDr + ($sc->InterestToAnotherAccountPercent * a.RdAmount)/100, a.LastCurrentInterestUpdatedAt = '".getNow("Y-m-d")."', b.CurrentBalanceCr = b.CurrentBalanceCr + ($sc->InterestToAnotherAccountPercent * a.RdAmount)/100 where a.id=$acc->id and b.id=$interestToAcc->id and a.branch_id = ".$b->id;
//                     executeQuery($query);
//                        $days=my_date_diff(getNow("Y-m-d"),$acc->LastCurrentInterestUpdatedAt);
                    
                    $interest_amount = round(($acc->CurrentBalanceCr - $acc->CurrentBalanceDr) * $sc->Interest / 1200 * 6,0);
                    // echo "working under " . $sc->Name . " scheme's ". $acc->AccountNumber . " account with its balance " . $acc->CurrentBalanceCr.  " and giving interest @ " . $sc->Interest . " = ". $interest_amount . " <br/>";
                    $creditAccount = array($acc->AccountNumber => $interest_amount);

                    $debitAccount = array(
                        $b->Code . SP . INTEREST_PAID_ON . $sc->Name => $interest_amount
                    );

                    $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $acc->id);

                    Transaction::doTransaction($debitAccount, $creditAccount, "HID Interest posting in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_HID_ACCOUNT, $voucherNo, date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));


                    
                    $interest = ($sc->InterestToAnotherAccountPercent * $acc->RdAmount) / 100;
                    $creditAccount = array($interestToAcc->AccountNumber => $interest);

                    $debitAccount = array(
                        $acc->AccountNumber => $interest
                    );

                    $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $acc->id);
                    Transaction::doTransaction($debitAccount, $creditAccount, "HID Amount Transfer in $acc->AccountNumber", TRA_INTEREST_POSTING_IN_HID_ACCOUNT, $voucherNo, date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));




                    $q = "update jos_xaccounts set CurrentInterest = 0 where id=$acc->id and branch_id = " . $b->id;
                    executeQuery($q);
                }
                }
            }
        }
 
?>
