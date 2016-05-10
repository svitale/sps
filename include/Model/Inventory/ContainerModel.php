<?php
class ContainerModel{
   /** 
   * returns some basic info about a Rack
   */
	var $id = null;
	var $id_uuid = null;
        // the number of subunits wide this container is
        var $x = null;
       // the number of subunits hight this container is
        var $y = null;
        // the number or position of this container in reference to the rest
        // of the collection
        var $utilization = null;
        var $capacity = null;
        var $percent_full = null;
        var $num = null;
	var $sample_type = null;
	var $shipment_type = null;
	var $id_visit = null;
	var $site = null;
        public function getCapacity() {
           if (is_numeric($this->x) && is_numeric($this->y)) {
               $this->capacity =  abs(($this->x)*($this->y));
           }   
        }
        public function getPercentFull() {
           if (is_numeric($this->utilization) 
               && is_numeric($this->capacity) && $this->capacity >0) {
               $this->percent_full =  round((($this->utilization)/($this->capacity))*100);
           }   
        }



       //get the contents of this container
        public function getContainedIds() {
            global $dbrw;
            $contents = array();
            $sql  = "select id_item from locations where id_container = '$this->id' and date_moved is null";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }    
            while ($row = mysqli_fetch_assoc($result)) {
                $contents[] = $row['id_item'];
            }  
            return $contents;
        }
   
}
