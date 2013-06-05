<?php

class Transaction_type extends DataMapper {

    var $table = "xtransaction_type";
    var $has_many = array(
        'transactions' => array(
            'class' => 'transaction',
            'join_self_as' => 'transaction_type',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'transaction_type'
        )
    );

}

?>
