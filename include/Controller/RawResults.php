<?php
lib('Task');
lib('Model/Results');
class RawResults{
   /** 
   * Returns a results object to guide the user to different actions
   * available on results analysis
   * return:
   *  [
   * post update records for ids provided to controller
   * ]
   */
    //var $records = null;
    function __construct() {
       $idArray = $_POST['selected'];
       $action = $_POST['action'];
       //$this->idArray = $idArray;
       $results = $this->processResults($action,$idArray);
       $records = $results->records;
       $this->records = $records;
       $this->action = $action;
       $this->idArray = $idArray;
    }

    public function processResults($action,$idArray) {
        $results = New ResultsObjects();
        $results->idArrayTable($idArray);
        $results->performAction($action);
        $results->fetchRecords();
        if($results->error) {
            $error = $results->error;
            $this->error = $error;
        }
        return $results;
    }
}
