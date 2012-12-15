<?php

class Account extends DataMapper {

    var $table = 'xaccounts';
    var $has_one = array(
        'branch' => array(
            'class' => 'branch',
            'join_other_as' => 'branch',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'accounts'
        ),
        'member' => array(
            'class' => 'member',
            'join_other_as' => 'member',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'accounts'
        ),
        'scheme' => array(
            'class' => 'scheme',
            'join_other_as' => 'schemes',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'accounts'
        ),
        'opennedbystaff' => array(
            'class' => 'staff',
            'join_other_as' => 'staff',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'accountsopenned'
        ),
        'agent' => array(
            'class' => 'agent',
            'join_other_as' => 'agents',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'accountsopenned'
        ),
        'dealer' => array(
            'class' => 'dealer',
            'join_other_as' => 'dealer',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'accounts'
        )
    );
    var $has_many = array(
        'premiums' => array(
            'class' => 'premium',
            'join_self_as' => 'accounts',
            'join_table' => 'jos_xpremiums',
            'other_field' => 'account'
        ),
        'transactions' => array(
            'class' => 'transaction',
            'join_self_as' => 'accounts',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'account'
        ),
        'documents' => array(
            'class' => 'document',
            'join_self_as' => 'accounts',
            'join_other_as' => 'documents',
            'join_table' => 'jos_xdocuments_submitted',
            'other_field' => 'submited_in_accounts'
        )
    );
    
     var $validation = array(
        'ActualCurrentBalance' => array(
            'get_rules' => array('actualcurrentbalance')
        ),
        'PaneltyDUE' => array(
            'get_rules' => array('duePaneltyCalculate')
        )
    );

    function _actualcurrentbalance($field) {
    	if($this->CurrentBalanceCr - $this->CurrentBalanceDr > 0 )
	        $this->{$field} = ($this->CurrentBalanceCr - $this->CurrentBalanceDr) . " CR";
	else
		$this->{$field} = ($this->CurrentBalanceDr - $this->CurrentBalanceCr) . " DR";
    }

    function _duePaneltyCalculate($field) {
        // $loanPenalty = 10;
        $thismonth = getNow("m");
        $lastmonth = date("m", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH"));
        $closingdate = getNow("Y-m-d");
        $lastmonthlastdate = date("Y-m-t", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH"));
        $firstdateofthismonth = getNow("Y-m-01");
        $penaltyQ = "
                    select accounts_id, SUM(Penalty) Penalty from (

                    
                    /* PREMIUM DUE IN THIS MONTH - NOT PAID */
                    select
                    'A' as nm,p.id,(DATEDIFF('$closingdate',p.DueDate) + 1) * 10 as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $thismonth AND
                    p.PaidOn is NULL and

                    DATEDIFF('$closingdate',p.DueDate) <= 31 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    AND p.accounts_id = $this->id

                    UNION


                   
                    /* PREMIUM DUE IN THIS MONTH - LATE PAID IN THIS MONTH */
                    select
                    'B' as nm,p.id,IF((DATEDIFF(p.PaidOn,p.DueDate)) * 10 > 300,300,(DATEDIFF(p.PaidOn,p.DueDate)) * 10) as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $thismonth AND
                    p.PaidOn > p.DueDate AND
                    DATEDIFF('$closingdate',p.DueDate) <= 31 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    AND p.accounts_id = $this->id

                    UNION

                    /* PREMIUM DUE IN LAST MONTH - STILL NOT PAID */
                    select
                    'C' as nm,p.id,if(DATEDIFF('$closingdate',p.Duedate)>=30,300,(DATEDIFF('$closingdate',p.DueDate)+1)*10) as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $lastmonth AND
                    p.PaidOn is NULL AND
                    DATEDIFF('$closingdate',p.DueDate) <= 62 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    AND p.accounts_id = $this->id

                    UNION

                    /* PREMIUM DUE IN LAST MONTH - PAID IN THIS(NEXT) MONTH */
                    select
                    'D' as nm,p.id, IF(DAY(p.PaidOn) >= DAY(p.DueDate), (300 - (DATEDIFF('$lastmonthlastdate',p.DueDate) * 10)), DATEDIFF(p.PaidOn,'$firstdateofthismonth') * 10) as Penalty, p.accounts_id,MONTH(p.DueDate), $thismonth, p.PaidOn, p.DueDate
                    from jos_xpremiums p
                    where
                    MONTH(p.DueDate) = $lastmonth AND
                    MONTH(p.PaidOn) = $thismonth AND
                    DATEDIFF('$closingdate',p.DueDate) <= 62 AND
                    DATEDIFF('$closingdate',p.DueDate) >=0
                    AND p.accounts_id = $this->id
                    )
                    as t
                    GROUP  BY accounts_id
";
        $result=$this->db->query($penaltyQ);
        if($result->num_rows() > 0)
            $this->{$field} = $this->db->query($penaltyQ)->row()->Penalty;
        else
            $this->{$field} = 0;



    }

    public static function getAccountForCurrentBranch($ac, $addCodeInfront=true) {
        if ($addCodeInfront) {
            $acc = Branch::getCurrentBranch()->Code . SP . $ac;
            $returnAccount = new Account();
            $returnAccount->where("branch_id", Branch::getCurrentBranch()->id);
            $returnAccount->where("AccountNumber", $acc)->get();
            if ($returnAccount->result_count() == 0) {
//				echo $acc . " Not found searching " . $AccountNumber . "<br>";
                $acc = $ac;
//				$returnAccount=Doctrine::getTable("Accounts")->findOneByBranch_idAndAccountnumber(Branch::getCurrentBranch()->id,$acc);
                $returnAccount = new Account();
                $returnAccount->where("branch_id", Branch::getCurrentBranch()->id);
                $returnAccount->where("AccountNumber", $acc)->get();
            }
        } else {
//			$returnAccount=Doctrine::getTable("Accounts")->findOneByBranch_idAndAccountnumber(Branch::getCurrentBranch()->id,$AccountNumber);
//			print_r($returnAccount);
            $returnAccount = new Account();
            $returnAccount->where("branch_id", Branch::getCurrentBranch()->id);
            $returnAccount->where("AccountNumber", $ac)->get();
        }
        return $returnAccount;
    }

    function getOpeningBalance($date, $ac=null,$side='both') {
        if ($ac != null) {
            $this->where("id", $ac)->get();
        }
        $CI = & get_instance();
        $trans = $CI->db->query("select sum(amountDr) as Dr,sum(amountCr) as Cr from jos_xtransactions where accounts_id = $this->id and created_at < '" . $date . "'")->row();
        // echo "select sum(amountDr) as Dr,sum(amountCr) as Cr from jos_xtransactions where accounts_id = $this->id and created_at < '" . $date . "'";
        if($side=='both'){
            return array(
                        "DR" =>  $trans->Dr + ( $this->OpeningBalanceDr),
                        "CR" => ($this->OpeningBalanceCr) + $trans->Cr
                    );
        }

        if(strtolower($side)=='dr') return (($trans->Dr + ($this->OpeningBalanceDr))- (($this->OpeningBalanceCr) + $trans->Cr));
        if(strtolower($side)=='cr') return (($this->OpeningBalanceCr + $trans->Cr) - $trans->Dr + ( $this->OpeningBalanceDr));
//         return array(
//        "DR" =>  $trans->Dr ,
//        "CR" =>  $trans->Cr
//        );
    }

    function getAccountTotal($dateFrom,$dateTo,$ac=''){
        if ($ac != '') {
            $this->where("id", $ac)->get();
        }
        $CI = & get_instance();
        $trans = $CI->db->query("select sum(amountCr) as Cr, sum(amountDr)as Dr from jos_xtransactions where accounts_id = $this->id and created_at between '" . $dateFrom . "' and DATE_ADD('" . $dateTo . "',INTERVAL +1 DAY)")->row();
        $openingBalance = $this->getOpeningBalance($dateFrom, $ac);
        return (($openingBalance['DR'] - $openingBalance['CR'])+($trans->Dr - $trans->Cr));

    }

    function getTransactionSUM($from_date,$to_date,$side='both'){
        $tr=$this->transactions;
        $tr->where('created_at >=',$from_date);
        $tr->where('created_at <',date('Y-m-d',strtotime(date("Y-m-d", strtotime($to_date)) . " +1 day")));
        $tr->select_func('sum','[AmountCr]','CRSUM');
        $tr->select_func('sum','[AmountDr]','DRSUM');
        $tr->get();
        // $tr->check_last_query();

        if($side=='both'){
            return array(
                'DR'=>$tr->DRSUM,
                'CR'=>$tr->CRSUM
                );
        }

        if(strtolower($side)=='dr') return $tr->DRSUM;
        if(strtolower($side)=='cr') return $tr->CRSUM;

    }

}

?>