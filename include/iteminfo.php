<?php
include_once('config.php');
function retItem($id) {
$type = type($id);
	if ($type == 'tube') {
		$result = mysql_query("SELECT *  FROM `items` left join (visits) on (`items`.`id_visit`=`visits`.`id_visit` and `items`.`id_subject`=`visits`.`id_subject` and `items`.`id_study`=`visits`.`id_study`) left join (locations) on  (items.id = locations.id_item) WHERE items.id =  '$id'");
	} else if ($type == 'box') {
		$result = mysql_query("SELECT *  FROM `items` left join (locations) on  (items.id = locations.id_item) WHERE items.id =  '$id'");
	} else {	
			echo 'item not recognized';
			exit;
	}
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
	 	}
		//*
	while ($row = mysql_fetch_object($result)) {
	$id_uuid =  $row->id_uuid;
	$id_subject =  $row->id_subject;
	$sample_type = $row->sample_type;
	$id_visit = $row->id_visit;
	$id_study = $row->id_study;
	$date_visit = $row->date_visit;
	$date_create = $row->date_create;
	$shipment_type = $row->shipment_type;
	$destination = $row->destination;
	$id_container = $row->id_container;
	}
	mysql_free_result($result);
	echo "<div class=\"wrapper\">";
	echo "<div>study: ".$id_study."</div>";
	echo "<div>1d barcode: ". substr($id_uuid,0,8)."</div>";
	echo "<div>visit:".$id_visit."</div>";
	echo "<div>container ".$id_container."</div>";
	echo "<div>".$id_subject."</div>";
	echo "<div>".$sample_type."</div>";
	echo "<div>".$shipment_type."</div>";
	echo "<div>visit: ".$date_visit."</div>";
	echo "<div>created: ".$date_create."</div>";
	echo "<div>".$destination."</div>";
	echo "<div></div>";
	echo "</div>";
	//* get locations



	$result = mysql_query("SELECT * FROM `locations` WHERE id_item =  '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	 	}
	while ($row = mysql_fetch_object($result)) {
	$subdiv4 = $row->subdiv4;
	$subdiv5 = $row->subdiv5;
	$id_container = $row->id_container;
	$date_moved = $row->date_moved;
	if ($row->date_moved == '') {
	echo "<div class=\"wrapper\" style=\"width:100px\">";
        $wrapper =  "<div class=\"wrapper\" style=\"width:".(500 + (100 * (count($assays))) )."px\">";
	echo "<div class=\"left_column\">Box: ".substr(retUuid($id_container),0,8)."</div>";
	echo "<div class=\"left_column\">Position: ".num2chr($subdiv4) . $subdiv5;
	echo "</div>";
	echo "<div class=\"left_column\"><input type=\"button\" value=\"clear\" onclick=\"cleardestination('".$id."')\">";
	echo "</div>";
	//echo "<div class=\"left_column\">".retFieldcomment('date_moved','locations')."</div><div class=\"content\">".$date_moved."</div>";
	}
	echo "</div>";
	}
	mysql_free_result($result);



	$result = mysql_query("SELECT * FROM `batch_quality` WHERE id_parent =  '$id_uuid'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	 	}
	while ($row = mysql_fetch_object($result)) {
	echo "<div class=\"wrapper\" style=\"width:100px\">";
	echo "<div class=\"left_column\">aliquot ".$row->id_uuid;
	echo "<input type=\"button\" value=\"print aliquot label\" onclick=\"printlabel('".$row->id_uuid."','batch_quality')\">";
	echo "</div>";
	echo "</div>";
	}
	mysql_free_result($result);

	//*buttons
	//*end of form
	}
	echo "<div class=\"wrapper\">";
	echo "<div class=\"left_column\">";
	echo "<input type=\"button\" value=\"print new label\" onclick=\"printlabel('".$id."','items')\">";
	echo "<input type=\"button\" value=\"delete\" onclick=\"remitem('".$id."')\">";
	echo "<input type=\"button\" value=\"Alq\" onclick=\"alqid('$id','items','1')\">";
	echo "</div>";
	echo "</div>";


?>
