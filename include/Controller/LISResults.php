<?php
lib('Task');
lib('Model/Results');
class LISResults{
   /** 
   * Returns a results object to guide the user to different actions
   * available on results analysis
   * return:
   *  [
   *  'fields' => the valid fields used by a paricular record set
   *  'rungroups' => [] list of rungroups that fit the current filters
   *  'project' => a project that these results are a part of
   * ]
   */
    var $filters = null;
    var $records = null;
    var $rungroup = null;
    var $rungroups = null;
    var $assay = null;
    var $assays = null;
    var $plate = null;
    var $plates = null;
    var $uqc = null;
    var $uqcs = null;
    var $project = null;
    var $start = null;
    var $limit = null;
    var $total = null;

    function __construct($id,$query) {

        global $sps;
        if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
            $handle = fopen("php://input", "r");
            $contents = ''; 
            while (!feof($handle)) {
                $contents .= fread($handle, 8192);
            }   
            fclose($handle);
            parse_str($contents, $params);
            if($params['selected']) {
                $selected = $params['selected'];
            }
            if($params['action']) {
                $action = $params['action'];
                if ($action =='import') {
                   $this->doImport($selected);
                }
                if ($action =='ignore') {
                   $this->doIgnore($selected);
                }
            }
            
        }



        // check to see if an apikey has already been generated
        if ($sps->entity =='browser' && isset($_SESSION['apikey'])) {
            $sps->apikey = $_SESSION['apikey'];
        }
        $this->filters = $query;
        
        if(!isset($this->id) && isset($query)) {
            $results = $this->returnResultRecordSets();
            $this->records = $results->records;
            $this->sequenced = $results->sequenced;
        }
        $this->model = $this->records;
    }
    function __destruct() {
        global $sps;
        if ($sps->entity =='browser' && $sps->apikey) {
            $_SESSION['apikey'] = $sps->apikey;
        }
    }
    //  grab the raw results data
    public function returnResultRecordSet() {
        $sets = New ResultsObjects();
        $sets->start = $this->start;
        $sets->limit = $this->limit;
        $sets->rungroup = $this->rungroup;
        $sets->assay = $this->assay;
        $sets->plate = $this->plate;
        $sets->uqc = $this->uqc;
        $sets->rungroups = $set->RetDistinct('id_rungroup');
        $sets->assays = $set->RetDistinct('id_assay');
        $sets->plates = $set->RetDistinct('name_plate');
        $sets->uqcs = $set->RetDistinct('uqc');
        $sets->filters = $this->filters;
        $set->fetchRecords();
        return $set;
    }
    public function returnResultRecordSets() {
        $results = New ResultsObjects();
        $results->start = $this->start;
        $results->limit = $this->limit;
        $results->rungroup = $this->rungroup;
        $results->assay = $this->assay;
        $results->plate = $this->plate;
        $results->uqc = $this->uqc;
        $results->rungroups = $results->RetDistinct('id_rungroup');
        $results->assays = $results->RetDistinct('id_assay');
        $results->plates = $results->RetDistinct('name_plate');
        $results->uqcs = $results->RetDistinct('uqc');
        $results->filters = $this->filters;
        $results->fetchRecords();
        $records = $results->records;
        $results->sequenced = $this->sequence($records,'id_assay');
        return $results;
    }
    public function sequence($records,$by) {
        $occurance = array();
        $return = array();
        $buckets = array();
        $results = array();
        foreach ($records as $record) {
            // if grouping by plate, our bucket has plate
            if($by=='plate') {
                $bucket = array(
                    'assay'=>$record['id_assay'],
                    'instrument'=>$record['id_instrument'],
                    'plate'=>$record['name_plate'],
                    'run'=>$record['run'],
                );
            } else if ($by=='uqc') {
                $bucket = array(
                    'assay'=>$record['id_assay'],
                    'instrument'=>$record['id_instrument'],
                    'layout_plate'=>$record['layout_plate'],
                    'uqc'=>$record['uqc'],
                );
            } else if ($by=='date_assay') {
                $bucket = array(
                    'assay'=>$record['id_assay'],
                    'instrument'=>$record['id_instrument'],
                    'date_assay'=>date_format(date_create($record['datetime_assay']),'Y-m-d'),
                );
            } else {
                $bucket = array(
                    'assay'=>$record['id_assay'],
                    'instrument'=>$record['id_instrument'],
                );
            }
            $group_serial = serialize($bucket);
            // create a hash for this record to catch duplicates
            $subject_serial = serialize(array(
                $record['barcode_source'],
                $record['dilution'],
                $record['id_subject'],
                $record['id_uuid'],
                $record['id_visit'],
                $record['id_barcode'],
                $record['layout_plate'],
                $record['id_study'],
                $record['uqc'],
                $record['name_plate']
            ));
            $group = crc32($group_serial);
            $hash = crc32($subject_serial.$group_serial);
            if (array_key_exists($hash,$occurance)) {
                // add one to occurance array for this hash
                $occurance[$hash]++;
            } else {
                // set occurance value to one for this hash
                $occurance[$hash] = 1;
            }
            $record['hash'] = $hash;
            $record['sequence'] = $occurance[$hash];
            
            $results[$group][] = array('id'=>$record['id']);
            $buckets[$group] = $bucket;
        }
        foreach($results as $key=>$array) {
            $return[] = array_merge($buckets[$key],array('id'=>$key,'records'=>$array)); 
           // $return[$key]['results'] = $array;
        }
        return $return;
    }
    // split an array of results into multiple arrays with something in common
    public function segmentBy($records,$group_by) {
        $segmented = array();
        foreach ($records as $record) {
            $group = $record[$group_by];
            $segmented[$group][] = $record;
        }
            return $segmented;
    }
    public function doImport($ids) {
        foreach ($ids as $id) {
            $this->importResult($id);
        }
    }
    public function importResult($id) {
         global $dbrw,$sps;
         $results_fields = array(
             'id_barcode','id_uuid','id_subject','id_study','id_lab',
             'id_instrument','id_assay','id_visit','id_rungroup','id_retest',
             'value','units','cv','datetime_assay','date_collection',
             'date_visit','qc','uqc','reviewed','calibrator','reagent',
             'cleaner','share','notes','sample_type','shipment_type');
         //convert array to string
         $sql_fields =  '`'.implode('`,`',$results_fields).'`';
         //make statement
         $sql = 'insert into `results` (`id_results_raw`,'.$sql_fields.') (select `id`,'.$sql_fields.' from results_raw where id = '.$id.')';
         $result = mysqli_query($dbrw,$sql);
         if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
         }       
         $results_id = mysqli_insert_id($dbrw);
         $sql = 'insert into `results_import` (`id_results`,`id_results_raw`) values ('.$results_id.','.$id.')';
         $result = mysqli_query($dbrw,$sql);
         if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
         }       
         return true;
         
    }
    public function doIgnore($ids) {
        foreach ($ids as $id) {
            $result = new ResultsObject($id);
            $changed = array('ignore'=>1);
            $result->patchRecord($changed);
        }
    }
}
