#!/usr/bin/php
<?php
include_once('/usr/local/sps/include/lib.php');
lib('Controller/Randomizer');

$uuid = $argv[1];
print "uuid:$uuid\n";
$randomizer = New Randomizer();
$randomizer->randomizeByUuid($uuid);

?>
