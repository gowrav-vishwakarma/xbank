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
	$a->select_subquery('(SELECT MAX(DueDate) FROM jos_xpremiums WHERE accounts_id=${parent}.accounts_id)','LastEmiDate');
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
    // $a->where_related('account','branch_id',Branch::getCurrentBranch()->id); // REMOVED TO GET ALL ACCOUNTS OF THIS AGENT IN ALL BRANCHES
    $a->where_related('account','ActiveStatus',1);
    
    $a->where_related('account/agent','id',inp('agent_id'));
	$a->having('LastEmiDate >= "'.getNow('Y-m-d').'"');
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

     function timeCollepsedDueList(){
        xDeveloperToolBars::onlyCancel("report_cont.new_reports", "cancel", "Time Collepsed Due List");
        
        $a= new Account();

    
        $a->include_related('dealer','DealerName');
        $a->include_related('agent/member','Name');
        $a->include_related('agent/member','PhoneNos');

        $a->select_subquery('(SELECT MAX(Amount) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','Amount');
        $a->select_subquery('(SELECT MAX(DueDate) From jos_xpremiums p WHERE p.accounts_id=${parent}.id)','lastPremium');
        $a->select_subquery('(SELECT SUM(amountDr) From jos_xtransactions t WHERE t.accounts_id=${parent}.id)','TotalDrTransactions');
        $a->select_subquery('(SELECT SUM(amountCr) From jos_xtransactions t WHERE t.accounts_id=${parent}.id)','TotalCrTransactions');

        $p_paid = new Premium();
        $p_paid->select_func('COUNT', '*', 'count');
        $p_paid->where("PaidOn is not null");
        // $p_paid->where("DueDate between '".inp("fromDate")."' and '".inp("toDate")."'");
        $p_paid->where_related('account', 'id', '${parent}.id');
        $a->select_subquery($p_paid,'PaidPremiumCount');

        $p = $a->premiums;
        $p->select_func('COUNT', '*', 'count');
        $p->where("PaidOn is null");
        // $p->where("DueDate between '".inp("fromDate")."' and '".inp("toDate")."'");
        $p->where_related('account', 'id', '${parent}.id');

        $a->select_subquery($p,'DuePremiumCount');

        $a->include_related('member','Name');
        $a->include_related('member','FatherName');
        $a->include_related('member','PhoneNos');
        $a->include_related('member','CurrentAddress');
        $a->include_related('scheme','Name');
        $a->select('*, id as PaneltyDUE, id as OtherCharges');

        $a->where_related('scheme','SchemeType like' ,'loan');
        $a->where_related('dealer',"DealerName like '%".inp('DealerName')."%'");
        $a->where("ActiveStatus",1);
        $a->where("((OpeningBalanceDr + CurrentBalanceDr)-(OpeningBalanceCr + CurrentBalanceCr)) <>",0);
        $a->having("lastPremium < '".getNow('Y-m-d')."'");
        if(JFactory::getUser()->username != "admin" && JFactory::getUser()->username != "xadmin")
            $a->where("branch_id",Branch::getCurrentBranch()->id);

        $a->get();
        //echo $a->check_last_query();
        $data['report']= getReporttable($a,             //model
                array("Account Openning Date",'Last Premium',"Account Number","Scheme","Member Name","Father Name", "Phone Number","Address","Paid Premium Count","Due Premium Count",'EMI Amount',"Due Penalty","Legal/Conveyance/Insurance Charge", 'Total',"Dealer Name","Guarantor Name","Guarantor Address","Guarantor Phone"),       //heads
                array('~date("Y-m-d",strtotime("#created_at"))','~date("Y-m-d",strtotime("#lastPremium"))','AccountNumber', 'scheme_Name','member_Name','member_FatherName','member_PhoneNos','member_CurrentAddress',"PaidPremiumCount","DuePremiumCount", 'Amount','PaneltyDUE', 'OtherCharges',"~(#OpeningBalanceDr + #TotalDrTransactions - #TotalCrTransactions)",'dealer_DealerName','Nominee','MinorNomineeParentName','RelationWithNominee'),       //fields
                array('~(#OpeningBalanceDr + #TotalDrTransactions - #TotalCrTransactions)'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }

     function closingBalanceForm(){
        xDeveloperToolBars::onlyCancel("customreport_cont.index", "cancel", "Closing Balances Of Accounts");

        $s=new Scheme();
        $s->select('DISTINCT(SchemeGroup)');
        $s->group_by('SchemeGroup');
        $s->get();
        // echo $s->check_last_query();
        $scheme_groups=array();
        $scheme_groups +=array("All" => "%");
        $i=0;
        foreach($s as $ss){
            $scheme_groups += array($ss->SchemeGroup => $ss->SchemeGroup);
        }

        $this->form->open("accountOpenningForm","index.php?option=com_xbank&task=report_2_cont.closingBalanceReport")
                ->setColumns(2)
                // ->dateBox("Select Date From","name='fromDate' class='input'")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->select("For","name='scheme_group'",$scheme_groups)
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader(); 

     }

     function closingBalanceReport(){
        xDeveloperToolBars::onlyCancel("report_2_cont.closingBalanceForm", "cancel", "Closing Balances Of Accounts");
        $t=new Transaction();
        $t->select_func('sum','[amountCr-amountDr]','Amount');
        // $t->where('created_at >= "'. inp('fromDate').'"');
        $t->where('created_at <= "'. (nextDate('toDate')==""?nextDate():nextDate('toDate')). '"');
        $t->include_related('account','OpeningBalanceCr');
        $t->include_related('account','OpeningBalanceDr');
        $t->include_related('account','AccountNumber');
        $t->include_related('account/member','Name');
        $t->include_related('account/member','FatherName');
        $t->include_related('account/member','PermanentAddress');
        $t->include_related('account/member','PhoneNos');
        if(inp('scheme_group') != "%"){
            $t->where_related('account/scheme','SchemeGroup',inp('scheme_group'));
        }
        $t->where('branch_id',Branch::getCurrentBranch()->id);
        $t->group_by('accounts_id');
        $t->get();
        // echo $t->check_last_query();

        $data['report']= getReporttable($t,             //model
                array("Account Number",'Name',"Father/Husband Name",'Address','Phone Number',"Closing Balance"),       //heads
                array('account_AccountNumber','account_member_Name','account_member_FatherName','account_member_PermanentAddress','account_member_PhoneNos',
                    '~(abs((#account_OpeningBalanceCr - #account_OpeningBalanceDr) + #Amount))'
                ),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();

     }

     function FDInterestProvisionReportForm(){
        xDeveloperToolBars::onlyCancel("customreport_cont.index", "cancel", "Closing Balances Of Accounts");
        $this->form->open("accountOpenningForm","index.php?option=com_xbank&task=report_2_cont.FDInterestProvisionReport")
                ->dateBox("Select Date till","name='toDate' class='input'")
                ->submit("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader(); 

     }

     function FDInterestProvisionReport(){
        xDeveloperToolBars::onlyCancel("report_2_cont.FDInterestProvisionReportForm", "cancel", "FD Interest Provision");
        
        $t=new Transaction();
        $t->include_related('account','AccountNumber');
        $t->include_related('account/scheme','Name');
        $t->include_related('account','RdAmount');
        $t->include_related('referenceaccount','AccountNumber');

        $t->where_related('account','schemes_id',19);
        $t->get();
        // echo $t->check_last_query();

        $data['report']= getReporttable($t,             //model
                array("Account Number","FD PLan","Amount","Rounded Interest"),       //heads
                array('referenceaccount_AccountNumber','account_scheme_Name','account_RDAmount',),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );

        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }

     function dds_commission_and_tds_report_form(){
        xDeveloperToolBars::onlyCancel("report_cont.dashboard", "cancel", "DDS Commission and TDS Report");
        $this->form->open("one","index.php?option=com_xbank&task=report_2_cont.dds_commission_and_tds_report")
            ->dateBox("DDS Commission For date","name='toDate' class='input'")
            ->submit("go");

        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader(); 

     }

     function dds_commission_and_tds_report(){
        xDeveloperToolBars::onlyCancel("report_2_cont.dds_commission_and_tds_report_form", "cancel", "DDS Commission and TDS Report");
        
        $m=date('m',strtotime(inp('toDate')));
        $y=date('Y',strtotime(inp('toDate')));
        $fromDate="$y-$m-01";
        $toDate=inp('toDate');
        echo $toDate;
        
        $t=new Transaction();

        $t->select('*');

        $t->select_subquery('(SELECT amountCr FROM jos_xtransactions  WHERE voucher_no=${parent}.voucher_no AND side="CR" AND branch_id=${parent}.branch_id ORDER BY id LIMIT 1)','Commission');
        $t->select_subquery('(SELECT amountCr FROM jos_xtransactions  WHERE voucher_no=${parent}.voucher_no AND side="CR" AND branch_id=${parent}.branch_id ORDER BY id LIMIT 1,1)','Tds');
        
        $t->include_related('referenceaccount','AccountNumber');
        $t->include_related('referenceaccount/scheme','Name');
        $t->include_related('referenceaccount/agent/member','Name');
        $t->where('transaction_type_id',11); //DDS Account amount deposited
        // $t->where_related('account/scheme','SchemeType','DDS');
        $t->where_related('referenceaccount/scheme','SchemeType','DDS');
        $t->where('amountCr <>',0);
        $t->where('created_at >=',$fromDate);
        $t->where('created_at <',nextDate('toDate'));
        $t->where('branch_id',Branch::getCurrentBranch()->id);
        // $t->having ('Commission is not null');
        $t->group_by('reference_account_id');


        $t->get_iterated();

        // echo $t->check_last_query();

        $msg="DDS Commission and TDS Report on " . inp('toDate');
        $data['report'] = getReporttable($t,             //model
                array("Account Number", "SchemeName" ,"Commission Paid", 'TDS',"Total commision" ,"Agent Name" , "Voucher_no"),       //heads
                array('referenceaccount_AccountNumber','referenceaccount_scheme_Name' ,'Commission',"Tds",'~(#Commission + #Tds)' ,'referenceaccount_agent_member_Name' ,'display_voucher_no'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<h3>". $msg . "</h3>",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate
                array('display_voucher_no'=>array(
                                            'task'=>'report_cont.transactionDetails',
                                            'class'=>'alertinwindow',
                                            'title'=>'_blank',
                                            'url_post'=>array('vn'=>'#voucher_no','format'=>'"raw"','tr_type'=>'#transaction_type_id','branch_id'=>'#branch_id')
                                        ))
                );


        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }

  function dds_commission_and_tds_report_old(){
        xDeveloperToolBars::onlyCancel("report_2_cont.dds_commission_and_tds_report_form", "cancel", "DDS Commission and TDS Report");
        
        $m=date('m',strtotime(inp('toDate')));
        $y=date('Y',strtotime(inp('toDate')));
        $fromDate="$y-$m-01";
        $toDate=inp('toDate');
        
        $t=new Transaction();
        
        $t->select_func('SUM','[amountCr]','AmountSubmitted');

        $t->select_subquery('(SELECT GROUP_CONCAT(display_voucher_no) FROM jos_xtransactions  WHERE reference_account_id=${parent}.accounts_id AND created_at="'.inp('toDate').' 00:00:00" AND side="DR" AND branch_id=${parent}.branch_id LIMIT 1)','display_voucher_no');
        $t->select_subquery('(SELECT GROUP_CONCAT(voucher_no) FROM jos_xtransactions  WHERE reference_account_id=${parent}.accounts_id AND created_at="'.inp('toDate').' 00:00:00" AND side="DR" AND branch_id=${parent}.branch_id LIMIT 1)','voucher_no');
        $t->select_subquery('(SELECT SUM(amountDr) FROM jos_xtransactions  WHERE jos_xtransactions_subquery.reference_account_id=${parent}.accounts_id AND jos_xtransactions_subquery.created_at="'.inp('toDate').' 00:00:00" AND jos_xtransactions_subquery.side="DR" AND jos_xtransactions_subquery.branch_id=${parent}.branch_id  GROUP BY jos_xtransactions_subquery.reference_account_id)','Commission');
        // $t->select_subquery('(SELECT amountCr FROM jos_xtransactions  WHERE reference_account_id=${parent}.accounts_id AND created_at="'.inp('toDate').' 00:00:00" AND side="CR" AND branch_id=${parent}.branch_id limit 1)','NET');
        // $t->select_subquery('(SELECT amountCr FROM jos_xtransactions  WHERE reference_account_id=${parent}.accounts_id AND created_at="'.inp('toDate').' 00:00:00" AND side="CR" AND branch_id=${parent}.branch_id limit 1,1)','TDS');
        
        $t->include_related('account','AccountNumber');
        $t->include_related('account/scheme','Name');
        $t->include_related('account/agent/member','Name');
        $t->where('transaction_type_id',20); //DDS Account amount deposited
        $t->where_related('account/scheme','SchemeType','DDS');
        $t->where('created_at >=',$fromDate);
        $t->where('created_at <',nextDate('toDate'));
        $t->where('branch_id',Branch::getCurrentBranch()->id);
        $t->having ('Commission is not null');
        $t->group_by('accounts_id');


        $t->get_iterated();

        echo $t->check_last_query();

        $msg="DDS Commission and TDS Report on " . inp('toDate');
        $data['report'] = getReporttable($t,             //model
                array("Account Number", "Amount Submitted" , "SchemeName" ,"Commission", 'TDS',"Net Amount" ,"Agent Name" , "Voucher_no"),       //heads
                array('account_AccountNumber','AmountSubmitted','account_scheme_Name' ,'Commission',"~(#Commission*10/100)",'~(#Commission - (#Commission*10/100))' ,'account_agent_member_Name' ,'display_voucher_no'),       //fields
                array(),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<h3>". $msg . "</h3>",     //headerTemplate
                '',      //tableFooterTemplate
                "",      //footerTemplate
                array('display_voucher_no'=>array(
                                            'task'=>'report_cont.transactionDetails',
                                            'class'=>'alertinwindow',
                                            'title'=>'_blank',
                                            'url_post'=>array('vn'=>'#voucher_no','format'=>'"raw"','tr_type'=>'#transaction_type_id','branch_id'=>'#branch_id')
                                        ))
                );


        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();
     }
}
