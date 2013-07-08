<?php

/* ------------------------------------------------------------------------
  # com_xcideveloper - Seamless merging of CI Development Style with Joomla CMS
  # ------------------------------------------------------------------------
  # author    Xavoc International / Gowrav Vishwakarma
  # copyright Copyright (C) 2011 xavoc.com. All Rights Reserved.
  # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.xavoc.com
  # Technical Support:  Forum - http://xavoc.com/index.php?option=com_discussions&view=index&Itemid=157
  ------------------------------------------------------------------------- */
// no direct access
defined('_JEXEC') or die('Restricted access');
?><?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class com_xbank extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function permissionPage($msg=""){
        echo "You are not permitted to do this";
        exit();
    }

    function index() {

        xDeveloperToolBars::getDefaultToolBar();
        $this->session->set_userdata('branch_name',Branch::getCurrentBranch()->Name);
//            $document = JFactory::getDocument();
//            $script  ="shortcut.add('Alt+g',function() {
//	window.open('index.php?option=com_xbank&task=setdate_cont.setDateTimeForm', 'Set Date Window',
//        'menubar=no,location=yes,resizable=no,scrollbars=yes,status=yes,height=400,width=650,alwaysRaised=yes');
//            });";
//            $this->jq->addDomReadyScript($script);
//            $this->load->model('sample');
//        $data['result'] = array('a' => 'b');
        $data = $this->schemewiseAccountDetails();
         $this->jq->useGraph();
         $data_url =  "index.php?option=com_xbank&format=raw&task=com_xbank.schemewiseAccountDetailsGraph";
        $data['graph'] = $this->jq->getGraphObject('80%', '200', $data_url, 'test_chart');
        $where = "";
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin"){
                $b = Branch::getCurrentBranch();
                $where = "and a.branch_id = $b->id";
        }
   //     $data['duesToReceive'] = $this->db->query("select a.AccountNumber as Accnum, p.Amount as amount, p.DueDate as DueDate, m.Name, m.FatherName,m.CurrentAddress, m.PhoneNos from jos_xpremiums p inner join jos_xaccounts a on a.id=p.accounts_id inner join jos_xmember m on a.member_id=m.id where p.DueDate ='" . getNow("Y-m-d") . "' and p.Paid = 0 and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where")->result();
   
   $a= new Account();
        $a->select('*, id as ActualCurrentBalance');
        $a->select('premiums_jos_xpremiums.DueDate as DueDate,premiums_jos_xpremiums.Amount as Amount');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where_related('premiums',"DueDate ", getNow("Y-m-d"));
        $a->order_by('AccountNumber Desc');
        $a->get();

        
//        $a->check_last_query();
        $data['duesToReceive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Scheme","Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'scheme_Name','member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );
               
//----------------------------------------------------------
   
   
 /*       $data['duesToGive'] = $this->db->query("select a.AccountNumber as Accnum, (a.CurrentBalanceCr + a.CurrentInterest - a.CurrentBalanceDr)  as amount, DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH) as DueDate from  jos_xaccounts a inner join jos_xschemes s on s.id=a.schemes_id where DATE_ADD(DATE(a.created_at), INTERVAL s.MaturityPeriod MONTH) ='" . getNow("Y-m-d") . "' and (s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' or s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "' ) and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where ")->result();
*/

	$a= new Account();
        $a->select('*,jos_xaccounts.AccountNumber as AccountNumber, round((CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)) as Amount');
        $a->select_func("DATE_ADD",'[date(jos_xaccounts.created_at),INTERVAL jos_xschemes.MaturityPeriod MONTH]','DueDate');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        $a->where('LockingStatus',0);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
//        $a->where_related('premiums',"DueDate ", getNow("Y-m-d"));
        $a->group_start();
        $a->where_related('scheme',"SchemeType",'recurring');
        $a->or_where_related('scheme',"SchemeType",'FixedAndMis');
        $a->or_where_related('scheme',"SchemeType",'DDS');
        $a->group_end();
        $a->where_field_func('DATE_ADD(DATE(jos_xaccounts.created_at), INTERVAL jos_xschemes.MaturityPeriod MONTH) = ','',getNow("Y-m-d"));
        $a->get();


//        $a->check_last_query();
        $data['duesToGive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Scheme","Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'scheme_Name','member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

//------------------------------------------------------------
	$a= new Account();
        $a->select('*, id as ActualCurrentBalance');
        $a->select('premiums_jos_xpremiums.DueDate as DueDate,premiums_jos_xpremiums.Amount as Amount');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
//        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where_field_func('WEEKOFYEAR(DueDate)', "WEEKOFYEAR",getNow("Y-m-d"));
        $a->where_field_func('YEAR(DueDate)', "YEAR",getNow("Y-m-d"));
        $a->where_related('premiums',"Paid",0);
        $a->get();


        $data['weeklyduesToReceive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );




 //       $data['weeklyduesToReceive'] = $this->db->query("select a.AccountNumber as Accnum, p.Amount as amount, p.DueDate as DueDate from jos_xpremiums p inner join jos_xaccounts a on a.id=p.accounts_id where WEEKOFYEAR(p.DueDate) = WEEKOFYEAR('". getNow("Y-m-d")."') and YEAR(p.DueDate)=YEAR('". getNow("Y-m-d")."') and p.Paid = 0 and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where")->result();
 
 	/*$a=new Account();
 	$a->include_related('scheme','MaturityPeriod');
 	$a->include_related('member','Name');
 	$a->include_related('member','FatherName');
 	$a->include_related('member','PermanentAddress');
 	$a->include_related('member','PhoneNos');
 	$a->where("WEEKOFYEAR(DATE_ADD(jos_xaccounts.created_at, INTERVAL s.MaturityPeriod MONTH)) =WEEKOFYEAR('". getNow("Y-m-d")."') and YEAR(DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH))=YEAR('". getNow("Y-m-d")."') and (s.SchemeType='".ACCOUNT_TYPE_FIXED."' or s.SchemeType='".ACCOUNT_TYPE_RECURRING."' ) and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where");
 	
 	$a->get();
 	
 	$data['weeklyduesToGive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'member_PhoneNos','~(#CurrentBalanceCr + #CurrentInterest - #CurrentBalanceDr)','~( DATE_ADD(#created_at, INTERVAL #scheme_MaturityPeriod MONTH))','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );
               
        $data['weeklyduesToGive'] = $this->db->query("select a.AccountNumber as Accnum, (a.CurrentBalanceCr + a.CurrentInterest - a.CurrentBalanceDr)  as amount, DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH) as DueDate from  jos_xaccounts a inner join jos_xschemes s on s.id=a.schemes_id where WEEKOFYEAR(DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH)) =WEEKOFYEAR('". getNow("Y-m-d")."') and YEAR(DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH))=YEAR('". getNow("Y-m-d")."') and (s.SchemeType='".ACCOUNT_TYPE_FIXED."' or s.SchemeType='".ACCOUNT_TYPE_RECURRING."' ) and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where")->result();

	*/
	
	        $a= new Account();
//        $a->select('*, id as ActualCurrentBalance');
        $a->select('*,jos_xaccounts.AccountNumber as AccountNumber, round((CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)) as Amount');
        $a->select_func("DATE_ADD",'[date(jos_xaccounts.created_at),INTERVAL jos_xschemes.MaturityPeriod MONTH]','DueDate');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
//        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where_field_func('WEEKOFYEAR(DATE_ADD(DATE(jos_xaccounts.created_at), INTERVAL jos_xschemes.MaturityPeriod MONTH)) = ', "WEEKOFYEAR",getNow("Y-m-d"));
        $a->where_field_func('YEAR(DATE_ADD(DATE(jos_xaccounts.created_at), INTERVAL jos_xschemes.MaturityPeriod MONTH))= ', "YEAR",getNow("Y-m-d"));
//        $a->where_related('premiums',"Paid",0);
        $a->group_start();
        $a->where_related('scheme',"SchemeType",'recurring');
        $a->or_where_related('scheme',"SchemeType",'FixedAndMis');
        $a->or_where_related('scheme',"SchemeType",'DDS');
        $a->group_end();
        $a->get();


        $data['weeklyduesToGive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );
	
	
		
        $a= new Account();
        $a->select('*, id as ActualCurrentBalance');
        $a->select('premiums_jos_xpremiums.DueDate as DueDate,premiums_jos_xpremiums.Amount as Amount');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
//        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where_field_func('MONTH(DueDate)', "MONTH",getNow("Y-m-d"));
        $a->where_field_func('YEAR(DueDate)', "YEAR",getNow("Y-m-d"));
        $a->where_related('premiums',"Paid",0);
        $a->limit(300,JRequest::getVar('page_start',0)*300);
        $a->get();


        $data['monthlyduesToReceive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true,"sno_start"=>JRequest::getVar('page_start',0)*300+1,"page"=>true,'page_var'=>'page_start'),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );


//        $data['monthlyduesToReceive'] = $this->db->query("select a.AccountNumber as Accnum, p.Amount as amount, p.DueDate as DueDate from jos_xpremiums p inner join jos_xaccounts a on a.id=p.accounts_id where MONTH(p.DueDate) = MONTH('". getNow("Y-m-d")."') and YEAR(p.DueDate)=YEAR('". getNow("Y-m-d")."') and p.Paid = 0 and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where")->result();
//        $data['monthlyduesToGive'] = $this->db->query("select a.AccountNumber as Accnum, (a.CurrentBalanceCr + a.CurrentInterest - a.CurrentBalanceDr)  as amount, DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH) as DueDate from  jos_xaccounts a inner join jos_xschemes s on s.id=a.schemes_id where MONTH(DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH)) =MONTH('". getNow("Y-m-d")."') and YEAR(DATE_ADD(a.created_at, INTERVAL s.MaturityPeriod MONTH))=YEAR('". getNow("Y-m-d")."') and (s.SchemeType='".ACCOUNT_TYPE_FIXED."' or s.SchemeType='".ACCOUNT_TYPE_RECURRING."' ) and a.ActiveStatus = 1 and a.LockingStatus = 0 and a.DefaultAC = 0 $where")->result();

	       $a= new Account();
//        $a->select('*, id as ActualCurrentBalance');
        $a->select('*,jos_xaccounts.AccountNumber as AccountNumber, round((CurrentBalanceCr + CurrentInterest - CurrentBalanceDr)) as Amount');
        $a->select_func("DATE_ADD",'[date(jos_xaccounts.created_at),INTERVAL jos_xschemes.MaturityPeriod MONTH]','DueDate');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('agent/member','Name');
        $a->include_related('dealer','DealerName');
//        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where_field_func('MONTH(DATE_ADD(DATE(jos_xaccounts.created_at), INTERVAL jos_xschemes.MaturityPeriod MONTH)) = ', "MONTH",getNow("Y-m-d"));
        $a->where_field_func('YEAR(DATE_ADD(DATE(jos_xaccounts.created_at), INTERVAL jos_xschemes.MaturityPeriod MONTH))= ', "YEAR",getNow("Y-m-d"));
//        $a->where_related('premiums',"Paid",0);
        $a->group_start();
        $a->where_related('scheme',"SchemeType",'recurring');
        $a->or_where_related('scheme',"SchemeType",'FixedAndMis');
        $a->or_where_related('scheme',"SchemeType",'DDS');
        $a->group_end();
        $a->get();


        $data['monthlyduesToGive'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );


	


	   $a= new Account();
        $a->select('*, id as ActualCurrentBalance');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');
        $a->where('DefaultAC',0);
        $a->where('ActiveStatus',1);
        $a->where('created_at like',"%".getNow("Y-m-d")."%");
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->get();
        $data['AccountsOpenedToday'] = getReporttable($a,             //model
                array("Account Number","Member Name","Father Name","Address", "Phone Number","Scheme", "Balance"),       //heads
                array('AccountNumber','member_Name','member_FatherName','member_CurrentAddress', 'member_PhoneNos','scheme_Name',"ActualCurrentBalance"),       //fields
                array("ActualCurrentBalance"),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );


//        $data['AccountsOpenedToday'] = $this->db->query("select * from jos_xaccounts a where a.created_at like '".  getNow("Y-m-d")." %' $where")->result();
        
        $a=new Account();
        $a->where_related('scheme','Name',CASH_ACCOUNT_SCHEME);
        $a->select('*, id as ActualCurrentBalance');
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin"){
            $b = Branch::getCurrentBranch();
            $a->where("branch_id",$b->id);
        }
        $a->get();
        $get_date=getNow('Y-m-d');
        $next_date=date('Y-m-d',strtotime(date("Y-m-d", strtotime($get_date)) . " +1 day"));
        $data['report_cash']=getReporttable($a,             //model
                array("Account Number","Openning Balance","Todays Receive","Todays Payment", "Balance",),       //heads
                array('AccountNumber',"~(#getOpeningBalance('$get_date',null,'dr'))","~(#getTransactionSUM('$get_date','$get_date','dr'))","~(#getTransactionSUM('$get_date','$get_date','cr'))", "~(#getOpeningBalance('$next_date',null,'dr'))"),       //fields
                array("~(#getOpeningBalance('$next_date',null,'dr'))"),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<h3>Cash Report</h3>",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );
        
        $a=new Account();
        $a->where_related('scheme','Name',BANK_ACCOUNTS_SCHEME);
        $a->select('*, id as ActualCurrentBalance');
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin"){
            $b = Branch::getCurrentBranch();
            $a->where("branch_id",$b->id);
        }
        $a->get();

        $data['report_bank']=getReporttable($a,             //model
                array("Account Number","Openning Balance","Todays Receive","Todays Payment", "Balance",),       //heads
                array('AccountNumber',"~(#getOpeningBalance('$get_date',null,'dr'))","~(#getTransactionSUM('$get_date','$get_date','dr'))","~(#getTransactionSUM('$get_date','$get_date','cr'))", "~(#getOpeningBalance('$next_date',null,'dr'))"),       //fields
                array("~(#getOpeningBalance('$next_date',null,'dr'))"),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<h3>Bank Report</h3>",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );
        
        $data['CashAsOnToday'] = $this->db->query("select sum(t.amountDr) as Dr, sum(t.amountCr) as Cr from jos_xtransactions t join jos_xaccounts a on a.id=t.accounts_id join jos_xschemes s on s.id = a.schemes_id where s.`Name` = '" . CASH_ACCOUNT_SCHEME . "' and t.created_at like '".  getNow("Y-m-d")." %' $where")->row();
        $data['BankAsOnToday'] = $this->db->query("select sum(t.amountDr) as Dr, sum(t.amountCr) as Cr from jos_xtransactions t join jos_xaccounts a on a.id=t.accounts_id join jos_xschemes s on s.id = a.schemes_id where s.`Name` = '" . BANK_ACCOUNTS_SCHEME . "' and t.created_at like '".  getNow("Y-m-d")." %' $where")->row();
        
        $a= new Account();
        $a->select_func("DATE_ADD","[LoanInsurranceDate]", "[INTERVAL +365 DAY]","EndInsuranceDate");
        $a->select('*');
        $a->include_related('dealer','DealerName');
        $a->include_related('dealer','Address');
        $a->include_related('scheme','Name');
        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PermanentAddress');
        $a->include_related('member','PhoneNos');
        // $a->select_subquery('(SELECT Description From jos_xdocuments_submitted doc WHERE doc.accounts_id=${parent}.id AND doc.documents_id='.inp('Documents_Submitted').')','Documents');
        $a->where('branch_id',Branch::getCurrentBranch()->id);
        // $a->where_related('dealer','DealerName like \'%'.inp('DealerName').'%\'');
        $a->having("EndInsuranceDate between '".getNow('Y-m-d')."' and '".myDateAdd(getNow('Y-m-d'),30)."'");
        $a->get();
        // echo $a->check_last_query();
        $data['loan_insurance_due_report']=getReporttable($a,             //model
            array("Account Number","Member Name","Father Name","Address", "Mobile", "Loan Insurance End Date",'DealerName'),       //heads
            array('AccountNumber','member_Name','member_FatherName','member_PermanentAddress','member_PhoneNos','EndInsuranceDate','dealer_DealerName'),       //fields
            array(),        //totals_array
            array(),        //headers
            array('sno'=>true),     //options
            "<h3>Loan Insurance Due report </h3>",     //headerTemplate
            '',      //tableFooterTemplate
            "",      //footerTemplate,
            array()
            );

        // $data['loan_insurance_due_report']
        // $data['insuranceDueList'] = $this->db->query("select a.AccountNumber,m.Name,m.PermanentAddress,m.PhoneNos,a.LoanInsurranceDate,d.DealerName from jos_xaccounts a join jos_xmember m on a.member_id = m.id join jos_xdealer d on a.dealer_id=d.id  join jos_xschemes s on a.schemes_id = s.id where (a.LoanInsurranceDate <> '0000-00-00 00:00:00' or a.LoanInsurranceDate is not null) and s.SchemeType = '".ACCOUNT_TYPE_LOAN."' and DATE_ADD(a.LoanInsurranceDate, INTERVAL +365 DAY) >= DATE_ADD('".getNow("Y-m-d")."', INTERVAL -15 DAY) and  DATE_ADD(a.LoanInsurranceDate, INTERVAL +365 DAY) <= DATE_ADD('".getNow("Y-m-d")."', INTERVAL +1 DAY) $where")->result();

        $this->displayMenubar();
        
        $this->load->view('welcome.html', $data);
        $this->jq->getHeader();
    }

    function getMenubar() {
        $views = array();

        $views['index'] = 'Dashboard';
        $views['branch_cont.dashboard'] = 'Branches';
        $views['member_cont.dashboard'] = 'Member';
        $views['accounts_cont.index'] = 'Accounts';
        $views['schemes_cont.dashboard'] = 'Schemes';
        /*
          $views['ticketstates'] = 'Ticket Statuses';
          $views['frequents'] = 'Canned Text';
          $views['tools'] = 'Tools';
          $views['config'] = 'Configuration';
         *
         */

        return $views;
    }

    function displayMenubar() {
        $this->getMenubar();
        $views = $this->getMenubar();
//
        $left = array();
        if (isset($this->leftMenu)) {
            $left = $this->getLeftMenubar();
        }

        foreach ($views as $view => $title) {
            $current = strtolower(JRequest::getVar('view'));
            $active = ($view == $current );
            if (array_key_exists($current, $left) && $view == 'localization') {
                $active = true;
            }
            JSubMenuHelper::addEntry(JText::_($title), 'index.php?option=com_xbank&task=' . $view, $active);
        }
    }

    function schemewiseAccountDetails() {
        $where = "";
        if(JFactory::getUser()->username != "admin"){
                $b = Branch::getCurrentBranch();
                $where = "and a.branch_id = $b->id";
        }
        $q = "SELECT count(*) as Accounts, s.Name as scheme
        from jos_xaccounts a inner join jos_xschemes s on a.schemes_id=s.id
        where a.DefaultAC = 0 and a.ActiveStatus = 1 $where
        group by s.Name order by s.id
            ";
        $r = $this->db->query($q)->result();
        $a['result'] = "<table class='adminlist' align='center' width=100%><thead><tr align='center'><th>S No </th><th>Scheme </th><th>Number of Accounts</th></tr></thead><tbody>";
        $i=0;
        foreach ($r as $rr) {
        $i++;
            $a['result'] .= "<tr align='center'><td align='center'>$i</td><td align='center'>$rr->scheme</td><td align='center'>$rr->Accounts</td></tr>";
            
        }
        $a['result'] .= "</tbody></table>";
//        $a['data_url'] = "index.php?option=com_xbank&task=com_xbank.schemewiseAccountDetailsGraph";
//        $data['contents'] = $this->load->view('graph', $a, true);
//        $this->load->view('template', $data);
        return $a;
    }

    function schemewiseAccountDetailsGraph() {
        $this->load->library('ofc2');
        $where = "";
        $title = " all Branches";
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin"){
                $b = Branch::getCurrentBranch();
                $where = "and a.branch_id = $b->id";
                $title = " $b->Name";
        }
        $q = "SELECT count(*) as Accounts, s.SchemeType as scheme
        from jos_xaccounts a inner join jos_xschemes s on a.schemes_id=s.id
        where a.DefaultAC = 0 and a.ActiveStatus = 1 $where
        group by s.SchemeType
        having scheme <> 'Default'
            ";
        $r = $this->db->query($q)->result();
        $vals = array();
        foreach ($r as $rr) {
            $vals[] = new pie_value((int) $rr->Accounts, $rr->scheme . " (" . $rr->Accounts . ")");
//            $vals += array((int)$rr->Accounts,$rr->scheme);
        }
        $title = new title("Scheme Wise Account Details of $title");
        $pie = new pie();
        $pie->set_alpha(0.6);
        $pie->set_start_angle(35);
        $pie->add_animation(new pie_fade());
        $pie->add_animation(new pie_bounce(15));
        $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
        $pie->set_colours(array('#FF0000','#0f0f0f', '#FF368D', '#0FFC00', '#FF6600', '#0099FF','#01FC00'));
        $pie->set_values($vals);

        $chart = new open_flash_chart();
        $chart->set_title($title);
        $chart->add_element($pie);
        $chart->x_axis = null;
        echo $chart->toPrettyString();
    }

    public function get_data_pie()
    {
        $this->load->library('ofc2');

        $title = new title( 'Pork Pie, Mmmmm' );

        $pie = new pie();
        $pie->set_alpha(0.6);
        $pie->set_start_angle( 35 );
//        $pie->add_animation( new pie_fade() );
        $pie->add_animation(new pie_bounce(15));
        $pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
        $pie->set_colours( array('#1C9E05','#FF368D') );
        $pie->set_values( array(2,3,4,new pie_value(6.5, "hello (6.5)")) );

        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $pie );


        $chart->x_axis = null;

        echo $chart->toPrettyString();
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */