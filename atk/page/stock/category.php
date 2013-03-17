<?php
class page_stock_category extends Page{
	function page_index(){

		$crud=$this->add('CRUD');
		$crud->setModel('Category');

		if(!$crud->isEditing()){
			$crud->grid->addColumn('expander','items','items');
		}

	}

	function page_items(){
		$this->api->stickyGET('jos_xcategory_id');

		$cat=$this->add('Model_Category')->load($_GET['jos_xcategory_id']);
		$crud=$this->add('CRUD');
		$crud->setModel($cat->ref('Items'));

	}
}