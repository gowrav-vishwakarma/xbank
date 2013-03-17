<?php
class page_stock_current extends Page{
	function init(){
		parent::init();

		$stocks=$this->add('Model_Stock');
		$stocks->addCondition('branch_id',$this->api->auth->model['branch_id']);
		$grid=$this->add('Grid');
		$grid->setModel($stocks);

	}
}