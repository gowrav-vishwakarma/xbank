<?php
$form = $this->form->open("FixedAndMISScheme", 'index.php?option=com_xbank&task=schemes_cont.newscheme')
                ->setColumns(2)
                ->text("Scheme Name", "name='Name' class='input req-string tooltip' title='Type the name for new Scheme'")
                ->select("Account Type", "name='SchemeType' class='not-req tooltip' title='Select the type of Scheme' not-req-val='Select Account Type'", $accountTypeArray)
                ->text("Minimum Balance/Amount", "name='MinLimit' class='input req-numeric tooltip' title='Give minimum balance/amount for the scheme. Type 0 in case of no minimum'")
                ->text("Maximum Limit", "name='MaxLimit' class='input req-numeric tooltip' title='Give the maximum limit for the scheme. In case of no limit type -1'")
                ->text("Interest (In %)", "name='Interest' class='input req-numeric tooltip' title='Type interest rate in percentage'")
//                ->select("Premium", "name='PremiumMode' class='not-req tooltip' title='Select the mode of paying Premium' not-req-val='-1'", array('Select Premium Mode' => '-1', 'Not Applicable' => '0', 'Yearly' => 'Y', 'Half Yearly' => 'HF', 'Quarterly' => 'Q', 'Monthly' => 'M', 'Weekly' => 'W', 'Daily' => 'D'))
//                ->select("Interest Mode", "name='InterestMode' class='not-req tooltip' title='Select the mode of interest posting' not-req-val='-1'", array('Select Intrest Mode' => '-1', 'Yearly' => 'Y', 'Half Yearly' => 'HF', 'Quarterly' => 'Q', 'Monthly' => 'M', 'Weekly' => 'W', 'Daily' => 'D'))
// 		->select("Posting Mode","name='PostingMode' class='not-req' not-req-val='-1'",array('Select Intrest Mode'=>'-1','Yearly'=>'Y','Half Yearly' => 'HF','Quarterly'=>'Q','Monthly'=>'M','Weekly'=>'W','Daily'=>'D'))
                ->_()
                ->text("Account Commissions(in %)", "name='AccountOpenningCommission' class='input req-string tooltip' title='Give account commissions in %. Give comma separated values starting with 0 in RD Scheme. e.g. 0,20,16,9,7' value='0'")
//                ->text("Number Of Premiums", "name='NumberOfPremiums' class='input req-numeric tooltip' title='Type the value for Number of Premiums in RD & number of EMI in Loan Schemes'")
                ->select("Active Status", "name='ActiveStatus'", array('Active' => '1', 'InActive' => '0'), 1)
                ->select("Under Head", "name='Head' class='not-req tooltip' title='Select the head to which Scheme belongs' not-req-val='-1'", $arr)
//                ->checkBox("Check if Processing Fee in %", "name='ProcessingFeesinPercent' class='input' value='1' checked")
//                ->text('Processing Fees', "name='ProcessingFees' class='input req-numeric tooltip' title='Give processing fee in % if Checkbox is checked' value='0'")
                ->checkBox("Interest To Account (check if interest to be posted to other account)", "name='InterestToAnother' class='input' value='1'")
                ->text("Period of Maturity for FD, MIS, RD, DDS(in months)", "name='MaturityPeriod' class='input tooltip' title='Period of Maturity for FD, MIS, RD, DDS in years'")
                ->text("Scheme Points","name='SchemePoints' class='input'")
                ->text("Scheme Group","name='SchemeGroup' class='input'");
//                ->text("Interest % (To Saving Account) for HID", "name='InterestPercentToAnother' class='input tooltip' title='Provide rate of interest for saving account in case of HID Scheme'")
//                ->checkbox("Is Depriciable", "name='isDepriciable' class='input' value='1'")
//                ->text("Depriciation % before September", "name='DepriciationPercentBeforeSep' class='input'")
//                ->text("Depriciation % After September", "name='DepriciationPercentAfterSep' class='input'")

                  if($xc->getKey("number_of_agent_levels")){
                            for($i=1;$i<=$xc->getKey("number_of_agent_levels");$i++)
                                $form = $form->text("Commission String for Level $i","name='AgentCommissionString$i' class='input'");
                        }

                 $form = $form->confirmButton("Confirm", "New Scheme to create", "index.php?option=com_xbank&task=schemes_cont.confirmSchemesCreateForm&format=raw", true)
                 ->submit("Create");
$this->jq->addTab(1, "Fixed & MIS Schemes", $this->form->get());

?>
