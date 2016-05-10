<?php
global $perms, $skip, $place;
if (isset($_SESSION['task'])) {
    $task = $_SESSION['task'];
    if ($task != 'store') {
        $perms = 'ro';
    } else {
        $perms = 'rw';
        if (isset($_SESSION['skip'])) {
            $skip = $_SESSION['skip'];
    	} else {
	    	$skip = array();
	    }

	    if (isset($_SESSION['place'])) {
	    	$place = $_SESSION['place'];
	    }
	    else {
	    	$place = array();
	    }
	}

	//$name_created  =  $GLOBALS['sps']->username;
	//$GLOBALS['sps']->username;
	$name_created = $_SESSION['username'];
}
//
//* color coded view of shelf contents
function shelfView($shelfid=false) {
	global $skip, $color;
        $j=1;
	if(!$shelfid) {
	$shelfid = $_SESSION['shelfid'];
	}
	if (!isset($_SESSION['divX_' . $shelfid])) {
		$_SESSION['divX_' . $shelfid] = divX($shelfid);
	}
	$shelfdivX = $_SESSION['divX_' . $shelfid];
	$shelfQuery = mysql_query("SELECT items.id,items.id_uuid,items.shipment_type,items.id_visit,locations.subdiv2 FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item`) WHERE `id_container` = '$shelfid' and locations.date_moved is null");
	if (mysql_num_rows($shelfQuery) > 0) {
		for ($i = 0;$i < mysql_num_rows($shelfQuery);$i++) {
			extract(mysql_fetch_array($shelfQuery));
			//* we're making a matrix - and dropping the racks into it
			//* rackarray is a list of racks and values that have this shelf location
			$uuidShort{$subdiv2} = substr($id_uuid, 0, 8);
			$uuidLong{$subdiv2} = $id_uuid;
                        if (isset($sample_type)) {
			    $sample_type{$subdiv2} = $sample_type;
                        } else {
			    $sample_type{$subdiv2} =''; 
                        }
			$shipment_type{$subdiv2} = $shipment_type;
			$id_visit{$subdiv2} = $id_visit;
			if (isset($resolved) && ($resolved == 'no')) {
				$hasissue{$subdiv2} = 'X';
			}
			$tableid{$subdiv2} = $id;
		}
	}
	// make sure the item is in the correct container
	displayLocationInformation($shelfid, 'shelf');
	//* create a matrix
	echo "<div id='containercontainer' class='container span5'>";
	echo '<div class=row id="row_1">';
	//* racks are put into shelfes starting on top right but we're
	//* going to allow users to skip a spot
	unset($next_j);
	for ($k = '1';$k <= $shelfdivX;$k++) { 
		$rack = ($k);
		//*
		//* check to see if that position has variable declared
		//* it's the same as the last one, don't change the color
		//* if it's new, generate a color for it
		//* if that position has no value then set the color to white
		if (isset($hasissue) && isset($hasissue{$rack}) && $hasissue{$rack} == 'X') {
			$r = '255';
			$g = '0';
			$b = '0';
		} else if (isset($uuidShort) && isset($uuidShort{$rack})) {
			if (!isset ($x) ||  $x != $uuidShort{$rack}) {
				$x = $uuidShort{$rack};
				$fx = (pi() * $x / 10000000);
				$gx = ((pi() * ($x) * (1 / 10)));
				$r = round(128 * (1 + sin($fx)));
				$g = round(128 * (1 + cos($fx)));
				$b = round(130 + 32 * (1 + cos($gx)));
			}
		} else {
			//* if we don't have a value, we don't have a color
			$r = 240;
			$g = 240;
			$b = 240;
		}
			if (isset($tableid{$rack})) {
			$bgColor = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
			$onClick = 'postScan(\'' . $uuidLong{$rack} . '\')';
			$tooltip = $uuidShort{$rack};
			$text =  $j . "  " . $k;
			} else if (isset($skip{$shelfid}{$rack})) {
			$bgColor = '#000000';
			$onClick = 'skipSpot(\''.$shelfid.'\',\''.$rack.'\',0)';
			$tooltip = 'click to unskip';
			$text = "";
			} else if (isset($skip)) {
			$bgColor = '#FFFFFF';
			$onClick = 'skipSpot(\''.$shelfid.'\',\''.$rack.'\',1)';
			$tooltip = 'click to skip';
			$text = "";
			} else  {
				$bgColor = '#FFFFFF';
				$onClick = '';
				$tooltip = '';
				$text = '';
			}
		if (($j <= ($shelfdivX)) && ($k >= 1)) {
			echo '<div class=wellFloat id="well_' . ($k * $j) . '" 
        			style="background-color: ' . $bgColor . '; cursor: pointer;" 
				onMouseOver="tooltip(\'' . $tooltip . '\')"
        			onMouseOut="exit()"
				onClick="' . $onClick . '">';
                        if (isset($hasissue) && isset($hasissue{$rack})) {
			echo $hasissue{$rack} . " ";
                        }
			echo $text . "</div>\n";
		}
	}
	echo "</div>\n";
	echo "<div>";
	echo '<div><input type="button" value="print shelf label" onclick="printlabel(' . $shelfid . ',\'items\')"></div>';
echo '<div><input type="button" value="info" onclick="getItemId(\'' . $shelfid . '\')"></div>';
	echo "</div>";
	mysql_free_result($shelfQuery);
}

function addRack($shelfid, $rackid) {
        $destination = '';
	global $name_created,$skip,$perms;
	if ($perms != 'rw') {
		print "you aren't allowed to do that here";
		exit;
	}
	$shelf_array = $_SESSION['shelf_array'];
	foreach(array_keys($shelf_array) as $k) {
		$i = $shelf_array[$k]['id'];
		$d = $shelf_array[$k]['dest'];
		if ($i == $rackid) {
			$destination = $d;
		}
	}
	$shelfdivX = $_SESSION['divX_' . $shelfid];
	$filled_spots = mysql_query("select subdiv2 from `locations` where id_container = '" . $shelfid . "' and date_moved is null");
	if (!$filled_spots) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($filled_spots) > 0) {
		for ($i = 0;$i < mysql_num_rows($filled_spots);$i++) {
			extract(mysql_fetch_array($filled_spots));
			//* we're making a matrix - and dropping the racks into it
			//* rackarray is a list of racks and values that have this location
			$filled{$subdiv2} = "1";
		}
	}
	for ($x = 1;$x < ($shelfdivX+1);$x++) {
		if (!isset($filled{$x}) && (!isset($skip{$shelfid}{$x}))) {
			break;
		}
	}
	if ($x == $shelfdivX+1) {
		print "<script type='javascript'>alert('this container is full');</script>";
	} else {
		$locations_insert = mysql_query("INSERT INTO `locations` (id_item,id_container,subdiv2,name_created) values ('$rackid','$shelfid','$x','$name_created')");
		if (!$locations_insert) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		$loc_id = mysql_insert_id();
		$locations_update = mysql_query("update locations set date_moved = curdate() where date_moved is NULL  and id_item = '$rackid' and id != '$loc_id'");
		if (!$locations_update) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		$items_update = mysql_query("UPDATE `items` set destination = '$destination', `name_last_updated` = '" . $GLOBALS['sps']->username . "' where id  = '$rackid'");
		if (!$items_update) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}

function rackView($rackid=false) {
	global $skip, $color;
	if (!$rackid) {
		$rackid = $_SESSION['rackid'];
	}
	if (!isset($_SESSION['divX_' . $rackid]) ||  !isset($_SESSION['divY_' . $rackid])) {
		$_SESSION['divX_' . $rackid] = divX($rackid);
		$_SESSION['divY_' . $rackid] = divY($rackid);
	}
	$rackdivX = abs($_SESSION['divX_' . $rackid]);
	$rackdivY = $_SESSION['divY_' . $rackid];
	$rackQuery = mysql_query("SELECT items.id_uuid,items.sample_type,items.shipment_type,items.id_visit,items.id,locations.subdiv3,items.destination FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item`) WHERE `id_container` = '$rackid' and locations.date_moved is null");
	if (mysql_num_rows($rackQuery) > 0) {
		for ($i = 0;$i < mysql_num_rows($rackQuery);$i++) {
			extract(mysql_fetch_array($rackQuery));
			$tt_uuidShort->$subdiv3 = substr($id_uuid, 0, 8);
			$uuidLong{$subdiv3} = $id_uuid;
			$tt_1->$subdiv3 = substr($id_uuid, 0, 8);
			$tt_2->$subdiv3 = $sample_type;
			$tt_3->$subdiv3 = $shipment_type;
			$tt_4->$subdiv3 = $id_visit;
			$tableid{$subdiv3} = $id;
			$dest{$subdiv3} = $destination;
		}
	}
	// make sure the item is in the correct container
	displayLocationInformation($rackid, 'rack');
	//if ($shelfid = checkContainerRack($rackid)) checkContainerShelf($shelfid, false);
	//* create a matrix
		echo "<div id='containercontainer' class='container span5'>";
		echo '<div><input type="button" value="info" onclick="getItemId(\'' . $rackid . '\')"></div>';
if ($_SESSION['divX_' . $rackid] != $rackdivX) {
	for ($j = '1';$j <= $rackdivY;$j++) {
		echo '<div class=row id="row_' . $j . '">';
		//* boxes are put into racks starting on top right but we're
		//* going to let users skip a spot
		unset($next_j);
		for ($k = $rackdivX;$k >= '1';$k--) { 
			$box = ((($j - 1) * $rackdivX) + $k);
			//*
			//* check to see if that position has variable declared
			//* it's the same as the last one, don't change the color
			//* if it's new, generate a color for it
			//* if that position has no value then set the color to white
			if (isset($tableid{$box})) {
			$bgColor = '#' . $color{$dest{$box}};
			$onClick = 'inventoryDetail(\'' . $uuidLong{$box} . '\')';
			$tooltip = "<div>";
			for ( $tt_num = 0; $tt_num < 6; $tt_num++ ) {
				$tooltip .= "<div>".${"tt_".$tt_num}->$box."</div>";
//				$tooltip .= "<div>".$tt_1->$box."</div>";
			}
			$tooltip .= "</div>";
		//	$text =  $j . "  " . $k;
			$text = $box;
			} else if (isset($skip{$rackid}{$box})) {
			$bgColor = '#000000';
			$onClick = 'skipSpot(\''.$rackid.'\',\''.$box.'\',0)';
			$tooltip = 'click to unskip';
			$text =  "";
			} else if (isset($skip)) {
			$bgColor = '#FFFFFF';
			$onClick = 'skipSpot(\''.$rackid.'\',\''.$box.'\',1)';
			$tooltip = 'click to skip';
			$text =  "";
			} else  {
				$bgColor = '#FFFFFF';
				$onClick = '';
				$tooltip = '';
				$text = '';
			}
		if (($k <= ($rackdivX * $rackdivY)) && ($j >= 1)) {
			echo '<div class=boxFloat id="well_' . $j.'_'.$k.'"';
			echo 'style="background-color: ' . $bgColor . '; cursor: pointer"';
				echo 'onMouseOver="tooltip(\'' . $tooltip . '\')"
        			onMouseOut="exit()"
				onClick="' . $onClick;
				echo '">';
			echo  $text .  "</div>\n";
		}
		}
		echo "</div>\n";
	}
} else {
	for ($j = '1';$j <= $rackdivY;$j++) {
		echo '<div class=row id="row_' . $j . '">';
		//* boxes are put into racks starting on top right but we're
		//* going to let users skip a spot
		unset($next_j);
		for ($k = $rackdivX;$k >= '1';$k--) { 
			$box = ((($k - 1) * $rackdivY) + $j);
			//*
			//* check to see if that position has variable declared
			//* it's the same as the last one, don't change the color
			//* if it's new, generate a color for it
			//* if that position has no value then set the color to white
			if (isset($tableid{$box})) {
			$bgColor = '#' . $color{$dest{$box}};
			$onClick = 'inventoryDetail(\'' . $uuidLong{$box} . '\')';
			$tooltip = "<div>";
			for ( $tt_num = 0; $tt_num < 6; $tt_num++ ) {
if (isset(${"tt_".$tt_num})) {
				$tooltip .= "<div>".${"tt_".$tt_num}->$box."</div>";
}
//				$tooltip .= "<div>".$tt_1->$box."</div>";
			}
			$tooltip .= "</div>";
			$text =  $j . "  " . $k;
			} else if (isset($skip{$rackid}{$box})) {
			$bgColor = '#000000';
			$onClick = 'skipSpot(\''.$rackid.'\',\''.$box.'\',0)';
			$tooltip = 'click to unskip';
			$text =  "";
			} else if (isset($skip)) {
			$bgColor = '#FFFFFF';
			$onClick = 'skipSpot(\''.$rackid.'\',\''.$box.'\',1)';
			$tooltip = 'click to skip';
			$text =  "";
			} else  {
				$bgColor = '#FFFFFF';
				$onClick = '';
				$tooltip = '';
				$text = '';
			}
		if (($j <= ($rackdivX * $rackdivY)) && ($k >= 1)) {
			echo '<div class=boxFloat id="well_' . $k.'_'.$j.'"';
			echo 'style="background-color: ' . $bgColor . '; cursor: pointer"';
				echo 'onMouseOver="tooltip(\'' . $tooltip . '\')"
        			onMouseOut="exit()"
				onClick="' . $onClick;
				echo '">';
			echo  $text .  "</div>\n";
		}
		}
		echo "</div>\n";
	}
}
echo "<div>";
echo '<div><input type="button" value="print rack label" onclick="printlabel(' . $rackid . ',\'items\')"></div>';
echo '<div><input type="button" value="manifest" onclick="window.location.href=\'npc.php?action=data&format=xls&type=manifest&id=' . $rackid . '\'"></div>';
echo "</div>";
echo "</div>";
mysql_free_result($rackQuery);
}

function addBox($rackid, $boxid) {
	global $name_created,$skip,$perms;
	if ($perms != 'rw') {
		print "you aren't allowed to do that here";
		exit;
	}
	$rack_array = $_SESSION['rack_array'];
	foreach(array_keys($rack_array) as $k) {
		$i = $rack_array[$k]['id'];
		$d = $rack_array[$k]['dest'];
		if ($i == $rackid) {
			$destination = $d;
		}
	}
	$rackdivX = abs($_SESSION['divX_' . $rackid]);
	$rackdivY = abs($_SESSION['divY_' . $rackid]);
	$rackspots = ($rackdivX)*($rackdivY);
	$filled_spots = mysql_query("select subdiv3 from `locations` where id_container = '" . $rackid . "' and date_moved is null");
	if (!$filled_spots) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($filled_spots) > 0) {
		for ($i = 0;$i < mysql_num_rows($filled_spots);$i++) {
			extract(mysql_fetch_array($filled_spots));
			//* we're making a matrix - and dropping the boxes into it
			//* boxarray is a list of boxs and values that have this location
			$filled{$subdiv3} = "1";
		}
	}
	for ($x = 1;$x < ($rackspots+1);$x++) {
		if (!isset($filled{$x}) && (!isset($skip{$rackid}{$x}))) {
			break;
		}
	}
	if ($x == $rackspots+1) {
		print "<script type='javascript'>alert('this rack is full');</script>";
	} else {
		$locations_insert = mysql_query("INSERT into `locations` (id_item,id_container,subdiv3,destination,name_created) values ('$boxid','$rackid','$x','$destination','$name_created')");
		if (!$locations_insert) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		$loc_id = mysql_insert_id();
		$locations_update = mysql_query("update locations set date_moved = curdate() where date_moved is NULL  and id_item = '$boxid' and id != '$loc_id'");
		if (!$locations_update) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
	}
}
/**
 * display a color coded view of the box defined in $_SESSION['boxid']
 * if $tubeid is a 'tube' item and is in the box, it's cell is highlighted
 * the type of item of $tubeid does not match the box, an error is displayed
 * 
 * @param integer $tubeid id from the items table (does not need to be a tube)
 * @param array $highlightCells an array of 2d arrays of box coordinates
 */

function topView($tubeid = '', $highlightCells=null) {
	global $sps,$name_created,$skip,$color,$id_study,$place;
        $highlightedIds = $sps->highlighted;
        $classes = array();
        if($highlightedIds) {
            foreach($highlightedIds as $name=>$highlights) {
                foreach($highlights as $id) {
                    if($classes[$id]) {
                        $classes_for_id = $classes[$id];
                    } else {
                        $classes_for_id = array();
                    }
                    array_push($classes_for_id,$name);
                    $classes[$id] = $classes_for_id;
                }
            }
        }
	if (isset($_SESSION['boxid'])) {
		$boxid = $_SESSION['boxid'];
		$box = getBoxInfo($boxid);
		if (!isset($_SESSION['divX_' . $boxid])) {
			$_SESSION['divX_' . $boxid] = $box['divX'];
		}
		if (!isset($_SESSION['divY_' . $boxid])) {
			$_SESSION['divY_' . $boxid] = $box['divY'];
		}
		$boxdivX = $box['divX'];
		$boxdivY = abs($box['divY']);
		$paramArray = array('sample_type','shipment_type','id_visit','id_study');
		$paramCount = count($paramArray);
		$checkCount = 0;
		foreach ($paramArray as $param) {
			if (isset($box[$param]) && $box[$param] != '' && $box[$param] != 'mixed') {
				$check[$param] = true;
				$checkCount++;
			} else {
				$check[$param] = false;
			}
		}


		/*print "<script type='javascript'>alert('place:'". $place{$boxid}. ");</script>";
		print_r($place);
*/
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
		$sql = 'SELECT * FROM `locations` LEFT JOIN (items) ON (`items`.`id`=`locations`.`id_item`) WHERE `id_container` = ' . $boxid . ' and locations.date_moved is null';
		$result = mysql_query($sql);
		//	echo "total #: ";
		while ($row = mysql_fetch_array($result)) {
			//* we're making a matrix - and dropping the tubes into it
			//* tubearray is a list of tubes and values that have this box location
			$subdiv4 = $row['subdiv4'];
			$subdiv5 = $row['subdiv5'];
			$subject{$subdiv4}{$subdiv5} = $row['id_subject'];
			$tableid{$subdiv4}{$subdiv5} = $row['id'];
			$flag{$subdiv4}{$subdiv5} = $row['errorflag'];
			//* array for mismatches
			foreach ($paramArray as $num=>$param) {
				if ($check[$param] && $box[$param] != $row[$param]) {
					$mismatch{$subdiv4}{$subdiv5}[] = $num;
				}
			}
//			$class{$subdiv4}{$subdiv5} = str_replace("_","",$class{$subdiv4}{$subdiv5});
		}
		// make sure the item is in the correct container
		displayLocationInformation($boxid, 'box');
		
		if ($tubeid > 0) {
				// make sure the container sample/visit/shipment types match
				//checkContainerMatch($boxid,$tubeid);
				
				// if $tubeid actualy is a tube and is in this box, highlight it
				$tubeinfo = getItemInformation($tubeid);
				if (($tubeinfo['type'] == 'tube') && ($tubeinfo['id_container']) == $boxid) {
					$selX = $tubeinfo['subdiv4'];
					$selY = $tubeinfo['subdiv5'];
					$highlightCells[] = array($selX, $selY);

					if ($mismatch{$selX}{$selY}) {
						$fieldStr = "[";
						foreach($mismatch{$selX}{$selY} as $num) {
							$fieldStr .= $paramArray[$num].', ';
						}
						$fieldStr = substr($fieldStr, 0, -2) . ']';
						echo "<div class=\"alert\">Warning: tube/box mismatch on $fieldStr. </div>";
					}
				}	
		}

		
		// create item detail div, temporary
		//* create a matrix
		echo "<div id='containercontainer' class='container span5'>";
                
		if (isset($color{dest($boxid)})) {
			$bgcolor = $color{dest($boxid)};
		} else {
			$bgcolor = 'DODODO';
		}
		echo "<div class='row boxViewContainer pull-left' style='border-color: #$bgcolor; background-color: #D0D0D0'>";
		echo '<div>';
		echo '<input type="button" value="+" onclick="getItemId(' . $boxid . ')">';
		echo "<select id='boxSkipFlag'> <option value='skip'>Skip</option'> <option value='place'>Place</option'></select>";
		echo '</div>';
		echo '<div class=row id="row_header">';
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
		//			for ($j=($jstart);(($jsign)*($j))<=($jend);$j+($sign)) {
		for ($jj = 1;$jj <= $boxdivX;$jj++) {
			if ($jstart > 0) {
				$j = ($jstart - $jj + 1);
			} else {
				$j = $jj;
			}
			echo '<div class=row id="row_' . $j . '">';
			unset($next_j);
			echo "<div class=wellFloat>" . num2chr($j) . "</div>";


			for ($kk = 1;$kk <= ($boxdivY);$kk++) {

				if ($kstart > 0) {
					$k = ($kstart - $kk + 1);
				} else {
					$k = $kk;
				} 

				// skip val
				if (isset($skip{$boxid}{$j.'-'.$k})) {
					$skipVal = 0;
				}
				else $skipVal = 1;

				if (isset($subject{$j}{$k})) {
					if ($subject{$j}{$k} == '0') {
						$r = 150;
						$g = 150;
						$b = 150;
						$bgColor = "rgb($r, $g, $b)";
					} else {
						$hash = md5('color' . $subject{$j}{$k});
						$r = hexdec(substr($hash, 0, 2)); // r
        				$g = hexdec(substr($hash, 2, 2)); // g
        				$b = hexdec(substr($hash, 4, 2)); //b
						
						$x = $subject{$j}{$k};
						$fx = (pi() * $x / 10000000);
						$gx = ((pi() * ($x) * (1 / 10)));
						$r = round(128 * (1 + sin($fx)));
						$g = round(128 * (1 + cos($fx)));
						$b = round(130 + 32 * (1 + cos($gx)));
						$bgColor = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
						$bgColor = genColor($x);
					}
                	//$bgColor = 'rgb(' . $r . ',' . $g . ',' . $b . ')';

					$tooltip = $subject{$j}{$k};
					$onClick = 'getItemId(\'' . $tableid{$j}{$k} .'\')';
				} else if (isset($skip{$boxid}{$j.'-'.$k})) {
					$bgColor = '#000000';
					
					$onClick = "if (jQuery('#boxSkipFlag').val() == 'skip') skipSpot('" .$boxid.'\',\''.$j.'-'.$k.'\',0); else placeSpot(\''.$boxid.'\',\''.$j."','".$k.'\',0);';
					$tooltip = 'click to unskip or place';
					$text = "";
				} else  if (isset($skip)) {
					$bgColor = '#FFFFFF';
					$onClick = "if (jQuery('#boxSkipFlag').val() == 'skip') skipSpot('".$boxid.'\',\''.$j.'-'.$k.'\',1); else placeSpot(\''.$boxid.'\',\''.$j."','".$k.'\',0);';
					$tooltip = 'click to skip or place';
					$text = "";
				} else  {
					$bgColor = '#FFFFFF';
					$onClick = '';
					$onDblClick = '';
					$tooltip = '';
					$text = '';
				}
				//if the cell is highlighted, change it's border color
				if ($place{$boxid}{j} == $j && $place{$boxid}{k} == $k) {
					$border = " #CC0000";
				}
				else if((is_array($highlightCells)) and in_array(array($j, $k), $highlightCells)) {
						$border = " yellow";
				} else {
						$border = " #000";
				}
				// display the div for the tube
				if (($j <= ($boxdivX)) && ($k >= 1)) {

                                        $item_id = $tableid{$j}{$k};
                                        if($classes[$item_id]) {
                	                        $tube_classes = implode($classes[$item_id],' ');
                                        } else {
                				$tube_classes = ''; 
                                        }
					print "<div class='wellFloat $tube_classes' ";
					print 'id="well_' . $j . $k . '" ';
					print 'style="';
					if (isset($mismatch{$j}{$k})) {
						$bgimage = array();
						foreach($mismatch{$j}{$k} as $num) {
							$bgimage[] = "url('/sps/images/circle.php?total=".$checkCount."&num=".$num."') ";
							$tooltip .= '<br><b>mismatch:'.$paramArray[$num].'</b>';
						}
						$background = implode($bgimage,",") . " $bgColor";
					} else {
						$background = $bgColor;
					}
					print 'background-color:'.$background.';';
					print 'border-color:'.$border.';
						cursor: pointer;" 
						onMouseOver="tooltip(\''.$tooltip.'\')"
						onMouseOut="exit()"
						onClick="' . $onClick . '">';
					if (isset($flag{$j}{$k}) && $flag{$j}{$k} != "Normal") {
//				if ((($errorflag{$j} {
//					$k
//				}) != 'Normal' )) {
					echo "<b>X</b>";
					}
					echo "</div>\n";
				}
				
				// if this tube matches $tubeid, dispay it's info
				if (isset($tableid) && isset($tableid{$j}{$k}) && $tubeid == $tableid{$j}{$k}) {
						$script = $onClick;
				}

			}
			echo "</div>\n";
		}
		print "<div class='span5'>";
		if (isset($tableid) && count($tableid) > 0) {
			print "<input type='button' value='print box contents' ";
			print "onclick='if(confirm(\"Are you sure you want to print ";
			print "all of those labels?\"))printcontents(\"$boxid\")'>";
		}
		print '<input type="button" value="new box" onclick="replaceBox(' . $boxid . ')">';
		print '<input type="button" value="manifest" onclick="window.location.href=\'npc.php?action=data&format=xls&type=manifest&id=' . $boxid . '\'">';
		print '<input type="button" value="print box labels" onclick="printlabel(' . $boxid . ',\'items\')">';
		print "</div>";
		print "</div>";
                print "<div id='dashboard' class='dashboard span5'></div>";
	}
}
//*
/**
 * Move a tube into a box
 * 
 * @uses $_SESSION[box_array]
 * @uses $_SESSION[divX_$boxid]
 * @uses $_SESSION[divY_$boxid]
 * 
 * @param int $boxid
 * @param int $tubeid
 * @param bool $transaction_flag=true if true, all updates and inserts occur within a transaction and are rolled back if an error occurs
 */
function addTube($boxid, $tubeid, $transaction_flag=true) {
	global $name_created,$skip,$perms,$place;
	if ($perms != 'rw') {
		print "you aren't allowed to do that here";
		exit;
	}
	$box_array = $_SESSION['box_array'];
	if ($boxid < 2) {
		echo 'Could not grok destination box';
		exit;
	}
	foreach(array_keys($box_array) as $k) {
		if ($box_array[$k]['id'] == $boxid) {
			$destination = $box_array[$k]['dest'];
			$boxdivX = $box_array[$k]['divX'];
			$boxdivY = $box_array[$k]['divY'];
		}
	}
	$filled_spots = mysql_query("select subdiv4,subdiv5 from `locations` where id_container = '" . $boxid . "' and date_moved is null");
	if (!$filled_spots) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
	if (mysql_num_rows($filled_spots) > 0) {
		for ($i = 0;$i < mysql_num_rows($filled_spots);$i++) {
			extract(mysql_fetch_array($filled_spots));
			$filled{$subdiv4}{$subdiv5} = "1";
		}
	}

	// get the spot to place the tube
	if(isset($place{$boxid}{j}) && isset($place{$boxid}{k})
		&& ($place{$boxid}{j} > 0) && ($place{$boxid}{k} > 0)){
		//print "<script type='javascript'>alert('place:');</script>";
		$x = $place{$boxid}{j};
		$y = $place{$boxid}{k};
		/*
		if (isset($filled{$x}{$y})) {
			print "<script type='javascript'>alert('There is already a tube at [$x, $y].');</script>";
		}
		else {
			print "<script type='javascript'>alert('There is no tube at [$x, $y].');</script>";
		}
		*/
	}
	else if ($boxdivY>0) {
		for ($x = 1;$x < (abs($boxdivX) + 1);$x++) {
			for ($y = 1;$y < $boxdivY;$y++) {
				if (!isset($filled{$x}{$y}) && (!isset($skip{$boxid}{$x.'-'.$y}))) {
					break;
				}
			}
			if (!isset($filled{$x}{$y}) && (!isset($skip{$boxid}{$x.'-'.$y}))) {
				break;
			}
		}
	} else {
		for ($y = 1;($y < abs($boxdivY) + 1);$y++) {
			for ($x = 1;$x < abs($boxdivX);$x++) {
				if (!isset($filled{$x}{$y}) && (!isset($skip{$boxid}{$x.'-'.$y}))) {
					break;
				}
			}
			if (!isset($filled{$x}{$y}) && (!isset($skip{$boxid}{$x.'-'.$y}))) {
				break;
			}
		}
	}
	if ($y == abs($boxdivY) + 1 or $x == abs($boxdivX) + 1) {
		print "<script type='javascript'>alert('this box is full');</script>";
	} else if (isset($filled{$x}{$y})) {
		print "<script type='javascript'>alert('There is already a tube at [". chr(64 + $x) . ", $y].');</script>";
		print "<script type='javascript'>placeSpot('$boxid',-1,-1,0);</script>";
	} else {
    	if($transaction_flag) mysql_query("start transaction");
		$locations_insert = mysql_query("INSERT INTO `locations` (id_item,id_container,subdiv4,subdiv5,destination, name_created) values ('$tubeid','$boxid','$x','$y','$destination', '$name_created')");
		if (!$locations_insert) {
			echo 'Could not run query: ' . mysql_error();
			mysql_query("rollback");
			exit;
		}
		$loc_id = mysql_insert_id();
		$locations_update = mysql_query("update locations set date_moved = curdate() where date_moved is NULL  and id_item = '$tubeid' and id != '$loc_id'");
		if (!$locations_update) {
			echo 'Could not run query: ' . mysql_error();
			if($transaction_flag) mysql_query("rollback");
		exit;
		}
		$items_update = mysql_query("UPDATE `items` set destination = '$destination', `name_last_updated` = '" . $GLOBALS['sps']->username . "' where id  = '$tubeid' and destination = ''");
		if (!$items_update) {
			echo 'Could not run query: ' . mysql_error();
			if($transaction_flag) mysql_query("rollback");
			exit;
		}
		if($transaction_flag) mysql_query("commit");
		if(isset($place{$boxid}{j}) && isset($place{$boxid}{k})) {
			print "<script type='javascript'>placeSpot('$boxid',-1,-1,0);</script>";
		}
	}
}
?>
