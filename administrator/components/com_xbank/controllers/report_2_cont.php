<?php

class report_2_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function agentWiseReportForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Agent Wise Report");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_2_cont.agentWiseReport")
                ->setColumns(2)
                ->lookupDB("Agent Name","name='agent_id' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.list_agents&format=raw",
 			array("a" => "b"),
			array("id","Name","Address","AccountNumber"),"id")
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();
     }


     function agentWiseReport(){
         xDeveloperToolBars::onlyCancel("report_2_cont.agentWiseReportForm", "cancel", "Agent Wise Report");

         $a=new Account();
         $a->select('COUNT(*) as NoOfAccounts');
         $a->select('SUM(RdAmount) as SumOfAccounts');
         $a->include_related('scheme','Name');
         $a->include_related('agent','id');
         $a->include_related('agent/member','Name');
         $a->include_related('agent/member','FatherName');
         $a->include_related('agent/member','PermanentAddress');
         $a->include_related('agent/member','PhoneNos');
         $a->include_related('agent','created_at');
         
         $a->group_by('schemes_id');
         $a->group_by('agent_id');
         
         $a->where('created_at >=',inp('fromDate'));
         $a->where('created_at <=',inp('toDate'));
         $a->where_related('agent','id',inp('agent_id'));
         

         $a->get(10);
         
        // echo $a->check_last_query();

         $data['report'] = getReporttable($a,             //model
                array("Scheme Type", "NoOfAccounts","Amount Sum","Remarks"),       //heads
                array('scheme_Name', "NoOfAccounts","SumOfAccounts",""),       //fields
                array("SumOfAccounts","NoOfAccounts"),        //totals_array
                array(
                	"Agent Name" => "agent_member_Name",
                	"Father / Husband Name" =>"agent_member_FatherName",
                	"Agent Code" => 'agent_id',
                	"Address "=> "agent_member_PermanentAddress",
                	"Phone Number"=>"agent_member_PhoneNos",
                	"Created At"=>"agent_created_at"
                	
                	
                	),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );



        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }
     
     
     function agentReportDeadAccountForm(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "Agent Wise Dead Report");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_2_cont.agentReportDeadAccount")
                ->setColumns(2)
                ->lookupDB("Agent Name","name='agent_id' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.list_agents&format=raw",
 			array("a" => "b"),
			array("id","Name","Address","AccountNumber"),"id")
                ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();
     }


     function agentReportDeadAccount(){
        xDeveloperToolBars::onlyCancel("report_2_cont.agentReportDeadAccountForm", "cancel", "Agent Wise Dead Account Report");

	$a=new Premium();
	$a->select('COUNT(*) as PremiumCount');
	$a->select('SUM(Amount) as PremiumDueSum');
	$a->select('MAX(Amount) as PremiumAmount');
	$a->include_related('account','id');
	$a->include_related('account/scheme','Name');
        $a->include_related('account','AccountNumber');
        $a->include_related('account/agent','id');
        $a->include_related('account/agent/member','Name');
        $a->include_related('account/agent/member','FatherName');
        $a->include_related('account/agent/member','PermanentAddress');
        $a->include_related('account/agent/member','PhoneNos');
        $a->include_related('account/member','Name');
        $a->include_related('account/member','FatherName');
        $a->include_related('account/member','PermanentAddress');
        $a->include_related('account/member','PhoneNos');
        $a->include_related('account/agent','created_at');
	$a->where('DueDate >=' ,inp('fromDate'));
	$a->where('DueDate <=', inp('toDate'));
	$a->where('PaidOn is null');
	$a->where_related('account','branch_id',Branch::getCurrentBranch()->id);
    $a->where_related('account','ActiveStatus',1);
	$a->where_related('account/agent','id',inp('agent_id'));
	$a->group_by('account_id');
	$a->get();
	
	//echo $a->check_last_query();
	
	 $data['report'] = getReporttable($a,             //model
                array("Account", "Name","Father Name","Address","Phone Nos",            "Scheme Type",          "NoOfPremiumsDue","Premium Amount","Amount Sum"),       //heads
                array('account_AccountNumber','account_member_Name','account_member_FatherName','account_member_PermanentAddress','account_member_PhoneNos',  'account_scheme_Name', "PremiumCount",    "PremiumAmount", "PremiumDueSum"),       //fields
                array("PremiumDueSum","PremiumAmount","PremiumCount"),        //totals_array
                array(
                	"Agent Name" => "account_agent_member_Name",
                	"Father / Husband Name" =>"account_agent_member_FatherName",
                	"Agent Code" => 'account_agent_id',
                	"Address "=> "account_agent_member_PermanentAddress",
                	"Phone Number"=>"account_agent_member_PhoneNos",
                	"Created At"=>"account_agent_created_at"
                	
                	
                	),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );



        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
	
	return;
        $data['result'] = $this->db->query($q)->result();
        JRequest::setVar("layout","rdpremiumduelist");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }
     
     function loanDocumentReportForm(){
     	xDeveloperToolBars::onlyCancel("report_cont.new_reports", "cancel", "Loan Document Report");
        $this->load->library("form");
        $this->form->open("pSearch","index.php?option=com_xbank&task=report_2_cont.loanDocumentReport")
                ->setColumns(2)
                ->lookupDB("Account Number","name='Account_Number' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=ajax.loan_report_account&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();	
     }
     
     function loanDocumentReport(){
     	xDeveloperToolBars::onlyCancel("report_2_cont.loanDocumentReportForm", "cancel", "Loan Document Report");

    	// $a=new Documents_submitted();
    	// $a->select("Description as Description");
    	// $a->where_related('account','AccountNumber', inp('Account_Number'));
    	// $a->include_related('document','Name');
    	// $a->include_related('submited_in_accounts','AccountNumber');

        $a=new Account();
        $a->where('AccountNumber',inp('Account_Number'));
        $a->get();
        $a->documents->include_join_fields();
        $a->documents->get();
        // $a=$a->documents;    	
    
    	// echo $a->check_last_query();
	
    	$data['report'] = getReporttable($a->documents,             //model
                array("Document Name","Description"),       //heads
                array('Name','join_Description'),       //fields
                array(),        //totals_array
                array(
                    "Account Number"=>'~'.$a->AccountNumber,
                    "Member Name" => '~'.$a->member->Name,
                    ),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );



        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
	
    	return;
     }

     function dealerWiseReport(){
        xDeveloperToolBars::onlyCancel('report_cont.new_reports','cancel','Dealer Wise report');
        $data['content']="hi there";

        $d=new Dealer();
        $d->get();

        $data['report']=getReporttable($d,             //model
                array("DealerName", "Address","Accounts Count"),       //heads
                array('DealerName','Address','~#accounts->where("branch_id",'.Branch::getCurrentBranch()->id.')->count()'),       //fields
                array('~#accounts->where("branch_id",'.Branch::getCurrentBranch()->id.')->count()'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array('~#accounts->where("branch_id",'.Branch::getCurrentBranch()->id.')->count()'=>array('task'=>'report_2_cont.dealerWiseAccountList','class'=>'alertinwindow', 'url_post'=>array('did'=>'#id','format'=>'"raw"')))//Links array('field'=>array('task'=>,'class'=>''))
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }
    
     function dealerWiseAccountList(){
        $d=new Dealer(inp('did'));
        $acc=$d->accounts->where('branch_id',Branch::getCurrentBranch()->id)->get();
        // $acc=$d->accounts->get();
        $data['report']=getReporttable($acc,             //model
                array("Account Number", "Branch"),       //heads
                array('AccountNumber','~#branch->Name'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<b>Dealer Accounts for $d->DealerName </b>",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array()
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }

     function noclist(){
        xDeveloperToolBars::onlyCancel("report_cont.new_reports", "cancel", "NOC List");
        $a=new Account();
        $a->include_related('member','Name');
        $a->where_related('scheme','SchemeType','Loan');
        $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->where('ActiveStatus',0);
        $a->where('DefaultAC',0);
        $a->get();
        $data['report'] = "<br/><br/><br/>" . getReporttable($a,             //model
                array("Account Number", "ActiveStatus",'Member'),       //heads
                array('AccountNumber','ActiveStatus','member_Name'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<b>All DeActivated Loan Accounts </b>",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate,
                array()
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }
}
