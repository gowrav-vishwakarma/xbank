<?php
class Model_Employee_Attendance extends Model_Table{
	var $table="jos_xatk_attendance";
	function init(){
		parent::init();

		$this->addField('Coming_At')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
	}
}