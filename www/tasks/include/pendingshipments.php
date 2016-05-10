<?php

//include_once("../../../include/db.php");

function operateScannedObject($table, $id) {
	if (isset($_POST['value'])) {
		$value = $_POST['value'];
		$sample_query = mysql_query("select sample_collos_id from batch_quality where id_barcode='$value' and sample_type!='box'") or die(mysql_error());
		$sample_id = mysql_fetch_row($sample_query);
		$box_query = mysql_query("select sample_collos_id from batch_quality where id_barcode='$value' and sample_type='box'") or die(mysql_error());
		$box_id = mysql_fetch_row($box_query);
		
		if ($sample_id == true){
			print "<script type='javascript'>";
			print 'window.open("https://collos.itmat.upenn.edu/samples/'.$sample_id[0].'/edit","_blank")';
			print "</script>";
		}
		elseif ($box_id == true) {
			print "<script type='javascript'>";
			print 'window.open("https://collos.itmat.upenn.edu/containers/'.$box_id[0].'","_blank")';
			print "</script>";
		}
		elseif ($sample_id == false){
			print $value." doesn't exist in the database!";
			break;
		}
	}
}
function pendingShipments() {
//$_SESSION['id_assay'] = array('TnI II','_BNPSTAT','MPO882010','proBNP','Cardi_PlGF','Cardi_PlGF','Anti-cTnI','Uric');
$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];

// construct date portion of query
if (strtotime($datestart) < strtotime($dateend)) {
	$date_query = " date_visit >= '$datestart' and date_visit <= '$dateend '";
} else {
	$date_query = " date_visit like '$datestart' ";
}
if (($_SESSION['id_study']) == '%') {
	$show_study = "id_study as 'Study',";
} else {
	$show_study = "";
}
$i = 1;
$returnArray=array();
$query = "select id,id_uuid as uuid,id_subject as 'Subject ID',id_visit as 'Visit #',sample_type as 'Type',date_visit as 'Visit Date',shipment_type as 'Shipment Type',date_ship as 'Ship Date',name_created,name_shipper,import_source from batch_quality where ";
$query .= " date_receipt is null and id_parent = 0 ";
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
$query .= 'and ' . $date_query;
$result = mysql_query($query);
while($row = mysql_fetch_assoc($result))
	{
		$row['print'] = '<input type="button" value="Print" onclick="printlabel('.$row['id'].',\'batch_quality\')">';
		array_push($returnArray, $row);
	}
header("Content-Type: application/json");
echo json_encode($returnArray);
}
?>
