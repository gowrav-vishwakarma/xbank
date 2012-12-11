<table width="100%" border="1" class="ui-widget">
    <?php

//      $displayFields = array();
//      foreach($fieldsToShow as $fieldname=>$val){
////          if(array_values($val) == 0) continue;
//          foreach($val as $key=>$vals){
//          if($vals == 0 or $vals == false) continue;
//          $displayFields += array($key=>$fieldname);
//          }
//      }
      ?>
  <tr class="ui-widget-header">
    <td>S.No</td>
   <?php
     
//      foreach($displayFields as $headval=>$dbval ){
   foreach($fieldsToShow as $fieldname=>$val){
       if($val == false) continue;
   ?>
      <td><?php echo $val; ?></td>
   <?php
      }
    ?>
<!--    <td>id</td>
    <td>Account</td>
    <td>EMI</td>
    <td>Paid</td>
    <td>Due Date</td>
    <td>Paid On</td>-->

  </tr>


  <?php
  if($result){
  foreach($result as $r)
  {
    ?>
        <tr>
            <td align="center"><?= ++$i;?></td>
                <?php
//                    foreach($displayFields as $headval=>$dbval ){
                   foreach($fieldsToShow as $fieldname=>$val){
                    if($val == false) continue;
                ?>
                    <td><?php  echo $r->{$val}    ; ?></td>
                <?php
                    }
                ?>
        </tr>
    <?php

  } // end of foreach
  } // end of if($result)
  else{
         echo "No such account found.";
      }
  ?>
</table>

<?php $x = ($start-$count) < 0 ? 0 : ($start-$count);
      $y = ($start+$count) > $i ? $start : ($start+$count);
?>
<!--<p class="ui-widget ui-widget-header"><a style="float:left;" href="<?= site_url()."?option=com_xbank&task=report_cont.searchLoans&start=$x"?>">Previous</a>
  <a style="float:right;" href="<?= site_url()."?option=com_xbank&task=report_cont.searchLoans&start=$y"?>">Next</a><p>-->

