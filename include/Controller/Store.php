<?php
lib('Task');
class Store  extends Task{
   /** 
   * Returns a crf object to guide the user to different actions
   * available on new inventory objects
   * return:
   *  [
   * ]
   */
    function __construct() {
     if (isset($_SESSION['active_object'])) {
           $object = $_SESSION['active_object'];
            $this->active_object = $object;
        }   
        if (!$this->active_object) {
            $this->active_object = new InventoryObject();
        }   
        $behavior = New Behavior();
        $this->tracked_fields = $behavior->retFields($this->active_object);
        parent::__construct();
    }   
}
