<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class reporter {
    public function reporter(){

    }
    
    public static function getReportForm($id){
//        $id = inp("id");
        $CI=& get_instance();
        $form=$CI->load->library("form");
        $report=new report($id);
//        setInfo($report->ReportTitle, "");
        $reportID=$id;
        eval($report->CodeBeforeForm);
        eval($report->formFields);
//        $form->open(1,"index.php?//mod_reports/generateReport/$formID")->text("Name","name='name' class='input req-string'")
//->lookupDB("Account number : ","name='AccountNumber' class='input req-string'","index.php?//ajax/lookupDBDQL",array("select"=>"a.*","from"=>"Accounts a","leftJoin"=>"a.Branch b","where"=>"a.AccountNumber Like '%\$term%'","andWhere"=>"b.id='2'","limit"=>"10"),array("AccountNumber"),"");
        return $CI->form->get();
    }

    
    public static function getReport($id,$result,$arr,$ReportTitle){
//        setInfo($ReportTitle,"");
//        $html="<center>".$ReportTitle."</center><br/>";
        xDeveloperToolBars::onlyCancel("customreport_cont.index", "cancel", $ReportTitle);
        $html ="<table border='1' width='100%'><tr>";

         $temp=explode(",",$arr);
         $arr=array();
         foreach($temp as $t){
             $temp2=explode("=",$t);
             $keys=array_keys($temp2);
             $vals=array_values($temp2);
             $arr += array($temp2[0]=>$temp2[1]);
         }
         $html .="<th>S No</th>";
        foreach(array_values($arr) as $header){
            $html .="<th align='left'>$header</th>";
        }
        $html .="</tr>";
	$i = 1;
        foreach($result as $rs){
            $html .= "<tr>";
            $html .="<td>$i</td>";
            foreach(array_keys($arr) as $field){
                $field=trim($field);
                 if($field == 'voucher_no')
                    $html .= ("<td><a href='index.php?option=com_xbank&task=report_cont.transactionDetails&vn=".($rs->$field)."&format=raw'>".($rs->$field)."</a></td>");
                else
                    $html .= ("<td>".($rs->$field)."</td>");
            }
            $html .="</tr>";
            $i++;
        }
        $html .="</table>";
        return $html;
    }
}
?>
