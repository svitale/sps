<?php
lib('Xlt');
include($GLOBALS['root_dir'] . '/www/tasks/include/cPull.php');
$id_study = $sps->active_study->id_study;

$Pulls = new Pulls();
$active_pulls = $Pulls->GetActivePulls($id_study);
foreach ($active_pulls as $active_pull) {
  $pull = $active_pull;
  $pull['id'] = $active_pull['pull_header_id'];
  $pull['name'] = $active_pull['pull_name'];
  $pull['description'] = $active_pull['pull_description'];
  $array[] = $pull;
}
header('content-type: application/json; charset=utf-8');
print json_encode($array);
?>
