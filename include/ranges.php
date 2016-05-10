<?php
lib('PHPExcel');
ini_set ( 'max_execution_time', 1200); 
global $valid_columns,$template_dir;
$valid_columns = array();
$valid_columns['results'] = array("id","id_barcode","id_uuid","id_subject","id_study","id_lab","id_instrument","id_assay","id_visit","id_rungroup","id_retest","value","units","datetime_assay","date_assay","time_assay","date_collection","date_visit","timestamp","qc","uqc","reviewed","calibrator","reagent","cleaner","ignore","share","notes","sample_type","shipment_type");
$valid_columns['results_raw'] = array("id","id_barcode","id_uuid","id_subject","id_study","id_lab","id_instrument","id_assay","id_visit","id_rungroup","id_retest","value","value_1","value_2","value_3","value_4","value_5","value_6","value_calculated","value_measured","units","datetime_assay","date_assay","time_assay","date_collection","date_visit","timestamp","qc","uqc","cv","reviewed","calibrator","reagent","cleaner","ignore","share","notes","sample_type","layout_plate","position_plate","position_source","layout_count");
$valid_columns['orders'] = array("id_uuid","id_mrn","id_acc");
$valid_columns['crf'] = array("id_uuid","id_study","id_subject","sample_type","date_visit","id_barcode","id_visit","date_receipt","shipment_type","id_lot","id_alq","sequence","subdiv4","subdiv5","quant_init","hemolyzation","label_text","notes","collection_time","treatment","sample_name","sample_identifier","sample_collos_id");
$valid_columns['vars'] = array("id_visit","sample_type","shipment_type","id_assay","id_study","id_rungroup","notes","units","date_assay","datetime_assay","id_instrument","name_plate","barcode_source","units_1","units_2","units_3","units_4","units_5","units_6","id_assay_1","id_assay_2","id_assay_3","id_assay_4","id_assay_5","id_assay_6");
$valid_columns['pull_requirements'] = array("id_visit","sample_type","shipment_type","id_assay","id_study","id_subject","date_visit","id_study");
$valid_columns['pull_vars'] = array("id_visit","sample_type","shipment_type","id_assay","id_study","id_subject","pull_name","pull_description");
function findNamedRanges($file,$parent='results',$include_value=false) {
	$namedArray = array();
        global $valid_columns;
	$objPHPExcel = PHPExcel_IOFactory::load($file);
	$objPHPExcel->setActiveSheetIndex(0);
	$objWorksheet = $objPHPExcel->getActiveSheet();
// find the named ranges and put them into $rangeArray
		foreach ($objPHPExcel->getNamedRanges() as $named) {
			$name = $named->getName();
			$range = $named->getRange();
			$tokstart = strtok($range,":");
			$tokend = strtok(":");
			$row['start'] = $objWorksheet->getCell($tokstart)->getRow();
			$col['start'] = $objWorksheet->getCell($tokstart)->getColumn();
			if ($tokend =="") {
				$row['end'] = $row['start'];
				$col['end'] = $col['start'];
			} else {
				$row['end'] = $objWorksheet->getCell($tokend)->getRow();
				$col['end'] = $objWorksheet->getCell($tokend)->getColumn();
			}
		$rangeArray[$name] = array('row'=>$row,'col'=>$col);
		}
	$contentsArray = array();
if(isset($rangeArray{$parent})) {
	for ( $i = ($rangeArray{$parent}{'row'}{'start'}); $i <= ($rangeArray{$parent}{'row'}{'end'}); $i++ ) {
		for ( $j = ($rangeArray{$parent}{'col'}{'start'}); $j <= ($rangeArray{$parent}{'col'}{'end'}); $j++ ) {
		$cell = $j . $i;
		$contentsArray{$i}{$j}{'cell'} = $cell;
		}
	}
	foreach ($objPHPExcel->getNamedRanges() as $named) {
		$name = $named->getName();
			if((in_array($name,$valid_columns[$parent])) && $rangeArray{$name}{'row'}{'start'} >= $rangeArray{$parent}{'row'}{'start'} && $rangeArray{$name}{'row'}{'end'} <= $rangeArray{$parent}{'row'}{'end'} && $rangeArray{$name}{'col'}{'start'} >= $rangeArray{$parent}{'col'}{'start'} && $rangeArray{$name}{'col'}{'end'} <= $rangeArray{$parent}{'col'}{'end'}) {
				for ( $i = ($rangeArray{$name}{'row'}{'start'}); $i <= ($rangeArray{$name}{'row'}{'end'}); $i++ ) {
					for ( $j = ($rangeArray{$name}{'col'}{'start'}); $j <= ($rangeArray{$name}{'col'}{'end'}); $j++ ) {
						$namedArray{$i}{$j}{'label'} = $name;
						$namedArray{$i}{$j}{'cell'} = $contentsArray{$i}{$j}{'cell'};
						if ($include_value) {
							$value = $objWorksheet->getCell($j.$i)->getValue();
							$namedArray{$i}{$j}{'value'} = $value;
						}
					}
				}
			}
	}
}
//$namedArray[$parent] = $contentsArray;
//return  $contentsArray;
$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);
return $namedArray;
}
function makeNamedRanges($file,$parent='results') {
        global $valid_columns;
	$objPHPExcel = PHPExcel_IOFactory::load($file);
	$objPHPExcel->setActiveSheetIndex(0);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$col['start'] = 'A';
	$row['start'] = '1';
	$col['end'] = $objPHPExcel->getActiveSheet()->getHighestColumn(); // e.g. "EL" 
	$row['end'] = $objPHPExcel->getActiveSheet()->getHighestRow(); // e.g. "EL" 
	for ($column = $col['start']; $column <= $col['end']; $column++ ) {
			$return = $objWorksheet->getCell($column.'1')->getValue();
	  //print $column . '1' . $return . '\n';
        }
//	$highestRow = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestRow();  


// find the named ranges and put them into $rangeArray
/**
			if((in_array($name,$valid_columns[$parent])) && $rangeArray{$name}{'row'}{'start'} >= $rangeArray{$parent}{'row'}{'start'} && $rangeArray{$name}{'row'}{'end'} <= $rangeArray{$parent}{'row'}{'end'} && $rangeArray{$name}{'col'}{'start'} >= $rangeArray{$parent}{'col'}{'start'} && $rangeArray{$name}{'col'}{'end'} <= $rangeArray{$parent}{'col'}{'end'}) {
				for ( $i = ($rangeArray{$name}{'row'}{'start'}); $i <= ($rangeArray{$name}{'row'}{'end'}); $i++ ) {
					for ( $j = ($rangeArray{$name}{'col'}{'start'}); $j <= ($rangeArray{$name}{'col'}{'end'}); $j++ ) {
						$namedArray{$i}{$j}{'label'} = $name;
						$namedArray{$i}{$j}{'cell'} = $contentsArray{$i}{$j}{'cell'};
					}
				}
			}
	}
**/
//return $namedArray;
}
function formatArray($tmpfile,$array) {
        $rangeArray = array();
// Source Spreadsheet stuff
//	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
//	$objReaderRO = PHPExcel_IOFactory::createReader('Excel2007');
	$objReader = PHPExcel_IOFactory::createReaderForFile($tmpfile);
	$objReaderRO = PHPExcel_IOFactory::createReaderForFile($tmpfile);
	$objReader->setReadDataOnly(false);
	$objReaderRO->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($tmpfile);
	$objPHPExcelRO = $objReaderRO->load($tmpfile);
	$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
	$objWorksheetRO = $objPHPExcelRO->setActiveSheetIndex(0);
	$objWorksheetRO = $objPHPExcelRO->getActiveSheet();
	$objWorksheet = $objPHPExcel->getActiveSheet();
//	foreach ($array as $group=>$rangeArray) {
//	foreach ($rangeArray as $row=>$rowArray) {
	foreach ($array as $row=>$rowArray) {
		foreach($rowArray as $column=>$columnArray) {
			$return = $objWorksheet->getCell($column.$row)->getValue();
//rewrite '0' as '0.0' to avoid loss of zero values
			if ($return == '0') {
				$return = '0.0';
			}
			if ($return  != '') {
			if (isset($columnArray['value']))  {
				$return = str_replace($columnArray['value'],'',$return);
			}
				if (stristr($columnArray['label'],'time_'))  {
					$formatCode = $objWorksheet->getStyle($column.$row)->getNumberFormat()->getFormatCode();
					$formattedString = PHPExcel_Style_NumberFormat::toFormattedString($return, $formatCode);
					$return = $formattedString;
				} else if (stristr($columnArray['label'],'value')) {
					$formatCode = $objWorksheet->getStyle($column.$row)->getNumberFormat()->getFormatCode();
					$formattedString = PHPExcel_Style_NumberFormat::toFormattedString($return, $formatCode);
					$return = $formattedString;
					
				} else if (stristr($columnArray['label'],'date_')) {
					$formattedString = PHPExcel_Style_NumberFormat::toFormattedString($return, 'YYYY-MM-DD');
					$return = strtotime($formattedString);
					$return = date( 'Y-m-d', $return ); 
				}
        			if (is_object($return)) { // if the field happens to contain richtext
					$return = $objWorksheetRO->getCell($column.$row)->getValue();
				} else if (substr($return,0,1) == "=") {
					$return = $objWorksheetRO->getCell($column.$row)->getCalculatedValue();
				}
				$rangeArray[$row][$column]['value']= $return;
				$rangeArray[$row][$column]['label']= $array[$row][$column]['label'];
			}
		}
				//$rangeArray[$row][$column]['value']= $return;
		
	}
//	$array[$group] = $rangeArray;
//}
	$objPHPExcel->disconnectWorksheets();
	unset($objPHPExcel);
	$objPHPExcelRO->disconnectWorksheets();
	unset($objPHPExcelRO);
	return $rangeArray;
}

function mergeColumns($array,$columnlabel1,$columnlabel2,$newlabel) {
	foreach ($array as $row=>$rowArray) {
		foreach($rowArray as $column=>$columnArray) {
			if ($columnArray['label'] == $columnlabel1)	{
				$column1 = $column;
				$value1 = $columnArray['value'];	
				unset($array[$row][$column]);
			}
			if ($columnArray['label'] == $columnlabel2)	{
				$column2 = $column;
				$value2 = $columnArray['value'];	
				unset($array[$row][$column]);
			}
		}
		if (isset($value1) || isset($value2)) {
			$array[$row][$column1.$column2]['value'] = $value1 . " " .$value2;	
			$array[$row][$column1.$column2]['label'] = $newlabel;	
		}
	}
return $array;
}



function fixDates($groupArray) {
		$found_date = false;
		foreach ($groupArray as $row=>$rowArray) {
               	 $labels = array();
               	 $values = array();
               	 foreach($rowArray as $column=>$columnArray) {
               	         if (($columnArray['label'] == 'datetime_assay') && (isset($columnArray['value'])))  {
				$groupArray[$row][$column]['value'] =  date('Y-m-d H:i:s',strtotime($groupArray[$row][$column]['value']));
				$found_date = true;
               	         }
               	 }
		}
		if ($found_date == false) {
		foreach ($groupArray as $row=>$rowArray) {
               	 $labels = array();
               	 $values = array();
               	 foreach($rowArray as $column=>$columnArray) {
               	         if (($columnArray['label'] == 'date_assay') && (isset($columnArray['value'])))  {
				$groupArray[$row][$column]['value'] =  date('Y-m-d H:i:s',strtotime($groupArray[$row][$column]['value']));
				$groupArray[$row][$column]['label'] =  'datetime_assay';
				$found_date = true;
               	         }
               	 }
		}
		}
return $groupArray;
}

function cleanArray($array) {
		foreach ($array as $row=>$rowArray) {
               	 $labels = array();
               	 $values = array();
               	 foreach($rowArray as $column=>$columnArray) {
               	         if (($columnArray['label'] == 'id_barcode') && (isset($columnArray['value'])))  {
				$array[$row][$column]['value'] =  preg_replace("/[^a-zA-Z0-9\-\s]/", "", $array[$row][$column]['value']);
               	         }
               	 }
		}
return $array;
}

function flattenRange($array) {
	$returnArray = array();
        $i=0;
	foreach ($array as $row=>$rowArray) {
		foreach($rowArray as $column=>$columnArray) {
			if ((isset($columnArray['label'])) && (isset($columnArray['value'])) && ($columnArray['value'] != "")) {
				$returnArray[$i][$columnArray['label']] = $columnArray['value'];
			}
		}
		$i++;
	}
return $returnArray;
}
function flattenedArraytoSql($array,$tmptable) {
	$sqlArray = array();
	foreach ($array as $record) {
    //if ($record'layout_plate'] == 'STD5') {
    //}
		// if the count column is populated we'll have to copy
		// some data from row to row
		if (isset($record['layout_count'])) {
	// set all counts to 2 for now
			$count = 2;
			//unset($record['layout_count']);
			$parent_record = $record;
			$count--;
		} else if (isset($count) && $count >0) {
			if (isset($parent_record['id_barcode']) && !isset($record['id_barcode'])) {
				$record['id_barcode'] = $parent_record['id_barcode'];
			}
			if (isset($parent_record['layout_plate']) && !isset($record['layout_plate'])) {
				$record['layout_plate'] = $parent_record['layout_plate'];
			}
			$count--;
		}
			unset($record['layout_count']);
		$keys = implode(array_keys($record),'`,`');
		$values = implode(array_values($record),'\',\'');
		$sqlArray[] = 'insert into `'.$tmptable.'` (`'.$keys.'`) values (\''.$values.'\');';
	}
	return $sqlArray;
}
function rangeInsertSQL($array,$tmptable) {
	$sqlArray = array();
		foreach ($array as $row=>$rowArray) {
			$labels = array();
			$values = array();
			foreach($rowArray as $column=>$columnArray) {
				if ((isset($columnArray['label'])) && (isset($columnArray['value'])) && ($columnArray['value'] != "")) {
					array_push($labels,$columnArray['label']);
					array_push($values,$columnArray['value']);
				}
			}
			if (count($labels) > 0) {
				$sql = "insert into `".$tmptable."` (".implode($labels,',').") values (\"".implode($values,'","')."\");";
				array_push($sqlArray,$sql);
			}
		}
	if (count($sqlArray) > 0) {
	return $sqlArray;
	} else {
	return false;
	}
}
function mergeVars($rangeArray,$varsArray) {
    array_walk($rangeArray,'insertVars',$varsArray);
    return $rangeArray;
}
function insertVars(&$row, $key, $varsArray) {
    foreach ($varsArray as $var) {
    $row = array_merge($row,$var);
    }
}
function rangeUpdateSQL($array,$tmptable) {
	$sqlArray = array();
		foreach ($array as $row=>$rowArray) {
			$labels = array();
			$values = array();
			foreach($rowArray as $column=>$columnArray) {
				if ((isset($columnArray['label'])) && (isset($columnArray['value'])) && ($columnArray['value'] != "")) {
				$sql = "update `".$tmptable."` set ".$columnArray['label']." = '".$columnArray['value']."';";
				array_push($sqlArray,$sql);
				}
			}
		}
	if (count($sqlArray) > 0) {
	return $sqlArray;
	} else {
	return false;
	}
}
?>
