<?php
class page_stock_transfer extends Page{
	function init(){
		parent::init();

			$stock_transfer=$this->add('Model_Stock_Transfer');
			$stock_transfer->addCondition('from_id',$this->api->auth->model['branch_id']);
			$form=$this->add('Form');
			// $from_field=$this->addField('dropdown','from_id')->setEmptyText('----')->setModel('Branch');
			$from_field=$form->addField('Dropdown','to_id')->setEmptyText('Select Any Branch')->validateNotNull()->setModel('Branch');
			$item_field=$form->addField('dropdown','item_id')->setEmptyText('Select Any Item')->validateNotNull()->setModel('Items');
			$qty_field=$form->addField('line','Quantity')->validateNotNull();
			$date_field=$form->addField('DatePicker','date')->validateNotNull()->set(date('Y-m-d'));
			$form->addSubmit('Transfer');

			if($form->isSubmitted()){
				if($form->get('to_id') == $this->api->auth->model['branch_id']){
					$form->displayError('to_id',"You cannot transfer to your own branch");
				}

				$stock_transfer=$this->add('Model_Stock_Transfer');
				$stock_transfer['from_id']=$this->api->auth->model['branch_id'];
				$stock_transfer['to_id']=$form->get('to_id');
				$stock_transfer['item_id']=$form->get('item_id');
				$stock_transfer['Quantity']=$form->get('Quantity');
				$stock_transfer['date']=$form->get('date');
				$stock_transfer->save();

				$form->js(null,$form->js()->reload())->univ()->successMessage("Stock Transfer Successesfully")->execute();

			}
	}
}