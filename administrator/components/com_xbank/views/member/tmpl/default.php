<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

global $com_params;
?>

<table width="100%" class="adminlist">
    <thead>
        <tr>
            <th class="title" width="10">#</th>
            <?php
            if ($com_params->get("member_has_code")){
            ?>
                <th>Member Code</th>
            <?php } ?>
            <th class="title" width="15">Name</th>
            <th class="title">Father/Husband Name</th>
            <th class="title">Address</th>
            <th class="title">Phone Number</th>
            <th class="title">Member Created On</th>
            <th class="title">Branch</th>
            <th class="title">Edit</th>
            <?php
            if ($com_params->get("customer_has_code")){
            ?>
                <th></th>
                <th></th>
            <?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php
                jimport('joomla.html.html');
                $i = 0;
                foreach ($member as $m) :
            $id = JHTML::_('grid.id', ++$i, $m->id);
            $published = JHTML::_('grid.published', $m, $i)
        ?>
                    <tr onMouseOver="this.bgColor='cyan';" onMouseOut="this.bgColor='inherit';" class="row<?php echo $i % 2 ?>">
                        <td><?php echo $m->id; ?></td>
                        <?php
                        if ($com_params->get("member_has_code")){
                        ?>
                            <td><?php echo $m->MemberCode; ?></td>
                        <?php } ?>
                        <td><?php echo $m->Name; ?></td>
                        <td><?php echo $m->FatherName; ?></td>
                        <td class="title" align="center"><?php echo $m->PermanentAddress; ?></td>
                        <td class="title" align="center"><?php echo $m->PhoneNos; ?></td>
                        <td class="title" align="center"><?php echo date("Y-m-d",strtotime($m->created_at)); ?></td>
                        <td class="title" align="center"><?php echo $m->registeredinbranch->Name; ?></td>
                        <td class="title" align="center"><a title="Edit member <?php echo $m->Name; ?>" href="index.php?option=com_xbank&task=member_cont.editMemberForm&id=<?php echo $m->id; ?>" >Edit</a></td>
            <?php
                    if ($com_params->get("customer_has_code")){
            ?>
                        <td><?php echo $m->CustomerCode; ?></td>
                        <td  align="center"><a title="Convert <?php echo $m->Name; ?> to Customer" href="index.php?option=com_xbank&task=member_cont.convertToCustomerForm&id=<?php echo $m->id; ?>"  !hrefok="index.php?option=com_xbank&task=member_cont.convertToCustomer&id=<?php echo $m->id; ?>&format=raw" ><?php echo ($m->IsCustomer == 0 ? "Convert To Customer" : ""); ?></a></td>
            <?php } ?>
                    </tr>
        <?php
                        endforeach;
        ?>
                    </tbody>
                </table>

<?php
//                        $x = ($start - $count) < 0 ? 0 : ($start - $count);
//                        $y = ($start + $count) > $i ? $start : ($start + $count);
    
    echo $page->getListFooter();

?>
<!--                        <p class="ui-widget ui-widget-header"><a style="float:left;" href="<?= site_url() . "?option=com_xbank&task=member_cont.dashboard&pagestart=" . $x ?>">Previous</a>
                            <a style="float:right;" href="<?= site_url() . "?option=com_xbank&task=member_cont.dashboard&pagestart=" . $y ?>">Next</a><p>-->