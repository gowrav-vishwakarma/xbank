<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//AGENT, Scheme, MEMBER ARE CHECKED IN GENERAL CALLING FILE

//$q = Doctrine_Query::create()
//                ->select("a.AccountNumber")
//                ->from("Accounts a")
//                ->where('a.AccountNumber like ?', '____' . inp('AccountNumber'))
//                ->orWhere('a.AccountNumber = ?', inp('AccountNumber'));
$result = $this->db->query("select a.AccountNumber from jos_xaccounts a  where a.AccountNumber like '%" . inp('AccountNumber') . "%' or a.AccountNumber='%" . inp('AccountNumber')."%'")->row();
//$result = $q->execute();
//if ($result->count() > 0) {
if ($result){
    $err = true;
    showError("This Account Number is Illegal due to existing account <br/><b>" . $result->AccountNumber . "</b> false");
    return;
    //$msg .= $this->jq->flashMessages(true);
}
$u=inp("UserID");
$m=new Member($u);
//$m = Doctrine::getTable("Member")->find(inp("UserID"));

if (!$m) {
    $err = true;
    showError("The Member not found <br/>false");
    //$msg .= $this->jq->flashMessages(true);
}

$sc = Scheme::getScheme(inp("AccountType"));
if (!$sc) {
    $err = true;
    showError("Must Define a Scheme to continue<br/>");
    //$msg .= $this->jq->flashMessages(true);
    //$msg .= "false";
    return;
}
?>
