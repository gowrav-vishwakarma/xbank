<?php
class Document extends DataMapper{
    var $table='xdocuments';
      var $has_many = array(
        'submited_in_accounts'=>array(
            'class'=>'account',
            'join_self_as'=>'documents',
            'join_other_as'=>'accounts',
            'join_table'=>'jos_xdocuments_submitted',
            'other_field'=>'documents'
            )
          );
}

?>
