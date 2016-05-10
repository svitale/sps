<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (isset($_POST['id_uuid'])) {
    $id_uuid = $_POST['id_uuid'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
//TODO: validate input
lib('Process');
$process = New Process();
$process_events = $process->retProcesslogArray($id_uuid);
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($process_events . ");";
?>
