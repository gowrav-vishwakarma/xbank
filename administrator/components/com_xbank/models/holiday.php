<?php

class holiday extends DataMapper {

    var $table = 'xbank_holidays';
    var $has_one = array(
        'inbranch' => array(
            'class' => 'branch',
            'join_other_as' => 'branch',
            'join_table' => 'jos_xbank_holidays',
            'other_field' => 'holidays'
        )
    );

}

?>
