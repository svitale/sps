<?php
include_once('include/config.php');
$id_visit  = $_SESSION['id_visit'];
$id_study  = $_SESSION['id_study'];
$assays  = $_SESSION['id_assay'];
$sets = '0';
if ($action == 'export') {
//
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=results.csv");
	$wrapper =  "";
	$left_column = ";";
	$content_column = ";";
	$right_column = ";";
	$end_column = ";";
	$end_row = "\n";
	unset($sample_type);
	} else {
//
	$sample_type  = $_SESSION['sample_type'];
	$wrapper =  "<div class=\"wrapper\" style=\"width:".(500 + (100 * (count($assays))) )."px\">";
	$left_column = "<div class=\"left_column\">";
	$content_column = "<div class=\"content\">";
	$right_column = "<div class=\"right_column\">";
	$end_column = "</div>";
	$end_row = "</div>\n";
	if (!isset($_SESSION['id_assay']) || ($_SESSION['id_assay'] == "")) {
		echo "please select an Assay";
		exit;
	}
	}

if (isset($sample_type)) {
$results = array();
$issue_status = array();
$issues_colors = array('#FFFFFF','#FFFFFF','#efb9b9','#b9efc2','#fbd5ff','#f3d8bf');

		$query = "select status,sample_status.id_subject as id_subject from sample_status left join (cohort)  on (cohort.id_subject = sample_status.id_subject) where cohort.id_subject > '01000000' and cohort.id_subject < '11010500' and sample_status.id_visit = 'V3Y0' and  sample_type = '$sample_type'";
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










		$query = "select results.id_subject,results.id_assay,results.datetime_assay,results.value,results.id,results.id_visit,share from cohort left join (results) on (cohort.id_subject = results.id_subject) where cohort.id_subject > '01000000' and cohort.id_subject < '11010500' and results.id_visit = 'V3Y0' and results.ignore != '1' and ( ";
//		$query = "select results.id_subject,results.id_assay,results.datetime_assay,results.value,results.id,results.id_visit,share from cohort left join (results) on (cohort.id_subject = results.id_subject) where cohort.id_subject > '07027229' and cohort.id_subject < '07027231' and results.id_visit = 'V3Y0' and results.ignore != '1' and ( ";
		$i = '1';
		foreach ($assays as $assay ) {
		$query .= "id_assay = '$assay' ";
		if ($i < count($assays)) {
		$query .= " or ";
		$i++;
		}
		}
		$query .= ") group by results.value, results.id_subject, results.id_assay, results.datetime_assay order by cohort.id_subject, id_assay";
		$result = mysql_query($query);
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
	 	}
		//*
		//our column header

		$id_subject = "Subject ID";
		foreach ($assays as $assay ) {
				$val_{$assay} = $assay;
				}
while ($row = mysql_fetch_object($result)) {
//* move the last stored values into the array since we've now got a new subject id
	if ($id_subject != $row->id_subject) {
	$subject_values =  $wrapper;
	$subject_values .= $left_column;
	$subject_values .= $id_subject;
	$subject_values .= $end_column;
	
		foreach ($assays as $assay ) {
				if ($action != 'export') {
				$subject_values .= "<div class=\"content\" style=\"background-color: \"".$issues_colors[$issue_status[$row->id_subject]]."\">";
				} else {
				$subject_values .= $content_column;
				}
				if (!isset($val_{$assay})) {
				$val_{$assay} = "NULL";
				$incomplete[$id_subject] = '1';
				}
				$subject_values .=  $val_{$assay};
				unset($val_{$assay});
				$subject_values .= $end_column;
		}




		if (retIssue($id_subject)) {
		$issue  = (retIssue($id_subject));
		} else {
		$issue = "-";
		}
		$subject_values .=  $right_column . substr($issue,0,22);
		if (substr($issue,0,22) != $issue ) {
		$subject_values .= "...";
		}
		$subject_values .= $end_column;
		$subject_values .= $end_row;

		$results[$id_subject] = $subject_values;
		unset($subject_values);
		}






//		$sum_results = $incomplete['01010397'];



		if (is_numeric($row->value)) {
	$val_{$row->id_assay} = round($row->value, 2);
		} else {
	$val_{$row->id_assay} = $row->value;
		}
	$id_subject = $row->id_subject;
	} 
	mysql_free_result($result);


		$query = "select id_subject from cohort where id_study = 'CRIC' order by id_subject";
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
		if (!$results[$row->id_subject]){
	$subject_values =  $wrapper;
	$subject_values .= $left_column;
	$subject_values .= $row->id_subject;
	$subject_values .= $end_column;
		foreach ($assays as $assay ) {
				if ($action != 'export') {
				$subject_values .= "<div class=\"content\" style=\"background-color: \"".$issues_colors[$issue_status[$row->id_subject]]."\">";
				} else {
				$subject_values .= $content_column;
				}
				$subject_values .= "<div onClick =\"window.location.href='npc.php?action=retbarcodes&id=".$row->id_subject."'\">NULL</div>";
				$subject_values .= $end_column;
				}
			
				if (retIssue($row->id_subject)) {
				$issue  = (retIssue($row->id_subject));
				} else {
				$issue = "-";
				}
				$subject_values .=  $right_column . substr($issue,0,22);
				if (substr($issue,0,22) != $issue ) {
				$subject_values .= "...";
				}
				$subject_values .= $end_column;

				$subject_values .= $end_row;
		$results[$row->id_subject] = $subject_values;
		$incomplete[$row->id_subject] = "2" ;
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

if ($action == 'export') {
		echo $sum_results;
	} else {
		echo "<div>".$sets." matching results</div>";
		echo "<div><a href='export.php?action=export'>export results</a></div>";
		echo $sum_results;
	}
//echo $status;

?>
