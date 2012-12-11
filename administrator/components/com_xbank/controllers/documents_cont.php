<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class documents_cont extends CI_Controller {

    function documents_cont() {
        parent::__construct();
    }

    /**
     * function to create new Document <B>FORM</b>
     */
    function documentForm() {
        xDeveloperToolBars::onlyCancel("com_xbank.index", "cancel", "Add a new Document here");
        $this->load->library('form');
        $this->form->open("one", 'index.php?option=com_xbank&task=documents_cont.addDocument')
                ->setColumns(2)
                ->text("Document Name", "name='DocumentName' class='input req-string'")
                ->_()
                ->checkBox("Saving Current Account", "name='SavingAccount' class='input' value='1'")
                ->_()
                ->checkBox("Fixed and MIS Accounts", "name='FixedMISAccount' class='input' value='1'")
                ->_()
                ->checkBox("Loan Account", "name='LoanAccount' class='input' value='1'")
                ->_()
                ->checkBox("RD and DDS Accounts", "name='RDandDDSAccount' class='input' value='1'")
                ->_()
                ->checkBox("CC Account", "name='CCAccount' class='input' value='1'")
                ->_()
                ->checkBox("Other Accounts", "name='OtherAccounts' class='input' value='1'")
                ->submit('Create');
        $data['contents'] = $this->form->get();
        $data['result'] = $this->db->query("select * from jos_xdocuments")->result();
        $this->load->view("document.html", $data);
        $this->jq->getHeader();
    }

    /**
     * Actually creates a new document
     * Saves the document name and 1 for accounts for which the document has to be submitted
     */
    function addDocument() {

        try {
            $this->db->trans_begin();
            $docs = new Document();
            $docs->Name = inp('DocumentName');
            $docs->SavingAccount = inp('SavingAccount');
            $docs->FixedMISAccount = inp('FixedMISAccount');
            $docs->LoanAccount = inp('LoanAccount');
            $docs->RDandDDSAccount = inp('RDandDDSAccount');
            $docs->CCAccount = inp('CCAccount');
            $docs->OtherAccounts = inp('OtherAccounts');
            $docs->save();

            $this->db->trans_commit();
        } catch (Exception $e) {
            $rollback = true;
        }
        if ($this->db->trans_status() === false or $rollback == true) {
            $this->db->trans_rollback();
            re("documents_cont.documentForm", " Document Not Added ", "error");
        }
        $this->db->trans_commit();
        re('documents_cont.documentForm',"New Document Successfully Added");
    }

    /**
     *
     * @param <type> $id
     * Function to edit a document
     * Does not actually edit the document. creates a <b>form</b> for editing.
     */
    function editDocumentForm($id='') {
//        Staff::accessibleTo(ADMIN);

//        setInfo("EDIT DOCUMENT", "");
        $id = JRequest::getVar("id");
        $docs = new Document($id);
        $chkStatus = "UNCHECKED";
        $this->load->library('form');
        $form = $this->form->open("one", "index.php?option=com_xbank&task=documents_cont.editDocument&id=$id")
                        ->setColumns(2)
                        ->text("Document Name", "name='DocumentName' class='input req-string' value='$docs->Name'")
                        ->_();
        if ($docs->SavingAccount == 1) {
            $form = $form->checkBox("Saving Current Account", "name='SavingAccount' class='input' value='1' CHECKED")
                            ->_();
        } else {
            $form = $form->checkBox("Saving Current Account", "name='SavingAccount' class='input' value='1'")
                            ->_();
        }

        if ($docs->FixedMISAccount == 1) {
            $form = $form->checkBox("Fixed and MIS Accounts", "name='FixedMISAccount' class='input' value='1' CHECKED")
                            ->_();
        } else {
            $form = $form->checkBox("Fixed and MIS Accounts", "name='FixedMISAccount' class='input' value='1'")
                            ->_();
        }

        if ($docs->LoanAccount == 1) {
            $form = $form->checkBox("Loan Account", "name='LoanAccount' class='input' value='1' CHECKED")
                            ->_();
        } else {
            $form = $form->checkBox("Loan Account", "name='LoanAccount' class='input' value='1' ")
                            ->_();
        }
        if ($docs->RDandDDSAccount == 1) {
            $form = $form->checkBox("RD and DDS Accounts", "name='RDandDDSAccount' class='input' value='1' CHECKED")
                            ->_();
        } else {
            $form = $form->checkBox("RD and DDS Accounts", "name='RDandDDSAccount' class='input' value='1' ")
                            ->_();
        }
        if ($docs->CCAccount == 1) {
            $form = $form->checkBox("CC Account", "name='CCAccount' class='input' value='1' CHECKED")
                            ->_();
        } else {
            $form = $form->checkBox("CC Account", "name='CCAccount' class='input' value='1' ")
                            ->_();
        }
        if ($docs->OtherAccounts == 1) {
            $form = $form->checkBox("Other Accounts", "name='OtherAccounts' class='input' value='1' CHECKED")
                            ->_();
        } else {
            $form = $form->checkBox("Other Accounts", "name='OtherAccounts' class='input' value='1' ")
                            ->_();
        }
        $form = $form->submit('Edit')
                        ->resetBtn("Reset");
        $data['contents'] = $this->form->get();
        $data['result'] = $this->db->query("select * from jos_xdocuments")->result();
        $this->load->view("document.html", $data);

        $this->jq->getHeader();
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     * Acyually edit the documents
     */
    function editDocument($id) {
        try {
            $id = JRequest::getVar("id");
            $this->db->trans_begin();
            $docs = new Document($id);
            $docs->Name = inp('DocumentName');
            $docs->SavingAccount = inp('SavingAccount');
            $docs->FixedMISAccount = inp('FixedMISAccount');
            $docs->LoanAccount = inp('LoanAccount');
            $docs->RDandDDSAccount = inp('RDandDDSAccount');
            $docs->CCAccount = inp('CCAccount');
            $docs->OtherAccounts = inp('OtherAccounts');
            $docs->save();
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
        re('documents_cont.documentForm',"Document ".inp('DocumentName')." added successfully.");
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     * Removes the document with id $id
     */
    function removeDocument($id='') {
//        Staff::accessibleTo(ADMIN);
        $id = JRequest::getVar("id");
        try {
            //TODO- Account number addition + Datatable all fields recheck
            //User ID Unique
            $this->db->trans_begin();
//	 		$categ=Doctrine::getTable('Category')->find($id);
            $q = "delete from jos_xdocuments where id = $id";
            executeQuery($q);
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
//            return;
            re("documents_cont.documentForm","Document not Deleted Successfully.",'error');
        }
// 		setInfo("Saved","Your new Category has been sucessfully saved");
        re('documents_cont.documentForm','Document Removed Successfully.');
    }

}

?>
