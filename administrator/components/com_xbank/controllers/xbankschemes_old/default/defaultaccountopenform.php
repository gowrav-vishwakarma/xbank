<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
$form = $this->form->open("OtherAccounts", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                ->setColumns(2)
                ->text("AccountID", "name='AccountID' class='input ' DISABLED value='Auto Generated'")
                ->lookupDB("Account number", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                ->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsO", "", $member)
                ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_DEFAULT))
                ->_()
                ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:jQuery(\"#agentDetailsO\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                ->div("agentDetailsO", "", $defaultAgent)
                ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                ->hidden("", "name='CurrentScheme' value='$currentFolder'");
                

$y = 1;
foreach ($documents as $d) {
    if ($d->OtherAccounts == "" or $d->OtherAccounts == 0) {
        $y++;
        continue;
    }
    $form = $form->checkBox($d->Name, "name='Documents_$y' class='input' value='$d->id'")
                    ->textArea("Description for $d->Name", "name='Description_$y'");
    $y++;
}

$form = $form->confirmButton("Confirm", "New Account to create", "index.php?option=com_xbank&task=accounts_cont.confirmAccountCreateForm&format=raw", true)
                ->submit("Create");
$this->jq->addTab(1, "Other Accounts", $this->form->get());
?>
