<?php $this->jq->useGraph(); ?>
<table width="100%" class="adminlist">
    <thead>
        <tr>
            <th class="title" width="10">S. No.</th>
            <th class="title" width="15">Running Mode</th>
            <th class="title">Branch Name</th>
            <th class="title">Balance Sheet</th>
            <th class="title">P and L</th>
            <th class="title">Accounts Summery</th>
            <th class="title">Edit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        jimport('joomla.html.html');
        $i = 0;
        foreach ($branches as $b) :
            $id = JHTML::_('grid.id', ++$i, $b->id);
            $published = JHTML::_('grid.published', $b, $i);
        ?>
            <tr class="row<?php echo $i % 2 ?>">
                <td><?php echo $i; ?></td>
                <td align="center"><?php echo $published ?>
                <?php if (Branch::getCurrentBranch()->Code != 'DFL') {
                ?>
                    <a href="index.php?option=com_xbank&task=branch_cont.swapbranchstatus&branchid=<?php echo $b->id; ?>"> !! </a>
                <?php } ?>
            </td>
            <td><?php echo $b->Name; ?></td>
            <td class="title" align="center"><a title="Edit Branch <?php echo $b->Name; ?>" href="index.php?option=com_xbank&task=branch_cont.edit&branchid=<?php echo $b->id; ?>&format=raw" class="alertinwindow">Balance Sheet</a></td>
            <td class="title" align="center"><a title="Edit Branch <?php echo $b->Name; ?>" href="index.php?option=com_xbank&task=branch_cont.edit&branchid=<?php echo $b->id; ?>&format=raw" class="alertinwindow">P and L</a></td>
            <td class="title" align="center"><a title="Edit Branch <?php echo $b->Name; ?>" href="index.php?option=com_xbank&task=branch_cont.edit&branchid=<?php echo $b->id; ?>&format=raw" class="alertinwindow">Summary</a></td>
            <td class="title" align="center">
                <?php if (Branch::getCurrentBranch()->Code != 'DFL') {
                ?>
                    <a title="Edit Branch <?php echo $b->Name; ?>" href="index.php?option=com_xbank&task=branch_cont.edit&branchid=<?php echo $b->id; ?>&format=raw" class="alertinwindow">Edit</a></td>
                <?php } ?>
        </tr>
        <?php
                endforeach;
        ?>
    </tbody>
</table>