<table width="100%">
    <tr class="ui-widget-header">
        <th>S no.</th>
        <th>Account Number</th>
        <th>Member Name</th>
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
    <tr class="ui-widget-content">
        <td><?php echo $i++; ?></td>
        <td><?php echo $r->AccountNumber; ?></td>
        <td><?php echo $r->Name ?></td>
        <td><?php echo date("Y-m-d", strtotime(date("Y-m-d", strtotime($r->LoanInsurranceDate)))); ?></td>
        <td><?php echo date("Y-m-d", strtotime(date("Y-m-d", strtotime($r->LoanInsurranceDate)) . " +365 DAYS")); ?></td>
    </tr>

<?php
endforeach;
?>
</table>