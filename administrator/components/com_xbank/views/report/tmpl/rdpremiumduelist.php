<table>

    <tr class="ui-widget-header" align="left">
        <th>S no</th>
        <th>Account Number</th>
        <th>Account Opening Date</th>
        <th>Name</th>
        <th>Father Name</th>
        <th>Address</th>
        <th>Phone Nos</th>
        <th>Premiums Due</th>
        <th>Premium Amount</th>
        <th>Total</th>
        <th>Advisor Name</th>
        <th>Advisor Code</th>
        <th>Advisor Phone Nos</th>
    </tr>
    <?php
    $i=1;
    $premium_due_sum=0;
    $premium_amount_sum=0;
    $total_sum=0;

        foreach($result as $r){
       	$agName = "";
       	$agCode = "";
       	$agPhone = "";
            echo ($i % 2 ==0 ? "<tr bgcolor='white'>" : "<tr bgcolor='#D9E8E8'>");
            echo "<td>".$i++."</td>";
            echo "<td>".$r->AccountNumber."</td>";
            echo "<td>".$r->created_at."</td>";
            echo "<td>".$r->Name."</td>";
            echo "<td>".$r->FatherName."</td>";
            echo "<td>".$r->PermanentAddress."</td>";
            echo "<td>".$r->PhoneNos."</td>";
            echo "<td>".$r->premiumcount."</td>";
            echo "<td>".$r->Amount."</td>";
            echo "<td>".$r->premiumcount * $r->Amount."</td>";
            if($r->agents_id){
                $ag = new Agent($r->agents_id);
                $agName = $ag->member->Name;
                $agCode = $ag->id;
                $agPhone = $ag->member->PhoneNos;
            }
            echo "<td>".$agName."</td>";
            echo "<td>".$agCode."</td>";
            echo "<td>".$agPhone."</td>";
            echo "</tr>";

            $premium_due_sum += $r->premiumcount;
            $premium_amount_sum += $r->Amount;
            $total_sum += ($r->premiumcount * $r->Amount);
        }

    ?>
    <tr class="ui-widget-header" align="left">
        <th>Total</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th><?php echo $premium_due_sum; ?></th>
        <th><?php echo $premium_amount_sum; ?></th>
        <th><?php echo $total_sum; ?></th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
    </tr>
</table>