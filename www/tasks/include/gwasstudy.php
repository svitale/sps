<?php
/**
 * Main page for the GWAS study.
 *
 * Displays status information on what has been scanned
 *
 * Allows the user to scan boxes and tubes
 */

/**
 *Function that is called when a uuid is scanned.
 *
 * @param string $table  - the table where the item is found. Either 'items' or 'batch_quality'
 * @param string $id - the id of the scanned object
 * 
 *If a box is scanned:
 *  Show the contents of the box.  Any samples that meet the requirements are highlighted.
 *If a tube is scanned:
 *  Check to see if it meets the requirements.  If so, move the tube to the testing box.
 *  Update the logs and the gwas_* tables.
*/

function onPageLoad() {
    echo "<div>";
    showBench();
    echo "<h2>GWAS Study</h2><br/>";
    displayGWASLocations();
    echo "</div>";
}

function operateScannedObject($table, $id) {
    // clear main div
    echo '<script type="text/javascript">';
    echo "$('staticcontainer').innerHTML = '<div id=\"actioncontainer\"></div>'";
    echo '</script>';
    
    if ($table != "items"){
        echo "Object not in 'items' table";
        if($logFlag) InsertGwasLog('', $id, '', $labtech, '', '', '', 'scan object','incorrect table', $table);
        displayGWASLocations();
    }
    $updateLocationFlag = true;
    $logFlag = true;
    
	//$labtech = "test";
	$labtech = $_SESSION['username'];
	$result = mysql_query("SELECT * from $table where id = $id");
	$row = mysql_fetch_array($result);
    
	if ($row['type'] == "box") {
        boxArray($id);
        if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, '', '', '', 'show box','', '');
		
        showBench();
        echo "<div  id=\"status_action\" style=\"border: 1px solid rgb(192, 192, 192); background-color: rgb(222, 222, 222);\"></div>";
        
        echo "<h2>GWAS Study</h2>";
        echo "<br/><br/>";
        
        //echo '<div class="boxFloat" id="boxview_' . $k . '" style="border: '. $borderwidth.' solid  #' . $bordercolor . '; color: #' . $fgcolor . ' ;background-color: #' . $bgcolor . '; cursor: pointer;" onmouseover="highlightCell(\'boxview_' . $k . '\')" onmouseout="resetCell(\'boxview_' . $k . '\')" onclick="postScan(\'' . retUuid($v) . '\')">';    
        displayBox($table, $id, "boxdiv", "Destination Box");
        
		return;
	}
	else if ($row['type'] == "tube") {
        showBench();
        echo "<div  id=\"status_action\" style=\"border: 1px solid rgb(192, 192, 192); background-color: rgb(222, 222, 222);\"></div>";
        echo "<br/><br/>";
        
        // make sure we have a box selected
        $boxid = getActiveBenchBox();
        if (!isset($boxid) || (!$boxid)) {
            echo "<font class=\"alert\">Unable to move sample: No destination box selected.</font>";
            if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], 'no box selected','', '');
            return;
        }
        
        if ($boxid == 0) {
            echo "<font class=\"alert\">Unable to move sample: Invalid destination box selected, id = $boxid.</font>";
            if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], 'invalid box selected','', '');
            return;
        }
        
        // make sure box has a valid destionation
        $boxdest = dest($boxid);
        if ((!$boxdest) ||
            !(($boxdest == 'test-01') || ($boxdest == 'test-02') || ($boxdest == 'test-03'))) {
            echo "<font class=\"alert\">Unable to move sample: Box destination incorrect: '$boxdest'.</font>";
            if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], 'box destination incorrect','', '');
            return;
        }
   
        // make sure box is not full
        $boxpos = getNextTubePosition($boxid);
        if($boxpos[0] == 0) {
            echo "<font class=\"alert\">Unable to move sample: Target box $boxid full.</font>";
            if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], 'box full','', '');
            return;
        }
        
        //get source box
        $sourceloc = getTubeLocation($id);
        $destination = "GWAS testing";
        
        //make sure source box has been scanned into the bench
        if(in_array($sourceloc['id_container'], getBenchArray()) == false) {
            echo "<font class=\"alert\">Unable to move sample: Source box is not in the bench</font><br/>";
            if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], 'source box not in bench', $sourceloc['id_container'], '');
            
            if (($sourceloc) && (strlen($sourceloc['id_container']) > 0)){
                    echo '<div id="box1"  style:"border: 5px solid #;", "Source Box">';
                    displayBox($table, $sourceloc['id_container'], "boxdivsource", "Source Box", array($sourceloc['position']));
                    echo '</div>';
            }
            else {
                echo "Source Box Unknown";
            }
            
			displayBox($items, $boxid, "boxdivdest", "Destination Box");
            return;
        }
        
		// error check the tube
		$error = errorCheck($table, $id);
            
		if ($error == "ok") {
            // update locations
            $insertResult = false;
            if($updateLocationFlag) {
                $insertResult = insertTubeIntoBox($boxid, $id);
            }
            if((!$updateLocationFlag) or ($insertResult == "ok")) {
                // update gwas_specimens
                if($updateLocationFlag) {
                    echo "<font class=\"success\">Item scanned sucessfully, item location updated.</font>";
                    if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], $error,'', $boxid);
                }
                else {
                    echo "<font class=\"success\">Item scanned sucessfully. Testing mode, item location not updated.</font>";
                    if($logFlag) InsertGwasLog($row['id_uuid'], $id, $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], $error,'', '');
                }
                echo "<ul>";
                echo "<li>Barcode: " . $row['id_uuid'] . "</li>";
                echo "<li>Patient ID: " . $row['id_subject'] . "</li>";
                echo "<li>Visit Number: " . $row['id_visit'] . "</li>";
                echo "<li>Date Visit: " . $row['date_visit'] . "</li>";
                echo "<li>Sample Type: " . $row['sample_type'] . "</li>";
                echo "</ul><br/>";
                UpdateGwasSpecimens($row['id_uuid'], $row['id_visit'], $row['date_visit'], $labtech, $row['id_subject'], $row['id_visit']);
                /*
                $query = "UPDATE gwas_specimens
                    set barcode = '" . $row['id_uuid'] . "',
                    scan_date = NOW(),
                    tcl_vnum = '" . $row['id_visit'] . "',
                    tcl_vdate = '" . $row['date_visit'] . "',
                    tcl_labtech = '$labtech'
                    WHERE pid = '" . $row['id_subject'] . "'
                    and vnum = '" . $row['id_visit'] . "'";
                mysql_query($query);
                */
            }
            else {
                echo "<br/><font class=\"alert\">Error when updating item location.</font>";
                echo "<ul><li>Reason: $insertResult<li/></ul>";
            }
            
            if($updateLocationFlag) {
                if (($sourceloc) && (strlen($sourceloc['id_container']) > 0)){
                    //$sourceloc
                    echo '<div id="box1"  style:"border: 5px solid #;", "Source Box">';
                    displayBox($table, $sourceloc['id_container'], "boxdivsource", "Source Box", array($sourceloc['position']));
                    echo '</div>';
                }
                else {
                    echo "Source Box Unknown";
                }
                
                displayBox($items, $boxid, "boxdivdest", "Destination Box", array($boxpos));
            }
            else
                displayBox($items, $boxid, "boxdivdest", "Destination Box");
            return;
		} else {
            if($logFlag) InsertGwasLog($row['id_uuid'], $id,  $row['type'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], $error,'', '');
			// update gwas_spec_err
			echo "<font class=\"alert\">Error when scanning, item location not changed.</font>";
			echo "<ul>";
			echo "<li>Error: $error</li>";
			echo "<li>Barcode: " . $row['id_uuid'] . "</li>";
			echo "<li>Patient ID: " . $row['id_subject'] . "</li>";
			echo "<li>Visit Number: " . $row['id_visit'] . "</li>";
			echo "<li>Date Visit: " . $row['date_visit'] . "</li>";
			echo "<li>Sample Type: " . $row['sample_type'] . "</li>";
			echo "</ul>";
			// log errors
			if (($error == "PID not on list") or ($error == "PID already scanned") or ($error == "TCL_VDATE < VDATE - 90") or ($error == "TCL_VDATE > VDATE + 90") or ($error == "TCL_VNUM != VNUM")) {
				echo "<b>This error has been logged.</b><br/><br/>";
                InsertGwasErrorLog($row['id_uuid'], $labtech, $row['id_subject'], $row['id_visit'], $row['date_visit'], $error,'');
			}
            if($updateLocationFlag) {
                if (($sourceloc) && (strlen($sourceloc['id_container']) > 0)){
                        echo '<div id="box1"  style:"border: 5px solid #;", "Source Box">';
                        displayBox($table, $sourceloc['id_container'], "boxdivsource", "Source Box", array($sourceloc['position']));
                        echo '</div>';
                }
                else {
                    echo "Source Box Unknown";
                }
            }
            
			displayBox($items, $boxid, "boxdivdest", "Destination Box");
            return;
		}
	}
	displayGWASLocations();
}

function UpdateGwasSpecimens($uuid, $id_visit, $date_visit, $labtech, $where_id_subject, $where_id_visit) {
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if (!(isset($ses_replacement)) || ($ses_replacement == '') || $ses_replacement == "%") {
        return "Could not run query: no replacement selected.";
    }
    
    $query = "UPDATE gwas_specimens
    set barcode = '" . $uuid . "',
    scan_date = NOW(),
    tcl_vnum = '" . $id_visit . "',
    tcl_vdate = '" . $date_visit . "',
    tcl_labtech = '$labtech'
    WHERE pid = '" . $where_id_subject . "'
    and vnum = '" . $where_id_visit . "'
    and replacement = '" . $ses_replacement. "'";
    $result = mysql_query($query);
    
    if (!$result) {
        return 'Could not run query: ' . mysql_error();
	}
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
	$checkStatement = "SELECT locations.id_container, locations.subdiv4, locations.subdiv5 from locations WHERE locations.id_item = '$tubeid' and locations.date_moved is null";
	$checkResult = mysql_query($checkStatement);
	if (mysql_affected_rows() != 0) {
		$checkRow = mysql_fetch_array($checkResult);
        $returnArray{"id_container"} = $checkRow['id_container'];
        $returnArray{"position"} = array($checkRow['subdiv4'], $checkRow['subdiv5']);
	}
    // get rack info
    // get shelf info
    
    return $returnArray;
}

/**
 *Write to the table gwas_log
 * @param string $uuid
 * @param string $id
 * @param string $type
 * @param string $labtech - user currently signed in
 * @param string $pid
 * @param string $vnum - visit number, i.e. "V1Y1"
 * @param string $vdate - string that represents the visit date.  Must be in a mysql friendly format.
 * @param string $response - the result of the scan
 * @param string $comment
 * @param string $new_id_container - if the tube move was successful, the new box the tube is contained in 
 *
 * @return bool
 * */
function InsertGwasLog($uuid, $id,  $type, $labtech, $pid, $vnum, $vdate, $response, $comment, $new_id_container){
    $query = "INSERT INTO gwas_log (pid, type, barcode, scan_id, scan_date, tcl_vnum, tcl_vdate, tcl_labtech, response, new_id_container)
        values ('$pid', '$type', '$uuid', '$id', NOW(),  '$vnum',  '$vdate',  '$labtech',  '$response', '$new_id_container')";
	$result = mysql_query($query);
    
    if (!$result) {
        echo 'Could not run query: ' . mysql_error();
        return false;
	}
    return true;
}

/**
 *Checks the given item to against the gwas_specimens table for the selected replacement number.
 *If the sample matches an unscanned request, the string 'ok' is returned.
 *If the sample does not match, an error is returned.
 *
 * @param $table
 * @param $id
 *
 * @return string
 *
 *Return values:
    PID not on list
    PID already scanned
    *wrong item type
    *wrong sample type
    *wrong study
    *TCL_VDATE not set
    TCL_VDATE > VDATE+90
    TCL_VDATE < VDATE-90
    TCL_VNUM != VNUM
    ok
*/
function errorCheck($table, $id) {
	$result = mysql_query("select * from $table where id = $id");
	if (!$result) return "Query failed: " . mysql_error();
    
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if (!(isset($ses_replacement)) || ($ses_replacement == '') || $ses_replacement == "%") {
        return "No replacement selected.";
    }
    
	$item = mysql_fetch_array($result);
	$id_visit = $item['id_visit'];
	$id_subject = $item['id_subject'];
	$date_visit = $item['date_visit'];


    
	//wrong item type
	if ($item['type'] != "tube") return "wrong item type";
	//wrong study
	if ($item['id_study'] != "CRIC") return "wrong study";
	//wrong sample type
	if ($item['sample_type'] != "BUFFY") return "wrong sample type";
	//pid exists check
	$gwasresult = mysql_query("select * from gwas_specimens where pid = $id_subject");
	if (!$gwasresult) return "Query failed: " . mysql_error();
	if (mysql_affected_rows() == 0) return "PID not on list";
    
    //any tube for this patent already scanned
    $gwasresult = mysql_query("select * from gwas_specimens where pid = $id_subject and barcode != '' and replacement = $ses_replacement");
    if (!$gwasresult) return "Query failed: " . mysql_error();
	if (mysql_affected_rows() > 0) return "PID already scanned";
    
    //vnum/replacement id check
	$gwasresult = mysql_query("select * from gwas_specimens where pid = $id_subject and vnum = '$id_visit' and replacement = $ses_replacement");
	if (!$gwasresult) return "Query failed: " . mysql_error();
	if (mysql_affected_rows() == 0) return "TCL_VNUM != VNUM";
	$gwas_item = mysql_fetch_array($gwasresult);
	//pid already scanned check
	if ($gwas_item['scan_date'] != '0000-00-00') return "PID already scanned";
	
    //vdate check
	$gwas_date_visit = $gwas_item['vdate'];
	if ($date_visit == 0) {
		return "TCL_VDATE not set";
	}
	$diff = dateDiff($gwas_item['vdate'], $item['date_visit']);
    //$diff = 0;  #debug!
	$dateRange = 90;
	/*
	echo $gwas_item['vdate'] . ", " .  $item['date_visit'];
	echo $diff;
	*/
	if ($diff < - $dateRange) {
		return "TCL_VDATE < VDATE - 90";
	}
	if ($diff > $dateRange) {
		return "TCL_VDATE > VDATE + 90";
	}
	return "ok";
}

/**
 *Moves a tube into a box.  The tube and box must already exist in the items table.
 * @param Int $boxid
 * @param Int $tubeid
 * @param String destination what will appear in the item.destionation field
 *
 * @return String "ok" if successful, false or error message on failure.
*/
function insertTubeIntoBox($boxid, $tubeid, $destination = "GWAS Study") {
    //echo "<br>insertTubeIntoBox($boxid, $tubeid)";
    
    //make sure boxid and tubeid are valid 8 character ids
    if(strlen($boxid) <= 8) {
        $boxChkQuery = "SELECT * from items where id = $boxid and type = 'box'";
        $result = mysql_query($boxChkQuery);
        if(!$result)
            return "Invalid box id: $boxid";
        if(mysql_affected_rows() != 1)
            return "Invalid box id: $boxid";
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
    
    
    $tubearray = getNextTubePosition($boxid);
    if ($tubearray[0] == 0) {
        return "Box full";
    }
    
    $x = $tubearray[0];
    $y = $tubearray[1];
              
    //echo "<br/> new position: $x, $y";
    
	// move the tube into the box
	$locations_insert = mysql_query("INSERT INTO `locations` (id_item,id_container,subdiv4,subdiv5) values ('$tubeid','$boxid','$x','$y')");
	if (!$locations_insert) {
		echo 'Could not run query: ' . mysql_error();
		return "location insert query failed: " . mysql_error();
	}
	$loc_id = mysql_insert_id();
	$locations_update = mysql_query("update locations set date_moved = curdate() where date_moved is NULL and id_item = '$tubeid' and id != '$loc_id'");
	if (!$locations_update) {
		echo 'Could not run query: ' . mysql_error();
		return "location update query failed: " . mysql_error();
	}
	$items_update = mysql_query("UPDATE `items` set destination = '$destination' where id  = '$id'");
	if (!$items_update) {
		echo 'Could not run query: ' . mysql_error();
		return "items update query failed: " . mysql_error();
	}          
    //echo "<br/>Sample moved to test box: $gwasboxid";
    //echo "<br/>Position: x:$x   y:$y<br/>";
    return "ok";
}

/**
 *returns the id of the box currently selected in the bench
 *
 * @return string id from items table
 */
function getActiveBenchBox() {
    $box_array = $_SESSION['box_array'];
    if (!isset($_SESSION['boxid']) || (!isset($_SESSION['box_array'])))
        return false;
    
	if (count($_SESSION['box_array']) == 1) {
		return $_SESSION['boxid'];
	} else {
        foreach($box_array as $key => $row) {
            if(($row['id'] == $_SESSION['boxid']) && ($_SESSION['boxid'] != 0))
                return $_SESSION['boxid'];
        }
    }  
    return false;
}

/**
 *returns an array ids for all items in the bench
 *
 * @return array ids for all items in the bench
 **/
function getBenchArray() {
    $box_array = $_SESSION['box_array'];
    $returnArray = array();
    
    if (count($_SESSION['box_array']) > 0) {
        foreach($box_array as $key => $row) {
            array_push($returnArray, $row['id']);
        }
    }  
    return $returnArray;
}

/**
 * Finds the difference in days between two calendar dates.
 *
 * @param Date $startDate
 * @param Date $endDate
 * @return Int
 */
function dateDiff($startDate, $endDate) {
	// Parse dates for conversion
	$startArry = date_parse($startDate);
	$endArry = date_parse($endDate);
	// Convert dates to Julian Days
	$start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
	$end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);
	// Return difference
	return round(($end_date - $start_date) , 0);
}

/**
 *Write to the table gwas_spec_err
 * @param string $uuid
 * @param string $labtech - user currently signed in
 * @param string $pid
 * @param string $vnum - visit number, i.e. "V1Y1"
 * @param string $vdate - string that represents the visit date.  Must be in a mysql friendly format.
 * @param string $error - the error message
 * @param string $comment
 *
 * @return bool
 * */
function InsertGwasErrorLog($uuid, $labtech, $pid, $vnum, $vdate, $error, $comment){
    $query = "INSERT INTO gwas_spec_err (pid, barcode, scan_date, tcl_vnum, tcl_vdate, tcl_labtech, error_code)
        values ('$pid', '$uuid', NOW(),  '$vnum',  '$vdate',  '$labtech',  '$error')";
	$result = mysql_query($query);
    
    if (!$result) {
        echo 'Could not run query: ' . mysql_error();
        return false;
	}
    return true;
}


function displayGWASLocations() {
    
    /*
    echo "<pre>";
    print_r($_SESSION);
    echo"</pre>";
    */    
    echo "<form method='POST' name='gwasstudy_displayform'>";
    echo '<button name="gwasstudy_sps" value="true" type="submit">Show Split Tube Locations for Selected Freezer</button><br/>';
    echo '<button name="gwasstudy_freezerworks" value="true" type="submit">Show Pre-split Tube Locations for Selected Freezer</button><br/>';
    echo '<button name="gwasstudy_freezerworksbox" value="true" type="submit">Show Pre-split Boxes for Selected Freezer</button>';
    echo '</form><br/>';
    
    if($_POST['gwasstudy_sps'] == 'true') {
        displaySPSLocations();
    }
    else if($_POST['gwasstudy_freezerworks'] == 'true') {
        displayFreezerworksLocations();
    }
    else if($_POST['gwasstudy_freezerworksbox'] == 'true') {
        displayFreezerworksBoxLocations();
    }
    
    displaySampleStatus();
}

/**
 *Display known location information for unscanned samples in the gwas_specimens table
 **/
function displaySPSLocations() {
    $replacement_where = " (replacement = 4 or replacement = 5)";
    
    //freezer check
    $filter = " 1=1 ";
    $filterTxt = "";
    $freezerFlag = false;
    
    $ses_freezer = mysql_real_escape_string(trim($_SESSION['freezer']));
    if(isset($ses_freezer) && strlen($ses_freezer) > 0) {
        $freezerFlag = true;
        if (($ses_freezer != "quick") && $ses_freezer != "%") {
            $filter .= " and VwShelfAndLocations.freezer = '$ses_freezer'";
            $filterTxt .= " and freezer = '$ses_freezer'<br/>";
        }
    }
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if(isset($ses_replacement) && strlen($ses_replacement) > 0 && ($ses_replacement <> "%")) {
        $filter .= " and gwas_specimens.replacement = '$ses_replacement'";
        $filterTxt .= " and tube replacement number = '$ses_replacement'<br/>";
    }    
    
    //deep query on specific freezer
    if(($freezerFlag) && ($ses_freezer != "quick")) {
        // sps locations
        $query = "SELECT gwas_specimens.replacement as replacement_num,
            if(gwas_specimens.pid in (select pid from gwas_spec_err) >= 1, 'true', 'false') as PID_has_scan_err,
            gwas_specimens.pid as Req_PID, gwas_specimens.vnum as Req_visit_num, gwas_specimens.vdate as Req_date_visit,
            VwTubeAndLocations.id as item_id,
            VwTubeAndLocations.sample_type as sample_type,
            VwTubeAndLocations.date_visit as act_date_visit,
            VwShelfAndLocations.freezer as Freezer,
            VwShelfAndLocations.subdiv as Shelf,
            VwRackAndLocations.subdiv as Rack,
            VwBoxAndLocations.subdiv as Box,
            VwTubeAndLocations.subdivx as Row,
            VwTubeAndLocations.subdivy as Col
            
            FROM gwas_specimens inner join VwTubeAndLocations
                on gwas_specimens.pid = VwTubeAndLocations.id_subject and
                gwas_specimens.vnum = VwTubeAndLocations.id_visit
                inner join VwBoxAndLocations on VwTubeAndLocations.id_container = VwBoxAndLocations.id
                inner join VwRackAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
                inner join VwShelfAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
                
            WHERE VwTubeAndLocations.sample_type = \"BUFFY\"
            and gwas_specimens.scan_date is NULL
            and VwTubeAndLocations.id_study = 'CRIC'
            and gwas_specimens.pid not in (select pid from gwas_specimens where barcode != '' and $replacement_where )
            and $filter
            limit 2500";
    }
    //quick query on all freezers
    else {        
        $query = "SELECT gwas_specimens.replacement as replacement_num,
        if(gwas_specimens.pid in (select pid from gwas_spec_err) >= 1, 'true', 'false') as PID_has_scan_err,
        gwas_specimens.pid as Req_PID, gwas_specimens.vnum as Req_visit_num, gwas_specimens.vdate as Req_date_visit,
        items.date_visit as act_date_visit,
        items.id as item_id,
        
        locations.freezer as Freezer,
        locations.subdiv1 as Shelf,
        locations.subdiv2 as Rack,
        locations.subdiv3 as Box,
        locations.subdiv4 as Row,
        locations.subdiv5 as Col
        FROM
        items inner join locations on items.id = locations.id_item
        inner join gwas_specimens on gwas_specimens.pid = items.id_subject and
        gwas_specimens.vnum = items.id_visit
        
        WHERE $filter
        and items.sample_type = \"BUFFY\"
        and items.id_study = 'CRIC'
        and gwas_specimens.pid not in (select pid from gwas_specimens where barcode != '' and $replacement_where)
        limit 2500";
        
        $filterTxt = "<br/><i><b>May not contain location information for items moved in from the past 24 hours.<br/>
		For up to date search, choose \"Show All\" or a freezer.</b></i><br/>$filterTxt";
    }
    
	//debug, has uuids
    $query_dbg = "SELECT gwas_specimens.replacement as replacement_num,
            if(gwas_specimens.pid in (select pid from gwas_spec_err) >= 1, 'true', 'false') as PID_has_scan_err,
            gwas_specimens.pid as Req_PID, gwas_specimens.vnum as Req_visit_num, gwas_specimens.vdate as Req_date_visit,
            VwTubeAndLocations.id as item_id,
            VwTubeAndLocations.sample_type as sample_type,
            VwTubeAndLocations.date_visit as act_date_visit,
            VwShelfAndLocations.freezer as Freezer,
            VwShelfAndLocations.subdiv as Shelf,
            VwRackAndLocations.subdiv as Rack,
            VwBoxAndLocations.subdiv as Box,
            VwTubeAndLocations.subdivx as Row,
            VwTubeAndLocations.subdivy as Col,
            VwBoxAndLocations.id_uuid as Box_uuid,
            VwTubeAndLocations.id_uuid as Tube_uuid
            FROM gwas_specimens inner join VwTubeAndLocations
                on gwas_specimens.pid = VwTubeAndLocations.id_subject and
                gwas_specimens.vnum = VwTubeAndLocations.id_visit
                inner join VwBoxAndLocations on VwTubeAndLocations.id_container = VwBoxAndLocations.id
                inner join VwRackAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
                inner join VwShelfAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
                
            WHERE VwTubeAndLocations.sample_type = \"BUFFY\"
            and gwas_specimens.scan_date is NULL
            and VwTubeAndLocations.id_study = 'CRIC'
            and gwas_specimens.pid not in (select pid from gwas_specimens where barcode != '' and $replacement_where)
            and $filter
            limit 2500";
	
        
    echo "<h2>Required Specimen Locations</h2>";
	echo "<i>(Multiple locations may appear for the same patient/visit number)</i>";
	echo "<br/><i>(Actual date visit must be within 90 days of the required date visit)</i>";
    echo "<ul><li>$filterTxt</li></ul>";
    
    //echo "<br/><br/>$query<br/><br/>";
	displayQuery($query, "locdiv1", array('Row'), true, false);
}

/**
 *Display known location information for unscanned samples in the gwas_specimens table
 **/
function displayFreezerworksLocations() {
    //freezer check
    $filter = "";
    $filterTxt = "";
    $ses_freezer = mysql_real_escape_string(trim($_SESSION['freezer']));
    if(isset($ses_freezer) && strlen($ses_freezer) > 0 && ($ses_freezer <> "%")) {
        $filter .= " and VwLocations_active.freezer = '$ses_freezer'";
        $filterTxt .= "freezer = '$ses_freezer'<br/>";
    }
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if(isset($ses_replacement) && strlen($ses_replacement) > 0 && ($ses_replacement <> "%")) {
        $filter .= " and gwas_specimens.replacement = '$ses_replacement'";
        $filterTxt .= "and tube replacement number = '$ses_replacement'<br/>";
    }    
    
    // freezerworks locations
    $query = "select gwas_specimens.replacement as replacement_num,
        if(gwas_specimens.pid in (select pid from gwas_spec_err) >= 1, 'true', 'false') as PID_has_scan_err,
        gwas_specimens.pid as Req_PID, gwas_specimens.vnum as Req_visit_num, gwas_specimens.vdate as Req_date_visit,
        items.sample_type as sample_type,
        items.date_visit as act_date_visit,
        VwLocations_active.freezer as Freezer,
        VwLocations_active.subdiv1 as Shelf, 
        VwLocations_active.subdiv2 as Rack,
        VwLocations_active.subdiv3 as Box,
        VwLocations_active.subdiv4 as Row,
        VwLocations_active.subdiv5 as Col
        FROM gwas_specimens
        inner join items on gwas_specimens.pid = items.id_subject and
            gwas_specimens.vnum = items.id_visit
        inner JOIN VwLocations_active ON items.id = VwLocations_active.id_item
            
        where items.sample_type = \"BUFFY\"
        and gwas_specimens.scan_date is NULL
        and items.id_study = 'CRIC'
            
        and  VwLocations_active.id_container = 0
        and items.type = 'tube'
        and VwLocations_active.freezer != ''
        $filter
        limit 2500";
        
	echo "<h2>Required Specimen Presplit Locations</h2>";
	echo "<i>(Multiple locations may appear for the same patient/visit number)</i>";
	echo "<br/><i>(Actual date visit must be within 90 days of the required date visit)</i>";
    echo "<ul><li>$filterTxt</li></ul>";
	displayQuery($query, "locdiv1", array('Row'), true, false);
}

/**
 *Display known location information for unscanned samples in the gwas_specimens table
 **/
function displayFreezerworksBoxLocations() {
    //freezer check
    $filter = "";
    $ses_freezer = mysql_real_escape_string(trim($_SESSION['freezer']));
    if(isset($ses_freezer) && strlen($ses_freezer) > 0 && ($ses_freezer <> "%")) {
        $filter = " and VwLocations_active.freezer = '$ses_freezer'";
        $filterTxt = "freezer = '$ses_freezer'<br/>";
    }
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if(isset($ses_replacement) && strlen($ses_replacement) > 0 && ($ses_replacement <> "%")) {
        $filter = " and gwas_specimens.replacement = '$ses_replacement'";
        $filterTxt = "and tube replacement number = '$ses_replacement'<br/>";
    } 
    
    // freezerworks locations
    $query = "select count(distinct gwas_specimens.id) as total_unique_required_samples, 
        VwLocations_active.freezer as Freezer,
        VwLocations_active.subdiv1 as Shelf, 
        VwLocations_active.subdiv2 as Rack,
        VwLocations_active.subdiv3 as Box
        FROM gwas_specimens
        inner join items on gwas_specimens.pid = items.id_subject and
            gwas_specimens.vnum = items.id_visit
        inner JOIN VwLocations_active ON items.id = VwLocations_active.id_item
            
        where items.sample_type = \"BUFFY\"
        and gwas_specimens.scan_date is NULL
        and items.id_study = 'CRIC'
            
        and  VwLocations_active.id_container = 0
        and items.type = 'tube'
        and VwLocations_active.freezer != ''
        $filter

        group by 
        VwLocations_active.freezer,
                VwLocations_active.subdiv1, 
                VwLocations_active.subdiv2,
                VwLocations_active.subdiv3
        limit 2500";
        
	echo "<h2>Required Specimen Presplit Locations</h2>";
	echo "<i>(Multiple locations may appear for the same patient/visit number)</i>";
	echo "<br/><i>(Actual date visit must be within 90 days of the required date visit)</i>";
    echo "<ul><li>$filterTxt</li></ul>";
	displayQuery($query, "locdiv1", array('Row'), true, false);
}

/**
 *Display known location information for unscanned samples in the gwas_specimens table
 **/
function displaySampleStatus() {
    $replacementFlag = false;
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if ((isset($ses_replacement)) && ($ses_replacement != '') && $ses_replacement != "%") {
        $replacementFlag = true;
    }
    
    $replacement_where = " (replacement = 4 or replacement = 5)";

    if($replacementFlag){
        echo "<br/><br/><h2>Scanned Specimens for replacement $ses_replacement</h2>";
        $query = "SELECT gwas_specimens.id, gwas_specimens.pid, gwas_specimens.replacement as replacement_num, gwas_specimens.vdate, gwas_specimens.vnum,
            gwas_specimens.scan_date, gwas_specimens.tcl_vdate, tcl_labtech, gwas_specimens.barcode
            FROM gwas_specimens
            WHERE not(gwas_specimens.scan_date is NULL) AND scan_date !=  '0000-00-00'
            and replacement = $ses_replacement
            order by replacement desc";
        displayQuery($query, "locdiv2", '',  false, false);
        
        echo "<br/><br/><h2>Unscanned Specimens for replacement $ses_replacement</h2>";
        $query = "SELECT gwas_specimens.pid, gwas_specimens.replacement as replacement_num, gwas_specimens.vdate, gwas_specimens.vnum,
        if(gwas_specimens.pid in (select pid from gwas_spec_err) >= 1, 'true', 'false') as PID_has_scan_err,
        if(concat(gwas_specimens.pid, \"-\", gwas_specimens.vnum) in (select concat(pid, \"-\", tcl_vnum) from gwas_spec_err) >= 1, 'true', 'false') as PID_VDATE_has_scan_err
        FROM gwas_specimens
        WHERE pid not in (select pid from gwas_specimens where barcode != '' and $replacement_where)
        and replacement = $ses_replacement
        order by replacement desc, pid";
    }
    else {   
        echo "<br/><br/><h2>Scanned Specimens</h2>";
        $query = "SELECT gwas_specimens.id, gwas_specimens.pid, gwas_specimens.replacement as replacement_num, gwas_specimens.vdate, gwas_specimens.vnum,
            gwas_specimens.scan_date, gwas_specimens.tcl_vdate, tcl_labtech, gwas_specimens.barcode
            FROM gwas_specimens
            WHERE not(gwas_specimens.scan_date is NULL) AND scan_date !=  '0000-00-00'
            order by replacement desc";
        displayQuery($query, "locdiv2", '',  false, false);
        
        echo "<br/><br/><h2>Unscanned Specimens</h2>";
        $query = "SELECT gwas_specimens.pid, gwas_specimens.replacement as replacement_num, gwas_specimens.vdate, gwas_specimens.vnum,
            if(gwas_specimens.pid in (select pid from gwas_spec_err) >= 1, 'true', 'false') as PID_has_scan_err,
            if(concat(gwas_specimens.pid, \"-\", gwas_specimens.vnum) in (select concat(pid, \"-\", tcl_vnum) from gwas_spec_err) >= 1, 'true', 'false') as PID_VDATE_has_scan_err
            FROM gwas_specimens
            WHERE pid not in (select pid from gwas_specimens where barcode != '')
            order by replacement desc pid";
    }
	displayQuery($query, "locdiv3", '', true, true);
	return;
    
}


/**
 *Display the results of a query in a javascript table.
 * @param string $query
 * @param string $divId The creates a div with this name to display the query
 * @param bool $filter Should the table have a filter
 * @param bool $search Should the query have field searching
 */
function displayQuery($query, $divId = "querydiv", $toCharArray, $filter = true, $search = true) {
	$result = mysql_query($query);
	if (!$result) {
		echo "<br/>unable to perform query: " . mysql_error();
		return;
	}
	if (mysql_affected_rows() == 0) {
		echo "<br/><b>0 results found</b>";
		return;
	}
	echo "<div id='$divId'></div>";
	echo "<script type=\"text/javascript\">";
	echo "var test = new Array();";
	$sArray = "data = new Array(";
	while ($row = mysql_fetch_assoc($result)) {
		$sArray.= "{";
		foreach(array_keys($row) as $key) {
            if (is_array($toCharArray) and in_array($key, $toCharArray))
                $sArray.= "\"$key\": \"" . num2chr($row[$key]) . "\",";
            else
                $sArray.= "\"$key\": \"" . $row[$key] . "\",";
        }
		$sArray = substr($sArray, 0, strlen($sArray) - 1) . "} ,";
	}
	$sArray = substr($sArray, 0, strlen($sArray) - 1) . ");";
	echo "$sArray";
	echo "new TableOrderer('$divId',{data: data, paginate:true, pageCount:10";
	if ($filter) echo ", filter:true ";
	if ($search) echo ", search:true ";
	echo "});";
	echo "</script>";
	return;
}



/**
 *Show all boxes scanned into the bench.  Clicking on a box will make it the active box.
 */
function showBench() {
   //echo "<br/>showBench()";
    //return;
   echo '<div id="benchView" class="benchView" style="width: 300px">';    
   if ($_SESSION['box_array'] && $_SESSION['boxid']) {
        echo '<div class=rowFloat id="bench_header" style="width: 180px;">';
		$box_array = $_SESSION['box_array'];
		foreach(array_keys($box_array) as $k) {
			$v = $box_array[$k]['id'];
			$m = $box_array[$k]['dest'];
			if ($v == $_SESSION['boxid']) {
				$bgcolor = "00000";
				$fgcolor = colorop(getFreezerColor($m), '505050');
				$bordercolor = "000000";
                $borderwidth = "1px";
			} else {
				$fgcolor = "000000";
				$bgcolor = getFreezerColor($m);
                $bordercolor = "000000";
                $borderwidth = "1px";
			}
			echo '<div class="boxFloat" id="boxview_' . $k . '" style="border: '. $borderwidth.' solid  #' . $bordercolor . '; color: #' . $fgcolor . ' ;background-color: #' . $bgcolor . '; cursor: pointer;" onmouseover="highlightCell(\'boxview_' . $k . '\')" onmouseout="resetCell(\'boxview_' . $k . '\')" onclick="postScan(\'' . retUuid($v) . '\')">';
            //echo '<div class="benchBox" id="boxview_' . $k . '" style="border: 1px solid  #' . $bordercolor . '; color: #' . $fgcolor . ' ;background-color: #' . $bgcolor . '; cursor: pointer;" onmouseover="highlightCell(\'boxview_' . $k . '\')" onmouseout="resetCell(\'boxview_' . $k . '\')" onclick="postScan(\'' . retUuid($v) . '\')">';
			echo $m;
		echo '</div>';
		}
		echo '</div>';
	}
    echo '</div><br/><br/><br/>';
}


/**
 *Display the contents of a box.
 *Items that match unfufulled requests in the gwas_specimens table are highlighted.
 *
 * @param string $table
 * @param string $id
 * @param string $divID = "boxdiv"
 * @param string $title = ""
 * @param array $highlightCells
 **/
function displayBox($table, $id, $divID = "boxdiv", $title="", $highlightCells="") {
    $replacement_where = " (replacement = 4 or replacement = 5) ";
    if (trim($id) == "0") {
        echo "Cannot display box<br/>";
        echo "Table: $table<br/>";
        echo "Id: $id<br/>";
        return;
    }
    
    $ses_replacement = mysql_real_escape_string(trim($_SESSION['gwasreplacement']));
    if (!(isset($ses_replacement)) || ($ses_replacement == '') || $ses_replacement == "%") {
        echo "Cannot display box: no replacement selected.";
    }
	global $color;
	$boxid = $id;
	$boxdivX = divY($boxid);
	$boxdivY = divY($boxid);
	if ($boxdivX < 0) {
		$boxdivX = (-1) * $boxdivX;
		$jend = $boxdivX;
		$jstart = 0;
	} else {
		$jend = 0;
		$jstart = $boxdivX;
	}
	if ($boxdivY < 0) {
		$boxdivY = (-1) * $boxdivY;
		$kstart = $boxdivY;
		$kend = 0;
	} else {
		$kend = $boxdivY;
		$kstart = 0;
	}
    
	$boxQuery = mysql_query("SELECT items.id as id, items.id_subject as id_subject,
        items.id_visit as id_visit, items.date_visit as date_visit, gwas_specimens.vdate as gwas_date_visit,
        locations.subdiv4, locations.subdiv5, gwas_specimens.id as gwasID, gwas_specimens.replacement as replacement,
        ((year(items.date_visit) < 1990) or (year(items.date_visit) > 2090) or (datediff(gwas_specimens.vdate, items.date_visit) < -90) or (datediff(gwas_specimens.vdate, items.date_visit) > 90)) as alert,
        ((not gwas_specimens.id is null) and (gwas_specimens.scan_date = '00-00-0000') and (sample_type = 'BUFFY')) as filter,
        (specimens_found.barcode is not null) as filter2,
        items.id_uuid in (select barcode from gwas_spec_err) as error_flag
        FROM `locations` inner join (items) ON (`items`.`id`=`locations`.`id_item`)
        left join gwas_specimens on
            items.id_subject = gwas_specimens.pid and gwas_specimens.vnum = items.id_visit and gwas_specimens.replacement = $ses_replacement
            and gwas_specimens.pid not in (select pid from gwas_specimens where barcode != '' and $replacement_where )
        left join gwas_specimens as specimens_found on specimens_found.barcode = items.id_uuid
        WHERE `id_container` = '$boxid'
        and locations.date_moved is null");
    
    echo mysql_error();
	//
	if (mysql_num_rows($boxQuery) > 0) {
		for ($i = 0;$i < mysql_num_rows($boxQuery);$i++) {
			extract(mysql_fetch_array($boxQuery));
			$subject{$subdiv4} {$subdiv5} = $id_subject;
			$tableid{$subdiv4} {$subdiv5} = $id;
			$tableid_visit{$subdiv4} {$subdiv5} = $id_visit;
            $tablegwas_date_visit{$subdiv4} {$subdiv5} = $gwas_date_visit;
			$tabledate_visit{$subdiv4} {$subdiv5} = $date_visit;
			$tablegwas_id{$subdiv4} {$subdiv5} = $gwasID;
			$selectCell{$subdiv4} {$subdiv5} = ($filter == 1);
            $selectCell2{$subdiv4} {$subdiv5} = ($filter2 == 1);
            $alertCell{$subdiv4} {$subdiv5} = ($alert == 1);
            $errorFlagCell{$subdiv4}{$subdiv5} = ($error_flag == 1);
            $replacementCell{$subdiv4}{$subdiv5} = $replacement;
		}
	}
	mysql_free_result($boxQuery);
    
	//create a matrix
    //echo "<div  id=\"status_action\" style=\"border: 1px solid rgb(192, 192, 192); background-color: rgb(222, 222, 222);\"></div>";
    
    echo '<div class="boxView" style="width:  ' . ((($boxdivY + 1) * 37) + 10) . 'px;">';
    echo "<h2>$title</h2>";
    displayLocationInformation($boxid, 'box');
	echo "<div style=\"border: 5px solid #" . $color{dest($boxid) } . "; background-color: #D0D0D0\">";
	echo '<div class=rowFloat id="row_header" style="width:  ' . (($boxdivY + 1) * 35) . 'px;">';
	echo "<div class=wellFloat></div>";
	for ($kk = 1;$kk <= ($boxdivY);$kk++) {
		if ($kstart > 0) {
			$k = ($kstart - $kk + 1);
		} else {
			$k = $kk;
		}
		echo "<div class=wellFloat>" . $k . "</div>";
	}
	echo '</div>';
	for ($jj = 1;$jj <= $boxdivX;$jj++) {
		if ($jstart > 0) {
			$j = ($jstart - $jj + 1);
		} else {
			$j = $jj;
		}
		echo '<div class=rowFloat id="row_' . $j . '" style="width:  ' . (($boxdivY + 1) * 35) . 'px;">';
		unset($next_j);
		echo "<div class=wellFloat>" . num2chr($j) . "</div>";
		for ($kk = 1;$kk <= ($boxdivY);$kk++) {
			if ($kstart > 0) {
				$k = ($kstart - $kk + 1);
			} else {
				$k = $kk;
			}
            // if the cell is required or has already been scanned, make the cell black or grey
			if (($selectCell{$j} {$k} == true) || ($selectCell2{$j} {$k} == true)) {
				
                if ($selectCell{$j} {$k} == true) {
                    $color = array(
                        "red" => 0,
                        "green" => 0,
                        "blue" => 0
                    );
                }
                else
                    $color = array(
                        "red" => 90,
                        "green" => 90,
                        "blue" => 90
                    );
                // if there is something wrong with the date, color the text red
                if($alertCell{$j}{$k})
                    $cellText = '<font color="red" size="-4">' . $tablegwas_id{$j}{$k} . '</font><br/>';
                else
                    $cellText = '<font color="white" size="-4">' . $tablegwas_id{$j}{$k} . '</font><br/>';
                
				$tooltip = "Required Sample ID: " . $tablegwas_id{$j} {$k}
                . "<br/>pid:" . $subject{$j} {$k}
                . "<br/>visit num: " . $tableid_visit{$j} {$k}
                . "<br/>Replacement num: " . $replacementCell{$j} {$k}
                . "<br/>required visit date: " . $tablegwas_date_visit{$j} {$k}
                . "<br/>...actual visit date: " . $tabledate_visit{$j} {$k}
                . "<br/>UUID: " . getUUID($tableid{$j}{$k});
			} else {
				$color = generateRGBColor($subject{$j} {$k});
                $cellText = "";
				$tooltip = "pid:" . $subject{$j} {$k}
                . "<br/>visit num: " . $tableid_visit{$j} {$k}
                . "<br/>visit date: " . $tabledate_visit{$j} {$k}
                . "<br/>UUID: " . getUUID($tableid{$j}{$k});
			}
            
            // if there was an error, add it to the tooltip text
            if($errorFlagCell{$j}{$k}) {
                $tooltip .= '<br/><b>Error logged for this barcode</b>';
                $cellText .= '<font size="-4" color = "red"><b>E</b></font>';
            }
            
            $tooltip = "tooltip('" . $tooltip . "')";
            
            //if the cell is highlighted, changed it's border color
            if((is_array($highlightCells)) and in_array(array($j, $k), $highlightCells)) {
                $border = "1px solid yellow";
                //$cellText .= '<font size="-4"><b>X</b></font>';
            }
            else
                $border = "1px solid #000";
            
			$bgColor = 'rgb(' . $color["red"] . ',' . $color["green"] . ',' . $color["blue"] . ')';
			$onClick = 'getItemId(\'' . $tableid{$j} {$k} . '\')';
            
			if (($j <= ($boxdivX)) && ($k >= 1)) {
				echo '<div class=wellFloat id="well_' . $j . $k . '" 
        			style="background-color: ' . $bgColor . ';
                    border:'.$border.';
                    cursor: pointer;" 
                    onMouseOver="' . $tooltip . '"
        			onMouseOut="exit()"
                    onClick="' . $onClick . '"><center>' . $cellText . '</center>';
				echo "</div>\n";
			}
		}
		echo "</div>";
	}
    
		echo "<div class=\"boxfoot\">";
		echo '<div><input type="button" value="print box labels" onclick="printlabel(' . $boxid . ',\'items\',\'1\')">';
		echo '<input type="button" value="new box" onclick="replaceBox(' . $boxid . ')">';
		//echo '<input type="button" value="export" onclick="window.location.href=\'npc.php?action=data&format=csv\'"></div>';
		echo '<div><input type="button" value="manifest" onclick="window.location.href=\'npc.php?action=manifest&id=' . $boxid . '\'"></div>';
		echo "</div>";
    
	echo "</div>";
    echo "</div>"; // close boxview
    
    /*
    echo "<pre>";
    print_r($highlightCells);
    echo "</pre>";
    
    if(is_array($highlightCells))
        echo "<br/><br/> in array: " . in_array(array(1,9), $highlightCells);
    */
}

/**
 *Generate an RGB color from an 8 digit number.
 *Used with displayBox to generate different background colors for tubes based on subject id.
 **/
function generateRGBColor($subject) {
	if ($subject != 0) {
		$fx = (pi() * $subject / 10000000);
		$gx = ((pi() * ($subject) * (1 / 10)));
		$r = round(128 * (1 + sin($fx)));
		$g = round(128 * (1 + cos($fx)));
		$b = round(130 + 32 * (1 + cos($gx)));
	} else {
		$r = 250;
		$g = 250;
		$b = 250;
	}
	return array(
		"red" => $r,
		"green" => $g,
		"blue" => $b
	);
}

/**
 *Returns the next free tube in the given box
 * @param int $boxid
 *
 * @return array($x, $y)
 **/
function getNextTubePosition($boxid) {
	$boxdivX = 9;
	$boxdivY = 9;
	$next_spot = mysql_query("select subdiv4,subdiv5 from `locations` where id_container = '" . $boxid . "' and date_moved is null");
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
	} else {
        //echo "none filled";
		$y = 1;
		$x = 1;
	}
    $result{0} = $x;
    $result{1} = $y;
    return $result;
}

/**
 *
 */
function getLastBox($rackId) {
    $query = "select id_item, subdiv3 from vwlocations_active where id_container = $rackId and subdiv3 =
        (select max(cast(subdiv3 as unsigned)) from vwlocations_active where id_container = $rackId)";

    $result = mysql_query($query);
    if (!$result) {
        echo 'Could not run query: ' . mysql_error();
        exit;
	}
    $boxarray = mysql_fetch_array($result);
    
    return $boxarray['id_item'];
}

/**
 *Create a new box in the given rack
 * @param int $rackId
 **/
function createNewBox($rackId) {
    //get last rack location
    $result = mysql_query("select max(subdiv3) as maxbox from locaitons where id_container = $rackId");
    $boxArray = mysql_fetch_array($result);
    $boxLoc = $boxArray['maxbox'] + 1;
    
    $items_query = "insert into items (id_uuid, divX, divY, type) values(UUID(), 9, 9, 'box')";
    mysql_query($items_query);
    $boxId = mysql_insert_id();
    
    $locations_query = "insert into locations (id_item, id_container, subdiv3) values($boxId, $rackId, $boxLoc)";
    
    return $boxId;
}
?>