<?php
lib('Task');
lib('Model/Inventory/ContainerModel');
lib('Model/Inventory/Containers');
class Freezer extends FreezerModel{
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
          $this->id = $id;
          if($id > 0) {
              //$freezer = new FreezerModel();
              //$freezer->id = $id;
              $this->fetch();
              $this->fetchShelves();
          } else if (id == 0) {
              $this->name = 'any';
              $this->fetchAllShelves();

          }
    
    }
}
