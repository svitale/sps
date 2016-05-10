<?php
if (isset($_POST['uqc'])) {
	$uqc = $_POST['uqc'];
}
if (isset($_GET['uqc'])) {
	$uqc = $_GET['uqc'];
}
	//$uqc = 'standard';
	//$generate_curve = true;
$table = 'results_raw';
$todays_date = date("Y-m-d"); 
if (!isset($_SESSION['datestart'])) {
$_SESSION['datestart'] = $todays_date;
}         
if (!isset($_SESSION['dateend'])) {
$_SESSION['dateend'] = $todays_date;
}
if (!isset($_SESSION['id_instrument'])) {
$_SESSION['id_instrument'] = 'ArchLIS';
}
if (!isset($_SESSION['id_study'])) {
$_SESSION['id_study'] = '%';
}

$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];
if (!isset($table)) {
$table = 'results';
}

function makeSnapshot($input_array) {
if (!count($input_array >  1)) {
exit;
}
$id_study = array();
$id_subject = array();
$id_visit = array();
$datetime_assay = array();
	foreach ($input_array as $key => $row) {
                $id_study[$key] = $row['id_study'];
                $id_subject[$key] = $row['id_subject'];
                $id_visit[$key] = $row['id_visit'];
                $datetime_assay[$key] = $row['datetime_assay'];
	}
       array_multisort($id_study, SORT_ASC, $id_subject, SORT_ASC, $id_visit, SORT_ASC,  $datetime_assay, SORT_DESC, $input_array);
	$returnArray = $input_array;
	$publish = mysql_query("insert into publish (creator) values ('".$_SESSION['username']."')");
	if (!$publish) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$id_publish = mysql_insert_id();
	$delete = mysql_query("delete from snapshots where id_publish = '$id_publish'");
	if (!$delete) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
	global $globalTitle;
	$globalTitle = '_snapshot_'.$id_publish;
	$visitArray = array();
	$query = "select id,id_subject,id_visit,id_study,date_create from visits where ";
	$query .= retQueryFilter($_SESSION['params'],array('id_study'));
	$result = mysql_query($query);
	while($visit = mysql_fetch_assoc($result)) {
	$match = 0;
	foreach ($input_array as $assay ) {
		if ($assay['id_subject'] == $visit['id_subject'] && $assay['id_visit'] == $visit['id_visit'] ) {
		$visit[$assay['id_assay']] = $assay['id'];
		$match = 1;
		}
		}
	if ($match == 1) {
		  array_push($visitArray, $visit);
	}
	}
$array_params = retArrayParams($_SESSION['params'], array('id_assay'));
$insert = "insert into snapshots (id_publish,id_subject,id_assay,id_visit,id_results) values ";
foreach ($visitArray as $visit ) {
        foreach ($array_params['id_assay'] as $id_assay ) {
                if (isset($visit[$id_assay])) {
                $insert .= "('$id_publish','".$visit['id_subject']."','$id_assay','".$visit['id_visit']."','".$visit[$id_assay]."'),";
                }
        }
}
$insert = rtrim($insert,",");
$insert .= ";";
$create = mysql_query($insert);
if (!$create) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }



	return $id_publish;
//	return $visitArray;

function snapshotMarkup(&$visit, $key, $input_array) {

		}
}


function valueMarkup(&$row, $key) {
global $table;
	$row['value'] = "<p><div class=\"dp dp_".$row['reviewed']."_".$row['approved']."\" id=\"result_".$row['id']."\" onclick=\"editResult(".$row['id'].",'".$table."')\">".$row['value']."</div>";
 if (($row['reviewed']) == "1") {
        $reviewed = 'checked="yes"';
} else {
        $reviewed = '';
}
 if (($row['approved']) == "1") {
	$approved = 'checked="yes"';
        } else {
	$approved = '';
}
 if (($row['ignore']) == "1") {
        $ignore = 'checked="yes"';
        } else {
        $ignore = '';

}
        $row['Reviewed'] = "<input type='checkbox' id='rdp_0' $reviewed onclick=checkChildren(this,'reviewed')></input>";
        $row['Approved'] = "<input type='checkbox' id='adp_0' $approved onclick=checkChildren(this,'share')></input>";
        $row['ignore'] = "<input type='checkbox' id='idp_0' $ignore onclick=checkChildren(this,'`ignore`')></input>";
}

function uniqArrayValues($array, $group_by) {
$new_array = array();
	foreach ($array as $row) {
		if (is_array($group_by))  {
		} else {
			if (!in_array($row[$group_by],$new_array)) {
				array_push($new_array,$row[$group_by]);
			}
		}
	}
return $new_array;
}

function groupArray($array, $group_by) {
	$array_values = uniqArrayValues($array,$group_by);
			$grouped_array = array();
		foreach  ($array_values as $value) {
			$j=0;	
			$grouped_array[$value] = array();
			$approved = 'checked';
			$reviewed = 'checked';
			unset($row);
			foreach  ($array as $row) {
				if ($row[$group_by] ==  $value) {
						$id_assay = $row['id_assay'];
						$row[$id_assay] = $row['value'];
						$row['units_'.$row['id_assay']] = $row['units'];
						$row['date_'.$row['id_assay']] = $row['datetime_assay'];
						if (($reviewed == 'checked') && $row['reviewed'] == 1) {
							$reviewed =  'checked';
						} else {
							$reviewed =  '';
						}

						if (($approved == 'checked') && $row['approved'] == 1) {
							$approved =  'checked';
						} else {
							$approved =  '';
						}

						unset($row['value']);
						unset($row['id_assay']);
						unset($row['id']);
						unset($row['datetime_assay']);
					        $row['Reviewed'] = "<input type='checkbox' id='rdp_$j' '$reviewed' onclick=checkChildren(this,'reviewed')></input>";
					        $row['Approved'] = "<input type='checkbox' id='adp_$j' '$approved' onclick=checkChildren(this,'share')></input>";
						$grouped_array[$value] = array_merge($grouped_array[$value],$row);
				}
			}
			$j++;
			unset($share);
			unset($reviewed);
		}
$returnArray = array();
	foreach ($grouped_array as $match) {
		array_push($returnArray,$match);
	}
return $returnArray;
}

function data_array($type) {
global $sps;
global $uqc;
global $table;
//in this task - data_array will return $table.  if format = json, these will be formatted with js functions to alter and confirm $table.  if called with format = snapshot, a snapshot will be generated

/*
if (!isset($_SESSION['params']) || (count($_SESSION['params']) == 0)) {
return array();
exit;
}
*/

if (isset($_SESSION['id_rungroup'])) {
	$date_query = "id_rungroup like '".$_SESSION['id_rungroup']."' ";
} else {
$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];

// construct date portion of query
if (strtotime($datestart) < strtotime($dateend)) {
	$date_query = "datetime_assay > '$datestart 00:00:00' and datetime_assay < '$dateend 23:59:59'";
} else {
	$date_query = "datetime_assay like '$datestart %' ";
}
}
$filters = $sps->filters;
$id_instrument = $filters['id_instrument'];

$returnArray = array();
//$i = 1;
$query = "select $table.id results_raw_id,$table.dilution,$table.reviewed,$table.id_uuid,$table.id_barcode,$table.share as approved,$table.`ignore`,$table.id_rungroup as run,$table.id,$table.barcode_source,$table.position_plate,$table.layout_plate,$table.id_visit,$table.id_visit visit,$table.sample_type,$table.shipment_type,$table.id_study,$table.id_instrument,$table.id_assay,$table.value,$table.value_1,$table.value_2,$table.value_measured,$table.cv,$table.wavelength,$table.id_subject";
//		$query .= ",value_measured as 'raw value',position_plate,$table.uqc";
$query .= ",$table.units,$table.date_visit,$table.datetime_assay,$table.name_plate";
if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
		$query .= ",visits.date_create,datetime_assay";
}
		$query .= " from $table ";
if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
		$query .= " left join visits on ($table.id_subject = visits.id_subject and $table.id_visit = visits.id_visit )";
}
		$query .= " where ";
                $query .= " id_instrument ='$id_instrument' ";
//		$query .= retQueryFilter($_SESSION['params'],array('id_study','id_instrument','id_assay','id_visit'),$table);
		$query .= "and (units != 'RLU' and units != 'Abs.' and units != 'Abs' and units != 'mV' and  units != 'Au' and units !=  'pg/')  ";
//		$query .= "and `ignore` != '1' and ";
		$query .= "and ".$date_query;
		if ($type == 'snapshot'){
		$query .= " and reviewed = 1 and share = 1 ";	
		}
if (isset($uqc)) {
		$query .= " and uqc = '$uqc' ";
}
                $query .= " order by id_subject";
//}
//$j = 0;
$result = mysql_query($query);
while($row = mysql_fetch_assoc($result)) {
if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
	if ($row['date_create'] != $row['date_visit']) {
		$row['mismatch'] = '1';
	} else {
		$row['mismatch'] = '0';
	}	
}
	if ($row['value'] == "") {
	$row['value'] = "-";
	}
	array_push($returnArray, $row);
}


	if (isset($_SESSION['group_by']) && $type != 'snapshot')  {
	$returnArray = groupArray($returnArray,$_SESSION['group_by']);
	}

if ($type != 'snapshot' && $type != 'export') {
	array_walk($returnArray,'valueMarkup');


	$trimmedArray = array();
	$uglyCols = array('reviewed','approved','id_visit','units','id');
	foreach($returnArray as $row) {
		foreach($uglyCols as $ugly) {
			unset($row[$ugly]);
		}
		array_push($trimmedArray,$row);
	}
	$returnArray = $trimmedArray;
}

return $returnArray;
}




function returnSnapshot($id_publish) {
$returnArray = array();
//$i = 1;
$query = "select $table.reviewed,$table.share as approved,$table.id,$table.id_barcode,$table.id_subject,$table.id_visit,$table.id_study,$table.id_instrument,$table.id_assay,`$table`.`value` ,$table.units,$table.date_visit,$table.datetime_assay";
//$query = "select $table.reviewed,$table.share as approved,$table.id,$table.id_barcode,$table.id_subject,$table.id_visit,$table.id_study,$table.id_instrument,$table.id_assay,cast(`$table`.`value` as decimal(8,3)) as value,$table.units,$table.date_visit,$table.datetime_assay";
if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
		$query .= ",visits.date_create ";
}
		$query .= " from snapshots ";
		$query .= " left join $table on (snapshots.id_$table = $table.id) left join visits on ($table.id_subject = visits.id_subject and $table.id_visit = visits.id_visit )";
		$query .= " where id_publish = '$id_publish' ";
                $query .= " order by id_subject,id_visit,id_assay,datetime_assay;";
//}
//$j = 0;
$result = mysql_query($query);
while($row = mysql_fetch_assoc($result)) {
  array_push($returnArray, $row);
}
return $returnArray;
}



function checkResult($id, $role, $value) {
global $table;
        $result_update = mysql_query("UPDATE `$table` set $role = '$value' where id  = '$id'");
        if (!$result_update) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
        $result = mysql_query("SELECT value,reviewed,share FROM `$table` WHERE `id` = '$id'");
        if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
        $row = mysql_fetch_row($result);
        $dp_format = "<div id=\"result_$id\" class='dp dp_$row[1]_$row[2]' onclick='editResult($id,'$table')'>$row[0]</div>";
        return $dp_format;
        mysql_free_result($result);
}



function resultDetail($id,$table) {
	echo '<div>';
	$result = mysql_query("SELECT * FROM `$table` WHERE `id` = '$id'");
	if (!$result) {
	echo 'Could not run query: ' . mysql_error();
       exit;
        }
	$j = 0;
	while ($row = mysql_fetch_object($result)) {
	echo "<div>Study: ".$row->id_study."</div>";
	echo "<div>Subject: ".$row->id_subject."</div>";
	echo "<div>Run Datetime: ".$row->datetime_assay."</div>";
	echo "<div>Assay: ".$row->id_assay."</div>";
	echo "<div>Instrument: ".$row->id_instrument."</div>";
	if ($row->value == "") {
	$value = "-";
	} else {
	$value = $row->value;
	}
echo '<div id="value" background-color: lightgreen">'.$value;
        echo "<script type='text/javascript'>";
        echo "editResultValue(".$id.",'".$table."')";
	echo "</script>";
	echo "</div>";
	echo "<div>". $row->units."</div>";
$notes = $row->notes;
}
?>
<form onsubmit="return false;" id="myform">
	<h1></h1>
	<fieldset>
		<legend>Notes</legend>
<input type="hidden" name="table"  value="results_raw">
		<textarea id="notes" name="notes" rows="8" cols="24" class="MB_focusable">
<?php
echo $notes;
?>

</textarea>

	<p><input type="submit" onclick="updateResultsNote('<?php echo $id?>','<?php echo $table?>'); Modalbox.hide();" value="Submit Note"> 
	</fieldset>
	<br/>
</form>
	<p><input type="submit" onclick="Modalbox.hide(); return false" value="Close"> 
<?php
	echo '</div>';
}
?>
