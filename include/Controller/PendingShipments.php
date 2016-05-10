<?php
lib('Task');
class PendingShipments extends Task{
   /** 
   * Returns a crf object to guide the user to different actions
   * available on new inventory objects
   * return:
   *  [
   *  'error' => any errors encontered during use
   *  'batchid' => an id used to group batched inventory objects
   *  'active object' => [] the last selected inventory object
   * ]
   */
    var $limit = null;
    var $start = null;
    var $total = null;
    var $filters = null;
    var $fields = null;
    var $records = null;
    function __construct() {
        global $sps;
        $behavior = New Behavior();
        $this->start=0;
        $this->limit=3000;
        //$this->fields = $behavior->retFields($this->active_object);
	$fields = array();
	$fields['sample_type'] =  array(
		'comment'=>'Sample Type',
		'format'=>'ro',
		'id'=>'sample_type',
		'field'=>'sample_type');
	$fields['id_subject'] =  array(
		'comment'=>'Subject ID',
		'format'=>'ro',
		'id'=>'id_subject',
		'field'=>'id_subject');
	$fields['id_visit'] =  array(
		'comment'=>'Visit',
		'format'=>'ro',
		'id'=>'id_visit',
		'field'=>'id_visit');
	$fields['name_shipper'] =  array(
		'comment'=>'Shipped By',
		'format'=>'ro',
		'id'=>'name_shipper',
		'field'=>'name_shipper');
	$fields['name_created'] =  array(
		'comment'=>'Created By',
		'format'=>'ro',
		'id'=>'name_created',
		'field'=>'name_created');
         $this->filters = $sps->filters;
         if(isset($_SESSION['id_subject'])) {
             $this->filters['id_subject'] = $_SESSION['id_subject'];
         }
	$this->fields = $fields;
	$this->search_results = $this->fetchRecordSet();
    }
    public function fetchRecordSet() {
        global $dbrw,$sps;
        $this->records = array();
        if (!$this->filters || count($this->filters) < 1) {
             $this->error = "Error: Please provide more filters!\n";
             return false;
             exit;
        }
        foreach ($this->filters as $key=>$value) {
            if ($key == 'dateend') {
                $filterSqlArray[] = " `batch_quality`.`date_visit` <= '$value' ";
            } else if ($key == 'datestart') {
                $filterSqlArray[] = " `batch_quality`.`date_visit` >= '$value' ";
            } else {
                $filterSqlArray[] = " `batch_quality`.`$key` = '$value' ";
            }
            $filterSqlArray[] = " `batch_quality`.`date_receipt` is null ";
            $filterSqlArray[] = " `batch_quality`.`id_parent`  = 0";
        }
        $filterSql = implode($filterSqlArray," and  ");

        //
        if ($this->start || $this->limit) {
            if (!$this->start) {
                $this->start = 0;
            }
            if (!$this->limit) {
                $this->error = "Error: must specify limit when using offset!\n";
                return false;
                exit;
            } 
            $calcSql = " SQL_CALC_FOUND_ROWS ";
            $limitSql = " Limit $this->start,$this->limit ";
        } else {
            $calcSql = "";
            $limitSql = "";
        }
/*
$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];
if (strtotime($datestart) < strtotime($dateend)) {
	$date_query = " date_visit >= '$datestart' and date_visit <= '$dateend '";
} else {
	$date_query = " date_visit like '$datestart' ";
}
*/
	   $sql = "select $calcSql id,id_uuid,id_subject,id_visit,sample_type,date_visit,shipment_type,collection_time,id_barcode,treatment,sample_name,sample_identifier,sample_collos_id,date_ship,name_created,name_shipper,import_source from batch_quality where ";
/*
        foreach ($this->fields as $field) {
                $sql .= "`batch_quality`.`". $field['field'] . "`,";
	}
*/
        $sql .= implode($filterSqlArray," and  ");
        $sql .= $limitSql;
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }   
        while ($row = mysqli_fetch_assoc($result)) {
            $this->records[] = $row;
        }   
        $sql = "select FOUND_ROWS() total";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $this->total = $row['total'];
        }
        return true;
    }
}
?>
