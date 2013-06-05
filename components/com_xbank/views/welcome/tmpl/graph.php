<?php
$this->jq->useGraph();
//$data_url = "index.php?/mod_bhawanireports/report_cont/kitWiseGraphData";
$this->jq->getGraphObject('100%', '200', $data_url, 'test_chart');
if(isset($result))
    echo $result;
?> 


