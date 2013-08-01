<?php
class page_stock_transfer extends Page{
	function init(){
		parent::init();

			$stock_transfer=$this->add('Model_Stock_Transfer');
			$tb=$this->api->auth->model['branch_id'];
			// $stock_transfer->addCondition('from_id',$this->api->auth->model['branch_id']);
			$stock_transfer->_dsql()->where(array(array('from_id',$tb),array('to_id',$tb)));
			$stock_transfer->_dsql()->order('date','desc');
			$form=$this->add('Form');
			// $from_field=$this->addField('dropdown','from_id')->setEmptyText('----')->setModel('Branch');
			$from_field=$form->addField('Dropdown','to_id')->setEmptyText('Select Any Branch')->validateNotNull()->setModel('Branch');
			$item_field=$form->addField('dropdown','item_id')->setEmptyText('Select Any Item')->validateNotNull()->setModel('Items');
			$qty_field=$form->addField('line','Quantity')->validateNotNull();
			$qty_field=$form->addField('text','Remarks')->validateNotNull();
			$date_field=$form->addField('DatePicker','date')->validateNotNull()->set(date('Y-m-d'));
			$form->addSubmit('Transfer');

			$crud=$this->add('CRUD',array('allow_add'=>false,"allow_edit"=>false));
			if(!$crud->isEditing()){
				$crud->grid->addPaginator(50);
				$crud->grid->addQuickSearch(array('item', 'to','from','remarks'));
			}
			$crud->setModel($stock_transfer);

			if($form->isSubmitted()){
				if($form->get('to_id') == $this->api->auth->model['branch_id']){
					$form->displayError('to_id',"You cannot transfer to your own branch");


				}

				$stock_transfer=$this->add('Model_Stock_Transfer');
				$stock_transfer['from_id']=$this->api->auth->model['branch_id'];
				$stock_transfer['to_id']=$form->get('to_id');
				$stock_transfer['item_id']=$form->get('item_id');
				$stock_transfer['Quantity']=$form->get('Quantity');
				$stock_transfer['Remarks']=$form->get('Remarks');
				$stock_transfer['date']=$form->get('date');
				$stock_transfer->save();

				$form->js(null,array($form->js()->reload(),$crud->grid->js()->reload()))->univ()->successMessage("Stock Transfer Successesfully")->execute();

			}
	}
}