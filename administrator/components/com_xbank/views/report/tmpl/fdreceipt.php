<?php
    if(JRequest::getVar("format") != "raw"){
?>
<a href="index.php?option=com_xbank&task=printing_cont.fdPrint&format=raw" target="bdvvgd">Print FD Receipt</a>
<?php }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>



<style type="text/css">
<!--
td {
	font-size: 14px;
}
-->
</style>
</head>

<body>
<table width="1024" height="600" border="0" align="center" background="components/com_xbank/images/fd_receipt.jpg" style="background: url('components/com_xbank/images/fd_receipt.jpg') no-repeat;">
  <tr>
    <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td height="104">&nbsp;</td>
        </tr>
      <tr>
        <td height="34"><table width="100%"  height="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="14%" height="19">&nbsp;</td>
            <td width="13%" align="center"><?php echo 'BCCS '.trim($fd->AccountNumber,'SM'); ?></td>
            <td width="7%">&nbsp;</td>
            <td width="13%" valign="middle"><?php echo $fd->AccountNumber; ?></td>
            <td width="7%">&nbsp;</td>
            <td width="12%" align="center" valign="middle"><?php echo date("Y-m-d",strtotime($fd->created_at)); ?></td>
            <td width="8%">&nbsp;</td>
            <td width="12%" align="center" valign="middle"><?php echo $fd->CurrentBalanceCr/RATE_PER_SHARE; ?></td>
            <td width="14%">&nbsp;</td>
          </tr>
        </table></td>
        </tr>
      <tr>
        <td height="154">&nbsp;</td>
        </tr>
      <tr>
        <td height="38"><table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="36%" height="47">&nbsp;</td>
            <td width="64%" valign="bottom"><?php echo $fd->member->Name;?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td height="37"><table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="37%" height="41">&nbsp;</td>
            <td width="63%" valign="bottom"><?php echo $fd->CurrentBalanceCr/RATE_PER_SHARE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td height="95">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>