<?php echo $form; ?>
<center><h2>Bhawani Credit Co-Operative Society<br/>Balance Sheet</h2></center>
<h3 align='center'>From <?php echo date('d-M-Y',strtotime($this->session->userdata('fromdate'))) ?> to <?php echo date('d-M-Y',strtotime($this->session->userdata('todate'))); ?></h3>
<table width='100%' border='1' id='bal'>
	<tr>
		<th>Liablities</th>
		<th>Assets</th>
	</tr>
	<tr>
		<td valign='top' width='50%'>
			<?php echo $LT;?>
		</td>
		<td valign='top'>
			<?php echo $RT;?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $LT_SUM; ?>
		</td>
		<td>
			<?php echo $RT_SUM; ?>
		</td>
	</tr>
</table>