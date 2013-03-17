<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class corrections extends CI_Controller {
	function smReset(){

		// $this->db->query('ALTER TABLE `jos_xlog` ADD `staff_id` INT NOT NULL ');
		$sm=new Account();
		$sm->where('DefaultAC',0);
		$sm->where('schemes_id',6);
		$sm->order_by('id');
		$sm->get();

		$i=1;
		$tmp=null;
		try{
			foreach($sm as $s){
				// Get previous SM account number used
				$ts=new Account();
				$ts->where('AccountNumber','SM'.$i);
				$ts->where('id >',$s->id);
				$ts->get();
				if($ts->exists()){
					$ts->AccountNumber = $ts->AccountNumber . "_";
					$ts->save();
				}
				$s->AccountNumber = "SM".$i;
				$s->save();
				$i++;
			}
		}catch(Exception $e){
			echo $tmp->AccountNumber;
		}
	}
}