<?php
class page_employee_holidays extends Page{
	function init(){
		parent::init();

		$cols=$this->add('Columns');
		$left=$cols->addColumn(5);
		$center=$cols->addColumn(1)->addClass('ui-widget-shadow ui-corner-all');
		$right=$cols->addColumn(5);

		$left->add('H3')->set('Add New Holiday');

		// $left->addClass('ui-widget-shadow ui-corner-all');
		$addHolidayForm=$left->add('Form');
		$addHolidayForm->addField('dropdown','branch')->setEmptyText("All")->setModel($this->add('Model_Branch')->addCondition('name','<>','default'));
		$addHolidayForm->addField('DatePicker','date')->validateNotNull();
		$addHolidayForm->addField('line','remark')->validateNotNull();
		$addHolidayForm->addSubmit("Mark Holiday");
		
		// $right->addClass('ui-widget-shadow ui-corner-all');
		$right->add('H3')->set('Filter The Grid');
		$form=$right->add('Form',null,null,array('form_horizontal'));
		$form->addField('dropdown','month')->setValueList(array(1=>"Jan",
																2=>"Feb",
																3=>"March",
																4=>"April",
																5=>"May",
																6=>"Jun",
																7=>"July",
																8=>"Augest",
																9=>"Sept",
																10=>"Oct",
																11=>"Nov",
																12=>"Dec"))->setEmptyText("Select Month");

		$form->addField("line",'year');
		$form->addField("dropdown",'branch')->setEmptyText("All")->setModel($this->add('Model_Branch')->addCondition('name','<>','default'));
		$form->addSubmit("Get List");

		$holidays=$this->add('Model_Holidays');


		$crud=$this->add('CRUD',array('allow_add'=>false));

		if($crud->grid){
			if($_GET['filter']){
				$holidays->addCondition('month',(int)$_GET['month']);
				$holidays->addCondition('year',(int)$_GET['year']);
				if($_GET['branch'])	$holidays->addCondition('branch_id',(int)$_GET['branch']);
			}else{
				$holidays->addCondition('month',(int)date('m'));
				$holidays->addCondition('year',(int)date('Y'));
				$form->getElement('month')->set((int)date('m'));
				$form->getElement('year')->set((int)date('Y'));
			}

		}
		// $holidays->debug();
		// $holidays->setOrder(array('desc'=>'HolidayDate', 'desc'=>'id'));
		$holidays->_dsql()->order('HolidayDate','desc');
		$crud->setModel($holidays);

		if($form->isSubmitted()){
			$crud->grid->js()->reload(array('month'=>$form->get('month'),
											'year'=>$form->get('year'),
											'branch'=>$form->get('branch'),
											'filter'=>'1'))->execute();

		}

		if($addHolidayForm->isSubmitted()){
			$branch=$this->add('Model_Branch');
			$branch->addCondition('name','<>','default');
			
			if($addHolidayForm->get('branch')){
				$branch->addCondition('id',$addHolidayForm->get('branch'));
			}

			foreach ($branch as $junk) {
				$chk_holiday=$this->add('Model_Holidays');
				$chk_holiday->addCondition('branch_id',$branch->id);
				$chk_holiday->addCondition('HolidayDate',$addHolidayForm->get('date'));
				$chk_holiday->tryLoadAny();

				if(!$chk_holiday->loaded()){
					$holidays=$this->add('Model_Holidays');
					$holidays['branch_id'] =$branch->id;
					$holidays['HolidayDate'] =$addHolidayForm->get('date');
					$holidays['remark'] =$addHolidayForm->get('remark');
					$holidays->save();
				}

			}

			$addHolidayForm->js(null,array(
					$addHolidayForm->js()->univ()->successMessage('Holiday Added'),
					$crud->grid->js()->reload()
				))->reload()->execute();
		}


	}
}