<?php
class Model_Employee_Payment extends Model_Table{
	var $table="jos_xatk_payment";
	function init(){
		parent::init();

		$this->hasOne('Employee','emp_id');
		$this->addField('TotalWorkingDays');
		$this->addField('PresentDays');
		$this->addField('HoliDays');
		$this->addField('Sundays');
		$this->addField('Leaves');
		$this->addField('LeavesPaid');
		$this->addField('Absent');
		$this->addField('Salary');
		$this->addField('PFAmount');
		$this->addField('Deduction');//->display(array("grid"=>"grid/inline","form"=>"line"));
		$this->addField('Narration');//->display(array("grid"=>"grid/inline","form"=>"line"));
		$this->addField('Created_At')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('branch_id');

		$this->addField('MonthYear')->defaultValue(date('Ym'));


		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		$emp=$this->add('Model_Employee');
		$emp->load($this['emp_id']);
		$this['branch_id']=$emp['branch_id'];

		$this['Salary'] = (($emp['Salary']/$this['TotalWorkingDays'])*($this['PresentDays']+$this['LeavesPaid'])) - $this['Deduction'];
			
		$this['PFAmount']=0;

	}

	function calculateAttendaceData(){

		$emp=$this->add('Model_Employee');
		$emp->load($this['emp_id']);

		$payment=$this->add('Model_Employee_Payment');
		
		$holidays=$this->add('Model_Holidays');
		$holidays->addCondition('HolidayDate','>=',date('Y-m-01',strtotime($this['Created_At'])));
		$holidays->addCondition('HolidayDate','<=',date('Y-m-t',strtotime($this['Created_At'])));
		$holidays->addCondition('branch_id',$emp['branch_id']);
		// $holidays->debug();


		$att=$this->add('Model_Employee_Attendance');
		$att->addCondition('emp_id',$this['emp_id']);
		$att->addCondition('Created_At','>=',date('Y-m-01',strtotime($this['Created_At'])));
		$att->addCondition('Created_At','<=',date('Y-m-t',strtotime($this['Created_At'])));
		$att=$att->_dsql()->del('field')->field('count(*) days')->field('Mode')->group('Mode')->getAll();
		// $att->debug();
		$att_array=array();
		foreach($att as $a){
			$att_array[$a['Mode']] = $a['days'];
		}



		$totaldaysofmonth=date('t',strtotime($this['Created_At']));
		$totalholidays = $holidays->count()->getOne();
		$totalsundays=count($this->getSunday(date('Y',strtotime($this['Created_At'])),date('m',strtotime($this['Created_At']))));	
		$totalworkingdays  = $totaldaysofmonth - ($totalholidays + $totalsundays);

		$this['TotalWorkingDays'] = $totalworkingdays;
		$this['PresentDays'] = $att_array['P'];
		$this['HoliDays'] = $totalholidays;
		$this['Sundays'] = $totalsundays;
		$this['Leaves'] = $att_array['L'];
		$this['LeavesPaid'] = $att_array['PL'];
		$this['Absent'] = $att_array['A'];
		$this['MonthYear'] = date('Ym',strtotime($this['Created_At']));
		
		
	}

	function getSunday($y, $m)
	{
	    $ts  = strtotime("first sunday $y-$m-01");
	    $sunday = array();
	    while(date('m',$ts) == $m) {
	        $sunday[] = $ts;
	        $ts = strtotime('next sunday', $ts);
	    }
	    return $sunday;
	}
}