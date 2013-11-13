<?php
class Model_Leaves_Used extends Model_Table{
	var $table="jos_xatk_leaves_used";
	function init(){
		parent::init();

		$this->hasOne('Employee','emp_id');
		$this->addField('Created_At')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('leaves');
		$this->addField('Narretion');
		$this->addField('isPaid')->type('boolean');
	}
}