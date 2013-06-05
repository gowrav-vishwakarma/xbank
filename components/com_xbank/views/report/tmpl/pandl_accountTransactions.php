<table width="100%" border="1" class="ui-widget" cellpadding="3">
  <tr class="ui-widget-header">
    <td colspan="4"><div align="center"><strong style="font-size: 16px">TRANSACTIONS OF Account <?php echo $AccountNumber; ?></strong></div></td>
  </tr>
  <tr class="ui-widget-header">
    <td><div align="center"><strong style="font-size: 16px">Date</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Transaction</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Debit Side</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Credit Side</strong></div></td>
  </tr>
  <?php
  foreach($results as $r){
  ?>
  <tr class="ui-widget-content">
    <td width="7%"><?php echo $r['Date'];?></td>
    <td width="71%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.transactionDetails&vn=<?php echo urlencode($r['VoucherNumber']);?>&id=<?php echo $account;?>&format=raw"><?php echo $r['Transaction']." [".$r['referenceAccount']."]";?></a></td>
<!--     <td width="71%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.transactionDetails&vn=<?php echo urlencode($r['VoucherNumber']);?><?php echo $account;?>"><?php echo $r['Transaction']." [".$r['referenceAccount']."]";?></a></td>-->

    <td width="10%"><?php echo $r['DR'];?></td>
    <td width="12%"><?php echo $r['CR'];?></td>
  </tr>
  <?php
  }
  ?>
  <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <th><?php echo $TotalDR; ?> </th>
      <th><?php echo $TotalCR; ?> </th>
  </tr>
</table>


