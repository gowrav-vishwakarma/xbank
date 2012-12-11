<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
$form = $this->form->open("LoanAccounts", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                ->setColumns(2);
if (!$com_params->get('loan_accountnumber_auto_generated'))
    $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "")
                 ->_();
else {
    $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType ='" . ACCOUNT_TYPE_LOAN . "' ";
    $accnum = getNextCode($com_params->get("default_loan_accountnumber"), $query);
    $form = $form->text("Account number : $branchCode - ", "name='AccountNumberVisible' class='input' DISABLED value='$accnum'")
                    ->hidden("", "name='AccountNumber' value='$accnum'");
}

$form = $form->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsL\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);jQuery(\"#accountsDetailsF\").load(\"index.php?option=com_xbank&task=accounts_cont.accountsDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                ->div("memberDetailsL", "", $member)
                ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type' ", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_LOAN))
                ->text("Loan Amount", "name='initialAmount' class='input req-numeric' ")
                ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:jQuery(\"#agentDetailsL\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a" => "b"), array("id", "Name", "PanNo"), "id")
                ->div("agentDetailsL", "", $defaultAgent)
                ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))

                // made hidden until old entries are made..
//                ->lookupDB("Gaurantor","name='Nominee' class='input' ","index.php?//ajax/lookupDBDQL",array("select"=>"m.id AS ID, m.Name AS Name","from"=>"Member m, m.Branch b","where"=>"b.id=$b->id","andWhere"=>"m.Name Like '%\$term%'","orWhere"=>"m.id Like '%\$term%'"),array("id","Name"),"Name")
//                ->text("Gaurantor Address","name='MinorNomineeParentName' class='input'")  // IMP : Used as gaurantor address in loan accounts
// 		->text("Gaurantor Phone Nos.","Name='RelationWithNominee' class='input'")
                ->hidden("", "name='Nominee' class='input' ")
                ->hidden("", "name='MinorNomineeParentName' class='input'")  // IMP : Used as gaurantor address in loan accounts
                ->hidden("", "Name='RelationWithNominee' class='input'")
                ->select("Operation Mode", "name='ModeOfOperation' class='not-req req-string' not-req-val='Select_Mode'", array("Select_Mode" => '-1', "Self" => 'Self', "Joint" => 'Joint', "Any One" => 'Any', "Otehr" => 'Other'), "Self")
                ->lookupDB("Loan Amount From Account : $branchCode - ", "name='InterestTo' class='input req-string' ", "index.php?option=com_xbank&task=accounts_cont.loanFromAccount&format=raw", array("a" => "b"), array("AccountNumber","MemberName"), "AccountNumber")
                ->datebox("Loan Insurrance Date", "name='LoanInsurranceDate'")
                ->checkbox("Loan Ag Security", "name='LoanAgSecurity' class='input'  value='1'")
                ->text("Account ID(if Loan Ag. Security)", "Name='SecurityAccount' class='input' ")
                ->div("accountsDetailsF", "", $accounts)
                ->lookupDB("Dealer ID", "name='Dealer' class='input' ", "index.php?option=com_xbank&task=accounts_cont.getDealer&format=raw", array("a" => "b"), array("id", "DealerName", "Address"), "id")
                ->hidden("", "name='CurrentScheme' value='$currentFolder'");

$k = 1;
foreach ($documents as $d) {
    if ($d->LoanAccount == "" or $d->LoanAccount == 0) {
        $k++;
        continue;
    }
    $form = $form->checkBox($d->Name, "name='Documents_$k' class='input' value='$d->id'")
                    ->textArea("Description for $d->Name", "name='Description_$k'");
    $k++;
}

$form = $form->confirmButton("Confirm", "New Account to create", "index.php?option=com_xbank&task=accounts_cont.confirmAccountCreateForm&format=raw", true)
                ->_()->_()
                ->submit("Create");
$this->jq->addTab(1, "Loan Account", $this->form->get());

