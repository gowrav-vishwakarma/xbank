<?php
class Model_Stock extends Model_Table{
	var $table="jos_xstock";
	function init(){
		parent::init();

		$this->hasOne('Branch_All','branch_id');
		$this->hasOne('Items','item_id');
		$this->addField('Quantity');
	}
}