<?php
lib('dbi');
require_once('REDCap/RestCallRequest.php');


class REDCap{
   /**
   * Returns data for all new subjects for the given study
   * If $improted is true, return already imported subjects,
   * Otherwise, only return new subjects
   * return:
   * [
   *  'subjects' => []
   * ]
   */
  public function retSubjects($imported='false') {
    global $dbrw;
    $subjects = array();
    if (!$imported) {
        $crfSubjects = $this->getCRFsubjects();
        foreach ($crfSubjects as $crfSubject) {
          $id_study = 'CTTF';
          $crfSubject['collect_type'] = 'CTTF_BASE';
          $id_collection = $crfSubject['id_collection'];
          $sql = "select id_subject from `rc_cohort` where rc_collection_id = '$id_collection'";
          $result = mysqli_query($dbrw,$sql);
          if (mysqli_num_rows($result) == '0') {
            $subjects[] = $crfSubject;
          }
        }
    } else {
          $sql = "select id_study,id_subject,rc_collection_id,time_created from `rc_cohort`";
          $result = mysqli_query($dbrw,$sql);
         while ($row = mysqli_fetch_assoc($result)) {
            $subjects[] = $row;
          }
    }

    $data['subjects'] = $subjects;
    return $data;
  }

  public function retUpdatedStudyCollections() {
    global $dbrw;
    $subjects = array();
    $data = array();
    $crfSubjects = $this->getCRFsubjects();
    foreach ($crfSubjects as $crfSubject) {
      $id_study = $crfSubject['id_study'];
      $crfSubject['collect_type'] = 'CTTF_BASE';
      $id_collection = $crfSubject['id_collection'];
      $sql = "select id_study from `rc_cohort` where rc_collection_id = '$id_collection' and id_study != '$id_study'";
      $result = mysqli_query($dbrw,$sql);
      if (mysqli_num_rows($result) == '1') {
        $row = mysqli_fetch_array($result);
        $crfSubject['original_study'] = $row['id_study'];
        $subjects[] = $crfSubject;
      }
    }

    $data['subjects'] = $subjects;
    return $data; 
  }

    /**
    * Returns an sps field name and value for a redcap value:
    * [
    * ]
    */
    public function mapRcParams($rc_name,$param) {
      global $dbrw,$study;
      $params_value = $rc_name;
      $sql = "select params.value from rc_params left join params ";
      $sql .= "on (params.id = rc_params.id_param) ";
      $sql .= "where rc_params.id_study = 'CTTF' and rc_name = '$rc_name' ";
      $sql .= "and param = '$param'";
      $result = mysqli_query($dbrw,$sql);
      if (!$result) {
          echo 'Could not run query: ' . mysqli_error($dbrw);
          return false;
          exit;
      } else {
           while ($row = mysqli_fetch_array($result)) {
               $params_value  = $row['value'];
           }
           return $params_value;
      }
    }

    /**
    * Returns an array of subjectcrf "objects" based on the value of $id_subject:
    * [
    * 'id_visit'=>$redcap_record->subject_id,
    *  'id_subject'=>$redcap_record->subject_id,
    *  'id_subject2'=>$redcap_record->study_subject_id,
    *  'id_collection'=>$redcap_record->collection_id,
    *  'id_study'=>$redcap_record->study_id,
    *  'date_visit'=>$redcap_record->collection_date,
    *  'date_receipt'=>$redcap_record->cttf_date_received,
    *  'collect_type'=>$redcap_record->collect_type
    *  'sample_type' =  from SPS crf table
    *  'quantity' =  from SPS crf table
    * ]
    */
    public function getCRFSpecimens($collection_ids) {
    //TODO - better error handling
      global $dbrw,$id_study;
      if (gettype($collection_ids) != "array" or count($collection_ids) == 0) {
        return -1;
      }

      $specimens = array();

      $crfSubjects = $this->getCRFsubjects($collection_ids);
      foreach ($crfSubjects as $crfSubject) {
        if (in_array($crfSubject['id_collection'], $collection_ids)) {
          $sql = "select sample_type, quantity from crf where id_study = '" . $id_study . "'";
          $result = mysqli_query($dbrw,$sql);
          if (!$result) {
            return -1;
          }
          while ($row = mysqli_fetch_array($result)) {
            $crf_sample_type = $row['sample_type'];
            if (in_array($crf_sample_type, $crfSubject['sample_types'])) {
              $crfSubject['collect_type'] = 'CTTF_BASE';
              $crfSubject['sample_type'] =  $row['sample_type'];
              $crfSubject['quantity'] =  $row['quantity'];
              $specimens[] = $crfSubject;
            }
          }
        }
      }
      return $specimens;
    }

  /**
  * Return data from the CTTFCollections form in REDCap
  * If $collection_ids is specified, only get those collections.  Otherwise, get everything.
  *
  * !!!Hardcoded to ignore collections with a study of 'Pending' or a blank study
  *
  */
  public function getCRFsubjects($collection_ids = null) {
    global $id_study;
    $api_key = $this->getAPIKey($id_study);

    //fields
    $num_collection_type_fields = 12; // this probably shouldn't be hardcoded...
    $collection_type_fields = array();
    for ($i = 1; $i <= $num_collection_type_fields; ++$i) {
      $collection_type_fields[] = "collection_types___" . $i;
    }
//    $fields = array('collection_id', 'subject_id', 'study_subject_id', 'study_id', 'collection_date', 'cttf_date_received',
      //'collection_date', 'cttf_date_received', 'collection_types');
	$fields = array();

    //records
    if (($collection_ids == null) or (gettype($collection_ids) != "array")) {
      $records = array();
    }
    else {
      $records = $collection_ids;
    }

    //query redcap
    $data = array('content' => 'record', 'type' => 'flat', 'format' => 'json', 'rawOrLabel'=>'label','eventName'=>'unique',
     'records'=> $records, 'fields' => $fields, 'token' => $api_key);
    $request = new RestCallRequest("https://redcap.med.upenn.edu/api/", 'POST', $data);
    $request->execute();
    $response = $request->getResponseInfo();

    $type = explode(";", $response['content_type']);
    $contentType = $type[0];
    $json =  $request->getResponseBody();
    $decode = json_decode($json);
    if (count($decode) ==0) {
        $this->error = 'ERROR: Could not connect to REDcap Server';
        print $this->error;
        exit;
    }

    $subjects = array();
    foreach ($decode as $redcap_record) {
       if (isset($redcap_record->subject_id) && strlen($redcap_record->subject_id) > 0
       && ($redcap_record->study_id != 'Pending') && ($redcap_record->study_id != '')) {
        
       if (isset($redcap_record->anatomical_source) && strlen($redcap_record->anatomical_source) > 0) {
           $sample_source = $this->mapRcParams($redcap_record->anatomical_source,'sample_source');
	} else {
           $sample_source = null;
        }
        $sample_types = array();
        foreach ($collection_type_fields as $collection_type_field) {
          if ($redcap_record->$collection_type_field != '') {
            $sample_type = $this->mapRcParams($redcap_record->$collection_type_field,'sample_type');
            if ($sample_type) {
                $sample_types[] = $sample_type;
            }
          }
        }
        $subjects[] = array(
          'id_collection'=>$redcap_record->collection_id,
          'id_subject'=>$redcap_record->subject_id,
          'id_ancillary'=>$redcap_record->study_subject_id,
          'id_study'=>$this->mapRcParams($redcap_record->study_id,'id_study'),
          'id_visit'=>'Visit 0',   //$redcap_record=>time_point
          'date_visit'=>$redcap_record->collection_date,
        //  'date_receipt'=>$redcap_record->cttf_date_received,
          'sample_source'=>$sample_source,
          'sample_types'=>$sample_types
          );
      }
    }
    return $subjects;
  }

  private function getAPIKey($study) {
    global $dbrw;
    $sql = "select e.key from extdb_header as e inner join studies as s on s.id_extdb_header = e.id";
    $result = mysqli_query($dbrw,$sql);
    if (!$result) {
      return -1;
    }
    else {
      $row = mysqli_fetch_array($result);
      return $row['key'];
    }
  }

}
