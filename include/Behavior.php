<?php
lib('Model/Inventory');
class Behavior {
    var $settings = array();
    var $format = array();
    var $fields = array();
    var $task = null;
    var $id_study = null;
    var $autoexecute = false;
    /*
    // database retreival method:
    */
     function __construct() {
         global $sps;
         if (isset($sps->task)) {
             $this->task = $sps->task;
         } 
         if (isset($sps->active_study)) {
             $this->id_study = $sps->active_study->id_study;
         } 
         $this->settings = $this->retSettings();
         foreach ($this->settings as $name=>$setting) {
		if ($setting['autoexecute'] > 0) {
			$this->autoexecute = true;
		}
	}
     }
     public function retTasks() {
        global $dbrw;
        $settings = array();
        $sql = "select task,name from behavior left join behavior_header ";
        $sql .= "on behavior.id_behavior_header = behavior_header.id where (";
        if ($this->id_study) {
            $sql .= "`id_study` = '$this->id_study' or ";
        }
        $sql .= "`id_study`  is null) ";
        $result = mysqli_query($dbrw,$sql);
            if (!$result) {
            return false;
        }   
        while ($row = mysqli_fetch_assoc($result)) {
		$behavior_name = $row['name'];
		$task_name = $row['task'];
                if (!isset($settings[$behavior_name])) {
                    $settings[$behavior_name] = array();
                }
                $settings[$behavior_name][] = $task_name;
        }
        return $settings;
    }
     public function retSettings() {
        global $dbrw;
        $settings = array();
        $sql = "select name,description,autoexecute,function from behavior left join behavior_header ";
        $sql .= "on behavior.id_behavior_header = behavior_header.id where (";
        if ($this->id_study) {
            $sql .= "`id_study` = '$this->id_study' or ";
        }
        $sql .= "`id_study`  is null) and (";
        if ($this->task) {
            $sql .= "`task` = '$this->task' or ";
        }
       $sql .= "`task` is null) ";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                return false;
        }   
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['name']] = array(
                'value'=>$row['name'],
                'description'=>$row['description'],
                'function'=>$row['function'],
                'autoexecute'=>$row['autoexecute'],
            );
        }
        return $settings;
    }
    public function retFormat($object) {
       $format=array();
       if (isset($object->id_subject) && $object->type == 'tube') {
          $x = $object->id_subject;
          $fx = (pi() * $x / 10000000);
          $gx = ((pi() * ($x) * (1 / 10)));
          $r = round(128 * (1 + sin($fx)));
          $g = round(128 * (1 + cos($fx)));
          $b = round(130 + 32 * (1 + cos($gx)));
          $format['bgcolor'] = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
       }
      return $format;
    }
    public function retFilters() {
        global $dbrw;
        $study_filters = array();
        $sql = "select param,value from params left join filters on id_param = params.id where id_study = '$this->id_study'";
        $result = mysqli_query($dbrw,$sql);
        while ($row = mysqli_fetch_array($result)) {
            $field = $row['param'];
            $value = $row['value'];
            $study_filters[$field][] = $value;
        }   
        return $study_filters;
    }   

    /*
    // database storage method:
    // creates a new behavior
    // returns true on success, false otherwise
    */
    public function createBehaviorHeaderRecord () {
        global $dbrw;
        $name = $this->name;
        $description = $this->description;
        $sql = "select id from behavior_header where `name` = '$name' and `description` = '$description'";
        $result = mysqli_query($dbrw,$sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $behavior_header_id  = $row['id'];
            }
        } else { 
            $sql = "insert into behavior_header (name,description) ";
            $sql .= "values ('$name','$description')";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
            echo 'Could not run query: ' . mysqli_error($dbrw);
                    return false;
                    exit;
            }   
            $behavior_header_id =  mysqli_insert_id($dbrw);
        }   
        return $behavior_header_id;
    }


    /*
    // database storage method:
    // enables a behavior for a study and/or task
    // required: behavior_id
    // optional: id_study, task
    */
    public function setBehavior() {
        global $dbrw;
        if ($this->name && $this->description &&
          $id = $this->createBehaviorHeaderRecord()) {
            $sql = "insert into behavior set id_behavior_header = $id";
            if ($this->id_study) {
                $sql .= ',id_study = "'.$this->id_study .'"';
            }
            if ($this->task) {
                $sql .= ',task = "'.$this->task .'"';
            }
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                echo 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }   
        } else {
          return false;
          exit;
        }
        return true;
    }


    /*
    // database storage method:
    // disables a behavior for a study and/or task
    // required: behavior_id
    // optional: id_study, task
    */
    public function disableBehavior() {
    }
}



?>
