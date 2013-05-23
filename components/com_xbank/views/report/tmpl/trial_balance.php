<?php $gtDR=0; $gtCR=0; ?>
<table width="100%" border="0">
  <tr class="ui-widget-header" align="center">
    <td height="42" colspan="3" ><div align="center">BHAWANI CREDIT CO-OPERATIVE SOCIETY<br />
      UDAIPUR (RAJ.)</div>
      <div>
        <div align="center"></div>
      </div>
    </td>
  </tr>
  <tr class="ui-widget-header" align="center">
    <td colspan="3" ><strong style="font-size: 16px">Trial Balance</strong></td></tr>
    <tr class="ui-widget-header" align="center"><td colspan="3"  ><?php echo date("j M, Y",strtotime(inp("fromDate")))."   to   ".date("j M, Y",strtotime(inp("toDate"))); ?>
    </td>
  </tr>

 <!-- <tr>
    <td height="187" valign="top">
      <table width="100%" border="0" class="ui-widget" cellpadding="3">-->
        <tr class="ui-widget-header">
          <td><strong style="font-size: 16px">Particulars</strong></td>
          <td>Debit</td>
          <td>Credit</td>
        </tr>
        
        
      <?php

         $HeadsToTake=array("Capital Account","Liabilities","Expenses","Suspence Account");
          foreach($results as $scheme=>$account){
              $total=0;
              if(!in_array($scheme,$HeadsToTake)) continue;
           ?>
      <tr class="ui-widget-header">
        <td><div><strong style="font-size:14px"><?php echo $scheme; ?></strong></div></td>
        <td><strong><?php $DR=0; foreach($account as $s) $DR +=$s['DR']; echo $DR; $gtDR +=$DR; ?></strong></td>
        <td><strong><?php $CR=0; foreach($account as $s) $CR +=$s['CR']; echo $CR; $gtCR +=$CR; ?></strong></td>
      </tr>
      <?php
         foreach($account as $a){
        ?>
      <tr class="ui-widget-content">
        <td width="70%"><?php echo $a['SchemeName'];?></td>
        <td width="15%"><?php echo ($a['DR']==0?"":$a['DR']);?></td>
        <td width="15%"><?php echo ($a['CR']==0?"":$a['CR']);?></td>
      </tr>
      <?php

              }

    }
    ?>
    
    
    
    
      <?php

         $HeadsToTake=array("Fixed Assets","Assets");//,"Income");
          foreach($results as $scheme=>$account){
              $total=0;
              if(!in_array($scheme,$HeadsToTake)) continue;
           ?>
      <tr class="ui-widget-header">
        <td ><div><strong style="font-size: 14px"><?php echo $scheme ;?></strong></div></td>
         <td><strong><?php $DR=0; foreach($account as $s) $DR +=$s['DR']; echo $DR; $gtDR +=$DR; ?></strong></td>
        <td><strong><?php $CR=0; foreach($account as $s) $CR +=$s['CR']; echo $CR; $gtCR +=$CR; ?></strong></td>
      </tr>
      <?php
         foreach($account as $a){
        ?>
      <tr class="ui-widget-content">
        <td width="70%"><?php echo $a['SchemeName'];?></td>
        <td width="15%"><?php echo ($a['DR']==0?"":$a['DR']);?></td>
        <td width="15%"><?php echo ($a['CR']==0?"":$a['CR']);?></td>
      </tr>
      <?php

              }
       }
    ?>
           
        
        <?php
       $totalExpenses=0;
       $HeadsToTake=array("Direct Expenses","Indirect Expenses");
        foreach($results as $scheme=>$account){
            $total=0;
            if(!in_array($scheme,$HeadsToTake)) continue;
         ?>
        <tr class="ui-widget-header">
          <td ><div><strong style="font-size: 16px"><?php echo $scheme ?></strong></div></td>
          <td><strong><?php $DR=0; foreach($account as $s) $DR +=$s['DR']; echo $DR; $gtDR +=$DR; ?></strong></td>
          <td><strong><?php $CR=0; foreach($account as $s) $CR +=$s['CR']; echo $CR; $gtCR +=$CR; ?></strong></td>
        </tr>
        <?php
       foreach($account as $a){
      ?>
        <tr class="ui-widget-content">
          <td width="70%"><?php echo $a['Account'];?></td>
          <td width="15%"><?php echo ($a['DR']==0?"":$a['DR']);?></td>
          <td width="15%"><?php echo ($a['CR']==0?"":$a['CR']);?></td>
        </tr>
        <?php
          //  $total +=$a['Balance'];
            }
        ?>
       <!-- <tr class="ui-widget-content">
          <td width="70%"><b><?php // echo "TOTAL";?></b></td>
          <td width="12%"><b><?php // echo $total;?></b></td>
          <td width="18%"></td>
          </tr>-->
        <?php

     //  $totalExpenses +=$total;
  }
         $totalIncome=0;
       $HeadsToTake=array("Direct Income","Indirect Income");
        foreach($results as $scheme=>$account){
         if(!in_array($scheme,$HeadsToTake)) continue;
            $total=0;
         ?>
        <tr class="ui-widget-header">
          <td><div><strong style="font-size: 16px"><?php echo $scheme ?></strong></div></td>
          <td><strong><?php $DR=0; foreach($account as $s) $DR +=$s['DR']; echo $DR; $gtDR +=$DR; ?></strong></td>
          <td><strong><?php $CR=0; foreach($account as $s) $CR +=$s['CR']; echo $CR; $gtCR +=$CR; ?></strong></td>
        </tr>
        <?php
       foreach($account as $a){
      ?>
        <tr class="ui-widget-content">
           <td width="70%"><?php echo $a['Account'];?></td>
          <td width="15%"><?php echo ($a['DR']==0?"":$a['DR']);?></td>
          <td width="15%"><?php echo ($a['CR']==0?"":$a['CR']);?></td>
        </tr>
        <?php
          //  $total +=$a['Balance'];
            }
        ?>
      <!--  <tr class="ui-widget-content">
          <td width="70%"><b><?php // echo "TOTAL";?></b></td>
          <td width="12%"><b><?php //echo $total;?></b></td>
          <td width="18%"></td>
          </tr>-->
        <?php

     //   $totalIncome +=$total;
  }
  ?>
        
   <!-- </table></td>
  </tr>-->
  <tr style="font-weight:bold;">
  	<td>Grand Total</td>
    <td><?php echo $gtDR; ?></td>
    <td><?php echo $gtCR; ?></td>
  </tr>
  
</table>



