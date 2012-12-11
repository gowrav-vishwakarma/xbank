<?php
$sc = Scheme::getScheme($account->schemes_id);
if ($sc->ProcessingFeesinPercent == 1) {
        $processingfee = $sc->ProcessingFees * $account->RdAmount / 100;
    } else {
        $processingfee = $sc->ProcessingFees;
    }

$interest = (($account->RdAmount * $sc->Interest * ($sc->NumberOfPremiums + 1))/1200);
$premiums = new Premium();
$premiums->where("accounts_id",$account->id)->get();
?>

<table width="100%" border="0">
  <tr>
    <td colspan="2"><strong>Member Name : </strong><a class="tooltip"><?php echo $account->member->Name;?><span class="classic"><?php echo ($otheraccounts ? "Accounts of ".$account->member->Name."<br> $otheraccounts" : " ")?></span></a>
    <div class="style1"  id="accountsDiv" align="center"></div></td>
    <td><strong>Account Number : </strong><?php echo $account->AccountNumber;?></td>
  </tr>
<tr>
    <td colspan="2"><strong>Father name : </strong>  <?php echo $account->member->FatherName;?></td>
    <td>&nbsp;</td>
  </tr>
<tr>
    <td colspan="2"><strong>Address : </strong>  <?php echo $account->member->PermanentAddress;?></td>
    <td>&nbsp;</td>
  </tr>
<tr>
    <td colspan="2"><strong>Phone No : </strong>  <?php echo $account->member->PhoneNos;?></td>
    <td>&nbsp;</td>
  </tr>
<tr>
    <td colspan="2"><strong>Guarentor : </strong>  <?php echo $account->Nominee /*used as guarenter here*/;?></td>
    <td>&nbsp;</td>
  </tr>
<tr>
    <td colspan="2"><strong>Guarentor Address: </strong>  <?php echo $account->MinorNomineeParentName  /*used as guarenter address here*/;?></td>
    <td>&nbsp;</td>
  </tr>
<tr>
    <td colspan="2"><strong>Guarentor Phone: </strong>  <?php echo $account->RelationWithNominee  /*used as guarenter mobile here*/;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>Loan Amount : </strong>  <?php echo $account->RdAmount;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>EMI Details : </strong>  <?php echo $premiums->result_count() . " x" . $premiums->Amount;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>Account Opening Date : </strong>  <?php echo date("j M, Y", strtotime($account->created_at));?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>To DR :</strong><?php echo $account->RdAmount - $processingfee;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>To Processing Fee(File Charge) : </strong><?php echo $processingfee; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>Interest Rate : </strong><?php echo $sc->Interest." %"; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong>To Interest : </strong><?php echo $interest; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
<tr>
<td colspan="4">
<table border="1" width="100%">
  <tr>
    <td><strong>EMI Due Date</strong></td>
    <td><strong>Paid Status</strong></td>
    <td><strong>Paid Date</strong></td>
    <td><strong>EMI Amount</strong></td>
    <td><strong>Status</strong></td>
  </tr>
  <?php
    foreach($premiums as $p):
  ?>
  <tr>
    <td><?php echo $p->DueDate;?></td>
    <td><?php echo ($p->Paid != 0 ? "Paid" : "Unpaid");?></td>
    <td><?php echo $p->PaidOn;?></td>
    <td><?php echo $p->Amount; ?></td>
    <td><?php
    if($p->DueDate < getNow("Y-m-d") && $p->Paid == 0)
            echo "OverDue";
    if($p->PaidOn == null && $p->DueDate > getNow("Y-m-d"))
            echo "Due";
    ?></td>
  </tr>
  <?php
  endforeach;
  ?>
</table>
</td>
</tr>
</table>

