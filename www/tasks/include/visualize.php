<?php
$todays_date = date("Y-m-d"); 
if (!isset($_SESSION['datestart'])) {
$_SESSION['datestart'] = $todays_date;
}         
if (!isset($_SESSION['dateend'])) {
$_SESSION['dateend'] = $todays_date;
}

function custom_data_array($query) {
$result = mysql_query($query);
$returnArray = array();
while($row = mysql_fetch_assoc($result))
  array_push($returnArray, $row);
mysql_close();
return $returnArray;
}

function data_array() {
$query = "select count(*) as nofreezer,sample_type,shipment_type,id_visit,destination from items_cceb where id_site is null and share = 1 group by  sample_type,shipment_type,id_visit,destination order by destination,shipment_type,sample_type,id_visit";
$result = mysql_query($query);
$returnArray = array();
while($row = mysql_fetch_assoc($result))
{
$drilldown = 'SELECT DATE_FORMAT(date_visit,"%m-%Y") as VisitDate, count(id) as num_samples FROM items_cceb where ';
$drilldown .= "id_site is null and share = 1 and ";
$drilldown .= "sample_type = '".$row[sample_type]."' and ";
$drilldown .= "shipment_type = '".$row[shipment_type]."' and ";
$drilldown .= "id_visit = '".$row[id_visit]."' and ";
$drilldown .= "destination = '".$row[destination]."' ";
$drilldown .= " GROUP by VisitDate ";
$drilldown = urlencode($drilldown);
$row['graph'] = "<a href=npc.php?action=data&type=csv&query=".$drilldown.">visit dates</a>";
  array_push($returnArray, $row);
}
mysql_close();
return $returnArray;
}
?>
