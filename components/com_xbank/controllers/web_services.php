<?php

class web_services extends CI_Controller{

	function web_services(){
		parent::__construct();
// 		$this->output->enable_profiler(TRUE);
	}

	function transaction(){
		$transactionJSON=inp('transaction_json');
		echo "you sent " .$transactionJSON;
	}

}
