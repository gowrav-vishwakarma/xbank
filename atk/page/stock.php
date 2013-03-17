<?php

class page_stock extends Page{
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tabs->addtabUrl('stock_category','Category');
		$tabs->addtabUrl('stock_current','Current Stock');
		$tabs->addtabUrl('stock_purchase','Purchase');
		$tabs->addtabUrl('stock_transfer','Transfer');
		$tabs->addtabUrl('stock_consume','Consume');
	}
}