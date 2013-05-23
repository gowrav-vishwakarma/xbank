<table>

    <tr class="ui-widget-header">
        <th>S no</th>
        <th>Account Number</th>
        <th>Name</th>
        <th>Father Name</th>
        <th>Address</th>
        <th>Phone Nos</th>
        <th>EMI Due</th>
        <th>Emi Amount</th>
        <th>Loan Amount</th>
        <th>Gaurantor Name</th>
        <th>Gaurantor Address</th>
        <th>Gaurantor Phone Nos</th>
        <th>Dealer Name</th>
    </tr>
    <?php
    $i=0;
        foreach($result as $r){
            echo "<tr>";
            echo "<td>".$i++."</td>";
            echo "<td>".$r->AccountNumber."</td>";
            echo "<td>".$r->Name."</td>";
            echo "<td>".$r->FatherName."</td>";
            echo "<td>".$r->PermanentAddress."</td>";
            echo "<td>".$r->PhoneNos."</td>";
            echo "<td>".$r->premiumcount."</td>";
            echo "<td>".$r->Amount."</td>";
            echo "<td>".$r->RdAmount."</td>";
            echo "<td>".$r->Nominee."</td>";
            echo "<td>".$r->MinorNomineeParentName."</td>";
            echo "<td>".$r->RelationWithNominee."</td>";
            echo "<td>".$r->DealerName."</td>";
            echo "</tr>";
        }

    ?>

</table>