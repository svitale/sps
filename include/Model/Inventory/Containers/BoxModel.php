<?php
class BoxModel extends ContainerModel{
   /** 
   * returns some basic info about a Rack
   */
        var $id = null;
        public function getUtilization() {
            global $dbrw;
            $sql  = "select count(*) num from locations where id_container = '$this->id' and date_moved is null";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }    
            while ($row = mysqli_fetch_assoc($result)) {
                $this->utilization = $row['num'];
            }
            $this->getCapacity();
            $this->getPercentFull();
      }
 
}
