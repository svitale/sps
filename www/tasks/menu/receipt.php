<?php 
$todays_date = date("Y-m-d"); 
if (!isset($_SESSION['datestart'])) {
$_SESSION['datestart'] = $todays_date;
}
if (!isset($_SESSION['dateend'])) {
$_SESSION['dateend'] = $todays_date;
}
$dateend = $_SESSION['dateend'];
$datestart = $_SESSION['datestart'];
print selectStudy();
print selectPrinter();
print selectParam('shipment_type');
print selectParam('id_visit');
print selectParam('sample_type');
filter('id_subject', 'cohort', 'text');
print selectDateRange();
print '<a href="npc.php?action=retreceipt&type=csv">export results</a>';

