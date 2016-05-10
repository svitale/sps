<?php
lib('Task');
class InventorySearch extends Task{
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
        $this->limit=100;
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
        if (!$this->filters || count($this->filters) < 3) {
             $this->error = "Please select some filters\n";
             return false;
             exit;
        }
        foreach ($this->filters as $key=>$value) {
                $filterSqlArray[] = " `items`.`$key` = '$value' ";
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
        $sql = "select $calcSql `items`.`id`,`items`.`id_study`,`items`.`id_uuid`,`items`.`id_parent`,";
        $sql .= "`items`.`id_subject`,`items`.`date_visit`,`items`.`date_receipt`,";
//        foreach (get_object_vars($this) as $key=>$value) {
   //         if ($this->fields && array_key_exists($key,$this->fields)) {
        foreach ($this->fields as $field) {
                $sql .= "`items`.`". $field['field'] . "`,";
	}
 //            }
  //      }
        $sql .= "`locations`.`freezer`,`locations`.`subdiv1`,`locations`.`subdiv2`,";
        $sql .= "`locations`.`subdiv3`,`locations`.`subdiv4`,`locations`.`subdiv5`,";
	$sql .= "`boxes`.`id_uuid` `box_uuid` ";
        $sql .= "from items left join locations on items.id = locations.id_item ";
        $sql .= "left join items ";
	$sql .= " boxes  on locations.id_container = boxes.id ";
        $sql .= "where `items`.`type` = 'tube' and `locations`.`date_moved` is null and ";
//        $sql .= "`locations`.`id_site` = '1' and  ";
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
