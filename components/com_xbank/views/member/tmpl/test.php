<html><head>
        <script type="text/javascript" language="javascript">
            function get_round(X) { return Math.round(X*100)/100 }
            function showpay() {

                if (document.calc.loan.value == null || document.calc.loan.value.length == 0 || document.calc.months.value == null || document.calc.months.value.length == 0 || document.calc.rate.value == null || document.calc.rate.value.length == 0)
                {

                    document.calc.pay.value = "Incomplete data";
                    document.calc.tot_amount.value = "Incomplete data";
                    document.calc.tot_interest.value = "Incomplete data";
                    document.calc.yearly_interest.value = "Incomplete data";
                    document.calc.interest_pa.value = "Incomplete data";
                    document.calc.interest_pm.value = "Incomplete data";
                }
                else
                {


                    var princ = document.calc.loan.value;
                    var term  = document.calc.months.value;
                    var intr   = document.calc.rate.value / 1200;
                    var yrs   = document.calc.months.value / 12;
                    document.calc.pay.value = get_round(princ * intr / (1 - (Math.pow(1/(1 + intr), term))));
                    document.calc.tot_amount.value = get_round(document.calc.pay.value * term);
                    document.calc.tot_interest.value = get_round(document.calc.tot_amount.value - princ);
                    document.calc.yearly_interest.value = get_round(document.calc.tot_interest.value / yrs);
                    document.calc.interest_pa.value = get_round(document.calc.yearly_interest.value / princ * 100);
                    document.calc.interest_pm.value = get_round((document.calc.yearly_interest.value / princ * 100)/12);
                }

            }
        </script>
    </head>
<body>
    <table  width="400" align="center" border="0">
        <form method="post" name="calc" id="calc">
          <tbody>
              <tr>
                <td colspan="2"><div align="center"><strong><font color="#000066">EMI CALCULATOR </font></strong></div></td>
              </tr>
            <tr>
                    <td>

                        <p align="right">Loan Amount<font color="#ff0000">*</font></p>                    </td>
                    <td align="right">
                      <div align="center">
                        <input id="loan" name="loan" size="10" type="text">
                      </div></td></tr>
              <tr>
                  <td>
                      <p align="right">Loan Tenure (Months)<font color="#ff0000" face="Verdana" size="2">*</font></p>                  </td>
                  <td align="right">
                    <div align="center">
                      <input name="months" size="10" type="text">
                      </div></td></tr>
              <tr>
                  <td>
                      <p align="right">Interest Rate (Reducing)<font color="#ff0000" face="Verdana" size="2">*</font></p>                  </td>
                <td align="right">
                  <div align="center">
                    <input name="rate" size="7" type="text">
                  %</div></td>
              </tr>
              <tr>
                  <td >
                      <p align="right"> Monthly Payment (EMI)</p>                  </td>
                  <td align="right"><p align="center">
                          <input name="button" onclick="showpay()" value="Calculate" type="button">
                          &nbsp;<input name="reset" value="Reset" type="reset">

                      </p></td>
              </tr>
              <tr>
                  <td colspan="2"><div align="center">

                          <p>&nbsp;</p>
                          <table style="border-collapse: collapse;" id="AutoNumber3" width="80%" border="0" bordercolor="#111111" cellpadding="0" cellspacing="0">
                              <tbody><tr>
                                      <td align="right"><em style="font-style: normal;">Calculated Monthly EMI</em> </td>

                                      <td align="right"><input name="pay" size="12" type="text"></td>
                                    </tr>
                                  <tr>
                                      <td align="right"> Total Amount with Interest</td>
                                      <td align="right"><input name="tot_amount" size="12" type="text"></td>
                                  </tr>
                                  <tr>
                                      <td align="right"><em style="font-style: normal;">Flat Interest Rate PA</em></td>

                                      <td width="109" align="right"><input name="interest_pa" size="12" type="text"></td>
                                  </tr>
                                  <tr>
                                      <td align="right"><em style="font-style: normal;">Flat Interest Rate PM</em></td>
                                      <td align="right"><input name="interest_pm" size="12" type="text"></td>
                                  </tr>
                                  <tr>
                                      <td align="right"><em style="font-style: normal;">Total Interest Amount</em>  </td>

                                      <td width="109" align="right"><input name="tot_interest" size="12" type="text">                                      </td>
                                  </tr>
                                  <tr>
                                      <td align="right"><em style="font-style: normal;">Yearly Interest Amount</em></td>
                                      <td align="right"><input name="yearly_interest" size="12" type="text"></td>
                                  </tr>
                              </tbody></table>


                      </div></td>
              </tr>
        </tbody></form>
    </table>


</body>
</html>