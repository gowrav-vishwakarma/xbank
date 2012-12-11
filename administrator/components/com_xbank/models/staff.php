<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Staff extends DataMapper {

    var $table = "xstaff";
    var $auto_populate_has_one = TRUE;

    var $has_one = array(
        "branch" => array(
            'class' => 'branch',
            'join_other_as' => 'branch',
            'join_table' => 'jos_xstaff',
            'other_field' => 'staffs'
        ),
        'details' => array(
            'class' => 'staff_detail',
            'join_self_as'=>'staff',
            'join_table' => 'jos_xstaff_details',
            'other_field' => 'detailsof'
        )
    );
    var $has_many = array(
        'accountsopenned' => array(
            'class' => 'account',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'opennedbystaff'
        ),
        'membershandled' => array(
            'class' => 'member',
            'join_table' => 'jos_xmembers',
            'other_field' => 'staff',
            'join_self_as'=>'staff'
        ),
        'attendance' => array(
            'class' => 'attendance',
            'join_table' => 'jos_xstaff_attandance',
            'other_field' => 'staff'
        ),
        'salaryreceived' => array(
            'class' => 'staff_payment',
            'join_table' => 'jos_xstaff_payments',
            'other_field' => 'paidto',
            'join_self_as'=>'staff'
        ),
        'transactions' => array(
            'class' => 'transaction',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'bystaff'
        )
        
    );
    var $validation = array(
        'StaffID' => array(
            'label' => 'Staff ID',
            'rules' => array('required', 'trim', 'unique')
        ),
    );


     function getDefaultStaff(){
		$b = Branch::getDefaultBranch();
		$s = $this->db->query("select s.* from jos_xstaff s join jos_users u on s.jid=u.id where s.branch_id = $b->id ")->row();
		return $s;

	}

    public static function getCurrentStaff(){
        if(! $staff_id=JFactory::getUser()->id){
 				return FALSE;
 			}
 			$s = new Staff();
                        $s->get_by_jid(JFactory::getUser()->id);
 			if($s->result_count() == 0){
 				return FALSE;
 			}
 			return $s;
    }

    public static function login($branch, $StaffID, $password){
                $s = new Staff();
                $s->where("StaffID",$StaffID)->get();
 		if($s->result_count() > 0 ){
 			$s_input = new Staff();
 			$s_input->Password = $password;
 			if($s_input->Password == $s->Password && $s->branch_id == $branch){
 				unset($s_input);
 				$CI=& get_instance();
 				$CI->session->set_userdata('staff_id',$s->id);
 				return TRUE;
 			}
 			unset($s_input);
 		}
 		return FALSE;
 	}


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

}