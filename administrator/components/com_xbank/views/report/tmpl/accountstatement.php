<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$debitopeningbalance = ($openingbalance > 0 ? round(abs($openingbalance),2):'');
$creditopeningbalance = ($openingbalance < 0 ? round(abs($openingbalance),2):'');

$CRtotal = 0;
$DRtotal = 0;

if($transactions){
    $acc=new Account();
    $acc->where('AccountNumber',inp("AccountNumber"))->get();
//$acc = Doctrine::getTable("Accounts")->find(inp("AccountNumber"));
echo "<center><u><b>Account Statement for ".strtoupper($acc->AccountNumber)." - ".$acc->AccountDisplayName."</b></u></center>";
echo "<center><u><b>(".strtoupper($acc->Member->Name).")</b></u></center>";
?>
<br>
 <table width="100%" class="adminlist"><thead>
     <tr>
         
         		<th><center>S No</center></th>
                         <th><center>Transaction Date</center></th>
                         <!--<th>Particulars</th>-->
                         <th><center>Narration</center></th>
                         <th><center>Vch No</center></th>
                          <th><center>Debit</center></th>
                         <th><center>Credit</center></th>
                         <th><center>Bal.</center></th>
     </tr></thead>
     <tbody>
<tr>
    <td>&nbsp;</td>
<td>&nbsp;</td>
<td><center><b><?php echo ($openingbalance > 0 ? "TO Opening Balance" : "BY Opening Balance"); ?></b></center></td>
<td>&nbsp;</td>
<td><center><b><?php echo $debitopeningbalance; ?></b></center></td><td><center><b><?php echo $creditopeningbalance; ?></b></center></td>
<td>&nbsp;</td>
</tr>
<?php 
$i = 1;

foreach($transactions as $t){
    $CRtotal += $t->amountCr;
    $DRtotal += $t->amountDr;

?>
    <tr>
        <td><?php echo $i++; ?></td>
    	<td ><?php echo $t->created_at; ?></td>
<!--        <td><?php  ?></td>-->
        <td ><center><?php echo $t->Narration; ?></center></td>
        <td><a class='alertinwindow' Title='Transaction type' href="index.php?option=com_xbank&task=report_cont.transactionDetails&vn=<?php echo $t->voucher_no?>&format=raw&tr_type=<?php echo $t->transaction_type_id?>"><?php echo ($t->display_voucher_no ? $t->display_voucher_no : $t->voucher_no) ?></a></td>
        <td><center><?php echo $t->amountDr; ?></center></td>
        <td><center><?php echo $t->amountCr; ?></center></td>
        <td><?php echo (($DRtotal + $debitopeningbalance) - ($CRtotal + $creditopeningbalance)) > 0 ? (($DRtotal + $debitopeningbalance) - ($CRtotal + $creditopeningbalance))." Dr" : (($CRtotal + $creditopeningbalance) - ($DRtotal + $debitopeningbalance))." Cr"; ?></td>
     </tr>
   
    <?php
}
?>
    <tr>
	<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><b><center><?php echo $DRtotal; ?></center></b></td>
        <td><b><center><?php echo $CRtotal; ?></center></b></td>
        <td><b><center><?php echo (($DRtotal + $debitopeningbalance) - ($CRtotal + $creditopeningbalance)) > 0 ? (($DRtotal + $debitopeningbalance) - ($CRtotal + $creditopeningbalance))." Dr" : (($CRtotal + $creditopeningbalance) - ($DRtotal + $debitopeningbalance))." Cr"; ?></center></b></td>
    </tr></tbody>
</table>

<?php
//echo $msg;
}
else{
    echo "No Transaction Found.";
}
?>
