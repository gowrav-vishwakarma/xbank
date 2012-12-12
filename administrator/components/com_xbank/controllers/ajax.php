<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ajax extends CI_Controller{

	function ajax(){
		parent::__construct();
// 		$this->output->enable_profiler(TRUE);
	}

        function lookupDBQLShareCretificate(){
            $list = array();
        $q = "select a.*, m.Name AS mName from jos_xaccounts a join jos_xmember m on m.id=a.member_id join jos_xschemes s on a.schemes_id=s.id where( m.Name Like '%" . $this->input->post("term") . "%' or m.id Like '%" . $this->input->post("term") . "%' or a.AccountNumber Like '%" . $this->input->post("term") . "%') and s.Name = '".CAPITAL_ACCOUNT_SCHEME."' order by m.id limit 0,10";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array('id' => $dd->id,"AccountNumber" => $dd->AccountNumber, 'Name' => $dd->mName, "CurrentBalanceCr" => $dd->CurrentBalanceCr );
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

        function loan_report_account(){
            $list = array();
            $q = "select a.AccountNumber, m.Name AS Name, m.PanNo AS PanNo, s.Name AS Scheme from jos_xaccounts a join jos_xmember m on a.member_id = m.id
                    join jos_xschemes s on a.schemes_id = s.id
                    join jos_xbranch b on a.branch_id = b.id
                    where a.AccountNumber Like '%".$this->input->post("term")."%'
                    and b.id=".Branch::getCurrentBranch()->id."
                    or m.Name like '%".$this->input->post("term")."%' and m.branch_id=".Branch::getCurrentBranch()->id;
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("AccountNumber" => $dd->AccountNumber, 'Name' => $dd->Name, "PanNo" => $dd->PanNo ,"Scheme"=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

         function loan_report_scheme(){
            $list = array();
            $q = "select s.Name as Scheme
                    from jos_xschemes s
                    where s.Name Like '%".$this->input->post("term")."%'
                    and s.SchemeType='".ACCOUNT_TYPE_LOAN."'";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("Scheme"=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

        function loan_report_gaurantor(){
            $list = array();
            $q = "select m.Name AS Name, m.PermanentAddress AS Address, a.AccountNumber AS AccountNumber
                    from jos_xmember m
                    join jos_xaccounts a on a.member_id = m.id
                    join jos_xbranch b on b.id = a.branch_id
                    where m.Name Like '%".$this->input->post("term")."%'
                    and b.id=".Branch::getCurrentBranch()->id."
                    or m.Name like '%".$this->input->post("term")."%' and m.branch_id=".Branch::getCurrentBranch()->id;
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("Name"=>$dd->Name,"Address" => $dd->Address, "AccountNumber" => $dd->AccountNumber);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

        function loan_report_member(){
            $list = array();
            $q = "select m.Name AS Name, m.PermanentAddress AS Address, a.AccountNumber AS AccountNumber
                    from jos_xmember m
                    join jos_xaccounts a on a.member_id = m.id
                    join jos_xbranch b on b.id = a.branch_id
                    where m.Name Like '%".$this->input->post("term")."%'
                    and b.id=".Branch::getCurrentBranch()->id."
                    or m.Name like '%".$this->input->post("term")."%' and m.branch_id=".Branch::getCurrentBranch()->id;
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("Name"=>$dd->Name,"Address" => $dd->Address, "AccountNumber" => $dd->AccountNumber);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

        function loan_report_dealer(){
            $list = array();
            $q = "select id,DealerName AS Name, Address AS Address
                    from jos_xdealer
                    where DealerName Like '%".$this->input->post("term")."%'";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("id" => $dd->id,"Name"=>$dd->Name,"Address" => $dd->Address);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

        function get_loan_account(){
            $list = array();
            $q = "select a.AccountNumber, m.Name AS Name, m.PanNo AS PanNo, s.Name AS Scheme from jos_xaccounts a join jos_xmember m on a.member_id = m.id
                    join jos_xschemes s on a.schemes_id = s.id
                    join jos_xbranch b on a.branch_id = b.id
                    where a.AccountNumber Like '%".$this->input->post("term")."%'
                    or m.Name like '%".$this->input->post("term")."%' ";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("AccountNumber" => $dd->AccountNumber, 'Name' => $dd->Name, "PanNo" => $dd->PanNo ,"Scheme"=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }


         function loan_account_statement(){
            $list = array();
            $q = "select a.id,a.AccountNumber, m.Name AS Name, m.PanNo AS PanNo, s.Name AS Scheme from jos_xaccounts a join jos_xmember m on a.member_id = m.id
                    join jos_xschemes s on a.schemes_id = s.id
                    join jos_xbranch b on a.branch_id = b.id
                    where a.AccountNumber Like '%".$this->input->post("term")."%'
                    and b.id=".Branch::getCurrentBranch()->id."
                    or m.Name like '%".$this->input->post("term")."%' and m.branch_id=".Branch::getCurrentBranch()->id;
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("id" => $dd->id,"AccountNumber" => $dd->AccountNumber, 'Name' => $dd->Name, "PanNo" => $dd->PanNo ,"Scheme"=>$dd->Scheme);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

        function list_agents(){
            $list = array();
            $q = "select m.Name AS Name, m.PermanentAddress AS Address, ag.id as AgentID
                    from jos_xmember m
                    join jos_xbranch b on b.id = m.branch_id
                    join jos_xagents ag on ag.member_id=m.id
                    where m.Name Like '%".$this->input->post("term")."%'
                    and b.id=".Branch::getCurrentBranch()->id;
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("id"=> $dd->AgentID, "Name"=>$dd->Name,"Address" => $dd->Address);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

}
?>
