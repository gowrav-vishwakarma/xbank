<?php
class Model_Branch_All extends Model_Table{
	var $table="jos_xbranch";
	function init(){
		parent::init();

		$this->addField('name',"Name");
		$this->addField('Address');
		$this->addField('Code');
		$this->addField('PerformClosings')->type('boolean');
		$this->addField('SendSms')->type('boolean');
		$this->addField('published')->type('boolean');

		$this->hasMany('Stock_Sent','from_id');
		$this->hasMany('Stock_Received','to_id');
		$this->hasMany('Stock','branch_id');
		$this->hasMany('Stock_Purchase','branch_id');
		$this->hasMany('Stock_Consume','branch_id');
		$this->hasMany('Holidays','branch_id');
		$this->hasMany('Employee','branch_id');


	}
}