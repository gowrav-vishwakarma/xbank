<?php
class page_employee_leaves extends Page {
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$form->addField('dropdown','branch')->setEmptyText("All")->setModel('Branch');
		$form->addSubmit('Filter');

		$employee=$this->add('Model_Employee');
		$grid=$this->add('Grid');

		if($_GET['branch']){
			$employee->addCondition('branch_id',$_GET['branch']);
		}

		$grid->setModel($employee,array('branch','name','Alloted_Leaves','Used_Leaves'));
		$grid->addColumn('expander','leaves_allot');
		$grid->addColumn('expander','leaves_used');

		if($form->isSubmitted()){
			$grid->js()->reload(array('branch'=>$form->get('branch')))->execute();
		}

	}

	function page_leaves_allot(){
		$this->api->stickyGET('jos_xatk_employee_id');
		$allot=$this->add('Model_Leaves_Alloted');
		$allot->addCondition('emp_id',$_GET['jos_xatk_employee_id']);

		$crud=$this->add('CRUD');
		$crud->setModel($allot);
	}

	function page_leaves_used(){
		$this->api->stickyGET('jos_xatk_employee_id');
		$used=$this->add('Model_Leaves_Used');
		$used->addCondition('emp_id',$_GET['jos_xatk_employee_id']);

		$crud=$this->add('CRUD');
		$crud->setModel($used);
	}

}