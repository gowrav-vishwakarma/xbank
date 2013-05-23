<table width="100%" border="1" class="ui-widget">
  <tr class="ui-widget-header">
    <td>S.No.</td>
    <td>Category Name</td>
    <td>Description</td>
    <td>Edit</td>
    <td>Remove Category</td>


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
        <td><?php if(JFactory::getUser()->gid >= 24): ?>
            <a href="index.php?option=com_xbank&task=inventory_cont.editCategoryForm&id=<?php echo $r->id ?>">Edit</a>
            <?php           endif;
            ?>
        </td>
        <td><?php if(JFactory::getUser()->gid >= 24): ?>
            <a href="index.php?option=com_xbank&task=inventory_cont.removeCategory&id=<?php echo $r->id ?>">Remove</a>
        <?php           endif;
            ?>
        </td>

        </tr>
<?php
$i++;
  } // end
?>
</table>
