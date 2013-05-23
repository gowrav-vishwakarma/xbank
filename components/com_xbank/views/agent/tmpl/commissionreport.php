<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$ag = new xConfig("agent");
?>
<table width="100%" class="adminlist">
    <thead>
        <tr>
            <th class="title" width="10">#</th>
            <th class="title" width="70"> Advisor Name</th>
            <th class="title" width="15">Account No.</th>
            <th class="title" >Advisor Level</th>
            <th class="title" >Commission Payable</th>
            <th class="title">Commission Transferred On</th>
            <th class="title">Narration</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $agentLevels = $ag->getKey("number_of_agent_levels");
        jimport('joomla.html.html');
        $i = 0;
        foreach ($agent as $a) :
//            $id = JHTML::_('grid.id', ++$i, $m->id);
//            $published = JHTML::_('grid.published', $m, $i
            ?>
            <tr class="row<?php echo ++$i ?>">
                <td><?php echo $a->id; ?></td>
                <td><?php $ag=new Agent($a->agents_id); echo $ag->member->Name; ?></td>
                <td><?php $ac=new Account($a->accounts_id); echo $ac->AccountNumber; ?></td>
                <td><?php $l = new Commissionslab(); $l->where("Rank",$ag->Rank)->get(); echo $l->AdvisorLevel; ?></td>
                <td><?php echo $a->Commission; ?></td>
                <td class="title" align="center"><?php echo date("m-d-Y",strtotime($a->CommissionPayableDate)); ?></td>
                <td class="title" align="center"><?php echo $a->Narration; ?></td>
            </tr>
            <?php
        endforeach;
        ?>
    </tbody>
</table>