<?php
class Balance_sheet extends DataMapper {
    var $table='xbalance_sheet';
    var $has_many=array(
    'schemes'=>array(
            'class'=>'scheme',
            'join_self_as'=>'balance_sheet',
            'join_table'=>'jos_xschemes',
            'other_field'=>'balance_sheet'
        )
        );

    
}
?>
