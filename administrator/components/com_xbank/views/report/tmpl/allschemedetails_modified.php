<script type="text/javascript">

    /* call onload with table id(s) */
    function showDetails(id)
    {
        var img = "img"+id;
        if(document.getElementById(id).style.display == ""){
            document.getElementById(id).style.display = "none";
            document.getElementById(img).src='components/com_xbank/images/plus.gif'
        }
        else{
            document.getElementById(id).style.display = "";
            document.getElementById(img).src='components/com_xbank/images/minus.gif'
        }
    }
</script>
<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 if(inp('BranchId')!='%')
            $q = "and a.branch_id=" . inp("BranchId");
        else {
            $q = " ";
        }
foreach ($accountcount as $st) {
?>
    <div><img src="components/com_xbank/images/plus.gif" name="img<?php echo $st->SchemeType?>" align="absmiddle" id="img<?php echo $st->SchemeType?>" onclick="javascript:showDetails('<?php echo $st->SchemeType ?>')" ><?php echo $st->SchemeType . " ($st->cnt)"; ?></div>
<?php

    $accounts = $this->db->query("select a.id, a.AccountNumber,a.created_at,a.CurrentBalanceCr,a.CurrentBalanceDr,m.Name,m.FatherName,m.PermanentAddress,m.Age,m.Nominee, m.RelationWithNominee,ag.member_id as mid,ag.AccountNumber as agAccount,m.PhoneNos, b.Name as branchname, d.DealerName from jos_xaccounts a join jos_xschemes s on s.id=a.schemes_id join jos_xmember m on a.member_id = m.id join jos_xbranch b on a.branch_id = b.id left join jos_xdealer d on a.dealer_id=d.id left join jos_xagents ag on ag.id=a.agents_id  where a.created_at between '" . inp('fromDate') . "' and DATE_ADD('" . inp('toDate') . "', INTERVAL +1 DAY) and s.SchemeType = '$st->SchemeType' and a.DefaultAC = 0 ".$q)->result();
?>
    <table width="100%" id="<?php echo $st->SchemeType ?>" style="display:none">
        <tr class="ui-widget-header" align="left">
            <th>S No</th>
            <th>Account Number</th>
            <th>Member</th>
             <th>Father Name</th>
             <th>Address</th>
            <th>Phone Numbers</th>
            <th>Age</th>
             <th>Nominee</th>
            <th>Relation With Nominee</th>
        </tr>
    <?php
    $i = 0;
    $total = 0;
    foreach ($accounts as $acc) {
    $total += abs($acc->CurrentBalanceCr - $acc->CurrentBalanceDr);
    $i++;
    if($i%2 != 0)
        echo "<tr bgcolor='white'>";
    else
        echo "<tr bgcolor='#D9E8E8'>";
            ?>
            <td><?php echo $i ?></td>
            <td><?php echo $acc->AccountNumber ?></td>
            <td><?php echo $acc->Name ?></td>
            <td><?php echo $acc->FatherName ?></td>
            <td><?php echo $acc->PermanentAddress ?></td>
            <td><?php echo $acc->PhoneNos ?></td>
            <td><?php echo $acc->Age ?></td>
            <td><?php echo $acc->Nominee ?></td>
            <td><?php echo $acc->RelationWithNominee ?></td>
        <?php echo "</tr>"; ?>



    <?php
    }
    ?>
</table><br>
<?php
}
?>
