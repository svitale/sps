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
?>
<i><a href=npc.php?action=data&format=csv&type=export>Export Results to CSV</a></i>
<?php
print selectStudy();
print selectParam('id_study');
print selectParam('shipment_type');
print selectParam('id_visit');
print selectParam('sample_type');
filter('id_subject', 'cohort', 'text');
//filter('id_subject', 'cohort', 'text');
print "<button value='go' id='go' onclick='location.reload()'>go</button>";
