<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$debit = 0;$credit = 0;
$openingbalance = $OpeningBalance + $transactionOpeningBalance;
$debitopeningbalance = ($openingbalance > 0 ? round(abs($openingbalance),2):'');
$creditopeningbalance = ($openingbalance < 0 ? round(abs($openingbalance),2):'');
?>
<table class='ui-widget ui-widget-content ui-corner-all' width='100%'>
<tr class='ui-widget-header'><td colspan=7 align='center'>CASH BOOK</td></tr>
<tr class='ui-widget-header'><td colspan=7 align='center'>for <?php echo date("j M, Y", strtotime(inp("dateFrom")))." to ".date("j M, Y", strtotime(inp("dateTo")))?></tr>
<tr class='ui-widget-header'>
<?php
foreach (array_values($keyandvalues) as $header) {
?>
    <th><?php echo $header?></th>
<?php
}
?>
</tr>
<tr>
<td><?php echo inp("dateFrom")?></td>
<td><b><?php echo ($openingbalance > 0 ? "TO Opening Balance" : "BY Opening Balance") ?></b></td>
<td></td><td></td><td></td><td><b><?php echo $debitopeningbalance ?></b></td><td><b><?php echo $creditopeningbalance ?></b></td>
</tr>
<?php
$i = 0;
foreach ($results as $rs) {
   $i++;
    if($i%2 != 0)
        echo "<tr bgcolor='white'>";
    else
        echo "<tr bgcolor='#D9E8E8'>";
    foreach (array_keys($keyandvalues) as $field) {
        $field = trim($field);
        if($field =="Voucher_no"){
        ?>
        <td><a class='alertinwindow' href='index.php?option=com_xbank&task=report_cont.transactionDetails&vn=<?php echo ($rs->$field) ?>&format=raw'><?php echo ($rs->$field) ?></a></td>
        <?php } 
        else{
        if($field =="Narration"){
                $narration = trim($rs->$field,"10 (");
                $narration = trim($narration,")");
                $member = new Member($narration);
                echo "<td>".$rs->$field."  ".$member->Name."</td>";
            }
            else{
        ?>
       <td><?php echo ($rs->$field) ?></td>
       <?php
            }
       }
        if($field == "Debit")
            $debit +=$rs->$field;
        if($field == "Credit")
            $credit +=$rs->$field;
    }
    ?>
    </tr>
<?php
}
?>
<tr>
<td></td>
<td></td>
<td></td><td></td><td></td><td><b><?php echo ($debit) ?></b></td><td><b><?php echo ($credit) ?></b></td>
</tr>

<tr>
<td></td><td></td>
<td><b><?php echo (($debit + $debitopeningbalance) > ($credit + $creditopeningbalance) ? "BY Closing Balance" : "TO Closing Balance") ?></b></td>
<td></td><td></td><td><?php echo (($debit + $debitopeningbalance) > ($credit + $creditopeningbalance)?'': abs(($debit + $debitopeningbalance) - ($credit + $creditopeningbalance)))?></td>
    <td><?php echo (($debit + $debitopeningbalance) > ($credit + $creditopeningbalance)? abs(($debit + $debitopeningbalance) - ($credit + $creditopeningbalance)) : '')?></td>
</tr>

<tr>
<td></td><td></td>
<td></td>
<td></td><td></td><td><b><?php echo ((($debit + $debitopeningbalance) > ($credit + $creditopeningbalance)?0: abs(($debit + $debitopeningbalance) - ($credit + $creditopeningbalance)))+($debit + $debitopeningbalance)) ?></b></td>
    <td><b><?php echo ((($debit + $debitopeningbalance) > ($credit + $creditopeningbalance)? abs(($debit + $debitopeningbalance) - ($credit + $creditopeningbalance)) : 0)+($credit + $creditopeningbalance) ) ?></b></td>
</tr>

</table>
