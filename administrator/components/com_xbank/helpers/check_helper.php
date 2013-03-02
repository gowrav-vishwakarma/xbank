<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function inp($key) {
    $CI = & get_instance();
    return (($CI->input->post($key) == "") ? $CI->input->get($key) : $CI->input->post($key));
}

function re($url, $msg="", $type="message") {
    xRedirect("index.php?option=com_xbank&task=" . $url, $msg, $type);
}

function showError($msg){
    echo $msg;
}

function getNow($format="Y-m-d H:i:00"){
		date_default_timezone_set('Asia/Calcutta');
		$timeStamp = strtotime('now');
		$timeStamp=date($format,$timeStamp);
//		return $timeStamp;
//		echo date_default_timezone_get();
                $CI=& get_instance();
                if($currDate=$CI->session->userdata('currdate')){
                    $currDate = strtotime($currDate);
                    $currDate = date($format,$currDate);
                    return $currDate;
                }
                else{
                    return $timeStamp;
                }


	}


        function my_date_diff($d1, $d2){
		$d1 = (is_string($d1) ? strtotime($d1) : $d1);
		$d2 = (is_string($d2) ? strtotime($d2) : $d2);

		$diff_secs = abs($d1 - $d2);
		$base_year = min(date("Y", $d1), date("Y", $d2));

		$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
		return array(
		"years" => date("Y", $diff) - $base_year,
		"months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
		"months" => date("n", $diff) - 1,
		"days_total" => floor($diff_secs / (3600 * 24)),
		"days" => date("j", $diff) - 1,
		"hours_total" => floor($diff_secs / 3600),
		"hours" => date("G", $diff),
		"minutes_total" => floor($diff_secs / 60),
		"minutes" => (int) date("i", $diff),
		"seconds_total" => $diff_secs,
		"seconds" => (int) date("s", $diff)
		);

	}

        function myinclude($file){
            if(file_exists(strtolower($file)))
                include($file);


        }

        function getComission($CommissionString, $whatToGet,$PremiumNumber=-1){
            $CommissionString=trim($CommissionString," ");
            $CommissionString=trim($CommissionString,",");
            $commArray=explode(",",$CommissionString);
            $result=array();
            $result["isPercentage"]=false;
            if(strpos("%", $commArray[0]) !== false ){
                $result["isPercentage"]=true;
//                $commArray[0]=str_replace("%", "");
            }
            switch($whatToGet){
                case OPENNING_COMMISSION:
                    $result["Commission"]=trim($commArray[0]);

                    break;
                case PREMIUM_COMMISSION:
                    if(count($commArray)  <= 1){
                        throw new Exception("Premium must start from second Commission");
                    }

                    if($PremiumNumber >= count($commArray)){
                        $commArray[$PremiumNumber]=$commArray[count($commArray)-1];
                    }

                    if(strpos("%", $commArray[$PremiumNumber]) !== false ){
                        $result["isPercentage"]=true;
//                        $commArray[$PremiumNumber]=str_replace("%", "");
                    }

                    $result["Commission"]=trim($commArray[$PremiumNumber]);
                    $result["isPercentage"]=true;
                    break;
            }

            return $result["Commission"];
        }


        function formatDrCr($DR,$CR){
            $html='<table width="100%" border="1">
                  <tr class="ui-widget-header">
                    <th colspan="2"><div align="center">Debit Acocunts</div></th>
                    <th colspan="2"><div align="center">Credit Accounts</div></th>
                  </tr>
                  <tr>
                    <th>Account</th>
                    <th>Amount</th>
                    <th>Account</th>
                    <th>Amount</th>
                  </tr>';
            $DRKeys=array_keys($DR);
            $DRVals=array_values($DR);

            $CRKeys=array_keys($CR);
            $CRVals=array_values($CR);
            for($i=0;$i< max(count($DR),count($CR));$i++){

             $html .='<tr class="ui-widget-contents">
                    <td>'.  ((isset($DRKeys[$i]))? $DRKeys[$i] : "&nbsp;") .'</td>
                    <td>'. ((isset($DRKeys[$i]))? $DRVals[$i] : "&nbsp;") .'</td>
                    <td>'.  ((isset($CRKeys[$i]))? $CRKeys[$i] : "&nbsp;") .'</td>
                    <td>'. ((isset($CRKeys[$i]))? $CRVals[$i] : "&nbsp;") .'</td>
                  </tr>';
            }
            $html .='</table>';
            return $html;
        }

          function printTable($records,$title){
            $ci = & get_instance();
            $ci->load->library("table");
            $ci->table->set_heading($title);

            foreach($records as $r){
                $ci->table->add_row(array_values($r));
            }

            return $ci->table->generate($data);
        }

        function makeoperator($val,$whatis=''){
		$val=trim($val);
		while(strpos($val,"  ")){
			$val=str_replace("  "," ",$val);
		}
		switch(substr($val,0,2)){
			case "= ":
				$val="= '" . trim(substr($val,strpos($val," "))) . "'";
				break;
			case "> ":
				$val="> '" . trim(substr($val,strpos($val," "))) . "'";
				break;
                        case "< ":
				$val="< '" . trim(substr($val,strpos($val," "))) . "'";
				break;
			case ">=":
				$val=">= '" . trim(substr($val,strpos($val," "))) . "'";
				break;
			case "<=":
				$val="<= '" . trim(substr($val,strpos($val," "))) . "'";
				break;
			case "<>":
				$val="<> '" . trim(substr($val,strpos($val," "))) . "'";
				break;
			case "li":
				$val="like '%" . trim(substr($val,strpos($val," "))) . "%'";
				break;
			case "no":
				$val="not like '%" . trim(substr($val,strpos($val," ",5))) . "%'";
				break;
			case "be":
				$val1=trim(substr($val,strpos($val," "),strpos($val," and")-strpos($val," ")));
				$val2=trim(substr($val,strpos($val,"and ")+4));
				$val="between '" . $val1 . "' AND '". $val2 ."' ";
				break;
		}
		if($whatis=='select'){
			$val=" like '". $val ."'";
		}
		return " ".$val;
	}

        function executeQuery($query){
            $CI = & get_instance();
            $CI->db->query($query);
        }

        function extractNumberFromString($str){
            $no =false;
            $len = strlen($str);
            for($i=0;$i<$len;$i++){
                if(substr($str, $i, 1) != 0){
                    if(!is_numeric(substr($str, $i, 1)))
                        continue;
                        $no = substr($str,$i);
                        break;
                }
            }
            return $no;
        }

        function extractPrefixFromString($str){
            $no ="";
            $len = strlen($str);
            for($i=0;$i<$len;$i++){
                  if(!is_numeric(substr($str, $i, 1))){
                        $no .= substr($str,$i,1);
                        continue;
                    }
                        break;
            }
            return $no;
        }

       
/* function to generate the next code,
 * $query returns the count of the numbers generated
 * $originalcode refers to the default code for the series
 */
        function getNextCode($originalcode,$query){
            $CI = & get_instance();
            global $com_params;
            $acc = $CI->db->query($query)->row();
//            $a = array_values($acc);
            foreach($acc as $a)
                $nos = $a;

            $newno = $nos + 1;
//            if(!is_numeric($originalcode)){
                $pr=substr($originalcode,'0',strlen($originalcode)- strlen($newno));
                $pr .= $newno;
//            }
//            else
//                $pr = $originalcode + $newno;
            return $pr;
        }

        function getPath($path,$LegCount=0){
//            $p = explode(".",$path);
            $np = ($LegCount+1);
            $newpath = $path .".".str_repeat(0,strlen("000")-strlen($np)).$np;
            return $newpath;
        }
        
$table_id=0;

function getReportTable($model, $heads, $fields, $totals_array,$headers, $option,$headerTemplate="",$tableFooterTemplate="", $footerTemplate="",$links=array()) {
    $CI =& get_instance();
    $table_id = $CI->session->userdata('table_id');
    if(!$table_id) $table_id=0;
    $table_id++;
    $CI->session->set_userdata('table_id',$table_id);

    // $CI->jq->addJs('js/jquery.dataTables.js');
    // $CI->jq->addJs('js/TableTools.js');
    // $CI->jq->addJs('js/ZeroClipboard.js');
    $headerTemplate_striped=strip_tags($headerTemplate);
    $tableScript="$('#report_".$table_id."').dataTable({'bPaginate' : false, 'bSort': true, 'sDom': 'T<\"clear\">lfrtp', 
        'oTableTools': {
            'sSwfPath': 'copy_csv_xls_pdf.swf',
            'aButtons': [
                'copy',
                'csv',
                'xls',
                {
                    'sExtends': 'pdf',
                    'sPdfMessage': ' $headerTemplate_striped '
                },
                'print'
            ]
        }
     });";

    $CI->jq->addDomReadyScript($tableScript);

    $html='';
    foreach ($totals_array as $tt)
        $sum[$tt] = 0;

    $html .=$headerTemplate;

    if(count($headers)>0){
        foreach($headers as $title => $field){
            $html .= "<b>".$title . "</b> : " . ((strpos($field,'~') === false ) ? $model->$field : str_replace("~","",$field) ) . "<br/>";
        }
    }

    $html .= "<br/><br/>
        <table class='adminlist1' border='1' width='100%' id='report_$table_id' class='report'><thead>";
    $html .="<tr>";
    if ($option['sno'] == true) {
        $html .=" <th>SNO</th>";
        $sno = isset($option['sno_start'])? ($option['sno_start']%2==0?$option['sno_start']+1:$option['sno_start']):1;
    }
    foreach ($heads as $h) {
        $html .= "<th>$h</th>";
    }
    $html .="</tr></thead><tbody>";
    foreach ($model as $m):
        $html .="<tr >";
        if ($option['sno'] == true) {
            $html .=" <td>".sprintf('%06d',$sno++)."</td>";
        }
        foreach ($fields as $f) {
            if(strpos($f,"~") !==false){
                $ft=$f;
            	$ft=str_replace("~","",$ft);
            	$ft=str_replace("#",'$m->',$ft);
            	$ft =' $tf = '. $ft . ';';
            	eval($ft);
                if(array_key_exists($f, $links)){
                    if(isset($links[$f]['url_post'])){
                        $ft2="";
                        foreach($links[$f]['url_post'] as $var=>$val){
                            eval('$val_t = '.str_replace("#",'$m->',$val).';');
                            $ft2.= "&".$var. "=". $val_t;
                        }
                    }else{
                        $ft2="";
                    }
                    $a_s="<a href='index.php?option=com_xbank&task=".$links[$f]['task']."&".$ft2."' class='".(isset($links[$f]['class'])?$links[$f]['class'] : "" ) ."' title='".(isset($links[$f]['title'])?$links[$f]['title'] : "" ) ."'>";
                    $a_e = "</a>";
                }else{
                    $a_s="";
                    $a_e = "";
                }
            	$html.= "<td>$a_s". $tf . "$a_e</td>";
            	if (in_array($f, $totals_array))
            	    $sum[$f] += $tf;
            }else{
            if(array_key_exists($f, $links)){
                if(isset($links[$f]['url_post'])){
                        $ft2="";
                        foreach($links[$f]['url_post'] as $var=>$val){
                            eval('$val_t = '.str_replace("#",'$m->',$val).';');
                            $ft2.= "&".$var. "=". urlencode($val_t);
                        }
                    }else{
                        $ft2="";
                    }
                $a_s="<a href='index.php?option=com_xbank&task=".$links[$f]['task']."&".$ft2."' class='".(isset($links[$f]['class'])?$links[$f]['class'] : "" ) ."'>";
                $a_e = "</a>";
            }else{
                $a_s="";
                $a_e = "";
            }
            $html .= "<td border='1'>$a_s" . $m->$f . "$a_e</td>";
            if (in_array($f, $totals_array))
                $sum[$f] += $m->$f;
            }
        }
        $html .="</tr>";
    endforeach;

    if (count($totals_array) > 0) {
        $html .= "<tr>";
        if ($option['sno'] == true) {
            $html .="<th>Total</th>";
        }
        foreach ($fields as $f) {
            if (in_array($f, $totals_array)) {
                $html .= "<th align='left'>" . round($sum[$f],2) . "</th>";
            } else {
                $html .= "<td>&nbsp;</td>";
            }
        }
        $html .= "</tr>";
    }
    eval($tableFooterTemplate);
    $html .= "</tobdy></table>";
    $html .= $footerTemplate;

    if(isset($model->paginateHTML))
        $html .= $model->paginateHTML;


    if(isset($option['page']) and $option['page']==true){
        if(isset($option['page_var']))
            $pagevar=$option['page_var'];
        else
            $pagevar='page_start';

        $next=JRequest::getVar($pagevar,0)+1;
        $previous=JRequest::getVar($pagevar,1)-1;

        if($previous < 0 ) $previous=0;
        $nextURL=JURI::getInstance()->toString();
        $prevURL=JURI::getInstance()->toString();
        
        if(strpos($nextURL,$pagevar)===false) $nextURL .= "&$pagevar=0";
        if(strpos($prevURL,$pagevar)===false) $prevURL .= "&$pagevar=0";

        $nextURL=str_replace("$pagevar=".JRequest::getVar($pagevar,0),"$pagevar=".$next,$nextURL);

        $prevURL=str_replace("$pagevar=".JRequest::getVar($pagevar,0),"$pagevar=".$previous,$prevURL);

        $html .= "<table width='50%' align='center' style='font-size: 1.8em'><tr><td><a href='".$prevURL."'>Previous</a></td><td><a href='".$nextURL."'>Next</a></td></tr></table>";
    }

    return $html;
}

function paginateIt(&$model,$pageNumber,$onPage=50){
    
    $model->paginatHTML="";
}


function arrayToObject($array)
{
    // First we convert the array to a json string
    $json = json_encode($array);

    // The we convert the json string to a stdClass()
    $object = json_decode($json);

    return $object;
}

function objectToArray($object)
{
    // First we convert the object into a json string
    $json = json_encode($object);

    // Then we convert the json string to an array
    $array = json_decode($json, true);

    return $array;
}

function nextDate($date=null,$is_date=false){
    if($date==null) $date=getNow('Y-m-d');
    if($is_date==false)
        $date=inp($date);
    
    $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 DAY"));    
    return $date;
}