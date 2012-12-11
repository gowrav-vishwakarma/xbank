<?php

class Agent extends DataMapper {

    var $table = 'xagents';
    var $has_many = array(
        'accountsopenned' => array(
            'class' => 'account',
            'join_self_as' => 'agent',
            'join_table' => 'jos_xaccounts',
            'other_field' => 'agent'
        ),
        "sponsorof" => array(
            "class" => "agent",
            "join_self_as" => "sponsor",
            "other_field" => "sponsor",
            "join_table" => "jos_xagents"
        )
    );
    var $has_one = array(
        'member' => array(
            'class' => 'member',
            'join_other_as' => 'member',
            'join_table' => 'jos_xagents',
            'other_field' => 'asagent'
        ),
        'sponsor' => array(
            "class" => "agent",
            "join_other_as" => "sponsor",
            "other_field" => "sponsorof",
            "join_table" => "jos_xagents"
        )
    );

    public static function getAgentFromAccount($id='') {
        if ($id) {
            $a = new Account($id);
            $ag = new agent($a->agents_id);
            return $ag->member;
        } else {
            return false;
        }
    }

    function updateAncestors() {
        $ancestor = $this->sponsor;
        while ($ancestor->id) {
            $ag = new xConfig("agent");
            $levels = $ag->getKey("number_of_agent_levels");

            // ADD 1 TO RANK COUNT OF ANCESTOR
            $RankCount = "Rank_" . $this->Rank . "_Count";
            $ancestor->{$RankCount} = $ancestor->{$RankCount} + 1;

            // CHECK IF THE ANCESTOR MEETS THE CRITERIA DEFINED IN THE COMMISSION SLAB TABLE
            $check = $this->CheckAncestorsCriteria($ancestor);
            $ancestor->save();

            // DEFINE NEW ANCESTOR AS CURRENT ANCESTORS' ANCESTOR
            $ancestor = $ancestor->sponsor;
        }
    }

    function CheckAncestorsCriteria($ancestor) {
        $commSlab = new Commissionslab();
        $commSlab->where("Rank", $ancestor->Rank)->get();
//        $flag = "";
        $flag = array();
        if ($ancestor->BusinessCreditPoints > $commSlab->TotalCreditBusinessForPromotion) {
//            $flag = false;
            for ($i = 1; $i <= $ancestor->Rank; $i++) {
                $RankCount = "Rank_" . $i . "_Count";
                $level = Level . "$i";
                if ($ancestor->{$RankCount} < $commSlab->{$level}) {
//                    $flag = true;
                    $flag +=array("true");
//                    break;
                }
            }
        }
        // FLAG IS TRUE WHEN ANCESTOR MEETS THE COMMISSION SLAB CRITERIA
//        if ($flag === false) {
        if (!in_array("true", $flag) && !empty($flag)) {
            $ancestor->Rank = $ancestor->Rank + 1;
            $ancestor->BusinessCreditPoints = 0;
            $ancestor->save();
        }
    }

    function getAncestorCommissionString() {
        
    }

}

?>