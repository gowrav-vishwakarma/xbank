<?php

class printing_cont extends CI_Controller {

	function index() {
        xDeveloperToolBars::getPrintingToolBar();
        $this->load->view("report.html");
        $this->jq->getHeader();
    }


    function voucherPrintForm(){
    	xDeveloperToolBars::onlyCancel("printing_cont.index", "cancel", "Print Vouchers");
    	$this->form->open("accountOpenningForm","index.php?option=com_xbank&task=printing_cont.voucherPrintSelect")
                ->dateBox("Select Date","name='voucherDate' class='input'")
                ->submitNoHide("Go");
        $data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader(); 
    }

    function voucherPrintSelect(){
    	xDeveloperToolBars::onlyCancel("printing_cont.voucherPrintForm", "cancel", "Select Vouchers To Print");
    	$t=new TRansaction();
    	$t->where('created_at >= "' .inp('voucherDate').'"');
    	$t->where('created_at <"' . nextDate('voucherDate').'"');
    	$t->where('branch_id',Branch::getCurrentBranch()->id);
    	$t->group_by('voucher_no');
    	$t->get();
    	
    	$this->form->open("accountOpenningForm","index.php?option=com_xbank&task=printing_cont.voucherPrint&format=raw","target='blank'")
    	->setColumns(1)
    	->hidden('',"name='voucherDate' value='".inp('voucherDate')."'");

    	foreach($t as $tt){
    		$this->form->checkbox($tt->display_voucher_no. "($tt->Narration)","name='v_$tt->id'");
    	}
    	$this->form->submit('Print');
    	$data['form']=$this->form->get();
        $this->load->view("formonly.html",$data);
        $this->jq->getHeader();

    }

    function voucherPrint(){

    	$t=new TRansaction();
    	$t->where('created_at >= "' .inp('voucherDate').'"');
    	$t->where('created_at <"' . nextDate('voucherDate').'"');
    	$t->where('branch_id',Branch::getCurrentBranch()->id);
    	$t->group_by('voucher_no');
    	$t->get();
    	JRequest::setVar('layout','voucher');
    	
    	foreach($t as $tt){
    		if(inp('v_'.$tt->id)!='') {
    			$voucher_data['voucher_no'] = ($tt->display_voucher_no ==0 ? $tt->voucher_no : $tt->display_voucher_no);
    			$voucher_data['voucher_date'] = date("d-M-Y",strtotime($tt->created_at));
    			$dr_v=new TRansaction();
    			$dr_v->where('voucher_no',$tt->voucher_no);
    			$dr_v->where('branch_id',$tt->branch_id);
    			$dr_v->where('amountDr <>',0);
    			$dr_v->get();
    			
    			$cr_v=new TRansaction();
    			$cr_v->where('voucher_no',$tt->voucher_no);
    			$cr_v->where('branch_id',$tt->branch_id);
    			$cr_v->where('amountCr <>',0);
    			$cr_v->get();

    			$voucher_data['voucher_dr'] = $dr_v;
    			$voucher_data['voucher_cr'] = $cr_v;
    			echo $this->load->view('print.html',$voucher_data,true);
    		}
    	}

    	JRequest::setVar('layout','default');
	 	$this->load->view("print.html");
        $this->jq->getHeader();
    }

}

