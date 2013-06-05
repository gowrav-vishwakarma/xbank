<table width="100%" border="1" class="ui-widget">
  <tr class="ui-widget-header">
    <td>S.No.</td>
    <td>Item Name</td>
    <td>Stock Alloted Date</td>
    <td>Quantity Alloted</td>
    <td>Status</td>
    <td>Branch Name</td>


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
        <td><?php echo $r->QuantityAlloted; ?></td>
        <td><?php if($r->StockStatus == 1)
                    echo "Added";
                  if($r->StockStatus == 0)
                    echo "Removed";
                  if($r->StockStatus == 2)
                    echo "Alloted";
                  if($r->StockStatus == 3)
                    echo "Returned" ; ?></td>
        <td><?php echo $r->BranchName; ?></td>

        </tr>
<?php
$i++;
  } // end
?>
</table>
