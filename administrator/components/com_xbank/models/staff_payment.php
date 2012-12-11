<?php
class Staff_payment extends DataMapper {

    var $table = "xstaff_payments";
    var $has_one = array(
        'paidto' => array(
            'class' => 'staff',
            'join_other_as'=>'staff',
            'join_table' => 'jos_xstaff_payments',
            'other_field' => 'salaryreceived'
        )
        );
}
?>
