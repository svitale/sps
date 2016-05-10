<?php
function retReceipt($format='json') {
//$_SESSION['id_assay'] = array('TnI II','_BNPSTAT','MPO882010','proBNP','Cardi_PlGF','Cardi_PlGF','Anti-cTnI','Uric');
$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];

// construct date portion of query
if (strtotime($datestart) < strtotime($dateend)) {
	$date_query = " date_receipt >= '$datestart' and date_receipt <= '$dateend '";
} else {
	$date_query = " date_receipt like '$datestart' ";
}
		if (($_SESSION['id_study']) == '%') {
$show_study = "id_study as 'Study',";
} else {
$show_study = "";
}
$i = 1;
$returnArray=array();
if ($format == 'csv') {
$fields = array("id_uuid","id_subject","id_visit","sample_type","shipment_type","date_receipt","date_visit","quality","quant_init","status","name_created","name_shipper","import_source");
$query = "select id_uuid as uuid,id_subject as 'Subject ID',id_visit as 'Visit #',sample_type as 'Type',shipment_type as 'Shipment Type',date_receipt as 'Receipt Date',date_visit as 'Visit Date',quality,quant_init,status,name_created,name_shipper,import_source from batch_quality where ";
} else {
$query = "select count(*) as 'Quantity',id_subject as 'Subject ID',id_visit as 'Visit #',sample_type as 'Type',shipment_type as 'Shipment Type',date_receipt as 'Receipt Date',date_visit as 'Visit Date',quality,quant_init,status,name_created,name_shipper,import_source from batch_quality where" ;
}
	$query .= $date_query;
	$query .= " and id_parent = 0 ";
	if (isset($_SESSION['id_study'])) {
		$id_study = $_SESSION['id_study'];
		$query .= " and id_study = '$id_study' ";
	}
	if (isset($_SESSION['id_subject'])) {
		$id_subject = $_SESSION['id_subject'];
		$query .= " and id_subject = '$id_subject' ";
	}
	if (isset($_SESSION['id_visit'])) {
		$id_visit = $_SESSION['id_visit'];
		$query .= " and id_visit = '$id_visit' ";
	}
	if (isset($_SESSION['sample_type'])) {
		$sample_type = $_SESSION['sample_type'];
		$query .= " and sample_type = '$sample_type' ";
	}
	if (isset($_SESSION['shipment_type'])) {
		$shipment_type = $_SESSION['shipment_type'];
		$query .= " and shipment_type = '$shipment_type' ";
	}
	if ($format != 'csv') {
                $query .= " group by id_subject,sample_type,id_visit ";
	}
       $query .= " order by date_receipt,id_subject;";
$j = 0;
$result = mysql_query($query);
while($row = mysql_fetch_assoc($result))
{
  array_push($returnArray, $row);
}

mysql_close();
if ($format == 'csv') {
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=receipt".$datestart."to".$dateend.".csv");
echo implode(",", $fields) . "\n";
//$fp = fopen('file.csv', 'w');
foreach ($returnArray as $line) {
echo implode(",", $line) . "\n";
}
//echo $fp;
//fclose($fp);
} else {
header("Content-Type: application/json");
echo json_encode($returnArray);
}
}
?>
