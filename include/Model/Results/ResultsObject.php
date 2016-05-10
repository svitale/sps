<?php
class ResultsObject{
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
    var $table = 'results_raw';
    var $id = 'null';
   function __construct($id) {
       $this->id = $id;
       $this->fetchRecord();
   }
   public function fetchRecord() {
        global $dbrw,$sps;
        if (!$this->id) {
            return false;
        }
        $id = $this->id;
        $table = $this->table;
        $sql = "select * from `$table` where id = '$id'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        }   
        if (mysqli_num_rows($result) != 1) {
            return false;
        }
        $row = mysqli_fetch_assoc($result);
           foreach ($row as $field=>$val) {
              $this->$field = $val;
           }   
    }
   public function patchRecord($changed) {
        global $dbrw,$sps;
        if (!$this->id || count($changed) == 0) {
            return false;
        }
        $id = $this->id;
        $table = $this->table;
        $sql = "update `$table` set ";
        $updateArray = array();
        foreach ($changed as $var=>$val) {
            $updateArray[] = "`$var` = '$val' ";
        }   
        $sql .= implode($updateArray,",");
        $sql .= "where id = '$id'";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        }   
    }
    public function importResult() {
        lib('Datastore');
print('foo');
        return array('foo');
    }
}
