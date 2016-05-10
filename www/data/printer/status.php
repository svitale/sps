<?php
lib('Printer');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (isset($_POST['jobid']) && ($_POST['jobid']) > 0) {
    $jobid = $_POST['jobid'];
} else {
    print "Error: No Batch id specified";
    exit;
}   
$job = New PrintJobs();
$status = $job->getPrintJob($jobid);

header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($status) . ");";
