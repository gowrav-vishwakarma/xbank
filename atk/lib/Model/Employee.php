<?php
class Model_Employee extends Model_Employee_All{
	
	function init(){
		parent::init();

		$this->addCondition('is_Active',true);

	}
}