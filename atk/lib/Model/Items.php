<?php
class Model_Items extends Model_Table{
	var $table='jos_xitems';
	function init(){
		parent::init();

		$this->hasOne('Category','category_id');
		$this->addField('name');
		$this->addField('Price');
		$this->addField('Description');
		$this->hasMany('Stock_Purchase','item_id');
		$this->hasMany('Stock_Consume','item_id');
		
	}
}