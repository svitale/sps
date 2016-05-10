<?php
class ShelfModel extends ContainerModel{
   /** 
   * returns some basic info about a freezer
   */
	var $id = null;
	var $racks = null;

        public function fetch() {
            global $dbrw;
	    if (!is_numeric($this->id)) {
                $this->error = 'object id was not provided';
                return false;
            }
           $sql = 'select items.id_uuid,divY,divX,sample_type,';
           $sql .= 'shipment_type,id_visit,destination from items ';
           $sql .= "where id = '$this->id'";
           $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }   
            while ($row = mysqli_fetch_assoc($result)) {
                $this->id = $row['id'];
                $this->id_uuid = $row['id_uuid'];
                $this->sample_type= $row['sample_type'];
                $this->shipment_type = $row['shipment_type'];
                $this->id_visit = $row['id_visit'];
                $this->site = $row['destination'];
            }           
        }    
         //get racks
         public function fetchRacks() {
            global $dbrw;
            $sql = "select items.id,items.id_uuid,subdiv2,divX,divY,sample_type,shipment_type,id_visit,items.destination from locations left join items on items.id = locations.id_item where locations.id_container=  '$this->id'";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }    
            while ($row = mysqli_fetch_assoc($result)) {
                $rack = new RackModel();
                $rack->id = $row['id'];
                $rack->id_uuid = $row['id_uuid'];
                $rack->num = $row['subdiv2'];
                $rack->x = $row['divX'];
                $rack->y = $row['divY'];
                $rack->capacity = abs($rack->x*$rack->y);
                $rack->sample_type = $row['sample_type'];
                $rack->shipment_type = $row['shipment_type'];
                $rack->id_visit = $row['id_visit'];
                $rack->site = $row['destination'];
                $rack->fetchBoxes();
                $rack->utilization = count($rack->boxes);
                $rack->percent_full = 100 * ($rack->utilization/$rack->capacity);
                $this->racks[] = $rack;
            }   
        }       

}
