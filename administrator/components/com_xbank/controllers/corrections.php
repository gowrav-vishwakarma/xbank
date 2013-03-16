<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class corrections extends CI_Controller {
	function smReset(){
		$sm=new Account();
		$sm->where('DefaultAC',0);
		$sm->where('schemes_id',6);
		$sm->order_by('id');
		$sm->get();

		$i=1;
		foreach($sm as $s){
			$s->AccountNumber = "SM".$i;
			$s->save();
			$i++;
		}
	}
}