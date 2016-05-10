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
		topView($id);
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

function operateScannedObject($table, $id, $short=0) {
if (($short == 1) && isset($_SESSION['containerid'])) {
        $html = '<div>Error:  You may not store a sample by scanning a linear barcode.</br> Please scan the full 2d UUID.</br>If you need to re-print the label click here</div>';
	$html .= '<input type="button" value="re-print"  onclick="printlabel(\''.$id.'\',\''.$table.'\')";>';
	$html .= "</div>";

	print $html;
	exit;
}
        //fields common to both tables that we want transferred
        $src_fields = 'date_receipt,date_ship,date_visit,destination,id_barcode,hemolyzation,id_encounter,id_parent,id_study,id_subject,id_uuid,id_visit,name_created,name_last_updated,name_shipper,notes,quant_init,quant_init,quant_thaws,sample_source,sample_type,sample_name,shipment_type,treatment,collection_time,sample_identifier,sample_collos_id,type';
        $dst_fields = 'date_receipt,date_ship,date_visit,destination,id_barcode,hemolyzation,id_encounter,id_parent,id_study,id_subject,id_uuid,id_visit,name_created,name_last_updated,name_shipper,notes,quant_cur,quant_init,quant_thaws,sample_source,sample_type,sample_name,shipment_type,treatment,collection_time,sample_identifier,sample_collos_id,type';
        //pre-processing occurs when autoexecute is set to true
        $behavior = new Behavior();
        if ($behavior->autoexecute) {
		lib('Process');
                $inv_object = New InventoryObject();
                $inv_object->table = $table;
                $inv_object->id = $id;
                $inv_object->Fetcher();
                $process = new Process();
                $process->active_object = $inv_object;
                $process->autoexecutor($behavior);
                $processed_object = $process->active_object;
                $id = $processed_object->id;
                $table = $processed_object->table;
        } 
	if ($table == 'batch_quality') {
        $sql = "insert into items ($dst_fields) (select $src_fields  from batch_quality where id = '$id')";
        //echo $sql;
		$insert = mysql_query($sql);
		$id = mysql_insert_id();
		if ($id > 0) {
			$table = 'items';
		} else {
			echo "Could not move from batch_quality to items:\n";
			echo mysql_error();
			exit;
		}
	}
	if (isset($id)) {
		$type = type($id);
	} else {
		echo "Error: no id";
		exit;
	}
	if ($type == 'unassigned') {
		$containerid = $_SESSION['containerid'];
		newItem($id);
	} else {
		// is this the container or the containee? if it's not a tube and there's no container set:
		//CONTAINER
		if (!isset($_SESSION['containerid'])) {
			// we can have multiple boxes, we can't have multiples of everything else tho
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
				topView($id);
			} else if ($type == 'tube') {
?>
                        <script type="text/javascript">
                        getItemId(<?php echo $id
?>)
                        </script>
                	<?php
			}
			//if there's already a container defined
			
		} else {
			$containertype = $_SESSION['containertype'];
			$containerid = $_SESSION['containerid'];
			// shelves have predefined positions and can not be moved, so if a shelf is scanned, just make it the container
			if ($type == 'shelf') {
				unset($_SESSION['box_array']);
				unset($_SESSION['rack_array']);
				unset($_SESSION['shelf_array']);
				$_SESSION['containerid'] = $id;
				$_SESSION['containertype'] = $type;
				shelfArray($id);
				shelfView();
				// putting away a rack, if there's a shelf we put the rack into it
				
			} else if ($type == 'rack') {
				if ($containertype == 'shelf') {
					if (retContainer($id) != $_SESSION['containerid']) {
						addRack($_SESSION['containerid'], $id);
						shelfView();
					} else {
						//		$_SESSION['containerid'] = $id;
						//		$_SESSION['containertype'] = $type;
						rackArray($id);
						rackView();
					}
				} else {
					// otherwise we make the rack the center of attention
					unset($_SESSION['box_array']);
					unset($_SESSION['rack_array']);
					$_SESSION['containerid'] = $id;
					$_SESSION['containertype'] = $type;
					rackArray($id);
					rackView();
				}
				// putting away a box
				
			} else if ($type == 'box') {
				if ($containertype == 'rack') {
					if (retContainer($id) != $containerid) {
						echo retContainer($id);
						echo $containerid;
						addBox($_SESSION['containerid'], $id);
						rackView();
					} else {
						$_SESSION['boxid'] = $id;
						//			$_SESSION['containerid'] = $id;
						//			$_SESSION['containertype'] = $type;
						topView($id);
					}
					?>
                        	<script type="text/javascript">
                        	getItemId(<?php echo $id?>);
                        	</script>
					<?php
					// maybe later we'll let people put boxes right onto shelves..
					//	} else if ($containertype == 'shelf') {
					//		addBox($_SESSION['containerid'],$id);
					//		shelfView();
					
				} else {
					unset($_SESSION['shelf_array']);
					$_SESSION['containerid'] = $id;
					$_SESSION['containertype'] = $type;
					boxArray($id);
					topView($id);
?>
                        <script type="text/javascript">
                        benchView();
                        </script>
                	<?php
				}
				// putting away a tube
				
			} else if ($type == 'tube') {
				if ($containertype == 'box') {
					$_SESSION['boxid'] = distrib($id);
					$boxid = $_SESSION['boxid'];
					if (!isset($_SESSION['seq_' . $boxid])) {
						$_SESSION['seq_' . $boxid] = '0';
					}
					$seq = $_SESSION['seq_' . $boxid];
					addtube(($_SESSION['boxid']) , $id);
?>
                        	<script type="text/javascript">
                        	getItemId(<?php echo $id?>);
                       		 benchView();
                        	</script>
                		<?php
					topView($id);
					$_SESSION['seq_' . $boxid]++;
				} else {
?>
                        <script type="text/javascript">
                        	getItemId(<?php echo $id?>);
                       		 benchView();
                        </script>
			<?php
				}
				//replacing the container otherwise
				
			} else {
				echo "Can't figure out what to do with this Item";
?>
                        <script type="text/javascript">
                        getItemId(<?php $id
?>)
                        </script>
                	<?php
			}
		}
	}
}
function freezerworksRelabel($idsArray,$scanned) {
	$return =  "<div>".count($idsArray) . " aliquot(s) with this barcode never split</div>";
                                $i = 0;
                                foreach ($idsArray as $id) {
                                        $i++;
                                        $detail = getItemInformation($id);
                                        if (isset($detail['freezer'])  && $detail['freezer'] != "") {
                                                $location_info = $detail['freezer'].",".$detail['subdiv1'].",".$detail['subdiv2'].",".$detail['subdiv3'].",".num2chr($detail['subdiv4']).",".$detail['subdiv5'];
                                                $found = $id;
                                        } else {
                                                //first aliquot with no location information can be printed automatically if 
                                                $location_info = "no location info";
                                                $lost = $id;
                                        }
                                        $clickthis .=  "<div><a href='#' onclick=";
                                                if (isset($_SESSION['autoprint']) && isset($_SESSION['printer_name'])) {
                                                        $clickthis .= "printlabel('$id','items');";
                                                        //$clickthis .=  "alert('$id');";
                                                }
                                        $clickthis .=  "getItemId(".$id.");";
                                        $clickthis .= ">alq ".$i.": ".$location_info."</a></div>";
                                }
                                //if everything has been split except for lost tubes we can operate
                                if (!isset($found) && isset($lost)  && isset($_SESSION['select_first_missing'])) {
                                        //spool last label if user has automatic printing enabled
                                        if (isset($_SESSION['autoprint']) && isset($_SESSION['printer_name'])) {
                                                $return .= "<script>";
                                                $return .= "printlabel('$lost','items');";
                                              //  $return .=  "alert('$lost');";
                                                $return .= "</script>";
                                        }
                                        operateScannedObject('items',$lost);
                                } else {
						$return .=  "<div>".$detail['id_subject']."</div>";
                                                $return .= $clickthis;
						$return .=  "<div><input class='btn' type='submit' value='print' name='print' onclick=printfwalqs('$scanned')></div>";
                                }
	return $return;
}


function data_array() {
	$inventoryArray = array();
        $containerid = $_SESSION['containerid'];
        $query = "SELECT * FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item` ) WHERE `id_container` = '$containerid' and locations.date_moved is null ";
	if (divY($containerid) < 0 ) {
	$query .= "order by subdiv5,subdiv4"; 
	} else {
	$query .= "order by subdiv4,subdiv5";
	}
	$result = mysql_query($query);
	while ($row = mysql_fetch_assoc($result)) {
		$row['subdiv4'] = num2chr($row['subdiv4']);
		$row['well_id'] = $row['subdiv4'].$row['subdiv5'];
		array_push($inventoryArray, $row);
	}
		$returnArray = $inventoryArray;
	return $returnArray;
}
