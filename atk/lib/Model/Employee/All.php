<?php
class Model_Employee_All extends Model_Table {
	var $table= "jos_xatk_employee";
	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id');
		$this->addField('name');
		$this->addField('FatherName');
		$this->addField('PresentAddress');
		$this->addField('PermanentAddress');
		$this->addField('MobileNo');
		$this->addField('LandlineNo');
		$this->addField('DOB')->type('date');
		$this->addField('OtherDetails');
		$this->addField('is_Active')->type('boolean');

	}
}