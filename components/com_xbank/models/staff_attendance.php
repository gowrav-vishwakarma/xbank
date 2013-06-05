<?php
class Staff_attendance extends DataMapper {

    var $table = "xstaff_attendance";
    var $has_one = array(
        'staff' => array(
            'class' => 'staff',
            'join_other_as'=>'staff',
            'join_table' => 'jos_xstaff_attandance',
            'other_field' => 'attendance'
            )
        );
}
?>
