<?php
$todays_date = date("Y-m-d"); 
if (!isset($_SESSION[id_instrument])) {
	$_SESSION['id_instrument'] = 'ArchLIS';
}
if (!isset($_SESSION[id_study])) {
	$_SESSION['id_study'] = '%';
}
if (isset($_SESSION['tmptable'])) {
	$table = $_SESSION['tmptable']; 
} else if (!isset($table)) {
	$table = 'results';
}

function dashBoard() {
	if (isset($_SESSION['tmptable'])) {
		$tmptable =  $_SESSION['tmptable'];
		$query = "select count(*) as num from ".$tmptable.";";
		if (mysql_num_rows(mysql_query($query)) > 0) {
			$result = mysql_query($query);
			while($row = mysql_fetch_assoc($result)) {
				echo "<div> ".$row['num']." records</div>";
				echo '<input type="button" onclick="importBatch(\''.$tmptable.'\')" value="import records">';
			}
		} else {
			echo "failed";
		}
	} else {
		if ($_SESSION['task'] == 'orders') {
			$task = 'orders';
		} else {
			$task = 'results';
		}
		$query = "select distinct(id_rungroup) from $task where timestamp > curdate()";
		if (mysql_num_rows(mysql_query($query)) > 0) {
			echo "<fieldset><legend>Recent imports</legend>";
			$result = mysql_query($query);
			while($row = mysql_fetch_assoc($result)) {
				echo "<div><a href='?task=$task&id_rungroup=".$row['id_rungroup']."'>".$row['id_rungroup']."</div>";
			}
			echo "</fieldset>";
		}
	}
}


function importBatch() {
	$tmptable = $_SESSION['tmptable'];
	if ($_SESSION['task'] == 'orders') {
		$referrer = "?task=orders&id_rungroup=".urlencode($_SESSION['id_rungroup']);
		$query = "insert into orders (id_mrn,id_acc,lab,id_rungroup,id_uuid,uuid_parent,id_barcode,id_subject,id_study,id_visit,date_visit,sample_type,id_assay,fulfilled,user,timestamp) (select id_mrn,id_acc,lab,id_rungroup,id_uuid,uuid_parent,id_barcode,id_subject,id_study,id_visit,date_visit,sample_type,id_assay,fulfilled,user,timestamp from ".$tmptable.");";
	} else {
		$referrer = "?task=results&id_rungroup=".urlencode($_SESSION['id_rungroup']);
		$query = "insert into results (id_barcode,id_uuid,id_subject,id_study,id_lab,id_instrument,id_assay,id_visit,id_rungroup,id_retest,value,units,datetime_assay,date_collection,date_visit,timestamp,qc,uqc,reviewed,calibrator,reagent,cleaner,`ignore`,share,notes,sample_type) (select id_barcode,id_uuid,id_subject,id_study,id_lab,id_instrument,id_assay,id_visit,id_rungroup,id_retest,value,units,datetime_assay,date_collection,date_visit,timestamp,qc,uqc,reviewed,calibrator,reagent,cleaner,`ignore`,share,notes,sample_type from ".$tmptable.");";
	}
	$result = mysql_query($query);
        if(!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        } else {
		$drop = mysql_query("drop table $tmptable;");
		if (!$drop) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	unset($_SESSION['tmptable']);
        }
dashBoard();
}

function makeSnapshot($input_array) {
	if (!count($input_array >  1)) {
		print "Error: No results in array";
		exit;
	}
	foreach ($input_array as $key => $row) {
		$id_study[$key] = $row['id_study'];
		$id_subject[$key] = $row['id_subject'];
		$id_visit[$key] = $row['id_visit'];
		$datetime_assay[$key] = $row['datetime_assay'];
	}
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
/*
	if (isset($_SESSION['check_dates'])) {
		$visitArray = array();
		$query = "select id,id_subject,id_visit,id_study,date_create from visits where ";
		$query .= retQueryFilter($_SESSION['params'],array('id_study','id_visit'));
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
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
	}
*/
//	$array_params = retArrayParamsById($_SESSION['params'], array('id_assay'));
//	if (count($visitArray)) {
	foreach ($input_array as $result) {
		if ($result['id_retest'] == 1) {
			$billable{$result['id']}= 1;
		} else if (!isset($final{$result['id_visit']}{$result['id_subject']}{$result['id_assay']})) {
			$final{$result['id_visit']}{$result['id_subject']}{$result['id_assay']} = 1;
			$billable{$result['id']}= 1;
		} else {
			$billable{$result['id']}= 0;
		}
	}
	$insert = "insert into snapshots (id_publish,id_subject,id_assay,id_visit,id_results,billable) values ";
	foreach ($input_array as $result ) {
		$insert .= "('$id_publish','".$result['id_subject']."','".$result['id_assay']."','".$result['id_visit']."','".$result['id']."','".$billable{$result['id']}."'),";
	}
	if (isset($insert)) {
		$insert = rtrim($insert,",");
		$insert .= ";";
		$create = mysql_query($insert);
		if (!$create) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	} else {
		echo "ERROR: No Matching Results";
		exit;
	}
	return $id_publish;
}

function operateScannedObject($table, $id) {
	if (isset($id)) {
		$type = type($id);
	} else {
		echo "Error: no id";
		exit;
	}
	$_SESSION['Detailid'] = $id;
	$_SESSION['Detailtype'] = $type;
	?>
<script type="text/javascript">
new TableOrderer('actioncontainer',{url : 'npc.php?action=data&format=json&type=resultsForId&id=<?php print $id ?>' , paginate:true, search:'top', pageCount:50, filter:'top'});
</script>
	<?php
}



function valueMarkup(&$row, $key) {
	global $table;
	if (isset($row['value']) and isset($row['reviewed']) and isset($row['approved']) and isset($row['ignore'])) {
		$row['value'] = "<p><div class=\"dp dp_ignore_".$row['ignore']."  dp_".$row['reviewed']."_".$row['approved']."\" id=\"result_".$row['id']."\" onclick=\"editResult(".$row['id'].",'".$table."')\">".$row['value']."</div>";
	}
	if (($row['reviewed']) == "1") {
		$reviewed = '"checked"';
	}
	if (($row['approved']) == "1") {
		$approved = '"checked"';
	}
	if (($row['ignore']) == "1") {
		$ignore = '"checked"';
	}
	$row['Ignore'] = "<input type='checkbox' id='rdp_0' '$ignore' onclick=checkChildren(this,'ignore')></input>";
	unset($row['ignore']);
	$row['Reviewed'] = "<input type='checkbox' id='rdp_0' '$reviewed' onclick=checkChildren(this,'reviewed')></input>";
	unset($row['reviewed']);
	$row['Approved'] = "<input type='checkbox' id='adp_0' '$approved' onclick=checkChildren(this,'share')></input>";
	unset($row['approved']);
}

function uniqArrayValues($array, $group_by) {
	$new_array = array();
	foreach ($array as $row) {
		if (!in_array($row[$group_by],$new_array)) {
			array_push($new_array,$row[$group_by]);
		}
	}
	return $new_array;
}

function groupArray($array, $group_by) {
	global $postFormat,$table;
	$returnArray = array();
	$keychain = array();
	$assays = array();
	$group_values = uniqArrayValues($array,$group_by);
	$i = 0;
	foreach ($group_values as $value) {
		$keychain[$value] = $i;
		$i++;
	}
	foreach  ($array as $row) {
		$key = $keychain{$row[$group_by]};
		$id_assay = $row['id_assay'] ." (". $row['units'].")";
		if (isset($returnArray[$key]['date_assay']) && $returnArray[$key]['date_assay'] != $row['date_assay']) {
			if (!is_array($returnArray[$key]['date_assay'])) {
				$returnArray[$key]['date_assay'] = array($returnArray[$key]['date_assay'],$row['date_assay']);
			} else if (!in_array($row['date_assay'],$returnArray[$key]['date_assay'])) {
				array_push($returnArray[$key]['date_assay'],$row['date_assay']);
			}
		} else {
			$returnArray[$key]['date_assay'] = $row['date_assay'];
		}
		if (isset($returnArray[$key]['id_instrument']) && $returnArray[$key]['id_instrument'] != $row['id_instrument']) {
			$returnArray[$key]['instrument'] = 'multi';
		} else {
			$returnArray[$key]['id_instrument'] = $row['id_instrument'];
		}
		if ($postFormat == 'json') {
			$row['value'] = "<p><div class=\"dp dp_".$row['reviewed']."_".$row['approved']." dp_ignore_".$row['ignore']."\" id=\"result_".$row['id']."\" onclick=\"editResult(".$row['id'].",'".$table."')\">".$row['value']."</div>";
		}
		if (isset($returnArray[$key][$id_assay])) {
			$returnArray[$key][$id_assay] = $returnArray[$key][$id_assay].  " |  " . $row['value'];
		} else {
			$returnArray[$key][$id_assay] = $row['value'];
		}
		$returnArray[$key]['id_subject'] = $row['id_subject'];
		$returnArray[$key]['id_study'] = $row['id_study'];
		$returnArray[$key]['id_barcode'] = $row['id_barcode'];
		if (!in_array($id_assay,$assays)) {
			array_push($assays,$id_assay);
		}
		if (!isset($returnArray[$key]['ignore']) or $returnArray[$key]['ignore'] == 0) {
			$returnArray[$key]['ignore'] = $row['ignore'];
		}
		if (!isset($returnArray[$key]['reviewed']) or $returnArray[$key]['reviewed'] == 0) {
			$returnArray[$key]['reviewed'] = $row['reviewed'];
		}
		if (!isset($returnArray[$key]['approved']) or $returnArray[$key]['approved'] == 0) {
			$returnArray[$key]['approved'] = $row['approved'];;
		}
	}
	$headers = array('id_subject'=>'NA','id_barcode'=>'NA','id_instrument'=>'NA','id_study'=>'NA','date_assay'=>'NA');
	$assays = array_fill_keys($assays,'NA');
	asort($assays);
	foreach ($keychain as $key) {
		if (is_array($returnArray[$key]['date_assay'])) {
			$returnArray[$key]['date_assay'] = implode($returnArray[$key]['date_assay'],'|');
		}
		$returnArray[$key] = array_merge($headers,$assays,$returnArray[$key]);
	}
	return $returnArray;
}

function returnResults($id_item) {
	$returnArray = array();
	$query = "select results.id as id_result,items.id as id_items,items.id_uuid as UUID,items.id_barcode as FWID,items.id_study as items_id_study,items.id_subject as items_id_subject,items.id_visit as items_id_visit,items.sample_type as items_sample_type,results.id_assay,results.id_barcode,results.value,results.units,results.datetime_assay,results.id_rungroup,results.qc,results.uqc from items,results where (items.id_barcode = results.id_barcode or items.id_barcode2 = results.id_barcode or left(items.id_uuid,8) = results.id_barcode) and items.id =  '".$id_item."'";
	$result = mysql_query($query);
	while($row = mysql_fetch_assoc($result)) {
		array_push($returnArray,$row);
	}
return $returnArray;
}


function data_array($type) {
	global $scanned,$table;
	if (isset($_SESSION['tmptable'])) {
		$returnArray = array();
		$table = $_SESSION['tmptable'];
		if ($_SESSION['task'] == 'orders') {
		$sql = "select * ";
		} else {
		$sql = "select $table.id_rungroup,$table.id_retest,$table.id_barcode,$table.uqc,$table.id_subject,$table.id_visit,$table.id_study,$table.id_instrument,$table.id_assay,$table.value";
		$sql .= ",$table.units,$table.date_visit,$table.datetime_assay ";
		}
		$sql .= "from $table";

		$result = mysql_query($sql);
		while($row = mysql_fetch_assoc($result)) {
			array_push($returnArray,$row);
		}
	} else {
        // very large arrays slow the browser too much
		if (isset($_SESSION['rowlimit']) and (isnum($_SESSION['rowlimit'])) and ($_SESSION['rowlimit'] > 0)) {
			$rowlimit = mysql_real_escape_string($_SESSION['rowlimit']);
		} else {
			$rowlimit = 2000;
		}
		if ($type == 'export' || $type == 'snapshot' ) {
			$rowlimit = 100000;
		}
		//in this task - data_array will return $table.  if format = json, these will be formatted with js functions to alter and confirm $table.  if called with format = snapshot, a snapshot will be generated
		if ((!isset($_SESSION['params']) || (count($_SESSION['params']) == 0)) && (!isset($_SESSION['id_rungroup']))) {
			return array(array('Message'=>'No parameters selected'));
			exit;
		}
		if (isset($_SESSION['id_rungroup'])) {
			$date_query = "id_rungroup like '".$_SESSION['id_rungroup']."' ";
		} else if (isset($_SESSION['datestart']) && isset($_SESSION['dateend'])) {
			$datestart = $_SESSION['datestart'];
			$dateend = $_SESSION['dateend'];
			$date_query = "datetime_assay > '$datestart 00:00:00' and datetime_assay < '$dateend 23:59:59'";
		} else if (isset($_SESSION['datestart'])) {
			$date_query = "datetime_assay like '$datestart %' ";
		}
		$returnArray = array();
		$query = "select $table.reviewed,$table.share as approved,$table.`ignore`,$table.id_rungroup,$table.id_retest,$table.id,$table.id_barcode,$table.id_subject,$table.id_visit,$table.id_study,$table.id_instrument,$table.id_assay,$table.value";
		$query .= ",$table.units,$table.date_visit,";
		if (isset($_SESSION['group_by'])) {
			$query .= "DATE_FORMAT($table.datetime_assay,'%m-%d-%Y') as date_assay,";
		}
		$query .= "$table.datetime_assay";
		if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
			$query .= ",visits.date_create";
		}
		$query .= " from $table ";
		if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
			$query .= " left join visits on ($table.id_subject = visits.id_subject and $table.id_visit = visits.id_visit )";
		}
		$query .= " where ";
		if (isset($scanned)) {
			$query .= "id_barcode = '".$scanned."' ";
		} else if (isset($_SESSION['id_rungroup'])) {
				$query .= "id_rungroup = '".$_SESSION['id_rungroup']." ' ";
		} else {
			$query .= retQueryFilter($_SESSION['params'],array('id_study','id_instrument','id_assay','id_visit'),$table) ;
			$query .= "and (units != 'RLU' and units != 'Abs.' and units != 'Abs' and units != 'mV' and  units != 'Au' and units !=  'pg/')  ";
				if (isset($date_query)) {
					$query .= " and ".$date_query;
				}
		}
		if ($type == 'export' || $type == 'snapshot' ) {
			$query .= " and `ignore` != 1 ";	
		} 
		if (isset($_SESSION['group_by']))  {
               		$query .= " order by ".$_SESSION['group_by'].",datetime_assay";
		} else {
               		$query .= " order by id_assay,id_barcode,datetime_assay";
		}
		$result = mysql_query($query);
		$numrows = mysql_num_rows(mysql_query($query));
		if ($numrows >= $rowlimit) {
  			$returnArray = array(array("matches" => $numrows,"max" => $rowlimit));
		} else {
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
			}
			if ($type == 'snapshot' && (count($returnArray) == 0)){
				return array(array('ERROR'=>'No matching results were returned.  Perhaps they have not been reviewed and approved?'));
				exit;
			}
		}
	}
	return $returnArray;
}




function returnSnapshot($id_publish) {
global $table;
$returnArray = array();
//$i = 1;
$query = "select $table.reviewed,$table.share as approved,$table.id_retest as retest,$table.id,$table.id_barcode,$table.id_subject,$table.id_visit,$table.id_study,$table.id_instrument,$table.id_assay,`$table`.`value` ,$table.units,$table.date_visit,$table.datetime_assay";
//$query = "select $table.reviewed,$table.share as approved,$table.id,$table.id_barcode,$table.id_subject,$table.id_visit,$table.id_study,$table.id_instrument,$table.id_assay,cast(`$table`.`value` as decimal(8,3)) as value,$table.units,$table.date_visit,$table.datetime_assay";
if (isset($_SESSION['check_dates']) && $_SESSION['check_dates'] == '1') {
		$query .= ",visits.date_create ";
}
		$query .= ",snapshots.billable ";
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
		if (isset($_SESSION['group_by']))  {
			$returnArray = groupArray($returnArray,'id_subject');
		}
		
return $returnArray;
}



function checkResult($id, $role, $value) {
global $table;
        $result_update = mysql_query("UPDATE `$table` set `$role` = '$value' where id  = '$id'");
        if (!$result_update) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
        $result = mysql_query("SELECT value,reviewed,share,`ignore` FROM `$table` WHERE `id` = '$id'");
        if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
        $row = mysql_fetch_row($result);
        $dp_format = "<div id=\"result_$id\" class='dp dp_ignore_$row[3] dp_$row[1]_$row[2]' onclick='editResult($id,\"$table\")'>$row[0]</div>";
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
if ($row->reviewed == "1") {
        $reviewed = "checked=\"checked\"";
        }
if ($row->share == "1") {
        $approved = "checked=\"checked\"";
        }
if ($row->ignore == "1") {
        $ignore = "checked=\"checked\"";
        }
        $reviewed = '<input type="checkbox" id="rdp_'.$j.'" '.$reviewed.' onclick="checkResult(this,\''.$id.'\',\'reviewed\')"></input>';
        $approved = '<input type="checkbox" id="adp_'.$j.'" '.$approved.' onclick="checkResult(this,\''.$id.'\',\'share\')"></input>';
        $ignore = '<input type="checkbox" id="idp_'.$j.'" '.$ignore.' onclick="checkResult(this,\''.$id.'\',\'ignore\')"></input>';


	echo "<div>Study: ".$row->id_study."</div>";
	echo "<div>Subject: ".$row->id_subject."</div>";
	echo "<div>Run Datetime: ".$row->datetime_assay."</div>";
	echo "<div>Assay: ".$row->id_assay."</div>";
	echo "<div>Reviewed: ".$reviewed."</div>";
	echo "<div>Approved: ".$approved."</div>";
	echo "<div>Ignore: ".$ignore."</div>";
	echo "<div>Assay: ".$row->id_instrument."</div>";
	if ($row->value == "") {
	$value = "-";
	} else {
	$value = $row->value;
	}
echo '<div>Value: <span id="value" background-color: lightgreen">'.$value;
        echo "<script type='text/javascript'>";
        echo "editResultValue(".$id.",'".$table."')";
	echo "</script>";
	echo "</span></div>";
	echo "<div>". $row->units."</div>";
$notes = $row->notes;
}
?>
<form onsubmit="return false;" id="myform">
	<h1></h1>
	<fieldset>
		<legend>Notes</legend>
		<textarea id="notes" name="notes" rows="8" cols="24" class="MB_focusable">
<?
echo $notes;
?>

</textarea>

	<p><input type="submit" onclick="updateResult('<?echo $id?>', {params:Form.serialize('myform') }); Modalbox.hide();" value="Submit Note"> 
	</fieldset>
	<br/>
</form>
	<p><input type="submit" onclick="Modalbox.hide(); return false" value="Close"> 
<?
	echo '</div>';
}
?>
