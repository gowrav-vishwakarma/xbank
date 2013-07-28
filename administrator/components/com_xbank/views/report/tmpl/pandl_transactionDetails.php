<table width="100%" border="1" class="ui-widget" cellpadding="3">
  <tr class="ui-widget-header">
    <td colspan="5"><div align="center"><strong style="font-size: 16px">DETAILS OF TRANSACTIONS [<?php echo $tr_type ?>]</strong></div></td>
  </tr>
  <tr class="ui-widget-header">
    <td><div align="center"><strong style="font-size: 16px">Sno</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Voucher Number</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Account</strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Debit Account </strong></div></td>
    <td><div align="center"><strong style="font-size: 16px">Credit Account</strong></div></td>
  </tr>
  <?php
  foreach($results as $r){
  ?>
  <tr class="ui-widget-content">
    <td width="10%"><?php echo $r['Sno'];?></td>
    <td width="10%"><?php echo $r['Voucher'];?></td>
    <td width="10%"><?php echo $r['Account'];?></td>
    <td width="35%"><?php echo $r['DR'];?></td>
    <td width="35%"><?php echo $r['CR'];?></td>
  </tr>
  <?php
  }
  ?>
</table>
<p>
  <?php echo $Narration?>
</p>
<a class="alertinwindow" title="Delete Transaction" href="index.php?option=com_xbank&task=report_cont.confirmTransactionDelete&vn=<?php echo $r['Voucher_tech'] ?>&d_vn=<?php echo $r['DisplayVoucher']?>&id=<?php echo $accountID; ?>&format=raw"  >DELETE</a>
<a class="alertinwindow" title="Edit Transaction" href="index.php?option=com_xbank&task=report_cont.confirmTransactionEdit&vn=<?php echo $r['Voucher_tech'] ?>&d_vn=<?php echo $r['DisplayVoucher']?>&id=<?php echo $accountID; ?>&format=raw"  >EDIT TRANSACTION</a>