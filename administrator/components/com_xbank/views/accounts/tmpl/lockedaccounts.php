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
            <th class="title" width="15">AccountNumber</th>
            <th class="title">Member Name</th>
            <th class="title">CR Balance</th>
            <th class="title">DR Balance</th>
            <th class="title">Locked Account</th>
            <th class="title">Locking Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        jimport('joomla.html.html');
        $i = 0;
        foreach ($acc as $a) :
            $id = JHTML::_('grid.id', ++$i, $a->id);
            
        ?>
            <tr class="row<?php echo $i % 2 ?>">
                <td><?php echo $a->id; ?></td>
                <td align="center">
                    <? echo $a->AccountNumber; ?>
               </td>
            <td><?php echo $a->member->Name; ?></td>
            <td class="title" align="center"><?php echo $a->CurrentBalanceCr; ?></td>
            <td class="title" align="center"><?php echo $a->CurrentBalanceDr; ?></td>
            <td class="title" align="center"><?php $lockedAcc = new Account($a->LoanAgainstAccount);
                                                   echo $lockedAcc->AccountNumber;
                                                   $published = ($lockedAcc->LockingStatus == 0 ? "Unocked" : "Locked"); ?></td>
            <td class="title" align="center"><?php echo $published; ?><a href="index.php?option=com_xbank&task=accounts_cont.swaplockedstatus&id=<?php echo $lockedAcc->id; ?>"> !! </a></td>
                
        </tr>
        <?php
                endforeach;
        ?>
    </tbody>
</table>