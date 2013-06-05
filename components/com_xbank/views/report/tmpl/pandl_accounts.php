<table width="100%" border="1" class="ui-widget" cellpadding="3">
  <tr class="ui-widget-header">
    <td colspan="5"><div align="center"><strong style="font-size: 16px">Accounts for Scheme</strong></div></td>
  </tr>
  <tr class="ui-widget-header">
    <td><div align="center"><strong style="font-size: 16px">Date</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Transaction</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Account Name</strong></div></td>
    <td>Balance</td>
    <td><div align="center"><strong style="font-size: 16px">Debit Side</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Credit Side</strong></div></td>
  </tr>
  <?php
  foreach($results as $r){
  ?>
  <tr class="ui-widget-content">
    <td width="7%"><?php echo $r['Sno'];?></td>
    <td width="71%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.accountTransactions&format=raw&id=<?php echo urlencode($r['Account']);?>&scheme=<?php echo $forScheme;?>"><?php echo $r['Transaction'];?></a></td>
    <td><?php echo $r["AccountDisplayName"]; ?></td>
    <td style="background-color: <?php echo ($r['DR']-$r['CR']>0) ? '#D2E0E6' : '#e1e463';?> "><?php echo ($r['DR']-$r['CR']>0) ? $r['DR']-$r['CR']." Dr" : $r['CR']-$r['DR']." Cr"; ?></td>
    <td width="10%"><?php echo $r['DR'];?></td>
    <td width="12%"><?php echo $r['CR'];?></td>
  </tr>
  <?php
  }
  ?>
  <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <th><?php echo $TotalDR; ?> </th>
      <th><?php echo $TotalCR; ?> </th>
  </tr>
</table>