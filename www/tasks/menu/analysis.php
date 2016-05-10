<span id="lmenu">
<?php 
print selectStudy();
print selectParam('id_instrument');
print selectParam('id_study');
print '<span id="squashmenu"></span>';
print selectDateRange();
?>
<a href="npc.php?action=data&format=xls&type=snapshot">create snapshot</a>
</span>
