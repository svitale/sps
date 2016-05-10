<?php
print selectStudy();
print selectPrinter();
pullFreezerFilter();
function pullSampleReplacement($defaultVal = "1") {
    $filter = "gwasreplacement";
    // fast hack, use "0 " instead of "0"
    $value = array("%", "0 ", "1", "2");
    $label = array("All", "0 - primary", "1 - backup", "2 - second backup");
    
    // set default value
	if(($defaultVal) and (strlen(trim($defaultVal)) > 0) and (!isset($_SESSION[$filter]))) {
		$defaultVal = trim(htmlentities($defaultVal));
		$_SESSION[$filter] = $defaultVal;
	}
    
    echo "<div>Tube Replacement</div>";
    echo "<form>";
    echo '<select id="cc' . $filter . '" onChange="filter(\'' . $filter . '\',$F(\'cc' . $filter . '\'))" class="autocomplete">';
	for ($i = 0; $i < count($value); $i++) {
		if ($_SESSION[$filter] == $value[$i]) {
			$sel = 'selected';
		} else {
			$sel = '';
		}
		echo '<option value="' . $value[$i] . '" ' . $sel . '>' . $label[$i] . '</option>' . "\n";
	}
    echo "</select></form>";
}

/**
 * Displays a gui that allows the user to set session variables.
 * The session variables are intended to be used as query filters.
 *
 * For the value of 'filter' to be set, it must be defined in the 'filter' statement in npc.php
 *
 * @param string $defaultVal If $defaultVal is not false, if the session variable defined in $filter is not set, sets it with $defaultVal
 */

function pullFreezerFilter($defaultVal = "%") {
    $filter = "freezer";
    echo "<div>Freezer</div>";
    $result = mysql_query("SELECT distinct freezer as filter from VwShelfAndLocations order by freezer");
	if (!$result) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
    /*$filterArray = array('CRIC 1', 'CRIC 2', 'CRIC 3', 'CRIC 4', 'CRIC 2', 'CRIC 3', 'CRIC 4', 'CRIC 5', 'CRIC 6', 'CRIC 7', 'CRIC 8', 'CRIC 9', 'CRIC 10',
        'CRIC 12 Sanyo', 'CRIC 13', 'CRIC 14 Sanyo', 'CRIC 15 Sanyo', 'CRIC 16 Sanyo');*/
?>
<form>
<?php
	// set default value
	if(($defaultVal) and (strlen(trim($defaultVal)) > 0) and (!isset($_SESSION[$filter]))) {
		$defaultVal = trim(htmlentities($defaultVal));
		$_SESSION[$filter] = $defaultVal;
	}

	echo '<select id="cc' . $filter . '" onChange="filter(\'' . $filter . '\',$F(\'cc' . $filter . '\'))" class="autocomplete">';

    if ('%' == $row['filter']) {
			$sel = 'selected';
		} else {
			$sel = '';
		}
		echo '<option value="%" ' . $sel . '>Show All-quick</option>' . "\n";
		echo '<option value="quick_box" ' . $sel . '>Show All-boxes</option>' . "\n";

	
	while ($row = mysql_fetch_array($result)) {
		if ($_SESSION[$filter] == $row['filter']) {
			$sel = 'selected';
		} else {
			$sel = '';
		}
		echo '<option value="' . $row['filter'] . '" ' . $sel . '>' . $row['filter'] . '</option>' . "\n";
	}

	/*
    echo '<option value="-">--Presplit--</option>' . "\n";
    foreach ($filterArray as $val) {
        if ($_SESSION[$filter] == $val) {
			$sel = 'selected';
		} else {
			$sel = '';
		}
		echo '<option value="' . $val . '" ' . $sel . '>' . $val . '</option>' . "\n";
    }
	*/

	echo '</select>' . "\n";
?>
</form>
<script type="text/javascript">
</script>
<?php
}
?>
