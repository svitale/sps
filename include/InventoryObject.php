<?php
include_once('lib.php');
lib('dbi');
class InventoryObject {
    var $error = false;
    var $message = null;
    var $linear_barcode = null;
    var $table = null;
    var $daughters =  null;

    //BEGIN CONSTRUCTOR
    function __construct() {
        global $sps;
        if ($this->table == null) {
            $this->table = 'items';
         } 

         // any fields added here should exist in the items/batch_quality 
         // industrial complex 
  
         $fields =   array(
             'id_study','id_ancillary','id_collection','cell_count',
             'type','id_batch','id','id_uuid','date_visit','date_ship',
             'date_receipt','id_parent','id_subject','sample_type',
             'id_barcode','sample_source','shipment_type','id_visit',
             'name_shipper','notes','quantity','family','destination',
             'quant_init','quant_thaws','quant_cur');

         // add the field to me
         foreach ($fields as $field) {
             $this->$field = null;
         }

         //todo: why not just update behavior to look at the url?
         if (!preg_match('/^\/sps\/api\/.*/',($_SERVER['REQUEST_URI']))) {
             $behavior = New Behavior();
             $this->fields = $behavior->retFields($this);
         } else {
            $this->fields = $fields;
         }
    }
    //BEGIN CONSTRUCTOR



    public function resolveScannedLinearBarcode() {
            $this->lookForBarcode();
            if ($this->id == null) {
               $this->table = 'batch_quality';
            }
            $this->lookForBarcode();
            if ($this->id == null) {
              return false;
            } else {
              return $this;
            }
     }
 
     public function lookForBarcode() {
            global $id_study,$dbrw;
            $sql = "SELECT id FROM `$this->table` WHERE `id_barcode` = '$this->linear_barcode' and id_study = '$id_study'";
     	    $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                print 'Could not run query: ' . mysql_error();
                return false;
                exit;
            }
            $num_rows = mysqli_num_rows($result);
            if ($num_rows == 0) {
                return false; 
            }
            if ($num_rows == 1) {
                $row = mysqli_fetch_array($result);
                $this->id = $row['id'];
                return true;
            } else {
                print "Error:  More than one record found";
                return false;
            } 
        }
    public function returnUuidType($id_uuid) {
        global $dbrw;
        $type = array();
        $sql_array['batch_quality_record'] = "select id from `batch_quality` where id_uuid = '$id_uuid' limit 1";
        $sql_array['batch_quality_batch'] = "select id from `batch_quality` where id_batch = '$id_uuid' limit 1";
        $sql_array['items_record'] = "select id from `items` where id_uuid = '$id_uuid' limit 1";
        foreach ($sql_array as $key=>$sql) {
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }
            $num_rows = mysqli_num_rows($result);
            if ($num_rows > 0)  {
                $type[] = $key;
            }
    }
    return $type;
       
    }
    public function Fetcher() {
        global $dbrw;
        $sql = 'select id,id_study,type,id_uuid,id_batch,date_ship,date_visit,';
        $sql .= 'date_receipt,id_parent,id_subject,sample_type,';
        foreach (get_object_vars($this) as $key=>$value) {
            if (array_key_exists($key,$this->fields)) {
                $sql .= "`$key`,";
             }
        }
        $sql .= 'quant_init ';
        $sql .= ' from ' . $this->table .' ' ;
        if (isset($this->id_uuid)) {
	    $id_uuid = $this->id_uuid;
            $sql .= "where id_uuid = '$id_uuid' ";
        } else if (isset($this->id)) {
	    $id = $this->id;
            $sql .= "where id = '$id' ";
	} else {
            $this->error = 'Required Field: id_uuid not found';
            return false;
        }
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
        echo 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
        }
        $InventoryRecord = mysqli_fetch_object($result);
        foreach (get_object_vars($InventoryRecord) as $key=>$value) {
            $this->$key= $value;
        }
    }
    public function returnMetadata() {
        global $dbrw;
        $field_types= array();
        $obj_fields = get_object_vars($this);
        $sql = "describe $this->table";
        $result = mysqli_query($dbrw,$sql);
        while ($row = mysqli_fetch_array($result)) {
            $field_name = $row['Field'];
            $field_type = $row['Type'];
            if (array_key_exists($row['Field'],$obj_fields)) {
                $field_types[$field_name] = $field_type;
            }
        }
       return $field_types;
    }
    public function returnInventoryArray($limit=null,$start=null) {
        global $dbrw,$sps;
        $returnArray = array();
        if (!$sps->filters) {
             print "Error: Refusing to return the entire inventory!\n";
             exit;
        } else {
            $filters = $sps->filters;
        }
        foreach ($filters as $key=>$value) {
                $filterSqlArray[] = " `items`.`$key` = '$value' ";
        }
        $filterSql = implode($filterSqlArray," and  ");
        //
        if ($start || $limit) {
            if (!$start) {
                $start = 0;
            }
            if (!$limit) {
                print "Error: must specify limit when using offset!\n";
                exit;
            } 
            $calcSql = " SQL_CALC_FOUND_ROWS ";
            $limitSql = " Limit $start,$limit ";
        } else {
            $calcSql = "";
            $limitSql = "";
        }
        $sql = "select $calcSql `items`.`id`,`items`.`id_study`,`items`.`id_uuid`,`items`.`id_parent`,";
        $sql .= "`items`.`id_subject`,`items`.`date_visit`,`items`.`date_receipt`,";
        foreach (get_object_vars($this) as $key=>$value) {
            if (array_key_exists($key,$this->fields)) {
                $sql .= "`items`.`$key`,";
             }
        }
        $sql .= "`locations`.`freezer`,`locations`.`subdiv1`,`locations`.`subdiv2`,";
        $sql .= "`locations`.`subdiv3`,`locations`.`subdiv4`,`locations`.`subdiv5` ";
        $sql .= "from items left join locations on items.id = locations.id_item where ";
        $sql .= "`locations`.`date_moved` is null and ";
        $sql .= implode($filterSqlArray," and  ");
        $sql .= $limitSql;
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            print $this->error;
            return false;
            exit;
        }   
        while ($row = mysqli_fetch_assoc($result)) {
            $returnArray[] = $row;
        }   
        $sql = "select FOUND_ROWS() total";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            print $this->error;
            return false;
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $total = $row['total'];
        }
        return array('total'=>$total,'limit'=>$limit,'start'=>$start,'filter'=>$sps->filters,'matched'=>$returnArray);
    }
    public function returnBatchArray($crf,$id_subject = null,$sample_type = null) {
        global $dbrw;
        $batchid = $crf->batchid;
        $t = $crf->table;
        $sql = "select ";
        $sql = "select `$t`.id,`$t`.id_study,`$t`.id_uuid,`$t`.id_parent,`$t`.id_subject,`$t`.date_visit,`$t`.date_receipt,";
        foreach (get_object_vars($this) as $key=>$value) {
            if (array_key_exists($key,$this->fields)) {
                $sql .= "`$t`.`$key`,";
             }
        }
        $sql .= "`$t`.family,crf.quantity,crf.num_order,`$t`.sample_type ";
        $sql .= "FROM `$t` left join crf on (`$t`.family = crf.family and ";
        $sql .= "`$t`.shipment_type = crf.shipment_type and ";
        $sql .= "`$t`.sample_type = crf.sample_type) ";
        $sql .= "where `id_batch` = '$batchid' ";
        if ($id_subject != null) {
            $sql .= "and id_subject = '$id_subject' ";
        }   
        if ($sample_type != null) {
            $sql .= "and sample_type = '$sample_type' ";
        }   
        $sql .= "order by `$t`.id";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            print $this->error;
            return false;
            exit;
        }   
        while ($row = mysqli_fetch_assoc($result)) { $record = clone $this;
            foreach ($row as $key=>$value) {
               $record->$key = $value;
            }
            $returnArray[] = $record;
        }   
        if (isset($returnArray))  {
           return $returnArray;
        } else {
           return false; 
        }   
    }
   public function malkovich($invObjArray,$id_parent = '0') {
       $malkovich = array();
       $leftovers = array();
       foreach ($invObjArray as $invObject) {
           if ($invObject->id_parent == $id_parent) {
               $malkovich[] = $invObject;
           } else {
               $leftovers[] = $invObject;
           }
           for ($i = 0;$i < count($malkovich);$i++) {
             $malkovich[$i]->daughters = $this->malkovich($leftovers,$malkovich[$i]->id_uuid);
           }
       }
       if (count($malkovich) == 0) {
           $malkovich = null;
       }
       return $malkovich;
   }

  /**
  * Calculate the current generation of a sample.
  * Ancestors could be in either the items or batch_quality table.
  */
  public function calcGenerationNumber() {
    global $dbrw;
    $MAX_DEPTH = 15;  // just in case...

    $depth = 0;
    $id_uuid = $this->id_uuid;
    $id_parent = $this->id_parent;
    while (($id_parent != '0') && ($depth < $MAX_DEPTH)) {
      $this->generation = $depth;
      $sql = " select id_uuid, id_parent ";
      $sql .= " FROM items ";
      $sql .= " WHERE id_uuid = '$id_parent' ";
      $sql .= " UNION";
      $sql .= " select id_uuid, id_parent ";
      $sql .= " FROM batch_quality ";
      $sql .= " WHERE id_uuid = '$id_parent' ";

      $result = mysqli_query($dbrw,$sql);
      if (!$result) {
        $this->error = 'Could not run query: ' . mysqli_error($dbrw);
        return false;
        exit;
      }   
      $row = mysqli_fetch_assoc($result);

      $id_uuid = $row['id_uuid'];
      $id_parent = $row['id_parent'];
      $depth = $depth + 1;
    }
    $this->generation = $depth;
    return True;
  }

  public function modifyRecord() {
       global $dbrw;
       $sqlArray = array();
       $record = New InventoryObject();
       $table = $this->table;
       $id_uuid = $this->id_uuid;
       $record->table = $table;
       $record->id_uuid = $id_uuid;
       $record->Fetcher();
       $fields = $this->fields;
       $fields = array_keys($this->fields);
       foreach ($fields as $key) {
             $old_value = $record->$key;
             $new_value = $this->$key;
             if ($new_value != $old_value) {
               $sqlArray[] .= "`$key` = '$new_value'";
             }
       }
       if (count($sqlArray) > 0) {
           $sql = "update `$table` set ";
           $sql .= implode($sqlArray,",");
           $sql .= " where `id_uuid` = '$id_uuid' ";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            print($this->error);
            return false;
            exit;
        }   
       } else {
            $this->error = 'Could not find anything to update';
            print($this->error);
            return false;
            exit;
       }
  }
}
