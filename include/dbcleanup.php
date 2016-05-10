<?php
function filterSQL($tmptable,$instrument) {
	$sqlArray = array();
	$filter = "";
	if (isset($instrument['qc_samples'])) {
		array_push($sqlArray,'update `'.$tmptable.'` set uqc = "sample" where qc="'.$instrument['qc_samples'].';"');
		$filter .= ' and uqc = "sample" ';
	}
	if (isset($instrument['qc_controls'])) {
		array_push($sqlArray,'update `'.$tmptable.'` set uqc = "control" where qc="'.$instrument['qc_controls'].'";');
		$filter .= ' and uqc != "control" ';
	}
	if (isset($instrument['qc_standards'])) {
		array_push($sqlArray,'update `'.$tmptable.'` set uqc = "standard" where qc="'.$instrument['qc_standards'].'";');
		$filter .= ' and uqc != "standard" ';
	}
	if (isset($instrument['rungroup'])) {
		array_push($sqlArray,'update `'.$tmptable.'` set id_rungroup = "'.$instrument['rungroup'].'" where id_rungroup is null;');
	}
	if (isset($instrument['name'])) {
	array_push($sqlArray,'update `'.$tmptable.'` set id_instrument = "'.$instrument['name'].'";');
	}
	if (isset($instrument['assay'])) {
	array_push($sqlArray,'update `'.$tmptable.'` set id_assay = "'.$instrument['assay'].'";');
	}
	if (isset($instrument['study'])) {
	array_push($sqlArray,'update `'.$tmptable.'` set id_study = "'.$instrument['study'].'";');
	}
	return $sqlArray;
}
//matching ids
function replaceContents($tmptable,$array ) {
	$sqlArray = array();
		foreach ($array as $assay=>$valueArray) {
			foreach ($valueArray as $oldvalue=>$newvalue) {
				array_push($sqlArray,'update `'.$tmptable.'` set '.$assay.' = "'.$newvalue.'" where '.$assay.' = "'.$oldvalue.'";');
                }
        }
return $sqlArray;
}

function formatUuids($tmptable) {
	$sqlArray = array();
	array_push($sqlArray,'update `'.$tmptable.'` set id_uuid = lower(concat(substr(id_uuid,1,8),"-",substr(id_uuid,9,4),"-",substr(id_uuid,13,4),"-",substr(id_uuid,17,4),"-",substr(id_uuid,21,12))) where id_uuid REGEXP "[A-Ha-h0-9]{32}"');
	return $sqlArray;
}


function matchOrders($tmptable) {
	$sqlArray = array();
	$filter = "";
	array_push($sqlArray,'update `'.$tmptable.'` left join items on (items.id_uuid = `'.$tmptable.'`.id_uuid)  set `'.$tmptable.'`.id_study = items.id_study,`'.$tmptable.'`.id_subject = items.id_subject,`'.$tmptable.'`.uuid_parent = items.id_parent,`'.$tmptable.'`.id_visit = items.id_visit,`'.$tmptable.'`.sample_type = items.sample_type,`'.$tmptable.'`.date_visit = items.date_visit where (`'.$tmptable.'`.id_uuid is not null and `'.$tmptable.'`.id_study is null and `'.$tmptable.'`.id_subject is null and `'.$tmptable.'`.id_visit is null and `'.$tmptable.'`.sample_type is null) '.$filter.';');
	array_push($sqlArray,'update `'.$tmptable.'` left join batch_quality on (batch_quality.id_uuid = `'.$tmptable.'`.id_uuid)  set `'.$tmptable.'`.id_study = batch_quality.id_study,`'.$tmptable.'`.id_subject = batch_quality.id_subject,`'.$tmptable.'`.uuid_parent = batch_quality.id_parent,`'.$tmptable.'`.id_visit = batch_quality.id_visit,`'.$tmptable.'`.sample_type = batch_quality.sample_type,`'.$tmptable.'`.date_visit = batch_quality.date_visit where (`'.$tmptable.'`.id_uuid is not null and `'.$tmptable.'`.id_study is null and `'.$tmptable.'`.id_subject is null and `'.$tmptable.'`.id_visit is null and `'.$tmptable.'`.sample_type is null) '.$filter.';');
	return $sqlArray;
}

function matchResults($tmptable,$instrument) {
	global $dsttable,$sps;
	$sqlArray = array();
	$filter = "";
	if($sps->active_study) {
		array_push($sqlArray,'update `'.$tmptable.'` left join items on (items.id_barcode2 = lower(`'.$tmptable.'`.id_barcode) and items.id_study  = "'.$sps->active_study->id_study.'")  set `'.$tmptable.'`.id_study = items.id_study,`'.$tmptable.'`.id_subject = items.id_subject,`'.$tmptable.'`.id_visit = items.id_visit,`'.$tmptable.'`.sample_type = items.sample_type,`'.$tmptable.'`.date_visit = items.date_visit where (`'.$tmptable.'`.id_barcode != "" and `'.$tmptable.'`.id_barcode != "0" and uqc = "sample" and (`'.$tmptable.'`.id_study is null or `'.$tmptable.'`.id_study = "unknown") and (`'.$tmptable.'`.id_subject is null or `'.$tmptable.'`.id_subject = "unknown") and `'.$tmptable.'`.id_visit is null and `'.$tmptable.'`.sample_type is null) '.$filter);
	}
	array_push($sqlArray,'update `'.$tmptable.'` left join items on (items.id_uuid = `'.$tmptable.'`.id_uuid)  set `'.$tmptable.'`.id_study = items.id_study,`'.$tmptable.'`.id_subject = items.id_subject,`'.$tmptable.'`.id_visit = items.id_visit,`'.$tmptable.'`.sample_type = items.sample_type,`'.$tmptable.'`.date_visit = items.date_visit where (`'.$tmptable.'`.id_uuid is not null and uqc = "sample" and `'.$tmptable.'`.id_study is null and `'.$tmptable.'`.id_subject is null and `'.$tmptable.'`.id_visit is null and `'.$tmptable.'`.sample_type is null) '.$filter);
	if($sps->active_study) {
		array_push($sqlArray,'update `'.$tmptable.'` left join items on (items.id_barcode = `'.$tmptable.'`.id_barcode and items.id_study  = "'.$sps->active_study->id_study.'")  set `'.$tmptable.'`.id_study = items.id_study,`'.$tmptable.'`.id_subject = items.id_subject,`'.$tmptable.'`.id_visit = items.id_visit,`'.$tmptable.'`.sample_type = items.sample_type where (`'.$tmptable.'`.id_barcode != "" and `'.$tmptable.'`.id_barcode != "0" and `'.$tmptable.'`.id_subject is null and uqc = "sample") '.$filter);
	}
	if ($dsttable == 'results_raw') {
		array_push($sqlArray,'update `'.$tmptable.'`  set uqc = "control" where layout_plate like "CTL%"');
		array_push($sqlArray,'update `'.$tmptable.'`  set uqc = "standard" where layout_plate like "STD%"');
		array_push($sqlArray,'delete from `'.$tmptable.'`  where id_barcode = "0" and id_uuid is null and id_subject is null and layout_plate is null');
	} else {
		array_push($sqlArray,'delete from `'.$tmptable.'`  where id_barcode = "0" and id_uuid is null and id_subject is null');
	}
	array_push($sqlArray,'delete from `'.$tmptable.'`  where id_barcode = "Name"');
//	array_push($sqlArray,'delete from `'.$tmptable.'`  where value REGEXP "[0-9]+" = 0');
if($dsttable=='results') {
	array_push($sqlArray,'delete from `'.$tmptable.'`  where id_uuid is null and id_subject is null and uqc = "sample"');
	array_push($sqlArray,'delete from `'.$tmptable.'`  where id_barcode = "Sample ID"');
}
	array_push($sqlArray,'update `'.$tmptable.'` set id_study = "QC" where id_study is NULL and (uqc = "qc_control" or uqc = "qc_standard")');
	array_push($sqlArray,'delete from `'.$tmptable.'`  where value is null');
	return $sqlArray;
}
