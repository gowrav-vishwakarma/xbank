<?php

class page_test extends Page {
	function init(){
		parent::init();

		 $xxx=array("Dr"=>array(123,456),'Cr'=>array('hi'=>'there'));
		 $xxx=(json_encode($xxx));
		 // echo $xxx;

		 // return;
		 $ch = curl_init(); 
	    // set url 
	    curl_setopt($ch, CURLOPT_URL, "http://localhost/xbank/index.php?option=com_xbank&format=raw&task=web_services.transaction&transaction_json=$xxx"); 

	    //return the transfer as a string 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	    // $output contains the output string 
	    $output = curl_exec($ch); 
	    echo $output;
	    // close curl resource to free up system resources 
	    curl_close($ch);
	}
}