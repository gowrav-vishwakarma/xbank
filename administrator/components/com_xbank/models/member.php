<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Member extends DataMapper {

    var $table = "xmember";
    var $has_one = array(
        'createdbystaff' => array(
            'class' => 'staff',
            'join_other_as' => 'staff',
            'join_table' => 'jos_xmember',
            'other_field' => 'membershandled'
        ),
        'registeredinbranch' => array(
            'class' => 'branch',
            'join_other_as' => 'branch',
            'join_table' => 'jos_xmember',
            'other_field' => 'registeredmembers'
        ),
        'asagent' => array(
            'class' => 'agent',
            'join_self_as' => 'member',
            'join_table' => 'jos_xagent',
            'other_field' => 'member'
        ),
    );
    var $has_many = array(
        'accounts' => array(
            'class' => 'account',
            'join_self_as' => 'member',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'member'
        )
    );

    function saveJoomlaUser($staff, $pass, $name, $newUsertype = 'Registered') {
        global $mainframe;

        // Check for request forgeries
//            JRequest::checkToken() or jexit('Invalid Token');
        // Get required system objects
        $user = clone(JFactory::getUser());
        $config = & JFactory::getConfig();
        $authorize = & JFactory::getACL();
        $document = & JFactory::getDocument();

        // Initialize new usertype setting
//        $newUsertype = null; //$usersConfig->get('new_usertype');
//        if (!$newUsertype) {
//            $newUsertype = 'Registered';
//        }

        // Bind the post array to the user object
        $userdata = array();
        $userdata['id'] = "";
        $userdata['name'] = $name;
        $userdata['username'] = $staff;
        //$userdata['username'] = $id;
        $userdata['email'] = $staff . "@xavoc.com";
        $userdata['password'] = $pass;
        $userdata['password2'] = $pass;
        if (!$user->bind($userdata, 'usertype')) {
            JError::raiseError(500, $user->getError());
        }

        // Set some initial user values
        $user->set('id', 0);
        $user->set('usertype', $newUsertype);
        $user->set('gid', $authorize->get_group_id('', $newUsertype, 'ARO'));

        $date = & JFactory::getDate();
        $user->set('registerDate', $date->toMySQL());

        $useractivation = 0; //$usersConfig->get('useractivation');
        if ($useractivation == '1') {
            jimport('joomla.user.helper');
            $user->set('activation', JUtility::getHash(JUserHelper::genRandomPassword()));
            $user->set('block', '1');
        }

        // If there was an error with registration, set the message and display form
        if (!$user->save()) {
            JError::raiseWarning('', JText::_($user->getError()));
            return false;
        }
//        $this->netmember_id = $user->id;
        $this->save();
        return $user->id;
    }

    function getCurrent() {
        $this->get_by_jid(JFactory::getUser()->id);
    }

}