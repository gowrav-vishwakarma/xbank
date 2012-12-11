<table width="100%" border="1" class="ui-widget" cellpadding="3">
  <tr class="ui-widget-header">
    <td colspan="5"><div align="center"><strong style="font-size: 16px">Schemes Under HEAD </strong></div></td>
  </tr>
  <tr class="ui-widget-header">
    <td><div align="center"><strong style="font-size: 16px">Date</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Transaction</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Debit Side</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Credit Side</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Difference Side</strong></div></td>
  </tr>
  <?php
  foreach($results as $r){
  ?>
  <tr class="ui-widget-content">
    <td width="7%"><?php echo $r['Sno'];?></td>
    <td width="71%"><a href="index.php?option=com_xbank&task=report_cont.accountLevelDisplay&id=<?php echo urlencode($r['Head_id']);?>"><?php echo $r['Head'];?></a></td>
    <td width="10%"><?php echo $r['DR'];?></td>
    <td width="12%"><?php echo $r['CR'];?></td>
    <td width="12%"><?php echo abs($r['CR'] - $r['DR']);?></td>
  </tr>
  <?php
  $DRTotal += $r['DR'];
  $CRTotal += $r['CR'];
  }
  ?>
  <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <th><?php echo $DRTotal; ?> </th>
      <th><?php echo $CRTotal; ?> </th>
      <th><?php echo abs($CRTotal-$DRTotal); ?> </th>
  </tr>
</table>
<p><?php if(!isset($backURL) or $backURL <> "") {?><a href="<?php echo $backURL;?>">Back</a><?php }?></p>