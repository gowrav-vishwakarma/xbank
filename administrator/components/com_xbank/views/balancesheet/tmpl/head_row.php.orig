<table width='100%' style='border: 1px solid #aaa'>
	<tr>
		<td width='70%' onclick='javascript:jQuery("#<?php echo md5($Total->{$Total->Title}) ?>").toggle();'>
			<b><?php echo $Total->{$Total->Title}; ?></b>
		</td>
		<td width='70%'>
			<?php echo abs($Total->amountDr - $Total->amountCr); ?>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<table width=70% style='border: 1px solid #ccc; display: none' id='<?php echo md5($Total->{$Total->Title}) ?>'>
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
					<td width='70%'>
						<a title='Dig in to <?php echo $dt->{$dt->Title}?>' href='index.php?option=com_xbank&format=raw&task=balancesheet_cont.digin&digtype=<?php echo $dt->Title?>&digid=<?php echo urlencode($dt->{$dt->Title})?>' class='alertinwindow'><?php echo $dt->{$dt->Title}; ?></a>
					</td>
					<td width='30%'>
						<a href='index.php?option=com_xbank&task=balancesheet_cont.digin&digtype=<?php echo $dt->Title?>&digid=<?php echo urlencode($dt->{$dt->Title})?>' target='_blank'><?php echo "<font color='$color'>".abs($amt). " ". $postfix. "</font>";	 ?></a>
					</td>
				</tr>
			<?php endforeach;?>
			</table>
		</td>
	</tr>
</table>