<?php
class page_employee_report extends Page {
	function init(){
		parent::init();

		$branch=$this->add('Model_Branch');
		$emp=$this->add('Model_Employee');

		$form=$this->add('Form',null,null,array('form_horizontal'));
		$branch_field=$form->addField('dropdown','branch')->setEmptyText("All")->validateNotNull();

		$branch_field->setModel($branch);
		$emp_field=$form->addField('dropdown','employee')->setEmptyText("Select Employee")->validateNotNull();

		if($_GET['branch_field_id']){
			$emp->addCondition('branch_id',$_GET['branch_field_id']);
		}

		$emp_field->setModel($emp);

		$form->addField('DatePicker','month')->validateNotNull();

		$form->addSubmit("Get List");

		$branch_field->js('change',$form->js()->atk4_form('reloadField','employee',array($this->api->url(),'branch_field_id'=>$branch_field->js()->val())));

		$att=$this->add('Model_Employee_Attendance');
		$grid=$this->add('Grid');

		if($_GET['emp']){
			$att->addCondition('emp_id',$_GET['emp']);

			$att->addCondition('Created_At','>=',date('Y-m-01',strtotime($_GET['month'])));
			$att->addCondition('Created_At','<=',date('Y-m-t',strtotime($_GET['month'])));
		}

		$grid->setModel($att);
		$grid->setFormatter("Mode",'text');
		$grid->setFormatter("TimeHour",'text');
		$grid->setFormatter("TimeMinute",'text');

		if($form->isSubmitted()){
			$grid->js()->reload(array('emp'=>$form->get('employee'),'month'=>$form->get('month')))->execute();
		}
	}
}