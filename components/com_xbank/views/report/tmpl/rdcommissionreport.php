<table width="100%" border="1" class="ui-widget" cellpadding="3">
  <tr class="ui-widget-header">
    <td><div align="center"><strong style="font-size: 16px">Account Number</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Amount</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Scheme Name</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Commission</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">TDS</strong></div></td>
  </tr>
  <?php
  foreach($results as $r){
  ?>
  <tr class="ui-widget-content">
    <td width="7%"><?php echo $r['Sno'];?></td>
    <td width="71%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.accountTransactions&id=<?php echo urlencode($r['Account']);?>/<?php echo $forScheme;?>"><?php echo $r['Transaction'];?></a></td>
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