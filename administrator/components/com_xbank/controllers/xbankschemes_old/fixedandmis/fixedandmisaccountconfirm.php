<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//UNIQUE Account Number, Scheme, MEMBER ARE CHECKED IN GENERAL CALLING FILE
//ALWAYS put your output to $msg variable
/**       $msg should be extent always not replaced        */


//$q = Doctrine_Query::create()
//                ->select("a.AccountNumber")
//                ->from("Accounts a")
//                ->where('a.AccountNumber like ?', '____' . inp('AccountNumber'))
//                ->orWhere('a.AccountNumber = ?', inp('AccountNumber'));
$result = $this->db->query("select a.AccountNumber from jos_xaccounts a  where a.AccountNumber like '%" . inp('AccountNumber') . "%' or a.AccountNumber='%" . inp('AccountNumber')."%'")->row();
//$result = $q->execute();
//if ($result->count() > 0) {
if ($result) {
    $err = true;
    showError("This Account Number is Illegal due to existing account <br/><b>" . $result->AccountNumber . "</b>falsefalse");
    //$msg .= $this->jq->flashMessages(true);
}
$u=inp("UserID");
$m=new Member($u);
//$m = Doctrine::getTable("Member")->find(inp("UserID"));

if (!$m) {
    $err = true;
    showError("The Member not found <br/>falsefalse");
    //$msg .= $this->jq->flashMessages(true);
}

$sc = Scheme::getScheme(inp("AccountType"));
if (!$sc->id) {
    $err = true;
    showError("Must Define a Scheme to continue<br/>falsefalse");
    //$msg .= $this->jq->flashMessages(true);
    //$msg .= "false";
    return;
}
$Agents  = new Agent();
$Agents ->where('member_id', inp('Agents_Id'))->get();
//$Agents = Doctrine::getTable('Agents')->findOneByMember(inp('Agents_Id'));
if (!$Agents->result_count()) {
    $Agents = null; //Branch::getDefaultAgent();
}

if ($sc->InterestToAnotherAccount == 1) {
    $iac = Account::getAccountForCurrentBranch(inp("InterestTo"));
    if (!$iac->result_count()) {
        $err = true;
        showError("This Account Requires an Account to get Interest Please provide valid one <br/>falsefalse");
        //$msg .= $this->jq->flashMessages(true);
    }
}
if ($err) {
    $msg .="false";
    return;
}
$debitTo = "";
if(inp("DebitTo")){
    $debitTo = Account::getAccountForCurrentBranch(inp("DebitTo"));
}
if($debitTo)
    $debitToAccount = inp("DebitTo");
else
    $debitToAccount = CASH_ACCOUNT;


$commissionAmount = getComission($sc->AccountOpenningCommission, OPENNING_COMMISSION);
$commissionAmount = $commissionAmount * inp("initialAmount") / 100.00;

$debitAccount +=array($debitToAccount => inp("initialAmount"),);
$creditAccount +=array("This New Account" => inp("initialAmount"),);
$msg .= formatDrCr($debitAccount, $creditAccount);
if ($Agents and $commissionAmount != 0) {
     $agent  = new Account();
     $agent->where('AccountNumber',$Agents->AccountNumber)->get();
    //$agent = Doctrine::getTable("Accounts")->findOneByAccountNumber($Agents->AccountNumber);
     if ($agent->branch_id != Branch::getCurrentBranch()->id) {
                 $otherbranch=new Branch();
                 $otherbranch->where('id',$agent->branch_id)->get();
                //$otherbranch = Doctrine::getTable("Branch")->find($agent->branch_id);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $sc->Name => $commissionAmount,
                );
                $creditAccount = array(
                    // get agents' account number
                    //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                     $otherbranch->Code.SP.BRANCH_AND_DIVISIONS.SP."for".SP.Branch::getCurrentBranch()->Code  => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * TDS_PERCENTAGE / 100),
                );

                $msg .= formatDrCr($debitAccount, $creditAccount);

                $debitAccount = array(
                Branch::getCurrentBranch()->Code.SP.BRANCH_AND_DIVISIONS => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                );
                $creditAccount = array(
                    // get agents' account number
                   $Agents->AccountNumber => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                );

                $msg .= formatDrCr($debitAccount, $creditAccount);
            } else{

            $debitAccount +=array(Branch::getCurrentBranch()->Code . SP . "Commission Account" => $commissionAmount,);
            $creditAccount +=array(
//                                            Branch::getCurrentBranch()->Code."_Agent_SA_".$Agents->member_id ." [ ".$Agents->Member->Name." ] "  => ($commissionAmount  - ($commissionAmount * 10 /100)) ,
        $Agents->AccountNumber . " [ " . $Agents->Member->Name . " ] " => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
        Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * TDS_PERCENTAGE / 100),
    );
            $msg .= formatDrCr($debitAccount, $creditAccount);
}
}

