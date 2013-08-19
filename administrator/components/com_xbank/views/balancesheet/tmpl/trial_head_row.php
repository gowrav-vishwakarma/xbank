<table width='100%' style='border: 1px solid #aaa'>
	<tr>
		<td width='60%' onclick='javascript:jQuery("#<?php echo md5($Total->{$Total->Title}) ?>").toggle();'>
			<b><?php echo $Total->{$Total->Title}; ?></b>
		</td>
		<?php 
			if($is_pandl){
				$subtract_from = $subtract_from=='Dr'?'Cr':'Dr';
			}

			if($subtract_from=='Dr') {
				$Dr_side = $Total->amountDr - $Total->amountCr;
				$Cr_side= '&nbsp';
			}else{
				$Dr_side= '&nbsp';
				$Cr_side = $Total->amountCr - $Total->amountDr;
			}

		?>
		<th width='20%' align="left">
			<?php echo $Dr_side ?>
		</th>
		<th width='20%' align="left">
			<?php echo $Cr_side ?>
		</th>
	</tr>
	<tr>
		<td colspan=3>
			<table width=100% style='border: 1px solid #ccc; display: none' id='<?php echo md5($Total->{$Total->Title}) ?>'>
				<?php foreach($Detailed as $dt):
					$subtract_from = 'amount'. $dt->SubtractFrom;
					$subtract_to = 'amount'. ($dt->SubtractFrom == 'Dr' ? 'Cr' : 'Dr');
					$amt=$dt->{$subtract_from} - $dt->{$subtract_to};
					if($amt > 0){
						$postfix = $dt->SubtractFrom;
						$color='black';
					}
					else{
						$postfix = ($dt->SubtractFrom == 'Dr' ? 'Cr' : 'Dr');
						$color='red';
					}
				?>
				<tr>
					<td width='60%'>
						<a title='Dig in to <?php echo $dt->{$dt->Title}?>' href='index.php?option=com_xbank&format=raw&task=balancesheet_cont.digin&digtype=<?php echo $dt->Title?>&digid=<?php echo urlencode($dt->{$dt->Title})?>&pandl=<?php echo isset($is_pandl)?1:0;?>' class='alertinwindow'><?php echo $dt->{$dt->Title}; ?></a>
					</td>
					<?php if($dt->SubtractFrom=='Dr'):?>

						<td width='20%'>
							<a href='index.php?option=com_xbank&task=balancesheet_cont.digin&digtype=<?php echo $dt->Title?>&digid=<?php echo urlencode($dt->{$dt->Title})?>&pandl=<?php echo isset($is_pandl)?1:0;?>' target='_blank'><?php echo "<font color='$color'>".abs($amt). " ". $postfix. "</font>";	 ?></a>
						</td>
						<td width='20%'>
							&nbsp;
						</td>

					<?php else:?>
						<td width='20%'>
							&nbsp;
						</td>
						<td width='20%'>
							<a href='index.php?option=com_xbank&task=balancesheet_cont.digin&digtype=<?php echo $dt->Title?>&digid=<?php echo urlencode($dt->{$dt->Title})?>&pandl=<?php echo isset($is_pandl)?1:0;?>' target='_blank'><?php echo "<font color='$color'>".abs($amt). " ". $postfix. "</font>";	 ?></a>
						</td>

					<?php endif?>
				</tr>
			<?php endforeach;?>
			</table>
		</td>
	</tr>
</table>