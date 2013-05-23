<?php
class page_employee_payment extends Page{
	function page_index() {
		// parent::init();

		// $this->api->stickyGET('filter');
		// $this->api->stickyGET('branch');
		// $this->api->stickyGET('date');

		$payment=$this->add( 'Model_Employee_Payment' );
		$payment_for_grid=$this->add( 'Model_Employee_Payment' );
		$form=$this->add( 'Form', null, null, array( 'form_horizontal' ) );
		$form->addField( 'dropdown', 'branch' )->setEmptyText( 'Select Any Branch' )->validateNotNull()->setModel( 'Branch' );
		$form->addField( 'DatePicker', 'date' )->validateNotNull()->set( date( 'Y-m-d' ) );

		$form->addSubmit( 'Filter' );
		$crud=$this->add('CRUD',array('allow_add'=>false));
		

		if ( $_GET['filter'] ) {
			// $emp=$payment->leftJoin( 'jos_xatk_employee.id', 'emp_id' );
			// $emp->addField( 'branch_id' );
			$payment->addCondition( 'branch_id', $_GET['branch'] );
			$payment->addCondition( 'MonthYear', date( 'Ym', strtotime( $_GET['date'] ) ) );

			// $emp=$payment_for_grid->Join( 'jos_xatk_employee.id', 'emp_id' ,'right','a');
			// $emp->addField( 'branch_id' );
			$payment_for_grid->addCondition( 'branch_id', $_GET['branch'] );
			$payment_for_grid->addCondition( 'MonthYear', date( 'Ym', strtotime( $_GET['date'] ) ) );


			if ( $payment->count()->getOne() != $this->add( 'Model_Branch' )->load( $_GET['branch'] )->ref( 'Employee' )->count()->getOne() ) {

				$emp=$this->add( 'Model_Employee' );
				$emp->addCondition( 'branch_id', $_GET['branch'] );
				foreach ( $emp as $junk ) {
					$payment_add_check=$this->add( 'Model_Employee_Payment' );
					$payment_add_check->addCondition( 'emp_id', $emp->id );
					$payment_add_check->addCondition( 'MonthYear', date( 'Ym', strtotime( $_GET['date'] ) ));
					$payment_add_check->tryLoadAny();
					if ( !$payment_add_check->loaded() ) {
						$payment_add=$this->add( 'Model_Employee_Payment' );
						$payment_add['emp_id']=$emp->id;
						$payment_add['Narration']=0;
						$payment_add['Created_At']=$_GET['date'];
						$payment_add['MonthYear']=date( 'Ym', strtotime( $_GET['date'] ) ) ;
						$payment_add->calculateAttendaceData();
						$payment_add->save();
					}

				}

			}
		}else{
			if(!$this->api->isAjaxOutput()) $payment_for_grid->addCondition('id',-1);
		}

		// $payment_for_grid->debug();
		$crud->setModel( $payment_for_grid ,array('Salary','PFAmount','Deduction','Narration'),array('emp','TotalWorkingDays','PresentDays','HoliDays','Sundays','Leaves','LeavesPaid','Absent','Salary','PFAmount','Deduction','Narration'));

		if ( $form->isSubmitted() ) {
			$crud->grid->js( null, $crud->grid->js()->univ()->successMessage( "Record Filter" )
			)->reload( array( "branch"=>$form->get( 'branch' ),
					"date"=>$form->get( 'date' ),
					"filter"=>1 ) )->execute();

		}
	}
}
