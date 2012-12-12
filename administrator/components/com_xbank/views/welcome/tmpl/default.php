<?php
/* ------------------------------------------------------------------------
  # com_xcideveloper - Seamless merging of CI Development Style with Joomla CMS
  # ------------------------------------------------------------------------
  # author    Xavoc International / Gowrav Vishwakarma
  # copyright Copyright (C) 2011 xavoc.com. All Rights Reserved.
  # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.xavoc.com
  # Technical Support:  Forum - http://xavoc.com/index.php?option=com_discussions&view=index&Itemid=157
  ------------------------------------------------------------------------- */
// no direct access
defined('_JEXEC') or die('Restricted access');
?><pre>

    <?php
    jimport('joomla.html.pane');
    $tabs = JPane::getInstance('tabs');
    $closing = new Closing();
    $closing->where("branch_id", Branch::getCurrentBranch()->id)->get();
    ?>
    <label><b>Date : <?php echo getNow("Y-m-d"); ?></b></label><br/>
    <label><b>Last Closing Done on <?php echo $closing->daily; ?></b></label>
    <?php
    echo $tabs->startPane("tabone");
    echo $tabs->startPanel(JText::_('Dashboard'), "newtickets");


    echo $graph;
    if (isset($result))
        echo $result;

//     $this->jq->useGraph();
//$data_url = "index.php?option=com_xbank&task=com_xbank.schemewiseAccountDetailsGraph";
//    $this->jq->getGraphObject('100%', '200', $data_url, 'test_chart');




    echo $tabs->endPanel();

    echo $tabs->startPanel(JText::_('Daily Dues'), "newtickets");

//    echo "<h2>New Members here...</h2>";
//    echo @$this->lastThirty->image;
    echo "<label>DUES TO RECEIVE</label>";
    echo $duesToReceive;
    ?>

 
<!--<table width="100%" border="1" class="ui-widget">

        <?php

//        if ($duesToReceive) {
        ?>
         <tr class="ui-widget-header">
            <td>S.No.</td>
            <td>Account Number</td>
            <td>Name</td>
            <td>Father Name</td>
            <td>Address</td>
            <td>Phone Number</td>
            <td>Amount Due</td>
            <td>Due Date</td>
          </tr>-->
        <?php
//            $i = 1;
//            foreach ($duesToReceive as $r) {
        ?>
<!--                        <tr>
                        <td><?php echo $i; ?></td>
                    <td><?php echo $r->Accnum; ?></td>
                    <td><?php echo $r->Name; ?></td>
    		<td><?php echo $r->FatherName; ?></td>
            	<td><?php echo $r->CurrentAddress; ?></td>
    		<td><?php echo $r->PhoneNos; ?></td>

                <td><?php echo $r->amount; ?></td>
                <td><?php echo $r->DueDate; ?></td>


                </tr>-->
        <?php
//                $i++;
//            } // end
//        } else {
//            echo "<i>No Dues Found.</i>";
//        }
        ?>
<!--</table>-->




<br/><br/>
<label>DUES TO GIVE</label>
    <?php
    	echo $duesToGive;
        echo $tabs->endPanel();

        echo $tabs->startPanel(JText::_('Weekly Dues'), "newtickets");
        echo "<label>DUES TO RECEIVE</label>";
        echo $weeklyduesToReceive;
//    echo "<h2>New Members here...</h2>";
//    echo @$this->lastThirty->image;
    ?>
<!--            <label>DUES TO RECEIVE</label>
        <table width="100%" border="1" class="ui-widget">-->

        <?php
//        if ($weeklyduesToReceive) {
        ?>
<!--             <tr class="ui-widget-header">
                <td>S.No.</td>
                <td>Account Number</td>
                <td>Amount Due</td>
                <td>Due Date</td>
              </tr>-->
        <?php
//            $i = 1;
//            foreach ($weeklyduesToReceive as $r) {
        ?>
<!--                        <tr>
                        <td><?php echo $i; ?></td>
                    <td><?php echo $r->Accnum; ?></td>
                    <td><?php echo $r->amount; ?></td>
                    <td><?php echo $r->DueDate; ?></td>


                    </tr>-->
        <?php
//                $i++;
//            } // end
//        } else {
//            echo "<i>No Dues Found.</i>";
//        }
        ?>
<!--</table>-->

<br/><br/>
<label>DUES TO GIVE</label>
<?php echo $weeklyduesToGive;
        echo $tabs->endPanel();


        echo $tabs->startPanel(JText::_('Monthly Dues'), "newtickets");
        echo "<label>DUES TO RECEIVE</label>";
        echo $monthlyduesToReceive;
//    echo "<h2>New Members here...</h2>";
//    echo @$this->lastThirty->image;
    ?>
<!--            <label>DUES TO RECEIVE</label>
        <table width="100%" border="1" class="ui-widget">-->

        <?php
//        if ($monthlyduesToReceive) {
        ?>
<!--             <tr class="ui-widget-header">
                <td>S.No.</td>
                <td>Account Number</td>
                <td>Amount Due</td>
                <td>Due Date</td>
              </tr>-->
        <?php
//            $i = 1;
//            foreach ($monthlyduesToReceive as $r) {
        ?>
<!--                    <tr>
                    <td><?php echo $i; ?></td>
                <td><?php echo $r->Accnum; ?></td>
                <td><?php echo $r->amount; ?></td>
                    <td><?php echo $r->DueDate; ?></td>-->


                    </tr>
        <?php
//                $i++;
//            } // end
//        } else {
//            echo "<i>No Dues Found.</i>";
//        }
        ?>
<!--</table>-->

<br/><br/>
<label>DUES TO GIVE</label>




    <?php
    	echo $monthlyduesToGive;
        echo $tabs->endPanel();


        echo $tabs->startPanel(JText::_('Accounts Opened Today'), "newtickets");
    	echo $AccountsOpenedToday;
        echo $tabs->endPanel();

        echo $tabs->startPanel(JText::_('Cash/Bank Report'), "newtickets");
        echo $report_cash;
        echo $report_bank;
        echo $tabs->endPanel();


        echo $tabs->startPanel(JText::_('Insurance Due List'), "newtickets");
    ?>
    <table width="100%">
     <tr class="ui-widget-header">
        <td>S.No.</td>
        <td>Account Number</td>
        <th>Member Name</th>
        <th>Father Name</th>
        <th>Address</th>
        <th>Mobile Number</th>
        <td>Dealer Name</td>
        <td>Loan insurance Date</td>
        <td>Loan insurance End Date</td>

      </tr>
        <?php
        $i = 1;
        foreach ($insuranceDueList as $r) {
        ?>
                   <tr  bgcolor="<?php if($i%2 != 0)
                            echo '#D9E8E8';
                        else
                            echo 'white';?>">
        <td><?php echo $i++; ?></td>
        <td><?php echo $r->AccountNumber; ?></td>
        <td><?php echo $r->Name ?></td>
        <td><?php echo $r->FatherName ?></td>
        <td><?php echo $r->PermanentAddress ?></td>
        <td><?php echo $r->PhoneNos ?></td>
        <td><?php echo $r->DealerName; ?></td>
        <td><?php echo date("Y-m-d", strtotime(date("Y-m-d", strtotime($r->LoanInsurranceDate)))); ?></td>
        <td><?php echo date("Y-m-d", strtotime(date("Y-m-d", strtotime($r->LoanInsurranceDate)) . " +365 DAYS")); ?></td>
    </tr>


        <?php
           
        }
        ?>
        </table>
    <?php
        echo $tabs->endPanel();




        echo $tabs->endPane();
    ?>



    <?php
        /*
         * To change this template, choose Tools | Templates
         * and open the template in the editor.
         */
//echo Current_Staff::staff()->Name."<br>";
//echo getNow();
//print_r($result);
    ?>
</pre>