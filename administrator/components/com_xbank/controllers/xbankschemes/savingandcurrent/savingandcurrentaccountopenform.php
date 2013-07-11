<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$currentFolder = basename(dirname(__FILE__));
$form = $this->form->open("NewBankAccount", 'index.php?option=com_xbank&task=accounts_cont.NewAccountCreate')
                         ->setColumns(2);
//                        ->text("AccountID", "name='AccountID' class='input ' DISABLED value='Auto Generated'")
                 if (!$com_params->get('saving_accountnumber_auto_generated'))
                        $form = $form->lookupDB("Account number : $branchCode - ", "name='AccountNumber' class='input req-string'", "index.php?option=com_xbank&task=accounts_cont.AccountNumber&format=raw", array("a"=>"b"), array("AccountNumber"), "")
                                    ->text("Account Name","name='AccountDisplayName' class='input'");
                 else{
                      $query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.SchemeType ='" . ACCOUNT_TYPE_BANK . "' ";
                      $accnum = getNextCode($com_params->get("default_saving_accountnumber"), $query);
                      $form = $form->text("Account number : $branchCode - ", "name='AccountNumberVisible' class='input' DISABLED value='$accnum'")
                                   ->hidden("", "name='AccountNumber' value='$accnum'");
                 }

                        $form = $form->lookupDB("Member ID", "name='UserID' class='input req-string' onblur='javascript:jQuery(\"#memberDetailsS\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsS", "", $member)
                        ->selectAjax("Account Under", "name='AccountType' class='req-string not-req' not-req-val='Select_Account_Type'", Branch::getAllSchemesForCurrentBranchOfType(ACCOUNT_TYPE_BANK))
                        ->text("Initial Opening Amount", "name='initialAmount' class='input req-numeric tooltip' title='Put the initial opening amount for account'")
                        ->lookupDB("Agent's Member ID", "name='Agents_Id' class='input'  onblur='javascript:jQuery(\"#agentDetailsS\").load(\"index.php?option=com_xbank&task=accounts_cont.agentDetails&format=raw&aid=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.AgentMemberID&format=raw", array("a"=>"b"), array("id", "Name", "PanNo"), "id")
                        //->lookupDB("Agent's Member ID", "name='Agents_Id' class='input' onblur='javascript:$(\"#agentDetailsA\").load(\"index.php?//mod_member/member_cont/agentDetails/\"+this.value);'", "index.php?/option=com_xbank&task=accounts_cont.AgentMemberID", array("select" => "m.id AS ID, m.Name AS Name, m.PanNo AS PanNo, m.PhoneNos", "from" => "Member m, m.Branch b", "innerJoin" => "m.Agents a", "where" => "b.id=$b->id", "andWhere" => "m.Name Like '%\$term%'", "orWhere" => "m.id Like '%\$term%'"), array("id", "Name", "PanNo"), "id")
                        ->div("agentDetailsS", "", $defaultAgent)
                        ->select("Active Status", "name='ActiveStatus'", array("Active" => '1', "DeActive" => '0'))
                        ->selectAjax("Operation Mode", "name='ModeOfOperation' class='not-req req-string' not-req-val='Select_Mode'", array("Select_Mode" => '-1', "Self" => 'Self', "Joint" => 'Joint', "Any One" => 'Any', "Otehr" => 'Other'))
                        ->lookupDB("Member ID 2 (For Joint Accounts)", "name='UserID_2' class='input' onblur='javascript:jQuery(\"#memberDetailsS1\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsS1", "", $member)
                        ->lookupDB("Member ID 3 (For Joint Accounts)", "name='UserID_3' class='input' onblur='javascript:jQuery(\"#memberDetailsS2\").load(\"index.php?option=com_xbank&format=raw&task=accounts_cont.memberDetails&id=\"+this.value);'", "index.php?option=com_xbank&task=accounts_cont.MemberID&format=raw", array("a"=>"b"), array("id", "Name", "FatherName", "BranchName"), "id")
                        ->div("memberDetailsS2", "", $member)
                        ->hidden("", "name='CurrentScheme' value='$currentFolder'")
                        ;

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
        $this->jq->addTab(1, "Saving Current Account", $form->get());
?>
