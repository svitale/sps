<?php
lib('Printer');
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (isset($_POST['printer_name'])) {
   $printer_name = $_POST['printer_name'];
} else {
    print "Error: printer  specified";
    exit;
}   
$PrintDev = New PrintDev;
$allowed_printers = array_keys($PrintDev->listPrinters());
if (in_array($printer_name,$allowed_printers)) {
   $PrintDev->printer_name = $printer_name;
   $PrintDev->getPrinter();
   $PrintDev->setPrinter();
} else {
    print "error: you are not permitted to use this printer";
    exit;
}

header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($PrintDev) . ");";
