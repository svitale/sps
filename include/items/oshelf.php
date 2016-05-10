<?
include('include/config.php');

        ?>



<?
//* see if browser requests a freezer
//* Get Sitewide Inventory -- presents the initial list of freezers
$selectedFreezer = urlencode($_SESSION['freezer']);
		$freezerQuery = mysql_query("SELECT comment1,divY,divX FROM `items` where type = 'freezer' ORDER BY `id`");
		echo '<div style="margin-bottom: 3px;">
			 <select id="ccFreezer"  onChange="setFreezer(\''.$id.'\',$F(\'ccFreezer\'))">';
		echo '<option value="">';
		for($i=0; $i<mysql_num_rows($freezerQuery); $i++) {
				extract(mysql_fetch_array($freezerQuery));
			if ($name) {
				$thisFreezer = urlencode($name);
				$numshelves{$name} = $div1;
				if($thisFreezer == $selectedFreezer) {
					$sel = 'selected';
				} else {
					$sel = '';
				} 
			}
		echo '<option value="'.$thisFreezer.'"' .  $sel . '>' . $comment1;
		}
		echo '</select>';
		echo '</form>
			</div>';
		mysql_free_result($freezerQuery);
if (isset($_SESSION['freezer'])) {

		for($j=1; $j<=$numshelves{$_SESSION['freezer']}; $j++) {
?>
        <div>
        <div class="shelfFloat" id="newItemShelf<?echo $j?>" style="border: 1px solid rgb(0, 0, 0); background-color: #d6c5e5; cursor: pointer;" onmouseover="highlightCell('newItemShelf<?echo $j?>')" onmouseout="resetCell('newItemShelf<?echo $j?>')" onclick="setItemParams('<?echo $id?>','shelf','6','<?echo $j?>')">
        <span style="position: relative; left: 1px;"><?echo $j?></span></div></div>
<?
}
} else {
}
