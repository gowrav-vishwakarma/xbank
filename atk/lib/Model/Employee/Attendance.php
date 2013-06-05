<?php
class Model_Employee_Attendance extends Model_Table{
	var $table="jos_xatk_attendance";
	function init(){
		parent::init();

		$h=array();
		$m=array();

		$this->hasOne('Employee','emp_id');
		$this->addField('Created_At')->type('date')->defaultValue(date('Y-m-d H:i:s'));
		for($i=1;$i<=24;$i++) $h += array($i=>$i);
		$this->addField('TimeHour')->setValueList($h)->defaultValue(date('H'))->display(array("grid"=>'grid/inline',"form"=>"dropdown"));
		
		for($i=0;$i<=59;$i++) $m += array($i=>$i);
		$this->addField('TimeMinute')->setValueList($m)->defaultValue(date('i'))->display(array("grid"=>'grid/inline',"form"=>"dropdown"));
		

		$this->addField('Mode')->setValueList(array('P'=>'Present',
													'A'=>'Absent',
													'L'=>'Leave',
													'PL'=>'PaidLeave'))->display(array("grid"=>'grid/inline',"form"=>"dropdown"));
	
		$this->addHook('afterSave',$this);
	}

	function afterSave(){
		$chk_leave=$this->add('Model_Leaves_Used');
		$chk_leave->addCondition('Created_At',$this['Created_At']);
		$chk_leave->addCondition('emp_id',$this['emp_id']);
		$chk_leave->tryLoadAny();
		if($chk_leave->loaded()) $chk_leave->delete();
		if($this['Mode']=='L' OR $this['Mode']=='PL'){
			$chk_leave=$this->add('Model_Leaves_Used');
			$chk_leave['leaves']=1;
			$chk_leave['Created_At']=$this['Created_At'];
			$chk_leave['emp_id']=$this['emp_id'];
			$chk_leave['Narretion']='Leave on '. $this['Created_At'];
			$chk_leave['isPaid']=($this['Mode']=='PL')?true:false;
			$chk_leave->save();
		}
	}


}