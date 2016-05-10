<?php
/**
 * Get widget that displays and allows editing of values of selected fields for a given row in 'items' or 'batch_quality' table
 * Uses showfield() function
 * @param integer $id
 * @param string $table
 *
 * @return string html for this widget
 **/

function Detail($id, $table) {
        global $sps;
        $obfuscatable =  false;
        if($table == 'items' && $sps->highlighted && $sps->highlighted['randomize']) {
            $randomize_array = $sps->highlighted['randomize'];
            if(in_array($id,$randomize_array)) {
                $obfuscatable = true;
            }
        }
		$return = '';
		$unknownval = "-"; // what to display if the entry is null
        $result = mysql_query("SELECT * FROM `$table` WHERE `id` = '$id'");
        if (!$result) {
                return 'Could not run query: ' . mysql_error();
                exit;
        }
        if (mysql_affected_rows() < 1) {
                return "No matches for id '$id'.";
                exit;
        }
        extract(mysql_fetch_array($result));
        if ($quant_cur > 0) {
        } else {
                $quant_cur = "0";
        }
        if ($quant_init > 0) {
        } else {
                $quant_init = "0";
        }
        //generate background color based on subject_id
        if (isset($id_subject) && $type == 'tube') {
                $x = $id_subject;
                $fx = (pi() * $x / 10000000);
                $gx = ((pi() * ($x) * (1 / 10)));
                $r = round(128 * (1 + sin($fx)));
                $g = round(128 * (1 + cos($fx)));
                $b = round(130 + 32 * (1 + cos($gx)));
                $bgColor = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
                $bgColor = genColor($x);
        } else {
                $bgColor = 'rgb(250,250,250)';
        }
        $return .= '<div style="width:auto; background-color:' . $bgColor . ';">';
        if (isset($type)) {
                $return .= showfield($id, 'Type', $type, '12', 'no', true);
        }
        if (isset($id_study) && $type == 'tube') {
                $return .= showfield($id, 'id_study', $id_study , '12', 'no', true);
                $return .= showfield($id, 'id_barcode', $id_barcode , '12', 'yes', true);
        } else if (isset($id_study) && $type != 'tube') {
                $return .= showfield($id, 'id_study', $id_study , '12', 'yes', true);
		} else {
                $return .= showfield($id, 'id_study', $unknownval , '12', 'no', true);
		}
        if (isset($id_uuid)) {
                $return .= showfield($id, $type.' ID', substr($id_uuid, 0, 8) , '12', 'no', true);
        }
        if (isset($destination) && $destination != "") {
                $return .= showfield($id, 'destination', $destination, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'destination', $unknownval, '12', 'yes', true);
        }
        if (isset($sample_type) && ($sample_type != '')) {
                $return .= showfield($id, 'sample_type', $sample_type, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'sample_type', $unknownval, '12', 'yes', true);
        }
		if (isset($hemolyzation) && ($hemolyzation != '')) {
                $return .= showfield($id, 'hemolyzation', $hemolyzation, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'hemolyzation', $unknownval, '12', 'yes', true);
        }
        if (($type == 'tube') || ($type == 'box' && studyGroupsBy($id_study) == 'id_subject')) {
                $return .= showfield($id, 'id_subject', $id_subject, '12', 'yes', true);
        }
        if (isset($collection_time) && ($collection_time != '')) {
                $return .= showfield($id, 'collection_time', $collection_time, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'collection_time', $unknownval, '12', 'yes', true);
        }
        if (isset($treatment) && ($treatment != '')) {
                $return .= showfield($id, 'treatment', $treatment, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'treatment', $unknownval, '12', 'yes', true);
        }
	if (isset($sample_name) && ($sample_name != '')) {
                $return .= showfield($id, 'sample_name', $sample_name, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'sample_name', $unknownval, '12', 'yes', true);
        }
	if (isset($sample_identifier) && ($sample_identifier != '')) {
                $return .= showfield($id, 'sample_identifier', $sample_identifier, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'sample_identifier', $unknownval, '12', 'yes', true);
        }
	if (isset($sample_collos_id) && ($sample_collos_id != '')) {
                $return .= showfield($id, 'sample_collos_id', $sample_collos_id, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'sample_collos_id', $unknownval, '12', 'yes', true);
        }
        if (isset($quant_thaws) && ($quant_thaws != '') && $type == 'tube') {
                $return .= showfield($id, 'quant_thaws', $quant_thaws, '12', 'yes', true);
		}
        if (isset($quant_cur) && ($quant_cur != '') && $type == 'tube') {
                $return .= showfield($id, 'quant_cur', $quant_cur, '12', 'yes', true);
		}
        if (isset($quant_init) && ($quant_init != '') && $type == 'tube') {
                $return .= showfield($id, 'quant_init', $quant_init, '12', 'yes', true);
		}
        if (isset($id_visit) && ($id_visit != '')) {
                $return .= showfield($id, 'id_visit', $id_visit, '12', 'yes', true);
        } else {
                $return .= showfield($id, 'id_visit', $unknownval, '12', 'yes', true);
	}
        if (isset($date_visit) && ($date_visit != '0000-00-00')) {
                $return .= showfield($id, 'date_visit', $date_visit, '10', 'yes', true);
        } else {
                $return .= showfield($id, 'date_visit', $unknownval, '10', 'yes', true);
	}
        if ($type == 'box') {
            if(!$label_text) {
                $label_text = '-';
            }
            $return .= showfield($id, 'label_text', $label_text , '10', 'yes', true);
        }
		
		if (isset($consumed) && ($type == 'tube')) {
                $return .= showfield($id, 'consumed', $consumed, '12', 'yes', true);
        }
		if (isset($notes) && ($type == 'tube')) {
                $return .= showfield($id, 'notes', $notes, '12', 'yes', true);
        }

		if($table == 'items' && $type != 'shelf') {
				$return .= "<div id='inventory'>";
				$inventory = mysql_query("SELECT id,id_container,subdiv4,subdiv5 FROM locations where `id_item` = '$id' and date_moved is null");
				if (!$inventory) {
						return 'Could not run query: ' . mysql_error();
						exit;
				}
				while($inventory_row = mysql_fetch_array( $inventory )) {
				$position =  num2chr($inventory_row['subdiv4']) . $inventory_row['subdiv5'];

				$containerid = substr(retUuid($inventory_row['id_container']),0,8);
                		$return .= showfield($id, 'Position', $position, '12', 'no', true);
                		$return .= showfield($id, 'Container ID', $containerid, '12', 'no', true);
				$return .= '<div style="font-size:smaller;">';
				$return .= "<input class='btn btn-warning btn-sm' type=\"button\" value=\"clear position\" onclick=\"clearlocation('".$inventory_row['id']."')\">";
				$return .= "</div>";
				}
		}
		if($table == 'items' && $type == 'tube') {
				$sql = "SELECT id,id_mrn,lab,id_rungroup,id_assay,fulfilled,timestamp FROM orders where `id_item` = '$id'";
				$result = mysql_query($sql);
				if (!$result) {
						return 'Could not run query: ' . mysql_error();
						exit;
				}
				while($row = mysql_fetch_array( $result )) {
				if ($row['id_mrn']) {
				$id_mrn = $row['id_mrn'];
				} else {
				$id_mrn = '-';
				}
				$return .= "<div id='order_'".$row['id'].">";
				$return .= showfield($row['id'], 'id_mrn', $id_mrn, '12', 'yes', true, 'orders');
                		$return .= showfield($row['id'], 'order date', $row['timestamp'], '12', 'no', true, 'orders');
				$return .= '<div style="font-size:smaller;">';
				$return .= "<input type=\"button\" value=\"delete order\" onclick=\"deleteorder('".$row['id']."', {onSuccess: getItemId(" . $id .")})\">";
				$return .= "</div>";
				}
		}
		if($table == 'items' && $type == 'tube') {
				$sql = "SELECT id,id_assay,value,id_retest,units,timestamp FROM results where `id_item` = '$id' and id_assay = 'dip'";
				$result = mysql_query($sql);
				if (!$result) {
						return 'Could not run query: ' . mysql_error();
						exit;
				}
				while($row = mysql_fetch_array( $result )) {
//				$return .= "<div id='order_".$row['id_mrn'].">";
				if ($row['value']) {
				$value = $row['value'];
				} else {
				$value = '-';
				}
				if ($row['id_retest']) {
					$id_retest = $row['id_retest'];
				} else {
					$id_retest = '-';
				}
				$return .= "<div id='result_'".$row['id'].">";
				$return .= showfield($row['id'], 'value', $value, '4', 'yes', true, 'results');
				$return .= showfield($row['id'], 'id_retest', $id_retest, '4', 'yes', true, 'results');
				$return .= '</div>';
				$return .= '<div style="font-size:smaller;">';
				$return .= "<input type=\"button\" value=\"delete result\" onclick=\"deleteresult('".$row['id']."')\">";
				$return .= "</div>";
				}
		}
	$return .= "</div>";
	$return.= '<div style="font-size:smaller;">';
        $return.= '<input class="btn btn-primary btn-sm" type="button" value="new order" onclick="neworder(\'' . $id . '\',\'' . $table .'\')">';
        $return.= '<input class="btn btn-primary btn-sm" type="button" value="UTP" onclick="newresult(\'' . $id . '\',\'' . $table .'\',\'dip\')">';
        //if this is obfuscatable - show the obfuscate button, otherwise show aliquot
        if($obfuscatable) {
            $return.= '<div><input class="btn btn-primary btn-sm" type="button" value="obfuscate" onclick="var store=new Store;store.obfuscate(' . $id . ',\'items\')"></div>';
        } else {
            $return.= "<div style='width: 138px;'><input class='btn btn-primary btn-sm' type='button' value='aliquot' onclick=$('aliquot_editor').show();>";
            $return.= '  <span id="aliquot_editor" style="display: none">';
            $return.= '    <span class="parent" id="make_daughters"  style="width: 38px; background-color: lightgrey"">0</span>';
            $return.= '  </span>';
            $return.= '</div>';
	    $return.= "<script type='text/javascript'>
                new Ajax.InPlaceEditor('make_daughters', 'util/make_daughters.php',{size: 3, callback: function(form, value) { return 'daughters=' + escape(value)+'&id=" . $id . "&table=".$table ."'}})</script>";
        }
        $return.= '<input class="btn btn-primary btn-sm" type="button" value="print" onclick="printlabel(' . $id . ',\'items\')">';
	return $return;
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
function showfield($id, $field, $val, $width, $changable, $showDesc = false, $table = 'items') {
		$labelWidth = 110;
		$div = $field . $id;

		// check to see if the field is in the 'filter' list, if so use a drop down
		$filterKeys = retArrayParams();
		
		$return = '<div style="background:lightgrey; font-size:smaller;"><table class = "left_column" width=300><tr>';
		
		if($showDesc)
				$return .= "<td width=100>$field</td>";
        
		if ($changable == 'yes') {
				// check that the field exists
				$fieldInfoResult = mysql_query("describe $table $field");
				if(!$fieldInfo = mysql_fetch_array($fieldInfoResult)) {
						$return .= '<div class="content" id="' . $div .'" style="width:' . ($width * 10) . 'px; background-color: lightgrey">';
				}
				
				// create the correct kind of edit widget based on field type
				if ($fieldInfo['Type'] == "binary(1)") { // bool
						if ($val == 0)
								$val = 'false';
						if ($val == 1)
								$val = 'true';
						$return .= '<td><div class="content" id="' . $div .'" style="width:' . ($width * 10) . 'px; background-color: lightgreen;">' . $val;
						$return .= "<script type='text/javascript'>
								new Ajax.InPlaceCollectionEditor('" . $div ."',
										'npc.php?action=ed',
										{collection:[[0, 'false'], [1, 'true']],
										externalControl: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&table=" . $table . "&id=" . $id . "&field=" . $field . "'}})</script>";
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

						$return .= '<td><div class="right_column" id="' . $div . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen;">' . $val;
						$return .= "<script type='text/javascript'>
								new Ajax.InPlaceCollectionEditor('" . $div . "',
										'npc.php?action=ed',
										{collection: " . $filterString . ",
										externalControl: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&table=" . $table . "&id=" . $id . "&field=" . $field . "'}})</script>";
						$return .= "</td></div>";
 
				}
				else {  // just display a text edit
						$return .= '<td><div class="content" id="' . $div . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen;">' . $val;
						$return .= "<script type='text/javascript'>
								new Ajax.InPlaceEditor('" . $div . "',
										'npc.php?action=ed',
										{formClassName: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&table=" . $table . "&id=" . $id . "&field=" . $field . "&table=" . $table . "'}})</script>";
						$return .= "</td></div>";
				}
        }
		else {
				// not editing, just display the value
                $return .= '<td><div class="content" id="' . $div . '" style="width:' . ($width * 10) . 'px; background-color: lightgrey">' . $val . '</div></td>';
        }
        $return .= '</tr></table></div>';
	return($return);
}
?>
