<?php
class Staff_detail extends DataMapper {

    var $table = "xstaff_details";
    var $has_one = array(
        'detailsof' => array(
            'class' => 'staff',
            'join_other_as'=>'staff',
            'join_table' => 'jos_xstaff_details',
            'other_field' => 'details'
            )
        );
}
?>
