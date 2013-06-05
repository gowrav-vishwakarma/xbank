<?php
class page_employee_add extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Employee_All');

		if($crud->form){
			$crud->form->getElement('isPFApplicable')
			->js(true)->univ()->bindConditionalShow(array(
				'*'=>array('name','FatherName','PresentAddress','PermanentAddress','MobileNo','LandlineNo','DOB','OtherDetails','Salary','Email_Id',
					'Allownces','isPFApplicable','PFSalary','PFAmount','TDSAmount','SalaryMode',
					'is_Active','Employee_Attendance','Employee_Payment',),
				''=>array('name','FatherName','PresentAddress','PermanentAddress','MobileNo','LandlineNo','DOB','OtherDetails','Salary','Email_Id',
					'Allownces','isPFApplicable','TDSAmount','Account_Number','SalaryMode',
					'is_Active','Employee_Attendance','Employee_Payment',
					)
				),'div .atk-row');



			$crud->form->getElement('SalaryMode')
			->js(true)->univ()->bindConditionalShow(array(
				'Bank'=>array('name','FatherName','PresentAddress','PermanentAddress','MobileNo','LandlineNo','DOB','OtherDetails','Salary','Email_Id',
					'Allownces','isPFApplicable','PFSalary','PFAmount','TDSAmount','SalaryMode','Bank_Name',
					'is_Active','Employee_Attendance','Employee_Payment'),
				

				'Cash'=>array('name','FatherName','PresentAddress','PermanentAddress','MobileNo','LandlineNo','DOB','OtherDetails','Salary','Email_Id',
					'Allownces','isPFApplicable','PFSalary','PFAmount','TDSAmount','SalaryMode',
					'is_Active','Employee_Attendance','Employee_Payment'),


				'Sb_Acc'=>array('name','FatherName','PresentAddress','PermanentAddress','MobileNo','LandlineNo','DOB','OtherDetails','Salary','Email_Id',
					'Allownces','isPFApplicable','TDSAmount','SalaryMode','Account_Number',
					'is_Active','Employee_Attendance','Employee_Payment',
					),
				''=>array('name','FatherName','PresentAddress','PermanentAddress','MobileNo','LandlineNo','DOB','OtherDetails','Salary','Email_Id',
					'Allownces','isPFApplicable','TDSAmount','SalaryMode',
					'is_Active','Employee_Attendance','Employee_Payment')
				),'div .atk-row');
		}


	}
}