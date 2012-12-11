<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//UNIQUE Account Number, Scheme, MEMBER ARE CHECKED IN GENERAL CALLING FILE
//ALWAYS put your output to $msg variable

$result = $this->db->query("select a.AccountNumber from jos_xaccounts a  where (a.AccountNumber = '%" . inp('AccountNumber') . "%' or a.AccountNumber='%" . inp('AccountNumber')."%')")->row();
//            $result = $q->execute();
           // if ($result->count() > 0) {
if($result){
                $err = true;
                showError( "This Account Number is Illegal due to existing account <br/><b>" . $result->AccountNumber . "</b>falsefalse");
                return;
 //$this->jq->addError("AccountNumber Error", "This Account Number is Illegal due to existing account <br/><b>" . $result->getFirst()->AccountNumber . "</b>false");
   //$msg .= $this->jq->flashMessages(true);
            }
            $u = inp('UserID');
            $m = new Member($u);
     //$m = Doctrine::getTable("Member")->find(inp("UserID"));

            if (!$m) {
                $err = true;
                showError("The Member not found <br/>falsefalse");
            //$msg .= $this->jq->flashMessages(true);
            }

            $sc = Scheme::getScheme(inp("AccountType"));
            if (!$sc) {
                $err = true;
                showError("Must Define a Scheme to continue<br/>falsefalse");
                //$msg .= $this->jq->flashMessages(true);
                //$msg .= "false";
                return;
            }
            $a = inp('Agents_Id');
            $Agents  = new Agent();
            $Agents ->where('member_id', $a)->get();
          //$Agents = Doctrine::getTable('Agents')->findOneByMember(inp('Agents_Id'));
            if (!$Agents) {
                $Agents = null; //Branch::getDefaultAgent();
            }

$commissionAmount = getComission($sc->AccountOpenningCommission, OPENNING_COMMISSION);
$debitAccount +=array(Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),);
$creditAccount +=array("This New Account" => inp("initialAmount"),);

if ($Agents and $commissionAmount != 0) {
                    $debitAccount +=array(Branch::getCurrentBranch()->Code . SP . "Commission Account" => $commissionAmount,);
                    $creditAccount +=array(
//                                            Branch::getCurrentBranch()->Code."_Agent_SA_".$Agents->member_id ." [ ".$Agents->Member->Name." ] "  => ($commissionAmount  - ($commissionAmount * 10 /100)) ,
                        $Agents->AccountNumber . " [ " . $Agents->Member->Name . " ] " => ($commissionAmount - ($commissionAmount * 10 / 100)),
                        Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * 10 / 100),
                    );
                }

                $msg .= formatDrCr($debitAccount, $creditAccount);

