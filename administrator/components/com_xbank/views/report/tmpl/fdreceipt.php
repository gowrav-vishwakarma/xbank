<?php
    if(JRequest::getVar("format") != "raw"){
?>
<a href="index.php?option=com_xbank&task=printing_cont.fdPrint&format=raw" target="bdvvgd">Print FD Receipt</a>
<?php }

$width=1024;
$height=768;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>



<style type="text/css">
<!--
td {
	font-size: 14px;
}

#fd_main{
    background: url('components/com_xbank/images/fd_receipt_new.jpg'); 
    background-size: 100%;
    width:<?php echo $width?>px; 
    height: <?php echo $height?>px;
    position: relative;
}

#branch{
    position: absolute;
    top: <?php echo $height*71/100?>px;
    left:<?php echo $width*43/100?>px;
    font-size:<?php echo $height*3/100?>px; 
}

#deposite_amount_word{
    position: absolute;
    top: <?php echo $height*51/100?>px;
    left:<?php echo $width*45/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#receipt_no{
    position: absolute;
    top: <?php echo $height*24/100?>px;
    left:<?php echo $width*79/100?>px;
    font-size:<?php echo $height*2/100?>px; 
}
#received_from{
    position: absolute;
    top: <?php echo $height*31/100?>px;
    left:<?php echo $width*46/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#gardian_name{
    position: absolute;
    top: <?php echo $height*35/100?>px;
    left:<?php echo $width*43/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#address{
    position: absolute;
    top: <?php echo $height*39/100?>px;
    left:<?php echo $width*27/100?>px;
    font-size:<?php echo $height*3/100?>px;
    width:<?php echo $height*80/100?>px;
    height:<?php echo $height*14/100?>px;;
}

#nominee_name{
    position: absolute;
    top: <?php echo $height*55/100?>px;
    left:<?php echo $width*36/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#relation{
    position: absolute;
    top: <?php echo $height*55/100?>px;
    left:<?php echo $width*69/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#age{
    position: absolute;
    top: <?php echo $height*55/100?>px;
    left:<?php echo $width*83/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#dob{
    position: absolute;
    top: <?php echo $height*60/100?>px;
    left:<?php echo $width*50/100?>px;
    font-size:<?php echo $height*3/100?>px;
    letter-spacing:20px;
}

#special_condition{
    position: absolute;
    top: <?php echo $height*63/100?>px;
    left:<?php echo $width*41/100?>px;
    font-size:<?php echo $height*3/100?>px;
}

#account_no{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*8/100?>px;
}

#date_issue{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*19/100?>px;
}

#as_on_date{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*31/100?>px;
}

#period{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*44/100?>px;
}

#due_date{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*55/100?>px;
}

#interest_pa{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*68/100?>px;
}

#deposit_amount{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*80/100?>px;
}

#maturity_amount{
    position: absolute;
    top: <?php echo $height*83/100?>px;
    left:<?php echo $width*88/100?>px;
}

.mStyle
{  
    font-size:<?php echo $height*2/100?>px;
}

</style>
</head>

<body>
  <div id="fd_main">
    <div id='branch' ><?php echo $fd->branch->Name;?></div>
    <div id='deposite_amount_word'><?php convert_digit_to_words($fd->RdAmount);?></div>
    <div id='receipt_no'><?php echo "#".$fd->AccountNumber;?></div>
    <div id='received_from'><?php echo $fd->member->Name;?></div>
    <div id='gardian_name'><?php echo $fd->member->FatherName;?></div>
   <!-- <div id='address'><?php echo 'hello my name is rakesh hyhgtfrhygtrfrsdgsdshdhshhhcsch v dvds vdhgvhdghgdgvsdvh sdhgvsdgdggdgsdfhd hfdhfdfhdfhgdgfdjsgfgfdgfgdfgdgfdgfgdfgdgfdfgdfgdfddf'?></div>-->
    <<div id='address'><?php echo $fd->member->CurrentAddress;?></div>
    <div id='nominee_name'><?php echo $fd->member->Nominee;?></div>
    <div id='relation'><?php echo $fd->member->RelationWithNominee;?></div>
    <div id='age'><?php echo $fd->member->Age;?></div>
    <div id='dob'><?php echo date('dmY',strtotime($fd->member->MinorDOB));?></div>
    <div id='special_condition' class="mStyle"><?php echo "";?></div>
    <div id='account_no' class="mStyle"><?php echo $fd->AccountNumber;?></div>
    <div id='date_issue' class="mStyle"><?php echo date('d-M-Y',strtotime($fd->created_at));?></div>
    <div id='as_on_date' class="mStyle"><?php echo getNow('d-M-Y');?></div>
    <div id='period' class="mStyle"><?php echo ($months=$fd->scheme->MaturityPeriod) ." Months" ;?></div>
    <div id='due_date' class="mStyle"><?php echo date("d-M-Y", strtotime(date("Y-m-d", strtotime($fd->created_at)) . " +$months MONTH"));?></div>
    <div id='interest_pa' class="mStyle"><?php echo $fd->scheme->Interest . " %";?></div>
    <div id='deposit_amount' class="mStyle"><?php echo $fd->RdAmount;?></div>
    <div id='maturity_amount' class="mStyle"><?php echo "maturity_amount";?></div>
  </div>



</body>
</html>