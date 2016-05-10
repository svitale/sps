<?php
lib('Task');
lib('Model/Inventory/ContainerModel');
lib('Model/Inventory/Containers');
class Consolidate extends Task{
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
      var $id = null;
      function __construct($id) {
              global $sps;
              $farm = new FarmModel();
              $farm->id_study = $sps->active_study->id_study;
              $farm->fetchFreezers();
              $this->freezers = $farm->freezers;
              //$this->active_object = '2204429';
              //$this->low_cutoff = '5';
              //$this->high_cutoff = '95';
    }
}
