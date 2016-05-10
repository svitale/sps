<?php
/*****
 * Represents the information in a pull
 ****/

class Pulls {
    /**
     * @return array [pull_header_id, pull_name, pull_description, group_name, pull_group_id]
     */
    public function GetActivePulls($id_study) {
        if ($id_study == "any") {
            $pullHeaderResult = mysql_query("select pull_header.id as pull_header_id, pull_header.pull_name,
            pull_header.pull_description, pull_group.group_name, pull_group.id as pull_group_id from pull_header
            left join pull_header_group on pull_header.id = pull_header_group.id_pull_header
            left join pull_group on pull_group.id = pull_header_group.id_pull_group
            where (pull_header.pull_status = 'active' or pull_header.pull_status = 'ongoing')
            order by pull_group.group_name, pull_header.id");
        }
        else {
            $pullHeaderResult = mysql_query("select pull_header.id as pull_header_id, pull_header.pull_name,
            pull_header.pull_description, pull_group.group_name, pull_group.id as pull_group_id from pull_header
            left join pull_header_group on pull_header.id = pull_header_group.id_pull_header
            left join pull_group on pull_group.id = pull_header_group.id_pull_group
            where (pull_header.pull_status = 'active' or pull_header.pull_status = 'ongoing')
            and pull_header.id_study_value = '$id_study'
            order by pull_group.group_name, pull_header.id"); 
        }
        
        if(!$pullHeaderResult)
            return null;
        
        $returnArray = array();
        
        while($returnArray[] = mysql_fetch_array($pullHeaderResult)) {
            ;
        }
        return $returnArray;
    }
    public function GetAllPulls() {
        $pullHeaderResult = mysql_query("select pull_header.id as pull_header_id, pull_header.pull_name,
            pull_header.pull_description, pull_group.group_name,
            pull_header.pull_status,
            pull_group.id as pull_group_id from pull_header
            left join pull_header_group on pull_header.id = pull_header_group.id_pull_header
            left join pull_group on pull_group.id = pull_header_group.id_pull_group
            order by pull_group.group_name, pull_header.id");
        //TODO
    }
    public function GetPullGroups() {
        // TODO
    }
    
    /**
     * int pullId
     * int groupId: -1 to remove from group
     */
    public function AddPullToGroup(int $pullId, int $groupId) {
        // TODO
    }
    
    public function CreateGroup(String $name) {
        // TODO
        return -1;
    }
}
class Pull {
    //direct string matches; $field_type_status, $field_type_value
    private $fieldNames = array("id_subject", "sample_type", "shipment_type", "id_study", "date_visit", "id_visit", "quant_thaws", "quant_cur", "alq_num", "id_uuid");
    //private $fieldNames = array("id_subject", "sample_type", "shipment_type", "id_study", "date_visit", "id_visit", "quant_thaws", "alq_num", "id_uuid");
    //date ranges; $field_type_status, $field_type_value, $field_type_range
    private $dateRangeFields = array("date_visit");
    //int ranges; $field_type_status, $field_type_value, $field_max
    private $intRangeFields = array("quant_thaws");
    //multiple values; $field_type_status, $field_type_value, $field_type_value2
    private $multipleValueFields = array("sample_type");
    //data stored as a varchar, but we want to evaluate it as a double and check that it's in range; $field_type_status, $field_type_value, $field_max
    private $toDecimalRangeFields = array("quant_cur");
    
    //fields having to do with the destination box
    private $destinationFieldNames = array("box_destination");
    
    // name of the fields in the view VwItemsAndLocations
    private $viewFieldNames = array("consumed"=>"consumed", "id_subject"=>"items_id_subject", "sample_type"=>"items_sample_type", "shipment_type"=>"items_shipment_type", "id_study"=>"items_id_study", "date_visit"=>"items_date_visit", "id_visit"=>"items_id_visit", "id_uuid"=>"items_id_uuid");
    
    private $isSet = false;
    private $id_pull;
    private $pull_name;
    private $pull_description;
    private $tubes_per_request;
    private $fieldData = array(); // associative array keyed by field name, indexed by 'status' and 'value'
    private $destinationFieldData = array(); // associative array keyed by field name, indexed by 'status' and 'value'
    
    public function __construct($id_pull) {
        return $this->UpdateFromDB($id_pull);
    }
    
    public function UpdateIdPull($id_pull) {
        if ($id_pull != $this->id_pull)
            return $this->UpdateFromDB($id_pull);
        return true;
    }
    
    /**
     *
     */
    private function UpdateFromDB($id_pull) {
        //get the status of the fields from the header
       $pullFieldsString = "pull_name, pull_description, tubes_per_request";
       foreach ($this->fieldNames as $field) {
            //see which fields names are expected in pull_header
           $pullFieldsString .= ", " . $field . "_status";
           $pullFieldsString .= ", " . $field . "_value";
           if (in_array($field, $this->fieldNames)) {
                //nothing new
           }
           if (in_array($field, $this->intRangeFields)) {
                $pullFieldsString .= ", " . $field . "_max";
           }
           if (in_array($field, $this->toDecimalRangeFields)) {
                $pullFieldsString .= ", " . $field . "_max";
           }
           if (in_array($field, $this->dateRangeFields)) {
                $pullFieldsString .= ", " . $field . "_range";
           }
            if (in_array($field, $this->multipleValueFields)) {
                $pullFieldsString .= ", " . $field . "_value2";
            }
       }

       foreach ($this->destinationFieldNames as $field) {
            //see which fields names are expected in pull_header
           $pullFieldsString .= ", " . $field . "_status";
           $pullFieldsString .= ", " . $field . "_value";
       }
       
       $query = "select $pullFieldsString from pull_header where id = $id_pull";
       
       $result = mysql_query($query);
       if (!$result) {
            $this->isSet = false;
            return false;
       }
       
       if (mysql_affected_rows() == 0) {
            $this->isSet = false;
           return false;
       }
       
       
       $row = mysql_fetch_assoc($result);
       $this->pull_name = $row['pull_name'];
       $this->pull_description = $row['pull_description'];
       $this->tubes_per_request = $row['tubes_per_request'];
       foreach ($this->fieldNames as $field) {
           $this->fieldData[$field]['status'] = $row[$field.'_status'];
           $this->fieldData[$field]['value'] = $row[$field.'_value'];
           if (in_array($field, $this->dateRangeFields)) {
                $this->fieldData[$field]['range'] = $row[$field.'_range'];
           }
           if (in_array($field, $this->multipleValueFields) and strlen(trim($row[$field.'_value2'])) > 0) {
                $this->fieldData[$field]['value2'] = $row[$field.'_value2'];
            }
            if (in_array($field, $this->intRangeFields)) {
                $this->fieldData[$field]['max'] = $row[$field.'_max'];
                $pullFieldsString .= ", " . $field . "_max";
           }
            if (in_array($field, $this->toDecimalRangeFields)) {
                $this->fieldData[$field]['max'] = $row[$field.'_max'];
                $pullFieldsString .= ", " . $field . "_max";
           }
       }
       foreach ($this->destinationFieldNames as $field) {
           $this->destinationFieldData[$field]['status'] = $row[$field.'_status'];
           $this->destinationFieldData[$field]['value'] = $row[$field.'_value'];
       }
       
       $this->id_pull = $id_pull;
       $this->isSet = true;
       return true;
    }
    
    public function GetIdPull() {
        return $this->id_pull;
    }
    public function GetPullName() {
        return $this->pull_name;
    }
    public function GetPullDescription() {
        return $this->pull_description;
    }
    public function GetFieldData() {
        return $this->fieldData;
    }
    public function GetDestinationFieldData() {
        return $this->destinationFieldData;
    }
    public function IsPullSet() {
        return $this->isSet;
    }
    
    public function GetFieldNames($status = 'all') {
        $retArray = array();
        foreach ($this->fieldNames as $field) {
            if (($status == 'all' ) || ($this->fieldData[$field]['status'] == $status))
                $retArray[] = $field;
        }
        return $retArray;
    }

    public function GetDestinationFieldNames($status = 'all') {
        $retArray = array();
        foreach ($this->destinationFieldNames as $field) {
            if (($status == 'all' ) || ($this->destinationFieldData[$field]['status'] == $status))
                $retArray[] = $field;
        }
        return $retArray;
    }
    
    public function GetRequestCount() {
        $query = "select count(*) as cnt from pull_requirements where id_pull_header = " . $this->id_pull;
        $result = mysql_query($query);
        if((!$result) || (mysql_affected_rows() < 1)){
            return -1;
        }
        $row = mysql_fetch_array($result);
        return $row['cnt'];        
    }
    
    public function GetUnfufilledRequestCount() {
        $query = "select count(*) as cnt from pull_requirements
        where id_pull_header = " . $this->id_pull . " and fufilled=0";
        $result = mysql_query($query);
        if((!$result) || (mysql_affected_rows() < 1)){
            return -1;
        }
        $row = mysql_fetch_array($result);
        return $row['cnt']; 
    }
    
    public function GetScannedCount() {
        $query = "select count(*) as cnt from pull_specimens
        inner join pull_requirements on pull_specimens.id_pull_requirements = pull_requirements.id
        where id_pull_header = " . $this->id_pull;
        $result = mysql_query($query);
        if((!$result) || (mysql_affected_rows() < 1)){
            return -1;
        }
        $row = mysql_fetch_array($result);
        return $row['cnt']; 
    }

    /**
    * If box destinations have been designated by the pull, return them.  Otherwise return the default destinations.
    */
    public function GetAllowedBoxDestinations() {

        if ($this->destinationFieldData['box_destination']['status'] == 'unused')
            return array('test-01', 'test-02', 'test-03', 'biomek', 'Pulled', 'BRB');

        else if ($this->destinationFieldData['box_destination']['status'] == 'staic')
            return array($destinationFieldData['box_destination']['value']);

        else if ($this->destinationFieldData['box_destination']['status'] == 'dynamic') {
            $query = "select distinct box_destination from pull_requirements
            where id_pull_header = " . $this->id_pull;
            $result = mysql_query($query);
            if((!$result) || (mysql_affected_rows() < 1)){
                return array("Get box dest. SQL Error: " . mysql_error());
            }

            $dests = array();
            
            while($row = mysql_fetch_row($result)) {
                $dests[] = $row[0];
            }
            return $dests;
        }
        else
            return array();
    }
    
    /**
    *Write to the table gwas_log
    * @param string $uuid
    * @param string $id
    * @param string $type
    * @param string $labtech - user currently signed in
    * @param string $response - the result of the scan
    * @param string $comment
    * @param string $source_id_container
    * @param string $dest_id_container
    * @param string $id_pull_requirements
    *
    * @return bool
    * */
   function InsertPullLog($uuid, $id,  $type, $labtech, $response, $comment, $source_id_container, $dest_id_container, $id_pull_requirements = ''){
       $response = mysql_real_escape_string($response);
       $comment = mysql_real_escape_string($comment);
       $query = "INSERT INTO pull_log (id_uuid, item_id, labtech, scan_date, item_type, response, comment, source_id_container, dest_id_container, id_pull_header, id_pull_requirements)
           values ('$uuid', '$id', '$labtech', NOW(), '$type', '$response', '$comment', '$source_id_container', '$dest_id_container', '" . $this->id_pull. "', '$id_pull_requirements')";
       $result = mysql_query($query);
       
       if (!$result) {
           echo 'Could not run query: ' . mysql_error();
           return false;
       }
       return true;
   }
    
    /**
     * Checks to see if tube fufills one of the pull requirements
     * @param integer $id_tube
     * @return string ['ok'|error message]
     *
     * Return Values:
     *  Tube already scanned - tube has been scanned into any active pull
     *  Requirement already fufilled
     *  Tube not on list
     *  Tube belongs to a locked cohort
     *  No study has been defined for the pull
     *  SQL error
     */
    public function ErrorCheckTubeID($id_tube, $dest_box_id) {
        // scanned into this pull
        /*$result = mysql_query("select pull_specimens.id
                              from pull_specimens inner join pull_requirements
                              on pull_specimens.id_pull_requirements = pull_requirements.id
                              where pull_requirements.id_pull_header = " . $this->id_pull . " and pull_specimens.id_item = '" . $id_tube. "'");*/
        
        // scanned into an active pull
        $result = mysql_query("select pull_specimens.id, pull_header.pull_name as pull_name
                              from pull_specimens inner join pull_requirements
                              on pull_specimens.id_pull_requirements = pull_requirements.id
                              inner join pull_header
                              on pull_header.id = pull_requirements.id_pull_header
                              where pull_header.pull_status = 'active' and pull_specimens.id_item = '" . $id_tube. "'");
        
        if(!$result){
            return "ErrorCheckTubeID1 SQL Error: " . mysql_error();
        }
        if (mysql_affected_rows() > 0) {
            if ($row = mysql_fetch_array($result)) {
                $pull_name = $row['pull_name'];
                return "Tube already scanned into an active pull: $pull_name";
            }
            else {
                return "Tube already scanned into an active pull";
            }
        }

        // check if tube is a specimen of a locked cohort
        $cohortMsg = $this->IsTubeCohortNotLocked($id_tube);
        if($cohortMsg != 'ok') {
            return $cohortMsg;
        }

        // check if this tube has already been pulled
        $qryWhere = $this->GetWhereStatement('items', false, false);
        $qryJoin = $this->GetJoinStatement('items', false);
        $qryDateSelect = $this->GetDateRangeSelect();
        $query = "select pull_requirements.id, pull_requirements.fufilled,
            $qryDateSelect as dateRangeError
            from pull_requirements inner join items on $qryJoin
            where items.id = $id_tube and $qryWhere";
            
        //echo "<br/>$query<br/>";
        //return "debug";
        
        $result = mysql_query($query);
        if(!$result){
            return "ErrorCheckTubeID2 SQL Error: " . mysql_error();
        }
        if (mysql_affected_rows() == 0) {
            return "Tube not on list";
        }
        
        // see if there is at least one required tube that is not fufilled
        $notFufilled = false;
        while($row = mysql_fetch_array($result)) {
            $notFufilled = ($notFufilled or ($row['fufilled'] == 0));
            $id_pull_requirement = $row['id'];

            if ($row['dateRangeError']) {
                return "Date out of range";
            }
            $boxValidation = $this->ValidateBoxDestination($id_pull_requirement, $dest_box_id);
            if ($boxValidation != "ok") {
                return $boxValidation;
            }
        }
        
        if ($notFufilled) {
            return 'ok';
        }
        else {
            return "Requirement already fufilled";
        }
    }


    private function ValidateBoxDestination($id_pull_requirement, $dest_box_id) {
        $boxdest = $this->GetBoxDestination($dest_box_id);
        if ($this->destinationFieldData['box_destination']['status'] == 'unused' || $this->destinationFieldData['box_destination']['status'] == 'static') {
            $allowedBoxdest = $this->GetAllowedBoxDestinations();
            if ((!$boxdest) || (!in_array($boxdest, $allowedBoxdest))) {
                $allowedDestString = implode(",", $allowedBoxdest);
                return "Box destination incorrect: '$boxdest' must be one of: $allowedDestString.";
            }
        }
        if ($this->destinationFieldData['box_destination']['status'] == 'dynamic') {
            $query = "select box_destination from pull_requirements where id_pull_header = " . $this->id_pull . " and id = $id_pull_requirement";
            $result = mysql_query($query);
            if((!$result) || (mysql_affected_rows() < 1)){
                return "ValidateBoxDest SQL Error: " . mysql_error();
            }
            $row = mysql_fetch_array($result);
            $expectedDest = $row['box_destination'];
            if ($expectedDest != $boxdest) {
                return "The this tube matches requirement $id_pull_requirement.  It expects a box with a destination of '$expectedDest' but found '$boxdest'.";
            }
        }

        return "ok";
    }

    private function GetBoxDestination($boxid) {
        $query = "select destination from items where id = '$boxid' and type = 'box'";
        $result = mysql_query($query);
        if(!$result){
            echo "GetBoxDest SQL Error: " . mysql_error();
            return -1;
        }
        if(mysql_affected_rows() < 1){
            echo "No matching box found for id $boxid: " . mysql_error();
            return -1;
        }
        $row = mysql_fetch_array($result);
        return $row['destination'];
    }

    /**
    * Return 'ok' if the tube this cohort belongs to is not locked
    * Return a message if the cohort is locked or there is an error
    */
    public function IsTubeCohortNotLocked($id_tube) {
        // check if tube is a specimen of a locked cohort
        $result = mysql_query("select id_subject, id_study from items where id = '$id_tube'");
        if(!$result) {
            return "SQL Error: " . mysql_error();
        }
        else if  (mysql_affected_rows() < 1) {
            return "Could not identify tube id $id_tube";
        }
        $row = mysql_fetch_array($result);

        $id_study = $row[id_study];
        $id_subject = $row[id_subject];

        if (!$id_study) {
            return "No study has been defined for this pull, please specify a study";
        }
        else if (!$id_subject) {
            return "Unable to find subject_id for id '$id_tube'";
        }

        $result = mysql_query("select * from cohort where cohort_lock = 1 and id_study = '$id_study' and id_subject='$id_subject'");
        if(!$result){
            return "SQL Error: " . mysql_error();
        }
        if (mysql_affected_rows() > 0) {
            return "Cohort '$id_subject' for study '$id_study' has been locked and cannot be pulled";
        }
        return "ok";
    }

    /**
     * Try to pull the selected tube.
     * Check to make sure the tube fufills the pull requirements
     * Update pull_requirements with the tube
     * Check to see if requirement is fufilled, if so update pull_requirements
     * @param bool $transaction_flag=false should the update statements use transactions?
     *
     * @return array (string ['ok' | error_msg], int id_pull_requirements)
     */
    public function PullTube($id_tube, $id_uuid, $scanned_by, $source_box_id, $source_subdiv4, $source_subdiv5, $dest_box_id, $dest_subdiv4, $dest_subdiv_5, $transaction_flag = false) {
        // make sure input parameters are ok
        if(strlen($id_tube) > 8)
            return array('status'=>"Invalid tube id: $id_tube");
        if(strlen($id_uuid) < 8)
            return array('status'=>"Invalid UUID: $id_uuid");
       
        //error check tube
        $errorMsg = $this->ErrorCheckTubeID($id_tube, $dest_box_id);
        if($errorMsg != 'ok') {
            return array('status' => $errorMsg);
        }

        // figure out which requirement this tube would fufill
        $qryWhere = $this->GetWhereStatement();
        $qryJoin = $this->GetJoinStatement();
        $query = "select pull_requirements.id
            from pull_requirements inner join items on $qryJoin
            where items.id = $id_tube and $qryWhere";
                
        $requestData = mysql_query($query);
        if(!$requestData){
            return array('status'=>"Query error:" + mysql_error());
        }
    
        if(mysql_num_rows($requestData) == 0) {
            return array('status'=>"This tube does not match any requirements.");
        }
        else {
            $requestResult = mysql_fetch_array($requestData);
            $id_pull_requirements = $requestResult['id'];
        }
        
        $boxValidation = $this->ValidateBoxDestination($id_pull_requirements, $dest_box_id);
        if ($boxValidation != "ok") {
            return array('status'=>$boxValidation);
        }

        if($transaction_flag) mysql_query("transaction start");
        
        // insert into pull specimens
        $query = "insert into pull_specimens
            (id_pull_requirements, id_uuid, id_item, scan_date, scanned_by, source_box_id, source_box_subdiv4, source_box_subdiv5, dest_box_id, dest_box_subdiv4, dest_box_subdiv5)
            values ($id_pull_requirements, '$id_uuid', $id_tube, NOW(), '$scanned_by', $source_box_id, '$source_subdiv4', '$source_subdiv5', '$dest_box_id', '$dest_subdiv4', '$dest_subdiv_5')";
        if(!mysql_query($query)) {
            $errorMsg = mysql_error();
            if($transaction_flag) mysql_query("rollback");
            return array('status'=>"sql error: " + $query);
        }
        
        // update pull_requirements if requirement has been fuflled
        if($this->tubes_per_request > 1) {
            // check to see if fufilled requirement has been met
            $query = "select count(*) as cnt from pull_specimens where id_pull_requirements = $id_pull_requirements";
            $result = mysql_query($query);
            if(!$result) {
                    $errorMsg = mysql_error();
                    if($transaction_flag)  mysql_query("rollback");
                    return array('status'=>$errorMsg);
            }
            $row = mysql_fetch_assoc($result);
            $count = $row['cnt'];
            
            if($count >= $this->tubes_per_request) {
                $query = "update pull_requirements set fufilled = 1 where pull_requirements.id = $id_pull_requirements";
                if(!mysql_query($query)) {
                    $errorMsg = mysql_error();
                    if($transaction_flag)  mysql_query("rollback");
                    return array('status'=>$errorMsg);
                }
            }
        }
        else {
            $query = "update pull_requirements set fufilled = 1 where pull_requirements.id = $id_pull_requirements";
            if(!mysql_query($query)) {
                $errorMsg = mysql_error();
                if($transaction_flag)  mysql_query("rollback");
                return array('status'=>$errorMsg);
            }
        }
        if($transaction_flag) mysql_query("commit");
        
        return array('status'=>'ok', 'id_pull_requirements'=>$id_pull_requirements);
    }
    
    /**
     * return a comma seperated string of the fields with a given status
     * @param string $status 'any'
     * @param string $prepend 'pull_requirements'
     * @param bool $useAltFieldNames false
     * @return string
     */
    private function GetCSFieldsString($status = 'any', $prepend = 'pull_requirements.', $useAltFieldNames = false) {
        $fieldString = "";
        foreach ($this->fieldNames as $field) {
            if(($status == 'any') || ($this->fieldData[$field]['status'] == $status))
                if($useAltFieldNames)
                    $fieldString .= "$prepend" . $this->viewFieldNames[$field] . ", ";
                else
                    $fieldString .= "$prepend$field, ";
        }
        if (strlen($fieldString) >= 2)
            $fieldString = substr($fieldString, 0, strlen($fieldString) - 2);
            
        return $fieldString;
    }
    
    /**
    * generate a select statement with the static fields from the 'items' table and the dynamic requirements from the 'pull_requirements' table
    * @param string $static_query_table 'items' table where static pull fields are located
    * @param string $dynamic_query_table 'pull_requiements' table where dynmic pull fields are located
    * @param bool $useAltFieldNames false
    * @return string
    */
    public function GetSelectStatement($static_query_table = 'items', $dynamic_query_table = 'pull_requirements', $useAltFieldNames = false, $dateRangeFields = true) {
        $qrySelectArray = array();

        foreach ($this->fieldNames as $field) {
            $prepend = "";
            if(($this->fieldData[$field]['status'] == 'static') && $static_query_table != 'none') {
                $prepend = $static_query_table;
                if($useAltFieldNames)
                     $qrySelectArray[] = "$prepend." . $this->viewFieldNames[$field];
                else
                     $qrySelectArray[] = "$prepend.$field";
            }
            elseif(($this->fieldData[$field]['status'] == 'dynamic') && $dynamic_query_table != 'none') {
                $prepend = $dynamic_query_table;
                if($useAltFieldNames)
                    $qrySelectArray[] = "$prepend." . $this->viewFieldNames[$field];
                else
                    $qrySelectArray[] = "$prepend.$field";
                // if a date, include the data difference and if it's out of range
                if($dateRangeFields && $static_query_table != 'none') {
                   if(in_array($field, $this->dateRangeFields)) {
                        $qrySelectArray[] = "$static_query_table.$field as actual_$field";
                        $qrySelectArray[] = "datediff($dynamic_query_table.$field, $static_query_table.$field) as $field" . "_diff";
                    }
                }
            }
            elseif(($this->fieldData[$field]['status'] == 'display') && $static_query_table != 'none') {
                $prepend = $static_query_table;
                if($useAltFieldNames)
                    $qrySelectArray[] = "$prepend." . $this->viewFieldNames[$field];
                else
                    $qrySelectArray[] = "$prepend.$field";
            }
        }

        foreach ($this->destinationFieldNames as $field) {
            if(($this->destinationFieldData[$field]['status'] == 'dynamic') && $dynamic_query_table != 'none') {
                $prepend = $dynamic_query_table;
                $qrySelectArray[] = "$prepend.$field";
            }
        }

        return implode(', ', $qrySelectArray);
    }
    
    public function GetDateRangeSelect($static_query_table = 'items', $dynamic_query_table = 'pull_requirements', $useAltFieldNames = false) {
        $returnVal = "0";
        foreach ($this->fieldNames as $field) {
            if((($this->fieldData[$field]['status'] == 'dynamic') && $dynamic_query_table != 'none') && (in_array($field, $this->dateRangeFields)))
            {
                $range = $this->fieldData[$field]['range'];
                $returnVal = "(abs(datediff($dynamic_query_table.$field, $static_query_table.$field)) > $range)";
            }
        }
        
        return $returnVal;
    }
    
    /**
    * generate a where statement for pull_requirements that incorporates static fields and 'pull_requirements.fufilled = 0'
    * @param string $query_table 'items'
    * @param bool $useAltFieldNames false
    * @param bool $fufilledFieldCheck true
    * @return false
    */
    public function GetWhereStatement($query_table = 'items', $useAltFieldNames = false, $fufilledFieldCheck = true) {
        $whereStatement = "pull_requirements.id_pull_header = $this->id_pull";
        if ($fufilledFieldCheck) {
            $whereStatement .= " and pull_requirements.fufilled = 0";
        }
        $whereStatement .= " and " . $query_table . "." . $this->viewFieldNames["consumed"] . " = '0' ";
        
        foreach ($this->fieldNames as $field) {
            if ($this->fieldData[$field]['status'] == "static"){
                if($useAltFieldNames)
                    $fieldName = $this->viewFieldNames[$field];
                else
                    $fieldName = $field;
                
                if (in_array($field, $this->multipleValueFields)) {
                    if (isset($this->fieldData[$field]['value2'])) {
                        $whereStatement .= " and ($query_table.$fieldName = '" . $this->fieldData[$field]['value'] . "'";
                        $whereStatement .= " or $query_table.$fieldName = '" . $this->fieldData[$field]['value2'] . "')";
                    }
                    else {
                        $whereStatement .= " and $query_table.$fieldName = '" . $this->fieldData[$field]['value'] . "'";
                    }
                }
                elseif (in_array($field, $this->intRangeFields)) {
                    if (isset($this->fieldData[$field]['max'])) {
                        $whereStatement .= "and ($query_table.$fieldName >= " . $this->fieldData[$field]['value'];
                        $whereStatement .= " and $query_table.$fieldName <= " . $this->fieldData[$field]['max'] .  ")";
                    }
                    else {
                        $whereStatement .= " and $query_table.$fieldName = '" . $this->fieldData[$field]['value'] . "'";
                    }
                }
                // data is stored as a varchar, but need to be evaluated within a float range
                elseif (in_array($field, $this->toDecimalRangeFields)) {
                    if (isset($this->fieldData[$field]['max'])) {
                        $whereStatement .= "and (convert($query_table.$fieldName, decimal(6,3)) >= " . $this->fieldData[$field]['value'];
                        $whereStatement .= " and convert($query_table.$fieldName, decimal(6,3)) <= " . $this->fieldData[$field]['max'] .  ")";
                    }
                    else {
                        $whereStatement .= " and convert($query_table.$fieldName, decimal(6,3)) = '" . $this->fieldData[$field]['value'] . "'";
                    }
                }
                else {
                    $whereStatement .= " and $query_table.$fieldName = '" . $this->fieldData[$field]['value'] . "'";
                }
            }
        }
        return $whereStatement;
    }
    
    /**
     * generates a join on statement on the dynamic fields
     * 'inner join pull_requirements on GetJoinStatement() ....
     * @param string $dynamic_query_table 'items'
     * @param bool $useAltFieldNames false
     * @return false
     */
    public function GetJoinStatement($dynamic_query_table = 'items', $useAltFieldNames = false) {
        $joinStatement = "TRUE ";
        foreach ($this->fieldNames as $field) {
            if ($this->fieldData[$field]['status'] == "dynamic"){
                if($useAltFieldNames)
                    $fieldName = $this->viewFieldNames[$field];
                else
                    $fieldName = $field;
                    
                // don't filter by date during the search; instead show the date diff for qa
                // the error check makes sure the tube is withing the correct date range
                if (in_array($field, $this->dateRangeFields)) {
                    /*if ($joinByDateRange) {
                        $range = $this->fieldData[$field][range];
                        $joinStatement .= " and (datediff($dynamic_query_table.$fieldName, pull_requirements.$field) > -$range) ";
                        $joinStatement .= " and (datediff($dynamic_query_table.$fieldName, pull_requirements.$field) < $range) ";
                    }*/
                }
                else {
                    $joinStatement .= " and $dynamic_query_table.$fieldName = pull_requirements.$field ";    
                } 
            }
        }
        return $joinStatement;
    }
    
    /**
    * Finds the difference in days between two calendar dates.
    *
    * @param Date $startDate
    * @param Date $endDate
    * @return Int
    */
   public function DateDiff($startDate, $endDate) {
       // Parse dates for conversion
       $startArry = date_parse($startDate);
       $endArry = date_parse($endDate);
       // Convert dates to Julian Days
       $start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
       $end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);
       // Return difference
       return round(($end_date - $start_date) , 0);
   }
}
?>
