<table width="100%" border="1" class="ui-widget ui-widget-content">
  <tr class="ui-widget-header">
    <td>S No.</td>
    <td>Scheme Name</td>
    <td>Total Count</td>

  </tr>
    <?php
    $i = 1;
        foreach($records as $r){
    ?>
  <tr>
    <td><?php echo $i++; ?></td>
    <td><a href="index.php?option=com_xbank&task=accounts_cont.accountForScheme&id=<?php echo $r->ID; ?>" ><?php echo $r->Name; ?></a>
    </td>
    <td><?php echo $r->Accounts; ?></td>
   </tr>
    <?php
	} //End of foreac
	?>
</table>