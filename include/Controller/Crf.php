<?php
lib('Model/Inventory');
lib('Model/Inventory/Batch');
class Crf extends Task{
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
    var $batchid = null;
    var $batch_array = null;
    var $batch = null;
    var $active_object = null;
    var $xls_template_file = null;
    var $xls_template_path = null;
    // state: enum(null,stage,batch)
    var $state = null;


    function __construct() {
        global $sps,$config;
//        $this->active_object = new InventoryObjects();
        // calculated fields for the batch list view only
//        $BATCH_FIELDS = array('generation');
//        if (isset($_SESSION['active_object'])) {
//           $active_object = $_SESSION['active_object'];
//        } 
//        $behavior = New Behavior();
//        $behavior->active_object = $active_object;
//        $this->active_object = $active_object;
//        $this->tracked_fields = $behavior->retFields($active_object);
//        $this->tracked_fields = $behavior->retFields($active_object);
//        foreach ($BATCH_FIELDS as $batch_field) {
//            $this->tracked_fields[$batch_field] = array('type'=>'tube','format'=>'batch','options'=>null,'comment'=>$batch_field);
//        }
        if (isset($_SESSION['batchuuid'])) {
            $this->batchid = $_SESSION['batchuuid'];
        }     
        if (isset($_SESSION['crf_state'])) {
            $this->state = $_SESSION['crf_state'];
        }     
        $study = $sps->active_study;
        $template_dir = $config['root_dir'].'/www/files/Xlt/sampleimport';
        $template_path = $config['web_root'].'/files/Xlt/sampleimport';
        $custom_template = '/bystudy/'.$study->id_study.'.xlt';
        $generic_template = '/generic-crf.xlt';
        if (file_exists($template_dir . '/' .   $custom_template)) {
            $this->xls_template_file = $template_dir . $custom_template;
            $this->xls_template_path = $template_path . $custom_template;
        } else {
            $this->xls_template_file = $template_dir . $generic_template;
            $this->xls_template_path = $template_path . $generic_template;
        }
    }   
    function __destruct() {
        if (isset($this->batchid)) {
            $_SESSION['batchuuid'] = $this->batchid;
        }   
    // save the last state for resumption 
        if (isset($this->state)) {
            $_SESSION['crf_state'] = $this->state;
        } else if (isset($_SESSION['crf_state'])) {
            unset($_SESSION['crf_state']);
        }
    }
    public function fetchBatchObjects($id_subject=null,$sample_type=null) {
           $batchobjects = New InventoryBatchObjects();
//           $batchobjects->table = $this->table;
           $batchobjects->id_subject = $id_subject;
           $batchobjects->sample_type = $sample_type;
           if ($this->state == 'stage') {
               $batchobjects->type = 'staging';
           } else {
               $batchobjects->type = 'batch';
               $batchobjects->batchid = $this->batchid;
           }
           $batchobjects->records =  $batchobjects->getBatch();
           $this->batch = $batchobjects;
    }
    public function fetchBatchObjectsArray($id_subject=null,$sample_type=null) {
           $batchobjectsArray = New InventoryBatchObjects();
           $batchobjectsArray->batchid = $this->batchid;
           $batchobjectsArray->batch = $this->fetchBatchObjects($id_subject,$sample_type);
           $this->batch_array = $batchobjectsArray;
    }
    public function markReceived($active_object) {
        global $dbrw;
        if (isset($this->batchid) && isset($active_object)) {
            $sql = "update batch_quality set date_receipt = CURDATE() ";
            $sql .= "where id_uuid = '$active_object->id_uuid'";
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
        return true;
    }
// update the batch id for this object
    public function addToBatch($invobject) {
        global $dbrw;
        if (isset($this->batchid) && isset($invobject)) {
            $sql = "update batch_quality set id_batch = '$this->batchid' ";
            $sql .= "where id_uuid = '$invobject->id_uuid'";
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
        return true;
    }

    public function showcol($field, $val, $width, $changable) {
        $html = '<td width = 100>';
        $html .= '<div class="left_column" style="width:' . ($width * 10) . 'px">';
       $html .= retFieldcomment($field, 'batch_quality');
        $html .= "</div>";
        $html .= '</td>';
        return $html;
    }

    public function crfval($id, $field, $val, $width, $changable) {
        $html = '<td width = 100>';
	if ($changable == 'yes') {
		$html .= '<div class="left_column" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgreen">' . $val;
		$html .= "<script type='text/javascript'>
        new Ajax.InPlaceEditor('" . $field . "', 'npc.php?action=crfed',{formClassName: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&id=" . $id . "&field=" . $field . "'}})</script>";
		$html .=  "</div>";
	} else {
		$html .= '<div class="left_column" id="' . $field . '" style="width:' . ($width * 10) . 'px; background-color: lightgrey">' . $val;
		$html .=  "</div>";
	}
	$html .= '</td>';
        return $html;
}

        public function crfDetail($row) {
		$date_collection = $row->date_collection;
		$date_visit = $row->date_visit;
		$date_ship = $row->date_ship;
		$date_receipt = $row->date_receipt;
		$id_parent = $row->id_parent;
		$id_subject = $row->id_subject;
		$sample_type = $row->sample_type;
		$sample_name = $row->sample_name;
		$sample_collos_id = $row->sample_collos_id; 
		$sample_identifier = $row->sample_identifier;
		$shipment_type = $row->shipment_type;
		$collection_time = $row->collection_time;
		$treatment = $row->treatment;
		$id_visit = $row->id_visit;
		$quality = $row->quality;
		$id = $row->id;
		$name_shipper = $row->name_shipper;
		if ($row->notes != '') {
			$notes = $row->notes;
		} else {
			$notes = '--';
		}
		if ($row->quant_init > 0) {
			$quant_init = $row->quant_init;
		} else {
			$quant_init = "0";
		}
		$html = '<div style="width:1200px; background-color: lightgrey";>';
		$html = "</div>";
		$html .=  "<table>";
		$html .=  "<tr>";
		$html .= $this->showcol('id_subject', $id_subject, '9', 'no');
		$html .= $this->showcol('id_visit', $id_visit, '7', 'no');
		$html .= $this->showcol('date_collection', $date_collection, '9', 'yes');
		$html .= $this->showcol('date_visit', $date_visit, '9', 'yes');
		$html .= $this->showcol('date_ship', $date_ship, '9', 'no');
		$html .= $this->showcol('date_receipt', $date_receipt, '9', 'no');
		$html .= $this->showcol('sample_type', $sample_type, '7', 'no');
		$html .= $this->showcol('sample_name', $sample_name, '10', 'no');
		$html .= $this->showcol('sample_collos_id', $sample_collos_id, '10', 'no');
		$html .= $this->showcol('sample_identifier', $sample_identifier, '12', 'no');
		$html .= $this->showcol('shipment_type', $shipment_type, '10', 'no');
		$html .= $this->showcol('collection_time', $collection_time, '10', 'no');
		$html .= $this->showcol('treatment', $treatment, '10', 'no');
		$html .= $this->showcol('quality', $quality, '9', 'yes');
		$html .= $this->showcol('quant_init', $quant_init, '7', 'yes');
		$html .= $this->showcol('name_shipper', $name_shipper, '9', 'no');
		$html .= $this->showcol('notes', $notes, '14', 'no');
		$html .= "</tr>";
		$html .= "<tr>";
		$html .= $this->crfval($id, 'id_subject', $id_subject, '9', 'no');
		$html .= $this->crfval($id, 'id_visit', $id_visit, '7', 'no');
		$html .= $this->crfval($id, 'date_collection', $date_collection, '9', 'yes');
		$html .= $this->crfval($id, 'date_visit', $date_visit, '9', 'yes');
		$html .= $this->crfval($id, 'date_ship', $date_ship, '9', 'no');
		$html .= $this->crfval($id, 'date_receipt', $date_receipt, '9', 'no');
		$html .= $this->crfval($id, 'sample_type', $sample_type, '7', 'yes');
		$html .= $this->crfval($id, 'sample_name', $sample_name, '10', 'yes');
		$html .= $this->crfval($id, 'sample_collos_id', $sample_collos_id, '10', 'yes');
		$html .= $this->crfval($id, 'sample_identifier', $sample_identifier, '12', 'yes');
		$html .= $this->crfval($id, 'shipment_type', $shipment_type, '10', 'yes');
		$html .= $this->crfval($id, 'collection_time', $collection_time, '10', 'yes');
		$html .= $this->crfval($id, 'treatment', $treatment, '10', 'yes');
		$html .= $this->crfval($id, 'quality', $quality, '2', 'yes');
		$html .= $this->crfval($id, 'quant_init', $quant_init, '7', 'yes');
		$html .= $this->crfval($id, 'name_shipper', $name_shipper, '5', 'no');
		$html .= $this->crfval($id, 'notes', $notes, '14', 'yes');
		$html .= "</tr>";
		$html .= "</table>";
		$html .= '</div>';
/*
	if ($date_receipt != date("Y-m-d")) {
		$html .=  '<script type="text/javascript">';
		$html .=  "alert('Note: This sample was scanned on a previous date');";
		$html .=  "</script>";
	}
*/
            return $html;
}

    public function returnDistinctMembersInBatch($field='id_subject') {
        global $dbrw,$study;
        $t = $this->table;
        $batchid = $this->batchid;
        $sql = "select distinct(`$field`) as members from `$t` ";
        $sql .= "where `id_batch` = '$batchid'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_array($result)) {
            $returnArray[] = $row['members'];
        }
        if (isset($returnArray)) {
            return $returnArray;
        } else {
            return false;
        }
    }

    public function importBatch($tmptable) {
        $sql = "update `$tmptable` set id_batch = '".$this->batchid."'";
        $result = mysql_query($sql);
        if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }

        $update = mysql_query("update `$tmptable` set id = id + (select max(id) from batch_quality);");
        if (!$update) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        }
        $query = "insert into batch_quality select * from `$tmptable`";
        $result = mysql_query($query);
        if (!$result) {
                echo 'Could not run query: ' . mysql_error();
                exit;
        } else {
        //  create a temporary cohort list containing all of the subject ids for studies that allow autoincremnting subject ids.  then add them to the real cohort list
        $tmpcohort_table = $tmptable.'_cohort';
        $tmpcohort_sql = "create temporary table `$tmpcohort_table`";
        $tmpcohort_sql .= " as select `$tmptable`.id_subject,`$tmptable`.id_study from `$tmptable`";
        $tmpcohort_sql .= " left join cohort on `$tmptable`.id_subject = cohort.id_subject";
        $tmpcohort_sql .= " and `$tmptable`.id_study = cohort.id_study";
        $tmpcohort_sql .= " left join studies on `$tmptable`.id_study = studies.id_study";
        $tmpcohort_sql .= " where cohort.id is null and studies.autoassign_cohort = 1";
        $tmpcohort_sql .= " group by `$tmptable`.id_subject,`$tmptable`.id_study;";
        $tmpcohort_result = mysql_query($tmpcohort_sql);
        if (!$tmpcohort_result) {
            'Could not run query: ' . mysql_error();
            exit;
        }
        $tmpcohort_num_rows = mysql_affected_rows();
        if ($tmpcohort_num_rows > 0) {
            $cohort_sql = "insert into cohort (id_subject,id_study) (select id_subject,id_study from `$tmpcohort_table`)";
            $cohort_result = mysql_query($cohort_sql);
            if (!$cohort_result) {
                'Could not run query: ' . mysql_error();
                exit;
            }
            $cohort_num_rows = mysql_affected_rows();
            if ($cohort_num_rows < 1) {
                'Could not add subjects to cohort list';
                exit;
            }
        }
        }
           mysql_query("drop table `$tmptable`");
           unset($_SESSION['tmptable']);
           $this->tmptable = null;
           return $this;
       }
}
