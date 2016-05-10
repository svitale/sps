<?php
global $template_dir;
lib('dbcleanup');
lib('ranges');
$template_dir = $GLOBALS['root_dir'] . '/www/files/Xlt/instruments';
class XltFormatter{
    var $rangeArray = null;
    var $project = null;
    var $plateMap = null;
    public function uuidFormat() {
	$found = false;
	$rangeArray = $this->rangeArray;
	foreach($rangeArray as $key=>$range) {
		foreach($range as $cell=>$val) {
			if ($val['label'] == 'id_uuid') {
				$uuidIn = $val['value'];
				if (!isUuid($uuidIn)) {
					$chunks = str_split(strtolower(str_replace("-", "",$uuidIn)),4);;
					$uuidOut = "$chunks[1]$chunks[2]-$chunks[3]-$chunks[4]-$chunks[5]-$chunks[6]-$chunks[7]$chunks[7]";
					if (isUuid($uuidOut)) {
						$rangeArray[$key][$cell]['value'] =  $uuidOut;
						$found = true;
					}
				} else {
					$found = true;
				}
			}
		}
	}
	
	$this->rangeArray = $rangeArray;
	return $found;
    }

    /*
    Description:  Given: result "r" with position_plate "PxPy" 
		  Given: parent_plate uuid 
		  Given: box(parent_plate) 
		  Given: item_uuid,
                  Given: formula f(r,Px,Px)->box,
		  to return  
    */ 
    public function setUuidsByPositions() {
        global $debug;
        $rangeArray = $this->rangeArray;
        $success = true; 
    // iterate over array and update results
    for($i=1; $i<count($rangeArray); $i++) {
        $result = $rangeArray[$i];
        if ($result['position_plate']) {
            $position_source = $this->mapSource($result['position_plate']);
	    if($position_source) {
            	$result['position_source'] = $position_source;
		$inventoryObject = new InventoryObject();
		$inventoryObject->type = 'tube';
		$inventoryObject->container_uuid = $result['barcode_source'];
		$inventoryObject->position = $result['position_source'];
		$inventoryObject->resolveUuid();
            	$result['id_uuid'] = $inventoryObject->id_uuid;
	    }
            $rangeArray[$i] = $result;
        } 
    }
    if($success) {
        $this->rangeArray = $rangeArray;
    }
    return $success;
    }
    public function mapSource($position_plate) {
	global $debug;
	if (!$this->plateMap) {
		// the map we'll create
		$plateMap = array();
		//the array we'll process from the file
		$map = array();
		$plateMapFile = $GLOBALS['root_dir'] . '/include/Plate/' .  $this->project . '.map';
		if (is_file($plateMapFile)) {
			$cols = null;
			$entries = array();
			$pf = fopen($plateMapFile, 'r');
			if (!$pf) {
				print "error: can't read map file";
				exit;
			}
			while (($buffer = fgets($pf, 4096)) !== false) {
				$map[] = str_replace(PHP_EOL,'',$buffer);
			}   
			$label = explode(",",array_shift($map));
			while ($line = array_shift($map)){
				$row = explode(",",$line);

				for ($i=0; $i<count($label); $i++) {
					$colname = $label[$i];
					$entry[$colname] = $row[$i];
				}
				$entries[] = $entry;
			}
			foreach ($entries as $entry) {
				if (isset($entry['position_plate']) && isset($entry['position_source'])) {
					$plateMap[$entry['position_plate']] = $entry['position_source'];
				}
			}
			$this->plateMap = $plateMap;
		}
	}
	$plateMap = $this->plateMap;
	if (isset($plateMap[$position_plate])) {
		return $plateMap[$position_plate];
	} else {
		return null;
	}
    }

}
?>
