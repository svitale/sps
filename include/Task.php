<?php
class Task{
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
    var $error = null;
    var $table = null;
    var $message = null;
    var $xls_template = null;
    var $active_object = null;
    var $active_project = null;
    public function setActiveObject($object) {
        $_SESSION['active_object'] = $object;
        //todo: remove lagacy references to detailid/detailposttable
        $_SESSION['Detailid'] = $object->id;
        $_SESSION['DetailpostTable'] = $object->table;
        $this->active_object = $object;
    }
    public function returnObjectType($id_uuid) {
        lib('Model/Inventory');
	$invobject = New InventoryObject();
        $type_array = $invobject->returnUuidType($id_uuid);
        if (count($type_array) == 0) {
            $this->message = 'record not found';
            $this->error = true;
            return false;
        } else if (count($type_array) > 1) {
            $this->message = 'Too many records found: can not pick one to process ';
            $this->error = true;
            return false;
        } else {
            return $type_array[0];
        }
    }
    public Function genUuids() {
        if (isset($this->tmptable)) {
        $sql = "select id from `".$this->tmptable."` where id_uuid = '';";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_array($result)) {
                $update = mysql_query("update `".$this->tmptable."` set id_uuid = '" . new_uuid() . "' where id = $row[id]");
                if (!$update) {
                        echo 'Could not run query: ' . mysql_error();
                        exit;
                }           
        }           

        }
    }
}
?>
