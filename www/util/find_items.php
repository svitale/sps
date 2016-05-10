<?php
include_once ('../include/lib.php');
$id_study = $_SESSION['id_study'];
if ((($_FILES["file"]["type"] == "application/vnd.ms-excel") || ($_FILES["file"]["type"] == "application/octet-stream")) && ($_FILES["file"]["size"] < 2000000)) {
	if ($_FILES["file"]["error"] > 0) {
		//    echo "Error: " . $_FILES["file"]["error"] . "<br />";
		echo "Error: " . $_FILES["file"]["type"] . "<br />";
	} else {
		//    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
		//    echo "Type: " . $_FILES["file"]["type"] . "<br />";
		//    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
		$tmpfile = $_FILES["file"]["tmp_name"];
	}
	$cmd = escapeshellcmd("xls2csv -q 0 -c; $tmpfile");
	$cmd.= " > $tmpfile.csv";
	@exec($cmd, $stdout, $errocode);
	//        unlink("$path/$xls_file");
	if ($errorcode > 0) return $errocode;
	$tmptable = mysql_query('create temporary table if not exists items_' . session_id() . ' like items');
	if (!$tmptable) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	mysql_query("alter table items_" . session_id() . " drop column id_uuid");
	$id_study = $_SESSION['id_study'];
	$loadfile = mysql_query("LOAD DATA INFILE '$tmpfile.csv' INTO TABLE items_" . session_id() . " FIELDS TERMINATED BY ';' ignore 1 lines (id_subject,id_visit,sample_type,shipment_type) set id_study = '$id_study'");
	if (!$loadfile) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} else {
		mysql_query("update items_" . session_id() . " set id_visit = 'V3Y0' where id_visit = '3'");
		mysql_query("update items_" . session_id() . " set id_visit = 'V5Y1' where id_visit = '5'");
		mysql_query("update items_" . session_id() . " set id_visit = 'V7Y2' where id_visit = '7'");
		mysql_query("update items_" . session_id() . " set id_visit = 'V9Y3' where id_visit = '9'");
		mysql_query("update items_" . session_id() . " set id_visit = 'V11Y4' where id_visit = '11'");
		mysql_query("update items_" . session_id() . " set id_visit = 'V13Y5' where id_visit = '13'");
		mysql_query("update items_" . session_id() . " set id_visit = 'V15Y6' where id_visit = '15'");
		if (isset($_SESSION['id_visit'])) {
			mysql_query("update items_" . session_id() . " set id_visit = '" . $_SESSION['id_visit'] . "' where id_visit is null or id_visit = ''");
		}
		if (isset($_SESSION['sample_type'])) {
			mysql_query("update items_" . session_id() . " set sample_type = '" . $_SESSION['sample_type'] . "' where sample_type is null or sample_type = ''");
		}
		if (isset($_SESSION['shipment_type'])) {
			mysql_query("update items_" . session_id() . " set shipment_type = '" . $_SESSION['shipment_type'] . "' where shipment_type is null or shipment_type = ''");
		}
	}
	$temp_session = session_id();
	$query = "select items_" . $temp_session . ".id_subject,items_" . $temp_session . ".id_subject as subs,items_" . $temp_session . ".id_visit,
	vwinventory.items_date_visit,items_" . $temp_session . ".sample_type,items_" . $temp_session . ".shipment_type,
	  vwinventory.items_id_uuid,
	  freezer,locations_subdiv1,locations_subdiv2,locations_subdiv3,locations_subdiv4,locations_subdiv5,items_destination,
	  items_id_subject from
	  items_" . $temp_session . "
	  left join vwinventory on
		(items_id_subject  = items_" . $temp_session . ".id_subject
		and items_id_visit = items_" . $temp_session . ".id_visit
		and items_sample_type = items_" . $temp_session . ".sample_type
		and items_shipment_type  = items_" . $temp_session . ".shipment_type)
	  where items_" . $temp_session . ".id_subject > 0;";
	$result = mysql_query($query);
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=locations.csv");
	echo "id_subject,id_visit,date_visit,sample_type,shipment_type,id_uuid,freezer,subdiv1,subdiv2,subdiv3,subdiv4,subdiv5,destination";
	echo "\n";
	for ($i = 0;$i < mysql_num_rows($result);$i++) {
		extract(mysql_fetch_array($result));
		echo "'$subs,$id_visit,$items_date_visit,$sample_type,$shipment_type,$items_id_uuid,$freezer,$locations_subdiv1,$locations_subdiv2,$locations_subdiv3," . num2chr($locations_subdiv4) . ",$locations_subdiv5,$items_destination";
		echo "\n";
	}
}
?>
