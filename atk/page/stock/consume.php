<?php
class page_stock_consume extends Page{
	function init(){
		parent::init();

		$stock_consume1=$this->add('Model_Stock_Consume');
		$stock_consume1->addCondition('branch_id',$this->api->auth->model['branch_id']);
		$stock_consume1->_dsql()->order('date','desc');


		$form=$this->add('Form');
		$form->addField('dropdown','item')->setEmptyText('Select Any item')->validateNotNull()->setModel('Items');
		// $form->addField('dropdown','branch')->setEmptyText('Select Any item')->validateNotNull()->setModel('Branch');
		$form->addField('line','Quantity')->validateNotNull();
		$form->addField('DatePicker','date')->validateNotNull()->set(date('Y-m-d'));
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
		// $form->addField('time','date')->validateNotNull()->set(date('Y-m-d H:i:s'));
=======
>>>>>>> 2464172d27a3e5ea554f988e4fdcc00793a3dd6a
=======
>>>>>>> 2464172d27a3e5ea554f988e4fdcc00793a3dd6a
=======
>>>>>>> 2464172d27a3e5ea554f988e4fdcc00793a3dd6a
=======
>>>>>>> 2464172d27a3e5ea554f988e4fdcc00793a3dd6a
		$form->addField('text','Remarks')->validateNotNull();
		$form->addSubmit('Consume');

		$crud=$this->add('CRUD',array('allow_add'=>false,'allow_edit'=>false));
		$crud->add('misc/Export');
		if(!$crud->isEditing()){
		$crud->grid->addQuickSearch(array('item','remarks'));
		$crud->grid->addPaginator(50);
		}
		$crud->setModel($stock_consume1);


		if($form->isSubmitted()){


				$stock_consume=$this->add('Model_Stock_Consume');
				// $stock_consume['from_id']=$this->api->auth->model['branch_id'];
				$stock_consume['branch_id']=$this->api->auth->model['branch_id'];
				$stock_consume['item_id']=$form->get('item');
				$stock_consume['Quantity']=$form->get('Quantity');
				$stock_consume['date']=$form->get('date');
				$stock_consume['Remarks']=$form->get('Remarks');
				$stock_consume->save();

				$form->js(null,array($form->js()->reload(),$crud->grid->js()->reload()))->univ()->successMessage("Stock Consume Successesfully")->execute();
		}
	}
}