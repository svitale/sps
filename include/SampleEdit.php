<?php
    include("include/querydisplay.php");

    function onPageLoad() {
        // set default action, update if new action posted
        if((!isset($_SESSION['sampleedit_action']))) {
            $_SESSION['sampleedit_action'] = 'display';
        }
        if(isset($_POST['sampleedit_action'])) {
            $_SESSION['sampleedit_action'] = $_POST['sampleedit_action'];
        }
        if(isset($_POST['sampleedit_variable'])) {
            $_SESSION['sampleedit_variable'] = $_POST['sampleedit_variable'];
        }
             
        echo "<div>";
        displayHeader();
        echo "</div>";
    }
    
    function displayHeader() {  
        $editActions = array(
            'nextsubject'=>'Get Next Subject ID',
            'display'=>'Display Item Information',
            'history'=>'Display Location and Change History',
            'setconsumed'=>'Set Consumed Status of Samples',
            'setthawed'=>'Increment Thaw Count of Samples',
            'decrementvolume'=>'Decrement Volume of Samples',
            'setvolume'=>'Set Volume of Samples',
			'investigatedates'=>'Investigate Date Problems');
        //'dhistory'=>'Display Deep Location History',
        
        //print header
        echo "<h2>Sample Edit</h2>";
        echo "<br/><b>Current action:<i>" . $editActions[$_SESSION['sampleedit_action']] . "</i></b><br/>";
        
        // print instructions for selected action
        switch ($_SESSION['sampleedit_action']) {
            case setconsumed:
                echo "<i>Scan a tube to mark its status as consumed.<br/>";
                echo "Scan a box to mark all the tubes in the box as consumed.</i><br/><br/>";
                break;
            case setthawed:
                echo "<i>Scan a tube to increment its thaw count.<br/>";
                echo "Scan a box to increment  the thaw count of all tubes in the box.</i><br/><br/>";
                break;
            case setvolume:
                echo "<i>Scan a tube to set its volume.<br/>";
                echo "Scan a box to set the volume of all tubes in the box.<br/>";
                echo "Requires a non-zero volume variable in units ml</i><br/><br/>";
            case decrementvolume:
                echo "<i>Scan a tube to decrement its volume.<br/>";
                echo "Scan a box to decrement the volume of all tubes in the box.<br/>";
                echo "Requires a non-zero volume variable in units ml</i><br/><br/>";
	    case investigatedates:
				echo "<i>Scan a tube to see all other tubes with the same subject and visit date.<br/>";
                break;
        }
        
        // print change action form
        echo "<form method='POST' name='sampleedit_form'>";
        echo "<select name='sampleedit_action'>";
        foreach($editActions as $action=>$desc) {
            if($_SESSION['sampleedit_action'] == $action)
                echo "<option value = '$action' selected>$desc</option>";
            else
                echo "<option value = '$action'>$desc</option>";
        }
        echo "</select>";
        echo "<input type='submit' value = 'Change Action'>";
        echo "</form>";
        
        // print change action form
	$editVariables = sampleEditVariables($_SESSION['sampleedit_action']);
	if(count($editVariables) > 0) {
            echo "<form method='POST' name='sampleedit_form'>";
            echo "<select name='sampleedit_variable'>";
            foreach($editVariables as $variable) {
                if($_SESSION['sampleedit_variable'] == $variable)
                    echo "<option value = '$variable' selected>$variable</option>";
                else
                    echo "<option value = '$variable'>$variable</option>";
            }
            echo "</select>";
            echo "<input type='submit' value = 'Change Variable'>";
            echo "</form>";
	}

        displayAction();
    }
    
    function displayAction() {
        $action = $_SESSION['sampleedit_action'];
        if (!isset($action) or ($action == "---")) {
            return;
        }
        
        switch ($action) {
            case nextsubject:
                $study = trim($_SESSION['id_study']);
                if (!isset($study) or (strlen($study) == 0)) {
                    echo "No study selected.";
                    return;
                }
                
                $statement = "select max(id_subject) as id_subject from items where id_study = '$study'";
                $result = mysql_query($statement);
                if (!$result) {
                    echo "Could not perform query: " . mysql_error();
                    return;
                }
                if(mysql_affected_rows() != 1) {
                    echo "Could not perform query: no results found.";
                    return;
                }

                $row = mysql_fetch_array($result);
                $max_items = $row['id_subject'];
                
                $statement = "select max(id_subject) as id_subject from batch_quality where id_study = '$study'";
                $result = mysql_query($statement);
                if (!$result) {
                    echo "Could not perform query: " . mysql_error();
                    return;
                }
                if(mysql_affected_rows() != 1) {
                    echo "Could not perform query: no results found.";
                    return;
                }
                $row = mysql_fetch_array($result);
                $max_batch_quality = $row['id_subject'];
                
                if(!is_numeric($max_items) || !is_numeric($max_batch_quality)) {
                    echo "<br/>Study '$study' does not use numeric subject id numbers.";
                    return;
                }
                $next_subject = max($max_items, $max_batch_quality) + 1;
                echo "<br/>The next subject id for study '$study'  is: " . $next_subject;
                break;
            default:
                break;
        }
    }
    
    /**
    *Function that returns an array of valid variables for an action.
    *
    * @param string $action - the action for which to look up variables
    * 
    *If the action is decrement volume
    *  return an array of valid volumes to decrement by
    *if no variables are required return an empty array
    */
   function sampleEditVariables($action) {
	$variable_array = array();
	if ($action == 'decrementvolume') {
		$volume = 0;
		array_push($variable_array,$volume);
		while ($volume <= 2) {
			$volume = $volume + .25;
			array_push($variable_array,$volume);
		}
	}
	return $variable_array;
   }

    /**
    *Function that is called when a uuid is scanned.
    *
    * @param string $table  - the table where the item is found. Either 'items' or 'batch_quality'
    * @param string $id - the id of the scanned object
    * 
    *If a box is scanned:
    *  Do the selected action on all tubes in the box (user prompt)
    *If a tube is scanned:
    *  Do the action on the given tube
    */
   function operateScannedObject($table, $id) {
        // clear main div
        echo '<script type="text/javascript">';
        echo "$('staticcontainer').innerHTML = '<div id=\"actioncontainer\"></div>'";
        echo '</script>';
        displayHeader();
        echo '<br/>';
        
        $action = $_SESSION['sampleedit_action'];
        if (!isset($action) or ($action == "---")) {
            echo "<br/>no action selected";
            return;
        }
        if($table != 'items') {
            echo "<br/>Item not in items table.";
            return;
        }
        
        $itemInfo = getItemInformation($id);
        
        //echo "<br/><i>action: $action table: '$table' id: $id</i><br/><br/>";
        
        // perform the action
        switch ($action){
            case display:
                print "Item id: $id, Location Id: " . $itemInfo['id_location'] . ", Container Id: " . $itemInfo['id_container'] . "<br/>";
                print "Table: $table,  Type: " .  $itemInfo['type'] . "<br/>";
                
                if($itemInfo['type'] == 'box') {
                    //hax!!!!
                    $_SESSION['boxid'] = $id;
                    topView();
                }
                elseif($itemInfo['type'] == 'tube') {
                    if(isset($itemInfo['id_container'])) {
                        //hax!
                        $_SESSION['boxid'] = $itemInfo['id_container'];
                        topView($id);
                    }
                }
                elseif($itemInfo['type'] == 'rack') {
                    //hax!
                    $_SESSION['rackid'] = $id;
                    rackView();
                }
                elseif($itemInfo['type'] == 'shelf') {
                    //hax!
                    $_SESSION['shelfid'] = $id;
                    shelfView();
                }
                elseif($itemInfo['type'] == 'unassigned'){
                    print "This item has not been assigned a type, update it in 'Store'.<br/>";
                    print "<br/>UUID: " . $itemInfo['id_uuid'];
                    print "<br/>Date Created: " . $itemInfo['timestamp'];
                }
                else {
                    print "<br/>Unknown type";
                }
                break;
            case history:
                $query = "(select id_item, freezer, subdiv1 as shelf, subdiv2 as rack, subdiv3 as box, subdiv4 as row, subdiv5 as col,
                timestamp as date_placed,
                if(date_moved, date_moved, 'current location') as date_moved, '' as name_created
                from locations_moved where id_item = $id)
                union 
                (select id_item, freezer, subdiv1 as shelf, subdiv2 as rack, subdiv3 as box, subdiv4 as row, subdiv5 as col,
                timestamp as date_placed,
                if(date_moved, date_moved, 'current location') as date_moved, name_created
                from locations where id_item = $id)
                order by date_placed desc";
				
				$changeQuery = "select log.timestamp, log.event_type, log.field, log.old_value, log.new_value
				from log_items as log
				where id_item = $id
				order by timestamp desc";
                
                echo "Location history for " . $itemInfo['type'] . " " . $itemInfo['id_uuid'];
                //echo "<br/><i>May not contain location information from the last 24 hours</i>";
                displayQuery($query, "querydiv", array("row"), false, false, true);
				
				echo "<br/><br/>Change log for " . $itemInfo['type'] . " " . $itemInfo['id_uuid'];
                displayQuery($changeQuery, "querydiv2", array("row"), false, false);
                break;			
            case dhistory:
                break;
            case setconsumed:
                if($itemInfo['type'] == 'box') {
                    /*echo '<script type="text/javascript">
                    var answer = confirm("Are you sure you want to set the contents of this box as consumed?")
                    </script>';*/
                    $total = SetBoxConsumed($id);
                    if ($total !== false) {
                        echo "<font class=\"success\">The contents box " . $itemInfo['id_uuid'] . " have been marked as consumed.<br/>";
                        echo "$total tubes were changed.</font>";
                    }
                    else {
                        echo "<font class=\"alert\">Unable to update the samples in this box.</font>";
                    }
                }
                elseif($itemInfo['type'] == 'tube') {
                    if (SetTubeConsumed($id))
                        echo "<font class=\"success\">Tube " . $itemInfo['id_uuid'] . " has been marked as consumed.</font>";
                }
                else {
                    echo "<font class=\"alert\">This action can only be perfomred on a tube or a box that is scanned into a parent container.</font>";
                }
                break;
            case setthawed:
                if($itemInfo['type'] == 'box') {
                    /*echo '<script type="text/javascript">
                    var answer = confirm("Are you sure you want to set the contents of this box as consumed?")
                    </script>';*/
                    $total = SetBoxThawed($id);
                    if ($total !== false) {
                        echo "<font class=\"success\">Thaw count of the tubes in box " . $itemInfo['id_uuid'] . " have been incremented by one.<br/>";
                        echo "$total tubes were changed.</font>";
                    }
                    else {
                        echo "<font class=\"alert\">Unable to update the samples in this box.</font>";
                    }
                }
                elseif($itemInfo['type'] == 'tube') {
                    if (SetTubeThawed($id))
                        echo "<font class=\"success\">Thaw count of tube " . $itemInfo['id_uuid'] . " has been marked incremented by one.</font>";
                }
                else {
                    echo "<font class=\"alert\">This action can only be perfomred on a tube or a box that is scanned into a parent container.</font>";
                }
                break;
            case decrementvolume:
				if(isset($_SESSION['sampleedit_variable']) && is_numeric($_SESSION['sampleedit_variable']) && $_SESSION['sampleedit_variable'] > 0) {
					$variable = $_SESSION['sampleedit_variable'];
				} else {
							echo '<script type="text/javascript">
							alert("a non-zero decrement variable must be selected");
							</script>';
					exit;
				}
                if($itemInfo['type'] == 'box') {
                    /*echo '<script type="text/javascript">
                    var answer = confirm("Are you sure you want to set the contents of this box as consumed?")
                    </script>';*/
                    $total = decrementBoxVolume($id,$variable);
                    if ($total !== false) {
                        echo "<font class=\"success\">The contents box " . $itemInfo['id_uuid'] . " have been decremented by $variable ml.<br/>";
                        echo "$total tubes were changed.</font>";
                    }
                    else {
                        echo "<font class=\"alert\">Unable to update the samples in this box.</font>";
                    }
                }
                elseif($itemInfo['type'] == 'tube') {
                    if (decrementTubeVolume($id,$variable))
                        echo "<font class=\"success\">Tube " . $itemInfo['id_uuid'] . " has been been decremented by $variable ml.</font>";
                }
                else {
                    echo "<font class=\"alert\">This action can only be perfomred on a tube or a box that is scanned into a parent container.</font>";
                }
                break;
			case investigatedates:
				if($itemInfo['type'] == 'tube') {
					$id_subject = $itemInfo['id_subject'];
					$id_visit = $itemInfo['id_visit'];
					$query = "select id_subject, id_visit, date_visit, sample_type, id_uuid, id
					from items
					where id_subject = '$id_subject' and id_visit = '$id_visit'";
					
					echo "Samples for id_subject '" . $id_subject . "' and id_visit '" . $id_visit . "'";
					//echo "<br/><i>May not contain location information from the last 24 hours</i>";
					displayQuery($query, "querydiv", array("row"), false, false);
					break;
				}
				else {
					echo "This action only works with tubes.";
				}
            default:
                break;
        }
        return;
   }
   
   
   /*functions relating to manipulating the status of samples*/

/**
 * Set the consumed status of a tube in items
 * @param int $id
 * @param bool $value
 * @return bool
 */
function SetTubeConsumed($tubeid, $value = true) {
    if($value)
        $valStr = "true";
    else
        $valStr = "false";
        
    $statement = "update items set consumed = $valStr, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where id = $tubeid and type = 'tube'";
    echo $statement;
    $result = mysql_query($statement);
    if(!$result) {
        return false;
    }
    return true;
}

/**
 * Set the consumed status of all tubes in a box
 * @param int $id
 * @param bool $value
 * @return int total tubes updated
 */
function SetBoxConsumed($boxid, $value = true) {
    if($value)
        $valStr = "true";
    else
        $valStr = "false";

    mysql_query("start transaction");
    $changedTubes = 0;
    $tubeResult = mysql_query("select distinct id_item from locations where id_container = $boxid and date_moved is null");
    if (!$tubeResult) {
        echo "<br/><br/>sel" . mysql_error();
        mysql_query("rollback");
        return false;
    }
    while($row = mysql_fetch_array($tubeResult)) {
        $id_item = $row['id_item'];
        $result = mysql_query("update items set consumed = $valStr, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where type = 'tube' and id = $id_item");
        if($result == false){
            echo "<br/><br/>up" . mysql_error() . "<br/><br/>update items set consumed = $valStr where type = 'tube' and id = $id_item";
            mysql_query("rollback");
            return false;
        }
        $changedTubes += mysql_affected_rows();
    }
    mysql_query("commit");
    return $changedTubes;
}

/**
 * Increment thaw count of a tube in items
 * @param int $id
 * @param bool $value
 * @return bool
 */
function SetTubeThawed($tubeid, $value = true) {
    if($value)
        $valStr = "quant_thaws + 1";
    else
        $valStr = "quant_thaws - 1";
    $statement = "update items set quant_thaws = $valStr, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where id = $tubeid and type = 'tube'";
//    echo $statement;
    $result = mysql_query($statement);
    if(!$result) {
        return false;
    }
    return true;
}

/**
 * Increment thaw count of all tubes in a box
 * @param int $id
 * @param bool $value
 * @return int total tubes updated
 */
function SetBoxThawed($boxid, $value = true) {
    if($value)
        $valStr = "quant_thaws + 1";
    else
        $valStr = "quant_thaws - 1";

    mysql_query("start transaction");
    $changedTubes = 0;
    $tubeResult = mysql_query("select distinct id_item from locations where id_container = $boxid and date_moved is null");
    if (!$tubeResult) {
        echo "<br/><br/>sel" . mysql_error();
        mysql_query("rollback");
        return false;
    }
    while($row = mysql_fetch_array($tubeResult)) {
        $id_item = $row['id_item'];
        $result = mysql_query("update items set quant_thaws = $valStr, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where type = 'tube' and id = $id_item");
        if($result == false){
            echo "<br/><br/>up" . mysql_error() . "<br/><br/>update items set quant_thaws = $valStr where type = 'tube' and id = $id_item";
            mysql_query("rollback");
            return false;
        }
        $changedTubes += mysql_affected_rows();
    }
    mysql_query("commit");
    return $changedTubes;
}

/**
 * decrement volume of all tubes in a box
 * @param int $boxid
 * @param var $variable
 * @return int total tubes updated
 */
function decrementBoxVolume($boxid, $variable) {
    if (is_numeric($variable) && $variable > 0) {
	$decrementor = $variable;
    } else {
        echo "<br/><br/>invalid variable: $variable";
        return false;
	exit;
    }
    mysql_query("start transaction");
    $targetTubes = array();
    $problemTubes = array();
    $changedTubes = 0;
    $sql = "select subdiv4,subdiv5,id_subject,items.id,quant_cur,id_item from locations left join items on items.id = locations.id_item where id_container = $boxid and date_moved is null group by id_item";
    $volumeResult = mysql_query($sql);
    if (!$volumeResult) {
        echo "<br/><br/>sel" . mysql_error();
        mysql_query("rollback");
        return false;
	exit;
    }
    while($row = mysql_fetch_array($volumeResult)) {
	if (is_numeric($row['quant_cur']) && $row['quant_cur'] > $decrementor) {
		array_push($targetTubes,$row);
	} else {
		array_push($problemTubes,$row);
	}
    }
    if (count($problemTubes) > 0) {
	foreach ($problemTubes as $problemTube) {
	echo '<br>' . num2chr($problemTube['subdiv4']) . $problemTube['subdiv5'] . ' (' . $problemTube['id_subject'] . ') has a current volume of ' .$problemTube['quant_cur'] . ' ml</br>'; 
	}
        mysql_query("rollback");
        return false;
	exit;
    }
    if (count($targetTubes) == 0) {
        echo "no tubes in this box<br/>";
        mysql_query("rollback");
        return false;
	exit;
    }
    foreach ($targetTubes as $targetTube) {
	$id_item = $targetTube['id_item'];
        $sql = "update items set quant_cur = quant_cur - $decrementor, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where type = 'tube' and id = $id_item and quant_cur +0 > $decrementor";
        $result = mysql_query($sql);
        if($result == false){
            echo "<br/><br/>up" . mysql_error() . "<br/><br/>$sql";
            mysql_query("rollback");
            return false;
	    exit;
        }
	if (mysql_affected_rows() != 1) {
            echo "<br/><br/>unexpected result for $sql";
            mysql_query("rollback");
            return false;
	    exit;
	} else {
	   $changedTubes = $changedTubes + 1;
	}
    }
    mysql_query("commit");
    return $changedTubes;
}
function decrementTubeVolume($tubeid, $variable) {
    if (is_numeric($variable) && $variable > 0) {
	$decrementor = $variable;
    } else {
        echo "<br/><br/>invalid variable: $variable";
        return false;
	exit;
    }
    $sql = "update items set quant_cur = quant_cur - $decrementor, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where type = 'tube' and id = $tubeid and quant_cur +0 > $decrementor";
    $result = mysql_query($sql);
    if($result == false){
        echo "<br/><br/>" . mysql_error() . "<br/><br/>$sql";
        return false;
        exit;
    }
    if (mysql_affected_rows() != 1) {  
        echo "<br/><br/>sample not updated";
	return false;
    } else {
	return true;
    }
}

/**
 * called from npc.php
 */
function data_array($postType) {
	$id = 1353792;
    $returnArray = array();
	
	$changeQuery = "select log.timestamp, log.event_type, log.field, log.old_value, log.new_value
					from log_items as log
					where id_item = $id
					order by timestamp desc";
	$result = mysql_query($changeQuery);
	
	while ($row = mysql_fetch_assoc($result)) {
		array_push($returnArray, $row);
	}
	return $returnArray;	
}
?>
