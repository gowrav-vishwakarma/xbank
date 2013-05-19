<?php
class page_employee_attendance extends Page{
	function init(){
		parent::init();

		$this->api->stickyGET('filter');
		$this->api->stickyGET('branch');
		$this->api->stickyGET('date');
		
		$form=$this->add('Form',null,null,array('form_horizontal'));
		$form->addField('dropdown','branch')->setEmptyText("Select Branch")->validateNotNull()->setModel('Branch');
		$form->addField('DatePicker','date')->set(date('Y-m-d'))->validateNotNull();
		$form->addSubmit('Filter');

		$att=$this->add('Model_Employee_Attendance');

		$grid=$this->add('Grid',null,null,array('view/filteronlygrid'));
		if($_GET['filter']){
			$emp=$att->join('jos_xatk_employee.id','emp_id');
			$emp->addField('branch_id');
			$att->addCondition('branch_id',$_GET['branch']);
			$att->addCondition('Created_At',$_GET['date']);

			if($att->count()->getOne() != $this->add('Model_Branch')->load($_GET['branch'])->ref('Employee')->count()->getOne()){
				// Need to add all employee in attendance table
				$emp=$this->add('Model_Employee');
				$emp->addCondition('branch_id',$_GET['branch']);
				foreach ($emp as $junk) {
					$att_add_check=$this->add('Model_Employee_Attendance');
					$att_add_check->addCondition('emp_id',$emp->id);
					$att_add_check->addCondition('Created_At',$_GET['date']);
					$att_add_check->tryLoadAny();
					if(!$att_add_check->loaded()){
						$att_add=$this->add('Model_Employee_Attendance');
						$att_add['branch_id']=$_GET['branch'];
						$att_add['Created_At']=$_GET['date'];
						$att_add['TimeHour']=date('H');
						$att_add['TimeMinute']=date('i');
						$att_add['Mode']='A';
						$att_add['emp_id']=$emp->id;
						$att_add->save();
					}

				}
			}
		}else{

			$att->addCondition('id',-1);

		}

		$grid->setModel($att);

		if($form->isSubmitted()){
			$grid->js(null,$grid->js()->univ()->successMessage("Record Filter"))->
			reload(array('branch'=>$form->get('branch'),
							'date'=>$form->get('date'),
							'filter'=>'1'))->execute();
		}
		
	}
}