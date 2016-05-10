<?php
lib('Task');
lib('Model/Projects');
class Projects extends Task{
   /** 
   * Returns a projects object to guide the user through adding
   * deleting or altering projects
   * return:
   *  [
   * TODO:  Figure out what we're going to return
   * ]
   */
    var $active_project = null;
    var $tasks = array('results','crf');
    var $required_params = null;
    var $optional_params = null;
    function __construct() {
        if (isset($_SESSION['active_project'])) {
            $this->active_project = $_SESSION['active_project'];
        }   
    }   
    function __destruct() {
        if($this->active_project) { 
            $_SESSION['active_project'] = $this->active_project;
        }
    }   
    //returns a list of valid projects
    public function retList() {
        global $sps;
        $filters = array();
        if ($sps->active_study->id_study != 'any')  {
             $filters['id_study'] = $sps->active_study->id_study;
        }
        if ($sps->task != 'projects') {
             $filters['task'] = $sps->task;
        }
        $fields = array('id','name','description');
        $db = New Db(); 
        $list = $db->retMultiRecords('project_header',$fields,$filters);
        return $list;
    }
    public function retNewProjectId() {
        global $sps;
        $db = New Db(); 
        $fields = array();
        if ($sps->task && $sps->task != 'projects') {
             $fields['task'] = $sps->task;
        }   
        if ($sps->active_study->id_study != 'any') {
             $fields['id_study'] = $sps->active_study->id_study;
        }   
        $id = $db->insert('project_header',$fields);
        if ($id) { 
            return $id;
        } else {
            return false;
        }   
    } 
    public function startNewProject() {
        $id = $this->retNewProjectId();
        if (!$id) { 
            return false;
        }
        $project = new ProjectObject();
        $project->id =  $id;
        $project->Loader();
        $this->active_project = $project;
    }
}
