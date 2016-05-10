<?php
/**
 *UI to set an item of type 'unsigned' to type 'freezer' with a given freezer
 *Display a list of freezer types.  When a freezer type is selected, display details for that freezer and allow the user to enter freezer name.
 *When a shelf is clicked, the selected item is updated.
 * 
 * Used by 'store', included by include/newitem.php
 *
 * $id - items.id, assumed to be set when this is included
 * $_SESSION[freezerid]
 *
 * dfunk.js:
 * setFreezer() - user chooses a freezer type from the dropdown
 * setItemParams() - user clicks a shelf button
*/


// display drop down of freezer types
if (isset($_SESSION['freezer'])) {
$selectedFreezer = urlencode($_SESSION['freezer']);
} else {
$selectedFreezer = '';
}
$freezerQuery = mysql_query("SELECT name,model,div1,div2 FROM `labstruct` where type = 'freezer' ORDER BY `id`");
echo '<div style="margin-bottom: 3px;"><br/><label>Choose a freezer Type:</label>';
echo '<select id="ccFreezer"  onChange="setFreezer(\''.$id.'\',$F(\'ccFreezer\'))">';
echo '<option value="">';
for($i=0; $i<mysql_num_rows($freezerQuery); $i++) {
		extract(mysql_fetch_array($freezerQuery));
		if ($name) {
				$thisFreezer = urlencode($name);
				$numshelves{$name} = $div1;
				$numracks{$name} = $div2;
				$modelid{$name} = $model;
				if($thisFreezer == $selectedFreezer) {
					$sel = 'selected';
				} else {
						$sel = '';
				} 
		}
		echo '<option value="'.$thisFreezer.'"' .  $sel . '>' . $name;
}
echo '</select>';
echo '	</div>';
mysql_free_result($freezerQuery);
		
// display information for selected freezer
if (isset($_SESSION['freezer'])) {
		echo '<div class="left_column" id="comment1" style="width:50px; background-color: lightblue">Name';
        echo "<script type='text/javascript'>
        new Ajax.InPlaceEditor('comment1', 'npc.php?action=addfreezer',{formClassName: 'left_column', size: '12', callback: function(form, value) { return 'comment1=' + escape(value)+'&id=".$id."&type=".$type."&width=".$numracks{$_SESSION['freezer']}."&hight=".$numshelves{$_SESSION['freezer']}."'}})</script>";
        echo "</div>";
		echo '<table><tr><img alt="'.$modelid{$_SESSION['freezer']}.'" src="images/'.$modelid{$_SESSION['freezer']}.'.jpg" width="80" height="75"></tr>';
		echo "<tr>Shelves: ".$numshelves{$_SESSION['freezer']}."</tr>";
		echo "<tr>Racks/Shelf: ".$numracks{$_SESSION['freezer']}."</tr></table>";
}
