<table width="100%" border="1" class="ui-widget ui-widget-content">
  <tr class="ui-widget-header">
    <td>Account Number</td>
    <td>Current Balance Credit</td>
    <td>Current Balance Debit</td>

  </tr>
    <?php
        foreach($accounts as $acc){
    ?>
  <tr>
    <td><?php echo $acc->AccountNo; ?></td>
    <td><?php echo $acc->Cr; ?></td>
    <td><?php echo $acc->Dr; ?></td>
   </tr>
    <?php
	} //End of foreac
	?>
</table>
