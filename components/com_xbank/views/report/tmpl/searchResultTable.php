<table width="100%" border="1" class="ui-widget">
  <tr class="ui-widget-header">
    <td>id</td>
    <td>Account</td>
    <td>Paid</td>
    <td>Skipped</td>
    <td>Due Date</td>
    <td>Paid On</td>
    <?php
    if($accountType == ACCOUNT_TYPE_RECURRING)
    {
    ?>
    <td>Agent Commission Percentage</td>
    <td>Agent Commission Given</td>
    <?php
    }

    ?>
  </tr>
  <?php
  if($result){
  foreach($result as $r)
  {
    if($accountType == ACCOUNT_TYPE_RECURRING)
    {
    ?>
        <tr class="<?php if($r->Skipped) {echo " ui-state-error";} else {echo "ui-widget-content";} ?>">
        <td><?php echo $r->id;?></td>
        <td><?php echo $r->AccountNumber;?></td>
        <td><?php echo (($r->Paid > 0) && ($r->Skipped == 0) )? "Yes":"No";?></td>
        <td><?php echo ($r->Skipped == 1 )? "Yes":"No";?></td>
        <td><?php echo $r->DueDate;?></td>
        <td><?php echo $r->PaidOn;?></td>
        <td><?php echo $r->AgentCommissionPercentage;?></td>
        <td><?php echo $r->AgentCommissionSend;?></td>

        </tr>
    <?php
     } // end of if(ACCOUNT_TYPE_RECURRING)

    if($accountType == ACCOUNT_TYPE_LOAN)
    {
    ?>
        <tr>
        <td><?php echo $r->id;?></td>
        <td><?php echo $r->AccountNumber;?></td>
        <td><?php echo ($r->Paid > 0) ? "Yes":"No";?></td>
        <td><?php echo ($r->Skipped == 1 )? "Yes":"No";?></td>
        <td><?php echo $r->DueDate;?></td>
        <td><?php echo $r->PaidOn;?></td>
        </tr>
    <?php
    } // end of if(ACCOUNT_TYPE_LOAN)
  } // end of foreach
  } // end of if($result)
  else{
         echo "No such account found.";
      }
  ?>
</table>
