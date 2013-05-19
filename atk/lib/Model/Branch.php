<?php
class Model_Branch extends Model_Branch_All{
	function init(){
		parent::init();
		$this->addCondition('published',true);
		

		
	}
}