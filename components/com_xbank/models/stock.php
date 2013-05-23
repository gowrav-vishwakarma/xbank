<?php
class Stock extends DataMapper{
    var $table='xstock';
      var $has_one = array(
        'item'=>array(
            'class'=>'item',
            'join_other_as'=>'items',
            'join_table'=>'jos_xstock',
            'other_field'=>'stock'
            )
          );
}

?>
