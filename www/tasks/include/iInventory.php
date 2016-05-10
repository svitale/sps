<?php
/**functions and classes for manipulating inventory
 * used by /tasks/include/pull.php
*/

/**
 * Moves a tube into a box.  The tube and box must already exist in the items table.
 * Like addtube(), but does not rely on session variables
 * 
 * @param Int $boxid
 * @param Int $tubeid
 * @param bool $transaction_flag=false if true, use transactions
 *
 * @return String "ok" if successful, false or error message on failure.
*/
function insertTubeIntoBox($boxid, $tubeid, $transaction_flag=false) {
    //echo "<br>insertTubeIntoBox($boxid, $tubeid)";
    
	$destination = '';
    //make sure boxid and tubeid are valid 8 character ids, get box destination
    if(strlen($boxid) <= 8) {
        $boxChkQuery = "SELECT * from items where id = $boxid and type = 'box'";
        $result = mysql_query($boxChkQuery);
        if(!$result)
            return "Invalid box id: $boxid";
        elseif(mysql_affected_rows() != 1)
            return "Invalid box id: $boxid";
		else {
			$boxResult = mysql_fetch_array($result);
			$destination = $boxResult['destination'];
		}
    }
    else
        return "Invalid box id: $boxid";
    
    if(strlen($tubeid) <= 8) {
        $tubeChkQuery = "SELECT * from items where id = $tubeid and type = 'tube'";
        $result = mysql_query($tubeChkQuery);
        if(!$result)
            return "Invalid tube id: $tubeid";
        if(mysql_affected_rows() != 1)
            return "Invalid tube id: $tubeid";
    }
    else
        return "Invalid tube id: $tubeid";
    
	// get tube position
    $tubearray = getNextTubePosition($boxid);
    if ($tubearray[0] == 0) {
        return "Box full";
    }
    
    $x = $tubearray[0];
    $y = $tubearray[1];
              
    //echo "<br/> new position: $x, $y";
	
    //TRANSACTION START
	if($transaction_flag) mysql_query("start transaction");
	// move the tube into the box
	$locations_insert = mysql_query("INSERT INTO `locations` (id_item,id_container,subdiv4,subdiv5, destination) values ('$tubeid','$boxid','$x','$y', '$destination')");
	if (!$locations_insert) {
		$error = mysql_error();
		if($transaction_flag) mysql_query("rollback");
		echo "Could not run query: $error";
		return "location insert query failed: $error";
	}
	$loc_id = mysql_insert_id();
	$locations_update = mysql_query("update locations set date_moved = curdate() where date_moved is NULL and id_item = '$tubeid' and id != '$loc_id'");
	if (!$locations_update) {
		$error = mysql_error();
		if($transaction_flag) mysql_query("rollback");
		echo "Could not run query: $error";
		return "location update query failed: $error";
	}
	$items_update = mysql_query("UPDATE `items` set destination = '$destination' where id  = '$id' and destination = ''");
	if (!$items_update) {
		$error = mysql_error();
		if($transaction_flag) mysql_query("rollback");
		echo "Could not run query: $error";
		return "items update query failed: $error";
	}          
	if($transaction_flag) mysql_query("commit");
    return "ok";
}

/**
 * Get the id of a box that contains a tube
 * @param string $tubeid
 *
 * @return array {'id_container', 'position', 'subdiv1', 'subdiv2', 'subdiv3', 'freezer'}
 */
function getTubeLocation($tubeid) {
    $returnArray = array();
    
    // get box  info
	$checkStatement = "SELECT locations.id_item, locations.id_container, locations.subdiv4, locations.subdiv5 from locations WHERE locations.id_item = '$tubeid' and locations.date_moved is null";
	$checkResult = mysql_query($checkStatement);
	if (mysql_affected_rows() != 0) {
		$checkRow = mysql_fetch_array($checkResult);
		$returnArray{"id_item"} = $checkRow['id_item'];
        $returnArray{"id_container"} = $checkRow['id_container'];
		$returnArray{"subdiv4"} = $checkRow['subdiv4'];
		$returnArray{"subdiv5"} = $checkRow['subdiv5'];
        $returnArray{"position"} = array($checkRow['subdiv4'], $checkRow['subdiv5']);
	}
    // get rack info
    // get shelf info
    
    return $returnArray;
}
/**
 *Returns the next free tube in the given box
 * @param int $boxid
 *
 * @return array($x, $y)
 **/
function getNextTubePosition($boxid) {
	$boxdivX = abs(divX($boxid));
	$boxdivY = abs(divY($boxid));
	$next_spot = mysql_query("select subdiv4,subdiv5 from `locations` where id_container = '" . $boxid . "' and date_moved is null");
	if (($boxdivX == null) or ($boxdivY == null) or ($boxdivX<0) or ($boxdivY<0)) {
		//echo "Box has invalid dimentions: ($boxdivX, $boxdivY)";
		$result{0} = 0;
        $result{1} = 0;
        return $result;
	}
	if (!$next_spot) {
		//echo 'Could not run query: ' . mysql_error();
		$result{0} = 0;
        $result{1} = 0;
        return $result;
	}
    // all positions are filled 
    if(mysql_num_rows($next_spot) >= $boxdivX * $boxdivY) {
        //echo "all filled";
        $x = 0;
        $y = 0;
    }
    // find next unfilled position
	else if (mysql_num_rows($next_spot) > 0) {
		for ($i = 0;$i < mysql_num_rows($next_spot);$i++) {
			extract(mysql_fetch_array($next_spot));
			$filled{$subdiv4}{$subdiv5} = "1";
		}
		
		// following the convention in topview:
		// if a square box, pop by row then col
		// if rectangular, pop by col then row
		if ($boxdivX == $boxdivY) {
			for ($x = 1;$x < ($boxdivX + 1);$x++) {
				for ($y = 1;$y < $boxdivY;$y++) {
					if (!isset($filled{$x} {$y})) {
						break;
					}
				}
				if (!isset($filled{$x} {$y})) {
					break;
				}
			}
		}
		else {
			for ($y = 1; $y <= ($boxdivY); $y++) {
				for ($x = 1;$x <= $boxdivX; $x++) {
					if (!isset($filled{$x} {$y})) {
						$result{0} = $x;
						$result{1} = $y;
						return $result;
					}
				}
			}
			// box is apparently full
			$result{0} = 0;
			$result{1} = 0;
			return $result;
		}
	} else {
        //echo "none filled";
		$y = 1;
		$x = 1;
	}
    $result{0} = $x;
    $result{1} = $y;
    return $result;
}
?>