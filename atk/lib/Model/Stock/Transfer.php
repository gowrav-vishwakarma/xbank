<?php
class Model_Stock_Transfer extends Model_Table{
	var $table="jos_xstock_transfer";
	function init(){
		parent::init();
		$this->hasOne("Items",'item_id');
		$this->hasOne('Branch_All','from_id');
		$this->hasOne('Branch_All','to_id');
		$this->addField('Quantity');
		$this->addField('date')->defaultValue(date('Y-m-d'))->type('date');
		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
	}
	function beforeSave(){
		$from_stock=$this->add('Model_Stock');
		$from_stock->addCondition('branch_id',$this['from_id']);
		$from_stock->tryLoadAny();

		if(!$from_stock->loaded()){
			$from_stock['Quantity'] =  -$this['Quantity'];
		}else{
			$from_stock['Quantity'] = $from_stock['Quantity'] - $this['Quantity'];
		}

		$from_stock->save();

		$to_stock=$this->add('Model_Stock');
		$to_stock->addCondition('branch_id',$this['to_id']);
		$to_stock->tryLoadAny();

		if(!$to_stock->loaded()){
			$to_stock['Quantity'] = $this['Quantity'];			
		}else{
			$to_stock['Quantity'] = $to_stock['Quantity'] + $this['Quantity'];
		}

		$to_stock->save();
	}

	function beforeDelete(){
		$from_stock=$this->add('Model_Stock');
		$from_stock->addCondition('branch_id',$this['from_id']);
		$from_stock->tryLoadAny();

		if(!$from_stock->loaded()){
			$from_stock['Quantity'] =  $this['Quantity'];
		}else{
			$from_stock['Quantity'] = $from_stock['Quantity'] + $this['Quantity'];
		}

		$from_stock->save();

		$to_stock=$this->add('Model_Stock');
		$to_stock->addCondition('branch_id',$this['to_id']);
		$to_stock->tryLoadAny();

		if(!$to_stock->loaded()){
			$to_stock['Quantity'] = -$this['Quantity'];			
		}else{
			$to_stock['Quantity'] = $to_stock['Quantity'] - $this['Quantity'];
		}

		$to_stock->save();
	}
}