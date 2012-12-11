<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
$form = $this->form->open("NewDhanSanchayaAccount", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                ->setColumns(2);
if (!$com_params->get('other_accountnumber_auto_generated'))
    $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "");
else {
    $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType IN ('" . ACCOUNT_TYPE_RECURRING . "','".ACCOUNT_TYPE_DDS."','".ACCOUNT_TYPE_DHANSANCHAYA."','".ACCOUNT_TYPE_FIXED."','".ACCOUNT_TYPE_MONEYBACK."') ";
    $accnum = getNextCode($com_params->get("default_other_accountnumber"), $query);
    $form = $form->text("Account number : $branchCode - ", "name='AccountNumberVisible' class='input' DISABLED value='$accnum'")
                    ->hidden("", "name='AccountNumber' value='$accnum'");
}
$form = $form->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:$(\"#memberDetailsB\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails/\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.CustomerID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsB", "", $member)
                ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_DHANSANCHAYA))
                ->text("Initial Opening Amount", "name='initialAmount' class='input req-numeric tooltip' title='Put the initial opening amount for account'")
                ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:$(\"#agentDetailsC\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a" => "b"), array("id", "Name", "PanNo"), "id")
                //->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' onblur='javascript:$(\"#agentDetailsA\").load(\"index.php?//mod_member/member_cont/agentDetails/\"+this.value);'", "index.php?/option=com_xbank&task=accounts_cont.AgentMemberID", array("select" => "m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos", "from" => "Member m, m.Branch b", "innerJoin" => "m.Agents a", "where" => "b.id=$b->id", "andWhere" => "m.Name Like '%\$term%'", "orWhere" => "m.id Like '%\$term%'"), array("id", "Name", "PanNo"), "id")
                ->div("agentDetailsA", "", $defaultAgent)
                ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                ->selectAjax("Operation Mode", "name='ModeOfOperation' class='not-req req-string' not-req-val='Select_Mode'", array("Select_Mode" => '-1', "Self" => 'Self', "Joint" => 'Joint', "Any One" => 'Any', "Otehr" => 'Other'))
                ->hidden("", "name='CurrentScheme' value='$currentFolder'");

// Documents to be submitted
foreach ($documents as $d) {
    if ($d->SavingAccount == "" or $d->SavingAccount == 0) {
        $i++;
        continue;
    }
    $form = $form->checkBox($d->Name, "name='Documents_$i' class='input' value='$d->id'")
                    ->textArea("Description for $d->Name", "name='Description_$i'");
    $i++;
}

$form = $form->confirmButton("Confirm", "New Account to create", "index.php?option=com_xbank&task=accounts_cont.confirmAccountCreateForm&format=raw", true)
                ->_()
                ->submit("Create");
$this->jq->addTab(1, "Dhan Sanchaya Account", $form->get());
?>
