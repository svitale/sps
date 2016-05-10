<?php
lib('Controller/Crf');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
//TODO: validate input
if (isset($_POST['interface'])) {
    $interface = $_POST['interface'];
} else {
    $interface = null;
}
if (isset($_POST['id_uuid'])) {
    $id_uuid = $_POST['id_uuid'];
    $crf = New Crf();
    $object_type = $crf->returnObjectType($id_uuid);
    if ($object_type == 'batch_quality_record') {
        $active_object = New InventoryObject();;
        $active_object->table = 'batch_quality';
        $active_object->id_uuid = $id_uuid;
$behavior = New Behavior();
$fields = $behavior->retFields($active_object);
$active_object->tracked_fields = $fields;

        $active_object->FetcherInventoryObject();
        if (is_null($crf->batchid)){
            $crf->batchid = new_uuid();
        }
        $crf->setActiveObject($active_object);
        if ($interface == 'scanner' ){
            $crf->addToBatch($active_object);
            $crf->markReceived($active_object);
        }
    } else if ($object_type == 'batch_quality_batch') {
        $crf->setActiveObject(null);
        $crf->batchid = $id_uuid;
    }
}
if (!isset($crf)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($crf) . ");";
?>
