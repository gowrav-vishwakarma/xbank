<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
 $form = $this->form->open("MoneyBack", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                        ->setColumns(2);
//                        ->text("AccountID", "name='AccountID' class='input ' DISABLED value='Auto Generated'")
                 if (!$com_params->get('other_accountnumber_auto_generated'))
                        $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "");
                 else{
                      $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType IN ('" . ACCOUNT_TYPE_RECURRING . "','".ACCOUNT_TYPE_DDS."','".ACCOUNT_TYPE_DHANSANCHAYA."','".ACCOUNT_TYPE_FIXED."','".ACCOUNT_TYPE_MONEYBACK."') ";
                      $accnum = getNextCode($com_params->get("default_other_accountnumber"), $query);
                      $form = $form->text("Account number : $branchCode - ", "name='AccountNumber' class='input' DISABLED value='$accnum'");
                 }

                        $form = $form->lookupDB("Customer ID", "name='UserID' class='input req-string' onblur='javascript:$(\"#memberDetailsL\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&id=\"+this.value);$(\"#accountsDetailsF\").load(\"index.php?option=com_xbank&task=accounts_cont.accountsDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.CustomerID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsL", "", $member)
                        ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_MONEYBACK))
                        ->text("Initial Opening Amount", "name='initialAmount' class='input req-numeric' READONLY")
                        ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:$(\"#agentDetailsC\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                        ->div("agentDetailsD", "", $defaultAgent)
                        ->text("RECURRING amount (premium)", "name='rdamount' class='input req-string'")
                        ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                        ->hidden("", "name='CurrentScheme' value='$currentFolder'")
                        ->_();

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
        $this->jq->addTab(1, "Money Back Accounts", $this->form->get());
?>
