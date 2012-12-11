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
<!--<a href="index.php?/mod_pandl/pandl_cont/balancesheetcondensed">Condensed</a>-->
<table width="100%" border="1">
    <tr class="ui-widget-header" align="center"><td><strong style="font-size:16px">BHAWANI CREDIT CO-OPERATIVE SOCIETY</strong></td></tr>
    <tr class="ui-widget-header" align="center" ><td><strong style="font-size:14px">BALANCE SHEET( <?php echo Branch::getCurrentBranch()->Name; ?> )</strong></td></tr>
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
//                echo "<pre>";
//               print_r($results);
//                echo "</pre>";
                ?>
                <?php
                    $total = 0;
                    foreach ($scheme as $sc => $account) {
                        foreach ($account["Account"] as $acc) {
                            $total += round(( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']) , ROUND_TO);
                        }
                        if ($head == "Branch/Divisions" and $total > 0)
                            continue 2;
                    }
                ?>
                    <tr class="ui-widget-header">
                        <td><div><strong style="font-size:14px"><?php echo $head; ?></strong></div></td>
                        <td></td>
                        <td>
                        <?php
                        echo round(abs($total), ROUND_TO);
                        $gtliabilities +=$total;
                        ?>
                    </td>
                </tr>

<?php
                        foreach ($scheme as $sc => $account) {
                            $total = 0;
                            foreach ($account["Account"] as $acc) {
//                if(($acc['Debit']-$acc['Credit'])==0) continue;
                                $total += round(( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']) , ROUND_TO);
                            }
                            if ($total == 0)
                                continue;

                            if ($head == "Branch/Divisions") {
                                foreach ($scheme as $sc => $account)
                                    foreach ($account["Account"] as $acc) {

                                        $branchAndDivisions = (round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), ROUND_TO) < 0 ? round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), ROUND_TO) : 0);
                                        if ($branchAndDivisions > 0) {
?>
                                            <tr class="ui-widget-content">
                                                <td width="72%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.accountTransactions&id=<?php echo urlencode($acc['AccountId']);?>&format=raw" ><?php echo $acc['AccountNumber']; ?></a></td>
                                                <td width="14%"><?php echo $branchAndDivisions; //echo ($a['DR']==0?"":$a['DR']);  ?></td>
                                                <td width="14%"></td>
                                            </tr>
<?php
                                        }
                                    }
                            } else {
?>

                                <tr class="ui-widget-content">
                                    <td width="72%"><a href="index.php?option=com_xbank&task=report_cont.accountLevelDisplay&id=<?php echo urlencode($account['SchemeId']);?>" target="vbfbv"><?php echo $account['SchemeName']; ?></a></td>
                                    <td width="14%"><?php echo round(abs($total), ROUND_TO); //echo ($a['DR']==0?"":$a['DR']);  ?></td>
                                    <td width="14%"></td>
                                </tr>
<?php
                            }
                        }
                    }

                    if ($pandlliabilities != 0) {
?>
                        <tr class="ui-widget-header">
                            <td><div><strong style="font-size: 14px"><?php echo "Profit & Loss Account"; ?></strong></div></td>
                            <td></td>
                            <td><?php echo round($pandlBalance, ROUND_TO); ?></td>
                        </tr>
<?php
                    }
?>

                </table>

            </td>
            <td  colspan="2" valign="top"><table width="100%"  border="0" cellpadding="3" class="ui-widget" >
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
                            $total += round(( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']) , ROUND_TO);
                        }
                        if ($head == "Branch/Divisions" and $total < 0)
                            continue 2;
                    }
                ?>
                    <tr class="ui-widget-header">
                        <td><div><strong style="font-size:14px"><?php echo $head; ?></strong></div></td>
                        <td></td>
                        <td>
                        <?php
                        echo round(abs($total), ROUND_TO);
                        $gtassets +=$total;
                        ?>
                    </td>
                </tr>
            
                    <?php
                        foreach ($scheme as $sc => $account) {
                            $total = 0;

                            /*           if($account["SchemeType"]=="Loan")
                              {
                              foreach($scheme as $sc=>$account){
                              foreach($account["Account"] as $acc)
                              $total +=abs($acc['Debit']-$acc['Credit']);
                              }
                              }
                              else{
                             *
                             */
                            foreach ($account["Account"] as $acc) {
//                        if(($acc['Debit']-$acc['Credit'])==0) continue;
                                $total += round(( $acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']) , ROUND_TO);
                            }
                            //     }
                            if ($total == 0)
                                continue;

                            if ($head == "Branch/Divisions") {
                                foreach ($scheme as $sc => $account)
                                    foreach ($account["Account"] as $acc) {

                                        $branchAndDivisions = (round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), ROUND_TO) > 0 ? round(($acc['OpeningBalanceDr'] - $acc['OpeningBalanceCr']) + ($acc['Debit'] - $acc['Credit']), ROUND_TO) : "");
                                        if ($branchAndDivisions > 0) {
                    ?>
                                            <tr class="ui-widget-content">
                                                <td width="72%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.accountTransactions&id=<?php echo urlencode($acc['AccountId']);?>&format=raw" ><?php echo $acc['AccountNumber']; ?></a></td>
                                                <td width="14%"><?php echo $branchAndDivisions; //echo ($a['DR']==0?"":$a['DR']); ?></td>
                                                <td width="14%"></td>
                                            </tr>
                    <?php
                                        }
                                    }
                            } else {
                                ?>

                                <tr class="ui-widget-content">
                                    <td width="72%"><a href="index.php?option=com_xbank&task=report_cont.accountLevelDisplay&id=<?php echo urlencode($account['SchemeId']);?>" target="_blank" ><?php echo $account['SchemeName']; ?></a></td>
                                    <td width="14%"><?php echo round(abs($total), ROUND_TO); //echo ($a['DR']==0?"":$a['DR']); ?></td>
                                    <td width="14%"></td>
                                </tr>
                    <?php
                            }
                        }
                    ?>
                   
                <?php
                    }
                    if ($pandlasset != 0) {
                ?>
                        <tr class="ui-widget-header">
                            <td><div><strong style="font-size: 14px"><?php echo "Profit & Loss Account"; ?></strong></div></td>
                            <td></td>
                            <td><?php echo round(abs($pandlBalance), ROUND_TO); ?></td>
                        </tr>
                <?php
                    }
                ?>
                </table></td>
        </tr>
    <?php
                    $suspenceAmount = round(abs($gtliabilities),ROUND_TO) + round($pandlBalance,ROUND_TO) - round(abs($gtassets),ROUND_TO);
    ?>
                    <tr>

                        <td><?php if (round($suspenceAmount,ROUND_TO) < 0) { ?><i>Suspence Amount Calculated at runtime*    </i><?php echo round(abs($suspenceAmount), ROUND_TO);
                    } ?></td>
                <td></td>
                <td><?php if (round($suspenceAmount,ROUND_TO) > 0) { ?><i>Suspence Amount Calculated at runtime*    </i><?php echo round(abs($suspenceAmount),ROUND_TO);
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
                    echo round(abs($liabilities), ROUND_TO);
    ?></td>
                <td>Total</td>
                <td align="center">
                    <?php
                        if ($suspenceAmount > 0)
                            $gtassets = abs($gtassets) + abs($pandlasset) + abs($suspenceAmount);
                        else
                            $gtassets = abs($gtassets) + abs($pandlasset);
                        echo round(abs($gtassets) ,ROUND_TO); ?></td>
            </tr>

        </table>

<?php
// echo "Suspence Amount - ".$suspenceAmount;
?>
