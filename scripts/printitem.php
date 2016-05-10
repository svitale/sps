#!/usr/bin/php
<?php
include_once('/opt/sps/include/lib.php');
global $sps;
$uuid = $argv[1];
$printer_name = $argv[2];
$table = 'items';
$device = New PrintDev();
$device->printer_name = $printer_name;
$device->getPrinter();

$item = new InventoryObject();
$item->id_uuid = $uuid;
$item->Fetcher();
print "spooling $uuid to $printer_name\n";
print_r($item);
if($device->printer_id && $item->id) {
    $job = New PrintJobs();
    $job->printer_id =  $device->printer_id;
    $status = $job->spoolPrintJob($item->id, $table);
    print_r($status);
} else {
    print "error\n";
}
?>
