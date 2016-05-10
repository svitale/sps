<?php
lib('dbi');

/**
* Delete the study and all associated data.
* Get things back to square-one
*
* - Remove the study from 'studies'
* - Remove any rows in 'params' associated with the study
* - Remove any rows fro 'behavior' associated with the study
* - Remove any rows fro 'study_fields' associated with the study
* - Remove any rows fro 'filters' associated with the study
* - Remove any rows fro 'print_filter' associated with the study
*
**/
function rmStudy($id_study) {
	global $dbrw;
	print "removing study $id_study\n";
	$sql = "delete from studies where id_study = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		$sql = "delete from rc_params where id_study = '$id_study'";
	}

	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		exit;
	} 
	$sql = "delete from params where id in (select instudy.id_param id from (select id_param from filters left join params on filters.id_param = params.id where id_study = '$id_study') instudy left join (select id_param from filters left join params on filters.id_param = params.id where id_study != '$id_study') notinstudy  on instudy.id_param = notinstudy.id_param where notinstudy.id_param is null)";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		exit;
	} 
	$sql = "delete from behavior where id_study = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		exit;
	} 
	$sql = "delete from study_fields where id_study = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		exit;
	} 
	$sql = "delete from filters where id_study = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		exit;
	} 
	$sql = "delete from print_filter where study_id = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error();
		exit;
	} 
}


/**
* Add a row to study_fields.
* Indicates that if the field exists in the items or batch_quality table, it will be viewable and modifiable to the user.
*/
function addUncommonFields($id_study, $field_array) {
	print "adding uncommon fields\n";
	print_r($field_array);
	global $dbrw;
	foreach ($field_array as $order=>$field) {
		$sql = "insert into study_fields (`id_study`,`field`,`order`)  values ('$id_study','$field','$order')";
		print_r($sql);
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			exit;
		} 
	}
}

/**
* Create a new entry in the studies table
*
* $id_study - the string that is the study name
* $autoassign_cohort_flag - if true, then new cohorts packets can be generated on the crf page
*/
function addStudy($id_study, $autoassign_cohort_flag=false) {
	print "adding study $id_study, autoassign_cohort = $autoassign_cohort_flag create_by = $create_by\n";
	global $dbrw;

	$autoassign_cohort = "0";
	if ($autoassign_cohort_flag === true) {
		$autoassign_cohort = "1";
	}

	$sql = "insert into studies (id_study,study_name,autoassign_cohort) values ('$id_study','$id_study','$autoassign_cohort')";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	} 
}



function addRcParamsMap($params_value,$rc_name,$params_param) {
	global $id_study,$dbrw;
	$sql = "select params.id from params left join filters ";
	$sql .= "on params.id = filters.id_param ";
	$sql .= "where filters.id_study = '$id_study' ";
	$sql .= "and params.param = '$params_param' ";
	$sql .= "and params.value = '$params_value' ";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
		exit;
	}
	$num_rows = mysqli_num_rows($result);
	if ($num_rows == 0) {
		print "Error: Map not created - The parameter '$params_param = $params_value' does not yet exist\n";
	} else if ($num_rows > 1) {
		print "Error: Map not created - duplicate parameters found\n";
	} else if ($num_rows == 1) {
		$row = mysqli_fetch_object($result);
		$id_param  = $row->id;
		print "$rc_name is $id_param\n";
	}
	$sql = "select id from rc_params where id_study = '$id_study' and rc_name = '$rc_name'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
		exit;
	}
	$num_rows = mysqli_num_rows($result);
	if ($num_rows > 0) {
		print "Error: Map not created - The rc field '$rc_name' is already mapped\n";
	} else {
		$sql = "insert into rc_params (id_study,rc_name,id_param) values ('$id_study','$rc_name','$id_param')";
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			return false;
			exit;
		}
		print "Success: The rc_name:$rc_name  mapped to param:$id_param\n";
		return true;
	}
}

function addUser($id_study,$username) {
	print "adding user $username to study: $id_study\n";
	global $dbrw;
	$sql = "select username from users where `username` = '$username'";
	$result = mysqli_query($dbrw,$sql);
	$num_rows = mysqli_num_rows($result);
	if ($num_rows == 0) {
		$sql = "insert into users (`username`) value ('$username')";
		$result = mysqli_query($dbrw,$sql);
	}
	lib('Roles');
	$roles = New Roles();
	$roles->username = $username;
	$roles->id_study = $id_study;
	$roles->rolename = 'lab';
	$roles->grantRole();
	
}
function addSampleType($sample_type,$id_study,$crf_quant=1) {
	print "adding sample_type $sample_type\n";
	global $dbrw;
	
	//crf entries
	$sql = "select id from crf where `sample_type` = '$sample_type' and `id_study` = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) == 0) {
		$sql = "insert into crf (id_study,sample_type,quantity) values ('$id_study','$sample_type',$crf_quant)";
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			return false;
			exit;
		}
	}

	//add params and  filter entries
	$sql = "select id from params where param = 'sample_type' and binary `value` = '$sample_type'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
		exit;
	}
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('sample_type','$sample_type')";
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			return false;
			exit;
		}
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;
}

function addDestination($destination) {
	print "adding destination $destination\n";
	global $dbrw;
	
	//add params and  filter entries
	$sql = "select id from params where param = 'destination' and binary `value` = '$destination'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('destination','$destination')";
		$result = mysqli_query($dbrw,$sql);
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;
}

function addIdVisit($visit) {
	print "adding id_visit $visit\n";
	global $dbrw;
	
	//add params and  filter entries
	$sql = "select id from params where param = 'id_visit' and binary `value` = '$visit'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('id_visit','$visit')";
		$result = mysqli_query($dbrw,$sql);
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;
}

function addShipmentType($shipment_type) {
	print "adding shipment_type $shipment_type\n";
	global $dbrw;
	
	//add params and  filter entries
	$sql = "select id from params where param = 'shipment_type' and binary `value` = '$shipment_type'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('shipment_type','$shipment_type')";
		$result = mysqli_query($dbrw,$sql);
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;
}

function addInstrument($instrument) {
	print "adding instrument $instrument\n";
	global $dbrw;
	
	//add params and  filter entries
	$sql = "select id from params where param = 'id_instrument' and binary `value`	 = '$instrument'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('id_instrument','$instrument')";
		$result = mysqli_query($dbrw,$sql);
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;
}

function addSubStudy($id_study) {
	global $dbrw;
	
	//add params and  filter entries
	$sql = "select id from params where param = 'id_study' and binary `value` = '$id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('id_study','$id_study')";
		$result = mysqli_query($dbrw,$sql);
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;


}

function addSampleSource($sample_source,$id_study) {
	global $dbrw;
	
	//add params and  filter entries
	$sql = "select id from params where param = 'sample_source' and binary `value` = '$sample_source'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_param  = $row['id'];
		}
	} else {
		$sql = "insert into params (param,value) values ('sample_source','$sample_source')";
		$result = mysqli_query($dbrw,$sql);
		$id_param = mysqli_insert_id($dbrw);	
	}
	return $id_param;


}

function addProcess($id_study,$name,$description) {
	global $dbrw;
	
	//crf entries
	$sql = "select id,name,description from process_header where `id_study` = '$id_study' and `name` = '$name'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
		exit;
	}
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			if ($row['description'] == $description) {
				print "Alert: Process '$description' already exists, will not add it here\n";
				$id  = $row['id'];
			} else {
				print "Error: Process not added, already exists but description is different\n";
				return false;
				exit;
			}
		}
	} else {
		$sql = "insert into process_header (id_study,name,description) values ('$id_study','$name','$description')";
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			return false;
			exit;
		}
		$id = mysqli_insert_id($dbrw);	
	}
	return $id;
}

function addStudyFilter($id_study,$id_param) {
	global $dbrw;
	$sql = "select id from filters where id_study = '$id_study' and id_param = '$id_param'";
	$result = mysqli_query($dbrw,$sql);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_array($result)) {
			$id_filter  = $row['id'];
		}
	} else {
		$sql = "insert into filters (id_study,id_param) values ('$id_study','$id_param')";
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			return false;
			exit;
		}
	}
}
function addSampleTypeProcess($name,$description,$input,$output) {
	global $id_study, $paramIDs, $dbrw;
	$process_header_id = addProcess($id_study,$name,$description);
	if (!is_null($input)){
		$input_id_param = $paramIDs[$input];
		if (isset($paramIDs[$input]) && !is_null($paramIDs[$input])) {
			$sql = "insert into process_params (process_header_id,params_id,type) values ($process_header_id,$input_id_param,'input')";
			$result = mysqli_query($dbrw,$sql);
			if (!$result) {
				echo 'Could not run query: ' . mysqli_error($dbrw);
				return false;
				exit;
			} 
		}
	}
	if (!is_null($output)){
		$output_id_param = $paramIDs[$output];
		if (!is_null($paramIDs[$output])) {
			$sql = "insert into process_params (process_header_id,params_id,type) values ($process_header_id,$output_id_param,'output')";
			$result = mysqli_query($dbrw,$sql);
			if (!$result) {
				echo 'Could not run query: ' . mysqli_error($dbrw);
				return false;
				exit;
			} 
		}
	}
	return true;
}
function addStudyPrinters($printers) {
	print "adding printers\n";
	global $id_study,$dbrw;
	foreach ($printers as $printer) {
		$sql = "insert into print_filter (print_dev_id,study_id) values ('$printer','$id_study')";
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			return false;
			exit;
		} 
	}
}
