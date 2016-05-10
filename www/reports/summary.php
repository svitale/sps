<?php
include_once('lib.php');
//header("Content-type: application/vnd.ms-excel");
//header("Content-Disposition: attachment; filename=results.csv");
$id_visit = 'V3Y0';


function getAssays() {
$query = "SELECT id_assay from assays";
$result = mysql_query($query);
if (!$result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}
while ($row = mysql_fetch_object($result)) {
$array[] = $row->id_assay;
}
mysql_free_result($result);
return $array;
}

//$assay_array = getAssays();
$assay_array = array('FIB','Troponin-I','CysC','CRE');



//echo "<div>";
$i = 0;
while ($i < count($assay_array)) {
$assay = $assay_array[$i];
//echo "<div>".$assay."</div>";
$i++;
}
//echo "</div>";
//echo ",complete,comment";


$cohort_query = "SELECT id_subject from cohort order by id_subject limit 200";
$cohort_result = mysql_query($cohort_query);
if (!$cohort_result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $cohort_query;
    die($message);
}

echo '<div id="sumRow_head" class="sumrow">';
echo '<div id="sumCell_id" class="sumcell" style="width: 80px;"><b>';
echo 'ID';
echo '</b></div>';
   foreach ( $assay_array as $val ) {
echo '<div id="sumCella_'.$k.'" class="sumcell"><b>';
      echo substr($val,0,4);
echo '</b></div>';
      } 
echo '<div id="sumCell_issue" class="sumcell" style="width: 200px;">';
echo "<b>Notes</b>";
echo "</div>";
echo '</div>';


while ($cohort_row = mysql_fetch_assoc($cohort_result)) {
	$id_subject = $cohort_row['id_subject'];
	//$results_query = "SELECT id_assay,value from results where id_subject = '$id_subject' and id_visit = '$id_visit' and `ignore` !=1 order by datetime_assay desc";
	$results_query = "SELECT id_assay,value from results where id_subject = '$id_subject' and id_visit = '$id_visit' and `ignore` !=1 order by datetime_assay desc";
	$results_result = mysql_query($results_query);
	if (!$results_result) {
	    $message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $results_query;
	    die($message);
	}


$complete = 1;
$j = 0;
echo '<div id="sumRow_'.$i.'" class="sumrow">';
while ($j < count($assay_array)) {
${$assay} = "NULL";
$j++;
}
while($results_row = mysql_fetch_assoc($results_result))
{
if  (is_numeric($results_row['value'])) {
${$results_row['id_assay']} = round($results_row['value'], 2);
//${$results_row['id_assay']} = $results_row['value'];
 } else {
 ${$results_row['id_assay']} = $results_row['value'];
${$results_row['id_assay']} = $results_row['value'];
 }
}
echo '<div id="sumCell_'.$i.'" class="sumcell" style="width: 80px;">';
echo "$id_subject";
echo "</div>";
$j = 0;
while ($j < count($assay_array)) {
echo '<div id="sumCell_'.$i.'_'.$j.'" class="sumcell">';
$assay = $assay_array[$j];
echo ${$assay};
if (${$assay} == "NULL") {
$complete = 0;
}
echo "</div>";
$j++;
}
//echo '<div id="sumCell_complete'.$i.'" class="sumcell">';
//echo $complete;
//echo "</div>";
echo '<div id="sumCell_issue'.$i.'" class="sumcell" style="width: 200px;">';
       echo substr(retIssue($id_subject),0,20);
echo "</div>";
echo "</div>";
}
mysql_free_result($results_result);
mysql_free_result($cohort_result);
?>
