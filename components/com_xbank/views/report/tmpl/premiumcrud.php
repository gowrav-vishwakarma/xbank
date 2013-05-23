<?php
$this->load->library("formcomponents");
$this->formcomponents->open("frm1","index.php?option=com_xbank&task=report_cont.setPremiums");
$i = 1;
?>
<h2>Premiums For Account <?php echo $AccountNumber; ?></h2>
<table width="100%" border="0">
  <tr>
    <td><div >S no</div></td>
    <td><div >Account</div></td>
    <td><div >Amount</div></td>
    <td><div align="center">Paid</div></td>
    <td><div align="center">Paid On</div></td>
    <td><div align="center">Due Date</div></td>
    <td><div align="center">Agent Commission Given</div></td>
    <td></td>
  </tr>
  <?php
  foreach ($p as $pr):
  ?>
  <tr>
      <td><?php echo $i++; ?></td>
    <td><?php echo $AccountNumber;?></td>
    <td><?php echo $pr->Amount;?></td>
    <td><?php $this->formcomponents->text("Paid","name='Paid_$pr->id' class='input' value='$pr->Paid'");?></td>
    <td><?php $this->formcomponents->dateBox("Paid On","name='PaidOn_$pr->id' class='input' value='$pr->PaidOn'");?></td>
    <td><?php echo $pr->DueDate;?></td>
    <td><?php $this->formcomponents->checkBox("Agent Commission Send","name='AgentCommissionSend_$pr->id' class='input'",$pr->AgentCommissionSend);?></td>
    <td><input type="hidden" name="id" value="<?php echo $pr->id;?>"></td>
  </tr>
    <?php
                 endforeach;
    ?>

   <tr>
     <td><?php
	     $this->formcomponents->submit("Change");
	 ?></td>
     <td></td>
   </tr>
</table>
<input type="hidden" name="ac_id" value="<?php echo $ac_id;?>">
<?php
	$this->formcomponents->close();
?>