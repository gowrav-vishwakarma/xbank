<table width="100%" border="1" class="ui-widget">
  <tr class="ui-widget-header">
    <td>S.No.</td>
    <td>Item Name</td>
    <td>Stock Added/Removed Date</td>
    <td>Quantity</td>
    <td>Stock Status</td>
    <td>Edit</td>


  </tr>
 <?php
 $i=1;
   foreach($result as $r)
  {
 ?>
        <tr>
        <td><?php echo $i;?></td>
        <td><?php echo $r->itemName;?></td>
        <td><?php echo $r->StockAllotedDate;?></td>
        <td><?php echo $r->Quantity; ?></td>
        <td><?php echo ($r->StockStatus ==1)?"Added" : "Removed"; ?></td>
        <td><?php if(JFactory::getUser()->gid >= 24): ?>
            <a href="index.php?option=com_xbank&task=inventory_cont.editNewStockForm&id=<?php echo $r->id ?>">Edit</a>
        <?php           endif;
            ?>
        </td>
        </tr>
<?php
$i++;
  } // end
?>
</table>
