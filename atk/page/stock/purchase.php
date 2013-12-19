<?php
class page_stock_purchase extends Page{
	function init(){
		parent::init();

		$allow_del=false;
		$stock_purchase=$this->add('Model_Stock_Purchase');
		if($this->api->auth->model['AccessLevel']>=80){
			$allow_del=true;
		}
		$stock_purchase->addCondition('branch_id',$this->api->auth->model['branch_id']);
		$crud=$this->add('CRUD',array('allow_edit'=>false,'allow_del'=>$allow_del));
		$crud->setModel($stock_purchase);
		if(!$crud->isEditing()){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('item', 'Quantity'));
		}
	}
}