<table width="100%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <th width="42%" height="40" scope="col">&nbsp;</th>
    <th width="19%" rowspan="2" scope="col"><p>
      <img src="../images/logo.JPG" />
    </p>
    <p>Regd. No. 2507Y </p></th>
    <th width="39%" rowspan="2"  align="left" scope="col"><table width="100%" border="0" cellspacing="1" cellpadding="1">
        <tr>
          <th align="left" scope="col">Bhawani Credit Co-Operative Society Ltd.</th>
        </tr>
        <tr>
          <th align="left" scope="row">Regd. : Udaipur Road, Jhadol(Ph.),</th>
        </tr>
        <tr>
          <th align="left" scope="row">Distt. Udaipur (Raj.)</th>
        </tr>
    </table></th>
  </tr>
  <tr>
    <th width="42%" height="21" align="left"  scope="col">Issuing Branch : <?php echo $account->Branch->Name; ?></th>
  </tr>
  <tr>
    <th height="205" colspan="3" scope="row"><table width="100%"  border="0" cellpadding="1" cellspacing="1">
      <tr>
        <th width="26%"  scope="col">Account No. <?php echo $account->AccountNumber; ?></th>
        <th colspan="3" rowspan="1" scope="col">&nbsp;</th>
        
      </tr>
      <tr>
        <th scope="col">Date of Issue <?php echo $account->created_at; ?></th>
        <th width="74%" colspan="3" rowspan="7"  align="left" scope="col"><table width="100%" border="0" cellspacing="1" cellpadding="1">
          <tr>
            <th colspan="3" align="left" scope="col"><em>Received from Sh./Smt./Ku.</em> <?php echo $account->Member->Name; ?></th>
          </tr>
          <tr>
            <th colspan="3" align="left" scope="row"><em>Name of Father/Husband</em> <?php echo $account->Member->FatherName; ?></th>
          </tr>
          <tr>
            <th colspan="3" align="left" scope="row"><em>Address</em> <?php echo $account->Member->PermanentAddress; ?></th>
          </tr>
          <tr>
            <th colspan="3" align="left" scope="row"><em>Deposit Amount (In Words Rs ....Only)</em></th>
          </tr>
          <tr>
            <th width="56%" align="left" scope="row"><em>Name of Nominee</em> <?php echo $account->Member->Nominee; ?></th>
            <th width="29%" align="left" scope="row"><em> Relation </em> <?php echo $account->Member->RelationWithNominee; ?></th>
            <th width="15%" align="left" scope="row"><em>Age</em> <?php echo $account->Member->NomineeAge; ?></th>
          </tr>
          <tr>
            <th colspan="3" align="left" scope="row"><em>Date of Birth (if nominee minor)</em> <?php echo $account->Member->MinorDOB; ?></th>
          </tr>
          <tr>
            <th colspan="3" align="left" scope="row"><em>Special Condition if Any</em></th>
          </tr>
          <tr>
            <th height="21" colspan="3" scope="row">&nbsp;</th>
          </tr>
        </table></th>
      </tr>
      <tr>
        <th scope="col">As on Date <?php echo getNow("Y-m-d"); ?></th>
        </tr>
      <tr>
        <th  scope="col">Period <?php echo ($account->Schemes->MaturityPeriod)/12 . " Years"; ?></th>
        </tr>
      <tr>
        <th  scope="col">Due Date</th>
        </tr>
      <tr>
        <th  scope="col">Interest @ P.A. <?php echo $account->Schemes->Interest; ?></th>
        </tr>
      <tr>
        <th scope="col">Deposit Amount <?php echo $account->RdAmount; ?></th>
        </tr>
      <tr>
        <th  scope="col">Maturity Amount</th>
        </tr>
    </table></th>
  </tr>
</table>
