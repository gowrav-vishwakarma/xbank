<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//$q = Doctrine_Query::create()
//                ->select("a.AccountNumber")
//                ->from("Accounts a")
//                ->where('a.AccountNumber like ?', '____' . inp('AccountNumber'))
//                ->orWhere('a.AccountNumber = ?', inp('AccountNumber'));
$result = $this->db->query("select a.AccountNumber as accnum from jos_xaccounts a  where a.AccountNumber like '%" . inp('AccountNumber') . "%' or a.AccountNumber='" . inp('AccountNumber')."'")->row()->accnum;
//$result = $q->execute();
//if ($result->count() > 0) {
if ($result)
{
    $err = true;
    showError("This Account Number is Illegal due to existing account <br/><b>" . $result->AccountNumber . "</b>falsefalse");
    //$msg .= $this->jq->flashMessages(true);
}
              $u = inp('UserID');
              $m = new Member($u);
//$m = Doctrine::getTable("Member")->find(inp("UserID"));

if (!$m) {
    $err = true;
    showError("The Member not found <br/>falsefalse");
//    $msg .= $this->jq->flashMessages(true);
}

$sc = Scheme::getScheme(inp("AccountType"));
if (!$sc->id) {
    $err = true;
    showError("Must Define a Scheme to continue<br/>falsefalse");
    $msg .= $this->jq->flashMessages(true);
//    $msg .= "falsefalse";
    return;
}
               $a = inp('Agents_Id');
               $Agents  = new Agent();
               $Agents->where('member_id', $a)->get();
//$Agents = Doctrine::getTable('Agents')->findOneByMember(inp('Agents_Id'));
if (!$Agents->result_count()) {
    $Agents = null; //Branch::getDefaultAgent();
}

$chkAcc = 0;
If (inp("SecurityAccount") != "") {
    $q=$this->db->query("select a.id from jos_xaccounts a innerjoin jos_xmember m on a.member_id=m.id join jos_xschemes s on a.schemes_id=s.id where(s.SchemeType=".ACCOUNT_TYPE_BANK."or s.SchemeType=" . ACCOUNT_TYPE_FIXED . " or s.SchemeType=" . ACCOUNT_TYPE_RECURRING . "') and a.branch_id=" . Branch::getCurrentBranch()->id . " and  a.member_id=" . inp("UserID") . " and a.ActiveStatus=1 and a.LockingStatus=0");
//    $q = Doctrine_Query::create()
//                        ->select(" a.id ")
//                        ->from("accounts a")
//                        ->innerJoin("a.Member m")
//                        ->innerJoin("a.Schemes s")
//                        ->where ("(s.SchemeType='" . ACCOUNT_TYPE_BANK . "' or s.SchemeType='" . ACCOUNT_TYPE_FIXED . "' or s.SchemeType='" . ACCOUNT_TYPE_RECURRING . "') and a.branch_id=" . Branch::getCurrentBranch()->id . " and  a.member_id=" . inp("UserID") . " and a.ActiveStatus=1 and a.LockingStatus=0");
    $a = $q->result();
    foreach ($a as $acc) {
        if ($acc->id == inp("SecurityAccount")) {
            $chkAcc = 1;
            break;
        }
    }
}
if ((inp("LoanAgSecurity") == 1 and inp("SecurityAccount") == "") or (inp("LoanAgSecurity") == "" and $chkAcc == 1) or (inp("LoanAgSecurity") == 1 and $chkAcc == 0)) {
    echo "<h2>CHECK FOR LOAN AGAINST SECURITY</h2>falsefalse";
    return;
} else {
    $commissionAmount = getComission($sc->AccountOpenningCommission, OPENNING_COMMISSION);
    if ($sc->ProcessingFeesinPercent == 1) {
        $processingfee = $sc->ProcessingFees * inp("initialAmount") / 100;
    } else {
        $processingfee = $sc->ProcessingFees;
    }
    $schemeName = $sc->Name;
    $loanFromAccount=new Account();
    $loanFromAccount->where('AccountNumber',inp("InterestTo"));
    //$loanFromAccount = Doctrine::getTable("Accounts")->findOneByAccountNumber(inp("InterestTo"));
    if ($loanFromAccount->scheme->SchemeType == ACCOUNT_TYPE_BANK) {
                $debitAccount += array(inp("AccountNumber") => inp("initialAmount"),);
                $creditAccount += array(Branch::getCurrentBranch()->Code . SP . PROCESSING_FEE_RECEIVED . $schemeName => $processingfee,);
                $creditAccount +=array(inp("InterestTo") => inp("initialAmount") - $processingfee,);
                $msg .= formatDrCr($debitAccount, $creditAccount);
                
                $debitAccount = array(inp("InterestTo") => inp("initialAmount") - $processingfee,);
                $creditAccount = array(Branch::getCurrentBranch()->Code . SP . CASH_ACCOUNT => inp("initialAmount") - $processingfee,);
    }
    else{

        $debitAccount +=array("This New Account" => inp("initialAmount"),);
        $creditAccount +=array(Branch::getCurrentBranch()->Code . SP . PROCESSING_FEE_RECEIVED . $schemeName => $processingfee,);
        $creditAccount +=array(inp("InterestTo") => inp("initialAmount") - $processingfee,);
    }

        $rate = $sc->Interest;
        $premiums = $sc->NumberOfPremiums;

        if ($sc->ReducingOrFlatRate == REDUCING_RATE) {
//        FOR REDUCING RATE OF INTEREST
            $emi = (inp('initialAmount') * ($rate / 1200) / (1 - (pow(1 / (1 + ($rate / 1200)), $premiums))));
        } else {
//        FOR FLAT RATE OF INTEREST
            $emi = ((inp('initialAmount') * $rate * ($premiums + 1)) / 1200 + inp('initialAmount')) / $premiums;
        }

//        FOR FLAT RATE OF INTEREST
//        $emi = ((inp('initialAmount') * $rate * ($premiums + 1)) / 1200 + inp('initialAmount')) / $premiums;

//        FOR REDUCING RATE OF INTEREST
//        $emi = (inp('initialAmount') * ($rate/1200) / (1 - (pow(1/(1 + ($rate/1200)), $premiums))));
        $emi = round($emi);
        echo "<h2>EMI = $emi</h2><br/>";
    }

    if ($Agents and $commissionAmount != 0) {
        $debitAccount +=array(Branch::getCurrentBranch()->Code . SP . "Commission Account" => $commissionAmount,);
        $creditAccount +=array(
//                                            Branch::getCurrentBranch()->Code."_Agent_SA_".$Agents->member_id ." [ ".$Agents->Member->Name." ] "  => ($commissionAmount  - ($commissionAmount * 10 /100)) ,
            $Agents->AccountNumber . " [ " . $Agents->Member->Name . " ] " => ($commissionAmount - ($commissionAmount * 10 / 100)),
            Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * 10 / 100),
        );
    }

    $msg .= formatDrCr($debitAccount, $creditAccount);
?>
