<?php
error_reporting(0);
require_once("/usr/share/php/Image/Barcode.php");
$code = $_GET['code'];
//function barcodeImage($code) {
Image_Barcode::draw($code, "code128", "png");
//}
//barcodeImage($code)
?>
