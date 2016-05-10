<?php
/**
 * TODO: update how importBatch() handles the 'id' field when importing records, will have issues when two people try to upload at the same time
*/

$todays_date = date("Y-m-d");
if (!isset($_SESSION['datestart'])) {
	$_SESSION['datestart'] = $todays_date;
}
if (!isset($_SESSION['dateend'])) {
	$_SESSION['dateend'] = $todays_date;
}
if (!isset($_SESSION['id_study'])) {
	$_SESSION['id_study'] = 'CRIC';
}
if (isset($_SESSION['datestart'])) {
	$datestart = $_SESSION['datestart'];
}
if (isset($_SESSION['dateend'])) {
	$dateend = $_SESSION['dateend'];
}

/**
 * If the id_uuid column for the given table is blank, update it with a new uuid
 * 
 * @param string $table
 */
function genUuids($table) {
	$query = "select id from `$table` where id_uuid = '';";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result)) {
		$update = mysql_query("update `$table` set id_uuid = '" . new_uuid() . "' where id = $row[id]");
		if (!$update) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}

function alqbatch($id_subject) {
	// look up a family of specimin types and add the samples to the quality db
	if ($_SESSION['task'] == 'crf-pending') {
		$batchuuid = '';
	} else {
		$batchuuid = $_SESSION['batchuuid'];
	}
	if ($id_subject == 'all') {
		$tubequery = mysql_query("SELECT sample_type,id,family,id_visit,id_subject FROM `batch_quality` WHERE id_batch = '$batchuuid' group by sample_type,family,id_visit,id_subject");
	} else {
		$tubequery = mysql_query("SELECT sample_type,id,family,id_visit,id_subject FROM `batch_quality` WHERE id_subject  = '$id_subject' and id_batch = '$batchuuid' group by sample_type,family,id_visit,id_subject");
	}
	if (!$tubequery) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	while ($tube = mysql_fetch_array($tubequery)) {
		$quantity = '0';
		$id_subject = $tube['id_subject'];
		$sample_type = $tube['sample_type'];
		if (strlen($tube['family']) > 0) {
			$family = $tube['family'];
		} else {
			$family = "none";
		}
		$id_visit = $tube['id_visit'];
		$id_parent = $tube['id'];
		if (isset($_SESSION['quantity'] {
			$id_subject
		} {
			$sample_type
		} {
			$id_visit
		} {
			$family
		})) {
			$quantity = $_SESSION['quantity'] {
				$id_subject
			} {
				$sample_type
			} {
				$id_visit
			} {
				$family
			};
		} else {
			$crfquery = mysql_query("SELECT * FROM `crf` WHERE `family` = '$family' and sample_type = '$sample_type' limit 1");
			if (!$crfquery) {
				echo 'Could not run query: ' . mysql_error();
				exit;
			}
			while ($crf = mysql_fetch_array($crfquery)) {
				$quantity = $crf['quantity'];
			}
		}
		if ($quantity > 0) {
			aliquot($id_parent, 'batch_quality', $quantity);
			$_SESSION['quantity'] {
				$id_subject
			} {
				$sample_type
			} {
				$id_visit
			} {
				$family
			} = 0;
		}
	}
}

/**
 * Display the items in the current batch and allow the user to update or print.
 * 
 * If $_SESSION[batchuuid] is set, display the items in the batch and allow the
 * 		user to print and aliquot the samples
 * If $_SESSION[batchuuid] is not set but the table tmp_crf_[sessionid] exists,
 *		display the items in the table and allow the user to import them to the
 *		batch_quality table
 */
function dashBoard() {
        global $study,$id_study;
	if (isset($_SESSION['batchuuid'])) {
		$batchUuid = $_SESSION['batchuuid'];
		$query = "select count(*) as num,sample_type,shipment_type,id_visit from batch_quality where id_batch = \"$batchUuid\" group by sample_type,shipment_type,id_visit;";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<div>" . $row['num'] . " " . $row['id_visit'] . " " . $row['shipment_type'] . " " . $row['sample_type'] . "</div>";
		}
		if (mysql_num_rows(mysql_query($query)) > 0) {
			if (isset($_SESSION['subject_array'])) {
				echo "<input type=\"button\" value=\"Aliquot All\" onclick=\"alqbatch('all')\">";
				echo "<input type=\"button\" value=\"Print All\" onclick=\"printalqs('all')\">";
			} else {
				echo '<input type="button" onclick="printBatch(\'' . $batchUuid . '\')" value="print all">';
				echo "<input type='button' onclick=\"(window.location.href='npc.php?action=data&format=xls&type=batchmanifest')\" value='create manifest'>";
			}
		}
	} else {
		$tmptable = 'tmp_' . $_SESSION['task'] . '_' . session_id();
		$query = "select count(*) as num,sample_type,shipment_type,id_visit from `$tmptable` group by sample_type,shipment_type,id_visit order by num desc;";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<div>" . $row['num'] . " " . $row['id_visit'] . " " . $row['shipment_type'] . " " . $row['sample_type'] . "</div>";
		}
		echo '<input type="button" onclick="importBatch(\'' . $tmptable . '\')" value="import records">';
	}
}

/**
 * Move create a uuid for the current batch and move samples from the table tmp_crf_[sessionid] to batch_quality
 * 
 * @param bool $updateDashboard - Display the dashboard after the samples have been moved
 */
function importBatch($updateDashboard = true) {
	$batchUuid = new_uuid();
	$_SESSION['batchuuid'] = $batchUuid;
	$tmptable = 'tmp_' . $_SESSION['task'] . '_' . session_id();
	
	$update = mysql_query("update `$tmptable` set id_batch = '$batchUuid';");
	if (!$update) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}

	$droppk = mysql_query("ALTER TABLE  `$tmptable` DROP PRIMARY KEY, ADD INDEX (  `id`)");
	if (!$droppk) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	
	$update = mysql_query("update `$tmptable` set id = id + (select max(id) from batch_quality);");
	if (!$update) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}

	$query = "insert into batch_quality select * from `$tmptable`";
	$result = mysql_query($query);
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		echo "insert into batch_quality select * from `$tmptable`";
		exit;
	} else {
        //  create a temporary cohort list containing all of the subject ids for studies that allow autoincremnting subject ids.  then add them to the real cohort list
        $tmpcohort_table = $tmptable.'_cohort';
        $tmpcohort_sql = "create temporary table `$tmpcohort_table`";
        $tmpcohort_sql .= " as select `$tmptable`.id_subject,`$tmptable`.id_study from `$tmptable`";
        $tmpcohort_sql .= " left join cohort on `$tmptable`.id_subject = cohort.id_subject";
        $tmpcohort_sql .= " and `$tmptable`.id_study = cohort.id_study";
        $tmpcohort_sql .= " left join studies on `$tmptable`.id_study = studies.id_study";
        $tmpcohort_sql .= " where cohort.id is null and studies.autoassign_cohort = 1";
        $tmpcohort_sql .= " group by `$tmptable`.id_subject,`$tmptable`.id_study;";
        $tmpcohort_result = mysql_query($tmpcohort_sql);
        if (!$tmpcohort_result) {
            'Could not run query: ' . mysql_error();
            exit;
        }
        $tmpcohort_num_rows = mysql_affected_rows();
        if ($tmpcohort_num_rows > 0) {
            $cohort_sql = "insert into cohort (id_subject,id_study) (select id_subject,id_study from `$tmpcohort_table`)";
            $cohort_result = mysql_query($cohort_sql);
            if (!$cohort_result) {
                'Could not run query: ' . mysql_error();
                exit;
            }
            $cohort_num_rows = mysql_affected_rows();
            if ($cohort_num_rows < 1) {
                'Could not add subjects to cohort list';
                exit;
            }
        }
	}
	if($updateDashboard) dashBoard();
}

/**
 * Return the items in the current batch (b_q or tmp_crf_[sessionid]) in an array.
 * !!!If the current batch is in tmp_crf_[sessionid], the items are assigned uuids.
 *
 * @return array the items in the b_q table with the given batch uuid or the items in the table tmp_crf_[sessionid]
 */
function data_array() {
        global $sps;
        if(array_key_exists('create_by_encounter',$sps->task_behavior)) {
            $by = 'id_encounter';
        } else {
            $by = 'id_subject';
        }
	if (isset($_SESSION['batchuuid'])) {
		$batchUuid = $_SESSION['batchuuid'];
		// construct date portion of query
		$returnArray = array();
		$query = "select id_study,$by,sample_name,id_uuid,id_barcode,sample_type,treatment,collection_time,sample_identifier,sample_collos_id from batch_quality where id_batch = '$batchUuid';";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			array_push($returnArray, $row);
		}
	} else {
		$tmptable = 'tmp_' . $_SESSION['task'] . '_' . session_id();
		genUuids($tmptable);
		// construct date portion of query
		$returnArray = array();
		$query = "select id_study,$by,sample_name,id_uuid,id_barcode,sample_type,treatment,collection_time,sample_identifier,sample_collos_id from `$tmptable`";
		//$query = "select * from ".$tmptable.";";
		$result = mysql_query($query);
		while ($row = mysql_fetch_assoc($result)) {
			array_push($returnArray, $row);
		}
		mysql_close();
	}
	return $returnArray;
}

function showcol($field, $val, $width, $changable) {
	echo '<td width = 100>';
	echo '<div class="left_column" style="width:' . ($width * 10) . 'px">';
	echo retFieldcomment($field, 'batch_quality');
	echo "</div>";
	echo '</td>';
}

function crfval($id, $field, $val, $width, $changable) {
	echo '<td width = 100>'; 
	if ($changable == 'yes') {
		echo '<div class="left_column" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen">' . $val;
		echo "<script type='text/javascript'>
        new Ajax.InPlaceEditor('" . $field . "', 'npc.php?action=crfed',{formClassName: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&id=" . $id . "&field=" . $field . "'}})</script>";
	} else {
		echo '<div class="left_column" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgrey">' . $val;
	}
	echo "</div>";
	echo '</td>';
}

function crfDetail($table, $id) {
	echo '<div style="width:1200px; background-color: lightgrey";>';
	$result = mysql_query("SELECT * FROM `$table` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$j = 0;
	while ($row = mysql_fetch_object($result)) {
	//	$parentcount = $row->parentcount;
		$date_collection = $row->date_collection;
		$date_visit = $row->date_visit;
		$date_ship = $row->date_ship;
		$date_receipt = $row->date_receipt;
		$id_parent = $row->id_parent;
		$id_subject = $row->id_subject;
		$sample_type = $row->sample_type;
		$shipment_type = $row->shipment_type;
		$id_visit = $row->id_visit;
		$quality = $row->quality;
		$name_shipper = $row->name_shipper;
		#notes
		if ($row->notes != '') {
			$notes = $row->notes;
		} else {
			$notes = '--';
		}
		if ($row->quant_init > 0) {
			$quant_init = $row->quant_init;
		} else {
			$quant_init = "0";
		}
		echo "<table>";
		echo "<tr>";
		showcol('id_subject', $id_subject, '9', 'no');
		showcol('id_visit', $id_visit, '7', 'no');
		showcol('date_collection', $date_collection, '9', 'yes');
		showcol('date_visit', $date_visit, '9', 'yes');
		showcol('date_ship', $date_ship, '9', 'no');
		showcol('date_receipt', $date_receipt, '9', 'no');
		showcol('sample_type', $sample_type, '7', 'no');
		showcol('shipment_type', $shipment_type, '10', 'no');
		showcol('quality', $quality, '9', 'yes');
		showcol('quant_init', $quant_init, '7', 'yes');
		showcol('name_shipper', $name_shipper, '9', 'no');
		showcol('notes', $notes, '14', 'no');
		echo "</tr>";
		echo "<tr>";
		crfval($id, 'id_subject', $id_subject, '9', 'no');
		crfval($id, 'id_visit', $id_visit, '7', 'no');
		crfval($id, 'date_collection', $date_collection, '9', 'yes');
		crfval($id, 'date_visit', $date_visit, '9', 'yes');
		crfval($id, 'date_ship', $date_ship, '9', 'no');
		crfval($id, 'date_receipt', $date_receipt, '9', 'no');
		crfval($id, 'sample_type', $sample_type, '7', 'yes');
		crfval($id, 'shipment_type', $shipment_type, '10', 'yes');
		crfval($id, 'quality', $quality, '2', 'yes');
		crfval($id, 'quant_init', $quant_init, '7', 'yes');
		crfval($id, 'name_shipper', $name_shipper, '5', 'no');
		crfval($id, 'notes', $notes, '14', 'yes');
		echo "</tr>";
		echo "</table>";
	}
	echo '</div>';
	if ($date_receipt != date("Y-m-d")) {
		echo "alert";
		echo '<script type="text/javascript">';
		echo "alert('Note: This sample was scanned on a previous date ".$date_receipt."');";
		echo "</script>";
	}
}

/**
 * If the sample is in the items table, display a message an make no changes.
 * If the sample is in the batch_quality table, update it's batchuuid, mark it as received and add it's subject_id to $_SESSION[subject_array].
 * 
 * @param string $table - Table the scanned item is contained in
 * @param int $id - ID of the scanned item
 */
function operateScannedObject($table, $id) {
	if ($table != 'batch_quality') {
		echo "<div>Error: this sample is already in our inventory</div>";
		echo "<div>It will not be displayed here</div>";
		exit;
	}
	if (!isset($_SESSION['batchuuid'])) {
		$_SESSION['batchuuid'] = new_uuid();
	}
	$result = mysql_query("update batch_quality set id_batch = '" . $_SESSION['batchuuid'] . "' where id = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$result = mysql_query("update batch_quality set date_receipt = CURDATE(), quality = 1, status = 1 where id = '$id' and date_receipt = '0000-00-00'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$result = mysql_query("select id_subject from batch_quality where id = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	$id_subject = $row[0];
	if ($id_subject > '0') {
		if (!isset($_SESSION['subject_array'])) {
			//hide the uploader if this is our first subject
			$hideuploader = 1;
		}
		subjectArray($id_subject);
		crfDetail($table, $id);
		echo '<script type="text/javascript">';
		echo "invoiceSubject('" . $id_subject . "');";
		echo "dashboard()";
		echo "</script>"
?>
<?php
		/*
		subjectArray($id_subject);
		$key = array_keys($_SESSION['subject_array'],$id_subject);
		header("Content-type: application/json; charset=utf-8");
		//$foo =  crForm($batchuuid,$id_subject,$key[0]);
		$arr = array ('group_'.$key[0]=>crForm($batchuuid,$id_subject,$key[0]));
		echo json_encode($arr);
		*/
	}
	//    break;
	
}

function crForm($batchuuid, $id_subject, $key) {
        $last_subject = null;
	$out = '<div style="width:412px; background-color: lightgrey";>';
	$result = mysql_query("SELECT count(*) as parentcount,id_parent,id_subject,date_collection,batch_quality.sample_type,batch_quality.id_visit,batch_quality.family,crf.quantity,crf.`num_order` FROM `batch_quality` left join crf on (batch_quality.family = crf.family and batch_quality.shipment_type = crf.shipment_type and batch_quality. sample_type = crf.sample_type) WHERE `id_batch` = '$batchuuid' and id_subject = '$id_subject' group by id_subject,sample_type,id_visit,id_parent,batch_quality.family order by id_subject,crf.num_order,sample_type,id_visit,id_parent DESC;");
	if (!$result) {
		$out = 'Could not run query: ' . mysql_error();
		exit;
	}
	$j = 0;
	while ($row = mysql_fetch_object($result)) {
		$id_parent = $row->id_parent;
		$parentcount = $row->parentcount;
		$date_collection = $row->date_collection;
		if (strlen($row->family) > 0) {
			$family = $row->family;
		} else {
			$family = "none";
		}
		$id_subject = $row->id_subject;
		$sample_type = $row->sample_type;
		$id_visit = $row->id_visit;
		if (isset($row->quantity)) {
			$quantity = $row->quantity;
		} else {
			$quantity = 0;
		}
		if (isset($_SESSION['quantity'] {
			$id_subject
		} {
			$sample_type
		} {
			$id_visit
		} {
			$family
		})) {
			$quantity = ($_SESSION['quantity'] {
				$id_subject
			} {
				$sample_type
			} {
				$id_visit
			} {
				$family
			});
		}
		if ($id_parent == '0') {
			$class = 'crfparent';
		} else {
			$class = 'crfchild';
			$count{$id_subject} {
				$sample_type
			} {
				$id_visit
			} {
				$family
			} = $parentcount;
		}
		if ($id_subject != $last_subject) {
			$out.= '<div class="row" style="width:411px">';
			$out.= '<div style="width:100px"class="left_column">' . $id_subject . '</div>';
			$out.= '<div style="width:100px"class="left_column">' . $id_visit . '</div>';
			$out.= '<div style="width:100px"class="left_column">' . $family . '</div>';
			$out.= '<div style="width:100px"class="left_column">' . $date_collection . '</div>';
			$out.= '</div>';
		}
		if ($class == 'crfparent') {
			$width = '78';
			$out.= '<div class="row" style="width:311px">';
			$out.= '<div class="left_column" id="parentcount_' . $key . '_' . $j . '"  style="width: 25px; background-color: lightblue">' . $parentcount . '</div>';
			$out.= '<div class="' . $class . '" id="sample_type_' . $key . '_' . $j . '"  style="width: 120px;">' . $sample_type . '</div>';
			if (isset($count{$id_subject} {
				$sample_type
			} {
				$id_visit
			} {
				$family
			})) {
				$out.= '<div class="' . $class . '" id="quantity_' . $key . '_' . $j . '"  style="width: 100px; background-color: lightgreen"">' . $quantity . '</div>';
				$out.= '<div class="' . $class . '" id="alqs_' . $key . '_' . $j . '"  style="width: 38px; background-color: lightgrey"">' . $count{$id_subject} {
					$sample_type
				} {
					$id_visit
				} {
					$family
				} . '</div>';
				//     $_SESSION['quantity']{$id_subject}{$sample_type}{$id_visit}{$family} = 0;
				
			} else {
				$out.= '<div class="' . $class . '" id="quantity_' . $key . '_' . $j . '"  style="width: 100px; background-color: lightgreen"">' . $quantity . '</div>';
				$out.= '<div class="' . $class . '" id="alqs_' . $key . '_' . $j . '"  style="width: 38px; background-color: lightgrey"">0</div>';
			}
			$out.= "<script type='text/javascript'>
	new Ajax.InPlaceEditor('quantity_" . $key . "_" . $j . "', 'npc.php?action=alqed',{size: 3, callback: function(form, value) { return 'value=' + escape(value)+'&=quantity_" . $key . "_" . $j . "&id_subject=" . $id_subject . "&sample_type=" . $sample_type . "&id_visit=" . $id_visit . "&family=" . $family . "&field=quantity'}})</script>";
			$out.= '</div>';
		}
		$last_subject = $row->id_subject;
		$j++;
	}
	$subjectarray = $_SESSION['subject_array'];
	$out.= '<div style="width:311px">';
	$out.= "<input type=\"button\" value=\"Aliquot\" onclick=\"alqbatch('" . $id_subject . "')\">";
	$out.= "<input type=\"button\" value=\"Print\" onclick=\"printalqs('" . $id_subject . "')\">";
	//	if ((count($subjectarray) > 1) && (array_pop($subjectarray) == $last_subject)) {
	//	$out .= "<div>";
	//	$out .= "<input type=\"button\" value=\"Aliquot All\" onclick=\"alqbatch('all')\">";
	//	$out .= "</div>";
	//	}
	$out.= "</div>";
//	$out.= $outchild;
	$out.= '</div>';
	return $out;
}
function retNewLabelsForm($id_study) {
    global $sps;
    if(array_key_exists('create_by_encounter',$sps->task_behavior)) {
        $by = 'encounter';
    } else {
        $by = 'subject';
    }
    $sql = "select sample_type,quantity from crf where id_study = '$id_study'";
    $sql .= " order by num_order";
    $result = mysql_query($sql);
    if (!$result) {
        return -1;
    }
    while ($row = mysql_fetch_array($result)) {
        $sample_type = $row['sample_type'];
        $quantity = $row['quantity'];
        $sampletypes[$sample_type] = $quantity;
    }
    foreach($sampletypes as $sample_type=>$quantity) {
        ${'num' . $sample_type} = $quantity;
    }
    $itemfields = array("id_visit"=>'', "shipment_type"=>'');
	print "<div>";
		?>
		<h2>Generate <?php echo $_SESSION['id_study']; ?> labels for new <?php echo $by?>s:</h2>
		<form action="tasks/form/crf.php" method="post">
		<input type="hidden" name="form_action" value="generateLabels"></input>
		<table>
		<tr><td><label for="numpackets">number of <?php echo $by?>s:</label></td>
		<td><input type="text" name="numpackets" value="1"></input></td></tr>
		<tr><td>or</tr></td>
                <tr><td><label for="id_<?php echo $by?>"><?php echo $by?> ID:</label></td>
                <td><input type="text" name="id_<?php echo $by?>" value=""></input></td></tr>

		
		<tr><td>Options</tr></td>
		<tr><td><label for="id_visit">Id Visit:</label></td>
		<td><select name="id_visit">
		<?php echo retParamsFormOptionList("id_visit", "V1Y0"); ?>
		</select></td></tr>
		
		<tr><td><label for="shipment_type">Shipment Type:</label></td>
		<td><select name="shipment_type">
		<?php echo retParamsFormOptionList("shipment_type", "LOCAL"); ?>
		</select></td></tr>
		<tr><td>&nbsp</td><td>&nbsp</td></tr>
        <?php
    foreach($sampletypes as $sample_type=>$quantity) {
        print '<tr><td><label for="sample'.$sample_type.'">'.$sample_type.' labels per '.$by.':</label></td>';
        print' <td><input type = "text" name="num'.$sample_type.'" value="'.$quantity.'"></input></td></tr>';
    }
        ?>
		</table>
		<br/><input class='btn' type="submit" name="submit" value="Submit" />
		</form>
	</div>
    <?php
}

function getStudy($id_study) {
	$sql = "select * from studies where id_study = '$id_study'";
	$result = mysql_query($sql);
	if (!$result) {
		return false;
	}
	$row = mysql_fetch_array($result);
	return $row;
}
function autoassignCohort($id_study) {
        $study = getStudy($id_study);
	$autoassign_cohort = $study['autoassign_cohort'];
	return $autoassign_cohort;
}
function getNextIdSubject($id_study) {
	$sql = "select max(id_subject) as id_subject from cohort where id_study = '$id_study' and id_study in (select id_study from studies where autoassign_cohort=1)";
	$result = mysql_query($sql);
	if (!$result) {
		//echo "Could not perform query: " . mysql_error();
		return -1;
	}
	$row = mysql_fetch_array($result);
	$max_id = $row['id_subject'];
	if (!is_numeric($max_id)) {
		return 0;
	}
	$next_subject = $max_id + 1;
	return $next_subject;
}
?>
