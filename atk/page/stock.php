<?php

class page_stock extends Page{
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		if($this->api->auth->model['AccessLevel']>=80){
			$tabs->addtabUrl('stock_category','Category');
			$tabs->addtabUrl('stock_current','Current Stock');
		}
		$tabs->addtabUrl('stock_purchase','Add Stock');
		$tabs->addtabUrl('stock_transfer','Transfer');
		$tabs->addtabUrl('stock_consume','Consume');
	}
}