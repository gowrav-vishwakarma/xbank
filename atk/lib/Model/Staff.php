<?php
class Model_Staff Extends Model_Table{
	var $table="jos_xstaff";
	function init(){
		parent::init();

		$this->hasOne('Branch_All','branch_id');
		$this->addField('name','StaffID');
		$this->addField('Password');
		$this->addField('AccessLevel');
		$this->addField('Jid');

	}
}