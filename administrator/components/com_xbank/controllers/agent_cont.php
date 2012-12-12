<?php

class agent_cont extends CI_Controller {

    function dashboard() {
        xDeveloperToolBars::getAgentManagementToolBar();

        global $com_params;
//        $start = JRequest::getVar("pagestart", 0);
//        $count = JRequest::getVar("pagecount", $com_params->get('RowsInData'));
//        $data['start'] = $start;
//        $data['i'] = $start;
//        $data['count'] = $count;

        $a= new Agent();
        $a->include_related("member","Name");
        $a->include_related("member","FatherName");
        $a->include_related("member","PermanentAddress");
        $a->include_related("member","PhoneNos");
        $a->where_related("member","branch_id",Branch::getCurrentBranch()->id);
        $a->get();
        

//        $a->check_last_query();
        $data['report'] = getReporttable($a,             //model
                array("Agent Name","Father Name","Address","Phone Nos","AgentCode","AccountNumber"),       //heads
                array('member_Name','member_FatherName',"member_PermanentAddress","member_PhoneNos","id","AccountNumber"),       //fields
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

/* == OLD AGENT DASH BOARD
        $data['agent'] = new Agent();
        $data['agent']->limit(10,JRequest::getVar("limitstart",0));
        $data['agent']->get();
        $a = new Agent();
        jimport('joomla.html.pagination');
 	$data['page']=new JPagination($a->count(), JRequest::getVar("limitstart",0), 10 ); //total, limit start, limit

        $this->load->view("agent.html", $data);
        $this->jq->getHeader();
*/
    }

    function createAgentform() {
        xDeveloperToolBars::onlyCancel("com_xbank.index", "cancel", "Create Agent");
        global $com_params;
       $ag = new xConfig("agent");
        $this->load->library('form');
        $form = $this->form->open("agent", 'index.php?option=com_xbank&task=agent_cont.createAgent');
                        if($com_params->get("agent_has_code")){
                            if (!$com_params->get('agent_code_auto_generated')) {
                                $form = $form->text("Agent Code", "name='AgentCode' class='input req-string'");
                            } else {
                                $query = "select count(AgentCode) from jos_xagents where AgentCode is not null";
                                $agent_code = getNextCode($com_params->get("default_agent_code"), $query);
                                $form = $form->text("Agent Code", "name='AgentCode' class='input' DISABLED value='$agent_code'");
                            }
                        }
                        $form = $form->lookupDB("Member Name", "name='UserID' class='input req-string' onblur='javascript:$(\"#memberDetailsB\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails/\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->lookupDB("Agent Sponsor", "name='SponsorID' class='input' onblur='javascript:$(\"#memberDetailsB\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails/\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id");

                        $ag = new xConfig("agent");
                        if($ag->getKey("manually_promote_agent")){
                            $agentRanks = array();
                            $cs = $this->db->query("select * from jos_xcommissionslab")->result();
                            foreach($cs as $c)
                                $agentRanks += array($c->AdvisorLevel => $c->Rank);
                            $form = $form->select("Advisor Rank","name='Rank'",$agentRanks);
                        }
                        $form = $form->lookupDB("Agent Saving A/C", "name='AccountNumber' class='input'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("id", "AccountNumber", "Name"), "AccountNumber")
                        ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                        ->_()
                        ->text("Gaurantor 1 Name", "name='Gaurantor1Name'")
                        ->textArea("Gaurantor 1 Father/Husband Name", "name='Gaurantor1FatherHusbandName'")
                        ->textArea("Gaurantor 1 Address", "name='Gaurantor1Address'")
                        ->textArea("Gaurantor 1 Occupation", "name='Gaurantor1Occupation'")
                        ->text("Gaurantor 2 Name", "name='Gaurantor2Name'")
                        ->textArea("Gaurantor 2 Father/Husband Name", "name='Gaurantor2FatherHusbandName'")
                        ->textArea("Gaurantor 2 Address", "name='Gaurantor2Address'")
                        ->textArea("Gaurantor 2 Occupation", "name='Gaurantor2Occupation'");

                        
                        $form = $form->confirmButton("Confirm", "New Agent to create", "index.php?option=com_xbank&task=agent_cont.confirmAgentCreateForm&format=raw", true)
                        ->_()
                        ->submit('Create Agent');
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "addagent");
        $this->load->view('agent.html', $data);
        $this->jq->getHeader();
    }

    function createAgent() {
        global $com_params;
        try{
            $this->db->trans_begin();
        
        $ca = new Agent();
        $ca->member_id = inp('UserID');
        $ca->ActiveStatus = inp('ActiveStatus');
        $ca->AccountNumber = inp('AccountNumber');
        $ca->sponsor_id = inp("SponsorID");
        $ca->Gaurantor1Name = inp("Gaurantor1Name");
        $ca->Gaurantor1Address = inp("Gaurantor1Address");
        $ca->Gaurantor1FatherHusbandName = inp("Gaurantor1FatherHusbandName");
        $ca->Gaurantor1Occupation = inp("Gaurantor1Occupation");

        $ca->Gaurantor2Name = inp("Gaurantor2Name");
        $ca->Gaurantor2Address = inp("Gaurantor2Address");
        $ca->Gaurantor2FatherHusbandName = inp("Gaurantor2FatherHusbandName");
        $ca->Gaurantor2Occupation = inp("Gaurantor2Occupation");
//        $ca->Rank = 1;
        $ca->created_at = getNow();
        $ca->updated_at = getNow();
        if($com_params->get("agent_has_code")){
           if ($com_params->get('agent_code_auto_generated')) {
                $query = "select count(AgentCode) from jos_xagents where AgentCode is not null";
                $agent_code = getNextCode($com_params->get("default_agent_code"), $query);
                $ca->AgentCode = $agent_code;
           }
        }

        if(inp("SponsorID")){
            $sp = new Agent(inp("SponsorID"));
            $path = getPath($sp->Path,$sp->LegCount);
            $ca->Tree_id = $sp->Tree_id;
            $ca->Path = $path;
            $sp->LegCount += 1;
            $sp->save();
        }
        else{
             $maxtree_id = $this->db->query("select max(Tree_id) as maxid from jos_xagents")->row()->maxid;
             $ca->Tree_id = $maxtree_id + 1;
             $ca->Path = "000";
        }

        $ca->save();

        $ag = new xConfig("agent");
        $levels = $ag->getKey("number_of_agent_levels");

        if($levels > 1 and $ag->getKey("manually_promote_agent")){
            $ca->Rank = inp("Rank");
        }
        else{
            $ca->Rank = 1;
            $ca->updateAncestors();
        }
        $ca->save();
        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("agent_cont.createAgentform", "Agent Not Added", "error");
        }
        $this->db->trans_commit();
        re('agent_cont.createAgentform', "Agent " . $ca->member->Name . " Created Sucessfully");
    }

    function confirmAgentCreateForm() {
        $a = new Agent();
        $a->where('member_id', inp('UserID'))->get();
        if ($a->result_count() > 0) {
            echo "<h2>".$a->member->Name . " is already an Agent.</h2><br>falsefalse";
            return;
        }
        $msg = "";
        $sponsor = new Agent(inp("SponsorID"));
        if (inp("SponsorID")) {
            $msg = " with Sponsor " . $sponsor->member->Name;
        }
        echo "<h2>Proceed with new agent creation...</h2><br>" . $a->member->Name . $msg;
    }

    function promoteAgentManually(){
        $id = JRequest::getVar("id");
        $a = new Agent($id);
        $cs = new Commissionslab();
//        $cs->where("Rank <>",$a->Rank)->get();
        $levels = array();
        foreach($cs->get() as $c){
            if($c->Rank == $a->Rank){
                    $myrank = $c->AdvisorLevel;
                    continue;
            }
            $levels +=array($c->AdvisorLevel => $c->Rank);
        }
        xDeveloperToolBars::onlyCancel("agent_cont.dashboard", "cancel", "");
        $form = $this->form->open("agent", 'index.php?option=com_xbank&task=agent_cont.promoteAgent')
                ->setColumns(2)
                ->select("Promote Advisor ".$a->Member->Name." from $myrank to","name='Rank'",$levels)
                ->hidden("","name='agentID' value='$id'")
                ->confirmButton("Confirm", "Promote Advisor ".$a->Member->Name." from $myrank", "index.php?option=com_xbank&task=agent_cont.promoteAgentConfirm&format=raw", true)
                ->submit("Promote");
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "promoteagent");
        $this->load->view('agent.html', $data);
        $this->jq->getHeader();
    }

    function promoteAgentConfirm(){
        echo "<h2>Are you sure?</h2>";
    }

    function promoteAgent(){
        $a = new Agent(inp("agentID"));
        $a->Rank = inp("Rank");
        $a->save();
        re('agent_cont.dashboard', "Agent " . $a->member->Name . " promoted Sucessfully");
    }

    function commissionReportFrom(){
        xDeveloperToolBars::onlyCancel("agent_cont.dashboard", "cancel", "View Commission Payable to Agents");
        $b = Branch::getCurrentBranch();
        $this->load->library("form");
        $form = $this->form->open("accountdetails", "index.php?option=com_xbank&task=agent_cont.commissionReport")
                        ->setColumns(2)
                        ->lookupDB("Account number : $b->Code - ", "name='AccountNumber' class='input'", "index.php?option=com_xbank&task=report_cont.AccountNumber&format=raw", array("a"=>"b"), array("id", "AccountNumber","MName"), "AccountNumber")
                        ->lookupDB("Agent's ID", "name='Agents_Id' class='input' ", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a" => "b"), array("id", "Name", "PanNo"), "id")
                        ->dateBox("Commission Transferred from", "name='fromDate' class='input'")
                        ->dateBox("Commission Transferred till", "name='toDate' class='input'");
        $form = $form->submit("Go");
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout","commissionreportform");
        $this->load->view("agent.html",$data);
        $this->jq->getHeader();
    }

    function commissionReport(){
        xDeveloperToolBars::onlyCancel("agent_cont.commissionReportFrom", "cancel", "View Commission Payable to Agents");
        $msg = "";
        $a=new Agent(inp('Agents_Id'));

        $p=new Premium();
        $p->include_related('account','AccountNumber');
        $p->include_related('account/member','Name');
        $p->where('AgentCommissionSend',1);
        $p->where_related('account/agent','id',inp('Agents_Id'));
        if(inp('fromDate')){
            $p->where('PaidOn >=',inp('fromDate'));
            $msg .= " from date " . inp('fromDate');
        }
        if(inp('toDate')){
            $p->where('PaidOn <',inp('toDate'));
            $msg .= " till date " . inp('toDate');
        }

        $p->get();

        $msg .= " :: For Agent " . $a->Name;
        $data['report'] = getReporttable($p,             //model
                array("Account Number",        "Member Name",       "PaidOn","Commission", "Phone Number","Amount Due","Due Date","Agent","Dealer"),       //heads
                array('account_AccountNumber','account_member_Name','PaidOn','~ (#Amount * #AgentCommissionPercentage / 100.0)', 'member_PhoneNos','Amount','DueDate','agent_member_Name','dealer_DealerName'),       //fields
                array('Amount'),        //totals_array
                array(),        //headers
                array('sno'=>true),     //options
                "<h3>". $msg . "</h3>",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
                );
        // $p->check_last_query();
        JRequest::setVar("layout","generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();

        // $rep = new agentcommissionreport();
        // $rep->where("Commission >",0);
        // if(inp("Agents_Id"))
        //     $rep->where("agents_id",inp("Agents_Id"));
        // if(inp("AccountNumber"))
        //     $rep->where("accounts_id",inp("AccountNumber"));
        // if(inp("fromDate") and inp("toDate"))
        //     $rep->where("CommissionPayableDate >=",inp("fromDate"))->where("CommissionPayableDate <",inp("toDate"));
        
        // $data['agent'] = $rep->get();
        // JRequest::setVar("layout","commissionreport");
        // $this->load->view("agent.html", $data);
        // $this->jq->getHeader();
    }
}

?>
