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
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jimport('joomla.html.toolbar');

class xDeveloperToolBars extends JObject {

    function getDefaultToolBar() {
        $xc = new xConfig('toolbar');
        JToolBarHelper::title('xBank Software for Bhawani Credit Co-op Society', 'generic.png');
        $u = JFactory::getUser();
        if ($u->username == 'admin'){
            JToolBarHelper::addNewX("config_cont.index", "Configurations");
            JToolBarHelper :: customX('customreport_cont.newReport', 'config', 'config', 'Custom Report', false, false);
        }
        // IF DEFAULT BRANCH IS NOT CREATED, SHOW THIS TOOLBAR
        $b = new Branch();
        $b->get();
        if ($b->result_count() == 0) {
            //JToolBarHelper::addNewX('branch_cont.addnewbranch', 'Setup Default Branch');
        } else {

            if ($xc->getkey('branch_toolbar'))
                //JToolBarHelper :: customX('branch_cont.dashboard', 'branch', 'branch', 'Branches', false, false);
            if ($xc->getkey('schemes_toolbar'))
                JToolBarHelper :: customX('schemes_cont.dashboard', 'schemes', 'schemes', 'Schemes', false, false);
            if ($xc->getkey('staff_toolbar'))
                JToolBarHelper::customX('staff_cont.dashboard', 'staff','staff','Staff',false,false);
            if ($xc->getkey('member_toolbar'))
                JToolBarHelper::customX('member_cont.dashboard', 'member','member',"Member",false,false);
            if ($xc->getkey('agent_toolbar'))
                    JToolBarHelper :: customX('agent_cont.dashboard', 'agent', 'agent', 'Agent', false, false);
            if ($xc->getkey('accounts_toolbar'))
                    JToolBarHelper :: customX('accounts_cont.index', 'accounts', 'accounts', 'Accounts', false, false);
            if ($xc->getkey('transaction_toolbar'))
                    JToolBarHelper :: customX('transaction_cont.index', 'transactions', 'transactions', 'Transactions', false, false);
            if ($xc->getkey('report_toolbar'))
                    JToolBarHelper :: custom('report_cont.dashboard', 'reports', 'reports', 'Reports', false, false);
            if ($xc->getkey('setdate_toolbar'))
                    JToolBarHelper :: customX('setdate_cont.setDateTimeForm', 'setdate', 'setdate', 'SetDate', false, false);
                    
            JToolBarHelper::addNew('report_cont.new_reports', 'New Reports');
            JToolBarHelper::addNewX('inventory_cont.dashboard', 'Inventory');
                    
             if ($u->username != 'admin')
                     JToolBarHelper :: customX('customreport_cont.index', 'config', 'config', 'Custom Report', false, false);
            if($u->gid >= 24)
                     JToolBarHelper :: customX('documents_cont.documentForm', 'config', 'config', 'Documents', false, false);

            JToolBarHelper :: customX('utility_cont.index', 'backup', 'backup', 'Utility', false, false);
            JToolBarHelper :: customX('search_cont.dashboard', 'search', 'search', 'Search', false, false);
        }
        JToolBarHelper::preferences(JRequest::getCmd('option'), '500');
    }

    function getBranchManagementToolBar() {
        JToolBarHelper::title('Manage Your Branches Here', 'generic.png');
        $u = JFactory::getUser();
        if ($u->username == 'admin')
            JToolBarHelper::addNewX('branch_cont.addnewform', 'New Branch');
        JToolBarHelper::cancel('com_xbank.index', 'cancle');
    }

    function getSchemesManagementToolBar() {
        JToolBarHelper::title('Manage Your Schemes Here', 'generic.png');
        $u = JFactory::getUser();
        if ($u->usertype == 'Super Administrator' || $u->usertype == 'Administrator')
            JToolBarHelper::addNewX('schemes_cont.addnewform', 'New Scheme');
        JToolBarHelper::cancel('com_xbank.index', 'cancle');
    }

    function getMemberManagementToolBar() {
        JToolBarHelper::title('Manage Branch Members Here', 'generic.png');
        JToolBarHelper::addNewX('member_cont.addmemberform', 'New Member');
        JToolBarHelper::addNewX('member_cont.dealerform', 'New Dealer');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function getAgentManagementToolBar() {
        JToolBarHelper::title('Manage Branch Agent Here', 'generic.png');
        JToolBarHelper::addNewX('agent_cont.commissionReportFrom', 'Agent Commission Report');
        JToolBarHelper::addNewX('agent_cont.createAgentform', 'New Agent');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function getStaffManagementToolBar() {
        JToolBarHelper::title('Manage Branch Staff Here', 'generic.png');
        JToolBarHelper::addNewX('staff_cont.createStaffform', 'New Staff');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function getAccountsManagementToolBar() {
        JToolBarHelper::title('Manage Your Accounts Here', 'generic.png');
         $u = JFactory::getUser();
        if ($u->gid < 24)
            JToolBarHelper::addNewX('accounts_cont.NewAccountForm', 'New Account');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function getTransactionManagementToolBar() {
        JToolBarHelper::title('Do Transactions Here', 'generic.png');
        JToolBarHelper::addNewX('transaction_cont.deposit', 'Deposit');
        JToolBarHelper::addNewX('transaction_cont.withdrawl', 'Withdraw');
        JToolBarHelper::addNewX('transaction_cont.jv', 'JV');
        JToolBarHelper::addNewX('transaction_cont.forClose', 'For Close');
        JToolBarHelper::cancel('com_xbank.index', 'cancle');
    }

    function getSearchManagementToolBar() {
        JToolBarHelper::title('Search Members and Accounts Here', 'generic.png');
        JToolBarHelper::addNewX('search_cont.searchMemberForm', 'Member Search');
        JToolBarHelper::addNewX('search_cont.searchAccountForm', 'Account Search');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function onlyCancel($toGo, $text='cancel', $title="") {
        JToolBarHelper::title($title, 'generic.png');
        JToolBarHelper::cancel($toGo, $text);
    }

    function configToolBar() {
        JToolBarHelper::title('Manage Configurations Here', 'generic.png');
        JToolBarHelper::preferences('com_xbank', '500');
        JToolBarHelper::cancel('com_xbank.index');
    }

    function configEditToolBar($configFile) {
        JToolBarHelper::title("Edit $configFile Config Here", 'generic.png');
        JToolBarHelper::save('config_cont.saveConfig');
        JToolBarHelper::cancel('config_cont.index');
    }

    function getReportManagementToolBar() {
        JToolBarHelper::title('View Reports Here', 'generic.png');
        JToolBarHelper::addNewX('report_cont.balanceSheetForm', 'Balance<br/> Sheet');
        //JToolBarHelper::addNewX('report_cont.pandlForm', 'Profit & Loss A/c');
        JToolBarHelper::addNewX('report_cont.accountstatementform', 'Account<br/> Statement');
        //JToolBarHelper::addNewX('report_cont.AccountBook', 'Account Books');
        JToolBarHelper::addNewX('report_cont.trialbalanceForm', 'Trial<br/> Balance');
        JToolBarHelper::addNewX('report_cont.shareCertificateForm', 'Share<br/> Certificate');
        JToolBarHelper::addNewX('report_cont.loan_report', 'Loan<br/> Report');
        JToolBarHelper::addNewX('report_cont.allschemedetailsform', 'Periodic<br/> Account Details');
        //JToolBarHelper::addNewX('report_cont.premiums_report', 'Premium Report');
        JToolBarHelper::addNewX('report_cont.loanAccountReportForm', 'Loan <br/>Detailed/Premium<br/> Report');
        JToolBarHelper::addNewX('report_cont.loan_insurrance_report_form', 'Loan <br/>Insurrance<br/> Report');
        JToolBarHelper::addNewX('report_cont.rdPremiumReceivedListForm', 'RD Premium <br/>Received List');
        JToolBarHelper::addNewX('report_cont.vlEMIReceivedListForm', 'VL EMI<br/> Received List');
        JToolBarHelper::addNewX('report_cont.plAndOtherEMIReceivedListForm', 'PL & Other EMI<br/> Received List');
        JToolBarHelper::addNewX('report_cont.tdsReportForm', 'TDS<br/> Report');
        JToolBarHelper::addNewX('report_2_cont.agentWiseReportForm', 'AgentWise<br/> Report');
        JToolBarHelper::addNewX('report_2_cont.agentReportDeadAccountForm', 'Agent Report<br/> Dead Account');


        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function getAccountBookManagementToolBar() {
        JToolBarHelper::title('View AccountBooks Here', 'generic.png');
        JToolBarHelper::addNewX('report_cont.cashBookForm', 'Cash Book');
        JToolBarHelper::addNewX('report_cont.dayBookForm', 'Day Book');
        JToolBarHelper::cancel('report_cont.dashboard', 'cancel');
    }

    function getCustomReportsToolBar(){
        $report = new report();
        $report->where("published",1)->get();
        JToolBarHelper::title('View other Reports Here', 'generic.png');
        foreach($report as $r){
            JToolBarHelper::addNewX("customreport_cont.showTestForm_$r->id", "$r->ReportTitle");
        }
        JToolBarHelper::cancel("com_xbank.index", 'cancel');

    }

    function getUtilityManagementToolBar(){
        JToolBarHelper::title('Other Utilities', 'generic.png');
        JToolBarHelper::addNewX('utility_cont.backup', 'Backup Database');
        JToolBarHelper::addNewX('utility_cont.memberRegistrationDateChangeForm', 'Change Member Registration Date');
        if(JFactory::getUser()->gid >= 24)
        	JToolBarHelper::addNewX('report_cont.premiumCrudForm', 'Premiums CRUD');
//             JToolBarHelper::addNewX('utility_cont.selectRdAccount', 'Adjust RD Premiums');
        JToolBarHelper::addNewX('utility_cont.loanEmiChangeForm', 'Change Loan Account EMI');
        JToolBarHelper::addNew('utility_cont.errorReport', 'Bug Finder');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }
    
    function getNewReportManagementToolBar(){
        JToolBarHelper::title('View Reports Here', 'generic.png');
        JToolBarHelper::addNewX('report_cont.loan_insurrance_due_report_form', 'Loan Insurrance <br/>Due List');
        JToolBarHelper::addNewX('report_cont.deposit_insurrance_due_report_form', 'Deposit Insurance <br/>Due Account Details');
        JToolBarHelper::addNewX('report_cont.loanEMIDueListForm', 'Loan EMI <br/>Due List');
        JToolBarHelper::addNewX('report_cont.plEMIDueListForm', 'PL EMI <br/>Due List');
        JToolBarHelper::addNewX('report_cont.loanNPAListForm', 'NPA List');
        JToolBarHelper::addNewX('report_cont.loanHardRecoveryListForm', 'Hard <br/>Recovery List');
        JToolBarHelper::addNewX('report_cont.RDPremiumDueListForm', 'RD Premium<br/> Due List');
        JToolBarHelper::addNewX('report_cont.loanReceiptReportForm', 'Loan <br/>Dispatch');
        JToolBarHelper::addNewX('report_2_cont.loanDocumentReportForm', 'Loan Document<br/> Report');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }
    
     function getInventoryManagementToolBar(){
        JToolBarHelper::title('Inventory & Stock Management', 'generic.png');
        JToolBarHelper::addNewX('inventory_cont.newCategoryForm', 'Category');
        JToolBarHelper::addNewX('inventory_cont.newItemForm', 'Item');
        JToolBarHelper::addNewX('inventory_cont.addNewStockForm', 'Stock');
        JToolBarHelper::addNewX('inventory_cont.manageStockForm', 'Stock Management');
        JToolBarHelper::cancel('com_xbank.index', 'cancel');
    }

    function getNewReportSubMenus(){
        JSubMenuHelper::addEntry("Dealer list", 'index.php?option=com_xbank&task=report_2_cont.dealerWiseReport', false);
        JSubMenuHelper::addEntry("NOC list", 'index.php?option=com_xbank&task=report_2_cont.noclist', false);
    }


}

?>