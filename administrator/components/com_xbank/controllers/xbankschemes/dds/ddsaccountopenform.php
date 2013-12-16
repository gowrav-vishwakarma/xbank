<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
$form = $this->form->open("DDS", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                ->setColumns(2);
if (!$com_params->get('other_accountnumber_auto_generated'))
    $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "")
                 ->_();
else {
    $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType IN ('" . ACCOUNT_TYPE_RECURRING . "','" . ACCOUNT_TYPE_DDS . "','" . ACCOUNT_TYPE_DHANSANCHAYA . "','" . ACCOUNT_TYPE_FIXED . "','" . ACCOUNT_TYPE_MONEYBACK . "') ";
    $accnum = getNextCode($com_params->get("default_other_accountnumber"), $query);
    $form = $form->text("Account number : $branchCode - ", "name='AccountNumber' class='input' DISABLED value='$accnum'");
}

$form = $form->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsD\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsD", "", $member)
                ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_DDS))
                ->text("Initial Opening Amount", "name='initialAmount' class='input req-numeric'")
                ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:jQuery(\"#agentDetailsD\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a" => "b"), array("id", "Name", "PanNo"), "id")
                ->div("agentDetailsD", "", $defaultAgent)
                ->text("DDS amount (in multiples of Rs.300 like 300, 600, 900....3000 etc.)", "name='rdamount' class='input req-string'")// monthly DDS Amount Required
                ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                ->text("Account Name (IF Joint)","name='AccountDisplayName' class='input'")
                ->_()
                ->lookupDB("Member ID 2 (For Joint Accounts)", "name='UserID_2' class='input' onblur='javascript:jQuery(\"#memberDetailsS1\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsS1", "", $member)
                ->lookupDB("Member ID 3 (For Joint Accounts)", "name='UserID_3' class='input' onblur='javascript:jQuery(\"#memberDetailsS2\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsS2", "", $member)
                ->lookupDB("Member ID 4 (For Joint Accounts)", "name='UserID_4' class='input' onblur='javascript:jQuery(\"#memberDetailsS3\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsS3", "", $member)
                ->lookupDB("Member ID 5 (For Joint Accounts)", "name='UserID_5' class='input' onblur='javascript:jQuery(\"#memberDetailsS4\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsS4", "", $member)
                ->text('Nominee','name="Nominee" class="input "')
                ->text('Nominee Age','name="NomineeAge" class="input "')
                ->text('Relation Nominee','name="RelationWithNominee" class="input "')
                ->hidden("", "name='CurrentScheme' value='$currentFolder'")
                ->hidden("", "name='CurrentScheme' value='$currentFolder'");

$l = 1;
foreach ($documents as $d) {
    if ($d->RDandDDSAccount == "" or $d->RDandDDSAccount == 0) {
        $l++;
        continue;
    }
    $form = $form->checkBox($d->Name, "name='Documents_$l' class='input' value='$d->id'")
                    ->textArea("Description for $d->Name", "name='Description_$l'");
    $l++;
}

$form = $form->confirmButton("Confirm", "New Account to create", "index.php?option=com_xbank&task=accounts_cont.confirmAccountCreateForm&format=raw", true)
                ->_()
                ->submit("Create");
$this->jq->addTab(1, "DDS Accounts", $this->form->get());
?>
