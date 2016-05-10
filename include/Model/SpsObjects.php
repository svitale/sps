<?php
class SpsObjects {
    // table/view we're working with
    var $table =  null;

    // filters SpsObjects based on these fields
    var $filters = null;

    // int: record_no 
    var $start = null;

    // int: maximum number of records to return
    var $limit = null;

    // number of matching records
    var $total = null;

    // number of records in this array
    var $length = null;

    // type of object
    // enum: batch, tmptable
    var $type = null;

    // Errors encountered while working with this object or its contents
    var $error = false;

    //Fetch a single Sps Object
    //returns empty_object populated with contents of db


    public function setTable() {
        if($this->type == 'staging' && isset($_SESSION['tmptable'])) {
             $this->table = $_SESSION['tmptable'];
        } else if (!isset($this->table)) {
            $this->error =  "Error: fetchSpsObject() requires a table.\n";
            $this->error .=  "None found\n";
            print $this->error;
            return false;
        }
    }

    public function getSpsObject($by_field,$empty_object) {
        $this->setTable();
/*
        if (!in_array($by_field->name,array('id','id_uuid')) || !isset($by_field->value)) {
            $this->error =  "Error: fetchSingleSpsObject() expects a value for id_uuid or id.\n";
            $this->error .=  "None found\n";
            print $this->error;
            return false;
        }   
*/
        $requested_fields = array();
        foreach($empty_object->fields as $field) {
            array_push($requested_fields,$field->name);
        }

        //Retrieve the record from the database
        $Record = Db::retObjectRecord($this->table,$requested_fields,$by_field);
        if (!$Record) {
            $this->error = "Record not found\n";
            print $this->error;
            return false;
        }  
        $ret_object = clone $empty_object;
        foreach ($Record as $key=>$value) {
                $ret_object->$key= $value;
        }   
        if ($ret_object && $ret_object->type) {
            $this->type = $invobject->type;
            $this->length = 1;
            return $ret_object;
        } else {
            return false;
        }
    }   
     
    public function getSpsObjectArray($empty_object) {
        $this->setTable();
        if ($this->type != 'staging' && !$this->filters) {
             $this->error =  "Error: getSpsObjectArray requires filters!\n";
             print $this->error;
             return false;
        }
        $requested_fields =  array_keys(get_object_vars($empty_object));
        $sps_fields = New SpsFields($this->table);
        foreach ($requested_fields as $requested_field) {
           // $this->fields[] = $sps_fields->$requested_field;
        }
$this->fields[] = 'id_subject';
        $Array = Db::retMultiRecords($this->table,$requested_fields,$this->filters,$this->start,$this->limit);
        $sps_object_array = array();
        if ($Array) {
            foreach ($Array['records'] as $Record) {
                $ret_object = clone $empty_object;
                foreach ($Record as $key=>$value) {
                     $ret_object->$key= $value;
                }   
                array_push($sps_object_array,$ret_object);
            }
            // we'll reset the current instance's start limit and total to what
            // what Db tells us it should be
            $this->start = $Array['start'];
            $this->limit = $Array['limit'];
            $this->total = $Array['total'];
            $this->length = count($sps_object_array);
            return $sps_object_array;
       } else {
            return false;
       }
    }

}
