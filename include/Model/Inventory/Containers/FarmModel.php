<?php
class FarmModel extends ContainerModel{
   /** 
   * returns some basic info about a Rack
   */
        public function fetchFreezers() {
            global $dbrw;
            $sql = "select id,id_uuid,comment1,id_study,destination from items where type = 'freezer' and consumed != 1";
            if (isset($this->id_study)) {
                $sql .= " and id_study = '$this->id_study'";
            }
           $result = mysqli_query($dbrw,$sql);
           if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }    
         while ($row = mysqli_fetch_assoc($result)) {
                $freezer = new FreezerModel();
                $freezer->id = $row['id'];
                $freezer->id_uuid = $row['id_uuid'];
                $freezer->name = $row['comment1'];
                $freezer->id_study = $row['id_study'];
                $freezer->site = $row['destination'];
                $this->freezers[] = $freezer;
         }
         $freezer = new FreezerModel();
         $freezer->name = 'any';
         $freezer->id = 0;
         $freezer->id_uuid = null;
         $freezer->id_study = null;
         $freezer->site = null;
         $this->freezers[] = $freezer;


        }
}
