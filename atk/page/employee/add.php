<?php
class page_employee_add extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Employee_All');
	}
}