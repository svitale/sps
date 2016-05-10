<?php 
$todays_date = date("Y-m-d"); 
$last_month = $startpoint = date("Y-m-d", strtotime("$todays_date -1 month"));
if (!isset($_SESSION['datestart'])) {
$_SESSION['datestart'] = $last_month;
}
if (!isset($_SESSION['dateend'])) {
$_SESSION['dateend'] = $todays_date;
}
$dateend = $_SESSION['dateend'];
$datestart = $_SESSION['datestart'];
print selectStudy();
print selectPrinter();
print selectParam('id_study','Substudy');
print selectParam('shipment_type');
print selectParam('id_visit');
print selectParam('sample_type','Sample type');
//print selectParam('treatment','Treatment')
//filter('treatment', 'cohort', 'text');
//print "<button value='go' id='go' onclick='location.reload()'>go</button>";
filter('id_subject', 'cohort', 'text');
print "<button value='go' id='go' onclick='location.reload()'>go</button>";

