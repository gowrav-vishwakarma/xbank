<?php
class Model_Stock_Consume extends Model_Table{
	var $table="jos_xstock_consume";
	function init(){
		parent::init();
		$this->hasOne('Branch_All','branch_id');
		$this->hasOne('Items','item_id');
		$this->addField('Quantity');
		$this->addField('date')->defaultValue(date('Y-m-d'))->type('date');
		$this->addField('Remarks');

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
	}
	function beforeSave(){
		$stock=$this->add('Model_Stock');
		$stock->addCondition('branch_id',$this['branch_id']);
		$stock->addCondition('item_id',$this['item_id']);

		$stock->tryLoadAny();
		if(!$stock->loaded()){
			$stock['Quantity']= -$this['Quantity'];
		}else{
			$stock['Quantity'] = $stock['Quantity'] - $this['Quantity'];
		}
		$stock->save();
	}

	function beforeDelete(){
		$stock=$this->add('Model_Stock');
		$stock->addCondition('branch_id',$this['branch_id']);
		$stock->addCondition('item_id',$this['item_id']);

		$stock->tryLoadAny();
		if(!$stock->loaded()){
			$stock['Quantity']= $this['Quantity'];
		}else{
			$stock['Quantity'] = $stock['Quantity'] + $this['Quantity'];
		}
		$stock->save();

	}
}