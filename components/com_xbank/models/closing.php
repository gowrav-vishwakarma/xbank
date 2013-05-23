<?php
class Closing extends DataMapper {
    var $table='xclosings';
    
    var $has_one=array(
        'branch'=>array(
            'class'=>'branch',
            'join_other_as'=>'branch',
            'other_field'=>'closing',
            'join_table'=> 'jos_xclosings'
        )
        );
}
?>
