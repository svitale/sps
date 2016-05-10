<?php		
function output_data() {
global $sps, $action, $postFormat, $postId, $postType, $postFilenameAddDate, $basename,$returnArray;
if (!isset($basename) && isset($postFilename)) {
			$basename = $postFilename;
		}
		if (!isset($basename)) {
			$basename = $sps->task;;
                }
		$returnArray = array();
		if (!isset($postId)) {
			$returnArray = data_array($postType);
			if (!is_array($returnArray)) {
				echo "error - not an array";
				exit;
			}
		}
		if ($postType == 'snapshot') {
			if (isset($postId)) {
				$id_publish = $postId;
			} else {
				$id_publish = makeSnapshot($returnArray);
			}
			$returnArray = returnSnapshot($id_publish);
			$basename = 'snapshot_'.$id_publish;
		}
		if ($postType == 'resultsForId') {
				$returnArray = returnResults($postId);
		}
		if ($postType == 'manifest') {
			lib('manifest');
			 $returnArray = manifest($postId);
			$basename = 'manifest';
		}
		if ($postType == 'batchmanifest') {
			lib('manifest');
			$returnArray = batchmanifest();
			$basename = 'batchmanifest';
		}
        if ($postType == 'linearbcs') {
            $dataArray = $returnArray;
			$returnArray = array();
            foreach($dataArray as $row) {
                array_push($returnArray,array($row['id_uuid']));
            }
            $basename = 'manifest';
        }
		if (isset($postFilenameAddDate) and ($postFilenameAddDate == true)) {
			$basename .= "_" . date("Ymd");
		}
		
		//--export the data--
		if ($postFormat == 'xls') {
			lib('PHPExcel');
			if (isset($_GET['usetemplate'])) {
				$XLTemplate = $_GET['usetemplate'];
			} else {
				$XLTemplate = $_SESSION['task'];
			}
			if (file_exists('xltemplates/'.$XLTemplate.'.php')) {
				include('xltemplates/'.$XLTemplate.'.php');
			} else {
				lib('Xlt/defaultwriter');
			}
		} else if ($postFormat == 'csv' || $postFormat == 'txt') {
		//	}
			header("Cache-Control: no-cache, must-revalidate");
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=" . $basename . "." . $postFormat);
			$flag = false;
			foreach($returnArray as $row) {
			if (isset($_GET['flag'])) {
			$flag = $_GET['flag'];
			}
				if (!$flag) {
					# display field/column names as first row
					print implode(",", array_keys($row)) . "\r";
					$flag = true;
				}
				$return =  implode(",", array_values($row)) . "\r";
				print $return;
			}
		} else if ($postFormat == 'zip') {
			$zipArray = array();
			$zip = new ZipArchive();
			$filename = '/tmp/data.zip';
			if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
				exit("cannot open <$filename>\n");
			}
			foreach($returnArray as $row) {
				if (isset($row['name_plate'])) {
					if (!is_array($zipArray{$row['name_plate']})) {
						$zipArray{$row['name_plate']} = array();
					}
					array_push($zipArray{$row['name_plate']},$row);
				}
			}
			foreach (array_keys($zipArray) as $name_plate) {
					$contents =  implode(",", array_values($zipArray{$name_plate}[0])) . "\r";
				foreach($zipArray{$name_plate} as $row) {
					$contents .=  implode(",", array_values($row)) . "\r";
				}
				$zip->addFromString("$name_plate.csv", $contents);
			}
			header("Content-type: application/octet-stream");
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			header("Content-Disposition: attachment; filename=\"results.zip\"");
			header("Content-length: " . filesize($filename) . "\n\n");
			readfile($filename);
		} else if ($postFormat == 'json') {
			header("Content-Type: application/json");
			if (is_null($returnArray)) {
				echo "[]";
			} else {
				echo json_encode($returnArray);
			}
		} else if ($postFormat == 'html') {
			$flag = false;
			foreach($returnArray as $row) {
				if (is_array($row)) {
					if ((!$flag) && (is_array($row))) {
					# display field/column names as first row
						echo implode("\t", array_keys($row)) . "\n";
						$flag = true;
					}
					echo implode("\t", array_values($row)) . "\n";
				}
			}
		} else {
			echo "ERROR: no recognized format specified";
		}
}
?>
