<?php
global $postValue,$postType,$postTable,$task,$id_study,$study;
$short = 0;
if (isset($_SESSION['id_study']) && ($_SESSION['id_study'] != '%')) {
$id_study = $_SESSION['id_study'];
}
	

		//in this function we figure out what has been scanned then pass the results on to the task specific function defined in $task/includes/include.php
		if ((!isset($postValue)) || ($postValue == "") || ($postValue == "undefined")) {
		echo "Error: didn't get anything to scan";
			exit;
		}
        $postValue = trim($postValue);
		//valueType: uuid,uuidShort,jsFunction,lpFunction,linearBarcode,tableId
		if (isset($postType)) {
			$valueType = $postType;
		} else {
			//see if we can determine what type of id was scanned
			$valueType = retIdType($postValue);
		}
		if (isset($postTable)) {
                        $inTable = $postTable;
                }
		if ($valueType == 'uuid') {
			$short = '0';
		} else if ($valueType == 'uuidShort') {
			$valueType = 'uuid';
			$short = '1';
		}
		if (isset($postTable) && ($valueType == 'uuid')) {
			$inTable = $postTable;
			$id = uuid2Id($inTable, $postValue, $short);
		}
		if (($valueType == 'uuid') && (!isset($inTable))) {
			// figure out which table contains info about this item
			$arrayTables = retInTable($postValue, $short);
			if (in_array('items', $arrayTables)) {
				$id = uuid2Id('items', $postValue, $short);
				$inTable = 'items';
                operateScannedObject($inTable, $id, $short);
			} else if (in_array('batch_quality', $arrayTables)) {
				$id = uuid2Id('batch_quality', $postValue, $short);
				$inTable = 'batch_quality';
                		operateScannedObject($inTable, $id, $short);
			} else {
                echo "<div>This barcode couldn't be found.</div>";
			}
		} else if ($valueType == 'tableId') {
			if (!isset($inTable)) {
			$inTable = 'items';
			}
			operateScannedObject($inTable,$postValue, $short);
//freezerworks barcodes
		} else if (($valueType == 'linearBarcode') && isset($study['allow_linear'])) {
                        lib('Model/Inventory');
			$sample = new InventoryObject;
			$sample->linear_barcode = $postValue;
			if ($sample->resolveScannedLinearBarcode()) {
                             $inTable = $sample->table;
                             $id = $sample->id;
                		operateScannedObject($inTable, $id, $short);
			} else {
                            print "Error: barcode could not be resolved\n";
			}
		} else if ($valueType == 'linearBarcode') {
			$idsArray = fwid2Id($postValue,$id_study);
			if (count($idsArray) > 0  and $task == 'store'){ 
		                $html = freezerworksRelabel($idsArray,$postValue);
			        print $html;
			//} else {
			} else {
			$postValue = retbqid($postValue);
			operateScannedObject('batch_quality',$postValue, $short);
			}
		} else if ($valueType == 'jsFunction') {
			$command = substr("$postValue", 2);
			echo '<script type="text/javascript">';
			echo stripslashes($command);
			echo "</script>";
		} else if ($valueType == 'npcFunction') {
global $command;
			$command = substr("$postValue", 4);
                        lib('barcodes');			
		} else if ($valueType == 'lpFunction') {
			$command = substr("$postValue", 6);
        		printCommand($command);

		} else if ($valueType == 'rungroup') {
			$id_rungroup = substr("$postValue", 9);
			$_SESSION['id_rungroup'] = $id_rungroup;
			echo '<script type="text/javascript">';
			echo 'window.location.reload();';
			echo "</script>";
				
		}
?>
