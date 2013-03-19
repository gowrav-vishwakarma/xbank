<?php
class Model_Stock_Purchase extends Model_Table{
	var $table="jos_xstock_purchase";
	function init(){
		parent::init();
		$this->hasOne('Branch_All','branch_id')->mandatory("This field is Required");
		$this->addField('Quantity')->mandatory("This field is Required");
		$this->addField('date')->defaultValue(date('Y-m-d'))->type('date')->mandatory("This field is Required");
		$this->addField('Remarks')->mandatory("This field is Required");
		$this->hasOne('Items','item_id')->mandatory("This field is Required");
		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);

	}
	function beforeSave(){
		$stock=$this->add('Model_Stock');
		$stock->addCondition('branch_id',$this['branch_id']);
		$stock->addCondition('item_id',$this['item_id']);
		$stock->tryLoadAny();

		if(!$stock->loaded()){
			$stock['Quantity'] = $this['Quantity'];
		}else{
			$stock['Quantity'] = $stock['Quantity'] + $this['Quantity'];
		}

		$stock->save();
	}

	function beforeDelete(){
		$stock=$this->add('Model_Stock');
		$stock->addCondition('branch_id',$this['branch_id']);
		$stock->addCondition('item_id',$this['item_id']);
		$stock->tryLoadAny();

		if(!$stock->loaded()){
			$stock['Quantity'] = -$this['Quantity'];
		}else{
			$stock['Quantity'] = $stock['Quantity'] - $this['Quantity'];
		}

		$stock->save();
	}
}