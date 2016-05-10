#!/usr/bin/php
<?php
include_once('/opt/sps/include/lib.php');
global $sps;
$printer_name = $argv[1];
$device = New PrintDev();
$device->printer_name = $printer_name;
$device->getPrinter();
$aliquots = array('1','2');
$volumes = array('.25','.50','.75','1','1.25','1.50','1.75','2','3','4','5','6','7','8','9','10');
$labels = array();
foreach($volumes as $volume) {
    $array = array('name'=>'Initial Volume','value'=>"$volume ml",'command'=>"npc:vol_init;$volume");
    array_push($labels,$array);
}
foreach($aliquots as $aliquot) {
    $array = array('name'=>"$aliquot Aliquot",'value'=>"$aliquot",'command'=>"npc:aliquot;$aliquot");
    array_push($labels,$array);
}
$job = New PrintJobs();
$job->printer_id =  $device->printer_id;
$status = $job->spoolCommandLabels($labels);
print_r($status);
?>
