<?php
class Category extends DataMapper{
    var $table='xcategory';
      var $has_many = array(
        'items'=>array(
            'class'=>'item',
            'join_self_as'=>'category',
            'join_table'=>'jos_xitems',
            'other_field'=>'category'
            )
          );
}

?>
