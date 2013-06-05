<?php
class Item extends DataMapper {
    var $table='xitems';
     var $has_one = array(
        'category' => array(
            'class' => 'category',
            'join_other_as' => 'category',
            'join_table' => 'jos_xitems',
            'other_field' => 'items'
        ),

         'stock'=> array(
             'class' => 'stock',
             'join_self_as' => 'items',
             'join_table' => 'jos_xstock',
             'other_field' => 'item'
         )
         );

     var $has_many = array(
       'stocklog'  => array(
           'class' => 'stocklog',
           'join_self_as' => 'items',
           'join_table' => 'jos_xstocklog',
           'other_field' => 'item'
       )
     );
}
?>
