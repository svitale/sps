<?php
class RackModel extends ContainerModel{
   /** 
   * returns some basic info about a Rack
   */
         var $id = null;
         public function fetchBoxes() {
             if (isset($_SESSION['shipment_type'])) {
                 $shipment_type = $_SESSION['shipment_type'];
             } else {
                 $shipment_type = false;
             }
             if (isset($_SESSION['sample_type'])) {
                 $sample_type = $_SESSION['sample_type'];
             } else {
                 $sample_type = false;
             }
             if (isset($_SESSION['id_visit'])) {
                 $id_visit = $_SESSION['id_visit'];
             } else {
                 $id_visit = false;
             }
             if (isset($_SESSION['destination'])) {
                 $destination = $_SESSION['destination'];
             } else {
                 $destination = false;
             }
            global $dbrw;
            $sql = "select items.id,items.id_uuid,subdiv3,divX,divY,sample_type,shipment_type,id_visit,items.destination from locations left join items on items.id = locations.id_item where locations.id_container=  '$this->id'";
            if ($destination) {
                $sql .= " and items.destination = '$destination'";
            }
            if ($sample_type) {
                $sql .= " and sample_type = '$sample_type'";
            }
            if ($shipment_type) {
                $sql .= " and shipment_type = '$shipment_type'";
            }
            if ($id_visit) {
                $sql .= " and id_visit = '$id_visit'";
            }
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }       
            while ($row = mysqli_fetch_assoc($result)) {
                $box= new BoxModel();
                $box->id = $row['id'];
                $box->id_uuid = $row['id_uuid'];
                $box->num = $row['subdiv3'];
                $box->x = $row['divX'];
                $box->y = $row['divY'];
                $box->sample_type = $row['sample_type'];
                $box->shipment_type = $row['shipment_type'];
                $box->id_visit = $row['id_visit'];
                $box->site = $row['destination'];
                $box->getUtilization();
                $this->boxes[] = $box;
            }   
        }          

}
