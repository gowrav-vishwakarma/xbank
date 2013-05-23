<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
echo $contents;
?>
<table width="100%" border="1" class="ui-widget">
  <tr class="ui-widget-header">
    <td>S.No.</td>
    <td>Document Name</td>
    <td>Saving Current Account</td>
    <td>Fixed and MIS Accounts</td>
    <td>Loan Account</td>
    <td>RD and DDS Accounts</td>
    <td>CC Account</td>
    <td>Other Accounts</td>
    <td>Edit</td>
    <td>Remove</td>


  </tr>
 <?php
 $i=1;
   foreach($result as $r)
  {
 ?>
        <tr>
        <td><?php echo $i;?></td>
        <td><?php echo $r->Name;?></td>
        <td><?php echo ($r->SavingAccount==1) ? "Yes" : "No";?></td>
        <td><?php echo ($r->FixedMISAccount==1) ? "Yes" : "No";?></td>
        <td><?php echo ($r->LoanAccount==1) ? "Yes" : "No";?></td>
        <td><?php echo ($r->RDandDDSAccount==1) ? "Yes" : "No";?></td>
        <td><?php echo ($r->CCAccount==1) ? "Yes" : "No";?></td>
        <td><?php echo ($r->OtherAccounts==1) ? "Yes" : "No";?></td>
        <td><a href="index.php?option=com_xbank&task=documents_cont.editDocumentForm&id=<?php echo $r->id ?>">Edit</a></td>
        <td><a href="index.php?option=com_xbank&task=documents_cont.removeDocument&id=<?php echo $r->id ?>">Remove</a></td>

        </tr>
<?php
$i++;
  } // end
?>
</table>
