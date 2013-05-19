<?php
class Model_Leaves_Alloted extends Model_Table{
	var $table="jos_xatk_leaves_alloted";
	function init(){
		parent::init();

		$this->hasOne('Employee','emp_id');
		$this->addField('Created_At')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('Leaves');
		$this->addField('Narretion');
	}
}