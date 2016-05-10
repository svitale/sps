<?php
/*
* Display the location of samples
*
* REQUIRES pear module HTML_TreeMenu
*
* There are two methods for describing the location of samples, the presplit from
* freezerworks and the method used by SPS.
* It is possible for an SPS sample to have only partial location information.
* The freezer is defined in the 'rack' of the SPS method, if a sample is 
* 'Orphaned' it will be listed under the freezer for the tube.
* Presplit + SPS Samples + Orphaned SPS Samples = total samples
*
* Presplit:
* locations.id_container = 0
* Only the tube has an entry in the items table; the box, rack and shelf do not and thus do not have uuids.
* The entire sample location is defined in one row of the locations table.
*   locations.id_item - foreign key to items table where item.type = 'tube'
*   locations.freezer - freezer location
*   locations.subdiv1 - rack location
*   locations.subdiv2 - shelf location
*   locations.subdiv3 - box location
*   locations.subdiv4 - x position in box
*   locations.subdiv5 - y position in box
*
* SPS:
* locations.id_container != 0
* The tube, box, rack and shelf have rows in the items table.
* Uses edge-adjacency list model to describe sample location. The locations table
* points to an item, it's container item, and the items location within the container.
* 
*   locations.id_item - foreign key to items table
*   locations.id_container - foreign key to the items table, item that contains locations.id_item
*   locations.freezer - freezer location, valid if the type of id_item is 'rack'
*   locations.subdiv1 - rack location, valid if the type of id_item is 'rack'
*   locations.subdiv2 - shelf location, valid if the type of id_item is 'shelf'
*   locations.subdiv3 - box location, valid if the type of id_item is 'box'
*   locations.subdiv4 - x position in box, valid if the type of id_item is 'tube'
*   locations.subdiv5 - y position in box, valid if the type of id_item is 'tube'
*   items.divx, items.divy - the dimensions of the box or rack
*/

// clear the scan session variables
/*
$id_study = $_SESSION['id_study'];
$printer_name = $_SESSION['printer_name'];
$task = $_SESSION['task'];
$id_study = $_SESSION['id_study'];
$sample_type = $_SESSION['sample_type'];
$shipment_type = $_SESSION['shipment_type'];
$id_visit = $_SESSION['id_visit'];
$username =  $_SESSION['username'];
session_unset();
$_SESSION['task'] = $task;
$_SESSION['id_study'] = $id_study;
$_SESSION['printer_name'] = $printer_name;
$_SESSION['id_study'] = $id_study;
$_SESSION['sample_type'] = $sample_type;
$_SESSION['shipment_type'] = $shipment_type;
$_SESSION['id_visit'] = $id_visit;
$_SESSION['username'] = $username;
*/

lib('querydisplay');

if(isset($_POST['freezerSelect'])) {
	$freezerName = mysql_real_escape_string($_POST['freezerSelect']);
	$_SESSION['freezerName'] = $freezerName;
	if(isset($_SESSION['reportName']))  {
		unset($_SESSION['reportName']);
	}
} else if (isset($_POST['reportSelect'])) {
	$reportName = mysql_real_escape_string($_POST['reportSelect']);
	$_SESSION['reportName'] = $reportName;
	if(isset($_SESSION['freezerName']))  {
		unset($_SESSION['freezerName']);
	}
} else if(isset($_SESSION['freezerName'])) {
	$freezerName = ($_SESSION['freezerName']);
} else if(isset($_SESSION['reportName'])) {
        $reportName = ($_SESSION['reportName']);
} 
?>


<?php
lib('HTML_TreeMenu')
?>
<div id="detailcontainer" class="span4 float-right"></div>

<div class="span10">
<h2>Inventory Browser</h2>

<?php 
    $id_study = mysql_real_escape_string(trim($_SESSION['id_study']));
    if(!isset($id_study)) {
        echo "<div>Please select a study.</div>";
        return;
    }
?>

<form method="post">
    View inventory by freezer:
    <select id="freezerSelect" name="freezerSelect">
    <?php
        $freezerArray = getFreezerArray();
        foreach ($freezerArray as $freezer) {
            if($freezer == $freezerName)
                echo "<option value='$freezer' selected>$freezer</option>";
            else
                echo "<option value='$freezer'>$freezer</option>";
        }
    ?>
    </select>
    <input type="submit" value="Search"/>
</form>
<form method="post">
    View reports:
    <select id="reportSelect" name="reportSelect">
    <?php
        $reportArray = getReportArray();
        foreach ($reportArray as $report) {
            if(isset($reportName) && $report == $reportName)
                echo "<option value='$report' selected>$report</option>";
            else
                echo "<option value='$report'>$report</option>";
        }
    ?>
    </select>
    <input type="submit" value="Search"/>
</form>

<?php
// display inventory tree or reports
if(isset($freezerName)) {
	//echo "<br/><br/>";
	displayFreezer($freezerName);
} else if(isset($reportName))  {
	displayReport($reportName);
}

echo "</div>";

/*REPORT VIEW*/
function getReportArray($divString = "----") {
    $aResult = array();
    //array_push($aResult, "Unfilled Freezers");
    array_push($aResult, "Homeless Racks");
 //   array_push($aResult, "Homeless boxes");
//    array_push($aResult, "Homeless tubes");
    array_push($aResult, "Unfilled Shelves");
    array_push($aResult, "Unfilled Racks");
    array_push($aResult, "Unfilled Boxes");
    return $aResult;
}

/* print an HTML version of the report
* $reportName: name of the report to display
*/
function displayReport($reportString) {
	// parse filter values
	/*$filter = array("sql" => "true", "text" => "");
	
	$id_study = mysql_real_escape_string(trim($_SESSION['id_study']));
	$id_study_field = "VwBoxAndLocations.id_study";
    if(isset($id_study) && strlen($id_study) > 0 && ($id_study <> "%")) {
        $filter['sql'] = $filter['sql'] . " and $id_study_field = '$id_study'";
        $filter['text'] = $filter['text'] . "id_study = '$id_study'<br/>";
    }
	else {
		$filter['text'] = $filter['text'] . "no filter<br/>";
	}
	
	$whereStatement = $filter['sql'];
	*/
    switch($reportString) {
        case "Homeless Racks":
			$filter = generateFilterFromSession('items.id_study', false, false, false);
			$whereStatement = $filter['sql'];
			
            $title = "<h3>Homeless Racks Report</h3>";
            $title .= "<i><smaller>" . $filter['text'] . "</smaller></i>";
			$query = "select
			id_uuid,
			items.destination,
			id_study,
			sample_type,
			timestamp,
			count(*) num_boxes
			from (select items.id box_id,locations.id_container id_rack
				from items left join locations on items.id = locations.id_item
				where items.type = 'box'
				and items.id in (select id_container from locations)) boxes
			left join (select items.id id_rack,locations.id_container id_shelf
				from items left join locations on items.id = locations.id_item where items.type = 'rack') racks
				on boxes.id_rack  = racks.id_rack
			left join items on items.id = racks.id_rack
			where id_shelf is null and boxes.id_rack is not null and items.destination != ''
			and $whereStatement
			group by racks.id_rack";
            break;
		
        case "Unfilled Freezers":
			$filter = generateFilterFromSession('VwShelfAndLocations.id_study', false, false, false);
			$whereStatement = $filter['sql'];
			
            $title = "<h3>Unfilled Freezers Report</h3>";
            $title .= "<i><smaller>" . $filter['text'] . "</smaller></i>";
			$query = "select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.divx as total_shelves,
            count(VwShelfAndLocations.subdiv) as checked_in_shelves
            from VwShelfAndLocations
            where $whereStatement
			
            group by VwShelfAndLocations.freezer
            
            having total_shelves != count(VwShelfAndLocations.subdiv)
            and VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'";
            break;
        case "Unfilled Shelves":
			$filter = generateFilterFromSession('VwShelfAndLocations.id_study', false, false, false);
			$whereStatement = $filter['sql'];
			
            $title = "<h3>Unfilled Shelves</h3>";
            $title .= "<i><smaller>" . $filter['text'] . "</smaller></i>";
            $query = "select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.subdiv as shelf,
            VwShelfAndLocations.divx as total_racks,
            count(VwRackAndLocations.subdiv) as checked_in_racks,
            VwShelfAndLocations.id_uuid as uuid
            from VwShelfAndLocations
            inner join VwRackAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
            where $whereStatement
			
            group by VwShelfAndLocations.freezer,
            VwShelfAndLocations.subdiv
            having total_racks != count(VwRackAndLocations.subdiv)
            and VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'
			
			union
			
			/*empty racks*/
			select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.subdiv as shelf,
            VwShelfAndLocations.divx as total_racks,
            0 as checked_in_racks,
            VwShelfAndLocations.id_uuid as uuid
            from VwShelfAndLocations
            where $whereStatement
			and VwShelfAndLocations.id not in (select VwRackAndLocations.id_container from VwRackAndLocations)
			
            group by VwShelfAndLocations.freezer
            having VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'";
            break;
        case "Unfilled Racks":
			$filter = generateFilterFromSession('VwRackAndLocations.id_study', false, false, false);
			$whereStatement = $filter['sql'];
			
            $title = "<h3>Unfilled Racks</h3>";
            $title .= "<i><smaller>" . $filter['text'] . "</smaller></i>";
            $query = "select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.subdiv as shelf, 
            VwRackAndLocations.subdiv as rack,
            VwRackAndLocations.divx * VwRackAndLocations.divy as total_boxes,
            count(VwBoxAndLocations.subdiv) as checked_in_boxes,
            VwRackAndLocations.id_uuid as uuid
            from VwShelfAndLocations
            inner join VwRackAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
            inner join VwBoxAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
			where $whereStatement
                    
            group by VwShelfAndLocations.freezer,
            VwShelfAndLocations.subdiv, 
            VwRackAndLocations.subdiv
            having total_boxes != count(VwBoxAndLocations.subdiv)
            and VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'
			
			union
			
			/*empty racks*/
			select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.subdiv as shelf, 
            VwRackAndLocations.subdiv as rack,
            VwRackAndLocations.divx * VwRackAndLocations.divy as total_boxes,
            0 as checked_in_boxes,
            VwRackAndLocations.id_uuid as uuid
            from VwShelfAndLocations
            inner join VwRackAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
			where $whereStatement
			and VwRackAndLocations.id not in (select VwBoxAndLocations.id_container from VwBoxAndLocations)
                    
            group by VwShelfAndLocations.freezer,
            VwShelfAndLocations.subdiv, 
            VwRackAndLocations.subdiv
            having 
            VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'";
            break;
        case "Unfilled Boxes":
			$filter = generateFilterFromSession('VwBoxAndLocations.id_study', 'VwBoxAndLocations.sample_type', 'VwBoxAndLocations.shipment_type', 'VwBoxAndLocations.id_visit');
			$whereStatement = $filter['sql'];
			
            $title = "<h3>Unfilled Boxes</h3>";
            $title .= "<i><smaller>" . $filter['text'] . "</smaller></i>";
            $query = "select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.subdiv as shelf, 
            VwRackAndLocations.subdiv as rack,
            VwBoxAndLocations.subdiv as box,
            abs(VwBoxAndLocations.divx * VwBoxAndLocations.divy) as total_tubes,
            count(VwTubeAndLocations.subdivx) as checked_in_tubes,
            abs(VwBoxAndLocations.divx * VwBoxAndLocations.divy) - count(VwTubeAndLocations.subdivx) as empty_spaces,
            VwBoxAndLocations.id_uuid as uuid
            from VwShelfAndLocations
            inner join VwRackAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
            inner join VwBoxAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
            inner join VwTubeAndLocations on VwTubeAndLocations.id_container = VwBoxAndLocations.id
            where $whereStatement
			
            group by VwShelfAndLocations.freezer,
            VwShelfAndLocations.subdiv, 
            VwRackAndLocations.subdiv,
            VwBoxAndLocations.subdiv
            having total_tubes != count(VwTubeAndLocations.subdivx)
            and VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'
			
			union
			
			/*empty boxes*/
			select
            VwShelfAndLocations.freezer as freezer,
            VwShelfAndLocations.subdiv as shelf, 
            VwRackAndLocations.subdiv as rack,
            VwBoxAndLocations.subdiv as box,
            abs(VwBoxAndLocations.divx * VwBoxAndLocations.divy) as total_tubes,
            0 as checked_in_tubes,
            abs(VwBoxAndLocations.divx * VwBoxAndLocations.divy) as empty_spaces,
            VwBoxAndLocations.id_uuid as uuid
            from VwShelfAndLocations
            inner join VwRackAndLocations on VwRackAndLocations.id_container = VwShelfAndLocations.id
            inner join VwBoxAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
            where $whereStatement
			and VwBoxAndLocations.id not in (select VwTubeAndLocations.id_container from VwTubeAndLocations)
			
            group by VwShelfAndLocations.freezer,
            VwShelfAndLocations.subdiv, 
            VwRackAndLocations.subdiv,
            VwBoxAndLocations.subdiv
            having
            VwShelfAndLocations.freezer != 'niddk'
            and VwShelfAndLocations.freezer != 'Temp Holding Freezer'
            and VwShelfAndLocations.freezer != 'TESTING'";
            break;
    }
    
    if(isset($reportString)) {
        echo $title . "<br/>";
		//echo $query;
        displayQuery($query, "reportDiv", array('Row'), true, true, true);
    }
    
    return;
}


/* Display the results of a SQL query in a HTML/javascript table
* $query: the query to execute
* $divId: the id of the html div element that will contain the table.  written by the function, should not already be used
* $filter: should the filter be enabled on the table
* $search: should search be enabled on the table
* $paginate: should the table be paginated
* $recordsPerPage: records displayed if $paginate=true
*/
function displayReportQuery($query, $divId = "querydiv", $filter = true, $search = true, $paginate = true, $recordsPerPage = 10) {
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
		foreach(array_keys($row) as $key) $sArray.= "\"$key\": \"" . $row[$key] . "\",";
		$sArray = substr($sArray, 0, strlen($sArray) - 1) . "} ,";
	}
	$sArray = substr($sArray, 0, strlen($sArray) - 1) . ");";
	echo "$sArray";
	echo "new TableOrderer('$divId',{data: data";
    if ($paginate) echo ", paginate:true, pageCount:$recordsPerPage ";
	if ($filter) echo ", filter:true ";
	if ($search) echo ", search:true ";
	echo "});";
	echo "</script>";
	return;
}



/*FREEZER VIEW*/
/**
 *returns an array of all the freezers in use by SPS and the strings "Orphaned Samples" and "Presplit"
 **/
function getFreezerArray($divString = "----") {

    $id_study = mysql_real_escape_string(trim($_SESSION['id_study']));
    if(!isset($id_study)) {
        $id_study = 'foobar';

    }
    //$statement = "select distinct freezer from VwShelfAndLocations order by freezer where items_id_study == '$id_study'";
    $statement = "select distinct comment1 as freezer from items where type='freezer' and id_study = '$id_study' order by comment1";
    $freezerQuery = mysql_query($statement);
    echo mysql_error();
    
    //$aResult = array("Presplit");
    //array_push($aResult, $divString);
    $aResult = array($aResult, "Unknown Samples");
    array_push($aResult, $divString);

    if(mysql_affected_rows() > 0) {
        while($row = mysql_fetch_array($freezerQuery)) {
            if (!is_null($row['freezer']))
                array_push($aResult, $row['freezer']);
        }
    }
    return $aResult;
}


/* print an HTML version of the inventory
* $freezerName: freezer name, "Unknown Samples" for all unknown samples, "Presplit" for presplit samples
*/
function displayFreezer($freezerName){
    switch($freezerName) {
        case 'Presplit';
            generatePresplitFreezerTree($freezerName);
            break;
        case 'Unknown Samples':
            generateUnknownSPSFreezerTree($freezerName);
            break;
        default:
            generateSPSFreezerTree($freezerName);
            break;
    }
}

/*return a hash of the ids and uuids of all items contained by $id
*/
function getItemsContainedByItem($id) {
    // get item type
    $type = type($id);
    
    //$filter = generateFilterFromSession("VwTubeAndLocations.id_study", "VwTubeAndLocations.sample_type", "VwTubeAndLocations.shipment_type", "VwTubeAndLocations.id_visit");
    //$filter = array("sql" => "true", "text" => "no filter<br/>");
    $filter = array("sql" => "true", "text" => "");
    $whereStatement = "true";
    
    switch($type) {
        case 'shelf':
            $statement = "select id, id_uuid, subdiv
                from VwRackAndLocations where $whereStatement and id_container = $id
                order by cast(subdiv as unsigned)";
            break;
        case 'rack':
            $statement = "select id, id_uuid, subdiv
                from VwBoxAndLocations where $whereStatement and id_container = $id
                order by cast(subdiv as unsigned)";
            break;
        case 'box':
            $statement = "select id, id_uuid, concat(subdivx, ' ', subdivy) as subdiv
                from VwTubeAndLocations where $whereStatement and id_container = $id
                order by cast(subdiv as unsigned)";
            break;
    }
    
    $result = mysql_query($statement);
    if (!$result) {
        echo "unable to perfom query: " . mysql_error();
        return array();
    }
    $returnVal = array();
    if (mysql_affected_rows() > 0) {
        while($row = mysql_fetch_array($result)) {
            $returnVal[] = array("id"=>$row['id'], "id_uuid"=>$row['id_uuid'], "subdiv"=>$row['subdiv']);
        }
    }
    return $returnVal;
}

/*return a hash of the ids and uuids of all shelves contained by $freezerName
*/
function getShelvesContainedByFreezer($freezerName) {
    //$filter = generateFilterFromSession("VwTubeAndLocations.id_study", "VwTubeAndLocations.sample_type", "VwTubeAndLocations.shipment_type", "VwTubeAndLocations.id_visit");
    //$filter = array("sql" => "true", "text" => "no filter<br/>");
    $whereStatement = "true";
    
    $statement = "select id, id_uuid, subdiv
        from VwShelfAndLocations where $whereStatement and VwShelfAndLocations.freezer = '$freezerName'
        order by cast(subdiv as unsigned)";
    
    $result = mysql_query($statement);
    if (!$result) {
        echo "getShelvesContainedByFreezer: unable to perfom query: " . mysql_error();
        echo "<br/>$statement";
        return array();
    }
    $returnVal = array();
    if (mysql_affected_rows() > 0) {
        while($row = mysql_fetch_array($result)) {
            $returnVal[] =  array("id"=>$row['id'], "id_uuid"=>$row['id_uuid'], "subdiv"=>$row['subdiv']);
        }
    }
    return $returnVal;
}

/**
 *Display a tree view of inventory that uses the SPS method to catagorize location
 * $freezerName: the freezer name or "Unknown Samples" for all samples that are orphaned
*/
function generateSPSFreezerTree($freezerName){
    if ($freezerName == 'Unknown Samples') {
        $idLinkFlag = false;
        $uuidLinkFlag = true;
        $countString = 0;
        
        $title = "<h3>Samples with incomplete locations, empty samples, samples for testing, other.</h3>";
        $filter = generateFilterFromSession();
        
        $whereStatement = "isnull(items_shelf_id)";
        $whereStatement .= " and " . $filter['sql'];
        //$whereStatement .= " and items_destination != 'niddk'";
    
        $statement = "select count(distinct items_id) as tube_count, freezer,
            items_shelf_id, locations_subdiv1, items_shelf_id_uuid,
            items_rack_id, locations_subdiv2, items_rack_id_uuid,
            items_box_id, locations_subdiv3, items_box_id_uuid
            from VwItemsAndLocations
            where $whereStatement
            group by items_shelf_id, items_rack_id, items_box_id
            order by items_shelf_id, locations_subdiv2, items_rack_id, cast(locations_subdiv3 as unsigned), items_box_id";
    }
    else {
        $idLinkFlag = false;
        $uuidLinkFlag = true;
        //$filter = array("sql" => "true", "text" => "no filter<br/>");
        $filter = array("sql" => "true", "text" => "");
        
        // get freezer uuid
        $freezerHeader = $freezerName;
        if($freezerUUID = getFreezerUUID($freezerName)) {
            $freezerHeader = '<a href="javascript:inventoryDetail(\''. $freezerUUID .'\');">' . $freezerName. '</a>';   
        } 
        
        $title = "<h3>Contents of Freezer \"$freezerHeader\"</h3>";
        
        $menu  = new HTML_TreeMenu();
        
        // generate the tree (BWAHAHAHAHA sooooo ugly)
        $totalTubes = 0;
        $shelves = getShelvesContainedByFreezer($freezerName);
        foreach($shelves as $shelf) {
            unset($shelf_menu);
            $shelf_menu = generateHTMLTreeNode('Shelf ' . $shelf['subdiv'] . ': ' . $shelf['id_uuid'], '', $shelf['id'], $shelf['id_uuid'], $idLinkFlag, $uuidLinkFlag);
            $racks = getItemsContainedByItem($shelf['id']);
            foreach($racks as $rack) {
                unset($rack_menu);
                $rack_menu = generateHTMLTreeNode('Rack ' . $rack['subdiv'] . ': ' . $rack['id_uuid'], '', $rack['id'], $rack['id_uuid'], $idLinkFlag, $uuidLinkFlag);
                $boxes = getItemsContainedByItem($rack['id']);
                foreach($boxes as $box) {
                    unset($box_menu);
                    $tubes = getItemsContainedByItem($box['id']);
                    //$totalTubes += count($tubes);
                    //$box_menu = generateHTMLTreeNode('Box ' . $box[subdiv] . ': ' . $box[id_uuid] . " Tubes: " . count($tubes), '', $box['id'], $box['id_uuid'], $idLinkFlag, $uuidLinkFlag);
                    $box_menu = generateHTMLTreeNode('Box ' . $box['subdiv'] . ': ' . $box['id_uuid'], '', $box['id'], $box['id_uuid'], $idLinkFlag, $uuidLinkFlag);
                    $rack_menu->addItem($box_menu);
                }
                $shelf_menu->addItem($rack_menu);
            }
            $menu->addItem($shelf_menu);
        }
        $tree = new HTML_TreeMenu_DHTML($menu, array('images'=>'images/html_treemenu'));
        
        // print the tree
        echo $title;
        //echo "<h2>Total Tubes: $totalTubes</h2>";
        echo "<i><smaller>" . $filter['text'] . "</smaller></i>";
        echo $tree->toHTML();
    }
}



/**
 *Display a tree view of inventory that uses the SPS method to catagorize location
 * $freezerName: the freezer name or "Unknown Samples" for all samples that are orphaned
*/
function generateUnknownSPSFreezerTree($freezerName) {
    $freezerHeader = $freezerName;
    
    $idLinkFlag = false;
    $uuidLinkFlag = true;
    $countString = 0;
        
    $title = "<h3>Samples with incomplete locations, empty samples, samples for testing, other.</h3>";
    $filter = generateFilterFromSession();
        
    $whereStatement = "isnull(items_shelf_id)";
    $whereStatement .= " and " . $filter['sql'];
    //$whereStatement .= " and items_destination != 'niddk'";
    
    $statement = "select count(distinct items_id) as tube_count, freezer,
        items_shelf_id, locations_subdiv1, items_shelf_id_uuid,
        items_rack_id, locations_subdiv2, items_rack_id_uuid,
        items_box_id, locations_subdiv3, items_box_id_uuid
        from VwItemsAndLocations
        where $whereStatement
        group by items_shelf_id, items_rack_id, items_box_id
        order by items_shelf_id, locations_subdiv2, items_rack_id, cast(locations_subdiv3 as unsigned), items_box_id";
    

    $result = mysql_query($statement);
    if (!$result){
        echo "Unable to perform query: " . mysql_error();
        return;
    }

    if (mysql_affected_rows() > 0) {
        $row = mysql_fetch_array($result);
        $currentShelf = $row['items_shelf_id'];
        $currentRack = $row['items_rack_id'];
        
        $menu  = new HTML_TreeMenu();
        $totalTubes = 0 + $row['tube_count'];
        
        if(strlen($row['items_shelf_id']) == 0)
            $shelf_menu = generateHTMLTreeNode('No Shelf');
        else
            $shelf_menu = generateHTMLTreeNode('Shelf ' . $row['locations_subdiv1'] . ': ' . $row['items_shelf_id_uuid'], '', $row['items_shelf_id'], $row['items_shelf_id_uuid'], $idLinkFlag, $uuidLinkFlag);
            
        if(strlen($row['items_rack_id']) == 0)
            $rack_menu = generateHTMLTreeNode('No Rack');
        else
            $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
        if(strlen($row['items_box_id']) == 0)
            $rack_menu->addItem(generateHTMLTreeNode('No Box'));
        else
            $rack_menu->addItem(generateHTMLTreeNode('Box ' . $row['locations_subdiv3'] . ': ' . $row['items_box_id_uuid'],  '', $row['items_box_id'], $row['items_box_id_uuid'], $idLinkFlag, $uuidLinkFlag));
            
        while($row = mysql_fetch_array($result)) {
            $totalTubes += $row['tube_count'];
            // if the shelf changes, add to the freezer and make new shelf and rack
           if($currentShelf != $row['items_shelf_id']) {
                $currentShelf = $row['items_shelf_id'];
                $currentRack = $row['items_rack_id'];
                $shelf_menu->addItem($rack_menu);
                $menu->addItem($shelf_menu);
                unset($shelf_menu);
                unset($rack_menu);
                if(strlen($row['items_shelf_id']) == 0)
                    $shelf_menu = generateHTMLTreeNode('No Shelf');
                else
                    $shelf_menu = generateHTMLTreeNode('Shelf ' . $row['locations_subdiv1'] . ': ' . $row['items_shelf_id_uuid'], '', $row['items_shelf_id'], $row['items_shelf_id_uuid'], $idLinkFlag, $uuidLinkFlag);
                if(strlen($row['items_rack_id']) == 0)
                    $rack_menu = generateHTMLTreeNode('No Rack');
                else
                    $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
            }
            // else if the rack changes, add to the shelf and make new rack
            else if ($currentRack != $row['items_rack_id']) {
                $currentRack = $row['items_rack_id'];
                $shelf_menu->addItem($rack_menu);
                unset($rack_menu);
                if(strlen($row['items_rack_id']) == 0)
                    $rack_menu = generateHTMLTreeNode('No Rack');
                else
                    $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
            }
          
            // add the box to the rack
            if(strlen($row['items_box_id']) == 0)
                $rack_menu->addItem(generateHTMLTreeNode('No Box'));
            else
                $rack_menu->addItem(generateHTMLTreeNode('Box ' . $row['locations_subdiv3'] . ': ' . $row['items_box_id_uuid'],  '', $row['items_box_id'], $row['items_box_id_uuid'], $idLinkFlag, $uuidLinkFlag));
        }
        
        //finish up
        $shelf_menu->addItem($rack_menu);
        $menu->addItem($shelf_menu);
        $tree = new HTML_TreeMenu_DHTML($menu, array('images'=>'images/html_treemenu'));
    
        echo $title;
        echo "<i><smaller>" . $filter['text'] . "</smaller></i>";
        //echo "Total Tubes: $totalTubes<br/><br/>";
        echo $tree->toHTML();
    }
    // no results
    else {
        echo "<h1>Contents of Freezer \"$freezerHeader\"</h1>";
        echo "<i><smaller>" . $filter['text'] . "</smaller></i>";
        echo "Total Tubes: 0<br/><br/>";
    }
}
/**
 *Display a tree view of inventory that uses the presplit method to catagorize location
 * $freezerName: "Presplit"
*/
function generatePresplitFreezerTree($freezerName) {
    if ($freezerName == 'Presplit') {
        $idLinkFlag = false;
        $uuidLinkFlag = false;
        $countString = 0;
        $title = "<h3>Presplit Samples</h3>";
        $filter = generateFilterFromSession("id_study", "sample_type", "shipment_type", "id_visit");
        
        $whereStatement =  $filter['sql'];
    
        $statement = "select count(distinct items.id) as tube_count,
            VwLocations_active.freezer,
            VwLocations_active.subdiv1 as items_shelf_id, VwLocations_active.subdiv1 as locations_subdiv1, '' as items_shelf_id_uuid,
            VwLocations_active.subdiv2 as items_rack_id, VwLocations_active.subdiv2 as locations_subdiv2, '' as items_rack_id_uuid,
            VwLocations_active.subdiv3 as items_box_id, VwLocations_active.subdiv3 as locations_subdiv3, '' as items_box_id_uuid
            FROM items
            inner JOIN VwLocations_active ON items.id = VwLocations_active.id_item
            where $whereStatement
            and  VwLocations_active.id_container = 0
            and items.type = 'tube'
            group by freezer, subdiv1, subdiv2, subdiv3
            order by freezer, subdiv1, subdiv2, subdiv3";
    }
    else if ($freezerName == 'Unknown Samples') {
        $idLinkFlag = true;
        $uuidLinkFlag = false;
        $countString = 0;
        $title = "<h3>Samples with incomplete locations, empty samples, samples for testing, other.</h3>";
        $filter = generateFilterFromSession("VwTubeAndLocations.id_study", "VwTubeAndLocations.sample_type", "VwTubeAndLocations.shipment_type", "VwTubeAndLocations.id_visit");
        
        $whereStatement = " and " . $filter['sql'];
    
        $statement = "select count(distinct VwTubeAndLocations.id) as tube_count, VwTubeAndLocations.freezer,
            null as items_shelf_id, null as locations_subdiv1, null as items_shelf_id_uuid,
            VwRackAndLocations.id as items_rack_id, VwRackAndLocations.subdiv as locations_subdiv2, VwRackAndLocations.id_uuid as items_rack_id_uuid,
            VwBoxAndLocations.id as items_box_id, VwBoxAndLocations.subdiv as locations_subdiv3, VwBoxAndLocations.id_uuid as items_box_id_uuid
            FROM  VwTubeAndLocations 
                left join VwBoxAndLocations on VwTubeAndLocations.id_container = VwBoxAndLocations.id
                left join VwRackAndLocations on VwBoxAndLocations.id_container = VwRackAndLocations.id
            where VwTubeAndLocations.id_container != 0 
                and isnull(VwRackAndLocations.id_container) $whereStatement
            group by freezer, items_rack_id, items_box_id
            order by freezer, items_shelf_id, locations_subdiv2, items_rack_id, cast(locations_subdiv3 as unsigned), items_box_id";
            //order by items_shelf_id, cast(locations_subdiv2 as unsigned), items_rack_id, cast(locations_subdiv3 as unsigned), items_box_id";
    }
    else {
        return;
    }
    //echo "<br/><br/>$statement<br/>";
    
    $result = mysql_query($statement);
    if (!$result){
        echo "Unable to perform query: " . mysql_error();
        return;
    }

    if (mysql_affected_rows() > 0) {
        $row = mysql_fetch_array($result);
        $currentFreezer = $row['freezer'];
        $currentShelf = $row['items_shelf_id'];
        $currentRack = $row['items_rack_id'];
        
        $menu  = new HTML_TreeMenu();
        $totalTubes = 0 + $row['tube_count'];
        
        if($row['freezer'] == null)
            $freezer_menu = generateHTMLTreeNode('Null Freezer');
        else if(strlen($row['freezer']) == 0)
            $freezer_menu = generateHTMLTreeNode('Blank Freezer');
        else
            $freezer_menu = generateHTMLTreeNode('Freezer ' . $row['freezer']);        
        if(strlen($row['items_shelf_id']) == 0)
            $shelf_menu = generateHTMLTreeNode('No Shelf');
        else
            $shelf_menu = generateHTMLTreeNode('Shelf ' . $row['locations_subdiv1'] . ': ' . $row['items_shelf_id_uuid'], '', $row['items_shelf_id'], $row['items_shelf_id_uuid'], $idLinkFlag, $uuidLinkFlag);
        if(strlen($row['items_rack_id']) == 0)
            $rack_menu = generateHTMLTreeNode('No Rack');
        else
            $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
        if(strlen($row['items_box_id']) == 0)
            $rack_menu->addItem(generateHTMLTreeNode('No Box', ' - Total tubes: ' . $row['tube_count']));
        else
            $rack_menu->addItem(generateHTMLTreeNode('Box ' . $row['locations_subdiv3'] . ': ' . $row['items_box_id_uuid'],  ' - Total tubes: ' . $row['tube_count'], $row['items_box_id'], $row['items_box_id_uuid'], $idLinkFlag, $uuidLinkFlag));
                
            
        while($row = mysql_fetch_array($result)) {
            $totalTubes += $row['tube_count'];
            // if the freezer changes, make new freezer, shelf and rack
           if($currentFreezer != $row['freezer']) {
                $currentFreezer = $row['freezer'];
                $currentShelf = $row['items_shelf_id'];
                $currentRack = $row['items_rack_id'];
                $shelf_menu->addItem($rack_menu);
                $freezer_menu->addItem($shelf_menu);
                $menu->addItem($freezer_menu);
                unset($freezer_menu);
                unset($shelf_menu);
                unset($rack_menu);
                if($row['freezer'] == null)
                    $freezer_menu = generateHTMLTreeNode('Null Freezer');
                else if(strlen($row['freezer']) == 0)
                    $freezer_menu = generateHTMLTreeNode('Blank Freezer');
                else
                    $freezer_menu = generateHTMLTreeNode('Freezer ' . $row['freezer']);        
                if(strlen($row['items_shelf_id']) == 0)
                    $shelf_menu = generateHTMLTreeNode('No Shelf');
                else
                    $shelf_menu = generateHTMLTreeNode('Shelf ' . $row['locations_subdiv1'] . ': ' . $row['items_shelf_id_uuid'], '', $row['items_shelf_id'], $row['items_shelf_id_uuid'], $idLinkFlag, $uuidLinkFlag);
                if(strlen($row['items_rack_id']) == 0)
                    $rack_menu = generateHTMLTreeNode('No Rack');
                else
                    $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
            }
            
            // if the shelf changes, add to the freezer and make new shelf and rack
           if($currentShelf != $row['items_shelf_id']) {
                $currentShelf = $row['items_shelf_id'];
                $currentRack = $row['items_rack_id'];
                $shelf_menu->addItem($rack_menu);
                $freezer_menu->addItem($shelf_menu);
                unset($shelf_menu);
                unset($rack_menu);
                if(strlen($row['items_shelf_id']) == 0)
                    $shelf_menu = generateHTMLTreeNode('No Shelf');
                else
                    $shelf_menu = generateHTMLTreeNode('Shelf ' . $row['locations_subdiv1'] . ': ' . $row['items_shelf_id_uuid'], '', $row['items_shelf_id'], $row['items_shelf_id_uuid'], $idLinkFlag, $uuidLinkFlag);
                if(strlen($row['items_rack_id']) == 0)
                    $rack_menu = generateHTMLTreeNode('No Rack');
                else
                    $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
            }
            // else if the rack changes, add to the shelf and make new rack
            else if ($currentRack != $row['items_rack_id']) {
                $currentRack = $row['items_rack_id'];
                $shelf_menu->addItem($rack_menu);
                unset($rack_menu);
                if(strlen($row['items_rack_id']) == 0)
                    $rack_menu = generateHTMLTreeNode('No Rack');
                else
                    $rack_menu = generateHTMLTreeNode('Rack ' . $row['locations_subdiv2'] . ': ' . $row['items_rack_id_uuid'],  '', $row['items_rack_id'], $row['items_rack_id_uuid'], $idLinkFlag, $uuidLinkFlag); 
            }
          
            // add the box to the rack
            if(strlen($row['items_box_id']) == 0)
                $rack_menu->addItem(generateHTMLTreeNode('No Box', ' - Total tubes: ' . $row['tube_count']));
            else
                $rack_menu->addItem(generateHTMLTreeNode('Box ' . $row['locations_subdiv3'] . ': ' . $row['items_box_id_uuid'],  ' - Total tubes: ' . $row['tube_count'] , $row['items_box_id'], $row['items_box_id_uuid'], $idLinkFlag, $uuidLinkFlag));
        }
        
        //finish up
        $shelf_menu->addItem($rack_menu);
        $freezer_menu->addItem($shelf_menu);
        $menu->addItem($freezer_menu);
        $tree = new HTML_TreeMenu_DHTML($menu, array('images'=>'images/html_treemenu'));
    
        echo $title;
        echo "<i><smaller>" . $filter['text'] . "</smaller></i>";
        echo "Total Tubes: $totalTubes<br/><br/>";
        echo $tree->toHTML();
    }
    // no results
    else {
        echo "<h1>Contents of Freezer \"$freezerName\"</h1>";
        echo "<i><smaller>" . $filter['text'] . "</smaller></i>";
        echo "Total Tubes: 0<br/><br/>";
    }
}

/** generate an HTML_Tree node
 * 
*/
function generateHTMLTreeNode($nameString, $countString = '', $in_id = '', $uuid = '', $idLinkFlag = false, $uuidLinkFlag = false) {
    if (strlen($countString) > 0) 
        $text = $nameString . $countString;
    else
        $text = $nameString;
    
    //$text .= "  id: $in_id  uuid: $uuid";
    
    if ($idLinkFlag)
        return new HTML_TreeNode(array('text'=>$text, 'link'=>'javascript:inventoryDetailId(\\\''. $in_id .'\\\');'));
    else if ($uuidLinkFlag)
        return new HTML_TreeNode(array('text'=>$text, 'link'=>'javascript:inventoryDetail(\\\''. $uuid .'\\\');'));
    else    
        return new HTML_TreeNode(array('text'=>$text)); 
}


/** generate a filter for the view 'items_locations' based on the session variables set by filter options on the left of the screen
* checks for the following session variables:
* id_study
* sample_type
* shipment_type
* id_visit
*
* Returns an array with the filter in sql and human readable text
*/
function generateFilterFromSession($id_study_field = "items_id_study", $sample_type_field = "items_sample_type", $shipment_type_field = "items_shipment_type", $id_visit_field = "items_id_visit"){
    $id_study = mysql_real_escape_string(trim($_SESSION['id_study']));
    $sample_type = mysql_real_escape_string(trim($_SESSION['sample_type']));
    $shipment_type = mysql_real_escape_string(trim($_SESSION['shipment_type']));
    $id_visit = mysql_real_escape_string(trim($_SESSION['id_visit']));
    
    $filter = "true";
    $text = "";
    if(isset($id_study) && strlen($id_study) > 0 && ($id_study <> "%") && $id_study_field) {
        $filter .= " and $id_study_field = '$id_study'";
        $text .= "id_study = '$id_study'<br/>";
    }
    if(isset($sample_type) && strlen($sample_type) > 0 && ($sample_type <> "%") && $sample_type_field) {
        $filter .= " and $sample_type_field = '$sample_type'";
        $text .= "sample_type = '$sample_type'<br/>";
    }
    if(isset($shipment_type) && strlen($shipment_type) > 0 && ($shipment_type <> "%") && $shipment_type_field) {
        $filter .= " and $shipment_type_field = '$shipment_type'";
        $text .= "shipment_type = '$shipment_type'<br/>";
    }
    if(isset($id_visit) && strlen($id_visit) > 0 && ($id_visit <> "%") && $id_visit_field){
        $filter .= " and $id_visit_field = '$id_visit'";
        $text .= "id_visit = '$id_visit'<br/>";
    }
    
    if(strlen($text) == 0)
        $text = "no filter<br/>";
    return array("sql" => $filter, "text" => $text);
}

/* Get the UUID of a given freezer*/
function getFreezerUUID($freezerName){
    $freezerName = mysql_real_escape_string(trim($freezerName));
    $statement = "select id_uuid from items where type='freezer' and comment1 = '$freezerName'";
    $result = mysql_query($statement);
    
    if (mysql_affected_rows() > 0) {
        $row = mysql_fetch_array($result);
        $uuid = $row['id_uuid'];
        return $uuid;
    }
    else {
        return "";
    }
}
?>
