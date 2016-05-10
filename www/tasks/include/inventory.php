<?php

function getInventory($format, $level) {
	if ($format == 'grid') {
		echo "<div id='level_" . $level . "_container'></div>";
		echo "<script>";
		echo "new TableOrderer('level_" . $level . "_container',{url : 'npc.php?action=data&format=html&type=" . $level . "' });";
		echo "</script>";
	}
}

function viewFreezer($freezer) {
?>
<ul>
<div class="demo" id="droppable_container">
  <div id="freezer_1" class="draggable_freezer">
<ul>
  	<div id="shelf_1" class="draggable_shelf">
		<ul>
  		<div id="rack_1" class="draggable_rack">
  		</div>
		</ul>
  	</div>
  	<div id="shelf_2" class="draggable_shelf">
  	</div>
</ul>
  </div>
</ul>
  
  <div id="droppable_demo">
    Drop here!
  </div>
</div>

<script type="text/javascript">
  new Draggable('rack_1', { 
    revert: true 
  });
  new Draggable('shelf_1', { 
    revert: true 
  });
  new Draggable('shelf_2', { 
    revert: true 
  });
  new Draggable('freezer_1', { 
    revert: true 
  });

  
  Droppables.add('droppable_demo', { 
    accept: Array('draggable_freezer','draggable_shelf','draggable_rack'),
    hoverclass: 'hover',
    onDrop: function() { $('droppable_demo').highlight(); }
  });
</script>
<?php
}

function getSite() {
	echo '<form>';
	//* Get Sitewide Inventory -- presents the initial list of freezers
	if ($_SESSION['freezer']) {
		$selectedFreezer = $_SESSION['freezer'];
	}
	if ($_SESSION['id_visit']) {
		$selectedVisit = $_SESSION['id_visit'];
	}
	if ($_SESSION['id_study'] && $_SESSION['id_visit'] && $_SESSION['sample_type']) {
		$id_study = $_SESSION['id_study'];
		$id_visit = $_SESSION['id_visit'];
		$sample_type = $_SESSION['sample_type'];
		//		$freezerQuery = mysql_query("SELECT COUNT( * ) AS `Rows`,`freezer` FROM `locations` GROUP BY `freezer` ORDER BY `freezer`", $conn);
		$freezerQuery = mysql_query("SELECT COUNT( * ) AS `Rows`,`freezer` FROM `locations` LEFT JOIN (`items`) ON (`items`.`id`=`locations`.`id_item`)  WHERE `id_study` LIKE '$id_study' and id_visit LIKE '$id_visit' and sample_type like '$sample_type' GROUP BY `freezer` ORDER BY `Rows` desc");
	} else {
		$freezerQuery = mysql_query("SELECT COUNT( * ) AS `Rows`,`freezer` FROM `locations` GROUP BY `freezer` ORDER BY `freezer`");
	}
	echo '<div style="margin-bottom: 3px;">
			<select id="ccFreezer" onChange="displayFreezer($F(\'ccFreezer\'))">';
	echo '<option value="">';
	for ($i = 0;$i < mysql_num_rows($freezerQuery);$i++) {
		extract(mysql_fetch_array($freezerQuery));
		if ($freezer) {
			$thisFreezer = urlencode($freezer);
			if ($thisFreezer == $selectedFreezer) {
				$sel = 'selected';
			} else {
				$sel = '';
			}
		}
		echo '<option value="' . $thisFreezer . '"' . $sel . '>' . $freezer;
	}
	echo '</select>';
	echo '</form>
			</div>';
	mysql_free_result($freezerQuery);
}

function getFreezer() {
	if ($_POST['freezer']) {
		$_SESSION['freezer'] = $_POST['freezer'];
		$selectedFreezer = urlencode($_POST['freezer']);
		$query = "SELECT COUNT( * ) AS `Rows` , `subdiv1` FROM `locations`  LEFT JOIN (`items`) ON (`items`.`id`=`locations`.`id_item`) WHERE `freezer` LIKE '$freezer'";
		$query.= " and (locations.date_moved is null or locations.date_moved = '0000-00-00') ";
		$query.= " and items.type = 'tube' ";
		//	$query .= " and locations.id_container != '0' ";
		if (isset($_SESSION['id_study'])) {
			$id_study = $_SESSION['id_study'];
			$query.= " and `id_study` LIKE '$id_study' ";
		}
		if (isset($_SESSION['id_visit'])) {
			$id_visit = $_SESSION['id_visit'];
			$query.= " and id_visit LIKE '$id_visit'";
		}
		if (isset($_SESSION['sample_type'])) {
			$sample_type = $_SESSION['sample_type'];
			$query.= " and sample_type like '$sample_type' ";
		}
		if (isset($_SESSION['shipment_type'])) {
			$shipment_type = $_SESSION['shipment_type'];
			$query.= " and shipment_type = '$shipment_type' ";
		}
		$query.= "GROUP BY `subdiv1` ORDER BY `subdiv1`;";
		$shelfQuery = mysql_query($query, $conn);
	} else {
		echo 'Could not get freezer for getFreezer';
		exit;
	}
	// Display the chosen freezer.
	if (mysql_num_rows($shelfQuery) > 0) {
		for ($i = 0;$i < mysql_num_rows($shelfQuery);$i++) {
			extract(mysql_fetch_array($shelfQuery) , EXTR_PREFIX_ALL, 'div1');
			$num{$div1_subdiv1} = $div1_Rows;
		}
	}
	mysql_free_result($shelfQuery);
	echo '<input type="button" value="export" onclick="window.location.href=\'npc.php?action=exportboxlist\'">';
	echo "<b> shelves in <i>" . $freezer . "</i></b>";
	for ($j = 1;$j <= 6;$j++) {
		if ($num{$j}) {
			$numAlq = $num{$j};
			$bgColor = '#' . $eventColor[1] . '';
			$onClick = 'displayShelf(\'' . $selectedFreezer . '\',\'' . $j . '\')';
			echo '<div class="freezerFloat" id="shelf_' . ($j) . '" style="background-color: ' . $bgColor . '; cursor: pointer;" 
				onMouseOver="highlightCell(\'shelf_' . $j . '\')"
				onMouseOut="resetCell(\'shelf_' . $j . '\')"
				onClick="' . $onClick . '">
				<span style="position: relative; top: ' . $tTop . '; left: 1px;">' . $j . ' - ' . $numAlq . '   samples</span>
				</div>';
		}
	}
}

function getShelf() {
	if ($_POST['shelf']) {
		$_SESSION['shelf'] = $_POST['shelf'];
	}
	$freezer = $_SESSION['freezer'];
	$selectedFreezer = urldecode($freezer);
	$shelf = $_SESSION['shelf'];
	$query = "SELECT COUNT( * ) AS `Rows` , `subdiv2` FROM `locations`  LEFT JOIN (`items`) ON (`items`.`id`=`locations`.`id_item`) WHERE `freezer` LIKE '$selectedFreezer' and `subdiv1` LIKE '$shelf'";
	$query.= " and (locations.date_moved is null or locations.date_moved = '0000-00-00') ";
	$query.= " and items.type = 'tube' ";
	//		$query .= " and locations.id_container != '0' ";
	if (isset($_SESSION['id_study'])) {
		$id_study = $_SESSION['id_study'];
		$query.= " and `id_study` LIKE '$id_study' ";
	}
	if (isset($_SESSION['id_visit'])) {
		$id_visit = $_SESSION['id_visit'];
		$query.= " and id_visit LIKE '$id_visit'";
	}
	if (isset($_SESSION['sample_type'])) {
		$sample_type = $_SESSION['sample_type'];
		$query.= " and sample_type like '$sample_type' ";
	}
	if (isset($_SESSION['shipment_type'])) {
		$shipment_type = $_SESSION['shipment_type'];
		$query.= " and shipment_type = '$shipment_type' ";
	}
	$query.= "GROUP BY `subdiv2` ORDER BY `subdiv2`;";
	$shelfQuery = mysql_query($query, $conn);
	echo "<b>racks in <i>shelf $shelf" . "</i></b>\n";
	if (mysql_num_rows($shelfQuery) > 0) {
		for ($i = 0;$i < mysql_num_rows($shelfQuery);$i++) {
			extract(mysql_fetch_array($shelfQuery) , EXTR_PREFIX_ALL, 'div2');
			$num{$div2_subdiv2} = $div2_Rows;
		}
	}
	for ($j = 1;$j <= 6;$j++) {
		if ($num{$j}) {
			$numAlq = $num{$j};
			$bgColor = '#' . $eventColor[2] . '';
			$onClick = 'displayRack(\'' . $selectedFreezer . '\',\'' . $shelf . '\',\'' . $j . '\')';
			echo '<div class="freezerFloat" id="rack_' . $j . '" style="background-color: ' . $bgColor . '; cursor: pointer;" 
			onMouseOver="highlightCell(\'rack_' . $j . '\')"
			onMouseOut="resetCell(\'rack_' . $j . '\')"
			onClick="' . $onClick . '">
			<span style="position: relative; top: ' . $tTop . '; left: 1px;">' . $j . ' - ' . $numAlq . '   samples</span>
			</div>';
		}
	}
	mysql_free_result($shelfQuery);
}

function getRack() {
	$freezer = $_POST['freezer'];
	$shelf = $_POST['shelf'];
	$rack = $_POST['rack'];
	$selectedFreezer = urldecode($freezer);
	$query = "SELECT COUNT( * ) AS `Rows` , `subdiv3` FROM `locations`  LEFT JOIN (`items`) ON (`items`.`id`=`locations`.`id_item`) WHERE `freezer` LIKE '$selectedFreezer' and `subdiv1` LIKE '$shelf' and `subdiv2` LIKE '$rack'";
	$query.= " and (locations.date_moved is null or locations.date_moved = '0000-00-00') ";
	$query.= " and items.type = 'tube' ";
	if (isset($_SESSION['id_study'])) {
		$id_study = $_SESSION['id_study'];
		$query.= " and `id_study` LIKE '$id_study' ";
	}
	if (isset($_SESSION['id_visit'])) {
		$id_visit = $_SESSION['id_visit'];
		$query.= " and id_visit LIKE '$id_visit'";
	}
	if (isset($_SESSION['sample_type'])) {
		$sample_type = $_SESSION['sample_type'];
		$query.= " and sample_type like '$sample_type' ";
	}
	if (isset($_SESSION['shipment_type'])) {
		$shipment_type = $_SESSION['shipment_type'];
		$query.= " and shipment_type = '$shipment_type' ";
	}
	$query.= "GROUP BY `subdiv3` ORDER BY `subdiv3`;";
	$rackQuery = mysql_query($query, $conn);
	echo "<b>boxes in <i>rack $rack" . "</i></b>\n";
	if (mysql_num_rows($rackQuery) > 0) {
		for ($i = 0;$i < mysql_num_rows($rackQuery);$i++) {
			extract(mysql_fetch_array($rackQuery) , EXTR_PREFIX_ALL, 'div3');
			$num{$div3_subdiv3} = $div3_Rows;
		}
	}
	//         		for($j=1; $j<=$div3_Rows; $j++) {
	for ($j = 1;$j <= 24;$j++) {
		if ($num{$j}) {
			$numAlq = $num{$j};
			$bgColor = '#' . $eventColor[0] . '';
			//				} else {
			//				$numAlq = 0;
			//				$bgColor = '#'. $eventColor[3] .'';
			//				}
			$onClick = 'displayBox(\'' . $selectedFreezer . '\',\'' . $shelf . '\',\'' . $rack . '\',\'' . $j . '\')';
			echo '<div class="freezerFloat" id="box_' . $j . '" style="background-color: ' . $bgColor . '; cursor: pointer;" 
				onMouseOver="highlightCell(\'box_' . $j . '\')"
				onMouseOut="resetCell(\'box_' . $j . '\')"
				onClick="' . $onClick . '">
				<span style="position: relative; top: ' . $tTop . '; left: 1px;">' . $j . ' - ' . $numAlq . '   samples</span>
				</div>';
		}
	}
	mysql_free_result($rackQuery);
}

function getBox() {
	$freezer = $_POST['freezer'];
	$selectedFreezer = urlencode($freezer);
	$shelf = $_POST['shelf'];
	$rack = $_POST['rack'];
	$box = $_POST['box'];
	echo "<b>Freezer</b>: $freezer
		<br><b>Shelf</b>:     $shelf
		<br><b>Rack:</b>      $rack
		<br><b>Box:</b>       $box
		<br><b>uuid:</b>      $id_uuid";
	if ($_SESSION['task'] == 'store') {
?>
                        <form action="" method="post">
                         <div><label>sample id</label>
                                <input type="text" id="boxuuidIn" name="boxuuidIn" /></div>
                                <div id="boxuuidIn" class="autocomplete"></div>
                                <script type="text/javascript"> 
                                new Ajax.Autocompleter("boxuuidIn","hint","rpc.php", {afterUpdateElement : displayBox('0','0','0','0')});
                                </script>
                </div>
                        </form>
<?php
	} else {
		echo "<br><a href=rpc.php?action=exportBox&freezer=$selectedFreezer&shelf=$shelf&rack=$rack&box=$box>print tube labels</a>\n";
	}
}

function getWell() {
	$tableid = $_POST['tableid'];
	$wellResult = mysql_query("SELECT * FROM `locations` WHERE `id` LIKE '$tableid'", $conn);
	if (!$wellResult) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	$row = mysql_fetch_row($wellResult);
	echo $row[0];
	mysql_free_result($wellResult);
}

function retScannedObject($table, $id) {
	if ($table != 'items') {
		echo "this object does not exist in the items table";
		exit;
	}
	// lets get some info about this item
	$type = type($id);
	// and use that to determine how we'll look at it
	if ($type == 'shelf') {
		$_SESSION['containerid'] = $id;
		$_SESSION['containertype'] = $type;
		shelfArray($id);
		shelfView();
	} else if ($type == 'rack') {
		$_SESSION['containerid'] = $id;
		$_SESSION['containertype'] = $type;
		rackArray($id);
		rackView();
	} else if ($type == 'box') {
		$_SESSION['containerid'] = $id;
		$_SESSION['containertype'] = $type;
		boxArray($id);
		topView();
	} else if ($type == 'tube') {
?>
                        <script type="text/javascript">
                        getItemId(<?php echo $id
?>)
                        </script>
                        <?php
	}
}
// lets get some info about this item

function operateScannedObject($table, $id) {
	if (isset($id)) {
		$type = type($id);
	} else {
		echo "Error: no id";
		exit;
	}
//	$_SESSION['Detailid'] = $id;
//	$_SESSION['Detailtype'] = $type;
	if ($type == 'box') {
	print topView();
	$_SESSION['boxid'] = $id;
	} else if ($type == 'rack') {
	print rackView($id);
	} else if ($type == 'shelf') {
	print shelfView($id);
	} else {
?>
                        <script type="text/javascript">
				new TableOrderer('taskcontainer',{url : 'npc.php?action=data&format=json&type=detailId' , paginate:true, search:true, pageCount:10, filter:true});

                        </script>
                	<?php
	}
}

function data_array($type) {
	if (($type == 'search') || ($type == 'export')) {
		// very large arrays slow the browser too much
		if (isset($_SESSION['rowlimit']) and (isnum($_SESSION['rowlimit'])) and ($_SESSION['rowlimit'] > 0)) {
			$rowlimit = mysql_real_escape_string($_SESSION['rowlimit']);
		} else {
			$rowlimit = 500;
		}
		if ($type == 'export') {
			$rowlimit = 200000;
		}
		global $title;
		//$_SESSION['id_assay'] = array('TnI II','_BNPSTAT','MPO882010','proBNP','Cardi_PlGF','Cardi_PlGF','Anti-cTnI','Uric');
		//$datestart = $_SESSION['datestart'];
		$datestart = '0000-00-00';
		//$dateend = $_SESSION['dateend'];
		$dateend = '3000-00-00';
		// construct date portion of query
		//if (strtotime($datestart) < strtotime($dateend)) {
		$date_query = " 1 = 1 ";
		$query = "select items_id_subject as id_subject,items_id_visit as id_visit,items_date_visit as date_visit,
	items_date_receipt as date_receipt,items_sample_type as sample_type,
	items_shipment_type as shipment_type,items_destination as destination,items_id as id,items_id_uuid as id_uuid,items_comment1 as comment1,items_box_id as id_box,
	items_rack_id as id_rack, items_shelf_id as id_shelf,
	freezer,locations_subdiv1 as subdiv1,locations_subdiv2 as subdiv2,locations_subdiv3 as subdiv3,locations_subdiv4 as subdiv4,locations_subdiv5 as subdiv5
	from vwinventory where";
		$query.= query_filter(array(
			'id_study',
			'sample_type',
			'id_subject',
			'shipment_type',
			'id_visit',
			'type',
			'destination'
		) , array(
			'items_id_study',
			'items_sample_type',
			'items_id_subject',
			'items_shipment_type',
			'items_id_visit',
			'items_type',
			'items_destination'
		));
		$query.= $date_query;
		$query.= " order by freezer,subdiv1,subdiv2,subdiv3,subdiv4,subdiv5 ";
		$result = mysql_query($query);
		$numrows = mysql_num_rows(mysql_query($query));
		if ($numrows >= $rowlimit) {
			$returnArray = array(
				array(
					"matches" => $numrows,
					"max" => $rowlimit
				)
			);
		} else {
			$freezerArray = array();
			while ($row = mysql_fetch_assoc($result)) {
				if ($row['id_shelf'] > '0') {
					$row['shelf'] = retDiv($row['id_shelf'], 'subdiv1');
				} else {
					$row['shelf'] = 'null';
				}
				if ($row['id_rack'] > '0') {
					$row['rack'] = retDiv($row['id_rack'], 'subdiv2');
				} else {
					$row['rack'] = 'null';
				}
				if ($row['id_box'] > '0') {
					$row['box'] = retDiv($row['id_box'], 'subdiv3');
				} else {
					$row['box'] = 'null';
				}
				if (($row['shelf'] == 'null') && ($row['rack'] == 'null') && ($row['box'] == 'null')) {
					$row['shelf'] = $row['subdiv1'];
					$row['rack'] = $row['subdiv2'];
					$row['box'] = $row['subdiv3'];
				} else {
					$row['freezer'] = retDiv($row['id_shelf'], 'freezer');
				}
				$row['row'] = num2chr($row['subdiv4']);
				$row['column'] = $row['subdiv5'];
			//	$row['print'] = '<input type="button" value="print" onclick="printlabel(\'' . $row['id'] . '\',\'items\')">';
				unset($row['id_shelf'], $row['subdiv1'], $row['id_rack'], $row['subdiv2'], $row['id_box'], $row['subdiv3'], $row['subdiv4'], $row['subdiv5']);
				array_push($freezerArray, $row);
			}
			$returnArray = $freezerArray;
		}
	} else if ($type == 'detailId') {
		$query = "select items_id_subject as id_subject,items_id_visit as id_visit,items_date_visit as date_visit,items_date_receipt as date_receipt,
	items_shipment_type as shipment_type,items_destination as destination,items_id as id,items_id_uuid as id_uuid,items_comment1 as comment1,items_box_id as id_box,
	items_rack_id as id_rack, items_shelf_id as id_shelf,
	 freezer,locations_subdiv1 as shelf,locations_subdiv2 as rack,locations_subdiv3 as box,locations_subdiv4 as row,locations_subdiv5 as col 
	from vwinventory  where ";
		///these queries are taking way too long
		/*
		from VwLocationsAndItems  where ";
		if (isset($_SESSION['Detailtype']) && (($_SESSION['Detailtype']) == 'box'  || ($_SESSION['Detailtype']) == 'box' ) || ($_SESSION['Detailtype']) == 'rack'  || ($_SESSION['Detailtype']) == 'shelf') {
		$query.= ' items_'.$_SESSION['Detailtype'].'_id = ' .$_SESSION['Detailid'];
		} else {
		*/
		$query.= ' items_id = ' . $_SESSION['Detailid'];
		//	}
		$result = mysql_query($query);
		$detailArray = array();
		while ($row = mysql_fetch_assoc($result)) {
			$row['id_subject'] = showval($row['id'], 'id_subject', $row['id_subject'], '9', 'yes');
			$row['id_visit'] = showval($row['id'], 'id_visit', $row['id_visit'], '5', 'yes');
			$row['date_visit'] = showval($row['id'], 'date_visit', $row['date_visit'], '8', 'yes');
			$row['date_receipt'] = showval($row['id'], 'date_receipt', $row['date_receipt'], '8', 'no');
			$row['destination'] = showval($row['id'], 'destination', $row['destination'], '8', 'yes');
			$row['shipment_type'] = showval($row['id'], 'shipment_type', $row['shipment_type'], '8', 'no');
			if ($row['comment1'] == "") {
				$row['comment1'] = "comment";
			}
			$row['comment1'] = showval($row['id'], 'comment1', $row['comment1'], '8', 'yes');
			//		$row['box'] = "<div onclick=\"postId(".$row['id_box'].")\"; style=\"background-color: lightgrey\">".$row['box']."</div>";
//			$row['print'] = '<input type="button" value="print" onclick="printlabel(' . $row['id'] . ',\'items\')">';
			unset($row['id_rack'], $row['id_shelf'], $row['id_box']);
			array_push($detailArray, $row);
		}
		$returnArray = $detailArray;
	}
	return $returnArray;
}
