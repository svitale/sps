<?php
class FreezerModel extends ContainerModel{
   /** 
   * returns some basic info about a freezer
   */
        var $id = null;
	var $name = null;
        var $shelves = null;

        public function fetch() {
            global $dbrw;
	    if (!is_numeric($this->id)) {
                $this->error = 'object id was not provided';
                return false;
            }
            $sql = "select id,id_uuid,id_study,comment1,divX,divY,destination from items left join locations on locations.id_item = items.id where ";
            $sql .= "type = 'freezer' and ";
            $sql .= "`items`.`id` = '$this->id'";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }            
            while ($row = mysqli_fetch_assoc($result)) {
                $this->name= $row['comment1'];
                $this->id_uuid =  $row['id_uuid'];
                $this->site = $row['destination'];
                $this->x = $row['divX'];
                $this->y = $row['divY'];
            }           
         }
            // get shelves
         public function fetchShelves() {
            global $dbrw,$filters;
            $sql = "select items.id,items.id_uuid,locations.subdiv1,divX,freezer,sample_type,shipment_type,id_visit,items.destination from locations left join items on items.id = locations.id_item where locations.id_container=  '$this->id'";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }        
            while ($row = mysqli_fetch_assoc($result)) {
                $shelf = new ShelfModel();
                $shelf->id = $row['id'];
                $shelf->id_uuid = $row['id_uuid'];
                $shelf->num = $row['subdiv1'];
                $shelf->x = $row['divX'];
                $shelf->sample_type = $row['sample_type'];
                $shelf->shipment_type = $row['shipment_type'];
                $shelf->id_visit = $row['id_visit'];
                $shelf->site = $row['destination'];
                $shelf->freezer = $row['freezer'];
                $shelf->fetchRacks();
                $shelf->capacity = $shelf->x;
                $shelf->utilization = count($shelf->racks);
                $shelf->percent_full = 100 * ($shelf->utilization/$shelf->capacity);
                $this->shelves[] = $shelf;
            }
        }    
         public function fetchAllShelves() {
            global $dbrw,$sps;
$id_study = $sps->active_study->id_study;
            $sql = "select items.id,items.id_uuid,locations.subdiv1,divX,freezer,sample_type,shipment_type,id_visit,items.destination from locations left join items on items.id = locations.id_item where type = 'shelf' and id_study = '$id_study'";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }        
            while ($row = mysqli_fetch_assoc($result)) {
                $shelf = new ShelfModel();
                $shelf->id = $row['id'];
                $shelf->id_uuid = $row['id_uuid'];
                $shelf->num = $row['subdiv1'];
                $shelf->x = $row['divX'];
                $shelf->sample_type = $row['sample_type'];
                $shelf->shipment_type = $row['shipment_type'];
                $shelf->id_visit = $row['id_visit'];
                $shelf->site = $row['destination'];
                $shelf->freezer = $row['freezer'];
                $shelf->fetchRacks();
                $this->shelves[] = $shelf;
            }
        }    
}
