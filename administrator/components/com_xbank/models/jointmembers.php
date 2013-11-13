<?php

class JointMembers extends DataMapper {
	var $table='xjointmembers';

	var $has_one = array(
        'account' => array(
            'class' => 'account',
            'join_other_as' => 'accounts',
            'join_table' => 'jos_xjointmembers',
            'other_field' => 'jointmembers'
        ),
        'member' => array(
            'class' => 'member',
            'join_other_as' => 'member',
            'join_table' => 'jos_xjointmembers',
            'other_field' => 'jointedinaccounts'
        ),
    );
}