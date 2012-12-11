<?php
//foreach($results as $head=>$scheme){
//    echo $head."<br>";
//    foreach($scheme as $sc=>$account){
//        $totalDr =0;
//        $totalCr =0;
//        echo $account["SchemeName"]." =>    ";
//        foreach($account["Account"] as $acc){
//            echo "<pre>";
//            print_r($acc);
//            echo "</pre>";
//            $totalDr += $acc["Debit"];
//            $totalCr += $acc["Credit"];
//
//        }
//        echo $totalDr."     ".$totalCr."<br>";
//    }
//}

//foreach ($results as $head => $scheme) {
//    echo $head . "<br>";
//
//$totalDr = 0;
//$totalCr = 0;
//    foreach ($scheme as $sc => $account) {
////        echo $account["SchemeType"];
//        switch ($account["SchemeType"]) {
//            case ACCOUNT_TYPE_LOAN :
//                foreach ($account["Account"] as $acc) {
////                            echo "<pre>";
////                            print_r($acc);
////                            echo "</pre>";
//                    $totalDr += $acc["OpeningBalanceDr"] + $acc["Debit"];
//                    $totalCr += $acc["OpeningBalanceDr"] + $acc["Credit"];
//                }
//                break;
//        }
//    }
//    echo $totalDr . "     " . $totalCr . "<br>";
//}
?>

<?php
$gtliabilities = 0;
$gtassets = 0;
$suspenceAmount = 0;
$pandlasset = 0;
$pandlliabilities = 0;
if ($pandlBalance < 0) {
    $pandlasset = $pandlBalance;
}
if ($pandlBalance > 0) {
    $pandlliabilities = $pandlBalance;
}
?>
<script type="text/javascript">

    /* call onload with table id(s) */
    function showDetails(id)
    {
        if(document.getElementById(id).style.display == "")
            document.getElementById(id).style.display = "none";
        else
            document.getElementById(id).style.display = "";
    }


</script>
<table width="100%" border="1">
    <tr class="ui-widget-header" align="center"><td><strong style="font-size:16px">BHAWANI CREDIT CO-OPERATIVE SOCIETY</strong></td></tr>
    <tr class="ui-widget-header" align="center" ><td><strong style="font-size:14px">BALANCE SHEET</strong></td></tr>
    <tr class="ui-widget-header" align="center"><td><?php echo date("j M, Y", strtotime(inp("fromDate"))) . "   to   " . date("j M, Y", strtotime(inp("toDate"))); ?></td></tr>
</table>
<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr>
        <td width="50%" colspan="2" valign="top" class="ui-widget-header" scope="row"><div align="center"><strong>Liabilities</strong></div></td>
        <td width="50%" colspan="2" valign="top"  class="ui-widget-header" scope="row"><div align="center"><strong>Assets</strong></div></td>
    </tr>
    <tr>
        <td colspan="2" valign="top">

            <table width="100%" border="0" cellpadding="3" class="ui-widget">
<?php
$HeadsToTake = array("Capital Account", "Liabilities", "Current Liabilities", "Suspence Account", "Branch/Divisions");
foreach ($results as $head => $scheme) {
    $total = 0;
    if (!in_array($head, $HeadsToTake))
        continue;
?>
                <?php
                $total = 0;
                foreach ($scheme as $sc => $account) {
                    foreach ($account["Account"] as $acc) {
                        $total += ( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']);
                    }
                    if ($head == "Branch/Divisions" and $total > 0)
                        continue 2;
                }
                ?>
                <tr>
                <tr class="ui-widget-content">
                    <td ><a href="#" onclick="javascript:showDetails('details_<?php echo $head; ?>')"><div><strong style="font-size:14px"><?php echo $head; ?></strong></div></a></td>
                    <td></td>
                    <td>
<?php
                echo round(abs($total), 2);
                $gtliabilities +=$total;
?>
                    </td>
                </tr>
                <tr>

<?php
                foreach ($scheme as $sc => $account) {
                    $total = 0;
                    foreach ($account["Account"] as $acc) {
//                if(($acc['Debit']-$acc['Credit'])==0) continue;
                        $total += ( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']);
                    }
                    if ($total == 0)
                        continue;

                    if ($head == "Branch/Divisions") {
                        foreach ($scheme as $sc => $account)
                            foreach ($account["Account"] as $acc) {

                                $branchAndDivisions = (round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), 2) < 0 ? round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), 2) : 0);
                                if ($branchAndDivisions > 0) {
?>
                                <tr  class="ui-widget-content">
                                    <td width="72%"><?php echo $acc['AccountNumber']; ?></td>
                                    <td width="14%"><?php echo $branchAndDivisions; //echo ($a['DR']==0?"":$a['DR']);    ?></td>
                                    <td width="14%"></td>
                                </tr>
<?php
                                }
                            }
                    } else {
?>

                        <tr class="ui-widget-content">
                            <td width="72%"><?php echo $account['SchemeName']; ?></td>
                            <td width="14%"><?php echo round(abs($total), 2); //echo ($a['DR']==0?"":$a['DR']);     ?></td>
                            <td width="14%"></td>
                        </tr>
<?php
                    }
                }
?>

                </tr></tr>
                <?php
//
            }

            if ($pandlliabilities != 0) {
                ?>
                <tr class="ui-widget-content">
                    <td><div><strong style="font-size: 14px"><?php echo "Profit & Loss Account"; ?></strong></div></td>
                    <td></td>
                    <td><?php echo round($pandlBalance, 2); ?></td>
                </tr>
<?php
            }
?>

        </table>

        </td>
        <td  colspan="2" valign="top">
            <table width="100%"  border="0" cellpadding="3" class="ui-widget" >
<?php
            $HeadsToTake = array("Fixed Assets", "Assets", "Branch/Divisions"); //,"Income");
            foreach ($results as $head => $scheme) {
                $total = 0;
                if (!in_array($head, $HeadsToTake))
                    continue;
?>
        <?php
                $total = 0;
                foreach ($scheme as $sc => $account) {
                    foreach ($account["Account"] as $acc) {
                        $total += ( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']);
                    }
                    if ($head == "Branch/Divisions" and $total < 0)
                        continue 2;
                }
        ?>
                <tr class="ui-widget-content">
                    <td><a href="#" onclick="showDetails('details_<?php echo $head; ?>')"><div><strong style="font-size:14px"><?php echo $head; ?></strong></div></a></td>
                    <td></td>
                    <td>
<?php
                echo round(abs($total), 2);
                $gtassets +=$total;
?>
            </td>
        </tr>

        <tr  >

<?php
                foreach ($scheme as $sc => $account) {
                    $total = 0;
                    foreach ($account["Account"] as $acc) {
//                if(($acc['Debit']-$acc['Credit'])==0) continue;
                        $total += ( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']);
                    }
                    if ($total == 0)
                        continue;

                    if ($head == "Branch/Divisions") {
                        foreach ($scheme as $sc => $account)
                            foreach ($account["Account"] as $acc) {

                                $branchAndDivisions = (round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), 2) < 0 ? round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), 2) : 0);
                                if ($branchAndDivisions > 0) {
?>
                                <tr id="details_<?php echo $head; ?>" style="display:none" class="ui-widget-content">
                                    <td width="72%"><?php echo $acc['AccountNumber']; ?></td>
                                    <td width="14%"><?php echo $branchAndDivisions; //echo ($a['DR']==0?"":$a['DR']);    ?></td>
                                    <td width="14%"></td>
                                </tr>
<?php
                                }
                            }
                    } else {
?>

                        <tr id="details_<?php echo $head; ?>" style="display:none" class="ui-widget-content">
                            <td width="72%"><?php echo $account['SchemeName']; ?></td>
                            <td width="14%"><?php echo round(abs($total), 2); //echo ($a['DR']==0?"":$a['DR']);     ?></td>
                            <td width="14%"></td>
                        </tr>
<?php
                    }
                }
?>

            </tr>




<?php
            }
            if ($pandlasset != 0) {
?>
                <tr class="ui-widget-content">
                    <td><div><strong style="font-size: 14px"><?php echo "Profit & Loss Account"; ?></strong></div></td>
                    <td></td>
                    <td><?php echo round(abs($pandlBalance), 2); ?></td>
                </tr>
<?php
            }
?>
        </table></td>
        </tr>
    <?php
            $suspenceAmount = round(abs($gtliabilities)) + round($pandlBalance) - round(abs($gtassets));
    ?>
        <tr>

            <td><?php if (round($suspenceAmount) < 0) {
 ?><i>Suspence Amount Calculated at runtime*    </i><?php echo round(abs($suspenceAmount), 2);
            } ?></td>
                <td></td>
                <td><?php if (round($suspenceAmount) > 0) {
 ?><i>Suspence Amount Calculated at runtime*    </i><?php echo round(abs($suspenceAmount), 2);
            } ?></td>
        <td></td>
    </tr>

    <tr>
        <td>Total</td>
        <td align="center"><?php
            if ($suspenceAmount < 0)
                $liabilities = abs($gtliabilities) + $pandlliabilities + abs($suspenceAmount);
            else
                $liabilities=abs($gtliabilities) + $pandlliabilities;
            echo round(abs($liabilities), 2);
    ?></td>
        <td>Total</td>
        <td align="center"><?php if ($suspenceAmount > 0)
                $gtassets = abs($gtassets) + $pandlasset + abs($suspenceAmount); echo round(abs($gtassets) + abs($pandlasset), 2); ?></td>
    </tr>

    </table>

<?php
// echo "Suspence Amount - ".$suspenceAmount;
?>
