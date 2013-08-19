<?php echo $form; ?>
<center><h2>Bhawani Credit Co-Operative Society<br/><?php echo isset($report_name)? $report_name: 'Balance Sheet'?> [ <?php echo Branch::getCurrentBranch()->Name; ?> ]</h2></center>
<h3 align='center'>From <?php echo date('d-M-Y',strtotime($this->session->userdata('fromdate'))) ?> to <?php echo date('d-M-Y',strtotime($this->session->userdata('todate'))); ?></h3>
<table width="100%" style='border: 1px solid #aaa'>
	<tr>
		<th width="60%" align="left" style="font-size: 16px">Perticular</th>
		<th width="20%" align="left" style="font-size: 16px">Debit</th>
		<th width="20%" align="left" style="font-size: 16px">Credit</th>
	</tr>
</table>
<?php echo $head_rows;?>
<table width="100%" style='border: 1px solid #aaa'>
	<tr>
		<th width="60%" align="left" style="font-size: 16px">Totals</th>
		<th width="20%" align="left" style="font-size: 16px"><?php echo $totals['dr'];?></th>
		<th width="20%" align="left" style="font-size: 16px"><?php echo $totals['cr'];?></th>
	</tr>
</table>