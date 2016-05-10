<?php
//
// * process the variables passed by browser
if (isset($_POST['id'])) {
	$id = $_POST['id'];
}
if (isset($_POST['type'])) {
	$type = $_POST['type'];
}
if (isset($_POST['divX'])) {
	$divX = $_POST['divX'];
}
if (isset($_POST['divY'])) {
	$divY = $_POST['divY'];
}
if (isset($_POST['action'])) {
	$action = $_POST['action'];
}
if (isset($_POST['function'])) {
	$function = $_POST['function'];
}
if (isset($_SESSION['id_study'])) {
	$id_study = $_SESSION['id_study'];
}
//<fgroup id='sample_params'>
/** <group id=sample_params'>Sample Parameter Functions
 * @param $array_fields
 * @param $field_names
 */
function query_filter($array_fields, $field_names = null) {
	$filter = ' ';
	global $title;
	$title = '';
	//foreach($array_fields as $field) {
    for($i = 0; $i < count($array_fields); $i++) {
		$field = $array_fields[$i];
		if(isset($field_names) and (strlen($field_names[$i]) > 0))
				$field_name = $field_names[$i];
		else
				$field_name = $field;
		
		if (isset($_SESSION[$field])) {
			if (is_array($_SESSION[$field])) {
				$filter.= $field_name . " REGEXP '" . implode($_SESSION[$field], "|") . "' and ";
				$title.= "-" . $field_name . "_" . implode($_SESSION[$field], "-");
			} else if ($_SESSION[$field] != '%') {
				$filter.= $field_name . " = '" . $_SESSION[$field] . "' and ";
				$title.= "-" . $field_name . "_" . $_SESSION[$field];
			}
		}
	}
	return $filter;
}

function setParam($param, $value, $mod, $type = "multi") {
	$old_params = $_SESSION['params'];
	$new_params = array();
	if ((in_array($value, $old_params)) && ($mod == 'del')) {
		foreach($old_params as $old_param) {
			if ($old_param != $value) {
				array_push($new_params, $old_param);
			}
		}
	} else {
		$new_params = $old_params;
		array_push($new_params, $value);
	}
	$_SESSION['params'] = $new_params;
	echo showParam($param,$type,true);
}

/**
 * Display a ui to choose the given parameter
 * @param string $param the parameter to display, must be in params.param [destination|id_assay|id_instrument|id_study|id_visit|sample_type|shipment_type]
 * @param string $type ['multi'|'single']
 */
function showParam($param,$type = "multi",$update=false) {
	if (!isset($_SESSION['params'])) {
		$_SESSION['params'] = array();
		return '';
		exit();
	} 
	$query = "select value,id_param from filters left join params on (filters.id_param = params.id)";
	$query .= "where id_study like '" . $_SESSION['id_study'] . "' and param = '" . $param . "'";
	$query .= ' group by params.id';
	$query .= " order by id_param";
	$result = mysql_query($query);
	$return = "";
	if ($update==false && $type = 'multi') {
		$return .= "<div id=\"" . $param . "_list\" onmouseover=Element.show('varSelect_" . $param . "') ";
		$return .= "onmouseout=Element.hide('varSelect_" . $param . "')>\n";
		$return .= "<label>$param</label>\n";
		$return .= "<div id=\"varSelect_$param\" style = \"display: none\">\n";
	} else if ($update==true && $type = 'multi') {
		$return .= "<div id=\"varSelect_$param\">\n";
	}
	while ($row = mysql_fetch_array($result)) {
		$return .= "<div>";
		if ((in_array($row['id_param'], $_SESSION['params']))) {
			$return .= "<input type=\"checkbox\" checked onchange=setParam('" . $param . "','" . $row['id_param'] . "','del');>";
			$return .= $row['value'] . "</input>\n";
		} else {
			$return .= "<input type=\"checkbox\" onchange=setParam('" . $param . "','" . $row['id_param'] . "','1');>";
			$return .= $row['value'] . "</input>\n";
		}
		$return .= "</div>\n";
	}
	$return .= "</div>\n";
	if ($update==false && $type = 'multi') {
		$return.= "</div>\n";
	}
	print $return;
}

/**
 *Get an array of possible allowed values for a given parameter
 * @param string $param
 */
function retArrayParamValues($param) {
		$return = array();
		$study = $_SESSION['id_study'];
		$query = "select distinct value from filters inner join params on filters.id_param = params.id
				where params.param = '$param'";
		if (isset($study) and strlen($study) > 0) {
				$query .= " and (filters.id_study = '$study' or filters.id_study = 'ALL')";
		}
		$query .= ' order by params.id';
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)) {
				$return[] = $row['value'];
		}
		return $return;
		
}

/**
 * Return an array of all parameters allowed for the current study
 */
function retArrayParams() {
         global $sps;
		$return = array();
		$study = $sps->active_study->id_study;
		$query = "select distinct param from filters inner join params on filters.id_param = params.id
				where true";
		if (isset($study) and strlen($study) > 0) {
				$query .= " and (filters.id_study = '$study' or filters.id_study = 'ALL')";
		}
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)) {
				$return[] = $row['param'];
		}
		return $return;
}
function studyGroupsBy($id_study) {
	$query = "select group_by from studies where id_study = '$id_study'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	return $row['group_by'];
}


/**
 * Get an array of parameters that coorespond to ids
 * @param array(int) $array_params array of ids from params.id
 * @param array(string) $array_fields array of field names
 */
function retArrayParamsById($array_params, $array_fields) {
	$return = "";
	$ret_array = array();
	if (isset($array_params) && count($array_params) > 0) {
		$query = "SELECT value,param from params where id in (" . implode($array_params, ',') . ") and param in ('" . implode($array_fields, "','") . "');";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) {
			if (!isset($ret_array[$row['param']])) {
				$ret_array[$row['param']] = array();
			}
			array_push($ret_array[$row['param']], $row['value']);
		}
	}
	return $ret_array;
}

/**
 * Generate the <option> list to be used in a select statement based on parameter
 * values for the current $_SESSION[id_study]
 * 
 * @param string $field
 * @param string $notFoundValue - option to use if the filter does not exist for the study
 * @param string $defaultValue - default option
 *
 * @return string - the option list from the filter or an option of the defaultValue
 */
function retParamsFormOptionList($field, $notFoundValue = "", $defaultValue = "") {
		$returnVal = "";
		
		$filterKeys = retArrayParams();
		if (in_array($field, $filterKeys)) {
				$filterValues = retArrayParamValues($field);
				foreach ($filterValues as $value) {
						if ($value == $defaultValue){
								$returnVal .= "<option selected value='$value'>$value</option>";
						}
						else {
								$returnVal .= "<option value='$value'>$value</option>";
						}
				}
		}
		else {
				$returnVal .= "<option value = '$notFoundValue'>$notFoundValue</option>";
		}
		return $returnVal;
print $returnVal;
}

function retQueryFilter($array_params, $array_fields, $prefix = null) {
	$return = '';
	if($prefix != null) {
	$prefix = $prefix.".";
	} else {
	$prefix = "";
	}
	$ret_array = retArrayParamsById($array_params, $array_fields);
	foreach($ret_array as $key => $filter) {
		if (count($filter) > 1) {
			$return.= $prefix.$key . " in ('" . implode($filter, '\',\'') . "')";
		} else {
			$return.= $prefix.$key . " = '" . $filter[0] . "' ";
		}
		if ($filter != end($ret_array)) {
			$return.= " and ";
		}
	}
	return $return;
}

//</fgroup>
//<fgroup id='array_functions'>
//array functions
function trimArray($array,$show_cols) {
$new_array = array();
foreach ($array as $row) {
        foreach ($show_cols as $col) {
        $newrow[$col] = $row[$col];
        }
        array_push($new_array,$newrow);
}
return $new_array;
}


function arrayTranspose($sourcearray,$usekey) {
        $new_array = array();
        foreach (array_keys($sourcearray) as $key) {
                $row = $sourcearray[$key];
                $new_key =  $row[$usekey];
                if (isset($new_key)) {
                        $new_array[$new_key] = $row;
                }
        }
return $new_array;
}

//</fgroup>
//<fgroup id=storage>
function setShelf($shelf_id, $freezer_id, $subdiv1) {
	$freezer_uuid = retUuid($freezer_id);
	$insert = mysql_query("INSERT INTO `locations` (id_item,id_container,freezer,subdiv1, name_created) values ('$shelf_id','$freezer_uuid',(select comment1 from items where id = $freezer_id limit 1),'$subdiv1', '" . $GLOBALS['sps']->username . "')");
	if (!$insert) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
}

//</fgroup>
//<fgroup id=results>
function retAssayName($id_assay) {
	$query = "SELECT name_assay FROM `assays` WHERE `id_assay` = '$id_assay'";
	if (isset($_SESSION['id_study'])) {
		$query.= " and id_study like '" . $_SESSION['id_study'] . "' ";
	}
	$array = mysql_query($query);
	$row = mysql_fetch_row($array);
	if ($row[0] != "") {
		return $row[0];
	} else {
		return $id_assay;
	}
}
function retAssaysRun($id) {
	$result = mysql_query("select distinct(id_assay) from results where id_subject = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_array($result);
	while ($row = mysql_fetch_object($result)) {
		$assay_array[] = $row->id_assay;
	}
	$assay_list = implode(",", $assay_array);
	return $assay_list;
	mysql_free_result($result);
}

function retAssayVal($id, $assay) {
	$result = mysql_query("select (value) from results where id_subject = '$id' and id_assay = '$assay' limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_object($result);
	if ($row->value) {
		return $row->value;
	} else {
		return 'NULL';
	}
	mysql_free_result($result);
}

//</fgroup>
//<fgroup id=generic>
function retShipType($alias) {
	$query = "SELECT shipment_type FROM `shipment_types` WHERE `alias` = '$alias'";
	$array = mysql_query($query);
	$row = mysql_fetch_row($array);
	return $row[0];
}

function retBarcodes($id_subject) {
	if ($_SESSION['id_visit'] && $_SESSION['sample_type']) {
		$id_visit = $_SESSION['id_visit'];
		$sample_type = $_SESSION['sample_type'];
		$query = "select  id_barcode from items where sample_type = '$sample_type' and id_visit = '$id_visit' and id_subject = '$id_subject'";
	} else {
		$query = "select id_barcode from items where id_subject = '$id_subject'";
	}
	$result = mysql_query($query);
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$barcodes = array();
	while ($row = mysql_fetch_object($result)) {
		$barcode = $row->id_barcode;
		array_push($barcodes, $barcode);
	}
	return array_unique($barcodes);
}

function retBatchq($id) {
	echo "<input type=\"button\" value=\"print new label\" onclick=\"printlabel('" . $id . "','batch_quality')\">";
}
//these functions operate on scanned barcodes

function uuid2Id($table, $uuid, $short) {
	if ($short == '1') {
		$result = mysql_query("SELECT id FROM `$table` WHERE `id_uuid` like '$uuid%'");
	} else {
		$result = mysql_query("SELECT id FROM `$table` WHERE `id_uuid` = '$uuid'");
	}
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function fwid2Id($barcode,$id_study) {
	if($id_study) {
		$return = array();
		$query = mysql_query("SELECT items.id FROM `items` left join locations on (items.id = locations.id_item) WHERE  items.destination = '' and  `id_barcode` = '$barcode' and id_study = '$id_study' group by items.id order by freezer,items.id");
		while ($row = mysql_fetch_array($query)) {
			$return[] = $row['id'];
		}
		return $return;
	} else {
		print "You must select a study in order to work with freezerworks barcodes";
	}
}

function retIdType($id) {
global $sps;
$active_study = $sps->active_study;
    // check for whitespace
	if (preg_match("/[A-Ha-h0-9]{8}-[A-ha-h0-9]{4}-[A-Ha-h0-9]{4}-[A-ha-h0-9]{4}-[A-Za-z0-9]{12}/", $id, $matches)
		and strlen($id) == 36) {
		$type = 'uuid';
        // order is really important here!  
        // check to see if these strings exist right after we 
        // look for a UUID
	} else if (preg_match("/^c:/", $id, $matches)) {
		$type = 'jsFunction';
	} else if (preg_match("/^npc:/", $id, $matches)) {
		$type = 'npcFunction';
	} else if (preg_match("/^print:/", $id, $matches)) {
		$type = 'lpFunction';
	} else if (preg_match("/^rungroup:/", $id, $matches)) {
		$type = 'rungroup';
	} else if (isset($active_study->allow_linear)) {
		$type = 'linearBarcode';
	} else if (preg_match("/[A-Ha-h0-9]{8}/", $id, $matches)
		and strlen($id) == 8) {
		$type = 'uuidShort';
	} else if (preg_match("/[A-Za-z0-9]{6}/", $id, $matches)) {
		$type = 'linearBarcode';
	} else if (preg_match("/[A-Za-z0-9]{7}/", $id, $matches)) {
		$type = 'linearBarcode';
	} else if (preg_match("/[I]{1}[0-9]{4}/", $id, $matches)) {
		$type = 'linearBarcode';
	} else if (is_numeric($id)) {
		$type = 'linearBarcode';
	} else {
		echo "unknown object";
		exit;
	}
	return $type;
}

function retInTable($uuid, $short) {
	$tableArray = array(
		'items',
		'batch_quality'
	);
	$returnArray = array();
	foreach($tableArray as $table) {
		if ($short == '1') {
			$query = "SELECT count(*) FROM `$table` WHERE `id_uuid` like '$uuid%'";
		} else {
			$query = "SELECT count(*) FROM `$table` WHERE `id_uuid` = '$uuid'";
		}
		$array = mysql_query($query);
		if (!$array) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		$count = mysql_fetch_row($array);
		if ($count[0] > 0) {
			array_push($returnArray, $table);
		}
	}
	return $returnArray;
}

function retSessVal($sessionvar) {
	if (!isset($_SESSION[$sessionvar])) {
		$ret = array(
			'none'
		);
	} else if ($_SESSION[$sessionvar] == array(
		'%'
	)) {
		$return = array(
			'all'
		);
	} else {
		if (is_array($_SESSION[$sessionvar])) {
			$ret = $_SESSION[$sessionvar];
		} else {
			$ret = array(
				'error:' . $sessionvar . ' not an array!'
			);
		}
	}
	return $ret;
}

function retBoxList($freezer, $shelf, $rack) {
	//* export the box contents for printing
	$query = "SELECT count(*) as numrows,items.id,items.sample_type,items.id_visit,items.id_subject,freezer,subdiv1,subdiv2,subdiv3 FROM `items` LEFT JOIN (locations) ON (`items`.`id`=`locations`.`id_item`) where items.type = 'tube' and locations.date_moved is null ";
	$query.= " and `freezer` = '$freezer'";
	//		$query .= " and `subdiv1` = '$shelf' ";
	//		$query .= " and `subdiv2` = '$rack' ";
	if (isset($_SESSION['id_study'])) {
		$id_study = $_SESSION['id_study'];
		$query.= " and `id_study` LIKE '$id_study' ";
	}
	if (isset($_SESSION['id_visit'])) {
		$id_visit = $_SESSION['id_visit'];
		$query.= " and id_visit LIKE '$id_visit'";
	}
	if (isset($_SESSION['sample_type'])) {
		$sample_type = $_SESSION['sample_type'];
		$query.= " and sample_type like '$sample_type' ";
	}
	if (isset($_SESSION['shipment_type'])) {
		$shipment_type = $_SESSION['shipment_type'];
		$query.= " and shipment_type = '$shipment_type' ";
	}
	$query.= "group by freezer,subdiv1,subdiv2,subdiv3";
	$boxQuery = mysql_query($query);
	$filename = 'boxlist';
	if (mysql_num_rows($boxQuery) > '0') {
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=" . $filename . ".csv");
		for ($i = 0;$i < mysql_num_rows($boxQuery);$i++) {
			extract(mysql_fetch_array($boxQuery));
			echo "$numrows,$freezer,$subdiv1,$subdiv2,$subdiv3\n";
		}
	}
	mysql_free_result($boxQuery);
	break;
}


/**
 * Get the id of the box a tube should be placed in
 * 
 * @param int $id - the id of a row in the items table of type tube
 * @return int - id of the box or 0
 * Requires that$_SESSION['box_array'] be set
 *
 * Checks that id_subject, sample_type, shipment_type and id_visit are set
*/
function distrib($id) {
	//only validate these studies
	$validateStudyArray = array('CRIC','CGI');
	$type_result = mysql_query("SELECT id_study,id_subject,sample_type,id_visit,shipment_type,destination FROM `items` WHERE `id` = '$id'");
   	if (!$type_result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}	
	$type_row = mysql_fetch_assoc($type_result);
	$id_subject = $type_row['id_subject'];
	$sample_type = $type_row['sample_type'];
	$id_visit = $type_row['id_visit'];
	$shipment_type = $type_row['shipment_type'];
	$destination = $type_row['destination'];
	$id_study = $type_row['id_study'];
	// validate fields
	if (((strlen($id_subject)==0 or strlen($sample_type)==0 or strlen($shipment_type)==0 or strlen($id_visit)==0) and (in_array($id_study,$validateStudyArray))) or strlen($id_study)==0) {
		echo '<div class="alert">Cannot move sample, incomplete sample record.<br/>Update the indicated fields and scan again.</div><br/><ul>';
		if (strlen($id_subject)==0)
			echo "<li>Subject ID not set</li>";
		if (strlen($sample_type)==0)
			echo "<li>Sample Type not set</li>";
		if (strlen($shipment_type)==0)
			echo "<li>Shipment Type not set</li>";
		if (strlen($id_visit)==0)
			echo "<li>Visit not set</li>";
		if (strlen($id_study)==0)
			echo "<li>Study not set</li>";
		echo "</ul>";
		
		echo "<div id=\"ItemInfo\" class =\"ItemInfo\"></div>";
		echo '<script type="text/javascript">';
        echo "getItemId($id);";
        echo "benchView();";
        echo "</script>";
		
		return exit;
	}
	
	// if only one box, return the box
	// if multiple boxes, use destination to determine which one to use
	$box_array = $_SESSION['box_array'];
	if ((($shipment_type == 'PROTRANS') || ($shipment_type == 'PROTRANS_R')) && (strlen($destination) > 0)) {
		$locArray = tubeLocationArray($id);
		if (count($locArray) > 0) {
			?>
			<script>
			alert('sample already scanned');
			getItemId('<?php echo $id;?>');
			</script>
			<?php
			exit;
		}
	}
	if (count($_SESSION['box_array']) == 1) {
		return $_SESSION['boxid'];
	}
	else {
		if (((strlen($destination) > 1)) && (count($_SESSION['box_array']) > 1)) {
			echo $destination;
				$locArray = tubeLocationArray($id);
				if (count($locArray) > 0) {
				topView($id);
				?>
				<script>
				alert('sample already scanned');
				getItemId('<?php echo $id;?>');
				</script>
				<?php
				} else {
					foreach($_SESSION['box_array'] as $key => $row) {
					if (($destination) == $row['dest']) {
						$return = $row['id'];
					}
					}
					return $return;
				}
				exit;
		} else {
			$dist_result = mysql_query("SELECT COUNT( * ) AS `Rows` , `destination` FROM items where id_subject = '$id_subject' and sample_type = '$sample_type' and shipment_type = '$shipment_type'  and id_visit = '$id_visit ' GROUP BY `destination` ORDER BY `destination`");
			while ($dist_row = mysql_fetch_object($dist_result)) {
				$destination = $dist_row->destination;
				$num_samples = $dist_row->Rows;
				foreach(array_keys($box_array) as $k) {
					$v = $box_array[$k]['dest'];
					if ($v == $destination) {
						$box_array[$k]['numrows'] = $dist_row->Rows;
					}
					if (!isset($box_array[$k]['numrows'])) {
						$box_array[$k]['numrows'] = 0;
					}
				}
			}
			$priority = priority($id);
			foreach($box_array as $key => $row) {
				$numrows[$key] = $row['numrows'];
				$bid[$key] = $row['id'];
				$dest = $row['dest'];
				$weight[$key] = $priority[$dest]['weight'];
				$max[$key] = $priority[$dest]['max'];
				if (!($weight > "0")) {
					echo 'Error: Can not determine split priority';
					exit;
				}
				if (is_numeric($priority[$dest]['max']) && ($priority[$dest]['max'] <= $row['numrows'])) {
					unset($numrows[$key]);
					unset($weight[$key]);
					unset($box_array[$key]);
				}
			}
			array_multisort($numrows, SORT_DESC, $weight, SORT_ASC, $box_array);
			$nextup = array_pop($box_array);
			echo $nextup['id'];
			return $nextup['id'];
			mysql_free_result($dist_result);
		}
		mysql_free_result($type_result);
	}
}


function priority($id) {
	$result = mysql_query("select split.destination,split.weight,split.max from items left join split on (split.id_visit = items.id_visit and split.shipment_type = items.shipment_type and split.sample_type = items.sample_type) where id = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$priority = array();
	while ($row = mysql_fetch_object($result)) {
		$destination = $row->destination;
		$weight = $row->weight;
		$max = $row->max;
		$priority[$destination]['weight'] = $weight;
		$priority[$destination]['max'] = $max;
	}
	//	print_r($priority);
	//	print_r(array_keys($priority));
	//echo "$priority[0]";
	return $priority;
	mysql_free_result($result);
}

function retLocations($id) {
	$result = mysql_query(" SELECT COUNT( * ) AS `Rows` , `sample_type` FROM `items` where id_subject Like '$id' GROUP BY `sample_type` ORDER BY `sample_type` LIMIT 0 , 30;");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_array($result);
	echo "<table>";
	while ($row = mysql_fetch_object($result)) {
		$sample_type = $row->sample_type;
		$num_samples = $row->Rows;
		echo "<tr><td>" . $sample_type . "</td><td>" . $num_samples . "</td></tr>\n";
	}
	echo "</table>";
	mysql_free_result($result);
}


function retDiv($id, $subdiv) {
	$result = mysql_query("select $subdiv from locations where id_item = '$id' and date_moved is null order by timestamp desc limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_object($result);
	if ($row->$subdiv) {
		return $row->$subdiv;
	} else {
		return 'null';
	}
	mysql_free_result($result);
}
function tubeLocationArray($id) {
	$result = mysql_query("select id_container,subdiv4,subdiv5 from locations where id_item = '$id' and date_moved is null order by timestamp desc limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($result) == 1) {
	$row = mysql_fetch_object($result);
		return $row;
	} else {
		return array();
	}
	mysql_free_result($result);
}


function retIssue($id) {
	$id_visit = $_SESSION['id_visit'];
	$id_study = $_SESSION['id_study'];
	$sample_type = $_SESSION['sample_type'];
	//$result = mysql_query("select status from sample_status where id_subject = '$id' and id_visit = '$id_visit' and id_study = '$id_study' and sample_type = '$sample_type' limit 1");
	$result = mysql_query("select * from issue where id_subject = '$id'  limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_object($result);
	if ($row->comment1) {
		//if ($row->status) {
		return $row->comment1;
		//        return $row->status;
		
	}
	mysql_free_result($result);
}

function retNextsubid($id_study) {
	$result = mysql_query("select id_subject from batch_quality where id_study = '$id_study' order by id DESC limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_object($result);
	if ($row->id_subject) {
		$next_available = ($row->id_subject);
		//	} else {
		//	$next_available = 0;
		
	}
	$next_available++;
	return $next_available;
	mysql_free_result($result);
}
//*

function divX($id) {
	$result = mysql_query("SELECT  divX FROM `items` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	return $row[0];
	mysql_free_result($result);
}

function divY($id) {
	$result = mysql_query("SELECT  divY FROM `items` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	return $row[0];
	mysql_free_result($result);
}

function seq($id) {
	$result = mysql_query("SELECT  count(*) FROM `locations` WHERE `id_container` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}
/**
 * Displays a gui that allows the user to set session variables.
 * The session variables are intended to be used as query filters.
 *
 * For the value of 'filter' to be set, it must be defined in the 'filter' statement in npc.php
 *
 * @param string $filter The field that is being filtered
 * @param string $table The table to search for possible values of the field
 * @param string $reach ['multi'|'single'|'checkbox'|'text']
 * @param bool $showAll If true, allow a 'show all' option
 * @param string $defaultVal If $defaultVal is not false, if the session variable defined in $filter is not set, sets it with $defaultVal
 * @param bool $filterByStudy If true, the values are filtered by $_SESSION id_study
*/
function filter($filter, $table, $reach, $showAll = true, $defaultVal = false, $filterByStudy = true) {
	if (($reach == 'multi') || ($reach == 'single')) {
		$id_study = $_SESSION['id_study'];
		$result = mysql_query("SELECT id,`$filter` FROM `$table` where id_study like '$id_study' group by `$filter` order by id");
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
    // set default value
	if(($defaultVal) and (strlen(trim($defaultVal)) > 0) and (!isset($_SESSION[$filter]))) {
		$defaultVal = trim(htmlentities($defaultVal));
		$_SESSION[$filter] = $defaultVal;
	}
	if ($reach == 'multi') {
		echo "<div>" . retFieldcomment($filter, $table) . "</div>";
		// defaults to single select
		
	} else if ($reach == 'single') {
		echo "<div>" . retFieldcomment($filter, $table) . "</div>";
		echo '<select class="btn" id="cc' . $filter . '" onChange="filter(\'' . $filter . '\',$F(\'cc' . $filter . '\'))" class="autocomplete">';
		//	echo '<select id="options-'.$filter.'" name="options-'.$filter.'" class="autocomplete">'."\n";
		if ($showAll) {
		   	echo '<option value="%">show all</option>' . "\n";
                }
		while ($row = mysql_fetch_object($result)) {
			if (isset($_SESSION[$filter]) && ($_SESSION[$filter] == $row->{$filter})) {
			$sel = 'selected';
			} else {
				$sel = '';
			}
			echo '<option value="' . $row->{$filter} . '" ' . $sel . '>' . $row->{$filter} . '</option>' . "\n";
		}
		echo '</select>' . "\n";
	} else if ($reach == 'checkbox') {
		if ($_SESSION[$filter] == 'yes') {
			echo "<input type=\"checkbox\" value=\"on\" onChange=\"filter('$filter','yes')\"/>";
		} else {
			echo "<input type=\"checkbox\" value=\"off\" onChange=\"filter('$filter','yes')\"/>";
		}
	} else if ($reach == 'text') {
		if (isset($_SESSION[$filter])) {
			$filtertext = $_SESSION[$filter];
		} else {
			$filtertext = '';
		}
		echo "<div>" . retFieldcomment($filter, $table) . "</div>";
		echo '<input class="btn input" type="text" value="' . $filtertext . '"  autocomplete="off" id="cc' . $filter . '" onChange="filter(\'' . $filter . '\',$F(\'cc' . $filter . '\'))" >';
	}
}

function filtervar($variable, $table, $mod) {
	$return = "<div id=varSelect_" . $variable . ">";
	if ($mod == 'no') {
		#	$return .=  '<div onMouseOver=setTimeout("Effect.Appear(\'filterVar(\''.$variable.'\',\''.$table.'\',\'yes\')\',{duration:0.1})"),300)>';
		$return.= '<p>';
	}
	if ($mod == 'yes') {
		$return.= "<div>";
		//	$return .=  '<div onMouseOut=setTimeout("filterVar(\''.$variable.'\',\''.$table.'\',\'no\')",300)>';
		$return.= '<p>';
	}
	if ($mod == 'yes') {
		$array_options = available_options($variable);
		foreach($array_options as $option) {
			if ((is_array($_SESSION[$variable])) && (in_array($option, $_SESSION[$variable]))) {
				$selected = 'checked';
			} else {
				$selected = '';
			}
			$return.= "<div><input type=\"checkbox\" " . $selected . " onchange=setVar('" . $variable . "','" . $option . "','yes');>" . $option . "</input></div>";
		}
	} else {
		if (is_array($_SESSION[$field])) {
			$return.= implode($_SESSION[$field], ",");
		} else {
			$return.= 'none';
		}
	}
	$return.= '</p>';
	$return.= "</div></div>";
	echo $return;
}

function dest($id) {
	$result = mysql_query("SELECT  destination FROM `items` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function remItem($id) {
	$result = mysql_query("DELETE FROM `items` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function showHint($id) {
	$result = mysql_query("SELECT id_subject FROM `cohort` WHERE `id_subject` = '$id%'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	echo "<ul>";
	while ($data = mysql_fetch_assoc($result)) {
		$id_subject = stripslashes($data['id_subject']);
		echo "<li>" . $id_subject . "</li>";
	}
	echo "</ul>";
	mysql_free_result($result);
}

function inCohort($id) {
	$result = mysql_query("select count(*) as numrows from cohort where id_subject = '$id' ");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	return (mysql_result($result, 0, "numrows"));
	mysql_free_result($result);
}

function isUuid($id) {
	if (preg_match("/[A-Ha-h0-9]{8}-[A-ha-h0-9]{4}-[A-Ha-h0-9]{4}-[A-ha-h0-9]{4}-[A-Za-z0-9]{12}/", $id, $matches)) {
		return true;
	} else {
                return false;
        }
}
//*

function type($id) {
	$result = mysql_query("SELECT  type FROM `items` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function retUuid($id) {
	$result = mysql_query("SELECT id_uuid FROM `items` WHERE `id` = '$id'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return false;
	}
	mysql_free_result($result);
}

//returns the uuid of a sample based on a linear barcode

function retbqId($id_barcode) {
	$id_study = $_SESSION['id_study'];
	$result = mysql_query("SELECT id FROM `batch_quality` WHERE `id_barcode` = '$id_barcode' and id_study = '$id_study'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}
function retbqUuid($id_barcode) {
	$id_study = $_SESSION['id_study'];
	$result = mysql_query("SELECT id_uuid FROM `batch_quality` WHERE `id_barcode` = '$id_barcode' and id_study = '$id_study'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function retParent($id_uuid) {
	$result = mysql_query("SELECT id_parent FROM `items` WHERE `id_uuid` = '$id_uuid'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function retContainer($id) {
	$result = mysql_query("SELECT id_container FROM `locations` WHERE `id_item` = '$id' and date_moved is NULL order by id desc limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return '0';
	}
	mysql_free_result($result);
}

function retVisitDate($id_subject, $id_visit) {
	$result = mysql_query("SELECT date_create FROM `visits` WHERE `id_subject` = '$id_subject' and id_visit = '$id_visit'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($result);
	if ($row[0] > '0') {
		return $row[0];
	} else {
		return 'N/A';
	}
	mysql_free_result($result);
}

function aliquot($id, $table, $daughters) {
	if (!is_numeric($daughters)) {
		exit;
	}
	for ($i = 0;$i < $daughters;$i++) {
		$uuid = new_uuid();
		if ($table == 'batch_quality') {
			$result = mysql_query("insert into batch_quality (id_uuid,id_parent,id_batch,id_subject,id_study,id_visit,id_alq,name_created,date_visit,date_collection,date_ship,date_receipt,shipment_type,sequence,sample_type,type,shipped,specnotavail,quality,status,family,copies,subdiv4,subdiv5,error_temp,error_label,error_volume,error_damage,error_delay,error_other,notes) (SELECT '$uuid',id_uuid,id_batch,id_subject,id_study,id_visit,$i+1,name_created,date_visit,date_collection,date_ship,date_receipt,shipment_type,sequence,sample_type,type,shipped,specnotavail,quality,status,family,copies,subdiv4,subdiv5,error_temp,error_label,error_volume,error_damage,error_delay,error_other,notes FROM `$table` WHERE `id` = '$id')");
		} else if ($table == 'items') {
			$result = mysql_query("insert into batch_quality (id_uuid,id_parent,id_subject,id_study,id_visit,id_alq,name_created,date_visit,date_collection,date_receipt,shipment_type,sample_type,quant_thaws,type,notes) (SELECT '$uuid',id_uuid,id_subject,id_study,id_visit,$i+1,name_created,date_visit,date_collection,date_receipt,shipment_type,sample_type,quant_thaws,type,notes from `$table` WHERE `id` = '$id')");
		}
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		} else {
		//mysql_free_result($result);
		}
	}
}

function aliquot_s($id, $table, $daughters, $spool) {
	$daughter_id = array();
	if (!is_numeric($daughters)) {
		exit;
	}
	for ($i = 0;$i < $daughters;$i++) {
		$uuid = new_uuid();
		if ($table == 'batch_quality') {
			$result = mysql_query("insert into batch_quality (id_uuid,id_parent,id_batch,id_subject,id_study,id_visit,id_alq,name_created,date_visit,date_ship,date_receipt,shipment_type,sequence,sample_type,type,shipped,specnotavail,quality,status,family,copies,subdiv4,subdiv5,error_temp,error_label,error_volume,error_damage,error_delay,error_other,notes) (SELECT '$uuid',id_uuid,id_batch,id_subject,id_study,id_visit,$i+1,name_created,date_visit,date_ship,date_receipt,shipment_type,sequence,sample_type,type,shipped,specnotavail,quality,status,family,copies,subdiv4,subdiv5,error_temp,error_label,error_volume,error_damage,error_delay,error_other,notes FROM `$table` WHERE `id` = '$id')");
		} else if ($table == 'items') {
			$result = mysql_query("insert into batch_quality (id_uuid,id_parent,id_subject,id_study,id_visit,id_alq,name_created,date_visit,date_receipt,shipment_type,sample_type,notes) (SELECT '$uuid',id_uuid,id_subject,id_study,id_visit,$i+1,name_created,date_visit,date_receipt,shipment_type,sample_type,notes from `$table` WHERE `id` = '$id' and type = 'tube')");
		}
		$newid = mysql_insert_id();
		if ($newid > '0') {
		$daughter_ids[$i] = $newid;
		} else {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		//      mysql_free_result($result);
		
	}
	if ($spool == '1') {
            global $sps;
            if (!$sps->printer) {
                print "error: no printer selected";
                exit;
            }
            $printer = New PrintDev();
            $printer = $sps->printer;
            $spooler = New PrintJobs();
            $spooler->printer_id =  $printer->printer_id;
//            $job = $spooler->createPrintJob();
            for ($i = 0;$i < $daughters;$i++) {
                $daughter_id = $daughter_ids[$i];
                $spooler->spoolPrintJob($daughter_id, 'batch_quality');
	    }
	}
}

function setBatch($id_subject, $id_visit, $date_visit) {
	if (isset($_SESSION['batchuuid'])) {
		$batchuuid = $_SESSION['batchuuid'];
		if (isset($_SESSION['shipment_type'])) {
			$shipment_type = $_SESSION['shipment_type'];
			$result = mysql_query("UPDATE `batch_quality` SET id_batch = '$batchuuid' WHERE `id_subject` = '$id_subject' and `id_visit` = '$id_visit' and `date_visit` = '$date_visit' and shipment_type = '$shipment_type' and `id_batch` = ''");
		} else {
			$result = mysql_query("UPDATE `batch_quality` SET id_batch = '$batchuuid' WHERE `id_subject` = '$id_subject' and `id_visit` = '$id_visit' and `date_visit` = '$date_visit' and `id_batch` = ''");
		}
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}

function printBatch() {
	$postUuid = $_SESSION['batchuuid'];
	if (isset($_SESSION['reprint']) && $_SESSION['reprint'] == 'yes') {
		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_batch` = '$postUuid'  order by id");
	} else {
		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_batch` = '$postUuid' order by id");
	}
	if (!$members) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($members) > 0) {
		// echo "Printing to ".$_SESSION['printer_name'];
		for ($i = 0;$i < mysql_num_rows($members);$i++) {
			extract(mysql_fetch_array($members) , EXTR_PREFIX_ALL, 'member');
			spoolLabel($member_id, 'batch_quality', $i);
			// echo ".";	
		}
	}
	mysql_free_result($members);
	$return = ftpFiles();
	return $return;
}


// # print all matches
// function printMatches() {
// 	$postUuid = $_SESSION['id_uuid'];
// 	if (isset($_SESSION['reprint']) && $_SESSION['reprint'] == 'yes') {
// 		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_uuid` = '$postUuid'  order by id");
// 	} else {
// 		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_uuid` = '$postUuid' order by id");
// 	}
// 	if (!$members) {
// 		echo 'Could not run query: ' . mysql_error();
// 		exit;
// 	}
// 	if (mysql_num_rows($members) > 0) {
// 			// echo "Printing to ".$_SESSION['printer_name'];
// 		for ($i = 0;$i < mysql_num_rows($members);$i++) {
// 			extract(mysql_fetch_array($members) , EXTR_PREFIX_ALL, 'member');
// 			spoolLabel($member_id, 'batch_quality', $i);
// 			//			echo ".";	
// 		}
// 	}
// 	mysql_free_result($members);
// 	$return = ftpFiles();
// 	return $return;
// }



function listFiles($dir,$filetype) {
$extlength = strlen($filetype);
$arrayFiles = array();
if (is_dir($dir)) {
	$rex = "/^.*\.(".$filetype.")$/i";
	$directory = opendir($dir);
	while($file = readdir($directory)){
    // We filter the elements that we don't want to appear ".", ".." and ".svn"
		if(preg_match($rex, $file)){
		$arrayFiles[] = substr($file,0,-($extlength +1));
	}	
	}
}
return $arrayFiles;
}

//function printAliquots() {

function printAliquots($id_subject) {
	if ($_SESSION['task'] == 'pending') {
		$batchuuid == '';
	} else {
		$batchuuid = $_SESSION['batchuuid'];
	}
	if ($id_subject == 'all') {
		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_batch` = '$batchuuid' and import_source is null and id_parent != '0' order by id_subject,sample_type,id_alq +0,id");
#		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_batch` = '$batchuuid' and import_source is null and id_parent != '0' order by id");
	} else {
		$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_batch` = '$batchuuid' and id_subject = '$id_subject' and import_source is null and id_parent != '0' order by sample_type,id_alq +0,id");
	}
	if (!$members) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($members) > 0) {
		echo "Printing to " . $_SESSION['printer_name'];
		for ($i = 0;$i < mysql_num_rows($members);$i++) {
			extract(mysql_fetch_array($members) , EXTR_PREFIX_ALL, 'member');
			spoolLabel($member_id, 'batch_quality', $i);
			echo ".";
		}
	}
	mysql_free_result($members);
	ftpFiles();
}

function printBlanks($quant, $copies = 1, $format = 'blank') {
	for ($i = 0;$i < $quant;$i++) {
		$printfile = session_id() . sprintf("%05d", $i) . '.txt';
		$handling = fopen('/tmp/' . $printfile, 'w');
		$id_uuid = new_uuid();
		$uuidShort = substr($id_uuid, 0, 8);
		$template = 'templates/'.$format.'.php';
		include ($template);
		fwrite($handling, $labelData);
	}
	ftpFiles();
}

function printCommand($command) {
	spoolCommand($command);
	ftpFiles();
}
//*

function insUuid($uuid, $type, $id_study, $sample_type, $shipment_type, $id_visit) {
		$result = mysql_query("INSERT INTO `items` (id_uuid,type,id_study,sample_type,shipment_type,id_visit,name_last_updated) values ('$uuid','$type','$id_study','$sample_type','$shipment_type','$id_visit','" . $GLOBALS['sps']->username . "')");
		$id = mysql_insert_id();
		if ($id > '0') {
			return $id;
		} else {
			return "error";
		}
}
function modId($id, $type, $divX, $divY, $comment1, $dest, $sample_type = "") {
	$id_study = $_SESSION['id_study'];
	$result = mysql_query("UPDATE `items` SET `divX` = '$divX', `divY` = '$divY', `type` = '$type', `destination` = '$dest', `id_study` = '$id_study', `comment1` = '$comment1', sample_type = '$sample_type', `name_last_updated` = '" . $GLOBALS['sps']->username . "' WHERE `items`.`id` ='$id' LIMIT 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
}




function setNote($id, $notes) {
	$id_study = $_SESSION['id_study'];
	$result = mysql_query("UPDATE `results` SET `notes` = '$notes' WHERE `items`.`id` ='$id' LIMIT 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
}
//*

function crf_edit($postId, $postField, $postValue) {
	$result = mysql_query("UPDATE `batch_quality` SET `$postField` = '$postValue' WHERE `id` = '$postId'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} else {
		echo $postValue;
	}
}

/**
 * Update a field in the items table.
 * Prints an error message if unable to update.
 * 
 * @param integer $postId
 * @param string $postField
 * @param string $postValue
 */
function item_edit($postId, $postField, $postValue) {
		$postField = mysql_real_escape_string(trim($postField));
		$postValue = mysql_real_escape_string(trim($postValue));
		
		// check that the field exists
		$fieldInfoResult = mysql_query("describe items $postField");
		if(!$fieldInfo = mysql_fetch_array($fieldInfoResult)) {
				echo "Could not update items, invalid field: $postField";
		}
		
		// check that we have a valid item id
		if((!isset($postId)) || ($postId <=0)) {
				echo "Could not update items, invalid id: $postId";
		}
		
		$result = mysql_query("UPDATE `items` SET `$postField` = '$postValue', `name_last_updated` = '" . $GLOBALS['sps']->username . "' WHERE `id` = '$postId'");
		if (!$result) {
				echo 'Could not run query: ' . mysql_error();
				exit;
		} else {
				echo "$postValue";
		}
}

function edit($postId, $postField, $postValue, $postTable) {
	$result = mysql_query("UPDATE `$postTable` SET `$postField` = '$postValue', `name_last_updated` = '" . $GLOBALS['sps']->username . "' WHERE `id` = '$postId'");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} else {
		echo $postValue;
	}
}

function vol_cur($id,$value,$table) {
                if (($id > 0) && ($table == 'items')) {
                        edit($id, 'quant_cur', $value, 'items');
                        // also update the sister alaquots who are still in the batch table
                        $id_uuid = retUuid($id);
                        //echo $postValue;
                } else if (($id > 0) && ($table == 'batch_quality')) {
			echo 'use the vol_init barcode to set the initial volume for a sample';
            		exit;
                } else {
		echo 'invalid id for vol function';
                exit;
		}
}
function vol_init($id,$value,$table) {
                if (($id > 0) && ($table == 'items')) {
                        edit($id, 'quant_init', $value, 'items');
                        edit($id, 'quant_cur', $value, 'items');
                        // also update the sister alaquots who are still in the batch table
                        $id_uuid = retUuid($id);
                        edit_sisters($id_uuid, 'quant_init', $value, 'batch_quality');
                        //echo $postValue;

                } else if (($id > 0) && ($table == 'batch_quality')) {
                        edit($id, 'quant_init', $value, 'batch_quality');
                } else {
		echo 'invalid id for vol function';
                exit;
		}
}


function thaw($id, $value) {
	$result = mysql_query("UPDATE `items` SET `quant_thaws` = quant_thaws + $value , `name_last_updated` = '" . $GLOBALS['sps']->username . "' WHERE `id` = '$id' limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} else {
		return $value;
	}
}

function hemolyzse_full($id, $value) {
	$result = mysql_query("UPDATE `items` SET `hemolyzation` = 'full', `name_last_updated` = '" . $GLOBALS['sps']->username . "' WHERE `id` = '$id' limit 1");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} else {
		return $value;
	}
}

function edit_sisters($id_uuid, $postField, $postValue, $postTable) {
	$id_parent = retParent($id_uuid);
	if (isUuid($id_parent) == '1') {
		$result = null;
		if ($postTable == 'items') {
			$result = mysql_query("UPDATE `$postTable` SET `$postField` = '$postValue', `name_last_updated` = '" . $GLOBALS['sps']->username . "' WHERE `id_parent` = '$id_parent'");
		}
		else {
			$result = mysql_query("UPDATE `$postTable` SET `$postField` = '$postValue' WHERE `id_parent` = '$id_parent'");
		}

		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}
//*

function export_box() {
	$boxid = $_SESSION['boxid'];
	$members = mysql_query("SELECT * FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item` ) WHERE `id_container` = '$boxid' and locations.date_moved is null");
	if (!$members) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($members) > 0) {
		header("Content-Type: application/x-csv");
		header("Content-Disposition: attachment; filename=" . $boxid . ".csv");
		for ($i = 0;$i < mysql_num_rows($members);$i++) {
			extract(mysql_fetch_array($members) , EXTR_PREFIX_ALL, 'member');
			echo "$member_id_uuid,CRIC,$member_shipment_type,$member_sample_type,$member_id_visit,'$member_id_subject,$member_date_visit,$member_freezer,$member_subdiv1,$member_subdiv2,$member_subdiv3," . num2chr($member_subdiv4) . ",$member_subdiv5\n";
		}
	}
}
function retBoxContents($id_container) {
	$return = array();
	$results = mysql_query("SELECT items.id FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item` ) WHERE `id_container` = '$id_container' and locations.date_moved is null order by locations.subdiv4,locations.subdiv5");
	if (!$results) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	while ($row = mysql_fetch_array($results)) {
		array_push($return,$row['id']);

	}
	return $return;
}


function newShipment($id_subject, $batchUuid, $date_collection, $id_visit) {
	// look up a family of specimin types and add the samples to the quality db
	$family = $_SESSION['family'];
	$result = mysql_query("SELECT * FROM `crf` WHERE `family` = '$family' order by `num_order`");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$n = 1;
	$values = '';
	while ($row = mysql_fetch_array($result)) {
		$quantity = $row['quantity'];
		$sample_type = $row['sample_type'];
		$id_study = $row['id_study'];
		$type = $row['type'];
		$copies = $row['copies'];
		$shipment_type = $row['shipment_type'];
		$num_order = $row['num_order'];
		for ($i = 0;$i < $quantity;$i++) {
			$unitUuid = new_uuid();
			if (($n != 1) || ($i != 0)) {
				$values.= ",";
			}
			$values.= "('$unitUuid', '$batchUuid', '$id_subject', '$date_collection', '$id_visit', '$id_study', '$n', '$copies','$type','$num_order','$sample_type', '$shipment_type')";
			$n++;
		}
	}
	$statement = "INSERT INTO `batch_quality` (`id_uuid` ,`id_batch`, `id_subject`, `date_collection`, `id_visit`, `id_study`, `sequence`,`copies`,`type`,`num_order`,`sample_type`, `shipment_type`)VALUES $values";
	$insert = mysql_query($statement);
	if (!$insert) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	return $batchUuid;
}

function aliquotShipment($id_subject) {
}


function disjoinShipment($id, $id_subject) {
	if ($_SESSION['batchuuid']) {
		$batchuuid = $_SESSION['batchuuid'];
		if ($id == '0') {
			$result = mysql_query("delete FROM `batch_quality` WHERE id_subject = '$id_subject' and `id_batch` = '$batchuuid'");
		}
		if ($id_subject == '0') {
			$result = mysql_query("delete FROM `batch_quality` WHERE id = '$id' and `id_batch` = '$batchuuid'");
		}
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}

function disjoinSample($id_uuid) {
	if ($_SESSION['batchuuid']) {
		$batchuuid = $_SESSION['batchuuid'];
		$result = mysql_query("delete FROM `batch_quality` WHERE id_uuid = '$id_uuid' and `id_batch` = '$batchuuid'");
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}

function cleardestination($id) {
	$items_update = mysql_query("UPDATE `items` set destination = '', `name_last_updated` = '" . $GLOBALS['sps']->username . "' where id  = '$id'");
	if (!$items_update) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} else {
		$location_update = mysql_query("UPDATE `locations` set date_moved = CURDATE(), 'name_created' = '" . $GLOBALS['sps']->username . "' where id_item  = '$id' and date_moved is NULL");
		if (!$location_update) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}
function clearlocation($id) {
		$location_update = mysql_query("UPDATE `locations` set date_moved = CURDATE() where id  = '$id' and date_moved is NULL");
		if (!$location_update) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}

function subjectArray($id_subject) {
	// add the subject to an array o' subjects
	$_SESSION['id_subject'] = $id_subject;
	if (!isset($_SESSION['subject_array'])) {
		$subject_array = array();
	} else {
		$subject_array = $_SESSION['subject_array'];
	}
	if (!in_multi_array($id_subject, $subject_array)) {
		array_push($subject_array, $id_subject);
		$_SESSION['subject_array'] = $subject_array;
	}
}

function boxArray($id) {
	// add the box to an array o' boxes
	$_SESSION['boxid'] = $id;
	if (!isset($_SESSION['box_array'])) {
		$box_array = array();
	} else {
		$box_array = $_SESSION['box_array'];
	}
	if (!in_multi_array($id, $box_array)) {
		$id_array = array(
			'id' => $id,
			'dest' => dest($id) ,
			'divX' => divX($id) ,
			'divY' => divY($id)
		);
		array_push($box_array, $id_array);
		$_SESSION['box_array'] = $box_array;
	}
}

function shelfArray($id) {
	// add the box to an array o' boxes
	$_SESSION['shelfid'] = $id;
	if (!isset($_SESSION['shelf_array'])) {
		$shelf_array = array();
	} else {
		$shelf_array = $_SESSION['shelf_array'];
	}
	if (!in_multi_array($id, $shelf_array)) {
		$id_array = array(
			'id' => $id,
			'dest' => dest($id) ,
			'divX' => divX($id) ,
			'divY' => divY($id)
		);
		array_push($shelf_array, $id_array);
		$_SESSION['shelf_array'] = $shelf_array;
	}
}

function rackArray($id) {
	// add the box to an array o' boxes
	$_SESSION['rackid'] = $id;
	//        if (!$_SESSION['rack_array']) {
	$rack_array = array();
	//        } else {
	//       $rack_array = $_SESSION['rack_array'];
	//        }
	if (!in_multi_array($id, $rack_array)) {
		$id_array = array(
			'id' => $id,
			'dest' => dest($id) ,
			'divX' => divX($id) ,
			'divY' => divY($id)
		);
		array_push($rack_array, $id_array);
		$_SESSION['rack_array'] = $rack_array;
	}
}

function retFieldname($comment, $table) {
	$query = mysql_query("SHOW FULL COLUMNS FROM $table where comment like '$comment'");
	$tableinfo = mysql_fetch_assoc($query);
	return $tableinfo['Field'];
	mysql_free_result($query);
}

function retFieldcomment($field, $table) {
	$query = mysql_query("SHOW FULL COLUMNS FROM $table where Field like '$field'");
	$tableinfo = mysql_fetch_assoc($query);
	return $tableinfo['Comment'];
	mysql_free_result($query);
}

function duplicateContainer() {
if (!isset($_SESSION['containerid'])) {
		return false;
		exit;
	} else {
		$id =  $_SESSION['containerid'];
		$new_uuid = new_uuid();
		$result = mysql_query("insert into items (id_uuid,id_parent,id_study,destination,id_visit,shipment_type,sample_type,divX,divY,type,name_last_updated) 
			select '$new_uuid',id_uuid,id_study,destination,id_visit,shipment_type,sample_type,divX,divY,type, '" . $GLOBALS['sps']->username . "' as name_last_updated
		 	from items where id = " . $id . "");
		$newid = mysql_insert_id();
	}
	if ($newid > '0') {
		echo "$newid labels spooled";
		spoolLabel($newid,'items',2);
		ftpFiles();
	} else {
		return false;
	}

}
function replaceBox($id) {
	$box_array = $_SESSION['box_array'];
	$new_array = array();
		$new_uuid = new_uuid();
		$result = mysql_query("insert into items (id_uuid,id_parent,id_study,destination,id_visit,shipment_type,sample_type,divX,divY,type,name_last_updated) 
			select '$new_uuid',id_uuid,id_study,destination,id_visit,shipment_type,sample_type,divX,divY,type, '" . $GLOBALS['sps']->username . "' as name_last_updated 
			from items where id = " . $id . "");
		$newid = mysql_insert_id();
		if ($newid > '0') {
	if (in_multi_array($id, $box_array)) {
			foreach(array_keys($box_array) as $k) {
				if ($box_array[$k]['id'] != $id) {
					$id_array = array(
						id => $box_array[$k]['id'],
						dest => $box_array[$k]['dest'],
						divX => $box_array[$k]['divX']
					);
				} else {
					$id_array = array(
						id => $newid,
						dest => dest($newid) ,
						divX => divX($newid)
					);
				}
				array_push($new_array, $id_array);
			}
			$_SESSION['containerid'] = $newid;
			$_SESSION['box_array'] = $new_array;
		} else if ($_SESSION['containertype'] == 'box') {
			$_SESSION['containerid'] = $newid;
			$_SESSION['box_array'] = array($newid);
		}
	}
}

function fix_date($string) {
	$unixdate = strtotime($string);
	$sqltime = date("Y-m-d", $unixdate);
	return $sqltime;
}

/**
 *  Create a new uuid and add it to the items table with type 'unassigned' and set
 *  $_SESSION['containertype'] = 'unassigned';
 *	$_SESSION['containerid'] = $id;
 *	$_SESSION['divX_'.$id] = 0;
 *
 *
 */
function new_uuid($insert=false) {
	if (function_exists('uuid_create')) {
		uuid_create($v4);
		uuid_make($v4, UUID_MAKE_V4);
		uuid_export($v4, UUID_FMT_STR, $v4String);
		$uuid =  $v4String;
	} else {
		$uuid =  sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

		// 32 bits for "time_low"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
	
	if ($insert) {
		// insert the new uuid
		$inTable = 'items';
		if (isset($_SESSION['id_study'])) {
			$id_study = $_SESSION['id_study'];
		} else {
			$id_study = '';
		}
		if (isset($_SESSION['sample_type'])) {
			$sample_type = $_SESSION['sample_type'];
		} else {
			$sample_type = '';
		}
		if (isset($_SESSION['shipment_type'])) {
			$shipment_type = $_SESSION['shipment_type'];
		} else {
			$shipment_type = ''; 
		}
		if (isset($_SESSION['id_visit'])) {
			$id_visit = $_SESSION['id_visit'];
		} else {
			$id_visit = '';
		}
		$id = insUuid($uuid,'unassigned',$id_study,$sample_type,$shipment_type,$id_visit);
		$_SESSION['containertype'] = 'unassigned';
		$_SESSION['containerid'] = $id;
		$_SESSION['divX_'.$id] = 0;
	}
		return $uuid;
}

function num2chr($a) {
    if(!isset($a) or (strlen(trim($a)) == 0))  {
		return " ";
	}
	if ($a < 27) {
		return strtoupper(chr($a + 96));
	} else {
		while ($a > 26) {
			$a = $a - 26;
			$a = strtoupper(chr($a + 96));
			return $a;
		}
	}
}

function in_multi_array($value, $array) {
	foreach($array as $key => $item) {
		// Item is not an array
		if (!is_array($item)) {
			// Is this item our value?
			if ($item == $value) return true;
		}
		// Item is an array
		else {
			// See if the array name matches our value
			//if ($key == $value) return true;
			// See if this array matches our value
			if (in_array($value, $item)) return true;
			// Search this array
			else if (in_multi_array($value, $item)) return true;
		}
	}
	// Couldn't find the value in array
	return false;
}

function fwBarcodePrint($id_barcode) {
	$result = mysql_query("SELECT id FROM items WHERE `id_barcode` = '$id_barcode' and destination = ''");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$count = mysql_num_rows($result);
	if ($count > '0') {
		echo $count . " unique records ";
		echo "<div><input type=\"button\" value=\"print all\" onclick=\"printarray('fwarray')\"></div>";
		$ids = array();
		$i = 1;
		while ($row = mysql_fetch_object($result)) {
			$id = $row->id;
			echo "<div><input type=\"button\" value=\"print #" . $i . "\" onclick=\"printlabel('" . $id . "','items')\" onmouseover=\"getItemId('" . $id . "')\"></div>";
			array_push($ids, $id);
			$i++;
		}
		$_SESSION['fwarray'] = $ids;
	}
}

function spoolLabel($id, $table, $num) {
	$i = '0';
	$result = mysql_query("SELECT subjects.*,$table.* FROM $table left join subjects on ($table.id_subject = subjects.id_subject and $table.id_study = subjects.id_study) WHERE $table.`id` = '$id'");
	if (!$result) {
		$result = mysql_query("SELECT $table.* FROM $table  WHERE $table.`id` = '$id' order by id");
		#        echo 'Could not run query: ' . mysql_error();
		#        exit;
		
	}
	while ($row = mysql_fetch_array($result)) {
		if ((!isset($row['copies'])) || ($row['copies'] > "0")) {
			$id_uuid = "---";
			$id_subject = "---";
			$id_visit = "---";
			$id_alq = "---";
			$id_study = "---";
			$sample_type = "---";
			$date_visit = "---";
			$date_birth = "-";
			$shipment_type = "---";
			$id_barcode  = "---";
			$gender = "-";
			$id_uuid = $row['id_uuid'];
			$quant_init = $row['quant_init'] * 1000 . ' ul';
			$id_subject = $row['id_subject'];
			if ($table == 'items' && isset($row['destination'])) {
				$destination = $row['destination'];
			} else {
				$destination = "";
			}
			$id_visit = $row['id_visit'];
			$id_alq = $row['id_alq'];
			$id_study = $row['id_study'];
			$id_alq = $row['id_alq'];
			$id_barcode = $row['id_barcode'];
			$sample_type = $row['sample_type'];
			if(isset($row['date_birth'])) {
				$date_birth = $row['date_birth'];
			}
			if(isset($row['gender'])) {
				$gender = $row['gender'];
			}
			$type = $row['type'];
			$date_visit = $row['date_visit'];
			$shipment_type = $row['shipment_type'];
			if(isset($row['copies'])) {
				$copies = $row['copies'];
			}
			if (($table == 'items') && ($type == 'shelf')) {
				$freezer_result = mysql_query("SELECT freezer,subdiv1 FROM locations WHERE `id_item` = '$id'");
				if (!$freezer_result) {
					echo 'Could not run query: ' . mysql_error();
					exit;
				}
				while ($freezer_row = mysql_fetch_array($freezer_result)) {
					$shelf = $freezer_row['subdiv1'];
					$freezer = $freezer_row['freezer'];
				}
			}
			if (($date_visit == '0000-00-00') && ($shipment_type == 'TRANSDRY' && $date_freeze)) {
				$date = strftime('%m/%d/%Y', strtotime($date_freeze));
			} else {
				$date = strftime('%m/%d/%Y', strtotime($date_visit));
			}
			//	echo $id_uuid;
			//	$printfile = session_id().$id.$table.'.txt';
			//	$printfile = session_id().$num.str_replace(" ", "", microtime()).'.txt';
			$printfile = session_id() . sprintf("%05d", $num) . '.txt';
			$handling = fopen('/tmp/' . $printfile, 'w');
			$uuidShort = substr($id_uuid, 0, 8);
			if ($destination == 'biomek') {
				$template = 'templates/biomek-plate.php';
			} else {
				if (file_exists($GLOBALS['root_dir'] . '/include/templates/' . $id_study . '-' . $type . '.php')) {
					$template = 'templates/' . $id_study . '-' . $type . '.php';	
				} else {
				$template = 'templates/' . $type . '.php';
				}
			}
			include ($template);
			fwrite($handling, $labelData);
			
		}
	}
	mysql_free_result($result);
}


function spoolCommand($command) {
	$copies = '1';
	$printfile = session_id() . sprintf("%05d", $num) . '.txt';
	$handling = fopen('/tmp/' . $printfile, 'w');
	$id_uuid = $command;
	$uuidShort = $command;
	$template = 'templates/blank.php';
	include ($template);
	fwrite($handling, $labelData);
}

function ftpFiles($session_id = 0) {
	$return = 1;
	if($session_id == 0) {
		$session_id = session_id();
	}
	if ($_SESSION['printer_name'] == 'file') {
		zipFiles();
		exit;
	} else {
		if (isset($_SESSION['debug']) && $_SESSION['debug'] == 'yes') {
			$fileslike = '/tmp/' . $session_id . '*.txt';
			foreach(glob($fileslike) as $ftpfile) {
				echo $ftpfile;
				unlink($ftpfile);
			}
		} else {
			$ftphost = $_SESSION['printer_name'];
			// set up basic connection
			if ($conn_id = ftp_connect($ftphost, '21', '5')) {
			} else {
				echo '<p>Error:</p>';
				echo 'Could not connect to printer.';
				exit;
			}
			$ftp_user_name = 'root';
			$ftp_user_pass = 'root';
			// login with username and password
			$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
			if ($login_result != 1) {
					echo "couldn't connect to printer\n";
			} else {
				ftp_pasv($conn_id, true);
				// upload a file
				$fileslike = '/tmp/' .$session_id. '*.txt';
				//$fileslike = '/tmp/*.txt';
				foreach(glob($fileslike) as $ftpfile) {
					$sleep = 0;
//					echo $ftpfile;
					if (ftp_put($conn_id, '/execute/' . $ftpfile, $ftpfile, FTP_ASCII)) {
			//			print "  Labels printed";
						unlink($ftpfile);
						echo ".";
//						echo ".\n";
					} else {
						echo "There was a problem while printing $ftpfile\n";
						$return = 0;
						$sleep = $sleep + 1;
					}
			}
			// close the connection
			ftp_close($conn_id);
		}
			}
	}
return $return;
}


function logThis($event) {
if (is_array($event)) {
$event = var_export($event,true);
}
$event .= "\n";
$logfile = "/tmp/sps.log";
$fh = fopen($logfile, 'w') or die("can't open file");
fwrite($fh, $event);
fclose($fh);
}


function Download($filename) {
	$size=filesize('/tmp/'.$filename);
	header("Content-Type: application/octet-stream");
	header("Content-Length: $size");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Transfer-Encoding: binary");
	$fh = fopen('/tmp/'.$filename, "r");
	fpassthru($fh);
} // end function Download

function zipFiles() {
	$za = new ZipArchive;
	$zafile = substr(new_uuid() , 0, 8) . '.zip';
	$zapath = '/tmp/';
	if($za->open($zapath . $zafile,ZipArchive::CREATE) === true) {
		$fileslike = '/tmp/' . session_id() . '*.txt';
		$i = 1;
		foreach(glob($fileslike) as $labelfile) {
			if ($za->addFile($labelfile,'label_' . $i . '.txt')) {
				unlink($labelfile);
			} else {
				echo "There was a problem while adding $labelfile to archive\n";
				exit;
			}
			$i++;
		}
		$za->close();
		usleep(5000);
		echo '<a href=npc.php?action=download&file=' . $zafile . '>Download</>';
		echo '<script type="text/javascript">';
		echo 'window.location = "npc.php?action=download&file=' . $zafile . '";';
		echo '</script>';
	} else {
		echo "There was a problem while archiving to $zafile \n";
	}
}


function reconcile() {
	$id_study = $_SESSION['id_study'];
	$id_visit = $_SESSION['id_visit'];
	$sample_type = $_SESSION['sample_type'];
	echo $sample_type . " " . $id_visit;
	$destination = $_SESSION['destination'];
	$boxSelect = "SELECT items.id,id_uuid,id_guaid,sample_type,items.id_visit,items.id_subject,visit.date_create,items.date_visit,subdiv4,subdiv5 FROM `items` LEFT JOIN (locations) ON (`items`.`id`=`locations`.`id_item`)  LEFT JOIN (visit) ON (`visit`.`id_subject`=`items`.`id_subject`)  WHERE `id_study` = '$id_study' and items.id_visit = '$id_visit' and `visit`.`id_visit` = '$id_visit' and items.sample_type = '$sample_type' and items.destination = '$destination' order by id_subject";
	echo $boxSelect;
	$boxQuery = mysql_query($boxSelect, $conn);
	$filename = $id_visit . "-" . $sample_type;
	if (mysql_num_rows($boxQuery) > '0') {
		header("Content-Type: text/comma-separated-values");
		header("Content-Disposition: attachment; filename=" . $filename . ".txt");
		for ($i = 0;$i < mysql_num_rows($boxQuery);$i++) {
			extract(mysql_fetch_array($boxQuery));
			//				echo "$id_uuid,$id_subject,$date_receipt,$sample_type,$id_study,$id_visit\n";
			$count_query = mysql_query("SELECT count(*) as numsamps FROM `items` WHERE `id_visit` = '$id_visit' and `id_subject` = '$id_subject' and `sample_type` = '$sample_type'", $conn);
			if (!$count_query) {
				echo 'Could not run query: ' . mysql_error();
				exit;
			}
			$typecount = (mysql_result($count_query, 0, "numsamps"));
			mysql_free_result($count_query);
			if ($id_uuid) {
				$uuid = $id_uuid;
			} else {
				$uuid = new_uuid();
			}
			if (inCohort($id_subject)) {
				$incohort = '1';
			} else {
				$incohort = '0';
			}
			$days = (strtotime($date_create) - strtotime($date_visit)) / 86400 + 1;
			echo "$uuid,$id_guaid,CRIC,$sample_type,$id_visit,$id_subject," . strftime('%m/%d/%Y', strtotime($date_create)) . "," . num2chr($subdiv4) . ",$subdiv5,$days,$incohort,$typecount\n";
		}
	}
	mysql_free_result($boxQuery);
}

/**
 * Get information from the items and locations table for a given item id
 * @param integer $id
 * @return array|'false'
 */
function getItemInformation($id) {
		$statement = "SELECT items.id_study, items.id_subject, items.id_visit, items.type, items.id, items.id_uuid, items.consumed, items.timestamp, locations.id as id_location, locations.freezer, locations.subdiv1, locations.subdiv2, locations.subdiv3, locations.subdiv4, locations.subdiv5, locations.id_container
		from items left join locations on locations.id_item = items.id WHERE items.id = '$id' and locations.date_moved is null";
		$result = mysql_query($statement);
		if (mysql_affected_rows() == 0) {
				return false;
		}
		else {
				$row = mysql_fetch_array($result);
				return $row;
		}
}

/**
 * Get information from the items and locations table for a given box id
 * @param integer $id
 * @return array|'false'
 */
function getBoxInfo($id) {
		$statement = "SELECT id_study, id_visit, type, sample_type, shipment_type, id, id_uuid, consumed, timestamp, divX, divY, destination ";
		$statement .= "from items WHERE items.id = '$id'";
		$result = mysql_query($statement);
		if (mysql_affected_rows() == 0) {
				return false;
		}
		else {
				$row = mysql_fetch_array($result);
				return $row;
		}
}

/**
 * Prints an error message if the item is not scanned into a rack or if it scanned into the wrong container type
 * @param integer $boxid
 * @param bool $isABox Changes the error message text
 */
function checkContainerBox($boxid, $isABox = true) {
	if ($isABox) $name = "box";
	else $name = "item's box";
	$checkStatement = "SELECT items.type, items.id, items.id_uuid, locations.subdiv2 as subdiv from locations left join items on locations.id_container = items.id WHERE locations.id_item = '$boxid' and locations.date_moved is null";
	$checkResult = mysql_query($checkStatement);
	if (mysql_affected_rows() == 0) {
		echo "<div class=\"alert-error\">Warning: This $name is not scanned into a rack.</div>";
		return false;
	} else {
		$checkRow = mysql_fetch_array($checkResult);
		if ($checkRow['type'] != "rack") echo "<div class=\"alert\">Error: This $name is scanned into the wrong container type: " . $checkRow['type'] . "</div>";
		echo "<br/>Rack " . $checkRow['subdiv'] . ": " . $checkRow['id_uuid'];
		return $checkRow['id'];
	}
}

/**
 * Prints an error message if the item is not scanned into a shelf or if it scanned into the wrong container type
 * @param integer $rackid
 * @param bool $isARack Changes the error message text
 */
function checkContainerRack($rackid, $isARack = true) {
	if ($isARack) $name = "rack";
	else $name = "item's rack";
	$checkStatement = "SELECT items.type, items.id, items.id_uuid, locations.subdiv1 as subdiv from locations left join items on locations.id_container = items.id WHERE locations.id_item = '$rackid' and locations.date_moved is null";
	$checkResult = mysql_query($checkStatement);
	if (mysql_affected_rows() == 0) {
		echo "<div class=\"alert-error\">Warning: This $name is not scanned into a shelf.</div>";
		return false;
	} else {
		$checkRow = mysql_fetch_array($checkResult);
		if ($checkRow['type'] != "shelf") echo "<div class=\"alert\">Error: This $name is scanned into the wrong container type: " . $checkRow['type'] . "</div>";
		echo "<br/>Shelf " . $checkRow['subdiv'] . ": " . $checkRow['id_uuid'];
		return $checkRow['id'];
	}
}

/**
 * Prints an error message if the item is not scanned into a freezer or if it scanned into the wrong container type
 * @param integer $freezerid
 * @param bool $isAShelf Changes the error message text
 */
function checkContainerShelf($shelfid, $isAShelf = true) {
	if ($isAShelf) $name = "shelf";
	else $name = "item's shelf";
	$checkStatement = "SELECT items.type, locations.freezer from locations left join items on locations.id_container = items.id WHERE locations.id_item = '$shelfid' and locations.date_moved is null";
	$checkResult = mysql_query($checkStatement);
	if (mysql_affected_rows() == 0) {
		echo "<div class=\"alert-error\">Warning: This $name is not scanned into a freezer.</div>";
		return false;
	} else {
		$checkRow = mysql_fetch_array($checkResult);
		if (strlen($checkRow['freezer']) == 0) echo "<div class=\"alert\">Error: This $name is not scanned into a freezer.</div>";
		echo "<br/>Freezer: " . $checkRow['freezer'];
		return true;
	}
}


/**
 * Get location information for a given item.
 * Displays an error if not checked in
 *
 * @param string $id
 * @param string $type ['box'|'rack'|'shelf']
 */

function displayLocationInformation($id, $type) {
		$iteminfo = getItemInformation($id);
		$itemuuid = $iteminfo['id_uuid'];
		
		if($type == 'box') {
				$boxid = $id;
				echo "Box " . $iteminfo['subdiv3'] . ": $itemuuid";
				echo "<i><small>";
				if ($rackid = checkContainerBox($boxid)) if ($shelfid = checkContainerRack($rackid, false)) checkContainerShelf($shelfid, false);
				echo "</small></i>";
		}
		else if($type == 'rack'){
				$rackid = $id;
				echo "Rack " . $iteminfo['subdiv2'] . ": $itemuuid";
				echo "<i>";
				if ($shelfid = checkContainerRack($rackid)) checkContainerShelf($shelfid, false);
				echo "</i>";
		}
		else if($type == 'shelf') {
				$shelfid = $id;
				echo "Shelf " . $iteminfo['subdiv1'] . ": $itemuuid";
				echo "<i>";
				checkContainerShelf($shelfid);
				echo "</i>";
		}
}

/**
 *Returns the UUID for a given id in the 'items' table
 * @param string $id
 *
 * @return String If successful, returns the uuid.  Else returns false.
 **/
function getUUID($id) {
    $result = mysql_query("select id_uuid from items where id=$id");
    if (!$result)
        return false;
    
    $data = mysql_fetch_array($result);
    return $data['id_uuid'];
}

/**
 * Checks that two items have the same sample_type, shipment_type, and id_visit (items do not need to be boxes or tubes)
 * Prints an error message if items do not match if $_SESSION['noalert'] is not set
 * 
 * @param integer $boxid id in the items table
 * @param integer $tubeid id in the items table
 * @return boolean
 */
function checkContainerMatch($boxid, $tubeid) {
	$sample_types = mysql_query("select sample_type,id_visit,shipment_type from items where id = '$tubeid' or  id = '$boxid' group by sample_type,shipment_type,id_visit;");
	if (!$sample_types) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (!isset($_SESSION['noalert']) && (mysql_num_rows($sample_types) > 1)) {
		echo "<div class=\"alert\">Warning: tube/box mismatch</div>";
	}
}

/**
 * Get the value of a field in the items table for a given item, optionally allow the user to change the value of the field
 * @param integer $id
 * @param string $field
 * @param string $val
 * @param integer $width
 * @param bool $changable
 * @param bool $showDesc
 *
 * @return string html for this widget
 */
function showval($id, $field, $val, $width, $changable, $showDesc = false) {
		$table = 'items';
		$labelWidth = 110;

		// check to see if the field is in the 'filter' list, if so use a drop down
		$filterKeys = retArrayParams();
		
		$return = '<div style="font-size:smaller;"><table class = "left_column" width=300><tr>';
		
		if($showDesc)
				$return .= "<td width=100>$field</td>";
        
		if ($changable == 'yes') {
				// check that the field exists
				$fieldInfoResult = mysql_query("describe $table $field");
				if(!$fieldInfo = mysql_fetch_array($fieldInfoResult)) {
						$return .= '<div class="content" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgrey">';
				}
				
				// create the correct kind of edit widget based on field type
				if ($fieldInfo['Type'] == "binary(1)") { // bool
						if ($val == 0)
								$val = 'false';
						if ($val == 1)
								$val = 'true';
						$return .= '<td><div class="content" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen;">' . $val;
						$return .= "<script type='text/javascript'>
								new Ajax.InPlaceCollectionEditor('" . $field . "',
										'npc.php?action=itemed',
										{collection:[[0, 'false'], [1, 'true']],
										externalControl: 'left_column', formClassName: '$controlName', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&id=" . $id . "&field=" . $field . "'}})</script>";
						$return .= "</td></div>";
				}
				elseif (in_array($field, $filterKeys)) {
						$filterValues = retArrayParamValues($field);
						$filterString = "[";
						foreach($filterValues as $value) {
								$filterString .= "['$value', '$value'],";
						}
						$filterString = substr($filterString, 0, strlen($filterString)-1);
						$filterString .= "]";
						//echo $filterString;

						$return .= '<td><div class="right_column" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen;">' . $val;
						$return .= "<script type='text/javascript'>
								new Ajax.InPlaceCollectionEditor('" . $field . "',
										'npc.php?action=itemed',
										{collection: " . $filterString . ",
										externalControl: 'left_column', formClassName: '$controlName', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&id=" . $id . "&field=" . $field . "'}})</script>";
						$return .= "</td></div>";
 
				}
				else {  // just display a text edit
						$return .= '<td><div class="content" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen;">' . $val;
						$return .= "<script type='text/javascript'>
								new Ajax.InPlaceEditor('" . $field . "',
										'npc.php?action=itemed',
										{formClassName: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&id=" . $id . "&field=" . $field . "'}})</script>";
						$return .= "</td></div>";
				}
        }
		else {
				// not editing, just display the value
                $return .= '<td><div class="content" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgrey">' . $val . '</div></td>';
        }
        $return .= '</tr></table></div>';
	return($return);
}
function iOSDetect() {
   $browser = strtolower($_SERVER['HTTP_USER_AGENT']); // Checks the user agent
   if(strstr($browser, 'iphone') || strstr($browser, 'ipod') || (strstr($browser, 'ipad')  && !(strstr($browser, 'os 6'))))   {
      $device = 'ios';
   } else { 
      $device = 'default';
    }	
   return($device);
}
?>
