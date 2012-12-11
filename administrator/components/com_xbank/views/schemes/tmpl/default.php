<?php $this->jq->useGraph(); ?>
<table width="100%" class="adminlist">
    <thead>
        <tr>
            <th class="title">ID</th>
            <th class="title">Name</th>
            <th class="title">Interest</th>
            <th class="title">Scheme Type</th>
            <th class="title">Acc Open Comm</th>
            <th class="title">Commission</th>
            <th class="title">Active Status</th>
            <th class="title">Edit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        jimport('joomla.html.html');
        $i = 0;
        foreach ($schemes as $s) :
            $id = JHTML::_('grid.id', ++$i, $s->id);
            $published = JHTML::_('grid.published', $s, $i);
        ?>
            <tr class="row<?php echo $i % 2 ?>">
                <td><?php echo $s->id; ?></td>
                <td><?php echo $s->Name; ?></td>
                <td class="title"><?php echo $s->Interest; ?></td>
                <td><?= $s->SchemeType ?></td>
                <td><?= $s->AccountOpenningCommission ?></td>
                <td><?= $s->Commission ?></td>
                <td align="center"><?php echo $published ?><a href="index.php?option=com_xbank&task=schemes_cont.swapschemestatus&schemeid=<?php echo $s->id; ?>"> !! </a></td>

                <td class="title" align="center">
                <?php if (JFactory::getUser()->gid > 23): ?>
                    <a title="Edit Schemes <?php echo $s->Name; ?>" href="index.php?option=com_xbank&task=schemes_cont.edit&schemeid=<?php echo $s->id; ?>&format=raw" class="alertinwindow">Edit</a>
                <?php
                    endif;
                ?>
                </td>

            </tr>
        <?php
                    endforeach;
        ?>
    </tbody>
</table>