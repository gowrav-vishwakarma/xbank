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
