#!/usr/bin/php
<?php
include_once('/opt/sps/include/lib.php');
lib('Controller/LISResults');
global $sps;
$id = $argv[1];
$ids = array($id);
$LISResults = new LISResults();
$Result = $LISResults->doImport($ids);
print("\n");
?>
