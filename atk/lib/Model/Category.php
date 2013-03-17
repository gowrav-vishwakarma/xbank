<?php
class Model_Category Extends Model_Table{
	var $table='jos_xcategory';
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('Description');
		$this->hasMany('Items','category_id');

	}
} 