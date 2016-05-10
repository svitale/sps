<?php
class Study {
 //   var $study = null;
    var $id_study = null;
    var $study_name = null;
    var $id_extdb_header = null;
    var $pi = null;
    var $autoassign_cohort = null;
    var $unique_linear_barcode = null;
    var $allow_linear = null;
    var $group_by = null;
    var $create_by = null;
 //   var $tracked_fields  = null;
// load the active study:  
// if there is one defined in the session variable, that is loaded
// if an id_study has been specified, read the study data from the database
    public function Loader() {
        global $dbrw;
	$sql = "select id,studies.id_study,study_name,id_extdb_header,pi,";
        $sql .= "autoassign_cohort,unique_linear_barcode,group_by from studies ";
        $sql .= "where id_study = '$this->id_study'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		return false;
	}
	$row = mysqli_fetch_array($result);
        if (!isset($row['id'])) {
            return false;
            exit;
        }
        $study['id_study'] = $row['id_study'];
        if ($row['id_extdb_header'] > 0) {
            $study['id_extdb_header'] = $row['id_extdb_header'];
        }
        //$study['create_by'] = $row['create_by'];
        $study['pi'] = $row['pi'];
        if ($row['autoassign_cohort'] > 0) {
            $study['autoassign'] = true;
        }
        $study['group_by'] = $row['group_by'];
        if ($row['unique_linear_barcode'] > 0) {
            $study['allow_linear'] = true;
        }
        foreach (get_object_vars($this) as $key=>$value) {
             if (isset($study[$key])) {
              $this->$key = $study[$key]; 
             }
        }
    }
}
