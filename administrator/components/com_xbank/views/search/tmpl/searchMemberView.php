<?php
    echo $contents;
?>
<table width="100%" border="1" class="ui-widget ui-widget-content">
  <tr class="ui-widget-header">
    <td>Member Id</td>
    <td>Name</td>
    <td>Father Name</td>
    <td>Current Address</td>
    <td>Occupation</td>
    <td>Age</td>
    <td>Phone Numbers</td>
<!--    <td>Share Account</td>-->
    <td>Edit</td>
  </tr>
    <?php
    if($members){
	foreach($members as $m)
	{
	?>
  <tr>
    <td><?php echo $m->id; ?></td>
    <td><?php echo $m->Name; ?></td>
    <td><?php echo $m->FatherName; ?></td>
    <td><?php echo $m->CurrentAddress; ?></td>
    <td><?php echo $m->Occupation; ?></td>
    <td><?php echo $m->Age; ?></td>
    <td><?php echo $m->PhoneNos; ?></td>
<!--    <td><?php echo $m->AccountNumber; ?></td>-->
    <td class="title" align="center"><a class="" title="Edit member <?php echo $m->Name; ?>" href="index.php?option=com_xbank&task=member_cont.editMemberForm&id=<?php echo $m->id; ?>"  hrefok="index.php?option=com_xbank&task=member_cont.editMemberForm&id=<?php echo $m->id; ?>&format=raw" >Edit</a></td>


  </tr>
    <?php
	} //End of foreac
    }
    else{
        echo "No member found";
    }
	?>
</table>