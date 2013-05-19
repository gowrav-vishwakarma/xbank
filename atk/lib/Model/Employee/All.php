<?php
class Model_Employee_All extends Model_Table {
	var $table= "jos_xatk_employee";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addField('name');
		$this->addField('FatherName');
		$this->addField('PresentAddress');
		$this->addField('PermanentAddress');
		$this->addField('MobileNo');
		$this->addField('LandlineNo');
		$this->addField('DOB')->type('date');
		$this->addField('OtherDetails');
		$this->addField('Salary');
		$this->addField('Allownces');
		$this->addField('PFSalary');
		$this->addField('isPFApplicable');
		$this->addField('PFAmount');
		$this->addField('TDSAmount');
		$this->addField('Account_Number');
		$this->addField('SalaryMode');
		$this->addField('is_Active')->type('boolean');
		$this->hasMany('Employee_Attendance','emp_id');
		$this->hasMany('Employee_Payment','emp_id');
		$this->hasMany('Leaves_Alloted','emp_id');
		$this->hasMany('Leaves_Used','emp_id');

		$this->addExpression('Alloted_Leaves')->set(function($m,$q){
			return $m->refSQL('Leaves_Alloted')->sum('Leaves');
		});

		$this->addExpression('Used_Leaves')->set(function($m,$q){
			return $m->refSQL('Leaves_Used')->sum('leaves');
		});


	}
}