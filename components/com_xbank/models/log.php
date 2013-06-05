<?php
class Log extends DataMapper{
    var $table='xlog';
     var $has_one = array(
            'branch'=>array(
                'class'=>'branch',
                'join_other_as'=>'branch',
                'join_table'=>'jos_xlog',
                'other_field'=>'logs'
                )
         );


     public static function write($message,$account=""){
            if($account=="")
                $account=null;
            else{
                $account=new Account($account);
                if($account->result_count() <= 0)
                    $account=null;
                else
                    $account=$account;
            }
		$log=new Log();
        $log->accounts_id=$account->id;
		$log->Message=$message;
        $log->branch_id = Branch::getCurrentBranch()->id;
        $log->staff_id = Staff::getCurrentStaff()->id;
		$log->save();
	}
}
             

?>