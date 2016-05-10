<?php
lib('Controller/Crf');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
	$callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (isset($_POST['update'])) {
  $update = $_POST['update'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
$crf = New Crf();
if (isset($update['import']) && $crf->batchid == null) {
     $batchid = new_uuid();
     $table = $update['import'];
     $crf->tmptable = $table;
     $crf->batchid = $batchid;
     $crf->importBatch($table);
} else if (isset($update['removefrombatch'])) {
     $batchid = 0;
     $id_uuid = $update['id_uuid'];
     $newcrf = New Crf();
     $newcrf->batchid = $batchid;
     $invobject = New InventoryObject();
     if ($crf->active_object && $crf->active_object->id_uuid == $id_uuid) {
         $crf->setActiveObject($invobject);
     }
     $invobject->id_uuid = $id_uuid ;
     $newcrf->addToBatch($invobject);
     unset($newcrf->batchid);
} else if (isset($update['addtobatch'])) {
     $batchid = $update['addtobatch'];
     $id_uuid = $update['id_uuid'];
     $newcrf = New Crf();
     $newcrf->batchid = $batchid;
     $invobject = New InventoryObject();
     $invobject->id_uuid = $id_uuid ;
     $newcrf->addToBatch($invobject);
     unset($newcrf->batchid);
} else {
    print "something went wrong!";
    exit;
}
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($crf) . ");";
?>
