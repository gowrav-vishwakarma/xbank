<?php
class page_employee extends Page{
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');

		$tabs->addtabUrl('employee_attendance','Attendance');
		$tabs->addtabUrl('employee_payment','Payment');
		$tabs->addtabUrl('employee_leaves','Leaves');
		$tabs->addtabUrl('employee_holidays','Holidays');
		$tabs->addtabUrl('employee_add','Add New Employee');
		$tabs->addtabUrl('employee_report','Attendance Report');
	}
}