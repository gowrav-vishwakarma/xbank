<table width="100%" border="1" class="ui-widget">
  <tr class="ui-widget-header">
    <td>S.No.</td>
    <td>Item Name</td>
    <td>Description</td>
    <td>Under Category</td>
<!--    <td>Stock Bought</td>
    <td>Stock Bought On</td>-->
    <td>Edit</td>
    <td>Remove Item</td>


  </tr>
 <?php
 $i=1;
   foreach($result as $r)
  {
 ?>
        <tr>
        <td><?php echo $i;?></td>
        <td><?php echo $r->Name;?></td>
        <td><?php echo $r->Description;?></td>
        <td><?php echo $r->CName;?></td>
<!--        <td><?php //echo $r->Qty;?></td>
        <td><?php // echo $r->DateBought;?></td>-->
        <td><?php if(JFactory::getUser()->gid >= 24): ?>
            <a href="index.php?option=com_xbank&task=inventory_cont.editItemForm&id=<?php echo $r->id ?>">Edit</a>
        <?php           endif;
            ?>
        </td>
        <td><?php if(JFactory::getUser()->gid >= 24): ?>
            <a href="index.php?option=com_xbank&inventory_cont.removeItem&id=<?php echo $r->id ?>">Remove</a>
        <?php           endif;
            ?>
        </td>

        </tr>
<?php
$i++;
  } // end
?>
</table>
