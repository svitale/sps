<?php
class ResultsObjects{
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
    var $limit = null;
    var $start = null;
    var $total = null;
    var $filters = null;
    var $fields = null;
    var $records = null;
    var $rungroups = null;
    var $rungroup = null;
    var $idArray = null;
    var $assays = null;
    var $plates = null;
    var $table = 'results_raw';
    var $tmpIdTable = null;
    var $excludeImported = false; 
    var $excludeIgnored = true;
    var $excludeOld = false;
    public function retDistinct($by) {
        global $dbrw,$sps;
        $rungroups = array();
        $sql = 'select distinct('.$by.') grouped from '.$this->table.' where ';
        $sql .= $this->filterSql();
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rungroups[] = $row['grouped'];
        }
        return $rungroups;
    }
    //create a temporary table for the idArray
    public function idArrayTable($idArray) {
        global $dbrw, $sps;
        if (!(count($idArray) >0)) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
        } 
        $tmptable = 'tmp_' . rand();
        $sql = "create temporary table `$tmptable` as select id from results_raw limit 0";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
        } 
        $sql = "alter table `$tmptable` add index(id)";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
        } 
        foreach ($idArray as $id) {
            $sql = "insert into  `$tmptable` values ($id);";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                    $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                    return false;
                    exit;
            } 
        }
        $this->tmpIdTable = $tmptable;
        return true;
    }
    
    public function filterSql() {
        if (!$this->filters) {
             $this->error = "Error: Refusing to return the results table!\n";
             return false;
             exit;
        }
        foreach ($this->filters as $key=>$value) {
                if ($key == 'datestart' || $key == 'startdate') {
                     $filterSqlArray[] = " `".$this->table."`.`datetime_assay` >= '$value 00:00:00' ";
                } else if ($key == 'dateend' || $key == 'enddate') {
                     $filterSqlArray[] = " ``.`datetime_assay` <= '$value 23:59:59' ";
                } else if ($key == 'imported') {
                    if($value == 'true') {
                         $this->excludeImported = 'false';
                         $filterSqlArray[]  = " `".$this->table."`.`id` in (select id_results_raw from results_import)";
                    }
                } else {
                  $filterSqlArray[] = " `".$this->table."`.`$key` = '$value' ";
                  if ($key == 'id_rungroup') {
                      $this->rungroup = $value;
                  }
                }
        }
        if($this->excludeIgnored) {
            $filterSqlArray[] = "`".$this->table."`.`ignore` = 0";
        }
        if($this->excludeOld) {
            $filterSqlArray[] = "`".$this->table."`.`timestamp`  >= now() - INTERVAL 90 DAY";
        }
        if($this->excludeImported) {
            $filterSqlArray[]  = " `".$this->table."`.`id` not in (select id_results_raw from results_import)";
        }
        $filterSql = implode($filterSqlArray," and  ");
        return $filterSql;
    }

    public function performAction($action) {
        global $dbrw;
        $tmpIdTable = $this->tmpIdTable;
        $sql = "update results_raw set ";
        if ($action == 'Identify') {
            $sql .=  "`id_subject` = 'baz' where ";
        } else if ($action == 'Approve') {
            $sql .=  "`reviewed` = 1 where ";
        } else if ($action == 'Save') {
            return true;
        }
        $sql .= "id in (select id from `$tmpIdTable`)";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }   
        return true;
       
      
    }
    public function fetchRecords() {
        global $dbrw,$sps;
        $tmpIdTable = $this->tmpIdTable;
        $this->records = array();
        if ($this->start || $this->limit) {
            if (!$this->start) {
                $this->start = 0;
            }
            if (!$this->limit) {
                $this->error = "Error: must specify limit when using offset!\n";
                return false;
                exit;
            } 
            $calcSql = " SQL_CALC_FOUND_ROWS ";
            $limitSql = " Limit $this->start,$this->limit ";
        } else {
            $calcSql = "";
            $limitSql = "";
        }
        $sql = "select $calcSql ".$this->table.".id results_raw_id,".$this->table.".dilution,".$this->table.".reviewed,".$this->table.".id_uuid,".$this->table.".id_barcode,";
        $sql .= "".$this->table.".share as approved,".$this->table.".`ignore`,".$this->table.".id_rungroup as run,".$this->table.".id,".$this->table.".barcode_source,";
        $sql .= "".$this->table.".position_plate,".$this->table.".layout_plate,".$this->table.".id_visit,".$this->table.".id_visit visit,".$this->table.".sample_type,";
        $sql .= "".$this->table.".shipment_type,".$this->table.".id_study,".$this->table.".id_instrument,".$this->table.".id_assay,".$this->table.".value,";
        $sql .= "".$this->table.".value_1,".$this->table.".value_2,".$this->table.".value_measured,".$this->table.".cv,".$this->table.".wavelength,".$this->table.".id_subject,";
        $sql .= "position_plate,".$this->table.".uqc,";
        $sql .= $this->table.".units,".$this->table.".date_visit,".$this->table.".datetime_assay,".$this->table.".name_plate from ".$this->table." where ";
/*
        foreach (get_object_vars($this) as $key=>$value) {
            if (array_key_exists($key,$this->fields)) {
                $sql .= "`items`.`$key`,";
             }
        }
*/
        if ($this->tmpIdTable) {
            $sql .= "id in (select id from `$tmpIdTable`)";
        } else {
            $sql .= $this->filterSql();
        }
//        $sql .= ' and value > 0 ';
//        $sql .= ' group by value ';
        $sql .= $limitSql;

        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }   
        while ($row = mysqli_fetch_assoc($result)) {
            $this->records[] = $row;
        }   
        $sql = "select FOUND_ROWS() total";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $this->total = $row['total'];
        }
        return true;
    }
}
