<table width="100%">
    <tr class="ui-widget-header">
        <th>S no.</th>
        <th>Account Number</th>
        <th>Member Name</th>
        <th>Father Name</th>
        <th>Address</th>
        <th>Mobile Number</th>
        <th>Policy Number</th>
        <th>Insurrance Date</th>
        <th>Insurrance End Date</th>
    </tr>
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$i=1;
foreach($result as $r):
?>
    <tr  bgcolor="<?php if($i%2 != 0)
                            echo '#D9E8E8';
                        else
                            echo 'white';?>">
        <td><?php echo $i++; ?></td>
        <td><?php echo $r->accnum; ?></td>
        <td><?php echo $r->Name ?></td>
        <td><?php echo $r->FatherName ?></td>
        <td><?php echo $r->PermanentAddress ?></td>
        <td><?php echo $r->PhoneNos ?></td>
        <td><?php echo "#"; ?></td>
        <td><?php echo date("Y-m-d", strtotime(date("Y-m-d", strtotime($r->LoanInsurranceDate)))); ?></td>
        <td><?php echo date("Y-m-d", strtotime(date("Y-m-d", strtotime($r->LoanInsurranceDate)) . " +365 DAYS")); ?></td>
    </tr>

<?php
endforeach;
?>
</table>