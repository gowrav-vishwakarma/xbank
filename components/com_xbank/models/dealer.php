<?php
class Dealer extends DataMapper {
    var $table='xdealer';
     var $has_many = array(
        'accounts' => array(
            'class' => 'account',
            'join_self_as' => 'dealer',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'dealer'
        )
         );
}
?>
