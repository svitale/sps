<?php
lib('db');
class InventoryObject extends SpsObject {
    var $type = null;
    var $id_parent =  null;
    var $id_uuid =  null;
    var $position =  null;
    var $table =  'items';
    public function Fetcher() {
        global $dbrw;
        $sql = 'select id,id_study,type,id_uuid,id_batch,date_ship,date_visit,';
        $sql .= 'date_receipt,id_parent,id_subject,sample_type,shipment_type,id_visit,';
        foreach (get_object_vars($this) as $key=>$value) {
            if (isset($this->fields) && array_key_exists($key,$this->fields)) {
                $sql .= "`$key`,";
             }   
        }   
        $sql .= 'quant_init ';
        $sql .= ' from ' . $this->table .' ' ;
        if (isset($this->id_uuid)) {
            $id_uuid = $this->id_uuid;
            $sql .= "where id_uuid = '$id_uuid' ";
        } else if (isset($this->id)) {
            $id = $this->id;
            $sql .= "where id = '$id' ";
        } else {
            $this->error = 'Required Field: id_uuid not found';
            return false;
        }   
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
        echo 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
        }   
        $InventoryRecord = mysqli_fetch_object($result);
        foreach (get_object_vars($InventoryRecord) as $key=>$value) {
            $this->$key= $value;
        }   
    }   
    public function resolveUuid() {
	global $dbrw;
 	preg_match('/(\$)?([A-I]?[A-Z])(\$)?(\d+)/',$this->position,$match);
                // return absolute column if there is a $ in the ref
                $col_rel = empty($match[1]) ? 1 : 0; 
                $col_ref = $match[2];
                $row_rel = empty($match[3]) ? 1 : 0; 
                $row     = $match[4];

                // Convert base26 column string to a number.
                $expn   = strlen($col_ref) - 1; 
                $col    = 0; 
                $col_ref_length = strlen($col_ref);
                for ($i = 0; $i < $col_ref_length; ++$i) {
                        $col += (ord($col_ref{$i}) - 64) * pow(26, $expn);
                        --$expn;
                }    

                // Convert 1-index to zero-index
                $col;
                $row;
		$box_uuid = $this->container_uuid;
	$sql = "select items.id_uuid from ";
	$sql .= "(select id,id_uuid from items where id_uuid = '$box_uuid') box ";
	$sql .= "left join locations on locations.id_container  = box.id ";
	$sql .= "left join items on items.id = locations.id_item ";
	$sql .= "where subdiv4 = '$col' and subdiv5 = '$row'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
        echo 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
	}
        $record = mysqli_fetch_object($result);

	if (isset($record->id_uuid)) {
		$this->id_uuid = $record->id_uuid;
		return true;
	} else  {
		//checking locations moved for this one
		$sql = "select items.id_uuid from ";
		$sql .= "(select id,id_uuid from items where id_uuid = '$box_uuid') box ";
		$sql .= "left join locations_moved locations on locations.id_container  = box.id ";
		$sql .= "left join items on items.id = locations.id_item ";
		$sql .= "where subdiv4 = '$col' and subdiv5 = '$row'";
		$sql .= " order by locations.timestamp desc limit 1";
        	$result = mysqli_query($dbrw,$sql);
        	if (!$result) {
        	echo 'Could not run query: ' . mysqli_error($dbrw);
               	 return false;
               	 exit;
		}
        	$record = mysqli_fetch_object($result);
		if (isset($record->id_uuid)) {
			$this->id_uuid = $record->id_uuid;
			return true;
		} else  {
			return false;
		}
	}

    }
}
