<?php
lib('db');
class InventoryCollection extends SpsObjects{
var $inventory = array();
    function __construct() {
        global $sps;
        if ($this->table == null) {
            if ($sps->task == 'crf') {
                $this->table = 'batch_quality';
            } else {
                $this->table == 'items';
            }   
        }   
    } 

    //Fetch a single Inventory Object
    public function fetchInventoryObject() {
        if (!$this->invobject) {
            $this->error .=  "Error: fetchInventoryObject() requires an InventoryObject with an id or uuid\n";
            print $this->error;
            return false;
            exit;
        }
        if (!$this->table) {
            $this->error .=  "Error: fetchInventoryObject() requires a table\n";
            print $this->error;
            return false;
            exit;
        }   

        $invobject = $this->invobject;
        //fetch by uuid or table id
        if (isset($invobject->id)) {
                $by_field->name = 'id';
                $by_field->value = $invobject->id;
        } else if (isset($invobject->id_uuid)) {
                $by_field->name = 'id_uuid';
                $by_field->value = $invobject->id_uuid;
        } else {
            $this->error =  "Error: fetchSingleInventoryObject() expects a value for id_uuid or id.\n";
            $this->error .=  "None found\n";
            print $this->error;
            return false;
        }   
        $empty_object = clone $this->invobject;
        $gotObject = $this->getSpsObject($by_field,$empty_object);
        if ($gotObject) {
            $this->invobject = $gotObject;
            return true;
        } else {
            return false;
        }

    }   
     
    public function fetchInvLocArray() {
        global $dbrw,$sps;
        $InventoryArray = array();
        if (!$sps->filters) {
             print "Error: Refusing to return the entire inventory!\n";
             exit;
        } else {
            $this->filters = $sps->filters;
        }
        $this->table = 'items';
        $empty_object = New InventoryObject();
        $gotObjectArray = $this->getSpsObjectArray($empty_object);
        if ($gotObjectArray) {
            $this->type = 'inventorylocations';
            $this->invobjectarray = $gotObjectArray;
            return true;
        } else {
            return false;
        }
    }

    public function fetchBatchArray($id_subject = null,$sample_type = null) {
        $filters =array();
        if ($id_subject) {
             $filters['id_subject'] = $id_subject;
        }
        if ($sample_type) {
             $filters['sample_type'] = $sample_type;
        }
        if ($this->batchid) {
             $filters['id_batch'] = $this->batchid;
        } else {
             $this->error = "Error: returnBatchArray() requires a batchid!\n";
             print $this->error;
             return false;
        }
        lib('Model/Inventory/Batch');
        $empty_object = New InventoryBatchObject();
        $this->table = 'batchqualitycrfview';
        $this->filters = $filters;
        $gotObjectArray = $this->getSpsObjectArray($empty_object);
        if ($gotObjectArray) {
            $this->invobjectarray = $gotObjectArray;
            $this->type = 'batch';
            return true;
        } else {
            return false;
        }
    }
}
