<?php
/*
  foreach($results as $head=>$scheme){
  echo $head."<br>";
  foreach($scheme as $sc=>$account){
  echo $account["SchemeName"]."<br>";
  foreach($account["Account"] as $acc){
  echo "<pre>";
  print_r($acc);
  echo "</pre>";
  }
  }
  }
 * 
 */
?>
<?php
$scriptProfit=""; $scriptLoss=""; $loss=0; $profit=0;
    if($pandlBalance < 0){
        $scriptLoss="<tr class='ui-widget-header'><td colspan='3'><div><strong style='font-size: 16px'>Net Loss</strong></div></td><td>".round(abs($pandlBalance),2)."</td></tr>";
        $loss=abs($pandlBalance);
        }
    if($pandlBalance > 0){
        $scriptProfit="<tr class='ui-widget-header'><td colspan='3'><div><strong style='font-size: 16px'>Net Profit</strong></div></td><td>".round(abs($pandlBalance),2)."</td></tr>";
        $profit=abs($pandlBalance);
        }
?>
<table width="100%" border="1">
    <tr class="ui-widget-header" align="center">
        <td height="42"  colspan="2" ><div align="center">BHAWANI CREDIT CO-OPERATIVE SOCIETY LTD.<br />
               </div>
            <div>
                <div align="center"></div>
            </div>
        </td>
    </tr>
    <tr class="ui-widget-header" align="center">
        <td colspan="2" ><strong style="font-size: 16px">Profit & Loss A/c</strong></td></tr>
    <tr class="ui-widget-header" align="center"><td colspan="2" ><?php echo date("j M, Y", strtotime(inp("fromDate"))) . "   to   " . date("j M, Y", strtotime(inp("toDate"))); ?>
        </td>
    </tr>

    <tr>
        <td width="50%" valign="top">
            <table width="100%" border="0" class="ui-widget" cellpadding="3">
                <tr class="ui-widget-header">
                    <td colspan="4"><div align="center"><strong style="font-size: 16px">Particularss</strong></div></td>
                </tr>
                
                <?php
                $totalExpenses = 0;
                $HeadsToTake = array("Direct Expenses", "Indirect Expenses");
                foreach ($results as $head => $scheme) {
//                  echo $results."<br>";
//                  echo $head."<br>";
//                  echo $scheme."<br>";
//                  echo $head."=>".$scheme;
                  
//            if(!in_array($head,$HeadsToTake)) continue;
                    foreach ($scheme as $sc => $account) {
                        $total = 0;

                        if (!in_array($account['SchemeName'], $HeadsToTake))
                            continue;
                ?>
                        <tr class="ui-widget-header">
                            <td colspan="3"><div><strong style="font-size: 16px"><?php echo $account['SchemeName'] ?></strong></div></td>
                            <td><b><?php foreach ($account["Account"] as $a) $total += /*( $a['OpeningBalanceDr'] - $a['OpeningBalanceCr']) + */ ($a['Debit'] - $a['Credit']); echo round($total,2); ?></b></td>
                        </tr>
                <?php
                         $total = 0;
                        foreach ($account["Account"] as $a) {
//                if($a['Debit']-$a['Credit']==0) continue;
                ?>
                            <tr class="ui-widget-content">
                                <td width="70%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.accountTransactions&format=raw&id=<?php echo $a['AccountId'];?>" ><?php echo $a['AccountNumber']; ?></a></td>
                                <td width="14%"><?php echo  round(/* ($a['OpeningBalanceDr'] - $a['OpeningBalanceCr']) + */ ($a['Debit'] - $a['Credit'])); ?></td>
                                <td width="14%"></td>
                            </tr>
<?php
                            $total += /* ( $a['OpeningBalanceDr'] - $a['OpeningBalanceCr'])  + */ ($a['Debit'] - $a['Credit']);
                        }
                    
?>
<!--                <tr class="ui-widget-content">
                    <td width="70%"><b><?php echo "TOTAL"; ?></b></td>
                    <td width="12%"><b><?php echo round($total,2); ?></b></td>
                    <td width="18%"></td>
                </tr>-->
<?php
                $totalExpenses +=$total;
                }
                }

                echo $scriptProfit;
?>

            </table></td>
        <td width="50%" valign="top">
            <table width="100%" border="0" cellpadding="1" class="ui-widget">
                <tr class="ui-widget-header">
                    <td colspan="4"><div align="center"><strong style="font-size: 16px">Particulars</strong></div></td>
                </tr>
<?php
                $totalIncome = 0;
                $HeadsToTake = array("Direct Income", "Indirect Income");
                foreach ($results as $head => $scheme) {
//         if(!in_array($head,$HeadsToTake)) continue;

                    foreach ($scheme as $sc => $account) {
                        $total = 0;

                        if (!in_array($account['SchemeName'], $HeadsToTake))
                            continue;
?>
                        <tr class="ui-widget-header">
                            <td colspan="3"><div><strong style="font-size: 16px"><?php echo $account['SchemeName'] ?></strong></div></td>
                            <td><b><?php foreach ($account["Account"] as $a) $total +=  /* ( $a['OpeningBalanceCr'] - $a['OpeningBalanceDr']) + */ ($a['Credit'] - $a['Debit']); echo round($total,2); ?></b></td>
                        </tr>
<?php
                         
                        foreach ($account["Account"] as $a) {
//            if($a['Balance']==0) continue;
?>
                            <tr class="ui-widget-content">
                                <td width="70%"><a class="alertinwindow" href="index.php?option=com_xbank&task=report_cont.accountTransactions&format=raw&id=<?php echo $a['AccountId'];?>" ><?php echo $a['AccountNumber']; ?></a></td>
                                <td width="14%"><?php echo round(/* ($a['OpeningBalanceCr'] - $a['OpeningBalanceDr']) +*/ ($a['Credit'] - $a['Debit']),2); ?></td>
                                <td width="14%"></td>
                            </tr>
<?php
//                            $total += /* ( $a['OpeningBalanceCr'] - $a['OpeningBalanceDr']) + */($a['Credit'] - $a['Debit']);
                        }
                    
?>
<!--                <tr class="ui-widget-content">
                    <td width="70%"><b><?php echo "TOTAL"; ?></b></td>
                    <td width="12%"><b><?php echo round($total,2); ?></b></td>
                    <td width="18%"></td>
                </tr>-->
<?php
                $totalIncome +=$total;
                }
                }

                echo $scriptLoss;
?>


            </table></td>
    </tr>
    <tr>
        <td>
            <table width="100%"><tr class="ui-widget-header"><td>Total</td><td align="right"><?php echo round(($totalExpenses + $profit),2); ?></td></tr></table>
        </td>
        
        <td>
            <table width="100%"><tr class="ui-widget-header"><td>Total</td><td align="right"><?php echo round($totalIncome + $loss , 2); ?></td></tr></table>
        </td>
        
    </tr>


<!--    <tr class="ui-widget-content">
        <td colspan="2" valign="top">
                <?php
//                $balanceLeft = $totalIncome - $totalExpenses;
//                if ($balanceLeft > 0)
//                    echo "NET PROFIT : " . round($balanceLeft);
//                else
//                    echo "NET LOSS : " . round($balanceLeft);
                ?>
        </td>
    </tr>-->
</table>

