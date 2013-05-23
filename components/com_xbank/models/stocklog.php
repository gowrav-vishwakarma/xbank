<?php
class Stocklog extends DataMapper{
    var $table='xstock_log';
      var $has_one = array(
        'item'=>array(
            'class'=>'item',
            'join_other_as'=>'items',
            'join_table'=>'jos_xstock_log',
            'other_field'=>'stocklog'
            )
          );
}

?>
