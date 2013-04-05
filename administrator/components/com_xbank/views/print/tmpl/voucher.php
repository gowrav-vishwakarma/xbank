<div style="border:1px solid black; margin: 5px; width: 100%">
	<table width="100%">
		<tr>
			<td width="80%" valign="top">
				<h3>BHAWANI CREDIT CO OPERATIVE SOCIETY</h3>
			</td>
			<td>
				<h3>VOUCHER</h3>
				Voucher No : <?php echo $voucher_no?>
				<br/> Date:  <?php echo $voucher_date?>
			</td>
		</tr>
	</table>
	
	<table width="100%" border="1">
		<tr>
			<td>Particular</td>
			<td>Rs.Paisa</td>
		</tr>
		<?php 
		$dr_total=0;
		$i=0;
		foreach($voucher_dr as $dv):
		$dr_total += $dv->amountDr;
		?>
		<?php if($i==0):?>
			<tr><th colspan=2> DEBIT </th></tr>
		<?php endif;?>
			<tr>
				<td><b>Account Number: </b><?php echo $dv->account->AccountNumber; ?> (<?php echo $dv->account->member->Name?>)</td>
				<td><?php echo $dv->amountDr; ?></td>
			</tr>
		<?php 
		$i++;
		endforeach;?>
		<tr>
			<td colspan='2'><b>Narration:</b> <?php echo $dv->Narration;?></td>
		</tr>
		<tr>
			<td><b>Total in words :</b> (<?php echo convert_digit_to_words($dr_total);?>)</td>
			<td><?php echo $dr_total;?></td>
		</tr>


		<?php 
		$cr_total=0;
		$i=0;
		foreach($voucher_cr as $cv):
		$cr_total += $cv->amountCr;
		?>
		<?php if($i==0):?>
			<tr><th colspan=2> CREDIT </th></tr>
		<?php endif;?>
			<tr>
				<td><b>Account Number: </b><?php echo $cv->account->AccountNumber; ?> (<?php echo $dv->account->member->Name?>)</td>
				<td><?php echo $cv->amountCr; ?></td>
			</tr>
		<?php 
		$i++;
		endforeach;?>
		<tr>
			<td colspan='2'><b>Narration:</b> <?php echo $cv->Narration;?></td>
		</tr>
		<tr>
			<td><b>Total in words :</b> (<?php echo convert_digit_to_words($cr_total);?>)</td>
			<td><?php echo $cr_total;?></td>
		</tr>

	</table>
	<table width="100%">
		<tr>
			<td>Cashier _______________</td>
			<td>Entry By ______________</td>
			<td>T. No _________________</td>
			<td rowspan='2' align="center">
				<div style="border:1px solid black; width:55px; height:60px; margin:auto">
					<small><center>Revenue Stamp</center></small>
				</div>
				Receiver's Stamp
			</td>
		</tr>
		<tr>
			<td>B.m. _______________</td>
			<td>Auditor ______________</td>
			<td>&nbsp;</td>
		</tr>
		
	</table>
</div>