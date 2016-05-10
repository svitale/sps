<span id="lmenu">
<?php 
print selectStudy();
print selectParam('id_instrument');
print selectParam('id_assay');
print selectDateRange();
?>
<a href="npc.php?action=data&format=xls&type=snapshot">create snapshot</a>
</span>
