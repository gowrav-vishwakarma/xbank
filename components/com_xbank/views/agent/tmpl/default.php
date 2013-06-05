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
            <th class="title" >Current Business Points</th>
            <?php if($ag->getKey("manually_promote_agent")){ ?>
                <th class="title" >Promote Advisor</th>
            <?php } ?>
<!--            <th class="title">Address</th>
            <th class="title">Phone Number</th>
            <th class="title">Branch</th>-->
<!--            <th class="title">Edit</th>-->
        </tr>
    </thead>
    <tbody>
        <?php
        
        $agentLevels = $ag->getKey("number_of_agent_levels");
        jimport('joomla.html.html');
        $i = 0;
        foreach ($agent as $a) :
            $id = JHTML::_('grid.id', ++$i, $a->id);
            $published = JHTML::_('grid.published', $a, $i)
            ?>
            <tr class="row<?php echo $i % 2 ?>">
                <td><?php echo $a->id; ?></td>
                <td><?php echo $a->member->Name; ?></td>
                <td><?php echo $a->AccountNumber; ?></td>
                <td><?php $l = new Commissionslab(); $l->where("Rank",$a->Rank)->get(); echo $l->AdvisorLevel; ?></td>
                <td><?php echo $a->BusinessCreditPoints; ?></td>
                <?php if($ag->getKey("manually_promote_agent")){ ?>
                    <td class="title" align="center"><?php if($a->Rank < $agentLevels) { ?><a title="Promote Advisor <?php echo $a->member->Name; ?>" href="index.php?option=com_xbank&task=agent_cont.promoteAgentManually&id=<?php echo $a->id; ?>">Promote</a><?php } ?></td>
                <?php } ?>
<!--                <td class="title" align="center"><?php echo $m->PermanentAddress; ?></td>
                <td class="title" align="center"><?php echo $m->PhoneNos; ?></td>
                <td class="title" align="center"><?php echo $m->registeredinbranch->Name; ?></td>-->
<!--                <td class="title" align="center"><a title="Edit agent <?php echo $a->member->Name; ?>" href="index.php?option=com_xbank&task=agent_cont.edit&id=<?php echo $a->id; ?>&format=raw" class="alertinwindow">Edit</a></td>-->
            </tr>
            <?php
        endforeach;
        ?>
    </tbody>
</table>

<?php  echo $page->getListFooter();
?>