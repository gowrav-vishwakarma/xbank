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
    <tr class="ui-widget-header" align="center"><td width="8%" rowspan="3"><img src="components/com_xbank/images/Logosmall.png" width="86" height="86" /></td>
      <td width="92%"><strong style="font-size:16px">BHAWANI CREDIT CO-OPERATIVE SOCIETY</strong></td>
    </tr>
    <tr class="ui-widget-header" align="center" ><td><strong style="font-size:14px">BALANCE SHEET( <?php echo Branch::getCurrentBranch()->Name; ?> )</strong></td>
    </tr>
    <tr class="ui-widget-header" align="center"><td><?php echo date("j M, Y", strtotime(inp("fromDate"))) . "   to   " . date("j M, Y", strtotime(inp("toDate"))); ?></td>
    </tr>
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
                $Bhead = new BalanceSheet();
                $Bhead->get();
//                foreach ($Bhead as $head) {
                foreach ($HeadsToTake as $h) {
                     $head = new BalanceSheet();
                     $head->where("Head",$h)->get();
                    if (!in_array($head->Head, $HeadsToTake))
                        continue;

                    if($head->Head == 'Branch/Divisions' and $head->getHeadTotal($head->id, inp("fromDate"), inp("toDate"),$branch) > 0)
                        continue;
                ?>
                <?php
//                echo "<pre>";
//               print_r($results);
//                echo "</pre>";
                ?>
               
                    <tr class="">
                        <td><div><strong style="font-size:14px"><a target="ghgh" href='index.php?option=com_xbank&task=report_cont.schemesLevelDisplay&Head=<?php echo $head->id ?>'><?php echo $head->Head; ?></a></strong></div></td>
                        <td></td>
                        <td></td>
                        <td>
                        <?php
                        $liab = abs($head->getHeadTotal($head->id, inp("fromDate"), inp("toDate"),$branch));
                        $gtliabilities += $liab;
                        echo $liab;
                        ?>
                    </td>
                </tr>

<?php
                 //--------------------------------------------------------------------------------------------
                 $SchemeTypeArray = array('recurring','FixedAndMis','SavingAndCurrent','Default');
                 foreach($SchemeTypeArray as $st){
                     if($st != 'Default'){
                     $sc = new Scheme();
                     $sc->where('balance_sheet_id',$head->id);
                     $sc->where("SchemeType",$st)->get();
                     $schemeTotal = 0;
                     if($sc->result_count()){
                     foreach($sc as $s){

                     $schemeTotal += abs($s->getSchemeTotal($s->id, inp("fromDate"), inp("toDate"),Branch::getCurrentBranch()->id));
                     if($schemeTotal == 0)
                         continue;
                     }
    ?>
                     <tr class="">
                            <td><div><strong style="font-size:14px"><?php echo $st; ?></strong></div></td>
                            <td></td>
                            <td>
                            <?php
    //                        $gtliabilities += $liab;
                            echo $schemeTotal;
                            ?>
                        </td>
                    </tr>
                    <?php
                     }
                     }
                     else{
                         if($st == 'Default'){
                            $sc = new Scheme();
                            $sc->where('balance_sheet_id',$head->id);
                            $sc->where("SchemeType",'Default')->get();
                             foreach($sc as $s){
                             $schemeTotal = 0;
                             $schemeTotal = abs($s->getSchemeTotal($s->id, inp("fromDate"), inp("toDate"),Branch::getCurrentBranch()->id));
                             if($schemeTotal == 0)
                                 continue;
            ?>
                             <tr class="">
                                    <td><div><strong style="font-size:14px"><?php echo $s->Name; ?></strong></div></td>
                                    <td></td>
                                    <td>
                                    <?php

            //                        $gtliabilities += $liab;
                                    echo $schemeTotal;
                                    ?>
                                </td>
                            </tr>
                            <?php
                             }

                     }
                     }
                }
                 //---------------------------------------------------------------------------------------------
                  
                    }
                

                    if ($pandlliabilities != 0) {
?>
                        <tr class="ui-widget-header">
                            <td><div><strong style="font-size: 14px"><?php echo "Profit & Loss Account"; ?></strong></div></td>
                            <td></td>
                            <td><?php echo round(abs($pandlBalance), ROUND_TO); ?></td>
                        </tr>
<?php
                    }
?>

          </table>

      </td>
            <td  colspan="2" valign="top">
                <table width="100%" border="0" cellpadding="3" class="ui-widget">
                <?php
                $HeadsToTake = array("Fixed Assets", "Assets", "Branch/Divisions");
                $Bhead = new BalanceSheet();
                $Bhead->get();
//                foreach ($Bhead as $head) {
                foreach ($HeadsToTake as $h) {
                     $head = new BalanceSheet();
                     $head->where("Head",$h)->get();
                    if (!in_array($head->Head, $HeadsToTake))
                        continue;

                     if($head->Head == 'Branch/Divisions' and $head->getHeadTotal($head->id, inp("fromDate"), inp("toDate"),$branch) < 0)
                        continue;
                ?>
                <?php
//                echo "<pre>";
//               print_r($results);
//                echo "</pre>";
                ?>

                    <tr class="">
                        <td><div><strong style="font-size:14px"><a target="ghgh" href='index.php?option=com_xbank&task=report_cont.schemesLevelDisplay&Head=<?php echo $head->id ?>'><?php echo $head->Head; ?></a></strong></div></td>
                        <td></td>
                        <td></td>
                        <td>
                        <?php
                        $assets = abs($head->getHeadTotal($head->id, inp("fromDate"), inp("toDate"),$branch));
                        $gtassets += $assets;
                        echo $assets;
                        ?>
                    </td>
                </tr>

<?php

                 //--------------------------------------------------------------------------------------------
                 $SchemeTypeArray = array('Loan','CC','Default');
                 foreach($SchemeTypeArray as $st){
                     if($st != 'Default'){
                     $sc = new Scheme();
                     $sc->where('balance_sheet_id',$head->id);
                     $sc->where("SchemeType",$st)->get();
                     if($sc->result_count()){
                     $schemeTotal = 0;
                     foreach($sc as $s){

                     $schemeTotal += abs($s->getSchemeTotal($s->id, inp("fromDate"), inp("toDate"),Branch::getCurrentBranch()->id));
                     if($schemeTotal == 0)
                         continue;
                     }
    ?>
                     <tr class="">
                            <td><div><strong style="font-size:14px"><?php echo $st; ?></strong></div></td>
                            <td></td>
                            <td>
                            <?php
    //                        $gtliabilities += $liab;
                            echo $schemeTotal;
                            ?>
                        </td>
                    </tr>
                    <?php
                     }
                     }
                     else{
                         if($st == 'Default'){
                            $sc = new Scheme();
                            $sc->where('balance_sheet_id',$head->id);
                            $sc->where("SchemeType",'Default')->get();
                             foreach($sc as $s){
                             $schemeTotal = 0;
                             $schemeTotal = abs($s->getSchemeTotal($s->id, inp("fromDate"), inp("toDate"),Branch::getCurrentBranch()->id));
                             if($schemeTotal == 0)
                                 continue;
            ?>
                             <tr class="">
                                    <td><div><strong style="font-size:14px"><?php echo $s->Name; ?></strong></div></td>
                                    <td></td>
                                    <td>
                                    <?php

            //                        $gtliabilities += $liab;
                                    echo $schemeTotal;
                                    ?>
                                </td>
                            </tr>
                            <?php
                             }

                     }
                     }
                }

                 //---------------------------------------------------------------------------------------------
                  


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

                </table>

            </td>
  </tr>
    <?php
                    $suspenceAmount = round(abs($gtliabilities),ROUND_TO) + round(($pandlBalance),ROUND_TO) - round(abs($gtassets),ROUND_TO);
    ?>
                    <tr>

                        <td><?php if (round($suspenceAmount,ROUND_TO) < 0) { ?><i>Suspence Amount Calculated at runtime*    </i><?php echo round(abs($suspenceAmount), ROUND_TO);
                    } ?></td>
                <td></td>
                <td><?php  if (round($suspenceAmount,ROUND_TO) > 0) { ?><i>Suspence Amount Calculated at runtime*    </i><?php echo round(abs($suspenceAmount),ROUND_TO);
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
