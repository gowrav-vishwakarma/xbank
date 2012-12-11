<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
$form = $this->form->open("CCAccounts", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                        ->setColumns(2);
if (!$com_params->get('cc_accountnumber_auto_generated'))
    $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a" => "b"), array("AccountNumber"), "")
                 ->_();
else {
    $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType = '" . ACCOUNT_TYPE_CC . "' ";
    $accnum = getNextCode($com_params->get("default_cc_accountnumber"), $query);
    $form = $form->text("Account number : $branchCode - ", "name='AccountNumberVisible' class='input' DISABLED value='$accnum'")
                    ->hidden("", "name='AccountNumber' value='$accnum'");
}
//$form = $form->text("AccountID", "name='AccountID' class='input ' DISABLED value='Auto Generated'")
//                        ->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                        $form = $form->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsC\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsC", "", $member)
                        ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_CC))
                        ->text("CC Limit", "name='initialAmount' class='input req-numeric'")
                        ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:jQuery(\"#agentDetailsC\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                        ->div("agentDetailsC", "", $defaultAgent)
                        ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
        ->hidden("", "name='CurrentScheme' value='$currentFolder'")
                        
                        ->_();

        $x = 1;
        foreach ($documents as $d) {
            if ($d->CCAccount == "" or $d->CCAccount == 0) {
                $x++;
                continue;
            }
            $form = $form->checkBox($d->Name, "name='Documents_$x' class='input' value='$d->id'")
                            ->textArea("Description for $d->Name", "name='Description_$x'");
            $x++;
        }

        $form = $form->confirmButton("Confirm", "New Account to create", "index.php?option=com_xbank&task=accounts_cont.confirmAccountCreateForm&format=raw", true)
                        ->submit("Create");
        $this->jq->addTab(1, "CC Accounts", $this->form->get());
?>
