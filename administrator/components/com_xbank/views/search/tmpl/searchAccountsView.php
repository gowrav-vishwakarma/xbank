<?php
    echo $contents;
?>
<table width="100%" border="1" class="ui-widget ui-widget-content" id="onetwo">
  <tr class="ui-widget-header">
    <td>S.No.</td>
    <td>Account Number</td>
    <td>Debit Balance</td>
    <td>Credit Balance</td>
    <td>Balance(CR - DR)</td>
    <td>Under Scheme</td>
    <td>Member ID</td>
    <td>Member Name</td>
    <td>Active Status</td>
    <td>Edit</td>
<!--   <td></td>-->
    <?php
    if(Staff::getCurrentStaff()->AccessLevel >= BRANCH_ADMIN){
    ?>
   <td>DELETE ACCOUNT</td>
   <?php
    }
    ?>
  </tr>
    <?php
    $i=1;
    if($accounts){

	foreach($accounts as $a)
	{
            $dr = /* $a->OpeningBalanceDr + */ $a->CurrentBalanceDr;
            $cr = /* $a->OpeningBalanceCr + */ $a->CurrentBalanceCr;
            
            if($i%2 != 0)
                echo "<tr bgcolor='white'>";
            else
                echo "<tr bgcolor='#D9E8E8'>";
	?>
  
    <td><?php echo $i; ?></td>
    <td><?php echo $a->AccountNumber; ?></td>
    <td><?php echo $dr;?></td>
    <td><?php echo $cr;?></td>
    <td><?php echo ($cr - $dr > 0 ? $cr - $dr ." CR" : ($dr - $cr > 0 ? $dr - $cr." DR" : 0 ) );?></td>
    <td><?php echo $a->SchemeName; ?></td>
    <td><?php echo $a->MemberID; ?></td>
    <td><?php echo $a->MemberName; ?></td>
    <td><a href="index.php?option=com_xbank&task=accounts_cont.statuschange&id=<?php echo $a->id; ?>"><?php echo ($a->ActiveStatus== 1)? 'Active': 'DeActive'; ?></a></td>
    <td><a title="Edit Account <?php echo $a->AccountNumber;?>" href="index.php?option=com_xbank&task=accounts_cont.editAccountsForm&id=<?php echo $a->id ?>">Edit</a></td>
<!--    <td><a href="index.php?option=com_xbank&task=accounts_cont.printToPDF&id=<?php echo $a->id ?>&format=raw" target="_blank">Print To PDF</a></td>-->
     <?php
    if(Staff::getCurrentStaff()->AccessLevel >= BRANCH_ADMIN){
    ?>
    <td><a class="" title="Delete Account <?php echo $a->AccountNumber;?>" href="index.php?option=com_xbank&task=accounts_cont.deleteAccountConfirm&id=<?php echo $a->id ?>" hrefok="index.php?option=com_xbank&task=accounts_cont.deleteAccount<?php echo $a->id ?>">DELETE</a></td>
  <?php
    }
    ?>
  </tr>
    <?php
    $i++;
	} //End of foreac

    }
    else{
        echo "No account found";
    }
	?>
</table>