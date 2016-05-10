<?php
class ShipmentModel{
   /** 
   * ]
   */
	var $id = null;
	var $tracking_number = null;
	var $site = null;
	var $handler = null;
	var $delivery_date = null;
 public function create() {
	global $dbrw;
	$sql = "insert into tracking () values ()";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		$this->error = 'Could not run query: ' . mysqli_error($dbrw);
		return false;
		exit;
	}
	$this->id = mysqli_insert_id($dbrw);
 }
 public function modify() {
	global $dbrw;
	$sql_array = array();
	if ($this->site) {
		$sql_array[] = "site = '$this->site' ";
	}
	if ($this->handler) {
		$sql_array[] .= "handler = '$this->handler' ";
	}
	if ($this->tracking_number) {
		$sql_array[] .= "tracking_number = '$this->tracking_number' ";
	}
	if ($this->delivery_date) {
		$sql_array[] .= "delivery_date = '$this->delivery_date' ";
	}
	// we should have something to update with by now
	if (count($sql_array) > 0) {
		$sql = "update tracking set ";
		$sql .= implode($sql_array,",");
		$sql .= " where id = $this->id";
       		$result = mysqli_query($dbrw,$sql);
       		if (!$result) {
			$this->error = 'Could not run query: ' . mysqli_error($dbrw);
            		return false;
            		exit;
		}
	}
	return $this;
    }       
 public function fetch() {
 	global $dbrw;
	$success = false;
	if (!is_numeric($this->id)) {
            $this->error = 'object id was not provided';
		
	}
        $sql = "select id,site,tracking_number,handler,delivery_date from tracking ";
        $sql .= "where `id` = '$this->id'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }            
        while ($row = mysqli_fetch_assoc($result)) {
            $this->tracking_number= $row['tracking_number'];
            $this->site = $row['site'];
            $this->handler = $row['handler'];
            $this->delivery_date = $row['delivery_date'];
	    $success = true;
        }           
	return $success;
    }    
 public function delete() {
 	global $dbrw;
	$success = false;
	if (!is_numeric($this->id)) {
            $this->error = 'object id was not provided';
	}
        $sql = "delete from tracking ";
        $sql .= "where `id` = '$this->id'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }            
	return $success;
    }    
}
