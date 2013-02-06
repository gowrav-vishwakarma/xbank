<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class search_cont extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function dashboard() {
        xDeveloperToolBars::getSearchManagementToolBar();
       
        $this->load->view("search.html");
       $this->jq->getHeader();
    }

    /**
     * function to search for all existing accounts
     * sends the link to {@link searchAccount}
     * - generate form
     */
    function searchAccountForm($return=false) {
        if(!$return)
        xDeveloperToolBars::onlyCancel("search_cont.dashboard", "cancel", "Search Accounts");
        global $com_params;
        $accountTypeArray = explode(",", $com_params->get('ACCOUNT_TYPES'));
        $accountTypeArray = array_merge(array("Any"), $accountTypeArray);
        $accountTypeArray = array_combine($accountTypeArray, $accountTypeArray);
        //$documents = $this->db->query("Select * from jos_xdocuments")->result();
        $i = 1;
        //setInfo("SEARCH ACCOUNT", "");
        $this->load->library('form');
        $form = $this->form->open("one", 'index.php?option=com_xbank&task=search_cont.searchAccount')
                        ->setColumns(2)
                        // ->text("Account Number", "name='AccountNumber' class='input'")
                        ->lookupDB("Account Number", "name='AccountNumber' class='input ui-autocomplete-input' hint='use % sign for unknown values like SB%%024'", "index.php?option=com_xbank&task=transaction_cont.AccountNumber&format=raw",
                        array("a"=>"b"),
                        array("AccountNumber", "Name", "Balance", "Scheme"), "AccountNumber")
                        ->select("Account Type", "name='SchemeType'", $accountTypeArray)
                        ->lookupDB("Member ID", "name='UserID' class='input' hint='Search account belongs to this member' ", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->select("Active Status", "name='ActiveStatus'", array("Any" => '%', "Active" => '1', "DeActive" => '0'))
                        ->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                        ->_()
                        ->submit('Search');
        $data['contents'] = $this->form->get();
        if(!$return){
            JRequest::setVar("layout", "accountsearchform");
            $this->load->view('search.html', $data);
            $this->jq->getHeader();
        }else
            return $data['contents'];
        //$this->load->view('template', $data);
    }

    /**
     * function simply search for accounts based on input given in searchAccountForm
     */
    function searchAccount() {
        xDeveloperToolBars::onlyCancel("search_cont.dashboard", "cancel", "Search Accounts");

        if(inp('AccountNumber')=='' and inp('UserID')=='')
            re('search_cont.searchAccountForm','Either AccountNumber or UserID must be filled','error');

        $query = "select a.*,m.id as MemberID, m.Name as MemberName, s.Name as SchemeName from jos_xaccounts a left join jos_xmember m on a.member_id=m.id join jos_xschemes s on s.id=a.schemes_id ";
//                $join=" join documents_submitted ds on a.id=ds.accounts_id join documents d on d.id=ds.documents_id";
        $where = " where ";
        $where1 = "";
        $flag = 0;
        if (inp("AccountNumber") != "") {
            $where1 .=" a.AccountNumber like '%" . inp("AccountNumber") . "%' AND ";
            $flag = 1;
        }
        if (inp("SchemeType") != "Any") {
            $where1 .="  s.SchemeType like '%" . inp("SchemeType") . "%' AND ";
            $flag = 1;
        }
        if (inp("UserID") != "") {
            $where1 .="  (m.Name like '%" . inp("UserID") . "%'  or m.id =" . (inp("UserID")) . ") AND";
            $flag = 1;
        }
        if (inp("ActiveStatus") != "%") {
            $where1 .="  a.ActiveStatus =" . inp("ActiveStatus") ." AND ";
            $flag = 1;
        }
        if (inp("BranchId") != "%") {
            $where1 .=" a.branch_id =" . inp("BranchId") . " AND";
            $flag = 1;
        }
        if ($flag == 1)
            $query .= $where . $where1;
//        $query .= $where;
        $query = trim($query, " AND");
        $query .= " ORDER BY id";
        $result = $this->db->query($query)->result();
        
        $data['accounts'] = $result;


        global $com_params;
        $accountTypeArray = explode(",", $com_params->get('ACCOUNT_TYPES'));
        $accountTypeArray = array_merge(array("Any"), $accountTypeArray);
        $accountTypeArray = array_combine($accountTypeArray, $accountTypeArray);
        $documents = $this->db->query("Select * from jos_xdocuments")->result();
        $i = 1;
        //setInfo("SEARCH ACCOUNT", "");


        $data['contents'] = $this->searchAccountForm(true);
//        echo $query;
        JRequest::setVar("layout", "searchAccountsView");
        $this->load->view('search.html', $data);
        $this->jq->getHeader();
    }

    function searchMemberForm() {
        xDeveloperToolBars::onlyCancel("search_cont.dashboard", "cancel", "Search Members");
        $this->load->library('form');
        $this->form->open(1, 'index.php?option=com_xbank&task=search_cont.searchMember')
                ->setColumns(2)
                ->text("Name or ID", "name='Name' class='input'")
                ->textArea("Permanent Address", "name='PermanentAddress' ")
                ->text("Age", "name='Age' class='input'")
                ->text("Phone Nos", "name='PhoneNos' class='input'")
                ->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                ->submit('Search');
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "searchmember");
        $this->load->view('search.html', $data);
        $this->jq->getHeader();
        //$this->load->view('template', $data);
    }

    function searchMember() {
        xDeveloperToolBars::onlyCancel("search_cont.dashboard", "cancel", "Search Members");
        $flag = 0;
        $query = "select m.* from jos_xmember m ";

        $where = " where";
        $where1 = "";
        if (inp("Name") != "") {
            $where1 .=" (m.Name like '%" . inp("Name") . "%' OR m.id = '".inp('Name') ."') AND";
            $flag = 1;
        }
        if (inp("PermanentAddress") != "") {
            $where1 .=" m.PermanentAddress like '%" . inp("PermanentAddress") . "%' AND";
            $flag = 1;
        }
        if (inp("Age") != "") {
            $where1 .=" m.Age " . makeoperator(inp("Age")) . " AND";
            $flag = 1;
        }
        if (inp("PhoneNos") != "") {
            $where1 .=" m.PhoneNos like '%" . inp("PhoneNos") . "%' AND";
            $flag = 1;
        }

        if (inp("BranchId") != "%") {
            $where1 .=" m.branch_id =" . inp("BranchId") . " AND";
            $flag = 1;
        }
        if ($flag == 1)
            $query .= $where . $where1;
//        $query .= $where;
        $query = trim($query, "AND");
        $result = $this->db->query($query)->result();
        $data['members'] = $result;

        //setInfo("SEARCH MEMBER", "");
        $this->load->library('form');
        $this->form->open(1, 'index.php?option=com_xbank&task=search_cont.searchMember')
                ->setColumns(2)
                ->text("Name Or ID", "name='Name' class='input'")
                ->textArea("Permanent Address", "name='PermanentAddress' ")
                ->text("Age", "name='Age' class='input'")
                ->text("Phone Nos", "name='PhoneNos' class='input'")
                ->select("Select Branch", "name='BranchId'", Branch::getAllBranchNames())
                ->submit('Search');


        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "searchMemberView");
        $this->load->view('search.html', $data);
        $this->jq->getHeader();
        //$data['contents'] .=$this->load->view('member.html', $data, true);
        //$this->load->view("template", $data);
    }

}

?>
