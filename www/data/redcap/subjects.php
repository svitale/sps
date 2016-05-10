<?php
lib('REDCap');
$REDCap = New REDCap;
$subjects = $REDCap->newSubjects();
print_r($subjects);
?>
