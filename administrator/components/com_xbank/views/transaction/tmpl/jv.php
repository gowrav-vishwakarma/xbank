<?php
$this->load->library("formcomponents");
$branch=Branch::getCurrentBranch()->id;
$this->formcomponents->open("frm1","index.php?option=com_xbank&task=transaction_cont.doJV");
?>
<table width="100%" border="0">
  <tr>
    <th colspan="2">Debit</th>
    <th colspan="2">Credit</th>
  </tr>
  <tr>
    <td><div align="center">Account</div></td>
    <td><div align="center">Amount</div></td>
    <td><div align="center">Account</div></td>
    <td><div align="center">Amount</div></td>
  </tr>
  <?php
  for ($i=1;$i<=20;$i++):
  ?>
  <tr>
    <td><?php $this->formcomponents->lookupDB("AccountNumber","name='DRAccount_$i' class='input ui-autocomplete-input'","index.php?option=com_xbank&task=transaction_cont.lookupForJV&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber");?></td>
    <td><?php $this->formcomponents->text("Amount","name='dramount_$i' class='input'");?></td>
    <td><?php $this->formcomponents->lookupDB("AccountNumber","name='CRAccount_$i' class='input  ui-autocomplete-input'","index.php?option=com_xbank&task=transaction_cont.lookupForJV&format=raw",
 			array("a"=>"b"),array("AccountNumber","Name","PanNo","Scheme"),"AccountNumber");?></td>
    <td><?php $this->formcomponents->text("Amount","name='cramount_$i' class='input'");?></td>
  </tr>
    <?php
                 endfor;
    ?>
    
   <tr>
     <td colspan="2"><?php $this->formcomponents->textArea("Narration","name='Naration' rows=5 cols=50");?></td>
     <td><?php
	     $this->formcomponents->submit("Doit");
	 ?></td>
     <td><?php
	     $this->formcomponents->confirmButton("Confirm","The following transaction is about to happen","index.php?option=com_xbank&task=transaction_cont.checkJV&format=raw",true);
	 ?></td>
   </tr>
</table>
<?php
	$this->formcomponents->close();
?>