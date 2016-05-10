<?php
//* color coded view of rack contents
function explodeid($id) {
	$idarray = array();
	$query = "SELECT items.id,items.type FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item` ) WHERE `id_container` = '$id' and locations.date_moved is null";
		$query .= ";";
	$result = mysql_query($query);
	if (!$result) {
	echo 'Could not run query: ' . mysql_error();
	exit;
	}
	for($i=0; $i<mysql_num_rows($result); $i++) {
	extract(mysql_fetch_array($result));
	
	array_push($idarray,array($id,$type));
	}
	return $idarray;
}


function manifest($id) {
$contentArray = array();
$idArray = array();
$type = type($id);
if ($type == 'rack') {
	$contents = (explodeid($id));
	foreach( $contents as $content) {
        	if ($content[1] == 'box') {
               		array_push($idArray,$content[0]);
        	}
	}
} else if ($type == 'box') {
	array_push($idArray,$id);
}
$j = 0;
while ($j < count($idArray)) {
	$contentArray = array_merge($contentArray,pullBox($idArray[$j]));
	$j++;
}
return $contentArray;
}

function batchmanifest() {
	$return = array();
	$postUuid = $_SESSION['batchuuid'];
	$query = "SELECT id_barcode,id_study,id_subject,sample_type,sample_name,treatment,collection_time,sample_identifier,sample_collos_id FROM `batch_quality` WHERE `id_batch` = '$postUuid' order by id";
	$result = mysql_query($query);
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
	exit;
	}
	while ($row = mysql_fetch_array($result)) {
	array_push($return,array('Barcode'=>$row['id_barcode'],'Study'=>$row['id_study'],'Subject id'=>$row['id_subject'],'Sample Type'=>$row['sample_type'],'Sample Name'=>$row['sample_name'],'Treatment'=>$row['treatment'],'Collection Time'=>$row['collection_time'],'Sample identifier'=>$row['sample_identifier'],'Sample id'=>$row['sample_collos_id']));
	}
	return $return;
}

function pullBox($boxid) {
$contentArray = array();
$box_uuid = retUuid($boxid);
$result = mysql_query("SELECT id_study as Study,items.id_uuid as \"2D Barcode\",left(id_uuid,8) as \"1D Barcode\",id_barcode as \"Alt barcode\",items.id_subject as \"Pt ID\",items.id_visit as \"Visit\",items.sample_type as \"Sample Type\",items.shipment_type as \"Shipment Type\",quant_init as \"Vol\",id_container,locations.subdiv4,locations.subdiv5,date_collection,date_visit FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item` ) WHERE `id_container` = '$boxid' and locations.date_moved is null order by locations.subdiv3,locations.subdiv4,locations.subdiv5");
//item.quant_init,items.shipment_type,items.sample_type,items.id_visit,items.id_subject,items.date_visit,date_collection,locations.subdiv4,locations.subdiv5 FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item` ) WHERE `id_container` = '$boxid' and locations.date_moved is null");
if (!$result) {
echo 'Could not run query: ' . mysql_error();
exit;
}
while($row = mysql_fetch_assoc($result)) {
if ($row['date_visit'] > '0000-00-00') {
$row['date'] = $row['date_visit'];
} else if ($row['date_collection'] > '0000-00-00') {
$row['date'] = $row['date_collection'];
} else if (isset($row['id_subject']) && isset($row['id_subject'])) {
retVisitDate($row['id_subject'],$row['id_visit']);
} else  {
$row['date'] = '';
}
$row['box_uuid'] = $box_uuid;
$row['Box 1D'] = substr($box_uuid,0,8);
$row['Row'] = num2chr($row['subdiv4']);
$row['Column'] = $row['subdiv5'];
unset($row['id_container']);
unset($row['date_collection']);
unset($row['date_visit']);
#unset($row['box_uuid']);
unset($row['subdiv4']);
unset($row['subdiv5']);
//unset($date);
array_push($contentArray,$row);
}
$returnArray = $contentArray;
return $returnArray;
mysql_close();
}


