<?php
lib('Printer');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (isset($_POST['type']) && $_POST['type'] != 'false') {
    $type = $_POST['type'];
} else {
    $type = null;
}
if (isset($_POST['table']) &&$_POST['table'] != 'false') {
    $table = $_POST['table'];
} else {
    $table = null;
}
if (isset($_POST['uuid']) && $_POST['uuid'] != 'false') {
    $id_uuid = $_POST['uuid'];
} else {
    $id_uuid = null;
}
if (isset($_POST['table_id']) != 'null') {
    $id = $_POST['table_id'];
} else {
   $id = null;
}
if (isset($_POST['batchid']) && $_POST['batchid'] != 'false') {
    $batchid = $_POST['batchid'];
} else if (isset($_SESSION['batchuuid'])) {
    $batchid = $_SESSION['batchuuid'];
} else {
    $batchid = null;
}


if (isset($_POST['subject']) && $_POST['subject'] != 'all' && $_POST['subject'] != 'false') {
    $subject = $_POST['subject'];
} else {
    $subject = null;
}

$printer = New PrintDev();
if (!$sps->printer) {
print "error: no printer selected";
exit;
}

$printer = $sps->printer;
$job = New PrintJobs();
$job->printer_id =  $printer->printer_id;
$job->type =  $type;
$job->subject =  $subject;

if ($batchid && preg_match("/[A-Ha-h0-9]{8}-[A-ha-h0-9]{4}-[A-Ha-h0-9]{4}-[A-ha-h0-9]{4}-[A-Za-z0-9]{12}/", $batchid)) {
    $status = $job->batchPrintJob($batchid);
} else if ($table) {
    if (!$id) {
        $object = New InventoryObject();
        $object->table = $table;
        $object->id_uuid = $uuid;
        $object->Fetcher();
        $id = $object->id;
    }
    $status = $job->spoolPrintJob($id, $table, $job=null);
} else if ($type == 'boxcontents') {
     lib('Process');
     $process = New Process();
     $container['id'] = $_POST['table_id'];
     $container['type'] = 'box';
     $process->container = $container;
     $contents = $process->retContentsArray();
     foreach ($contents as $tube) {
         $tubeid = $tube->id;
         $job = New PrintJobs();
         $job->printer_id =  $printer->printer_id;
         $job->type =  $type;
         $status = $job->spoolPrintJob($tubeid, 'items', $job=null);
     }
} else {
    print "Error: No object specfied";
    exit;
}   

header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($status) . ");";
