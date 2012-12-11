<?php
class Scheme extends DataMapper {
    var $table='xschemes';
    var $has_one=array(
        'branch'=>array(
            'class'=>'branch',
            'join_other_as'=>'branch',
            'join_table'=>'jos_xschemes',
            'other_field'=>'schemes'
            ),
        'balancesheet'=>array(
            'class'=>'balance_sheet',
            'join_other_as'=>'balance_sheet',
            'join_table'=>'jos_xschemes',
            'other_field'=>'schemes'
            )
        );
    var $has_many = array(
      'accounts'=>array(
            'class'=>'account',
            'join_self_as'=>'schemes',
            'join_table'=>'jos_xaccounts',
            'other_field'=>'scheme'
          )
        );
    public static function getScheme($id){
        $s=new Scheme();
        $s->where("id",$id)->get();
        return $s;
           // return Doctrine::getTable("Schemes")->find($id);
        }

    function getSchemeTotal($scheme,$dateFrom,$dateTo,$branch=''){
/*         if ($scheme != '') {
            $this->where("id", $scheme)->get();
        }
        $acc = new Account();
        $acc->where("schemes_id",$scheme);
        $acc->group_start()
        ->where("ActiveStatus",1)
        ->or_where("affectsBalanceSheet",1)
        ->group_end();
        if($branch)
            $acc->where("branch_id", $branch);
        $acc->get();
//        $acc->check_last_query();

        $total = 0;
        foreach($acc as $a){
            $total += $a->getAccountTotal($dateFrom, $dateTo,$a->id);
        }
        return $total;
*/

	$dateTo = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateTo)) . " +1 DAY"));
    	$t=new Transaction();
    	$t->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
    	$t->include_related("account","OpeningBalanceDr");
    	$t->include_related("account","OpeningBalanceCr");
    	$t->where("created_at >=",$dateFrom);
    	$t->where("created_at <",$dateTo);
    	$t->where_related("account/scheme","id",$scheme);
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
	$trans = $CI->db->query("select sum(t.amountDr) as Dr,sum(t.amountCr) as Cr from jos_xtransactions t left join jos_xaccounts a on a.id=t.accounts_id join jos_xschemes s on s.id=a.schemes_id  where t.created_at < '" . $dateFrom . "' and s.id = $scheme and t.branch_id=$branch")->row();
       $tot =  $trans->Dr - $trans->Cr;    	
    	
    	
    	
    	$a=new Account();
    	$a->select("SUM(OpeningBalanceDr) as OpeningBalanceDr, SUM(OpeningBalanceCr) as OpeningBalanceCr");
//    	$a->include_related("transaction","SUM(amountDr)","amountDr");
//    	$a->include_related("transaction","SUM(amountCr)","amountCr");
    	$a->where("created_at <",$dateFrom);
//    	$a->where("created_at <",$dateTo);
    	$a->where_related("scheme","id",$scheme);
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

    }
}
?>
