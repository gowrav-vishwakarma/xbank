<table width="100%"><th>Transaction Date</th><!--<th>Particulars</th>--><th><center>Narration</center></th><th><center>Debit</center></th><th><center>Credit</center></th>
 <?php
 $voucherno = 0;
foreach($transaction as $t){
?>
    <tr>
    	<td rowspan="2"><?php echo $t->updated_at; ?></td>
<!--        <td><?php  ?></td>-->
        <td rowspan="2"><center><?php echo $t->Narration; ?></center></td>
        <td rowspan="2"><center><?php echo $t->amountDr; ?></center></td>
        <td rowspan="2"><center><?php echo $t->amountCr; ?></center></td>
        <td rowspan="2"><center><?php echo $t->voucher_no; ?></center></td>
        <?php
            if($voucherno != $t->voucher_no)
            {
        ?>
        <td rowspan="2"><a href="index.php?option=com_xbank&task=transaction_cont.deleteTransaction/<?php echo $t->voucher_no; ?>/<?php echo $t->branch_id; ?>" target="xyz">Delete</a></td>
        <?php
            $voucherno = $t->voucher_no;
        }
        ?>
     </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <?php
}
?>
</table>
