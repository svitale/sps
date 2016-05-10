<?php
lib('dbi');
lib('Model/Inventory');
lib('Study');
class Process{
    var $container = null;
    var $daughter = null;
    var $process_name = null;
    var $process_id = null;
    var $process_description = null;
    var $tmptable = null;
    var $username = null;
    var $active_object = null;
    var $contentsArray = null;
    var $errorMsg = '';
    function __construct() {
        $this->active_object = New InventoryObject();
    }
    public function startTransaction() {
        global $dbrw;
        $result = mysqli_query($dbrw, "start transaction");
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }
        return true;
    }

    public function autoexecutor($behavior) {
	foreach ($behavior->settings as $setting) {
		if($setting['autoexecute'] ==1) {
			$name = $setting['function'];
			$this->tmptable = 'batch_quality';
			$this->$name();
		}
	}
    } 
   public function subDaughter() {
	//only execute this if a single microplate is scanned in
	if (isset($_SESSION['box_array'])) {
        	$box_array = $_SESSION['box_array'];
	} else {
		$box_array = array();
	}
// todo: display this message properly
	$daughter = null;
	if (count($box_array) == 1 && $box_array[0]['divX'] == '-8' && $box_array[0]['divY'] ==  '12') {
	print "<div class='alert-info'><b>Active Process:</b><i>Substitute Daughter</i></div>";
		$parent = $this->active_object;
		if ($parent->type == 'tube') {
			print "daughter:";
			$daughter_ids= $this->aliquotTube($parent,1);
			$daughter_id= array_pop($daughter_ids);
			if ($daughter_id > 0) {
				$daughter = new InventoryObject();
				$daughter->id = $daughter_id;
				$daughter->table = 'batch_quality';
				$daughter->Fetcher();
			} else {
				print "Error: could not create daughter aliquot";
				return false;
			}
		}
	}
	if ($daughter) {
		$this->active_object = $daughter;
	}

   }

    public function commitTransaction() {
        global $dbrw;
        $result = mysqli_query($dbrw, "commit");
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }
        return true;
    }

    public function rollbackTransaction() {
        global $dbrw;
        $result = mysqli_query($dbrw, "rollback");
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }
        return true;
    }


//TODO: Move this to REDcap Class
    /**
    * $sampledata has fields:
    *   'id_uuid','id_parent','id_subject', 'id_ancillary', id_study',
    *   'id_visit','id_alq','name_created','date_visit',
    *   'date_receipt','shipment_type','sample_type', 'id_collection'
    */
    public function createRedcapSample($sampledata) {
        global $dbrw;

        // error checking
        if ($this->tmptable == null) {
            $this->errorMsg = "class variable tmptable not set";
            return false;
        }
        // sample source is only valid for tissue
        //TODO: add ontology to identify tissue
        if (!$sampledata['sample_source'] || !preg_match('/Tis-/',$sampledata['sample_type'])) { 
            $sampledata['sample_source'] = '';
        }

        if ($sampledata['id_collection'] == null) {
            $this->errorMsg = "sample does not have a valid id_collection";
            return false;
        }

        if ($sampledata['id_study'] == null) {
            $this->errorMsg = "sample does not have a valid id_study";
            return false;
        }

        if ($sampledata['id_subject'] == null) {
            $this->errorMsg = "sample does not have a valid id_subject";
            return false;
        }
        /*
        $result = mysqli_query($dbrw, "select * from rc_cohort where id_collection = '$id_collection'");
        if ($result) {
            $row = mysql_fetch_array($result);
            $errorMsg = "collection already exists in rc_cohort"
        }
        */

        $id_subject = mysqli_real_escape_string($dbrw, $sampledata['id_subject']);
        $id_study = mysqli_real_escape_string($dbrw, $sampledata['id_study']);
        $id_collection = mysqli_real_escape_string($dbrw, $sampledata['id_collection']);

        // create the sample
        if (!$this->createParent($sampledata)) {
            return false;
        }

        // insert or update the data in the redcap cohort list
        $result = mysqli_query($dbrw, "select * from rc_cohort where rc_collection_id = '$id_collection'");
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }
        if (mysqli_num_rows($result) == 0) {
            $sql = "insert into `rc_cohort` (id_study, id_subject, rc_collection_id, time_created) 
            values('$id_study', '$id_subject', '$id_collection', NOW())";

            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
                return false;
            }
            return true;
        }
        else {
            $id = $row['id'];
            $sql = "update `rc_cohort` set 
            id_study='$id_study', id_subject='$id_subject', rc_collection_id='$id_collection', time_created=NOW() 
            where id = '$id'";

            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
                return false;
            }
            return true;
        }
    }

    //TODO: factor out items and batch_quality updates into InventoryObject Class
    public function updateStudyForSubjects($form_id_subject, $old_id_study, $new_id_study) {
        /*$sql = "update `rc_cohort` set 
        id_study='$new_id_study'
        where id_subject = '$form_id_subject' and id_study = '$old_id_study'";

        $this->errorMsg = $sql;
        return false;
*/
        global $dbrw;

        if ($form_id_subject == null) {
            $this->errorMsg = "id_subject not set";
            return false;
        }
         if ($old_id_study == null) {
            $this->errorMsg = "old_id_study not set";
            return false;
        }
         if ($new_id_study == null) {
            $this->errorMsg = "new_id_study not set";
            return false;
        }

        // update items
        $sql = "update `items` set 
        id_study='$new_id_study', `name_last_updated` = '" . $GLOBALS['sps']->username . "'
        where id_subject = '$form_id_subject' and id_study = '$old_id_study'";

        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }

        // update batch_quality
        $sql = "update `batch_quality` set 
        id_study='$new_id_study', name_created = '" .  $GLOBALS['sps']->username . "'
        where id_subject = '$form_id_subject' and id_study = '$old_id_study'";

        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }

        // update rc_cohort
        $sql = "update `rc_cohort` set 
        id_study='$new_id_study'
        where id_subject = '$form_id_subject' and id_study = '$old_id_study'";

        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
            return false;
        }
        return true;
    }

    //
    //
    // get the contents of the container call createItem for each
    // 
    public function aliquotBox() {
        if ($this->container != null) {
             $tubes = $this->retContentsArray();
             return $this->aliquotTubes($tubes);
        } else {
             return false;
        }

    }
    public function aliquotTube($parent,$num_daughters) {
        for ($i = 0; $i < ($num_daughters); ++$i) {
            $daughter = clone $parent;
            $daughter->id_uuid  = new_uuid();
            $daughter->id_parent = $parent->id_uuid;
            $daughter->name_created = $this->username;
            $id = $this->createDaughter($daughter);
            if ($id) {
                $ids[] = $id;
            } else {
                print "Error:  aliquotTube() couldn't create any daughters\n";
                return false;
            }
         } 
         if (count($ids) > 0) {
                return $ids;
         } else {
                print "Error:  aliquotTube() couldn't create any daughters\n";
                return false;
         }
    }
    //todo: clean up this mess!
    public function aliquotTubes($tubes) {
        $ids = array();
	foreach ($tubes as $parent) {
		    $num_daughters = $this->container['num_daughters'];
                    array($ids,$this->aliquotTube($parent,$num_daughters));
	}
        if (count($ids) > 0) {
	    return $ids;
        } else {
           return false;
        }
    }
    //
    //
    // get the defined process output for each sample and create it
    // 
    public function processContents() {
        $processArray = $this->retProcessArray();
        $processArray = $processArray[$this->process_name];
            foreach ($this->contentsArray as $record) {
                $this->logProcess($record->id_uuid);
            }
        if (isset($processArray['sample_type_output'])) {
            foreach ($this->contentsArray as $parent) {
                $sample_type_output_array = $processArray['sample_type_output'];
                if (!isset($processArray['sample_type_input']) || in_array($parent->sample_type,$processArray['sample_type_input'])) {
                    for ($i = 0; $i < ($this->container['num_daughters']); ++$i) {
                        foreach ($sample_type_output_array as $sample_type_output) {
                            $daughter = clone $parent;
                            $daughter->id_uuid  = new_uuid();
                            $daughter->sample_type = $sample_type_output;
                            $daughter->id_parent = $parent->id_uuid;
                            $daughter->name_created = $this->username;
                            $id = $this->createDaughter($daughter);
                            if ($id) {
                                $ids[] = $id;
                            } else {
                                return false;
                                exit;
                            }
                        }
                    }
                }
            }
            return $ids;
        } else {
            return true;
        }
    }


    public function processRecord() {
        $processArray = $this->retProcessArray();
        $processArray = $processArray[$this->process_name];
        if (isset($processArray['sample_type_output'])) {
		$parent = $this->record;
                $this->logProcess($parent->id_uuid);
                $sample_type_output_array = $processArray['sample_type_output'];
                if (!isset($processArray['sample_type_input']) || in_array($parent->sample_type,$processArray['sample_type_input'])) {
                    //for ($i = 0; $i < 4; ++$i) {
                        foreach ($sample_type_output_array as $sample_type_output) {
                            $daughter = clone $parent;
                            $daughter->id_uuid  = new_uuid();
                            $daughter->sample_type = $sample_type_output;
                            $daughter->id_study = $parent->id_study;
                            $daughter->id_parent = $parent->id_uuid;
                            $daughter->name_created = $this->username;
                            $id = $this->createDaughter($daughter);
                            if ($id) {
                                $ids[] = $id;
                            } else {
                                return false;
                                exit;
                            }
                        }
                //}
            }
            return $ids;
        } else {
            $id_uuid = $this->record->id_uuid;
            $this->logProcess($id_uuid);
            return $processArray;
        }
    }




    //TODO: Move to InventoryObject Class
    public function createParent($parent) {
        global $dbrw;
        $bq_fields = array(
            'id_uuid','id_parent','id_subject', 'id_ancillary', 'id_study',
            'id_visit','id_alq','name_created','date_visit','id_collection',
            'date_receipt','shipment_type','sample_type','sample_source'
            );
        $sqlFields = array();
        foreach ($bq_fields as $field) {
            if (isset($parent[$field])) {
                $safe_field = mysqli_real_escape_string($dbrw, $parent[$field]);
                $sqlFields[] = $field.' = \''.$safe_field.'\'';
            }
        }
        if (!isset($parent['name_created'])) {
            $sqlFields[] = "name_created = '" . $GLOBALS['sps']->username ."'";
        }

        if (count($sqlFields) > 0) {
            $sql = "insert into `$this->tmptable` set " . implode($sqlFields,",");
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);   
                return false;
            }
            return true;
        }
    }
    //
    //
    // inserting new records into tmptable
    //
    //TODO: Move to InventoryObject Class
    public function createDaughter($daughter) {
        global $dbrw,$sps;
        $bq_fields = array('id_uuid','id_parent','id_batch','id_subject','id_study','id_collection','id_ancillary','id_visit','id_alq','name_created','date_visit','date_ship','date_receipt','shipment_type','sequence','sample_type','sample_source','type','shipped','specnotavail','quality','status','family','copies','subdiv4','subdiv5','error_temp','error_label','error_volume','error_damage','error_delay','error_other');
        $sqlFields = array();
        foreach ($bq_fields as $field) {
            if (isset($daughter->$field)) {
                $sqlFields[] = $field.' = \''.$daughter->$field.'\'';
            }
        }
        if (count($sqlFields) > 0) {
            $sql = "insert into `$this->tmptable` set " . implode($sqlFields,",");
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->errorMsg = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
            } else {
            	return mysqli_insert_id($dbrw);
	    }
        } else {
		return false;
	}
    }
    //
    // return the contents of this item
    //
    //TODO: Move to InventoryObject Class
    public function retContentsArray() {
        global $dbrw;
        if ($this->container['type'] != 'box') {
 	        print "Error:  this function can only be performed on a box! \n";
        	exit;
        }
        $returnArray = array();
        $sql = 'select items.* from locations ';
        $sql .= 'left join items on items.id = locations.id_item ';
        $sql .= 'where id_container = '.$this->container['id'].' and date_moved is null ';
	    $sql .= ' order by subdiv4,subdiv5';
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            exit;
        }
        while ($row = mysqli_fetch_object($result)) {
            $returnArray[] = $row;
        }
        return $returnArray;
    }
    public function retValidProcessArray() {
           $sample_type = $this->active_object->sample_type;
           $allProcesses = $this->retProcessArray(); 
           $returnArray = array(); 
           foreach ($allProcesses as $process) { 
               if (!isset($process['sample_type_input']) || in_array($sample_type,$process['sample_type_input'])) {
                   $returnArray[] = $process;
               }
           }
           return $returnArray;
    }

    public function retProcessArray() {
        global $dbrw;
        $id_study = $GLOBALS['sps']->active_study->id_study; 
	$process_name = $this->process_name;
	$sql = 'select process_header.name,process_header.description,';
	$sql .= 'if(process_params.type="input",params.value,null) sample_type_input,';
	$sql .= 'if(process_params.type="output",params.value,null) sample_type_output ';
	$sql .= 'from process_header left join process_params on process_header.id = process_params.process_header_id ';
	$sql .= 'left join params on process_params.params_id = params.id ';
	$sql .= "where process_header.id_study = '$id_study'  ";
        if (!is_null($process_name)) {
	    $sql .= "and name = '$process_name'";
	}
        $sql .= "group by process_header.id,params_id,process_params.type";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            exit;
        }
        $returnArray = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['name'];
            if (!isset($returnArray[$name])) {
                $returnArray[$name] = array();
                $returnArray[$name]['process_name'] = $row['name'];
                $returnArray[$name]['process_description'] = $row['description'];
            }
            if(!is_null($row['sample_type_input'])){
                $returnArray[$name]['sample_type_input'][]  = $row['sample_type_input'];
            }
            if(!is_null($row['sample_type_output'])){
                $returnArray[$name]['sample_type_output'][]  = $row['sample_type_output'];
            }
        }
        return $returnArray;
    }

    /**
     * Set the consumed status of a tube in items
     * @param int $id
     * @param bool $value
     * @return bool
     */
    //TODO: Move to InventoryObject Class
     public function SetTubeConsumed($tubeid, $value = true) {
        global $dbi;
        if($value)
            $valStr = "true";
        else
            $valStr = "false";
            $sql = "update items set consumed = $valStr, `name_last_updated` = '" . $GLOBALS['sps']->username . "' where id = $tubeid and type = 'tube'";
        //    echo $sql;
            $result = mysqli_query($dbrw,$statement);
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
    //TODO: Move to InventoryObject Class
    public function SetBoxConsumed($boxid, $value = true) {
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
    //TODO: Move to InventoryObject Class
     public function SetTubeThawed($tubeid, $value = true) {
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
    //TODO: Move to InventoryObject Class
public function SetBoxThawed($boxid, $value = true) {
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
    //TODO: Move to InventoryObject Class
public function decrementBoxVolume($boxid, $variable) {
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
    //TODO: Move to InventoryObject Class
public function decrementTubeVolume($tubeid, $variable) {
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

    public function createProcess() {
        global $dbrw;
        $sql = "insert into process_header (id_study,name,description) ";
        $sql .= "values ('$this->id_study','$this->process_name'";
        $sql .= ",'$this->process_description')";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $message =  'Could not run query: ' . mysqli_error($dbrw);
            $this->logger($message);
            return false;
        } else {
             $this->process_id = mysqli_insert_id($dbrw);
             return true;
        }
    }
    public function logger($message) {
       lib('Logger');
       $logger = New Logger;
       $logger->message = $message;
       $logger->initialize();
    }
    public function retProcessedRecords() {
        return array();
    }
    public function deleteProcess() {
        global $dbrw;
	$processed = $this->retProcessedRecords();
	if (count($processed) > 0)  {
		$message = "Records have already been processed.  ";
                $message .= "Will not delete process $this->process_id.  ";
                $message .= "Deactiveate it instead.";
		$this->logger($message);
		return false;
		exit;
	}
        $sql = "delete from process_header ";
        $sql .= " where id = $this->process_id";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        } else {
             $this->process_id = mysqli_insert_id($dbrw);
             return true;
        }
    }
    public function deactivateProcess() {
        global $dbrw;
        if (!$this->process_id > 0) {
            $error = 'can not delete, process_id is null';
            $this->logger($error);
            return false;
            exit;
	}
        $sql = "update process_header set status = 'inactive' ";
        $sql .= " where id = $this->process_id";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $error = 'Could not run query: ' . mysqli_error($dbrw);
            $this->logger($error);
            return false;
            exit;
        } else {
             $this->process_id = mysqli_insert_id($dbrw);
             return true;
        }
    }
                                                                                         
    public function getProcessId() {
        global $dbrw;
        $sql = "select id from ";
        $sql .= "process_header where id_study = '".$GLOBALS['sps']->active_study->id_study."' ";
        $sql .= "and name = '$this->process_name'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $error =  'Could not run query: ' . mysqli_error($dbrw);
            $this->logger($error);
            return false;
        } else {
            while($row = mysqli_fetch_array($result)) {
                $this->process_id = $row['id'];
            }
           return true;
        }
    }
    public function retProcesslogArray($id_uuid) {
        global $dbrw;
        $process_array = array();
        $instance = array();
        $has_notes = false;
        $sql = "select process_log.id,process_log.timestamp,";
        $sql .= "process_header.id hid,process_header.id_study,process_header.name,";
        $sql .= "process_header.description,process_header.status process_status,";
        $sql .= "count(notes) num_notes from process_log left join process_header ";
        $sql .= "on process_log.id_process_header=process_header.id ";
        $sql .= "left join process_notes on process_notes.id_process_log=process_log.id ";
        $sql .= "where process_log.id_uuid = '$id_uuid'" ;
        $sql .= "group by process_log.id order by process_log.id desc";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        } else {
            while($row = mysqli_fetch_array($result)) {
                $event_id = $row['id'];
                $instance['timestamp'] = $row['timestamp'];
                $instance['header_id'] = $row['hid'];
                $instance['id_study'] = $row['id_study'];
                $instance['process_name'] = $row['name'];
                $instance['process_description'] = $row['description'];
                $instance['process_status'] = $row['process_status'];
                if ($row['num_notes'] > 0) {
                    $has_notes = true;
                    $instance['notes'] = array();
                } else {
                    $instance['notes'] = null;
                }
               $process_array[$event_id] = $instance;
            }
        }
        if ($has_notes) {
            $sql = "select process_log.id,notes,process_notes.timestamp from process_log ";
            $sql .= "left join process_notes on process_log.id = process_notes.id_process_log ";
            $sql .= "where process_log.id_uuid = '$id_uuid'" ;
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                print 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            } else {
                while($row = mysqli_fetch_array($result)) {
                    $event_id = $row['id'];
                    $notes = array();
                    $notes['text'] = $row['notes'];
                    $notes['timestamp'] = $row['timestamp'];
                    array_push($process_array[$event_id]['notes'],$notes);
                }
            }
        }
        return $process_array;
    }
    public function logProcess($id_uuid) {
        global $dbrw,$username;
        if (!isset($this->process_name) && !isset($GLOBALS['sps']->active_study->id_study)) {
            $message = 'Error: Attribute or Study missing';
            $this->logger($message);
            return false;
            exit;
	}
        if (!isset($this->process_id)) {
	   $this->getProcessId();
	}
//	$record = $this->record;
        $sql = "insert into process_log (id_uuid,id_process_header,username) values ";
        $sql .= "('$id_uuid',$this->process_id,'$username')";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        } else {
             return true;
        }
    }
}
