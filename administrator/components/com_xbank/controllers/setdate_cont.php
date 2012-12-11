<?php
class setdate_cont extends CI_Controller {


    function setDateTimeForm() {
        //Staff::accessibleTo(POWER_USER);
        xDeveloperToolBars::onlyCancel("com_xbank.index", "cancel", "Set the date here");
        $CI = & get_instance();
        $currDate = $CI->session->userdata('currdate');

        //setInfo("SET THE DATE : ", "Current Date is " . getNow());
        //xDeveloperToolBars::onlyCancel("transaction_cont.index", "cancel", "Withdrawl Amount");
        $form = $this->load->library('form');
        //index.php?option=com_xbank&task=transaction_cont.DoWithdrawl
        $this->form->open("one", "index.php?option=com_xbank&task=setdate_cont.setDateTime")
                ->setColumns(1)
                ->datebox("Change Date To", "name='newDate' class='input req-string'")
                ->submit("Change");
//            }


        $data['contents'] = $this->form->get();
        $this->load->view('setdate.html', $data);
         $this->jq->getHeader();
    }
    function setDateTime() {
         $c=new Closing();
        $closing=$c->where('branch_id',Branch::getCurrentBranch()->id)->get();
       
        $CI = & get_instance();
        $CI->session->set_userdata('currdate', inp("newDate") . " " . getNow("H:i:00"));
//        $session =& JFactory::getSession();
//        $session->set('currdate', inp("newDate") . " " . getNow("H:i:00"),'default');

        re('com_xbank.index');
    }
     function clearDateTime() {
        $CI = & get_instance();
        $CI->session->unset_userdata('currdate');
        re('setdate_cont.setDateTimeForm');
    }
}
?>
