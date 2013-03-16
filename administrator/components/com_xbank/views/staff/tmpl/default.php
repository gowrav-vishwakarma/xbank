<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<table width="100%" class="adminlist">
    <thead>
        <tr>
            <th class="title" width="10">#</th>
            <th class="title" width="15">Staff ID</th>
            <th class="title" width="15">Staff Name</th>
            <th class="title" width="15">Branch Name</th>
            <th class="title">Access Level</th><!--
            <th class="title">Phone Number</th>
            <th class="title">Branch</th>-->
            <th class="title">Edit</th>
            <th class="title">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        jimport('joomla.html.html');
        $i = 0;
        foreach ($staff as $s) :
            $id = JHTML::_('grid.id', ++$i, $s->id);
            $published = JHTML::_('grid.published', $s, $i);
            ?>
            <tr class="row<?php echo $i % 2 ?>">
                <td class="title"><?php echo $s->id; ?></td>
                <td class="title"><?php echo $s->StaffID; ?></td>
                <td class="title"><?php echo $s->details->Name; ?></td>
                <td class="title"><?php echo $s->branch->Name; ?></td>
                <td class="title" align="center">
                    <?php
                        if($s->AccessLevel == xADMIN) echo "Admin";
                        if($s->AccessLevel == BRANCH_ADMIN) echo "Branch Admin";
                        if($s->AccessLevel == POWER_USER) echo "Power User";
                        if($s->AccessLevel == USER) echo "User";
                    ?>
                </td><!--
                <td class="title" align="center"><?php echo $m->PhoneNos; ?></td>
                <td class="title" align="center"><?php echo $m->registeredinbranch->Name; ?></td>-->
                <td class="title" align="center"><a title="Edit Staff <?php echo $s->StaffID; ?>" href="index.php?option=com_xbank&task=staff_cont.createStaffform&id=<?php echo $s->id; ?>" !class="alertinwindow">Edit</a></td>
                <td class="title" align="center"><a title="Edit Staff <?php echo $s->StaffID; ?>" href="index.php?option=com_xbank&task=staff_cont.swapStatus&id=<?php echo $s->id; ?>" !class="alertinwindow"><?php echo $s->userStatus(); ?></a></td>
            </tr>
            <?php
        endforeach;
        ?>
    </tbody>
</table>

<?php
        //echo $page->getListFooter();
?>
