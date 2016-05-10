<?php
class InventoryBatchObjects extends InventoryCollection {
    var $batchid = null;
    var $type = null;
    var $id_subject = null;
    var $sample_type = null;
    public function getBatch() {
        $filters =array();
        if ($this->id_subject) {
             $filters['id_subject'] = $id_subject;
        }   
        if ($this->sample_type) {
             $filters['sample_type'] = $sample_type;
        }   
        if ($this->batchid) {
             $filters['id_batch'] = $this->batchid;
        } 
        if ($this->type != 'staging' &&  !$this->batchid) {
             $this->error = "Error: getBatch requires a batchid!\n";
             $this->error = "when type is $this->type\n";
             print $this->error;
             return false;
        }   
        $empty_object = New InventoryBatchObject();
        if (count($filters) > 0) {
            $this->filters = $filters;
        }
        $gotObjectArray = $this->getSpsObjectArray($empty_object);
        if ($gotObjectArray) {
            return $gotObjectArray;
        } else {
            return false;
        }   
    }   

}
