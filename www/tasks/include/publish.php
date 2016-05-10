<?php
$todays_date = date("Y-m-d"); 
if (!isset($_SESSION['datestart'])) {
$_SESSION['datestart'] = $todays_date;
}         
if (!isset($_SESSION['dateend'])) {
$_SESSION['dateend'] = $todays_date;
}

if (!isset($_SESSION[group_by_sample])) {
$_SESSION[group_by_sample] = 'yes';
}
if (!isset($_SESSION[instrument])) {
$_SESSION[instrument] = 'ArchLIS';
}
if (!isset($_SESSION[id_study])) {
$_SESSION[id_study] = '%';
}

$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];
$id_study = $_SESSION[id_study];

//if (!isset($_SESSION['id_assay'])) {

// construct date portion of query
if (strtotime($datestart) < strtotime($dateend)) {
        $date_query = "datetime_assay > '$datestart 00:00:00' and datetime_assay < '$dateend 23:59:59'";
} else {
        $date_query = "datetime_assay like '$datestart %' ";
}

$query = "select distinct(id_assay)";
                $query .= "from results where ";
                $query .= " id_study like '$id_study' and ";
        if (isset($_SESSION['instrument'])) {
                $id_instrument = $_SESSION['instrument'];
                $query .= " id_instrument like '$id_instrument' and ";
                }
                $query .= $date_query;
                $query .= " order by id_assay;";

$id_assays = array();
$result = mysql_query($query);
while($row = mysql_fetch_assoc($result))
{
  array_push($id_assays, $row['id_assay']);
}
//$_SESSION['id_assay'] = array('Cardi_sFlt','_BNPSTAT','TnI II','MPO882010','proBNP','Cardi_PlGF','CRP16','Uric','CreaC');
$_SESSION['id_assay'] = $id_assays;
//}
                                     


?>
