<?php
if (isset($_SESSION['id_visit']) ){
$id_visit  = $_SESSION['id_visit'];
} else {
$id_visit  = "V3Y0";
}
	if (!isset($_SESSION['id_assay']) || ($_SESSION['id_assay'] == "")) {
		echo "please select an Assay";
	} else {
$array_subjects = array();
$id_study  = $_SESSION['id_study'];
$array_assays  = $_SESSION['id_assay'];
$sets = '0';
if ($format == 'csv') {
//
	$wrapper =  "";
//	$left_column = ";";
	$left_column = "";
	$content_column = ",";
	$right_column = ",";
	$end_column = "";
	$end_row = "\n";
	unset($sample_type);
	} else {
//
	$sample_type  = $_SESSION['sample_type'];
	$wrapper =  "<div class=\"wrapper\" style=\"width:".(500 + (100 * (count($array_assays))) )."px\">";
	$left_column = "<div class=\"left_column\">";
	$content_column = "<div class=\"content\">";
	$right_column = "<div class=\"right_column\">";
	$end_column = "</div>";
	$end_row = "</div>\n";
	}

if (isset($sample_type)) {
$results = array();
$issue_status = array();
$issues_colors = array('#FFFFFF','#FFFFFF','#efb9b9','#b9efc2','#fbd5ff','#f3d8bf');
		$query = "select status,sample_status.id_subject as id_subject from sample_status left join (cohort)  on (cohort.id_subject = sample_status.id_subject)  where sample_status.id_visit = '$id_visit' and  sample_type = '$sample_type'";
        $result = mysql_query($query);
        if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
	while ($row = mysql_fetch_object($result)) {
	 $issue_status[$row->id_subject] = $row->status;
	}
	mysql_free_result($result);
}

		$query = "select results.id_subject,results.id_assay,results.id_lab,results.datetime_assay,items.date_visit,results.value,results.units,results.id,results.id_visit,results.id_barcode,results.sample_type,results.share from cohort left join (results) on (cohort.id_subject = results.id_subject) left join items on (results.id_uuid = items.id_uuid) where ";
		 $query .= "results.ignore != '1'"; 
//		 $query .= "cohort.id_subject > '01000000' ";
//		 $query .= "and cohort.id_subject < '11010500' ";
if (isset($_SESSION['id_visit'])) {
		 $query .= "and results.id_visit = '$id_visit' ";
		 }
		 $query .= "and results.id_study = '$id_study' ";
//		 $query .= "and results.qc is null ";
		 $query .= "and results.units != 'Abs.' ";
		$query .= "and ( ";
		$i = '1';
		foreach ($array_assays as $assay ) {
		$query .= "id_assay = '$assay' ";
		if ($i < count($array_assays)) {
		$query .= " or ";
		$i++;
		}
		}
//		$query .= ") group by results.value, results.id_subject, results.id_assay, results.datetime_assay order by cohort.id_subject, id_assay";
		$query .= ") group by results.value, results.id_subject, results.id_assay, results.datetime_assay order by cohort.id_subject, id_assay, datetime_assay desc";
//		$query .= ") group by results.value, results.id_subject, results.id_assay, results.datetime_assay order by id_assay";
		$result = mysql_query($query);
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
	 	}
		//*
		//our column header

		$id_subject = "Subject ID";
		$sum_results = $wrapper  . $left_column . "ID" .$end_column ;
		foreach ($array_assays as $assay ) {
		$sum_results .= $content_column . $assay . $end_column;
				$val_{$assay} = $assay;
				$val_{$assay} = array(); 
				$val_{$assay}[0] = $assay;
				if ($format == 'csv') {
		$sum_results .= $right_column . "Units" . $end_column;
		$sum_results .= $right_column . "Visit" . $end_column;
		$sum_results .= $right_column . "Sample Type" . $end_column;
		$sum_results .= $right_column . "Visit Date" . $end_column;
		$sum_results .= $right_column . "Barcode" . $end_column;
		$sum_results .= $right_column . "Assay Date" . $end_column;
		$sum_results .= $right_column . "Lab" . $end_column;
				}
				}
		$sum_results .= $right_column . "Notes" . $end_column;
		$sum_results .= $end_row;

while ($row = mysql_fetch_object($result)) {
//* move the last stored values into the array since we've now got a new subject id
	
		if (is_numeric($row->value)) {
	$val_{$row->id_assay}[$row->id_subject] = round($row->value,2);
		} else {
	$val_{$row->id_assay}[$row->id_subject] = $row->value;
		}
				if ($format == 'csv') {
	$resid_{$row->id_assay}[$row->id_subject] = $row->id;
	$units_{$row->id_assay}[$row->id_subject] = $row->units;
 	$visit_{$row->id_assay}[$row->id_subject] = $row->id_visit;
 	$sampletype_{$row->id_assay}[$row->id_subject] = $row->sample_type;
 	$barcode_{$row->id_assay}[$row->id_subject] = $row->id_barcode;
	if ($row->datetime_assay > '0000-00-00') {	
 	$assaydate_{$row->id_assay}[$row->id_subject] = date('Y-m-d H:i:s', strtotime($row->datetime_assay));
	}

	if ($row->date_visit > '0000-00-00') {	
 	$visitdate_{$row->id_assay}[$row->id_subject] = date('Y-m-d', strtotime($row->date_visit));
	}



 	$lab_{$row->id_assay}[$row->id_subject] = $row->id_lab;
	}

	} 
	mysql_free_result($result);


		$query = "select id_subject from cohort where id_study = '$id_study' order by id_subject + 0";
		$result = mysql_query($query);
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
			}
	$subject_values =  $wrapper;
	$subject_values .= $left_column;
	$subject_values .= "Subject";
	$subject_values .= $end_column;
		while ($row = mysql_fetch_object($result)) {
		array_push($array_subjects,$row->id_subject);
		if (!$results[$row->id_subject]){
	$subject_values =  $wrapper;
	$subject_values .= $left_column;
	$subject_values .= $row->id_subject;
	$subject_values .= $end_column;
		foreach ($array_assays as $assay ) {
				//begin value
//<div>
				if ($format != 'csv') {
				$subject_values .= "<div class=\"content\" style=\"background-color: \"".$issues_colors[$issue_status[$row->id_subject]]."\">";
				} else {
				$subject_values .= $content_column;
				}


				if (isset($val_{$assay}[$row->id_subject])) {
				$subject_values .= $val_{$assay}[$row->id_subject];
				} else {
					if ($format != 'csv') {
				$subject_values .= "<div onClick =\"window.location.href='npc.php?action=retbarcodes&id=".$row->id_subject."'\">N/A</div>";
					} else {
				$subject_values .= "N/A";
					}
				$incomplete[$row->id_subject] = "1" ;
				}
				$subject_values .= $end_column;
				//end value



				if ($format == 'csv') {


				if (isset($units_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $units_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}

				if (isset($visit_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $visit_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}
				if (isset($sampletype_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $sampletype_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}


				if (isset($visitdate_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $visitdate_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}




				if (isset($barcode_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $barcode_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}

				if (isset($assaydate_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $assaydate_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}




				if (isset($lab_{$assay}[$row->id_subject])) {
				$subject_values .= $content_column . $lab_{$assay}[$row->id_subject] . $end_column;
				} else {
				$subject_values .= $content_column . $end_column;
				}




				}



				}
			
//				if (retIssue($row->id_subject)) {
//				$issue  = (retIssue($row->id_subject));
//				} else {
				$issue = "-";
//				}
				$subject_values .=  $right_column . substr($issue,0,22);
				if (substr($issue,0,22) != $issue ) {
				$subject_values .= "...";
				}
				$subject_values .= $end_column;
				$subject_values .= $end_row;
				$results[$row->id_subject] = $subject_values;
		} 
if ((($incomplete[$row->id_subject]) > '0') && ($_SESSION['show_incomplete'] == 'yes')) {
//		$sum_results .= $row->id_subject . "  ".  $incomplete[$row->id_subject] . "\n";
//		$sum_results .= $incomplete[$row->id_subject] ." ". $row->id_subject. "\n";
		$sum_results .= $results[$row->id_subject];
		$sets++;
		} else if ((!isset($incomplete[$row->id_subject])) && ($_SESSION['show_complete'] == 'yes')) {
		$sum_results .= $results[$row->id_subject];
		$sets++;
	}
}
	mysql_free_result($result);

$status = 	"<div>";
$status .= 	"<script type=\"text/javascript\>";
$status .= 	"setstatus('";
$status .= 	$sets;
$status .= 	"')";
$status .=	"</script>";
$status .= 	"</div>";

if ($format == 'csv') {
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
//mysql_free_result($delete);


$insert = "insert into snapshots (id_publish,id_subject,id_assay,id_visit,id_results) values ";
foreach ($array_assays as $assay ) {
	foreach ($array_subjects as $subject ) {
		if (isset($resid_{$assay}[$subject])) {
		$insert .= "('$id_publish','$subject','$assay','$id_visit','".$resid_{$assay}[$subject]."'),";
		} else {
		$insert .= "('$id_publish','$subject','$assay','$id_visit','0'),";
		}
	}
}
$insert = rtrim($insert,",");
$insert .= ";";
//echo $insert;
//echo $commaend;
$create = mysql_query($insert);
if (!$create) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=snapshot".$id_publish.".csv");
echo "snapshot: $id_publish";
//	echo 'https://ctrc.itmat.upenn.edu/TCLab/sps-0.6/npc.php?action=publish&id='.$id_publish;
	echo $end_row;
	echo $sum_results;
	} else {
		echo "<div>".$sets." matching results</div>";
		echo "<div><a href='npc.php?action=publish'>export results</a></div>";
		echo $sum_results;
	}
//echo $status;
}
?>
