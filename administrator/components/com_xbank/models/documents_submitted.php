<?php
class Documents_submitted extends DataMapper{
    var $table='xdocuments_submitted';
    var $has_one=array(
        'document'=>array(        
                'class'=>'document',
                'join_other_as'=>'documents',
                'join_table'=>'jos_xdocuments_submitted',
                'other_field'=>'documents_submitted'
        ),
        'account'=>array(        
                'class'=>'account',
                'join_other_as'=>'accounts',
                'join_table'=>'jos_xdocuments_submitted',
                'other_field'=>'documents'
            )
        );
}
