<?php
lib('dbi');
class PullsCollection{
    //
    //
    // 
    var $id_study = null;
    public function Loader() {
        global $sps;
        if ($this->id > 0) {
            $filters = array('id'=>$this->id);
            $db = New Db(); 
            $fields = array_keys(get_class_vars(get_class($this)));
print_r($fields);
            $db_result = $db->retSingleRecord('project_header',$fields,$filters);
            print_r($db_result);
        }
    }

}
