<?php

class Transaction extends DataMapper {

    var $table = "xtransactions";
    var $has_one = array(
        'bystaff' => array(
            'class' => 'staff',
            'join_other_as' => 'staff',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'transactions'
        ),
        'branch' => array(
            'class' => 'branch',
            'join_other_as' => 'branch',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'transactions'
        ),
        'account' => array(
            'class' => 'account',
            'join_other_as' => 'accounts',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'transactions'
        ),
        'transaction_type' => array(
            'class' => 'transaction_type',
            'join_other_as' => 'transaction_type',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'transactions'
        )
    );

   public static function getNewVoucherNumber($branch_id='') {
        $CI = & get_instance();
        if(!$branch_id)
                $branch_id = Branch::getCurrentBranch()->id;
        $q = $CI->db->query("select MAX(t.voucher_no) AS maxVoucher from jos_xtransactions t where t.branch_id= " . $branch_id)->row();
        $voucherNo = $q->maxVoucher;
        $voucherNo++;
        return $voucherNo;
    }



    public static function getNewDisplayVoucherNumber($created_at,$currentbranchid) {
        if(date("m", strtotime($created_at)) <= 3){
            $year = date("Y", strtotime($created_at)) - 1;
            $startDate = $year."-04-01";
            $endDate = date("Y", strtotime($created_at))."-03-31";
        }
        if(date("m", strtotime($created_at)) > 3){
            $year = date("Y", strtotime($created_at)) + 1;
            $startDate = date("Y", strtotime($created_at))."-04-01";
            $endDate = $year."-03-31";
        }
        $CI = & get_instance();
        $voucherNo = $CI->db->query("select MAX(t.display_voucher_no) AS maxDisplayVoucher from jos_xtransactions t where t.created_at >= '$startDate' and t.created_at <= '$endDate' and t.display_voucher_no is not null and t.display_voucher_no <> 0 and t.branch_id= " . $currentbranchid)->row()->maxDisplayVoucher;
//        $voucherNo = $q->maxDisplayVoucher;
        $voucherNo++;
        return $voucherNo;
    }

    public static function doTransaction($DRs, $CRs, $remarks='', $type='', $voucherNo='', $transactionDate=false, $branchid=false, $onlyTRansactionSaving=false) 
                {
        if (count($DRs) > 1 and count($CRs) > 1) {
            echo "Some thing is wrong ..looks like wrong entry.. many to many posting";
            throw new Exception("array to array transaction is not allowed");
        }

// 			get new voucher bnumber if ommited
//        if(!DO_TRANSACTION)
//            throw new Exception("Transactions NOT allowed for the moment.");

        if (count($DRs) > 1)
            Transaction::doManyToOneTransaction_alt($DRs, $CRs, $remarks, $type, $voucherNo, $transactionDate, $branchid, $onlyTRansactionSaving);
        else
            Transaction::doOneToManyTransaction_alt($DRs, $CRs, $remarks, $type, $voucherNo, $transactionDate, $branchid, $onlyTRansactionSaving);
    }

    public static function doOneToManyTransaction_alt($DRs, $CRs, $remarks='', $type='', $voucherNo='', $transactionDate, $branchid, $onlyTRansactionSaving=false) {
        $CI = & get_instance();
        $DRAccountNumber = array_keys($DRs);
        if (!is_array($DRs) or !is_array($DRAccountNumber)) {
            $xxx = 10;
        }
        if (count($DRAccountNumber) == 0) {
            $xxx = 10;
        }
        $DRAccountNumber = $DRAccountNumber[0];
        $DRAmount = array_values($DRs);
        $DRAmount = $DRAmount[0];
        if ($branchid != false)
            $currentbranchid = $branchid;
        else
            $currentbranchid = Branch::getCurrentBranch()->id;

        $DRAccount = $CI->db->query("select a.id as aid ,s.id as sid ,s.SchemeType as SchemeType from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where (a.AccountNumber= '" . $DRAccountNumber . "' or a.AccountNumber ='" . Branch::getCurrentBranch()->Code . SP . $DRAccountNumber . "') and a.branch_id = " . $currentbranchid)->row();
        $accid = $DRAccount->aid;

        // Save transaction only when DRAmount > 0
        if ($DRAmount > 0) {

            $reference_account = ((is_array($voucherNo)) ? ($voucherNo['referanceAccount'] == Null ? 'NULL' : $voucherNo['referanceAccount']) : 'NULL');
//            $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction= '" . $type . "'")->row();
            $tT = new Transaction_type();
            $tT->where("Transaction",$type)->get();

////                      TODO- remove this runtime transaction creation from here
            if (!$tT->result_count()) {
                $query = $CI->db->query("insert into jos_xtransaction_type (Transaction,FromAC,ToAC) values('$type','xxx','yyy')");
                $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction= '" . $type . "'")->row();
            }
            if ($transactionDate !== false) {
                $created_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                $updated_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
            } else {
                $created_at = getNow();
                $updated_at = getNow();
            }

            $display_voucher_no = Transaction::getNewDisplayVoucherNumber($created_at,$currentbranchid);

            $voucher_no = (is_array($voucherNo)) ? $voucherNo['voucherNo'] : $voucherNo;
            $staff_id = Staff::getCurrentStaff()->id;
            $remarks = str_replace("'", " ", $remarks);
            $query = $CI->db->query("insert into jos_xtransactions
                                (accounts_id,transaction_type_id,staff_id,voucher_no,Narration,
                                amountDr,updated_at,created_at,branch_id,reference_account,display_voucher_no,side,accounts_in_side)
                                values($accid,$tT->id,$staff_id,$voucher_no,'$remarks',
                                $DRAmount,'$updated_at','$created_at',$currentbranchid,$reference_account,$display_voucher_no,'DR',1)");

            if (!$onlyTRansactionSaving) {
                include(xBANKSCHEMEPATH . "/" . strtolower($DRAccount->SchemeType) . "/" . strtolower($DRAccount->SchemeType) . "accountbeforedebited.php");
//                $query = $CI->db->query("update jos_xaccounts set CurrentBalanceDr = CurrentBalanceDr + $DRAmount where id = $accid");
                $ac = new Account($accid);
                $ac->CurrentBalanceDr = $ac->CurrentBalanceDr + $DRAmount;
                $ac->save();
                include(xBANKSCHEMEPATH . "/" . strtolower($DRAccount->SchemeType) . "/" . strtolower($DRAccount->SchemeType) . "accountafterdebited.php");
            }
        }
	
	$CRAmountTotal=0;
        foreach ($CRs as $account => $amount) {
            if ($amount > 0) {
            $CRAmountTotal += $amount;
                $CRAccount = $CI->db->query("select a.id as aid,s.id as sid,s.SchemeType as SchemeType from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where (a.AccountNumber='" . $account . "' or a.AccountNumber='" . Branch::getCurrentBranch()->Code . SP . $account . "') and a.branch_id = " . $currentbranchid)->row();
                $accid = $CRAccount->aid;
                $reference_account = ((is_array($voucherNo)) ? ($voucherNo['referanceAccount'] == Null ? 'NULL' : $voucherNo['referanceAccount']) : 'NULL');
//                $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction ='" . $type."'")->row();
                $tT = new Transaction_type();
                $tT->where("Transaction",$type)->get();
////                      TODO- remove this runtime transaction creation from here
                if (!$tT->result_count()) {
                    $query = $CI->db->query("insert into jos_xtransaction_type (Transaction,FromAC,ToAC) values('$type','xxx','yyy')");
                    $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction ='" .$type."'")->row();
                }
                if ($transactionDate !== false) {
                    $created_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                    $updated_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                } else {
                    $created_at = getNow();
                    $updated_at = getNow();
                }
                $voucher_no = (is_array($voucherNo)) ? $voucherNo['voucherNo'] : $voucherNo;
                $staff_id = Staff::getCurrentStaff()->id;
                $accounts_in_side=count($CRs);
                $query = $CI->db->query("insert into jos_xtransactions
                                (accounts_id,transaction_type_id,staff_id,voucher_no,Narration,
                                amountCr,updated_at,created_at,branch_id,reference_account,display_voucher_no,side,accounts_in_side)
                                values($accid,$tT->id,$staff_id,$voucher_no,'$remarks',
                                $amount,'$updated_at','$created_at',$currentbranchid,$reference_account,$display_voucher_no,'CR',$accounts_in_side)");

                if (!$onlyTRansactionSaving) {
                    include(xBANKSCHEMEPATH . "/" . strtolower($CRAccount->SchemeType) . "/" . strtolower($CRAccount->SchemeType) . "accountbeforecredited.php");
//                    $query =  $CI->db->query("update jos_xaccounts set CurrentBalanceCr = CurrentBalanceCr + $amount where id = $accid");
                    $ac = new Account($accid);
                    $ac->CurrentBalanceCr = $ac->CurrentBalanceCr + $amount;
                    $ac->save();
                    include(xBANKSCHEMEPATH . "/" . strtolower($CRAccount->SchemeType) . "/" . strtolower($CRAccount->SchemeType) . "accountaftercredited.php");
                }
            }
        }
        if($DRAmount != $CRAmountTotal) throw new Exception("Transactions are of mismached amount:: DRs " . serialize($DRs). " CRs " . serialize($CRs));
    }

    public static function doManyToOneTransaction_alt($DRs, $CRs, $remarks='', $type='', $voucherNo='', $transactionDate, $branchid, $onlyTRansactionSaving=false) {
        $CI = & get_instance();
        $CRAccountNumber = array_keys($CRs);
        $CRAccountNumber = $CRAccountNumber[0];
        $CRAmount = array_values($CRs);
        $CRAmount = $CRAmount[0];

        if ($branchid != false)
            $currentbranchid = $branchid;
        else
            $currentbranchid = Branch::getCurrentBranch()->id;

        $CRAccount = Account::getAccountForCurrentBranch($CRAccountNumber);
        if ($CRAmount > 0) {

            $CRAccount = $CI->db->query("select a.id as aid,s.id as sid, s.SchemeType as SchemeType from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where (a.AccountNumber='" . $CRAccountNumber . "' or a.AccountNumber='" . Branch::getCurrentBranch()->Code . SP . $CRAccountNumber . "') and a.branch_id=" . $currentbranchid)->row();
            $accid = $CRAccount->aid;

            $Branch = Branch::getCurrentBranch()->id;
            $reference_account = ((is_array($voucherNo)) ? ($voucherNo['referanceAccount'] == Null ? 'NULL' : $voucherNo['referanceAccount']) : 'NULL');
//            $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction ='".$type."'")->row();
            $tT = new Transaction_type();
            $tT->where("Transaction",$type)->get();
////                      TODO- remove this runtime transaction creation from here
            if (!$tT->result_count()) {
                $query = "insert into jos_xtransaction_type (Transaction,FromAC,ToAC) values('$type','xxx','yyy')";
                executeQuery($query);
                $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction ='" . $type."'")->row();
            }
//
            if ($transactionDate !== false) {
                $created_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                $updated_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
            } else {
                $created_at = getNow();
                $updated_at = getNow();
            }
            $display_voucher_no = Transaction::getNewDisplayVoucherNumber($created_at,$currentbranchid);
            $voucher_no = (is_array($voucherNo)) ? $voucherNo['voucherNo'] : $voucherNo;
            $staff_id = Staff::getCurrentStaff()->id;
            $remarks = str_replace("'", " ", $remarks);
            $query = "insert into jos_xtransactions
                                (accounts_id,transaction_type_id,staff_id,voucher_no,Narration,
                                amountCr,updated_at,created_at,branch_id,reference_account,display_voucher_no,side,accounts_in_side)
                                values($accid,$tT->id,$staff_id,$voucher_no,'$remarks',
                                $CRAmount,'$updated_at','$created_at',$currentbranchid,$reference_account,$display_voucher_no,'CR',1)";
//            executeQuery($query);

            if (!$onlyTRansactionSaving) {
                include(xBANKSCHEMEPATH . "/" . strtolower($CRAccount->SchemeType) . "/" . strtolower($CRAccount->SchemeType) . "accountbeforecredited.php");
//                $query = "update jos_xaccounts set CurrentBalanceCr = CurrentBalanceCr + $CRAmount where id = $accid";
                $ac = new Account($accid);
                $ac->CurrentBalanceCr = $ac->CurrentBalanceCr + $CRAmount;
                $ac->save();
                executeQuery($query);
                include(xBANKSCHEMEPATH . "/" . strtolower($CRAccount->SchemeType) . "/" . strtolower($CRAccount->SchemeType) . "accountaftercredited.php");
            }
        }
        
        $DRAmountTotal = 0;
        foreach ($DRs as $account => $amount) {
            if ($amount > 0) {
            $DRAmountTotal += $amount;
                $DRAccount = $CI->db->query("select a.id as aid,s.id as sid, s.SchemeType as SchemeType from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where(a.AccountNumber='" . $account . "' or a.AccountNumber='" . Branch::getCurrentBranch()->Code . SP . $account . "')and a.branch_id=" . $currentbranchid)->row();
                $accid = $DRAccount->aid;

                // Save transaction only when DRAmount > 0
//                $Branch = Branch::getCurrentBranch()->id;
                $reference_account = ((is_array($voucherNo)) ? ($voucherNo['referanceAccount'] == Null ? 'NULL' : $voucherNo['referanceAccount']) : 'NULL');
//                $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction ='".$type."'")->row();
                $tT = new Transaction_type();
                $tT->where("Transaction",$type)->get();
////                      TODO- remove this runtime transaction creation from here
                if (!$tT->result_count()) {
                    $query = "insert into jos_xtransaction_type (Transaction,FromAC,ToAC) values('$type','xxx','yyy')";
                    executeQuery($query);
                    $tT = $CI->db->query("select * from jos_xtransaction_type where Transaction ='" . $type."'")->row();
                }
                if ($transactionDate !== false) {
                    $created_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                    $updated_at = $transactionDate; //date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
                } else {
                    $created_at = getNow();
                    $updated_at = getNow();
                }
                $voucher_no = (is_array($voucherNo)) ? $voucherNo['voucherNo'] : $voucherNo;
                $staff_id = Staff::getCurrentStaff()->id;
                $accounts_in_side=count($DRs);
                $query = "insert into jos_xtransactions
                                (accounts_id,transaction_type_id,staff_id,voucher_no,Narration,
                                amountDr,updated_at,created_at,branch_id,reference_account,display_voucher_no,side,accounts_in_side)
                                values($accid,$tT->id,$staff_id,$voucher_no,'$remarks',
                                $amount,'$updated_at','$created_at',$currentbranchid,$reference_account,$display_voucher_no,'DR',$accounts_in_side)";
//                executeQuery($query);

                if (!$onlyTRansactionSaving) {
                    include(xBANKSCHEMEPATH . "/" . strtolower($DRAccount->SchemeType) . "/" . strtolower($DRAccount->SchemeType) . "accountbeforedebited.php");
//                    $query = "update jos_xaccounts set CurrentBalanceDr = CurrentBalanceDr + $amount where id = $accid";
                    $ac = new Account($accid);
                    $ac->CurrentBalanceDr = $ac->CurrentBalanceDr + $amount;
                    $ac->save();
                    executeQuery($query);
                    include(xBANKSCHEMEPATH . "/" . strtolower($DRAccount->SchemeType) . "/" . strtolower($DRAccount->SchemeType) . "accountafterdebited.php");
                }
            }
        }
        if($CRAmount != $DRAmountTotal) throw new Exception("Transactions are of mismached amount:: DRs " . serialize($DRs). " CRs " . serialize($CRs));
    }

}

?>
