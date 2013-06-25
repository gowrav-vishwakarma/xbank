<?php
/**
 * /tmp/phptidy-sublime-buffer.php
 *
 * @package default
 */


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class rd_corrections_cont extends CI_Controller {

	var $all_okey_till;

	function __construct() {
        parent::__construct();
		$this->all_okey_till = getNow('Y-m-01');
    }


	function step1(){
		echo "<h3>are the premiums paid in this month correct for all accounts</h3>";
		$p=new Premium();
		$p->select('accounts_id, 	COUNT(*) paid_premiums');
		$p->include_related('account','AccountNumber');
		$p->include_related('account/member','Name');

		$p->where_related('account','branch_id',BRanch::getCurrentBranch()->id);
		$p->where_related('account/scheme','SchemeType','Recurring');
		$p->where('PaidOn >', $this->all_okey_till);
		
		$p->group_by('accounts_id');
		$p->get();

		$form = $this->form->open("pandl", "index.php?option=com_xbank&task=rd_corrections_cont.step1_submitted");

		$i=1;
		foreach($p as $pr){
			$form->hidden($pr->account_member_Name,"name='account_$i' value='$pr->accounts_id'");
			$form->text($pr->account_AccountNumber,"name='acc_".$pr->accounts_id."' value='$pr->paid_premiums'");
			$i++;
		}

		$form->hidden('','name="total_count" value="'.$p->result_count().'"');
		$form->submit('Correct');

		$data['report'] = $form->get(); 

		// $data['report'] .= getReporttable($p, //model
  //           array("Account Number", "no Of Premiums Paid" ), //heads
  //           array('account_AccountNumber', 'paid_premiums'), //fields
  //           array(), //totals_array
  //           array(), //headers
  //           array('sno' => true), //options
  //           "<b>----------</b>", //headerTemplate
  //           '', //tableFooterTemplate
  //           "", //footerTemplate,
  //           array()
  //       );

        JRequest::setVar("layout", "generalreport");
        $this->load->view('report.html', $data);
        $this->jq->getHeader();

		// Get How many installments have been submitted in this month for each RD Account
		// generate a form with existing values
		// On this form submit adjust last Paid On values
	}

	function step1_submitted(){
		for($i=1;$i<=inp('total_count');$i++){
			$account_id=inp("account_".$i);
			// echo $i. "::". $account_id."<br/>";

			$p=new Premium();
			$p->select('COUNT(*) paid_premiums');
			$p->include_related('account','AccountNumber');
			$p->include_related('account/member','Name');

			$p->where('PaidOn >', $this->all_okey_till);
			$p->where('accounts_id',$account_id);			
			
			$p->get();

			if($p->paid_premiums > inp('acc_'.$account_id)){
				$diff= $p->paid_premiums - inp('acc_'.$account_id);
				$pt=new Premium();
				$pt->where('accounts_id',$account_id);
				$pt->order_by('id','desc');

				$pt->where('PaidOn is not null');
				$pt->limit($diff);
				$pt->get();

				foreach($pt as $ppt){
					echo "setting ". $ppt->PaidOn. "<br/>";
					$ppt->PaidOn = null;
					$ppt->save();
				}
			}

			if($p->paid_premiums < inp('acc_'.$account_id)){
				$diff= inp('acc_'.$account_id) - $p->paid_premiums;
				echo $p->account_AccountNumber . " is up by ". $diff . "<br/>";
				
				$last_PaidOn = new Premium();
				$last_PaidOn->where('accounts_id',$account_id);
				$last_PaidOn->where('PaidOn is not null');
				$last_PaidOn->order_by('id','desc');
				$last_PaidOn->limit(1);
				$last_PaidOn->get();


				$pt=new Premium();
				$pt->where('accounts_id',$account_id);
				$pt->order_by('id','asc');

				$pt->where('PaidOn is null');
				$pt->limit($diff);
				$pt->get();
				echo $pt->check_last_query();
				
				foreach($pt as $ppt){
					$ppt->PaidOn = $last_PaidOn->PaidOn;
					$ppt->save();
				}
			}

		}

		re("rd_Corrections_cont.step2");
	}

	function step2(){
		//Premium Paid counter corrections


        // Mark wrongly mature rds 
        // $q="UPDATE jos_xaccounts a JOIN jos_xschemes s on a.schemes_id=s.id SET a.MaturedStatus=0, a.affectsBalanceSheet=0, a.ActiveStatus=1 WHERE s.SchemeType = '".ACCOUNT_TYPE_RECURRING. "' AND a.id not in (7050,2527,2557,2556) AND a.MaturedStatus = 1";
        // $this->db->query($q);

        $a=new Account();
        if(inp('acc')) 
                $a->where('AccountNumber like', str_replace("-", "%", inp('acc'))); //Comment to run on all
        $a->where_related('scheme', 'SchemeType', ACCOUNT_TYPE_RECURRING);
        $a->where('branch_id',Branch::getCurrentBranch()->id);
        $a->get();
        // $total_accounts = $a->count();
        // $account_count=1;
        foreach ($a as $acc) {
            // echo "Done " . $account_count++ . " out of " . $total_accounts . "<br/>";
            // ob_end_flush();

            $tilldate= nextDate(null,true);

            // $this->db->query("UPDATE jos_xpremiums SET Paid=0 WHERE accounts_id = $acc->id");
            $this->db->query("UPDATE jos_xpremiums SET  AgentCommissionSend=1 WHERE accounts_id = $acc->id AND (PaidOn < '$this->all_okey_till' AND PaidOn is not null)");
            $due_and_paid_query = $this->db->query("SELECT GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray, GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray FROM jos_xpremiums WHERE accounts_id = $acc->id AND (PaidOn < '$tilldate' OR DueDate < '$tilldate') ORDER BY id")->row();
            $due_array=explode(",",$due_and_paid_query->DueArray);
            $paid_array=explode(",",$due_and_paid_query->PaidArray);

            if(inp('acc')){
                print_r($due_array);
                print_r($paid_array);
            }
            
            $account_premiums=$acc->premiums
            ->where("PaidOn < '$tilldate'")
            ->or_where("DueDate < '$tilldate'")
            ->order_by('id')
            ->get();


            $i=0;
            foreach($account_premiums as $p){

                $paid=0;
                for($j=0;$j<=$i;$j++){
                    if(isset($paid_array[$j]) AND $paid_array[$j] <= $due_array[$i]) $paid++;
                    // if(isset($paid_array[$j]) AND $j==0 AND $paid_array[$j] > $due_array[$i]) $paid++;
                }
                $p->Paid= $paid;
                $p->save();                                
                $i++;
            }
            // ob_flush();
            // flush();
        }

        echo "done";

        if(inp('acc')){
            // Comment below to run on all 
            $p=new Premium();
            $p->include_related('account', 'AccountNumber');
            $p->where('accounts_id', $a->id);
            $p->get();

            $data['report']= getReporttable($p,             //model
                array("id", 'Amount', "Due Date", 'Paid On', 'Paid'),       //heads
                array('id', 'Amount', 'DueDate', 'PaidOn', 'Paid', ),       //fields
                array(),        //totals_array
                array("Account Number"=>'account_AccountNumber'),        //headers
                array('sno'=>true),     //options
                "",     //headerTemplate
                '',      //tableFooterTemplate
                ""      //footerTemplate
            );

            JRequest::setVar("layout", "generalreport");
            $this->load->view('report.html', $data);
            $this->jq->getHeader();
        }
    
    	re('rd_Corrections_cont.step3',"ALL DONE. CHECK COmmissions now.. do not run it again");
	}


	function step3(){
		//Agent commission corrections	in premium table and new commission set
		$branch = Branch::getCurrentBranch()->id;
		$transactiondate = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day"));
            
            // ============ All Unpaid Commission Premiums
            $q="
                SELECT
                    a.id,
                    a.AccountNumber,
                    (SELECT COUNT(p.id)  FROM  jos_xpremiums p WHERE  p.AgentCommissionSend=0 AND p.accounts_id= a.id AND PaidOn >= '$this->all_okey_till' ) to_set_commission /* AND p.PaidOn is not null condition removed */
                FROM
                    jos_xaccounts a
                JOIN jos_xschemes s ON a.schemes_id = s.id
                WHERE
                    a.branch_id = $branch
                    AND s.SchemeType='Recurring'
                    AND a.ActiveStatus=1
                GROUP BY
                    a.id
                HAVING to_set_commission > 0
            ";

            $accounts=$this->db->query($q)->result();
            foreach ($accounts as $ac) {
                $acc = new Account($ac->id);
                $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
                $this->setCommissions($acc, $voucherNo,$transactiondate);
            }

        re('rd_Corrections_cont.step1');

	}

	function step4(){
		// 		
	}

    public function setCommissions($ac, $voucherNo, $transactiondate) {
        

        $ssddhj = ($ac->id == 9558 ? "sdsds" : "dhdbhj");

        $CI = & get_instance();
        $amount = $CI->db->query("select SUM(Amount * AgentCommissionPercentage / 100.00 ) AS Totals from jos_xpremiums where PaidOn is not null AND AgentCommissionSend = 0 AND accounts_id = $ac->id")->row()->Totals;
        $scfcsg = $ac->AccountNumber;
        $ag = new Agent($ac->agents_id);
        $agentAccount = $ag->AccountNumber;//get()->AccountNumber;
        $gbngfn =10;
        echo "For $ac->AccountNumber agent $ag->AccountNumber <br/>";
        if ($agentAccount) {
            echo " ----- Going in <br/>";
            $agent = new Agent();
            $agent->where("AccountNumber",$agentAccount)->get();
            $schemename = $ac->scheme->Name;
            $agentAccBranch = new Account();
            $agentAccBranch->where("AccountNumber",$agent->AccountNumber)->get();
            if ($agentAccBranch->branch_id != Branch::getCurrentBranch()->id) {
                // INTERBRANCH TRANSECTION
                $otherbranch = new Branch($agentAccBranch->branch_id);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $schemename => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $otherbranch->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . Branch::getCurrentBranch()->Code => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * 10 / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ".$ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $otherbranch->Code => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                );
                $creditAccount = array(
                    // get agents' account number
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ".$ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate, $otherbranch->id);
            } else {
//                $tdsaccount = Branch::getCurrentBranch()->Code." TDS";
//                $tdsAcc = Doctrine::getTable("Accounts")->findOneByAccountnumberAndBranch_id($tdsaccount,Branch::getCurrentBranch()->id)->AccountNumber;

                $debitAccount = array(
                    Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $schemename => $amount,
                );
                $creditAccount = array(
                    // get agents' account number
                    //                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
                    $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
                    Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * 10 / 100),
                );
                Transaction::doTransaction($debitAccount, $creditAccount, "RD Premium Commission ". $ac->AccountNumber, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo, $transactiondate);
            }
            executeQuery("UPDATE jos_xpremiums SET AgentCommissionSend=1 WHERE PaidOn is not null AND AgentCommissionSend = 0 AND accounts_id = " . $ac->id);
//            $AgentSavingAccount=Accounts::getAccountForCurrentBranch(Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id,false);
//            Accounts::updateInterest($agentAccount);
        }
    }


}