<?php
lib('dbi');
class PullModel{
    //
    //
    // 
    var $id = null;
    var $name = null;
    var $description = null;
    var $status ='active';
    var $id_subject = 'unused';
    var $sample_type = 'unused';
    var $shipment_type = 'unused';
    var $id_study_value = 'unused';
    var $date_visit = 'unused';
    var $date_visit_range = 'unused';
    var $id_visit = 'unused';
    var $quant_thaws 'unused';
    var $quant_cur_max = 'unused';
    var $alq_num = 'unused';
    var $id_uuid  = 'unused';
    
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
