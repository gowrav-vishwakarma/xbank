<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $Ac->id);
            $Ac = new Account($Ac->id);

//			Commission transfer
            if (($Ac->scheme->AccountOpenningCommission != "" OR $Ac->scheme->AccountOpenningCommission != null) AND $Ac->agents_id != null) {
                $commissionAmount = getComission($Ac->scheme->AccountOpenningCommission, OPENNING_COMMISSION);
            }

            if ($Agent && $commissionAmount != 0) {
                    $s = $Agent->AccountNumber;
                    $agents = new Account();
                    $agents->where('AccountNumber', $s)->get();
                    // $agents = Doctrine::getTable("Accounts")->findOneByAccountNumber($Agent->AccountNumber);
                    if ($agents->branch_id != Branch::getCurrentBranch()->id) {
                        $s = $agents->branch_id;
                        $otherbranch = new Branch();
                        $otherbranch->where('id', $s)->get();
                        //*****$otherbranch = Doctrine::getTable("Branch")->find($agents->branch_id);

                        $debitAccount += array(
                            Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $Ac->Schemes->Name => $commissionAmount,
                        );
                        $creditAccount += array(
                            // get agents' account number
                            //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                            $otherbranch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                            Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * TDS_PERCENTAGE / 100),
                        );
                        Transaction::doTransaction($debitAccount, $creditAccount, "Agent Account openning commision Transaction", TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $voucherNo);

                        $debitAccount = array(
                            Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $otherbranch->Code => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                        );
                        $creditAccount = array(
                            // get agents' account number
                            $Agent->AccountNumber => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                        );
                        Transaction::doTransaction($debitAccount, $creditAccount, "Agent Account openning commision Transaction", TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $voucherNo, false, $otherbranch->id);
                    } else {

                        $debitAccount = array(Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $Ac->Schemes->Name => $commissionAmount,);
                        $creditAccount = array(
//                                            Branch::getCurrentBranch()->Code."_Agent_SA_".$Agent->member_id => ($commissionAmount  - ($commissionAmount * 10 /100)) ,
                            $Agent->AccountNumber => ($commissionAmount - ($commissionAmount * TDS_PERCENTAGE / 100)),
                            Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($commissionAmount * TDS_PERCENTAGE / 100),
                        );
                        Transaction::doTransaction($debitAccount, $creditAccount, "Agent Account openning commision Transaction", TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $voucherNo);
                    }
                }
?>
