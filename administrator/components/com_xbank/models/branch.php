<?php

class Branch extends DataMapper {

    var $table = 'xbranch';
    var $has_one = array(
        'closing' => array(
            'class' => 'closing',
            'join_self_as' => 'branch',
            'other_field' => 'branch',
            'join_table' => 'jos_xclosings'
        )
    );
    var $has_many = array(
        'accounts' => array(
            'class' => 'account',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'branch'
        ),
        'holidays' => array(
            'class' => 'holiday',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xbank_holidays',
            'other_field' => 'inbranch'
        ),
        'registeredmembers' => array(
            'class' => 'member',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xmember',
            'other_field' => 'registeredinbranch'
        ),
        'schemes' => array(
            'class' => 'scheme',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xschemes',
            'other_field' => 'branch'
        ),
        'staffs' => array(
            'class' => 'staff',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xstaff',
            'other_field' => 'branch'
        ),
        'transactions' => array(
            'class' => 'transaction',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xtransactions',
            'other_field' => 'branch'
        ),
        'logs' => array(
            'class' => 'log',
            'join_self_as' => 'branch',
            'join_table' => 'jos_xlog',
            'other_field' => 'branch'
        ),
    );
    var $validation = array(
        'Name' => array(
            'label' => 'Branch Name',
            'rules' => array('required', 'trim', 'unique')
        ),
        'Code' => array(
            'label' => 'Branch Code',
            'rules' => array('required', 'trim', 'unique')
        ),
    );

    function getPandL() {
        
    }

    public static function getDefaultBranch() {
        $b = new Branch();
        $b->where("Name", "Default")->get();
        return $b;
    }

    public static function getDefaultMember($b='') {
        if ($b == '')
            $b = Branch::getCurrentBranch();
        $toFind = $b->Code . " Default";
        $m = new Member();
        $m->get_by_name($toFind);
        return $m;
    }

    public static function getAllBranches() {
        $b = new Branch();
        $b->get();
        return $b;
    }

    public static function getCurrentBranch() {
//        $s = Current_Staff::staff();
        $s = new Staff();
        $s->where("jid",JFactory::getUser()->id)->get();
        $b = new Branch($s->branch_id);
        return $b;
    }

    public static function getAllSchemesForCurrentBranch($asArray=true, $forSelect=true) {
//        $Acct = Doctrine::getTable('Schemes')->findByBranch_idOrBranch_id(Branch::getCurrentBranch()->id, Branch::getDefaultBranch()->id);
        $Acct = new Scheme();
        $Acct->where("SchemeType", $type);
        $Acct->group_start();
        $Acct->where("branch_id", Branch::getCurrentBranch()->id);
        $Acct->or_where("branch_id", Branch::getDefaultBranch()->id);
        $Acct->group_end();
        $Acct->get();

        $arr = array();
        if ($forSelect)
            $arr += array("Select_Account_Type" => '-1');

        foreach ($Acct as $h) {
            $arr +=array($h->Name => $h->id);
        }
        return $arr;
    }

    public static function getAllSchemesForCurrentBranchOfType($type, $asArray=true, $forSelect=true) {
//        $Acct = Doctrine::getTable('Schemes')->findBySchemetypeAndBranch_idOrBranch_id($type, Branch::getCurrentBranch()->id, Branch::getDefaultBranch()->id);
        $Acct = new Scheme();
        $Acct->where('published',1);
        $Acct->where("SchemeType", $type);
        $Acct->group_start();
        $Acct->where("branch_id", Branch::getCurrentBranch()->id);
        $Acct->or_where("branch_id", Branch::getDefaultBranch()->id);
        $Acct->group_end();
        $Acct->get();
        // $Acct->check_last_query();
        $arr = array();
        if ($forSelect)
            $arr += array("Select_Account_Type" => '-1');

        foreach ($Acct as $h) {
            if ($type == ACCOUNT_TYPE_DEFAULT && $h->Name == BRANCH_AND_DIVISIONS)
                continue;
            $arr +=array($h->Name => $h->id);
        }
        return $arr;
    }

    public static function getAllSchemesForCurrentBranchOfName($Name, $asArray=true, $forSelect=true) {
//        $Acct = Doctrine::getTable('Schemes')->findByNameAndBranch_idOrBranch_id($Name, Branch::getCurrentBranch()->id, Branch::getDefaultBranch()->id);
        $Acct = new Scheme();
        $Acct->where("Name", $Name);
        $Acct->where('published',1);
        $Acct->group_start();
        $Acct->where("branch_id", Branch::getCurrentBranch()->id)->get();
         $Acct->or_where("branch_id", Branch::getDefaultBranch()->id);
        $Acct->group_end();
        $Acct->get();
        
        $arr = array();
        if ($forSelect)
            $arr += array("Select_Account_Type" => '-1');

        foreach ($Acct as $h) {
            $arr +=array($h->Name => $h->id);
        }
        return $arr;
    }

    public static function getAllBranchNames() {
        $Acct = new Branch();
        $Acct->get();
        $arr = array();
        $arr +=array("All" => "%");
        foreach ($Acct as $h) {
            $arr +=array($h->Name => $h->id);
        }
        return $arr;
    }

}