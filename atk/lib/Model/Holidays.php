<?php
class Model_Holidays extends Model_table{
	var $table="jos_xatk_holidays";
	function init(){
		parent::init();

		$this->hasOne('Branch_All','branch_id');
		$this->addField("HolidayDate")->type('date')->defaultValue(date('y-m-d'));
		$this->addField('remark');


		$this->addExpression('month')->set('MONTH(HolidayDate)');
		$this->addExpression('year')->set('YEAR(HolidayDate)');
	}
}