<?php
lib('Controller/Store');
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
if (isset($_POST['id'])) {
    $id= $_POST['id'];
    $store = New Store();
    $active_object = New InventoryObject();;
    $active_object->table = 'items';
    $active_object->id = $id;
    $active_object->FetcherInventoryObject();
    $store->setActiveObject($active_object);
} else if (isset($_POST['id_uuid'])) {
    $id_uuid= $_POST['id_uuid'];
    $store = New Store();
    $active_object = New InventoryObject();;
    $active_object->table = 'items';
    $active_object->id_uuid = $id_uuid;
    $active_object->FetcherInventoryObject();
    $store->setActiveObject($active_object);
}
if (!isset($store)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($store) . ");";
?>
