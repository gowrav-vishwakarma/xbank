<?php
class page_stock_purchase extends Page{
	function init(){
		parent::init();
		$stock_purchase=$this->add('Model_Stock_Purchase');
		$stock_purchase->addCondition('branch_id',$this->api->auth->model['branch_id']);
		$crud=$this->add('CRUD');
		$crud->setModel($stock_purchase);
	}
}