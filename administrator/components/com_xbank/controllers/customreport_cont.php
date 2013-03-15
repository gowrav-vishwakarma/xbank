<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class customreport_cont extends CI_Controller {

    /**
     * Function generates a <b>FORM</b> to create a new report
     * Sends the link to {@link saveNewReport}
     */
    function index() {
        xDeveloperToolBars::getCustomReportsToolBar();
        $this->load->view("customreport.html");
        $this->jq->getHeader();
    }

    function newReport() {
//         Staff::accessibleTo(USER);
        xDeveloperToolBars::onlyCancel("com_xbank.index", "cancel", "Add a new Custom Report here");
        $form = $this->load->library("form");
        $this->form->setColumns(1)
                ->open(1, "index.php?option=com_xbank&task=customreport_cont.saveNewReport")
                ->text("Name Of Report", "name='reportname' class='input req-string'")
                ->textArea("Title To Display", "name='reportTitle' class='req-string'cols=100 rows=6", "")
                ->textArea("Code To Run Before Form", "name='beforeForm' class='req-string' cols=100 rows=6", "")
                ->textArea("Form Code", "name='FormCode' class='req-string'  cols=100 rows=6", "")
                ->textArea("CodeToRun", "name='CodeToRun' class='req-string' cols=100 rows=6", "")
                ->textArea("Result Mapping", "name='resultMapping'  cols=100 rows=6", "")
                ->textArea("Totals Of Fields", "name='totalsField'  cols=100 rows=6", "")
                ->checkBox("Publish Report", "name='published' class='input'")
                ->submit("Make");
        $data['contents'] = $this->form->get();
        JRequest::setVar("layout", "customreport");
        $this->load->view("customreport.html", $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $id
     * <b>FORM</b> to edit a report
     * Sends the link to {@link saveNewReport}
     */
    function editReport() {
//         Staff::accessibleTo(USER);
        $id = inp("id");
        $r = new report($id);

        $this->load->library("form");
        $form = $this->form;
        $form->setColumns(1);
        $form->open(1, "index.php?option=com_xbank&task=customreport_cont.saveNewReport&id=$id");
        $form->text("Name Of Report", "name='reportname' class='input req-string' value='$r->Name'", "");
        $form->textArea("Title To Display", "name='reportTitle' class='req-string'cols=100 rows=6", "", $r->ReportTitle);

        $form = $form->textArea("Code To Run Before Form", "name='beforeForm' class='req-string' cols=100 rows=6", "", $r->CodeBeforeForm)
                        ->textArea("Form Code", "name='FormCode' class='req-string'  cols=100 rows=6", "", $r->formFields)
                        ->textArea("CodeToRun", "name='CodeToRun' class='req-string' cols=100 rows=6", "", $r->CodeToRun)
                        ->textArea("Result Mapping", "name='resultMapping'  cols=100 rows=6", "", $r->Results)
                        ->textArea("Totals Of Fields", "name='totalsField'  cols=100 rows=6", "",$r->total_fields)
                        ->checkBox("Publish Report", "name='published' class='input'", ($r->published == 1 ? TRUE : FALSE))
                        ->submit("Make");
        $data['contents'] = $form->get();
        JRequest::setVar("layout", "customreport");
        $this->load->view("customreport.html", $data);
        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $id
     * Actaully saves the report if id="" else edits the report if id has a value
     */
    function saveNewReport() {
//         Staff::accessibleTo(USER);
        $id = inp("id");
        if ($id == "")
            $r = new report();
        else
            $r= new report($id);

        $r->Name = inp("reportname");
        $r->CodeBeforeForm = inp("beforeForm");
        $r->formFields = inp("FormCode");
        $r->CodeToRun = inp("CodeToRun");
        $r->ReportTitle = inp("reportTitle");
        $r->Results = inp("resultMapping");
        $r->total_fields = inp("totalsField");
        $r->published = (inp("published") ? 1 : 0);
        $r->save();
        $id = $r->id;
        re("customreport_cont.editReport&id=$id");
    }

    /**
     *
     * @param <type> $id
     * Function generates a report based on the report table fields
     */
    function generateReport() {
//         Staff::accessibleTo(USER);
        extract($_REQUEST);
        $r = new Report($id);
        eval($r->CodeToRun);

        $data['contents'] = reporter::getReport($id, $result, $r->Results, $r->ReportTitle,$r->total_fields);
        JRequest::setVar("layout", "customreport");
        $this->load->view("customreport.html", $data);
        $this->jq->getHeader();
    }

    public function _remap($method, $params = array()) {
        $arr = explode("_", $method);
        $mymethod = 'showTestForm';
        if ($arr[0] == $mymethod) {
            $this->$mymethod($arr[1]);
        } else {
            $this->$method();
        }
    }

    /*
     * Function shows the report
     */
    function showTestForm($id=15) {
//        $id = JRequest::getVar("id");
        $report=new report($id);
        xDeveloperToolBars::onlyCancel("customreport_cont.index", "cancel", "$report->ReportTitle");
        $form = reporter::getReportForm($id);
        $data['contents'] = $form;
        JRequest::setVar("layout", "customreport");
        $this->load->view("customreport.html", $data);
        $this->jq->getHeader();
    }
    

}

?>