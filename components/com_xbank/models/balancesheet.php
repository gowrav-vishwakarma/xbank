<?php
class BalanceSheet extends DataMapper {
    var $table='xbalance_sheet';
    var $has_many=array(
    'schemes'=>array(
            'class'=>'scheme',
            'join_self_as'=>'balance_sheet',
            'join_table'=>'jos_xschemes',
            'other_field'=>'balancesheet'
        )
        );

    function getClosingBalance($dateOn=null,$branch=null,$head=null){
        if($head==null) $head=$this->id;
        if($dateOn == null) $dateOn = getNow();

        throw new Exception("Error Processing Request", 1);
        

    	$dateOn = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateOn)) . " +1 DAY"));
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related('account/scheme/balancesheet','Head');
        $t->where("created_at <",$dateOn);
        $t->where_related("account/scheme/balancesheet","id",$head);
        if($branch!=null)
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->get();
        // if($head==3)
        // echo $t->check_last_query();

        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpeningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpeningBalanceCr');
        $a->include_related('scheme/balancesheet','Head');
        if($branch!=null)
            $a->where("branch_id",$branch);
        $a->where_related('scheme/balancesheet','id',$head);
        $a->group_start();
        $a->where("ActiveStatus","1");
        $a->or_where("affectsBalanceSheet","1");
        $a->group_end();
        $a->get();
        // if($head==3) echo $a->check_last_query();

        return arrayToObject(array(
                array(
                    'Head' => ($t->account_scheme_balancesheet_Head == '' ? $a->scheme_balancesheet_Head : $t->account_scheme_balancesheet_Head),
                    'Title' => 'Head',
                    'amountDr' => ($t->amountDr + $a->OpeningBalanceDr),
                    'amountCr' => ($t->amountCr + $a->OpeningBalanceCr)
                )
            )
            );

    }

    function getOpeningBalance($dateOn=null,$branch=null,$head=null){
        if($head==null) $head=$this->id;
        if($dateOn == null) $dateOn = getNow();

        // $dateOn = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateOn)) . " +1 DAY"));
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related('account/scheme/balancesheet','Head');
        $t->include_related('account/scheme/balancesheet','subtract_from');
        $t->where("created_at <",$dateOn);
        $t->where_related("account/scheme/balancesheet","id",$head);
        if($branch!='')
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->get();

        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpeningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpeningBalanceCr');
        // $a->include_related('scheme/balancesheet','id');
        if($branch!='')
            $a->where("branch_id",$branch);
        $a->where_related('scheme/balancesheet','id',$head);
        $a->get();

        return arrayToObject(array(
                array(
                    'Head' => ($t->account_scheme_balancesheet_Head == '' ? $a->scheme_balancesheet_Head : $t->account_scheme_balancesheet_Head),
                    'Title' => 'Head',
                    'amountDr' => ($t->amountDr + $a->OpeningBalanceDr),
                    'amountCr' => ($t->amountCr + $a->OpeningBalanceCr),
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from
                )
            )
            );

    }

    function getSchemeGroupViseClosingBalance($dateOn=null,$branch=null,$head=null){
        if($head==null) $head=$this->id;
        if($dateOn == null) $dateOn = getNow();

        $dateOn = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateOn)) . " +1 DAY"));
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related('account/scheme','SchemeGroup');
        $t->include_related('account/scheme/balancesheet','Head');
        $t->include_related('account/scheme/balancesheet','subtract_from');
        $t->where("created_at <",$dateOn);
        $t->where_related("account/scheme/balancesheet","id",$head);
        if($branch!=null)
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->group_by('account_scheme_SchemeGroup');
        $t->get();
        // if($head==3) echo $t->check_last_query();

        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpeningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpeningBalanceCr');
        $a->include_related('scheme','SchemeGroup');
        $a->include_related('scheme/balancesheet','Head');
        $a->include_related('scheme/balancesheet','subtract_from');
        if($branch!=null)
            $a->where("branch_id",$branch);
        $a->group_start();
        $a->where("ActiveStatus","1");
        $a->or_where("affectsBalanceSheet","1");
        $a->group_end();
        $a->where_related('scheme/balancesheet','id',$head);
        $a->group_by('scheme_SchemeGroup');
        $a->get();
        // if($head==3) echo $a->check_last_query();

        $arr=array();
            
        $schemegroup_found_in_tr=array();
        foreach($t as $tt){
            $dr=$tt->amountDr;// + $tt->account_OpeningBalanceDr;
            $cr=$tt->amountCr;// + $tt->account_OpeningBalanceCr;
            foreach($a as $aa){
                if($aa->scheme_SchemeGroup === $tt->account_scheme_SchemeGroup){
                    $dr=$tt->amountDr + $aa->OpeningBalanceDr;
                    $cr=$tt->amountCr + $aa->OpeningBalanceCr;
                    $schemegroup_found_in_tr[] = $aa->scheme_SchemeGroup;
                }
            }

            if($dr-$cr != 0)
            $arr[] = array(
                    'SchemeGroup' => ($tt->account_scheme_SchemeGroup),
                    'Title' => 'SchemeGroup',
                    'amountDr' => $dr,
                    'amountCr' => $cr,
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from

                );
        }

        foreach($a as $aa){
            if(array_search($aa->scheme_SchemeGroup, $schemegroup_found_in_tr)===false){
                if($aa->OpeningBalanceDr !=0 or $aa->OpeningBalanceCr!=0)
                    $arr[] = array(
                        'SchemeGroup' => ($aa->scheme_SchemeGroup),
                        'Title' => 'SchemeGroup',
                        'amountDr' => $aa->OpeningBalanceDr,
                        'amountCr' => $aa->OpeningBalanceCr,
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
            }
        }

        return arrayToObject($arr);

    }

    function getSchemeTypeViseClosingBalance($dateOn=null,$branch=null,$head=null){
        if($head==null) $head=$this->id;
        if($dateOn == null) $dateOn = getNow();

        $dateOn = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateOn)) . " +1 DAY"));
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related('account/scheme','SchemeType');
        $t->include_related('account/scheme/balancesheet','Head');
        $t->include_related('account/scheme/balancesheet','subtract_from');
        $t->where("created_at <",$dateOn);
        $t->where_related("account/scheme/balancesheet","id",$head);
        if($branch!=null)
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->group_by('account_scheme_SchemeType');
        $t->get();

        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpeningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpeningBalanceCr');
        $a->include_related('scheme','SchemeType');
        $a->include_related('scheme/balancesheet','Head');
        $a->include_related('scheme/balancesheet','subtract_from');
        if($branch!=null)
            $a->where("branch_id",$branch);
        $a->group_start();
        $a->where("ActiveStatus","1");
        $a->or_where("affectsBalanceSheet","1");
        $a->group_end();
        $a->where_related('scheme/balancesheet','id',$head);
        $a->group_by('scheme_SchemeType');
        $a->get();
        // $a->check_last_query();

        $arr=array();
        
        $schemetype_found_in_tr=array();
        foreach($t as $tt){
            foreach($a as $aa)
                if($aa->scheme_SchemeType === $tt->account_scheme_SchemeType){
                    $dr=$tt->amountDr + $aa->OpeningBalanceDr;
                    $cr=$tt->amountCr + $aa->OpeningBalanceCr;
                    $schemetype_found_in_tr[] = $aa->scheme_SchemeType;
                }

            if($dr-$cr != 0)
            $arr[] = array(
                    'SchemeType' => $tt->account_scheme_SchemeType,
                    'Title' => 'SchemeType',
                    'amountDr' => $dr,
                    'amountCr' => $cr,
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from
                );
        }

        foreach($a as $aa){
            if(array_search($aa->scheme_SchemeType, $schemetype_found_in_tr)===false){
                if($aa->OpeningBalanceDr !=0 and $aa->OpeningBalanceCr!=0)
                    $arr[] = array(
                        'SchemeType' => ($aa->scheme_SchemeType),
                        'Title' => 'SchemeType',
                        'amountDr' => $aa->OpeningBalanceDr,
                        'amountCr' => $aa->OpeningBalanceCr,
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
            }
        }

        return arrayToObject($arr);

    }

    function getSchemeNameViseClosingBalance($dateOn=null,$branch=null, $head=null){
        if($head==null) $head=$this->id;
        if($dateOn == null) $dateOn = getNow();

        $dateOn = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateOn)) . " +1 DAY"));
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related('account/scheme','Name');
        $t->include_related('account/scheme/balancesheet','Head');
        $t->include_related('account/scheme/balancesheet','subtract_from');
        $t->where("created_at <",$dateOn);
        $t->where_related("account/scheme/balancesheet","id",$head);
        if($branch!=null)
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->group_by('account_scheme_Name');
        $t->get();

        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpeningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpeningBalanceCr');
        $a->include_related('scheme','Name');
        $a->include_related('scheme/balancesheet','Head');
        $a->include_related('scheme/balancesheet','subtract_from');
        if($branch!=null)
            $a->where("branch_id",$branch);
        $a->group_start();
        $a->where("ActiveStatus","1");
        $a->or_where("affectsBalanceSheet","1");
        $a->group_end();
        $a->where_related('scheme/balancesheet','id',$head);
        $a->group_by('scheme_Name');
        $a->get();
        // $a->check_last_query();

        $arr=array();
        
        $schemename_found_in_tr=array();      
        foreach($t as $tt){
            foreach($a as $aa)
                if($aa->scheme_Name === $tt->account_scheme_Name){
                    $dr=$tt->amountDr + $aa->OpeningBalanceDr;
                    $cr=$tt->amountCr + $aa->OpeningBalanceCr;
                    $schemename_found_in_tr[] = $aa->scheme_Name;
                }
            if($dr-$cr != 0)
            $arr[] = array(
                    'SchemeName' => $tt->account_scheme_Name,
                    'Title' => 'SchemeName',
                    'amountDr' => $dr,
                    'amountCr' => $cr,
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from

                );
        }

        foreach($a as $aa){
            if(array_search($aa->scheme_Name, $schemename_found_in_tr)===false){
                if($aa->OpeningBalanceDr !=0 and $aa->OpeningBalanceCr!=0)
                    $arr[] = array(
                        'SchemeName' => ($aa->scheme_Name),
                        'Title' => 'SchemeName',
                        'amountDr' => $aa->OpeningBalanceDr,
                        'amountCr' => $aa->OpeningBalanceCr,
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
            }
        }


        return arrayToObject($arr);

    }

    function getAccountsViseClosingBalance($dateOn=null,$branch=null,$head=null){
        if($head==null) $head=$this->id;
        if($dateOn == null) $dateOn = getNow();

        $dateOn = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateOn)) . " +1 DAY"));
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related('account','AccountNumber');
        $t->include_related('account/scheme/balancesheet','Head');
        $t->include_related('account/scheme/balancesheet','subtract_from');
        $t->where("created_at <",$dateOn);
        $t->where_related("account/scheme/balancesheet","id",$head);
        if($branch!=null)
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->group_by('account_AccountNumber');
        $t->get();

        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpeningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpeningBalanceCr');
        $a->select('AccountNumber');
        $a->include_related('scheme/balancesheet','Head');
        $a->include_related('scheme/balancesheet','subtract_from');
        if($branch!=null)
            $a->where("branch_id",$branch);
        $a->group_start();
        $a->where("ActiveStatus","1");
        $a->or_where("affectsBalanceSheet","1");
        $a->group_end();
        $a->where_related('scheme/balancesheet','id',$head);
        $a->group_by('AccountNumber');
        $a->get();
        // $a->check_last_query();

        $arr=array();
        $accounts_found_in_tr=array();
        foreach($t as $tt){
            $dr=$cr=0;
            foreach($a as $aa)
                if($aa->AccountNumber === $tt->account_AccountNumber){
                    $dr=$tt->amountDr + $aa->OpeningBalanceDr;
                    $cr=$tt->amountCr + $aa->OpeningBalanceCr;
                    $accounts_found_in_tr[] = $aa->AccountNumber;
                }
            if($dr-$cr != 0)
            $arr[] = array(
                    'AccountNumber' => $tt->account_AccountNumber,
                    'Title' => 'AccountNumber',
                    'amountDr' => $dr,
                    'amountCr' => $cr,
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from

                );
        }

        foreach($a as $aa){
            if(array_search($aa->AccountNumber, $accounts_found_in_tr)===false){
                if($aa->OpeningBalanceDr !=0 and $aa->OpeningBalanceCr!=0)
                    $arr[] = array(
                        'AccountNumber' => ($aa->AccountNumber),
                        'Title' => 'AccountNumber',
                        'amountDr' => $aa->OpeningBalanceDr,
                        'amountCr' => $aa->OpeningBalanceCr,
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
            }
        }


        return arrayToObject($arr);

    }

    function getAllBalanceSheetHeads($dateFrom,$dateTo,$branch=''){
        $dateTo = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateTo)) . " +1 DAY"));
        // Transaction Sum of all heads
        $t=new Transaction();
        $t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        $t->include_related("account/scheme/balancesheet","Head");
        $t->where("created_at >=",$dateFrom);
        $t->where("created_at <",$dateTo);
        if($branch!='')
            $t->where("branch_id",$branch);
        $t->group_start();
        $t->where_related("account","ActiveStatus","1");
        $t->or_where_related("account","affectsBalanceSheet","1");
        $t->group_end();
        $t->group_by('account_scheme_balancesheet_Head');
        $t->get();

        // Opening Balances of heads
        $a=new Account();
        $a->select('SUM(OpeningBalanceDr) as OpenningBalanceDr');
        $a->select('SUM(OpeningBalanceCr) as OpenningBalanceCr');
        $a->include_related('scheme/balancesheet','Head');
        if($branch!='')
            $a->where("branch_id",$branch);
        $a->group_by('scheme_balancesheet_Head');
        $a->get();


        foreach($t as &$tt){
            foreach($a as $aa){
                if($tt->account_scheme_balancesheet_Head == $aa->scheme_balancesheet_Head){
                    $tt->amountDr += $aa->OpeningBalanceDr;
                    $tt->amountCr += $aa->OpeningBalanceCr;
                }
            }
        }

        return $t;
    }

    function getHeadTotal($head,$dateFrom,$dateTo,$branch=''){
        $dateTo = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateTo)) . " +1 DAY"));
    	$t=new Transaction();
    	$t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
    	$t->include_related("account","OpeningBalanceDr");
    	$t->include_related("account","OpeningBalanceCr");
    	$t->where("created_at >=",$dateFrom);
    	$t->where("created_at <",$dateTo);
    	$t->where_related("account/scheme/balancesheet","id",$head);
    	$t->where("branch_id",$branch);
    	$t->group_start();
    	$t->where_related("account","ActiveStatus","1");
    	$t->or_where_related("account","affectsBalanceSheet","1");
    	$t->group_end();
    	
    	$t->group_by('accounts_id');
    	$t->get();
    	
//    	echo $t->check_last_query();
    	$total = 0;
    	foreach($t as $tr){
    		$total += ($tr->amountDr - $tr->amountCr);
    	}
    	
    	$CI = & get_instance();
	$trans = $CI->db->query("select sum(t.amountDr) as Dr,sum(t.amountCr) as Cr from jos_xtransactions t left join jos_xaccounts a on a.id=t.accounts_id join jos_xschemes s on s.id=a.schemes_id join jos_xbalance_sheet b on s.balance_sheet_id = b.id where t.created_at < '" . $dateFrom . "' and s.balance_sheet_id = $head and t.branch_id=$branch")->row();
       $tot =  $trans->Dr - $trans->Cr;    	
    	
    	
    	
    	$a=new Account();
    	$a->select("SUM(OpeningBalanceDr) as OpeningBalanceDr, SUM(OpeningBalanceCr) as OpeningBalanceCr");
//    	$a->include_related("transaction","SUM(amountDr)","amountDr");
//    	$a->include_related("transaction","SUM(amountCr)","amountCr");
    	$a->where("created_at <",$dateFrom);
//    	$a->where("created_at <",$dateTo);
    	$a->where_related("scheme/balancesheet","id",$head);
    	$a->where("branch_id",$branch);
    	$a->group_start();
    	$a->where("ActiveStatus","1");
    	$a->or_where("affectsBalanceSheet","1");
    	$a->group_end();
    	
    	$a->get();
//    	echo $a->check_last_query();
    	$acctotal = 0;
    	foreach($a as $ac){
    		$acctotal += ($ac->OpeningBalanceDr - $ac->OpeningBalanceCr) ;
    	}
    	
    	
    	return $total + $acctotal + $tot ;

    
/*    
        $scheme = new Scheme();
        $scheme->where("balance_sheet_id",$head)->get();
        $t = 0;
        
        foreach ($scheme as $s){
            $t += $s->getSchemeTotal($s->id,$dateFrom,$dateTo,$branch);
        }
        return $t;
*/       
    }
}

?>
