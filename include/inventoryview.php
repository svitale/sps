<?php
/*Functions called from the 'inventory' tab
*/

/*
 Return an array of all of the freezers that match the current session filter.

linking to the items table so that the filter can be added makes this query expensive; might change to just show all freezers
*/
function getFreezerList() {
    $filter = generateFilterFromSession("items.id_study", "items.sample_type", "items.shipment_type", "items.id_visit");
    
    /*
    $statement = "select distinct freezer
        from locations left join items on locations.id_item = items.id
        where (isnull(locations.date_moved) or locations.date_moved = '0000-00-00')
            and $filter order by freezer";
    */      
    $statement = "select distinct freezer from locations order by freezer";
    $freezerQuery = mysql_query($statement);
    
    $aResult = array();
    echo mysql_error();
    if(mysql_affected_rows() > 0) {
        while($row = mysql_fetch_array($freezerQuery)) {
            array_push($aResult, $row['freezer']);
        }
    }
    return $aResult;
}

/*
Displays the contents of a given container.  Uses the items.id -> locations.items_id -> locations.container_id heirarchy to determine contents.
Uses the session filter variables.

$freezerName - exact string value of locations.freezer. Does an exact match against the value in locations.freezer associated with a "tube" item
$shelfId - items.id for a "shelf" type item
$rackId - items.id for a "rack" type item
$boxId - items.id for a "box" type item
    If a value of 'null' is passed, searches for a null container.
*/
function freezerInventory($freezerName) {
    $freezerName = mysql_real_escape_string($freezerName);
    
    $nullVal = 'null';
    $filter = generateFilterFromSession();
    
    $title = "Freezer " . $freezerName;
    
    $statement = "Select items3_id as groupId, items3_id_uuid as groupUuid, count(distinct items3_id) as groupCount, count(items_id) as tubeCount from items_locations where $filter ";
    
    if (strlen($freezerName) == 0 or ($freezerName == $nullVal)){
        $statement .= " and isNull(freezer)";
        $title = "Freezer unknown";
    }
    else {
        $statement .= "and freezer = '$freezerName'";
    }
    
    $statement .= " group by items3_id";
    
    $cQuery = mysql_query($statement);
    echo "<h1>$title</h1>";
    while($cArray = mysql_fetch_array($cQuery)) {
        if (strlen($cArray['groupId']) > 0) $itemTitle = $cArray['groupId'];
        else $itemTitle = "<b>NONE</b>";
        
        $uuid = $cArray['groupUuid'];
        echo "<div class='container' id='shelf_". $cArray['groupId'] ."'onclick=\"shelfSelect('$freezerName', '" . $cArray['groupId'] . "');\" onMouseOver=\"tooltip('$uuid')\" onMouseOut=\"exit()\">" . $itemTitle . " - " . $cArray['tubeCount'] . " tubes
        <input type='button' value='Scan' onclick=\"inventoryDetail('$uuid');\"/>
        </div>";
    }
}

function shelfInventory($freezerName, $shelfId) {
    $freezerName = mysql_real_escape_string($freezerName);
    $shelfId = mysql_real_escape_string($shelfId);
    $nullVal = 'null';
    $filter = generateFilterFromSession();
    
    $statement = "Select items2_id as groupId, items2_id_uuid as groupUuid, count(distinct items2_id) as groupCount, count(items_id) as tubeCount
        from items_locations where $filter ";
    
    $title = "Shelf " . $shelfId;
    if (strlen($freezerName) == 0 or ($freezerName == $nullVal))
        $statement .= "and isNull(freezer) ";
    else  $statement .= " and freezer = '$freezerName' ";
    
    if (strlen($shelfId) == 0 or ($shelfId == $nullVal)){
        $statement .= "and isNull(items3_id) ";
        $title = "Shelf unknown";
    }
    else $statement .= "and items3_id = '$shelfId' ";
    
    $statement .= " group by items2_id";

    $cQuery = mysql_query($statement);
    echo "<h1>$title</h1>";
    while($cArray = mysql_fetch_array($cQuery)) {
        if (strlen($cArray['groupId']) > 0) $itemTitle = $cArray['groupId'];
        else $itemTitle = "<b>NONE</b>";
        
        $uuid = $cArray['groupUuid'];
        echo "<div id='rack_". $cArray['groupId'] ."'onclick=\"rackSelect('$freezerName', '$shelfId', '" . $cArray['groupId'] . "'); \"onMouseOver=\"tooltip('$uuid')\" onMouseOut=\"exit()\">Rack " . $itemTitle . " - " . $cArray['tubeCount'] . " tubes
            <input type='button' value='Scan' onclick=\"inventoryDetail('$uuid');\"/>
            </div>";
    }
}

function rackInventory($freezerName, $shelfId, $rackId){
    $freezerName = mysql_real_escape_string($freezerName);
    $shelfId = mysql_real_escape_string($shelfId);
    $rackId = mysql_real_escape_string($rackId);
    
    $nullVal = 'null';
    $filter = generateFilterFromSession();
    
    $statement = "Select items1_id as groupId, items1_id_uuid as groupUuid, count(items_id) as groupCount, count(items_id) as tubeCount
        from items_locations where $filter ";
    
    $title = "Rack " . $rackId;
     if (strlen($freezerName) == 0 or ($freezerName == $nullVal))
        $statement .= "and isNull(freezer) ";
    else  $statement .= "and freezer = '$freezerName' ";
    
    if (strlen($shelfId) == 0 or ($shelfId == $nullVal))
        $statement .= "and isNull(items3_id) ";    
    else $statement .= "and items3_id = '$shelfId' ";
    
    if (strlen($rackId) == 0 or ($rackId == $nullVal)) {
        $statement .= "and isNull(items2_id) ";
        $title = "Rack unknown";
    }
    else $statement .= "and items2_id = '$rackId' ";
    
    $statement .= " group by items1_id";

    $cQuery = mysql_query($statement);
      
    echo "<h1>$title</h1>";
    $dArray = array();
    while($cArray = mysql_fetch_array($cQuery)) {
        array_push($dArray, $row);
        if (strlen($cArray['groupId']) > 0) $itemTitle = $cArray['groupId'];
        else $itemTitle = "<b>NONE</b>";
       
       $uuid = $cArray['groupUuid']; 
        echo "<div id='box_". $cArray['groupId'] ."'onclick=\"boxSelect('$freezerName', '$shelfId', '$rackId', '" . $cArray['groupId'] . "');\" onMouseOver=\"tooltip('$uuid')\" onMouseOut=\"exit()\">Box " . $itemTitle . " - " . $cArray['tubeCount'] . " tubes
            <input type='button' value='Scan' onclick=\"inventoryDetail('$uuid');\"/>
            </div>";
    }
    
    //displayContainerJson($title, $dArray);
}

function displayContainerJson($title, $dArray){
    echo "<h1>$title</h1>";
    
    header("Content-Type: application/json");
	if (is_null($dArray)) {
        echo "[]";
	} else {
		echo json_encode($dArray);
	}
    
    /*
    echo "<pre>";
    print_r($dArray);
    echo "</pre>";
    */
}

function boxInventory($freezerName, $shelfId, $rackId, $boxId){
    $freezerName = mysql_real_escape_string($freezerName);
    $shelfId = mysql_real_escape_string($shelfId);
    $rackId = mysql_real_escape_string($rackId);
    $boxId = mysql_real_escape_string($boxId);
    
    $nullVal = 'null';
    $filter = generateFilterFromSession();
    
    $statement = "Select distinct items_id as id, items_id_uuid as uuid
        from items_locations where $filter ";
    
    $title = "Box " . $boxId;
     if (strlen($freezerName) == 0 or ($freezerName == $nullVal))
        $statement .= "and isNull(freezer) ";
    else  $statement .= "and freezer = '$freezerName' ";
    
    if (strlen($shelfId) == 0 or ($shelfId == $nullVal))
        $statement .= "and isNull(items3_id) ";    
    else $statement .= "and items3_id = '$shelfId' ";
    
    if (strlen($rackId) == 0 or ($rackId == $nullVal)) {
        $statement .= "and isNull(items2_id) ";
    }
    else $statement .= "and items2_id = '$rackId' ";
    
    if (strlen($boxId) == 0 or ($boxId == $nullVal)) {
        $statement .= "and isNull(items1_id) ";
        $title = "Box unknown";
    }
    else $statement .= "and items1_id = '$boxId' ";

    $cQuery = mysql_query($statement);
    echo "<h1>$title</h1>";
    while($cArray = mysql_fetch_array($cQuery)) {
        //echo "<div id='rack_". $cArray['id'] ."'onclick=\"boxSelect('$freezerName', '$shelfId', '$rackId', '" . $cArray['id'] . "');\">Box " . $cArray['id'] . " - " . $cArray['tube_count'] . " tubes </div>";
        echo "Tube " . $cArray['id'];
        echo "<input type='button' value='Scan' onclick=\"inventoryDetail('$uuid');\"/>";
    }
}



// generate a filter for the view 'items_locations' based on the session variables set by filter options on the left of the screen
function generateFilterFromSession($id_study_field = "items_id_study", $sample_type_field = "items_sample_type", $shipment_type_field = "items_shipment_type", $id_visit_field = "items_id_visit"){
    $id_study = mysql_real_escape_string(trim($_SESSION['id_study']));
    $sample_type = mysql_real_escape_string(trim($_SESSION['sample_type']));
    $shipment_type = mysql_real_escape_string(trim($_SESSION['shipment_type']));
    $id_visit = mysql_real_escape_string(trim($_SESSION['id_visit']));
    
    $filter = "true";
    if(isset($id_study) && strlen($id_study) > 0 && ($id_study <> "%"))
        $filter .= " and $id_study_field = '$id_study'";
    if(isset($sample_type) && strlen($sample_type) > 0 && ($sample_type <> "%"))
        $filter .= " and $sample_type_field = '$sample_type'";
    if(isset($shipment_type) && strlen($shipment_type) > 0 && ($shipment_type <> "%"))
        $filter .= " and $shipment_type_field = '$shipment_type'";
    if(isset($id_visit) && strlen($id_visit) > 0 && ($id_visit <> "%"))
        $filter .= " and $id_visit_field = '$id_visit'";
        
    return $filter;
}
