<?php
include_once('include/config.php');
$sample_types = array('U_24Hr','PRB');
$id_subject = '01010204';
$id_visit = 'V3Y0';
	if (isset($showdiv)) {
	$wrapper =  "<div class=\"wrapper\" style=\"width:".(500 + (100 * (count($sample_types))) )."px\">";
	$left_column = "<div class=\"left_column\">";
	$content_column = "<div class=\"content\">";
	$right_column = "<div class=\"right_column\">";
	$end_column = "</div>";
	$end_row = "</div>\n";
	} else {
	$wrapper =  "";
	$left_column = ";";
	$content_column = ";";
	$right_column = ";";
	$end_column = ";";
	$end_row = "\n";
	}

		$query = "select id_subject,id_barcode,sample_type from items where id_subject = '$id_subject' and id_visit = '$id_visit' and (";
		$i = '1';
		foreach ($sample_types as $sample_type ) {
		$query .= "sample_type = '" . $sample_type. "'";
		if ($i < count($sample_types)) {
		$query .= " or ";
		$i++;
		}
		}
		$query .= ") order by sample_type";
		$result = mysql_query($query);
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
	 	}
		//*
		//our column header

		$id_subject = "Subject ID";
		foreach ($sample_types as $sample_type ) {
				$val_{$sample_type} = $sample_type;
				}
while ($row = mysql_fetch_object($result)) {
//* move the last stored values into the array since we've now got a new subject id
echo $row->sample_type;
}


?>
