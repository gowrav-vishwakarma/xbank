<?php
if ( JRequest::getVar( "format" ) != "raw" ) {
?>
<a href="index.php?option=com_xbank&task=report_cont.dayBook&format=raw" target="bvvgd">Print Day Book</a>
<?php }
?>
<table class='ui-widget ui-widget-content ui-corner-all' width='100%'>
    <tr class='ui-widget-header'><td colspan=6 align='center'>BHAWANI CREDIT CO-OP SOCIETY LTD., <?php echo Branch::getCurrentBranch()->Name; ?></td></tr>
    <tr class='ui-widget-header'><td colspan=6 align='center'>DAY BOOK</td></tr>
<tr class='ui-widget-header'><td colspan=6 align='center'>for <?php echo date( "j M, Y", strtotime( $date ) ) ?></tr>
<tr class='ui-widget-header'>
    <?php
foreach ( array_values( $keyandvalues ) as $header ) {
?>
    <th><?php echo $header ?></th>
    <?php
}
?>
</tr>
<?php
$newvch = 0;
$newvch_d = 0;
$prevvch = 0;
$prevvch_d = 0;
$tmp = 0;
$tmp_d = 0;
foreach ( $results as $rs ) {
    $insert_row = "";
    $newvch = $rs->voucher_no;
    $newvch_d = $rs->display_voucher_no;
    if ( $newvch != $prevvch or $newvch_d != $prevvch_d ) {
        $insert_row = "<tr><td colspan=5 align='center'>&nbsp;</td></tr>";
        $prevvch = $newvch;
        $prevvch_d = $newvch_d;
    }
    echo $insert_row;
?>
<tr style="font-size:14px;font-family:  Gill, Helvetica, sans-serif ">
    <?php
    foreach ( array_keys( $keyandvalues ) as $field ) {
        $field = trim( $field );
        if ( $field == "voucher_no" && $rs->$field != $tmp or $rs->display_voucher_no != $tmp_d ) {
            $newvch = $rs->$field;
            $newvch_d = $rs->display_voucher_no;
            $tmp = $rs->$field;
            $tmp_d = $rs->display_voucher_no;
?>
    <td><a class='alertinwindow' href='index.php?option=com_xbank&task=report_cont.transactionDetails&vn=<?php echo $rs->$field ?>&d_vn=<?php echo $rs->display_voucher_no ?>&format=raw&tr_type=<?php echo $rs->transaction_type_id ?>'><?php echo $rs->display_voucher_no != 0 ? $rs->display_voucher_no : $rs->voucher_no?></a></td>
    <?php
        } else {
            if ( $field == "voucher_no" ) {
?>
    <td>&nbsp;</td>
    <?php
            }else {
                if ( $field =="Narration" ) {
                    $narration = str_replace("10 (",'',$rs->$field );
                    $narration = str_replace(")",'',$narration );
                    $member = new Member( $narration );
                    echo "<td>".$rs->$field."  ".$member->Name."</td>";
                }elseif ($field =='AccountNumber') {
                    $account_for_name = new Account();
                    $account_for_name->where('AccountNumber',$rs->$field);
                    $account_for_name->get();

                    echo "<td>".$rs->$field. " [ ".$account_for_name->member->Name." ] " ."</td>";
                }else {
?>
    <td><?php echo $rs->$field?></td>
    <?php
                }
            }
        }
    }
?>
</tr><?php
}
?>
</table>
