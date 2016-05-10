<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
/**
 * Main page for the Pulls.
 *
 * Displays status information on what has been scanned
 *
 * Allows the user to scan boxes and tubes
 */
lib('bench');
lib('querydisplay');
include $GLOBALS['root_dir'].'/www/tasks/include/iInventory.php';
include $GLOBALS['root_dir'].'/www/tasks/include/cPull.php';

$myPull;

function onPageLoad()
{
    // set default action, update if new action posted
    if ((!isset($_SESSION['id_pull']))) {
        echo "<div>There is no pull selected.</div>";
    }
    if (isset($_POST['id_pull'])) {
        $_SESSION['id_pull'] = $_POST['id_pull'];
    }

    global $myPull;

    // only make a new Pull instance if it doesn't already exist
    $idPull = mysql_real_escape_string(trim($_SESSION['id_pull']));
    if (isset($idPull)) {
        if ($myPull == null) {
            $myPull = new Pull($idPull);
            //echo "new pull($idPull)";
        } else {
            $myPull->UpdateIdPull($idPull);
            //echo "update pull($idPull)";
        }
    } else {
        $myPull = null;
    }

    displayHeader();
    defaultView();
}

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
 *  Update the logs and the gwas_* tables..
 */
function operateScannedObject($table, $id)
{
    global $myPull;
    $updateLocationFlag = true; // const
    $logFlag = true;  // const
    $labtech = $_SESSION['username'];

    // only make a new Pull instance if it doesn't already exist
    $idPull = mysql_real_escape_string(trim($_SESSION['id_pull']));
    if (isset($idPull)) {
        if ($myPull == null) {
            $myPull = new Pull($idPull);
        } else {
            $myPull->UpdateIdPull($idPull);
        }
    } else {
        $myPull = null;
    }

    // clear main div
    echo '<script type="text/javascript">';
    echo "$('staticcontainer').innerHTML = '<div id=\"actioncontainer\"></div>';";
    echo "Effect.ScrollTo('staticcontainer', { duration:'0.2', offset:-20 });";
    echo '</script>';

    if (is_null($myPull) || (!$myPull->IsPullSet())) {
        displayHeader();
        echo "<div class=\"alert-warning\">There is no pull selected.</div>";

        return;
    }

    if ($table != "items") {
        displayHeader();
        echo "<div class='alert-warning'>Scanned object not in 'items' table.</div>";
        if ($logFlag) {
            $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'Scanned object not in items table', '', '', '');
        }
        defaultView();

        return;
    }

    // get item info
    $result = mysql_query("SELECT * from $table where id = $id");
    $row = mysql_fetch_array($result);

    //display the contents of the box and add it to the bench
    if ($row['type'] == "box") {
        boxArray($id);
        displayHeader();
        //if($logFlag) $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, '', '', '', 'show box','', '');
        displayPullBox($table, $id, "boxdiv", "Destination Box");

        return;
    }
    // move the tube if everything matches
    elseif ($row['type'] == "tube") {
        displayHeader();

        //-----------error checking
        // make sure we have a box selected
        $boxid = getActiveBenchBox();
        if (!isset($boxid) || (!$boxid)) {
            echo "<div class=\"alert-error\">Unable to move sample: No destination box selected.</div>";

            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'no box selected', '', '', '');
            }

            return;
        }

        if ($boxid == 0) {
            echo "<div class=\"alert-error\">Unable to move sample: Invalid destination box selected, id = $boxid.</div>";
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'invalid box selected', '', '', '');
            }

            return;
        }

        // make sure box has a valid destionation
        $boxdest = dest($boxid);
        $allowedBoxdest = $myPull->GetAllowedBoxDestinations();
        /*if ((!$boxdest) ||
            !(($boxdest == 'test-01') || ($boxdest == 'test-02') || ($boxdest == 'test-03') || ($boxdest == 'biomek') || ($boxdest == 'Pulled') || ($boxdest == 'BRB'))) {*/
        if ((!$boxdest) || (!in_array($boxdest, $allowedBoxdest))) {
            $allowedDestString = implode(",", $allowedBoxdest);
            echo "<div class=\"alert-error\">Unable to move sample, box destination incorrect: '$boxdest'.  Must be one of: $allowedDestString.</div>";
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'box destination incorrect', '', '', '');
            }

            return;
        }

        //get source box
        $sourceloc = getTubeLocation($id);

        // make sure source box is not dest box
        if ($boxid == $sourceloc['id_container']) {
            echo "<div class=\"alert-error\">Unable to move sample: Source box and destination box ($boxid) are the same.</div>";
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'box full', '', '', '');
            }

            return;
        }

        // make sure dest box is not full
        $boxpos = getNextTubePosition($boxid);
        if ($boxpos[0] == 0) {
            echo "<div class=\"alert-error\">Unable to move sample: Destination box ($boxid) is full.</div>";
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'box full', '', '', '');
            }

            return;
        }

        //make sure source box has been scanned into the bench
        if (in_array($sourceloc['id_container'], getBenchArray()) == false) {
            echo "<div class=\"alert-error\">Unable to move sample: Source box is not in the bench.</div><br/>";
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, 'source box not in bench', '', $sourceloc['id_container'], '');
            }

            if (($sourceloc) && (strlen($sourceloc['id_container']) > 0)) {
                echo '<div id="box1"  style:"border: 5px solid #;", "Source Box">';
                displayPullBox($table, $sourceloc['id_container'], "boxdivsource", "Source Box", array($sourceloc['position']));
                echo '</div>';
            } else {
                echo "Source Box Unknown";
            }

            displayPullBox($table, $boxid, "boxdivdest", "Destination Box");

            return;
        }

        // error check the tube
        $error = $myPull->ErrorCheckTubeID($id, $boxid);
        if ($error != "ok") { // tube error occured
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id,  $row['type'], $labtech, $error, '', $sourceloc['id_container'], '');
            }
            // update gwas_spec_err
            echo "<div class=\"alert-error\">Error when scanning tube, item location not changed.";
            echo "<ul>";
            echo "<li>Error: $error</li>";
            echo "<li>Barcode: ".$row['id_uuid']."</li>";
            echo "<li>Patient ID: ".$row['id_subject']."</li>";
            echo "<li>Visit Number: ".$row['id_visit']."</li>";
            echo "<li>Date Visit: ".$row['date_visit']."</li>";
            echo "<li>Sample Type: ".$row['sample_type']."</li>";
            echo "</ul>";
            echo "</div>";

            echo '<div class="row">';
            if ($updateLocationFlag) {
                if (($sourceloc) && (strlen($sourceloc['id_container']) > 0)) {
                    //echo '<div id="box1"  style:"border: 5px solid #;", "Source Box">';
                        echo '<div class="span5">';
                    displayPullBox($table, $sourceloc['id_container'], "boxdivsource", "Source Box", array($sourceloc['position']));
                    echo '</div>';
                } else {
                    echo "Source Box Unknown";
                }
            }
            echo '<div class="span5">';
            displayPullBox($table, $boxid, "boxdivdest", "Destination Box");
            echo '</div>';
            echo '</div>'; //row-fluid
            return;
        }
        //-----error checks passed

        // update locations
        $insertResult = false;

        mysql_query("start transaction");
        // update pull
        $pullResult = $myPull->PullTube($id, $row['id_uuid'], $labtech, $sourceloc['id_container'], $sourceloc['subdiv4'], $sourceloc['subdiv5'], $boxid, $boxpos[0], $boxpos[1], false);
        $updateStatus = $pullResult['status'];
        // update location
        if (($updateLocationFlag) && ($updateStatus == 'ok') && ($boxid != $sourceloc['id_container'])) {
            $updateStatus = insertTubeIntoBox($boxid, $id, false);
        }

        if ($updateStatus != "ok") {
            mysql_query("rollback");
        } else {
            mysql_query("commit");
        }

        //display success message
        if ($updateStatus == "ok") {
            // update gwas_specimens
            if ($updateLocationFlag) {
                echo "<font class=\"success\">Item scanned successfully, item location updated.</font>";
                if ($logFlag) {
                    $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, $error, '', $sourceloc['id_container'], $boxid, $pullResult['id_pull_requirements']);
                }
            } else {
                echo "<font class=\"success\">Item scanned successfully. Testing mode, item location not updated.</font>";
                if ($logFlag) {
                    $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, $error, '', $sourceloc['id_container'], $boxid, $pullResult['id_pull_requirements']);
                }
            }
            echo "<ul>";
            echo "<li>Barcode: ".$row['id_uuid']."</li>";
            echo "<li>Patient ID: ".$row['id_subject']."</li>";
            echo "<li>Visit Number: ".$row['id_visit']."</li>";
            echo "<li>Date Visit: ".$row['date_visit']."</li>";
            echo "<li>Sample Type: ".$row['sample_type']."</li>";
            echo "</ul><br/>";
        } else {
            echo "<br/><font class=\"alert-error\">Error when pulling and updating item location.</font>";
            echo "<ul><li>Reason: $updateStatus<li/></ul>";
            if ($logFlag) {
                $myPull->InsertPullLog($row['id_uuid'], $id, $row['type'], $labtech, $updateStatus, '', $sourceloc['id_container'], $boxid);
            }
        }

        // display location gui
        if ($updateLocationFlag) {
            echo '<div class="row">';
            if (($sourceloc) && (strlen($sourceloc['id_container']) > 0)) {
                //$sourceloc
                //echo '<div id="box1"  style:"border: 5px solid #;", "Source Box">';
                echo '<div class="span5">';
                displayPullBox($table, $sourceloc['id_container'], "boxdivsource", "Source Box", array($sourceloc['position']));
                echo '</div>';
            } else {
                echo "Source Box Unknown";
            }
            echo '<div class="span5">';
            displayPullBox($table, $boxid, "boxdivdest", "Destination Box", array($boxpos));
            echo '</div>';
            echo '</div>'; // </div class=row>
        } else {
            displayPullBox($table, $boxid, "boxdivdest", "Destination Box");
        }

        return;
    } else { //the scanned object was neither a box nor a tube, do nothing
        echo "<font class=\"alert\">This action can only be perfomred on a tube or a box.</font>";
        defaultView();
    }
}

/**
 *Display location and basic status info for the pull items
 */
function defaultView()
{
    /*
    echo "<pre>";
    print_r($_SESSION);
    echo"</pre>";
    */
    global $myPull;
    if (is_null($myPull) || (!$myPull->IsPullSet())) {
        return;
    }
    echo "<form method='POST' name='gwasstudy_displayform'>";
    echo '<button name="gwasstudy_sps" value="true" type="submit">Search for Tubes in Selected Freezer</button>';
    /*
    echo '<button name="gwasstudy_freezerworks" value="true" type="submit">Show Pre-split Tube Locations for Selected Freezer</button><br/>';
    echo '<button name="gwasstudy_freezerworksbox" value="true" type="submit">Show Pre-split Boxes for Selected Freezer</button>';
    */
    echo '</form><br/>';

    if (isset($_POST['gwasstudy_sps']) && $_POST['gwasstudy_sps'] == 'true') {
        displaySPSLocations();
    } elseif (isset($_POST['gwasstudy_freezerworks'])  && $_POST['gwasstudy_freezerworks'] == 'true') {
        displayFreezerworksLocations();
    } elseif (isset($_POST['gwasstudy_freezerworksbox']) && $_POST['gwasstudy_freezerworksbox'] == 'true') {
        displayFreezerworksBoxLocations();
    }

    echo "<br/><br/><h2>Requested Samples</h2>";
    displayRequestedSamples();

    echo "<br/><br/><h2>Scanned Samples</h2>";
    displayScannedSamples();
}

/**
 *Display known location information for unscanned samples in the gwas_specimens table
 **/
function displayHeader()
{
    global $myPull;
    if ($myPull) {
        $fieldData = $myPull->GetFieldData();

        $staticTxt = '';
        $dynamicTxt = '';
        foreach (array_keys($fieldData) as $field) {
            if ($fieldData[$field]['status'] == 'static') {
                if (isset($fieldData[$field]['value2'])) {
                    $staticTxt .= "$field = ".$fieldData[$field]['value'].", ".$fieldData[$field]['value2']."<br/>";
                } elseif (isset($fieldData[$field]['max'])) {
                    $staticTxt .= $fieldData[$field]['value']." <= $field  "." <= ".$fieldData[$field]['max']."<br/>";
                } else {
                    $staticTxt .= "$field = ".$fieldData[$field]['value']."<br/>";
                }
            } elseif ($fieldData[$field]['status'] == 'dynamic') {
                if (isset($fieldData[$field]['range'])) {
                    $dynamicTxt .= "$field, range=".$fieldData[$field]['range']."<br/>";
                } else {
                    $dynamicTxt .= "$field<br/>";
                }
            }
        }

        $fieldData = $myPull->GetFieldData();

        $fieldData = $myPull->GetDestinationFieldData();
        foreach (array_keys($fieldData) as $field) {
            if ($fieldData[$field]['status'] == 'static') {
                if (isset($fieldData[$field]['value2'])) {
                    $staticTxt .= "$field = ".$fieldData[$field]['value'].", ".$fieldData[$field]['value2']."<br/>";
                } elseif (isset($fieldData[$field]['max'])) {
                    $staticTxt .= $fieldData[$field]['value']." <= $field  "." <= ".$fieldData[$field]['max']."<br/>";
                } else {
                    $staticTxt .= "$field = ".$fieldData[$field]['value']."<br/>";
                }
            } elseif ($fieldData[$field]['status'] == 'dynamic') {
                if (isset($fieldData[$field]['range'])) {
                    $dynamicTxt .= "$field, range=".$fieldData[$field]['range']."<br/>";
                } else {
                    $dynamicTxt .= "$field<br/>";
                }
            }
        }
    }

    showBench();

    // print change pull form
    if (isset($_SESSION['id_study'])) {
        $id_study = $_SESSION['id_study'];
    } else {
        $id_study = "none";
    }
    $myPulls = new Pulls();
    $activePulls = $myPulls->GetActivePulls($id_study);

    echo "<div>";
    echo "<form method='POST' name='pull_form'>";
    echo "<select name='id_pull'>";
    $currentGroupName = "";
    foreach ($activePulls as $pullHeaderRow) {
        if ($currentGroupName != $pullHeaderRow['group_name']) {
            $currentGroupName = trim($pullHeaderRow['group_name']);
            if (strlen($currentGroupName) != 0) {
                echo "<option value=''>--$currentGroupName--</option>";
            }
        }
        if ($_SESSION['id_pull'] == $pullHeaderRow['pull_header_id']) {
            echo "<option value = '".$pullHeaderRow['pull_header_id']."' selected>".$pullHeaderRow['pull_name']."</option>";
        } else {
            echo "<option value = '".$pullHeaderRow['pull_header_id']."'>".$pullHeaderRow['pull_name']."</option>";
        }
    }
    echo "</select>";
    echo "<input type='submit' value = 'Change Selected Pull'>";
    echo "</form>";
    echo "</div>";

    echo "<div>";
    if (is_null($myPull) || (!$myPull->IsPullSet())) {
        echo "<h2>No Pull Selected</h2>";
    } else {
        echo "<h2>".$myPull->GetPullName()."</h2>";
        echo "<i>".$myPull->GetPullDescription()."</i><br/>";
        echo "<table style=\"border:1px solid black;border-collapse:collapse;\">";
        echo "<tr><th style=\"border:1px solid black;\">Samples must have&nbsp&nbsp&nbsp&nbsp</th>";
        echo "<th style=\"border:1px solid black;\">Searching on fields</th></tr><tr>";
        echo "<td style=\"border:1px solid black;\">$staticTxt</td>";
        echo "<td style=\"border:1px solid black;\">$dynamicTxt</td>";
        echo "</tr></table>";
    }
    echo "</div>";
}

function displaySPSLocations()
{
    global $myPull;

    //generate where statement based on the selected freezer and the pull
    $filterTxt = "";
    $ses_freezer = mysql_real_escape_string(trim($_SESSION['freezer']));

    // do a slow search over one freezer
    if (isset($ses_freezer) && strlen($ses_freezer) > 0 && ($ses_freezer != "%") && ($ses_freezer != "quick_box")) {
        $qryWhere = $myPull->GetWhereStatement('VwTubeAndLocations');
        $qryJoin = $myPull->GetJoinStatement('VwTubeAndLocations');
        $qrySelect = $myPull->GetSelectStatement('VwTubeAndLocations');

        $qryWhere .= " and VwShelfAndLocations.freezer = '$ses_freezer'";
        $filterTxt .= "freezer = '$ses_freezer'<br/>";

        //deep sps locations
        $query = "SELECT pull_requirements.id as request_id, $qrySelect,
			substr(VwTubeAndLocations.id_uuid,1,8) as tube_uuid,
			substr(VwBoxAndLocations.id_uuid,1,8) as box_uuid,

			/*
			VwBoxAndLocations.id_uuid as box_uuid,
			VwTubeAndLocations.id_uuid as tube_uuid,
			VwTubeAndLocations.id as item_id,
			VwTubeAndLocations.id_uuid as tube_uuid,
			VwBoxAndLocations.id_uuid as box_uuid,
			*/
			VwShelfAndLocations.freezer as Freezer,
			VwShelfAndLocations.subdiv as Shelf,
			VwRackAndLocations.subdiv as Rack,
			VwBoxAndLocations.subdiv as Box,
			VwTubeAndLocations.subdivx as Row,
			VwTubeAndLocations.subdivy as Col
			FROM
				VwTubeAndLocations inner join pull_requirements on $qryJoin
				inner join VwBoxAndLocations on VwTubeAndLocations.id_container = VwBoxAndLocations.id
				inner join VwRackAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
				inner join VwShelfAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
			WHERE $qryWhere
			limit 2500";
    }
    // do a quick search and show box uuids
    elseif (isset($ses_freezer) && strlen($ses_freezer) > 0 && ($ses_freezer != "%") && ($ses_freezer == "quick_box")) {
        $qryWhere = $myPull->GetWhereStatement('items');
        $qryJoin = $myPull->GetJoinStatement('items');
        $qrySelect = $myPull->GetSelectStatement('items');

        $filterTxt = "<i><b>May not contain location information for items moved in from the past 24 hours.<br/>
		For up to date search, choose a freezer.</b></i>";

        //quick sps locations
        $query = "SELECT pull_requirements.id as request_id, $qrySelect,
			substr(items.id_uuid,1,8) as tube_uuid,
			box_items.id_uuid as box_uuid,
			/*
			items.id as item_id,
			items.id_uuid as tube_uuid,
			locations.id_container as box_id,*/
			locations.freezer as Freezer,
			locations.subdiv1 as Shelf,
			locations.subdiv2 as Rack,
			locations.subdiv3 as Box,
			locations.subdiv4 as Row,
			locations.subdiv5 as Col

			FROM
			items inner join VwLocations_active as locations on items.id = locations.id_item
			inner join items as box_items on locations.id_container = box_items.id
			inner join pull_requirements on $qryJoin

			WHERE $qryWhere
			and id_container != 0
			and items.type = \"tube\"
			and (freezer != \"niddk\" or freezer is NULL)
			/*dont use samples in active pulls*/

			limit 2500";
    } else { // do a fast location search over all freezers
        $qryWhere = $myPull->GetWhereStatement('items');
        $qryJoin = $myPull->GetJoinStatement('items');
        $qrySelect = $myPull->GetSelectStatement('items');

        $filterTxt = "<i><b>May not contain location information for items moved in from the past 24 hours.<br/>
		For up to date search, choose a freezer.</b></i> <br/>Only display the first 500 matches.";

        //quick sps locations
        $query = "SELECT pull_requirements.id as request_id, $qrySelect,
			substr(items.id_uuid,1,8) as tube_uuid,
			locations.id_container as box_id,
			/*
			items.id as item_id,
			items.id_uuid as tube_uuid,
			locations.id_container as box_id,*/
			locations.freezer as Freezer,
			locations.subdiv1 as Shelf,
			locations.subdiv2 as Rack,
			locations.subdiv3 as Box,
			locations.subdiv4 as Row,
			locations.subdiv5 as Col
			FROM
			items inner join VwLocations_active as locations on items.id = locations.id_item
			inner join pull_requirements on $qryJoin

			WHERE $qryWhere
			and id_container != 0
			and items.type = \"tube\"
			and (freezer != \"niddk\" or freezer is NULL)
			/*dont use samples in active pulls*/

			limit 500";
    }

    echo "<h2>Required Specimen Locations</h2>";
    echo "<i>(Multiple locations may appear that satisfy the same requirement)</i>";
    echo "<ul><li>$filterTxt</li></ul>";

    displayQuery($query, "locdiv1", array('Row'), true, false);
}

/**
 *Display known location information for unscanned samples in the gwas_specimens table
 **/
function displayFreezerworksLocations()
{
    global $myPull;

    //generate where statement based on the selected freezer and the pull
    $filterTxt = "";
    $qryWhere = $myPull->GetWhereStatement('items');
    $qryJoin = $myPull->GetJoinStatement('items');
    $qrySelect = $myPull->GetSelectStatement('items');

    $ses_freezer = mysql_real_escape_string(trim($_SESSION['freezer']));
    if (isset($ses_freezer) && strlen($ses_freezer) > 0 && ($ses_freezer != "%")) {
        $qryWhere .= " and VwLocations_active.freezer = '$ses_freezer'";
        $filterTxt .= "freezer = '$ses_freezer'<br/>";
    }

    // freezerworks locations
    $query = "select pull_requirements.id as request_id, $qrySelect,
		items.id as item_id,
        VwLocations_active.freezer as Freezer,
        VwLocations_active.subdiv1 as Shelf,
        VwLocations_active.subdiv2 as Rack,
        VwLocations_active.subdiv3 as Box,
        VwLocations_active.subdiv4 as Row,
        VwLocations_active.subdiv5 as Col
        FROM
		items inner join pull_requirements on $qryJoin
        inner JOIN VwLocations_active ON items.id = VwLocations_active.id_item
        where $qryWhere
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
function displayFreezerworksBoxLocations()
{
    global $myPull;

    //generate where statement based on the selected freezer and the pull
    $filterTxt = "";
    $qryWhere = $myPull->GetWhereStatement('items');
    $qryJoin = $myPull->GetJoinStatement('items');

    $ses_freezer = mysql_real_escape_string(trim($_SESSION['freezer']));
    if (isset($ses_freezer) && strlen($ses_freezer) > 0 && ($ses_freezer != "%")) {
        $qryWhere .= " and VwLocations_active.freezer = '$ses_freezer'";
        $filterTxt .= "freezer = '$ses_freezer'<br/>";
    }

    // freezerworks locations
    $query = "select count(distinct pull_requirements.id) as total_unique_required_samples,
        VwLocations_active.freezer as Freezer,
        VwLocations_active.subdiv1 as Shelf,
        VwLocations_active.subdiv2 as Rack,
        VwLocations_active.subdiv3 as Box
        FROM items
        inner join pull_requirements on $qryJoin
        inner JOIN VwLocations_active ON items.id = VwLocations_active.id_item

        where $qryWhere

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

/*
 * Display data as defined in data_array("requestedSamples")
*/
function displayRequestedSamples()
{
    global $myPull;
    if ($myPull == null) {
        return;
    }
    $qrySelect = $myPull->GetSelectStatement('none', 'pull_requirements');
    $query = "SELECT id as request_id, fufilled, $qrySelect from pull_requirements where id_pull_header = ".$myPull->GetIdPull()." limit 150";
    //echo $query;

    //return;
    echo "Total Requests: ".$myPull->GetRequestCount()." Unfulfilled Requests: ".$myPull->GetUnfufilledRequestCount()."<br/>";
    displayQuery($query, "locdiv2", '',  false, false);
    echo '<a href="npc.php?action=data&format=csv&type=requestedSamplesExport&filename=pullrequest&filenameadddate=true">export csv</a>';
    //displayTable('pull_requirements', "locdiv2", $myPull->GetFieldNames('dynamic'), array('fufilled'), array("id_pull_header = " . $myPull->GetIdPull()));


/*
    echo "Total Requests: " . $myPull->GetRequestCount() . " Unfulfilled Requests: ". $myPull->GetUnfufilledRequestCount() . "<br/>";
    echo "<div id=locdiv2></div>";
    echo '<script type="text/javascript">';
    echo "new TableOrderer('locdiv2',{url : 'npc.php?action=data&format=json&type=requestedSamples' , paginate:true, search:true, pageCount:10, filter:true})";
    echo '</script>';
    echo '<a href="npc.php?action=data&format=csv&type=requestedSamplesExport&filename=pullrequest&filenameadddate=true">export csv</a>';
    */
}

function displayScannedSamples()
{
    global $myPull;
    if ($myPull == null) {
        return;
    }
    echo "Total Scanned Samples: ".$myPull->GetScannedCount();
    $query = "SELECT id_pull_requirements as request_id, id_uuid as tube_uuid, scan_date, scanned_by from pull_specimens
		where id_pull_requirements in (select id from pull_requirements where id_pull_header = ".$myPull->GetIdPull().")
		order by request_id";
    displayQuery($query, "locdiv3", '',  true, true);
    echo '<a href="npc.php?action=data&format=csv&type=scannedSamplesExport&filename=pullscanned&filenameadddate=true">export csv</a>';
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
function displayPullBox($table, $id, $divID = "boxdiv", $title = "", $highlightCells = null)
{
    global $myPull;
    if ($myPull == null) {
        return;
    }

    // make sure box is valid
    $boxid = trim($id);
    if (($boxid == "0") || ($table != 'items')) {
        echo "Cannot display box<br/>";
        echo "Table: $table<br/>";
        echo "Id: $id<br/>";

        return;
    }

    $color;
    $boxdivX = divX($boxid);
    $boxdivY = abs(divY($boxid));
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

    // display the static and dynamic search fields from the 'items' table
    $itemFieldNames = $myPull->GetFieldNames('static');
    $itemFieldNames =  array_merge($myPull->GetFieldNames('dynamic'), $itemFieldNames);
    $itemFieldNames =  array_merge($myPull->GetDestinationFieldNames(), $itemFieldNames);

    $nonPullItemFieldName = array('subject', 'item_sample_type', 'item_id_visit');

    $qrySelect = $myPull->GetSelectStatement('items', 'pull_requirements');
    $qryJoin = $myPull->GetJoinStatement('items');
    $qryWhere = $myPull->GetWhereStatement('items', false, false);
    $qryPullId = $myPull->GetIdPull();
    $qryDateCheck = $myPull->GetDateRangeSelect();
    // alert =
    // filter = tube matches requirements, not scanned in
    // filter2 = tube scanned in
    // filter3 = tube matches all requirements except date range
    // error_flag = error logged for tube
    $query = "SELECT items.id as id, pull_requirements.id as pull_id, items.id_subject as subject,
		items.sample_type as item_sample_type,
		items.id_visit as item_id_visit,
		locations.subdiv4, locations.subdiv5, $qrySelect,

		0 as alert,
        ((pull_requirements.id is not null) and (pull_specimens.id is null) and (pull_requirements.fufilled = 0) and ($qryWhere))  as filter,
        (pull_specimens.id is not null) as filter2,
		$qryDateCheck as filter3,
        0 as error_flag

        FROM `locations` inner join (items) ON (`items`.`id`=`locations`.`id_item`)
        left join pull_requirements on
			$qryJoin and pull_requirements.id_pull_header = $qryPullId
        left join pull_specimens on pull_specimens.id_pull_requirements = pull_requirements.id
			and pull_specimens.id_uuid = items.id_uuid

		WHERE `id_container` = '$boxid'
			and locations.date_moved is null";

    $boxResult = mysql_query($query);

    if (!$boxResult) {
        echo "Could not perform box query: ".mysql_error();

        return;
    }

    if (mysql_num_rows($boxResult) > 0) {
        while ($row = mysql_fetch_assoc($boxResult)) {
            //print_r($row);
            //print($query);
            $subdiv4 = $row['subdiv4'];
            $subdiv5 = $row['subdiv5'];

            $subject{$subdiv4}
            {$subdiv5}
            = $row['subject'];
            $tableid{$subdiv4}
            {$subdiv5}
            = $row['id'];
            $tablepull_id{$subdiv4}
            {$subdiv5}
            = $row['pull_id'];

            foreach ($itemFieldNames as $field) {
                $itemsData{$field}
                {$subdiv4}
                {$subdiv5}
                = $row[$field];
            }
            foreach ($nonPullItemFieldName as $field) {
                $itemsData{$field}
                {$subdiv4}
                {$subdiv5}
                = $row[$field];
            }

            $selectCell{$subdiv4}
            {$subdiv5}
            = ($row['filter'] == 1); // tube matches requirements and is not scanned in
            $selectCell2{$subdiv4}
            {$subdiv5}
            = ($row['filter2'] == 1); // tube has been scanned in
            $dateAlertCell{$subdiv4}
            {$subdiv5}
            = ($row['filter3'] == 1); // tube is out of date range
            $alertCell{$subdiv4}
            {$subdiv5}
            = ($row['alert'] == 1); // there is an alert for the tube
            $errorFlagCell{$subdiv4}
            {$subdiv5}
            = ($row['error_flag'] == 1); // there was an error logged for the tube
            $cohortLockAlertCell{$subdiv4}
            {$subdiv5}
            = ($myPull->IsTubeCohortNotLocked($row['id']) != 'ok');
        }
    }
    mysql_free_result($boxResult);

    //create a matrix
    //echo "<div  id=\"status_action\" style=\"border: 1px solid rgb(192, 192, 192); background-color: rgb(222, 222, 222);\"></div>";

    echo '<div class="row span7 boxViewContainer">';
    //echo '<div class="boxViewContainer">';
    echo "<h2>$title</h2>";

    displayLocationInformation($boxid, 'box');
    if (isset($_SESSION['box_array'])) {
        foreach ($_SESSION['box_array'] as $box) {
            $available_destinations[$box['dest']] = true;
        }
    }
    echo "<div id=\"ItemInfo\" class =\"ItemInfo\"></div>";  // item detail in inserted here

    echo "<div style=\"border: 5px solid #".$color{dest($boxid) }
    ."; background-color: #D0D0D0\">";
    echo '<div class=row id="row_header">';
    echo "<div class=wellFloat></div>";
    for ($kk = 1;$kk <= ($boxdivY);$kk++) {
        if ($kstart > 0) {
            $k = ($kstart - $kk + 1);
        } else {
            $k = $kk;
        }
        echo "<div class=wellFloat>".$k."</div>";
    }
    echo '</div>'; // </div class=rowFloat>

    for ($jj = 1;$jj <= $boxdivX;$jj++) {
        if ($jstart > 0) {
            $j = ($jstart - $jj + 1);
        } else {
            $j = $jj;
        }
        echo '<div class=row id="row_'.$j.'">';
        unset($next_j);
        echo "<div class=wellFloat>".num2chr($j)."</div>";
        for ($kk = 1;$kk <= ($boxdivY);$kk++) {
            //no backround image by default
            $pull_class ='';
            if ($kstart > 0) {
                $k = ($kstart - $kk + 1);
            } else {
                $k = $kk;
            }
            // empty cell
            if (!$subject{$j}{$k}) {
                $bgColor = sprintf('#%02x%02x%02x', 255, 255, 255);
                unset($tooltip);
            }
            // if the cell is required or has already been scanned, make the cell black or grey
            elseif (($selectCell{$j} {$k} == true) || ($selectCell2{$j} {$k} == true)) {
                if ($selectCell{$j}{$k} == true) {
                    $bgColor = sprintf('#%02x%02x%02x', 0, 0, 0);
                    $pull_class = 'pull-spec';
                } else {
                    $bgColor = sprintf('#%02x%02x%02x', 90, 90, 90);
                }

                // if alert, color the font red
                if ($alertCell{$j}{$k}) {
                    $cellText = '<font color="red" size="-4">'.$tablegwas_id{$j}
                    {$k}
                    .'</font><br/>';
                } else {
                    $cellText = '<font color="white" size="-4">'.$tablegwas_id{$j}
                    {$k}
                    .'</font><br/>';
                }

                // generate the tooltip
                $tooltip = "Required Sample ID: ".$tablepull_id{$j}
                {$k};
                $tooltip .= "<br/>UUID: ".getUUID($tableid{$j}{$k});
                foreach ($itemFieldNames as $field) {
                    $tooltip .= "<br/>$field: ".$itemsData{$field}
                    {$j}
                    {$k};
                }
            } else {
                //$color = generateRGBColor($subject{$j} {$k});
                $bgColor = genColor($subject{$j}{$k});
                $cellText = "";
                $tooltip = "UUID: ".getUUID($tableid{$j}{$k});
                foreach ($nonPullItemFieldName as $field) {
                    $tooltip .= "<br/>$field: ".$itemsData{$field}
                    {$j}
                    {$k};
                }
            }

            // if there was an error, add it to the tooltip text
            if ($errorFlagCell{$j}{$k}) {
                $tooltip .= '<br/><b>Error logged for this barcode</b>';
                $cellText .= '<font size="-4" color = "red"><b>E</b></font>';
            }
            if ($dateAlertCell{$j}{$k}) {
                $tooltip .= '<br/><b>Date out of range</b>';
                $cellText .= '<font size="-4" color = "red"><b>D</b></font>';
            }
            if ($cohortLockAlertCell{$j}{$k}) {
                $tooltip .= '<br/><b>Cohort is locked</b>';
                $cellText .= '<font size="-4" color = "red"><b>L</b></font>';
            }

            $mouseOver = '';
            if (isset($tooltip)) {
                $mouseOver = "onMouseOver=\"tooltip('$tooltip')\";
            		onMouseOut=\"exit()\";";
            }
            //$tooltip = "tooltip('" . $tooltip . "')";

            //if the cell is highlighted, changed it's border color
            if ((is_array($highlightCells)) and in_array(array($j, $k), $highlightCells)) {
                $border = "1px solid yellow";
                //$cellText .= '<font size="-4"><b>X</b></font>';
            }
            // if date alert, highlight the cell
            elseif ($dateAlertCell{$j}{$k}) {
                $border = "1px solid red";
            } else {
                $border = "1px solid #000";
            }

            $onClick = 'getItemId(\''.$tableid{$j}
            {$k}
            .'\')';

            if (($j <= ($boxdivX)) && ($k >= 1)) {
                $div_id = "well_".$j.$k;
                print "<div class='wellFloat $pull_class' id='$div_id'
        			       style='background-color: $bgColor;
                                       border: $border;
                                       cursor: pointer;'
                                       $mouseOver
                                       onClick='$onClick'>";
                if (isset($itemsData)
                                    && isset($itemsData['box_destination'])
                                    && isset($itemsData{'box_destination'}{$j}{$k})
                                    && array_key_exists($itemsData{'box_destination'}{$j}{$k}, $available_destinations)) {
                    $box_destination = $itemsData{'box_destination'}{$j}
                    {$k};
                    $dest_color = stringToColorCode($box_destination);
                    print '<div class=destFloat  style="background-color:#'.$dest_color.'">'.$cellText.'</div>';
                } else {
                }
                echo "</div>\n";
            }
        }
        echo "</div>"; // </div class=rowFloat>
    }

    echo "<div class=\"boxfoot\">";
    echo '<div><input type="button" value="print box labels" onclick="printlabel('.$boxid.',\'items\',\'1\')">';
    echo '<input type="button" value="new box" onclick="replaceBox('.$boxid.')">';
        //echo '<input type="button" value="export" onclick="window.location.href=\'npc.php?action=data&format=csv\'"></div>';
        echo '<input type="button" value="manifest" onclick="window.location.href=\'npc.php?action=manifest&id='.$boxid.'\'"></div>';
    echo "</div>";

    echo "</div>"; // close style
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
 *Used with displayPullBox to generate different background colors for tubes based on subject id.
 **/
// see http://stackoverflow.com/questions/3724111/how-can-i-convert-strings-to-an-html-color-code-hash
/*function hue($tstr) {
    return unpack('L', hash('adler32', $tstr, true))[1];
}

function hsl2rgb($H, $S, $V) {
    $H *= 6;
    $h = intval($H);
    $H -= $h;
    $V *= 255;
    $m = $V*(1 - $S);
    $x = $V*(1 - $S*(1-$H));
    $y = $V*(1 - $S*$H);
    $a = [[$V, $x, $m], [$y, $V, $m],
          [$m, $V, $x], [$m, $y, $V],
          [$x, $m, $V], [$V, $m, $y]][$h];
    return array(
        "red" => $a[0],
        "green" => $a[1],
        "blue" => $a[2]
    );
}*/

function generateRGBColor($subject)
{
    if ($subject != 0) {
        $fx = (pi() * $subject / 10000000);
        $gx = ((pi() * ($subject) * (1 / 10)));
        $r = round(128 * (1 + sin($fx)));
        $g = round(128 * (1 + cos($fx)));
        $b = round(130 + 32 * (1 + cos($gx)));

        //$subjHue = hue($subject);
        //return hsl2rgb($subjHue/0xFFFFFFFF, 0.4, 1);
    } else {
        $r = 255;
        $g = 255;
        $b = 255;
    }

    return array(
        "red" => $r,
        "green" => $g,
        "blue" => $b,
    );
}
function stringToColorCode($str)
{
    $code = dechex(crc32($str));
    $code = substr($code, 0, 6);

    return $code;
}

/**
 *Called from npc.php?action=data&type=[$type]
 */
function data_array($type)
{
    global $myPull;

    // only make a new Pull instance if it doesn't already exist
    $idPull = mysql_real_escape_string(trim($_SESSION['id_pull']));
    if (isset($idPull)) {
        if ($myPull == null) {
            $myPull = new Pull($idPull);
            //echo "new pull($idPull)";
        } else {
            $myPull->UpdateIdPull($idPull);
            //echo "update pull($idPull)";
        }
    } else {
        $myPull = null;
    }

    if ($type == 'requestedSamples') {
        $qrySelect = $myPull->GetSelectStatement('none', 'pull_requirements', false, false);
        $query = "SELECT pull_requirements.id as request_id, fufilled as fulfilled, count(pull_specimens.id) as pulled_aliquots, $qrySelect
		from pull_requirements left join pull_specimens
		on pull_requirements.id = pull_specimens.id_pull_requirements
		where id_pull_header = ".$myPull->GetIdPull()." group by pull_requirements.id limit 7000";

        $detailArray = array();
        $result = mysql_query($query);
        if (!result) {
            $returnArray = array();
            array_push($returnArray, array("error" => "Query failed", "error_msg" => mysql_error()));

            return $returnArray;
        }

        while ($row = mysql_fetch_assoc($result)) {
            $field = 'fufilled';
            $width = 3;
            $id = $row['request_id'];
            $val = $row['fulfilled'];
            if ($row['fulfilled']) {
                $strval = "true";
            } else {
                $strval = "false";
            }
            $updateString = '<div name="'.$strval.'" class="content" id="'.$field.$id.'" style="width:'.($width * 10).'px; background-color: lightgreen;">'.$strval;
            $updateString .= "<script type='text/javascript'>";
            $updateString .= "
				new Ajax.InPlaceCollectionEditor('".$field.$id."',
					'npc.php?action=ed',
					{collection:[[0, 'false'], [1, 'true']],
					formClassName: 'left_column', size: ".$width.", callback: function(form, value) { return 'value=' + escape(value)+'&id=".$id."&field=".$field."&table=pull_requirements'}})";
            $updateString .= "</script></div>";
            $row['fufilled'] = $updateString;
            array_push($detailArray, $row);
        }

        /*$returnArray = array();
        array_push($returnArray, array("updateString"=>"<pre>$updateString</pre>"));
        return $returnArray;*/

        return $detailArray;
    } elseif ($type == 'requestedSamplesExport') {
        $returnArray = array();

        $qrySelect = $myPull->GetSelectStatement('none', 'pull_requirements', false, false);
        $query = "SELECT id_pull_header as id_pull_header, pull_requirements.id as request_id, fufilled as fulfilled, count(pull_specimens.id) as pulled_aliquots, $qrySelect
			from pull_requirements left join pull_specimens
			on pull_requirements.id = pull_specimens.id_pull_requirements
			where id_pull_header = ".$myPull->GetIdPull()." group by pull_requirements.id";
        $result = mysql_query($query);

        while ($row = mysql_fetch_assoc($result)) {
            array_push($returnArray, $row);
        }

        return $returnArray;
    } elseif ($type == 'scannedSamplesExport') {
        $returnArray = array();

        $qrySelect = $myPull->GetSelectStatement('none', 'pull_requirements', false, false);
        $query = "SELECT '".$myPull->GetIdPull()."' as id_pull_header, id_pull_requirements as request_id, id_uuid as tube_uuid, scan_date, scanned_by from pull_specimens
			where id_pull_requirements in (select id from pull_requirements where id_pull_header = ".$myPull->GetIdPull().")
			order by request_id";
        $result = mysql_query($query);

        while ($row = mysql_fetch_assoc($result)) {
            array_push($returnArray, $row);
        }

        return $returnArray;
    } else {
        $returnArray = array();
        array_push($returnArray, array("error" => "Invalid type", "type" => $type));

        return $returnArray;
    }
}
