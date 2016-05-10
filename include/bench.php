<?php
/*include this to use the box bench*/

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
 *Show all boxes scanned into the bench.  Clicking on a box will make it the active box.
 */
function showBench() {
   //echo "<br/>showBench()";
    //return;
   echo '<div id="benchView" class="benchView" style="width: 300px; background-color: #A0C8FF;">';    
   if (isset($_SESSION['box_array']) && isset($_SESSION['boxid'])) {
        echo '<div class=rowFloat id="bench_header" style="width: 180px;">';
		$box_array = $_SESSION['box_array'];
		foreach(array_keys($box_array) as $k) {
			$v = $box_array[$k]['id'];
			$m = $box_array[$k]['dest'];
			if ($v == $_SESSION['boxid']) {
				//$bgcolor = "00000";
				//$fgcolor = colorop(getFreezerColor($m), '505050');
                $fgcolor = "000000";
                $bgcolor = getFreezerColor($m);
				$bordercolor = "000000";
                $borderwidth = "2px";
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
    echo '</div><br/>';
echo "<script>jQuery('#benchView').draggable();</script>";
}

?>
