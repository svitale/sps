<?php
include_once('config.php');


        $cmd = escapeshellcmd("xls2csv -q 0 -c; $tmpfile"); 
        $cmd .= " > $tmpfile.csv"; 
        @exec($cmd,$stdout,$errocode); 
//        unlink("$path/$xls_file"); 
        if ($errorcode > 0) return $errocode; 


	$tmptable = mysql_query('create temporary table if not exists results_'.session_id().' like results');
        if (!$tmptable) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
	$id_study = $_SESSION['id_study'];
if ($_SESSION['instrument'] == 'bn2') {
	$loadfile = mysql_query("LOAD DATA INFILE '$tmpfile.csv' INTO TABLE results_".session_id()." FIELDS TERMINATED BY ';' ignore 3 lines (id_barcode,qc,id_assay,value,units,@date_assay,@time_assay) set datetime_assay = CONCAT(STR_TO_DATE(@date_assay,GET_FORMAT(DATE,'EUR')),' ',@time_assay), id_study = '$id_study', id_lab = 'hill', id_instrument = 'bn2'");
        if (!$loadfile) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
}
?>
