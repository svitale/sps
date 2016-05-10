<?php
/**
 *UI to set an item of type 'unsigned' to type 'shelf' with a given freezer and shelf number
 *Display a list of all freezers.  When a freezer is selected, display the shelves in that freezer.
 *When a shelf is clicked, the selected item is updated.
 * 
 * Used by 'store', included by include/newitem.php
 *
 * $id - items.id, assumed to be set when this is included (ARGHHH)
 * $_SESSION[freezerid]
 *
 * dfunk.js:
 * newFreezer() - user chooses a freezer from the dropdown
 * setItemParams() - user clicks a shelf button
*/

// Display a list of freezers
if (isset($_SESSION['id_study'])) {
	$id_study = $_SESSION['id_study'];
}
if (isset($_SESSION['freezerid'])) {
  $selectedFreezer = isset($_SESSION['freezerid']);
} else {
  $selectedFreezer = '';
}
$numshelves = array();
$sql = "SELECT id as freezerid,comment1,divY,divX FROM `items` ";
$sql .= "where type = 'freezer' ";
if (isset($id_study)) {
	$sql .= "and id_study = '$id_study' ";
}
$sql .= "ORDER BY `freezerid`";
$result = mysql_query($sql);
echo '<div style="margin-bottom: 3px;">';
echo '<br/><br/><label>Choose a freezer:</label><br/>';
echo '<select id="ccFreezer"  onChange="newFreezer(\''.$id.'\',$F(\'ccFreezer\'))">';
echo '<option value="">';
while($row = mysql_fetch_assoc($result)) {
	$numshelves{$row['freezerid']} = $row['divY'];
	if($row['freezerid'] == $selectedFreezer) {
		$sel = 'selected';
	} else {
		$sel = '';
	} 
	echo '<option value="'.$row['freezerid'].'"' .  $sel . '>' . $row['comment1'] . '</option>';
}
echo '</select>';
echo '</form> </div>';
mysql_free_result($result);

// display the shelves for the selected freezer
if (isset($_SESSION['freezerid'])) {
	echo "<br/><label>Choose a shelf:</label>";
	for($j=1; $j<=$numshelves{$_SESSION['freezerid']}; $j++) {
?>
    <div>
    <div class="shelfFloat"
		 id="newItemShelf<?php echo $j?>"
		 style="border: 1px solid rgb(0, 0, 0); background-color: #d6c5e5; cursor: pointer;"
		 onmouseover="highlightCell('newItemShelf<?php echo $j?>')"
		 onmouseout="resetCell('newItemShelf<?php echo $j?>')"
		 onclick="setItemParams('<?php echo $id?>','shelf','6','<?php echo $j?>')">
    <span style="position: relative; left: 1px;"><?php echo $j?></span></div></div>
<?php
	}
}
