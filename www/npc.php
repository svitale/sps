<?php
//* see if browser requests a uuid
if (isset($_POST['uuid'])) {
	$postUuid = $_POST['uuid'];
}
if (isset($_GET['uuid'])) {
	$postUuid = $_GET['uuid'];
}
// or an id
if (isset($_POST['id'])) {
	$postId = $_POST['id'];
}
if (isset($_GET['id'])) {
	$postId = $_GET['id'];
}
// or a table
if (isset($_POST['table'])) {
	$postTable = $_POST['table'];
}
if (isset($_GET['table'])) {
	$postTable = $_GET['table'];
}
//date ranges
if (isset($_POST['start'])) {
	$PostStart = $_POST['start'];
}
if (isset($_GET['start'])) {
	$PostStart = $_GET['start'];
}
if (isset($_POST['end'])) {
	$PostEnd = $_POST['end'];
}
if (isset($_GET['end'])) {
	$PostEnd = $_GET['end'];
}
if (isset($_POST['variable'])) {
	$postVariable = $_POST['variable'];
}
if (isset($_GET['variable'])) {
	$postVariable = $_GET['variable'];
}
if (isset($_POST['param'])) {
	$postParam = $_POST['param'];
}
if (isset($_GET['param'])) {
	$postParam = $_GET['param'];
}
if (isset($_POST['mod'])) {
	$postMod = $_POST['mod'];
}
if (isset($_GET['mod'])) {
	$postMod = $_GET['mod'];
}
if (isset($_POST['value'])) {
	$postValue = $_POST['value'];
}
if (isset($_GET['value'])) {
	$postValue = $_GET['value'];
}
if (isset($_POST['select'])) {
	$postSelect = $_POST['select'];
}
if (isset($_GET['select'])) {
	$postSelect = $_GET['select'];
}
if (isset($_POST['role'])) {
	$postRole = $_POST['role'];
}
if (isset($_GET['role'])) {
	$postRole = $_GET['role'];
}
if (isset($_POST['format'])) {
	$postFormat = $_POST['format'];
}
if (isset($_GET['format'])) {
	$postFormat = $_GET['format'];
}
if (isset($_POST['parent'])) {
	$postParent = $_POST['parent'];
}
if (isset($_GET['parent'])) {
	$postParent = $_GET['parent'];
}
// or a type
if (isset($_POST['type'])) {
	$postType = $_POST['type'];
}
if (isset($_GET['type'])) {
	$postType = $_GET['type'];
}
//  or a quantity
if (isset($_POST['quant'])) {
	$postQuant = $_POST['quant'];
}
if (isset($_GET['quant'])) {
	$postQuant = $_GET['quant'];
}
if (isset($_POST['copies'])) {
	$postCopies = $_POST['copies'];
}
if (isset($_GET['copies'])) {
	$postCopies = $_GET['copies'];
}
//filename used by 'data' action
if (isset($_POST['filename'])) {
	$postFilename = $_POST['filename'];
}
if (isset($_GET['filename'])) {
	$postFilename = $_GET['filename'];
}
//if true, appends "yyyymmdd" to the filename in the 'data' action
if (isset($_POST['filenameadddate'])) {
	$postFilenameAddDate = $_POST['filenameadddate'];
}
if (isset($_GET['filename'])) {
	$postFilenameAddDate = $_GET['filenameadddate'];
}

//* see if browser is looking for action and if so set the $action variable
if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = 'null';
}
if (isset($_SESSION['task'])) {
	$task = $_SESSION['task'];
}

switch ($action) {
	case 'null':
		echo "action is null";
	break;
	case 'skip':
		unset($_SESSION['place']);
		$container = $_POST['container'];
		$spot = $_POST['spot'];
		$hide = $_POST['hide'];
		if (isset($_SESSION['skip'])) {
			$skip  = $_SESSION['skip'];
		} else {
			$skip = array();
		}
		if ($hide == 1) {
			$skip{$container}{$spot} = $hide;
		} else if (isset($skip{$container}{$spot})){
			unset($skip{$container}{$spot});
		}
		$_SESSION['skip'] = $skip;
	break;
	case 'place':
		unset($_SESSION['skip']);
		$container = $_POST['container'];
		$spot = $_POST['spot'];
		$clear = $_POST['clear'];
		$j = $_POST['j'];
		$k = $_POST['k'];
		if (isset($_SESSION['place'])) {
			$place  = $_SESSION['place'];
		} else {
			$place = array();
		}

		if (($clear == 1) || (
				$_SESSION['place']{$container}{$j} == $j &&
				$_SESSION['place']{$container}{$k} == $k)
		) {
			unset($_SESSION['place']);
		}
		else if (($place{$container}{'j'} == $j) && ($place{$container}{'k'} == $k)){
			unset($_SESSION['place']);
		}
		else {
			$place = array();
			$place{$container}{'j'} = $j;
			$place{$container}{'k'} = $k;
			$_SESSION['place'] = $place;
		}
	break;

	case 'settask':
		settask();
	break;
	case 'filtervar':
		echo filtervar($postVariable, $postTable, $postMod);
	break;
	case 'setparam':
		echo setParam($postParam,$postValue,$postMod);
	break;

	case 'newcontainer':
	$new_uuid = new_uuid(true);
	$id = uuid2Id('items', $new_uuid,0);
	operateScannedObject('items', $id);
	break;

	case 'scan':
		lib('scan');
		break;


	case 'settype':
		if (!$postId) {
			echo 'error: no id entered';
			exit;
		}
		if (!$postType) {
			echo 'error: no type entered';
			exit;
		}
		modId($postId, $postType);
		break;
	case 'daterange':
		$_SESSION['datestart'] = $PostStart;
		$_SESSION['dateend'] = $PostEnd;
		break;
	case 'filtersam':
		if ($_POST['sample_type']) {
			$_SESSION['sample_type'] = $_POST['sample_type'];
			echo $_POST['sample_type'];
		}
		if ($_POST['id_visit']) {
			$_SESSION['id_visit'] = $_POST['id_visit'];
		}
		break;
	case 'filter':
        if (isset($_POST['freezer'])) {
			$_SESSION['freezer'] = $_POST['freezer'];
		}
        if (isset($_POST['gwasreplacement'])) {
			$_SESSION['gwasreplacement'] = $_POST['gwasreplacement'];
		}
	        if (isset($_POST['pull_name'])) {
			$_SESSION['pull_name'] = $_POST['pull_name'];
		}
        
		if (isset($_POST['crf'])) {
			$_SESSION['crf'] = $_POST['crf'];
		}
		if (isset($_POST['sample_type'])) {
			$_SESSION['sample_type'] = $_POST['sample_type'];
		}
		if (isset($_POST['id_visit'])) {
			$_SESSION['id_visit'] = $_POST['id_visit'];
		}
		if (isset($_POST['id_study'])) {
			unset($_SESSION['params']);
                        $study = New Study();
                        $study->id_study = $_POST['id_study'];
                        $study->Loader();
                        $GLOBALS['sps']->setActiveStudy($study);
		}
		if (isset($_POST['id_subject'])) {
			if ($_POST['id_subject'] == '') {
				unset($_SESSION['id_subject']);
			} else {
				$_SESSION['id_subject'] = $_POST['id_subject'];
			}
		}
		if (isset($_POST['printer_name'])) {
			$_SESSION['printer_name'] = $_POST['printer_name'];
		}
		if (isset($_POST['shipment_type'])) {
			$_SESSION['shipment_type'] = $_POST['shipment_type'];
		}
		if (isset($_POST['family'])) {
			$_SESSION['family'] = $_POST['family'];
		}
		if (isset($_POST['id_instrument'])) {
			$_SESSION['id_instrument'] = $_POST['id_instrument'];
		}
		if (isset($_POST['order'])) {
			$_SESSION['order'] = $_POST['order'];
		}

		if (isset($_POST['group_by'])) {
			if ($_POST['group_by'] == 'none') {
			unset($_SESSION['group_by']);
			} else {
			$_SESSION['group_by'] = $_POST['group_by'];
			}
		}

		if (isset($_POST['show_incomplete'])) {
			$_SESSION['show_incomplete'] = $_POST['show_incomplete'];
		}
		if (isset($_POST['show_complete'])) {
			$_SESSION['show_complete'] = $_POST['show_complete'];
		}
		if (isset($_POST['show_menu'])) {
			$_SESSION['show_menu'] = $_POST['show_menu'];
		}
		if (isset($_POST['import_source'])) {
			$_SESSION['import_source'] = $_POST['import_source'];
		}
		if (isset($_POST['id_assay'])) {
			$newassay = $_POST['id_assay'];
			if (!$_SESSION['id_assay']) {
				$assayarray = array(
					$newassay
				);
			} else {
				$assayarray = $_SESSION['id_assay'];
				array_push($assayarray, $newassay);
			}
			$_SESSION['id_assay'] = $assayarray;
		}
		break;
	case 'setvar':
		if (!is_array($_SESSION[$postVariable])) {
			$_SESSION[$postVariable] = array(
				$postValue
			);
		} else {
			array_push($_SESSION[$postVariable], $postValue);
		}
		break;
	case 'crfed':
		$postId = $_POST['id'];
		$postField = $_POST['field'];
		$postValue = mysql_real_escape_string($_POST['value']);
		if (!$postValue) {
			echo 'error: no id entered';
			exit;
		}
		crf_edit($postId, $postField, $postValue);
		break;
	case 'getInventory':
		getInventory($postFormat, $postParent);
		break;
	case 'getFreezer':
		getFreezer();
		break;
	case 'itemed':
		//	$postId = $_POST['uuid'];
		$postField = $_POST['field'];
		$postValue = mysql_real_escape_string(trim($_POST['value']));
		if ((!$postId) or (strlen($postId) == 0)) {
			echo 'error: no id entered';
			exit;
		}
        // don't update with the default "null" value or the error messages
        if ((!isset($postValue)) or (strlen($postValue) == 0 and $postField != 'destination')
                or ($postValue == "-")
                or ($postValue == "error: no id entered") or ($postValue == "error: no value entered")) {
			echo "error: no value entered";
			exit;
		}
		item_edit($postId, $postField, $postValue);
		break;
	case 'ed':
		$postId = $_POST['id'];
		$postField = $_POST['field'];
		$postValue = $_POST['value'];
		$postTable = $_POST['table'];
		if ((!$postId) or (strlen($postId) == 0)) {
			echo 'error: no id entered';
			exit;
		}
        if ((!isset($postValue)) or (strlen($postValue) == 0)) {
			echo "error: no value entered";
			exit;
		}
		edit($postId, $postField, $postValue, $postTable);
		break;
	case 'vol':
		if (isset($_SESSION['Detailid']) && isset($_POST['value']) && isset($_SESSION['DetailpostTable'])) {
			vol($_SESSION['Detailid'],$_POST['value'],$_SESSION['DetailpostTable']);
		} else {
			echo 'error: no id entered';
			exit;
		}
		break;
	case 'thaw':
		$postValue = $_POST['value'];
		if (!$postValue) {
			echo 'error: no id entered';
			exit;
		}
		if (($_SESSION['Detailid'] > 0) && (($_SESSION['DetailpostTable']) == 'items')) {
			$id = $_SESSION['Detailid'];
			thaw($id, $postValue);
		}
		break;
	case 'retreceipt':
                if (isset($_GET['type'])) {
			$type = $_GET['type'];
                } else {
			$type = 'json';
                }
		return retReceipt($type);
		break;
	case 'pendingshipments':
		return pendingShipments();
		break;
	case 'exportbatch':
		exportBatch();
		break;
	case 'exportboxlist':
		$freezer = $_SESSION['freezer'];
		$shelf = '';
		$rack = '';
		retBoxList($freezer, $shelf, $rack);
		break;
	case 'data':
	lib('data');
		output_data();
		break;
	case 'importbatch':
		importBatch($postValue);
		break;
	case 'dashboard':
		dashBoard();
		break;
	case 'reconcile':
		reconcile();
		break;
	case 'printaliquots':
		printAliquots($_POST['id_subject']);
		break;
        case 'printblanks':
                printBlanks($postQuant, $postCopies, $postFormat);
                break;
	case 'remitem':
		remItem($_POST['id']);
		break;
	case 'replacebox':
		replaceBox($postId);
		break;
	case 'printbatch':
		if (printBatch() == 1) {
		print "labels printed \n";
		} else {
		print "there was a problem printing \n";
		}

		break;
	case 'benchview':
		if (isset($_SESSION['type']) && $_SESSION['type'] == 'shelf') {
			$v = $_SESSION['shelfid'];
			echo '<div class="rackFloat" id="rackview" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(222,222,222); cursor: pointer;" onmouseover="highlightCell(\'rackview\')" onmouseout="resetCell(\'rackview\')" onclick="selectUuid(\'' . retUuid($v) . '\')">';
			echo 'shelf';
			echo '</div>';
		} else if (isset($_SESSION['box_array']) && isset($_SESSION['boxid'])) {
			echo '<div class="rowFloat row" id="bench_header" style="width: 180px;">';
			$box_array = $_SESSION['box_array'];
			foreach(array_keys($box_array) as $k) {
				$v = $box_array[$k]['id'];
				$m = $box_array[$k]['dest'];
				if (array_key_exists($m,$color)) {
					//$bordercolor = colorop($color{$m}, '505050');
					$bordercolor = $color{$m};
				} else {
					$bordercolor = "888888";
				}
				if ($v == $_SESSION['boxid']) {
					$active = "activebox";
				} else {
					$active = "";
				}
				echo '<div class="boxFloat '.$active.'" id="boxview_' . $k . '" style="cursor: pointer; border-color: #' . $bordercolor . '" onclick="postId(\'' . $v . '\',\'tableId\')">';
				echo $m;
				echo '</div>';
			}
			echo '</div>';
		}
		if (isset($_SESSION['rack_array']) && isset($_SESSION['rackid'])) {
			echo '<div class="rowFloat row" id="bench_header" style="width: 180px;">';
			$rack_array = $_SESSION['rack_array'];
			foreach(array_keys($rack_array) as $k) {
				$v = $rack_array[$k]['id'];
				$m = $rack_array[$k]['dest'];
				echo '<div class="rackFloat" id="rackview_' . $k . '" style="border: 1px solid rgb(0, 0, 0); background-color: rgb(';
				if ($v == $_SESSION['rackid']) {
					echo "222,240,222";
				} else {
					echo "222,222,222";
				}
				echo '); cursor: pointer;" onmouseover="highlightCell(\'rackview_' . $k . '\')" onmouseout="resetCell(\'rackview_' . $k . '\')" onclick="selectUuid(\'' . retUuid($v) . '\')">';
				echo $m;
				echo '</div>';
			}
			echo '</div>';
		}
		break;
	case 'printlabel':
                global $sps;
		$id = $_POST['id'];
                $table = $_POST['table'];
                $printer = New PrintDev();
if (isset($sps->printer)) {
                $printer = $sps->printer;
} else {
print "no printer selected";
exit;
}
                $job = New PrintJobs();
                $job->printer_id =  $printer->printer_id;
                $status = $job->spoolPrintJob($id, $table);
		break;
	case 'download':
		Download($_GET['file']);
		break;
	case 'printcontents':
		$sent = 0;
		$id_container = $_POST['id'];
		$contents_array = retBoxContents($id_container);
		foreach($contents_array as $id) {
			spoolLabel($id, 'items', '1');
			$sent = $sent + ftpFiles();
		}
		print "$sent of ".count($contents_array) ." labels printed\n";
		break;
	case 'printfwalqs':
		$id_study = $_SESSION['id_study'];
		$sent = 0;
		$idsArray = fwid2Id($postId,$id_study);
		foreach($idsArray as $id) {
			spoolLabel($id, 'items', '1');
			$sent = $sent + ftpFiles();
		}
		print "$sent of ".count($idsArray) ." labels printed\n";
		break;
	case 'printarray':
		$container_id = $_POST['id'];
		$array = $_SESSION[$session_name];
		foreach(array_keys($array) as $k) {
			spoolLabel($array[$k], 'items', '1');
			ftpFiles();
		}
		break;
	case 'cleardestination':
		cleardestination($_POST['id']);
		break;

	case 'cleardestination':
		cleardestination($_POST['id']);
		break;
	case 'clearlocation':
		clearlocation($_POST['id']);
		break;
	case 'alq':
		if (isset($_POST['id'])) {
			$id = $_POST['id'];
			$spool = '0';
		} else if (($_SESSION['Detailid'] > 0) && (($_SESSION['DetailpostTable']) == 'items')) {
			$id = $_SESSION['Detailid'];
			$spool = '1';
		} else {
			echo "no id";
			exit;
		}
		if ($_POST['table'] == 'items') {
			edit($id, 'alq_tot', $_POST['daughters'], 'items');
		}
		// also update the sister alaquots who are still in the batch table
		//echo $postValue;
		aliquot_s($id, $_POST['table'], $_POST['daughters'], $spool);
		//ftpFiles();
		printFiles();
		break;
	case 'alq':
		aliquot($_POST['id'], $_POST['table'], $_POST['daughters']);
		//ftpFiles();
		printFiles();
		break;
	case 'aliquot':
		aliquotShipment($_POST['id'], $_POST['id_subject']);
		exit;
		break;
	case 'alqbatch':
		alqbatch($_POST['id_subject']);
		exit;
		break;
	case 'alqed':
		$id_subject = $_POST['id_subject'];
		$sample_type = $_POST['sample_type'];
		$id_visit = $_POST['id_visit'];
		$quantity = $_POST['value'];
		$family = $_POST['family'];
		$_SESSION['quantity']{$id_subject}{$sample_type}{$id_visit}{$family} = $quantity;
		echo $_SESSION['quantity']{$id_subject}{$sample_type}{$id_visit}{$family};
		exit;
		break;
	case 'exportbox':
		export_box();
		break;
	case 'importspreadsheet':
		lib('Xlt');
		if (isset($_POST['usetemplate'])) {
			$XLTemplate = $_POST['usetemplate'];
		} else {
			$XLTemplate = $_SESSION['task'];
		}
		if (file_exists($GLOBALS['root_dir'] . '/include/Xlt/'.$XLTemplate.'.php')) {
			include($GLOBALS['root_dir'] . '/include/Xlt/'.$XLTemplate.'.php');
		} else {
			include($GLOBALS['root_dir'] . '/include/Xlt/defaultreader.php');
		}
		break;

	case 'appendbatch':
		if ($_POST['id_visit']) {
			$id_visit = $_POST['id_visit'];
		} else {
			$id_visit = $_SESSION['id_visit'];
		}
		$id_study = $_SESSION['id_study'];
		$batchuuid = $_POST['batchuuid'];
		$family = $_SESSION['family'];
		$date_visit = $_POST['date_visit'];
		$date_collection = $_POST['date_collection'];
		if ($_POST['id_subject']) {
			$id_subject = $_POST['id_subject'];
			subjectArray($id_subject);
		} else {
			$id_subject = retNextsubid($id_study);
		}
		newShipment($id_subject, $batchuuid, $date_collection, $id_visit);
		break;
	case 'setbatch':
		$id_subject = $_POST['id_subject'];
		$id_visit = $_POST['id_visit'];
		$date_visit = $_POST['date_visit'];
		if ($id_subject && $id_visit && $date_visit) {
			setBatch($id_subject, $id_visit, $date_visit);
		}
		break;
	case 'disjoinbatch':
		$batchuuid = $_POST['batchuuid'];
		$id_subject = $_POST['id_subject'];
		$id = $_POST['id'];
		disjoinShipment($id, $id_subject, $batchuuid);
		break;
	case 'invoicesubject':
			$batchuuid = $_SESSION['batchuuid'];
		if (isset($_POST['id_subject'])) {
			$id_subject = $_POST['id_subject'];
		}
		if ($id_subject > '0') {
			subjectArray($id_subject);
			$key = array_keys($_SESSION['subject_array'], $id_subject);
			header("Content-type: application/json; charset=utf-8");
			$arr = array(
				'group_' . $key[0] => crForm($batchuuid,$id_subject, $key[0])
			);
			echo json_encode($arr);
		}
		break;
	case 'invoice':
		if ($_SESSION['task'] == 'pendingshipments') {
		} else {
			lib('crfinvoice');
			$batchuuid = $_SESSION['batchuuid'];
		}
		if (isset($_POST['id_subject'])) {
			$id_subject = $_POST['id_subject'];
		}
		if (isset($_POST['uuid'])) {
			if (isUuid($_POST['uuid'])) {
				$id_uuid = $_POST['uuid'];
			} else {
				echo 'Could not resolve barcode';
				exit;
			}
			$result = mysql_query("update batch_quality set id_batch = '$batchuuid' where id_uuid = '$id_uuid'");
			if (!$result) {
				echo 'Could not run query: ' . mysql_error();
				exit;
			}
			$result = mysql_query("update batch_quality set date_receipt = CURDATE(), quality = 1, status = 1 where id_uuid = '$id_uuid' and date_receipt = '0000-00-00'");
			if (!$result) {
				echo 'Could not run query: ' . mysql_error();
				exit;
			}
			$result = mysql_query("select id_subject from batch_quality where id_uuid = '$id_uuid'");
			if (!$result) {
				echo 'Could not run query: ' . mysql_error();
				exit;
			}
			$row = mysql_fetch_row($result);
			$id_subject = $row[0];
		}
		if ($id_subject > '0') {
			subjectArray($id_subject);
			$key = array_keys($_SESSION['subject_array'], $id_subject);
			header("Content-type: application/json; charset=utf-8");
			//$foo = 	crForm($batchuuid,$id_subject,$key[0]);
			$arr = array(
				'group_' . $key[0] => crForm($id_subject, $key[0])
			);
			echo json_encode($arr);
		}
		break;
	case 'crfpending':
		lib('crfpending');
		if (isset($_POST['id_subject'])) {
			$id_subject = $_POST['id_subject'];
		}
		if ($id_subject > '0') {
			subjectArray($id_subject);
			$key = array_keys($_SESSION['subject_array'], $id_subject);
			header("Content-type: application/json; charset=utf-8");
			$arr = array(
				'group_' . $key[0] => crForm($id_subject, $key[0])
			);
			echo json_encode($arr);
		}
		break;
	case 'transfer':
		$result = mysql_query("SELECT * FROM `specimentypes` WHERE `family` LIKE 'cric_transdry' order by `order`");
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		while ($row = mysql_fetch_array($result)) {
			$row = mysql_fetch_row($result);
		}
?>
	<table>
		<TR>
			<TD WIDTH=28></TD>
			<TD WIDTH=27>$seq</TD>
			<TD WIDTH=93>$type</TD>
			<TD WIDTH=60>$volume</TD>
			<TD WIDTH=35>$status_shipped</TD>
			<TD WIDTH=36>status_not_available</TD>
			<TD WIDTH=45>$status_rna</TD>
			<TD WIDTH=51>$status_un_un</TD>
			<TD WIDTH=29>$status_nr</TD>
			<TD WIDTH=60>$status_un_un</TD>
			<TD WIDTH=45>$thawed</TD>
			<TD WIDTH=51>$label_error</TD>
			<TD WIDTH=39>$low</TD>
			<TD WIDTH=45>$damaged</TD>
			<TD WIDTH=39>$delayed</TD>
			<TD WIDTH=28>$other</TD>
		</TR>
	</table>	
	<?php
		mysql_free_result($result);
		break;
	case 'summary':
		echo "<p>Assays run to date</p>";
		echo retAssaysRun($postId);
		echo "<p>Freezerworks Records</p>";
		echo retLocations($postId);
		break;
	case 'retbarcodes':
		echo "bc:";
		$barcodes = retBarcodes($postId);
		echo implode($barcodes, ",");
		break;
	case 'search':
		//if ($_POST['tableid']) {
		//	$id = $_POST['tableid'];
		//} else {
		//$id = retId($postId);
		//}
		if (isset($postUuid)) {
			$id = retId($postUuid);
		} else if (isset($postId)) {
			$id = $postId;
		}
		if ($postTable = 'items') {
			lib('iteminfo');
			retItem($id);
		} else if ($postTable = 'batch_quality') {
			retBqthing($id);
		}
		break;
	case 'hint':
		echo showHint($postId);
		break;
	case 'ses_reset':
		resetsession();
		break;
	case 'newType':
		$_SESSION['type'] = $_POST['type'];
	//	seeForm($_POST['id']);
		break;


	case 'newParam':
		$param =  $_POST['param'];
		$query = "SELECT value,param from params where id = $param";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) {
		$_SESSION[$row['param']] = $row['value'];
		}
		break;

	case 'newFreezer':
		$_SESSION['freezerid'] = $_POST['freezer'];
		break;

	case 'setFreezer':
		$_SESSION['freezer'] = $_POST['freezer'];
		//seeForm($_POST['id']);
		break;
	case 'selectFreezer':
		$_SESSION['freezerid'] = $_POST['freezerid'];
	//	seeForm($_POST['shelfid']);
		break;
	case 'newDest':
		$_SESSION['destination'] = $_POST['destination'];
		//seeForm($_POST['id']);
		break;

	case 'newSampleType':
		$_SESSION['sample_type'] = $_POST['type'];
		//seeForm($_POST['id']);
		break;

	case 'newresult':
		$id = $_POST['id'];
		$table = $_POST['table'];
		$id_assay = $_POST['id_assay'];
		lib('orders');
		if (newresult($id,$id_assay)) {
			$return = Detail($id,$table);
		} else {
			$return =  "Error: new assay not added";
		}
		print $return;
		break;

	case 'neworder':
		$id = $_POST['id'];
		$table = $_POST['table'];
		lib('orders');
		if (neworder($id)) {
			$return = Detail($id,$table);
		} else {
			$return =  "Error: new order not added";
		}
		print $return;
		break;
	case 'deleteorder':
		if (is_numeric($_POST['id'])) {
			lib('orders');
			deleteorder($_POST['id']);
			$return = Detail($id,$table);
		} else {
			$return = "non-numeric order id";
		}
		print $return;
		break;



	case 'addfreezer':
		$id = $_POST['id'];
		$type = $_POST['type'];
		$width = $_POST['width'];
		$hight = $_POST['hight'];
		$comment1 = $_POST['comment1'];
		$dest = $_SESSION['destination'];
		modId($id, $type, $width, $hight, $comment1, $dest);
		echo $comment1;
		unset($_SESSION['type']);
		unset($_SESSION['lab']);
		break;
	case 'associd':
		$id = $_POST['id'];
		$type = $_POST['type'];
		$width = $_POST['width'];
		$hight = $_POST['hight'];
		$comment1 = $_POST['comment1'];
		if (isset( $_SESSION['destination'])) {
			$dest = $_SESSION['destination'];
		} else {
			$dest  = '';
		}
		if (isset($_SESSION['sample_type'])) {
			$sample_type = $_SESSION['sample_type'];
		} else {
			$sample_type = '';
		}
		modId($id, $type, $width, $hight, $comment1, $dest, $sample_type);
		if (($type == 'shelf') && (isset($_SESSION['freezerid']))) {
			setShelf($id, $_SESSION['freezerid'], $hight);
		}
		unset($_SESSION['type']);
		unset($_SESSION['lab']);
		break;
	case 'box':
		topView();
		break;
	case 'edBox':
		$freezer = $_POST['freezer'];
		$shelf = $_POST['shelf'];
		$rack = $_POST['rack'];
		$box = $_POST['box'];
		edBox($freezer, $shelf, $rack, $box);
		break;
	case 'boxstatus':
		$boxid = $_SESSION['boxid'];
		$seq = $_SESSION['seq_' . $boxid];
		$boxdivX = $_SESSION['divX_' . $boxid];
		$y = (($seq % $boxdivX) + 1);
		$x = (floor($seq / $boxdivX) + 1);
		echo substr(retUuid($boxid) , 0, 8) . '<p>';
		echo "Next Well:" . num2chr($x) . $y;
		break;
	case 'samplestatus':
		echo retDestDist($postId);
		break;
	case 'checkresult':
		$value = checkResult($postId, $postRole, $postValue);
		echo $value;
		break;
	case 'findsamples':
		$id_subject = $postId;
		echo "<p>Item Info</p>";
		$query = "SELECT * FROM `items` left join locations on (items.id = locations.id_item) WHERE `id_subject` = '$id_subject'";
		if (isset($_SESSION['sample_type'])) {
			$query.= " and items.sample_type = '" . $_SESSION['sample_type'] . "'";
		}
		if (isset($_SESSION['id_visit'])) {
			$query.= " and items.id_visit = '" . $_SESSION['id_visit'] . "'";
		}
		if (isset($_SESSION['id_study'])) {
			$query.= " and items.id_study = '" . $_SESSION['id_study'] . "'";
		}
		if (isset($_SESSION['shipment_type'])) {
			$query.= " and items.shipment_type = '" . $_SESSION['shipment_type'] . "'";
		}
		$query.= ";";
		$result = mysql_query($query);
		if (!$result) {
			echo 'Could not run query: ' . mysql_error();
			exit;
		}
		while ($row = mysql_fetch_object($result)) {
			$tableid = $row->id;
			$created = strftime('%m/%d/%Y', strtotime($date_collection));
			$id_uuid = $row->id_uuid;
			$destination = $row->destination;
			echo "<div>";
			echo $row->id_subject;
			echo " ";
			echo $row->sample_type;
			echo " ";
			echo $row->id_visit;
			echo " ";
			echo $row->shipment_type;
			echo " ";
			echo $row->date_create;
			echo " ";
			echo $row->destination;
			echo " ";
			echo $row->id_container;
			echo " ";
			echo $row->freezer;
			echo " S-" . $row->subdiv1;
			echo " R-" . $row->subdiv2;
			echo " B-" . $row->subdiv3;
			echo " ";
			echo num2chr($row->subdiv4);
			echo $row->subdiv5;
			echo "</div>";
			/*
			echo "<p>".retFieldcomment('id_subject','items').": ".$id_subject."</p>";
			echo "<p>".retFieldcomment('id_visit','items').": ".$id_visit."</p>";
			echo "<p>".retFieldcomment('sample_type','items').": ".$sample_type."</p>";
			echo "<p>".retFieldcomment('destination','items').": ".$destination."</p>";
			echo "<p>".retFieldcomment('shipment_type','items').": ".$shipment_type."</p>";
			echo "<p>".retFieldcomment('date_collection','items').": ".$date_collection."</p>";
			echo "<p>".retFieldcomment('id_study','items').": ".$id_study."</p>";
			echo '<input type="button" value="new label" onclick="printlabel('.$tableid.',\'items\')">';
			*/
		}
		mysql_free_result($result);
		break;
	case 'detail':
		if (isset($postUuid)) {
			$id = retTableId($postUuid, $postTable);
		} else if (isset($postId)) {
			$id = $postId;
		}
		echo Detail($id, $postTable);
		$_SESSION['Detailid'] = $id;
		$_SESSION['DetailpostTable'] = $postTable;
		break;
	case 'resultdetail':
		$id = $postId;
		echo resultDetail($id, $postTable);
		break;
    
    // calls from the inventory tab; display the contents of a container
	case 'freezerSelect':
		freezerInventory($_POST['freezername']);
		break;
	case 'shelfSelect':
		shelfInventory($_POST['freezername'], $_POST['shelfid']);
		break;
	case 'rackSelect':
		rackInventory($_POST['freezername'], $_POST['shelfid'], $_POST['rackid']);
		break;
    case 'boxSelect':
		boxInventory($_POST['freezername'], $_POST['shelfid'], $_POST['rackid'], $_POST['boxid']);
		break;
	}
?>
