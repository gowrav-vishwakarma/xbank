<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
 $form = $this->form->open("RD", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                                        ->setColumns(2);
if (!$com_params->get('other_accountnumber_auto_generated'))
    $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "");
else {
    $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType IN ('" . ACCOUNT_TYPE_RECURRING . "','".ACCOUNT_TYPE_DDS."','".ACCOUNT_TYPE_DHANSANCHAYA."','".ACCOUNT_TYPE_FIXED."','".ACCOUNT_TYPE_MONEYBACK."') ";
    $accnum = getNextCode($com_params->get("default_other_accountnumber"), $query);
    $form = $form->text("Account number : $branchCode - ", "name='AccountNumberVisible' class='input' DISABLED value='$accnum'")
                    ->hidden("", "name='AccountNumber' value='$accnum'");
}
$form = $form->_()->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsRD\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.CustomerID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsRD", "", $member)
                        ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_RECURRING))
                        ->text("Initial Opening Amount", "name='initialAmount' class='input req-numeric'")
                        ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:jQuery(\"#agentDetailsRD\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                        ->div("agentDetailsRD", "", $defaultAgent)
                        ->text("RECURRING amount (premium)", "name='rdamount' class='input req-string'")
                        ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                        ->lookupDB("Collector ID", "name='CollectorID' class='input' onblur='javascript:jQuery(\"#memberDetailsB\").load(\"index.php?option=com_xbank&task=accounts_cont.memberDetails&format=raw&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.CustomerID&format=raw", array("a" => "b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->lookupDB("Collector Saving A/C", "name='CollectorAccountNumber' class='input'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("id", "AccountNumber", "Name"), "AccountNumber")
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
        $this->jq->addTab(1, "RD Accounts", $this->form->get());
?>
