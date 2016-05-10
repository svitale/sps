<?php
lib('dbi');
class Record{
    var $object = array();
    var $object_id = null;
    var $object_table = null;
    public function getObject() {
        return $this;
    }
}
class Attributes{
    var $attribute = array();
    var $object = array();
    var $attribute_id = null;
    var $id_study = null;
    var $attribute_name = null;
    var $attribute_type = null;
    var $attribute_description = null;
    var $username = null;
    // dialog we might need to maintain with end user
    var $message = null;
    var $error = null;

//TODO: check for attribute before creating it

    public function addNewAttribute() {
        global $dbrw;
        $sql = "insert into attributes_header (name,id_study,type,description) ";
        $sql .= "values ('$this->attribute_name','$this->id_study'";
        $sql .= ",'$this->attribute_type','$this->attribute_description')";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        } else {
             $this->attribute_id = mysqli_insert_id($dbrw);
             return $this;
        }
    }
                                                                                         
    public function getAttribute() {
        global $dbrw,$id_study;
        $sql = "select id,id_study,description,type,name from ";
        $sql .= "attributes_header where id_study = '$this->id_study' ";
        $sql .= "and name = '$this->attribute_name'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            $this->error = "Error:  getAttribute() Failed while running:\n $sql\n";
            return false;
        } else {
             $row = mysqli_fetch_array($result);
             $this->attribute_id = $row['id'];
             $this->attribute_type = $row['type'];
             $this->id_study = $row['id_study'];
             $this->attribute_name = $row['name'];
             $this->attribute_description = $row['description'];
             return $this;
        }
    }

    public function setAttribute() {
        global $dbrw,$username;
        if (!isset($this->attribute_name) && !isset($this->id_study)) {
            $this->message = 'Error: Attribute or Study missing';
            return false;
            exit;
	}
        $this->getAttribute();
	$object = $this->object;
        $sql = "insert into attributes_objects (id_attributes_header,id_object,table_object,assignee) values ";
        $sql .= "('$this->attribute_id','$object->object_id','$object->object_table','$username')";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        } else {
           //  $this->attribute_id = mysqli_insert_id($dbrw);
             return true;
        }
    }

    public function unsetAttribute() {
    }

   /**
   * Returns data for the given object (result, bq item, item, location)
   * return:
   */
    public function retAttributes() {
             return $this;
    }
}
