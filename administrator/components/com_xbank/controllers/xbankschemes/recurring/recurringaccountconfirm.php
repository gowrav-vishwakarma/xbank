<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//$q = Doctrine_Query::create()
//                            ->select("a.AccountNumber")
//                            ->from("Accounts a")
//                            ->where('a.AccountNumber like ?', '____' . inp('AccountNumber'))
//                            ->orWhere('a.AccountNumber = ?', inp('AccountNumber'));
$result = $this->db->query("select a.AccountNumber from jos_xaccounts a  where a.AccountNumber like '%" . inp('AccountNumber') . "%' or a.AccountNumber='%" . inp('AccountNumber')."%'")->row();
//            $result = $q->execute();
            //if ($result->count() > 0) {
           if($result) {
                $err = true;
                showError( "This Account Number is Illegal due to existing account <br/><b>" . $result->AccountNumber . "</b> falsefalse");
                return;
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
               // $msg .= $this->jq->flashMessages(true);
               //$msg .= "false";
                return;
            }
               $a = inp('Agents_Id');
               $Agents  = new Agent();
               $Agents ->where('member_id', $a)->get();
           // $Agents = Doctrine::getTable('Agents')->findOneByMember(inp('Agents_Id'));
            if (!$Agents) {
                $Agents = null; //Branch::getDefaultAgent();
            }

            preg_match('/\d+/', inp('AccountNumber'), $match);
            $ac_number = $match[0];

            if(inp('AccountNumber')!= Branch::getCurrentBranch()->Code."RD".$ac_number)
            {
              $err=true;
              showError("Your Account Number Pattern is wrong<br/>falsefalse");
              return;
            }



$commissionAmount = getComission($sc->AccountOpenningCommission, OPENNING_COMMISSION);
$debitAccount +=array(Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount"),);
$creditAccount +=array("This New Account" => inp("initialAmount"),);
if ($sc->NumberOfPremiums * inp("rdamount") < inp("initialAmount")) {
    echo "<h2>INTIAL AMOUNT IS GREATER THAN REQUIRED AMOUNT</h2>falsefalse";
    return;
}
            $a = inp('Agents_Id');
            $Agents  = new Agent($a);
//$Agents = Doctrine::getTable('Agents')->findOneByMember(inp('Agents_Id'));

if ($Agents->result_count() and $commissionAmount != 0 and inp("initialAmount")) {
    $debitAccount +=array(Branch::getCurrentBranch()->Code . SP . "Commission Account" => $commissionAmount,);
    $creditAccount +=array(
//                                            Branch::getCurrentBranch()->Code."_Agent_SA_".$Agents->member_id ." [ ".$Agents->Member->Name." ] "  => ($commissionAmount  - ($commissionAmount * 10 /100)) ,
        $Agents->AccountNumber . " [ " . $Agents->Member->Name . " ] " => ($commissionAmount - ($commissionAmount * 10 / 100)),
        Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * 10 / 100),
    );
}

$msg .= formatDrCr($debitAccount, $creditAccount);
?>
