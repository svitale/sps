<?php
lib('Study');
lib('Task');
lib('Behavior');
lib('Printer');
class Sps{
    var $apikey = null;
    var $task = null;
    var $active_study = null;
    var $state = null;
    var $entity = null;
    var $username = null;
    var $auth = null;
    var $filters = null;
    var $settings = null;
    var $printer = null;
    var $environment = null;
    function __construct() {
        global $config;
	error_reporting(E_ALL);
        if (!$this->username) {
           print "Error: username has not been set!\n";
           exit;
        }
        $this->state = 'init';
	if (isset($config['environment'])) {
		$this->environment = $config['environment'];
	}
        session_start();
        $auth = New Auth($this->username);
        $behavior = New Behavior();
        if (isset($_SESSION['printer'])) {
             $this->printer = New PrintDev();
             $this->printer = $_SESSION['printer'];
        }
        $auth->studies = $auth->retAllowedStudies();
        if (isset($_SESSION['active_study'])) {
            $study = $_SESSION['active_study'];
            $this->active_study = $study;
        //if they only have one study.. just open that one
        } else if (count($auth->studies) == 1) {
		$studies = $auth->studies;
 		$study = New Study();
                $study->id_study = $studies[0];
                $study->Loader();
		$this->active_study = $study;
         	$_SESSION['active_study']  = $study;
         	$_SESSION['id_study']  = $study->id_study;
	}
        if ($this->active_study) {
            $auth->roles = array_keys($auth->retStudyRoles($study->id_study));
            $behavior->id_study = $this->active_study->id_study;
        }
        $this->auth = $auth;
        if (isset($_SESSION['task'])) {
            $this->task = $_SESSION['task'];
        }
        if (isset($_GET['task']) && $_GET['task'] != $this->task) {
            $this->task = $_GET['task'];
            $this->resetSession();
        }
        $behavior->task = $this->task;
        if (isset($_SESSION['devmode'])) {
            $this->devmode = true;
        } 
        if (isset($_SESSION['filters'])) {
            $this->filters = $_SESSION['filters'];
        }
        if (isset($_SESSION['highlighted'])) {
            $this->highlighted = $_SESSION['highlighted'];
        }
        $this->settings = $behavior->retSettings();
        $this->task_behavior = $behavior->retTasks();
   }
   public function retJson() {
        $study = $this->active_study;
        return json_encode($this);
   }
   public function setFilter($variable,$value) {
       if (!$this->filters) {
           $this->filters = array();
       }
       if ($value == '') {
           unset($this->filters[$variable]);
       } else {
           $this->filters[$variable] = $value;
       }
       $this->state = 'update';

       //TODO: replace all code that looks for this directly
       if ($variable != 'id_study') {
          if ($value == '') {
           unset($_SESSION[$variable]);
          } else {
           $_SESSION[$variable] = $value;
          }
       }

       $_SESSION['filters'] = $this->filters;
       return true;
   }
   public function setActiveStudy($study) {
      $this->resetSession();
      if ($study) {
         $_SESSION['active_study']  = $study;
         $_SESSION['id_study']  = $study->id_study;
      } else {
        unset($_SESSION['id_study']);
        unset($_SESSION['active_study']);
      }
      return true;
    }
    public function resetSession() {
        global $dbrw;
        if (isset($_SESSION['printer'])) {
                $printer = $_SESSION['printer'];
        }
        if (isset($_SESSION['tmptable'])) {
                $tmptable = $_SESSION['tmptable'];
                $sql = "show tables like '$tmptable'";
                $result = mysqli_query($dbrw,$sql);
                if(mysqli_num_rows($result)) {
                        $sql = "drop table `$tmptable`";
                        $result = mysqli_query($dbrw,$sql);
                        if (!$result) {
                                echo 'Could not run query: ' . mysqli_error();
                                exit;
                        }
                }
        }
        session_destroy();
        session_start();
        $_SESSION['username'] = $this->username;
        if ($this->active_study) {  
            $_SESSION['active_study'] = $this->active_study;
            $_SESSION['id_study'] = $this->active_study->id_study;
        }
        if (isset($printer)) {
            $_SESSION['printer'] = $printer;
        }
        if ($this->task) {
            $_SESSION['task'] = $this->task;
        }
	if(isset($this->highlighted) && $this->highlighted) {
		unset($this->highlighted);
	}
        $this->state = 'reset';
    }
    //cache the object and return a key for its retrieval
    public function tokenize() {
        global $config;
        if (!$this->apikey) {
            $this->apikey = new_uuid();
        }

        // debug and local dev
        if ($config['apc_enabled'] == false) {
          return $this->apikey;
        }

        $tokenized = $this->castToClass('Api', $this);
        if (apc_store($this->apikey,$tokenized,0)) {
            return $this->apikey;
        } else {
          $this->error =  "unable to store token";
          //  return false;
       }
     }
     public function castToClass($class, $object) {
          return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . ':"' . $class . '"', serialize($object)));
     }
}
