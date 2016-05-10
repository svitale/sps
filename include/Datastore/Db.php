<?php
lib('dbi');
class Db {
    static function retTableColumns($table_name) {
        $return = array();
        global $dbrw;
        $sql =  'SELECT column_name FROM INFORMATION_SCHEMA.columns ';
        $sql .= 'where table_name = "'.$table_name.'"';
        $result = mysqli_query($dbrw, $sql);
        if(!$result) {
            print mysqli_error($dbrw)."\r\n";
        }
        while ($row = mysqli_fetch_array($result)) {
            $return[] = $row['column_name'];
        }
        if (count($return) > 0) {
         //   $this->data_array = $return;
            return $return;
        } else {
            print "Error: no columns found!";
            return false;
        }
    }
    static function retSingleRecord($table_name,$fields,$filters) {
        global $dbrw;
        $fieldSql =  '`' . implode($fields,"`,`") . '`'; 
        $filterSqlArray = array();
        foreach ($filters as $key=>$value) {
             $filterSqlArray[] = " `$key` = '$value' ";
        }   
        $filterSql = implode($filterSqlArray," and  ");

        $sql = "select $fieldSql from $table_name where $filterSql";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
        echo 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
        }   

        $num_rows = mysqli_num_rows($result);
        if ($num_rows != 1) {
                return false;
                exit;
        }
	return mysqli_fetch_object($result);;
    }   

    static function retMultiRecords($table_name,$requested_fields,$filters,$start=null,$limit=null) {
        global $dbrw;
        $records = array();
        $sql_array = array();
        foreach ($requested_fields as $requested_field) {
                $sql_array[] = "`$requested_field`";
        }   
        if ($filters) {
            foreach ($filters as $key=>$value) {
                $filterSqlArray[] = " `$key` = '$value' ";
            }   
            $filterSql = implode($filterSqlArray," and  ");
        }
        $fieldsSql =  implode($sql_array,","); 
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
        $sql = "select $calcSql $fieldsSql from `$table_name`";
        if ($filters) {
            $sql .= " where $filterSql";
        }
        $sql .= " $limitSql";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }   
        while ($row = mysqli_fetch_assoc($result)) {
            $records[] = $row;
        }   
        $sql = "select FOUND_ROWS() total";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $total = $row['total'];
        }

        $returnArray = array(
            'total'=>$total,
            'limit'=>$limit,
            'start'=>$start,
            'filters'=>$filters,
            'records'=>$records
        );

        return $returnArray;

    }   
    public function insert($table,$fields) {
        global $dbrw;
        $cols = array();
        $values = array();
        $dbdict = New DbDictionary();
        foreach ($fields as $col=>$value) {
           if (!property_exists($dbdict,$col)) {
               print "Error: column $col not found in our dictionary";
               return false;
           }
           $dbdict_col = $dbdict->$col;
           $dbdict_col_type = $dbdict_col['type'];
           $value_type = gettype($value);
/*
           if (gettype($value) != $dbdict_col['type']) {
               print "Error: column $col should have type $dbdict_col_type ";
               print "but you are trying to insert a value of type $value_type";
               return false;
           }
*/
           $values[] = $value;
           $cols[] = $col;
        }
        $sql = "insert into `$table` ";
        if (count($cols) > 0) {
            $sql .= '(`'. implode($cols,"`,`").'`) ';
            $sql .= "values ('". implode($values,"','")."')";
        } else {
            $sql .= '() values ()';
        }
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            print $sql;
            print 'Could not run query: ' . mysqli_error();
            return false;
            exit;
        }   
        return mysqli_insert_id($dbrw);
    }
    
    
}
