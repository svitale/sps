<?php
lib('dbi');
class Project{
    //
    //
    // 
    var $id = null;
    var $type = null;
    var $study = null;
    var $params = null;
    var $types = array('resultsanalysis'=>'Results Analysis','inventoryimport'=>"Inventory Import");
    var $html = null;
    var $message = null;
    var $form = null;
    var $required_params = array();
    var $optional_params = array();
    public function initialize() {
        global $dbrw;
        if (!isset($this->id)) {
            $sql = "insert into `project_header` set start_datetime = now()";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
             print 'Could not run query: ' . mysqli_error();
             return false;
             exit;
            } else {
              $this->id = mysqli_insert_id($dbrw);
              $this->log = "initialize blank project with id: $this->id";
            }
	}
        if (is_null($this->type)) {
             $this->html = $this->selectType();
             $this->log = "select type";
        } else if (is_null($this->study)) {
             $this->html = $this->selectStudy();
             $this->log = "select study";
        } else if (is_null($this->params)) {
             $this->form = $this->selectParams();
             $this->log = "select params";
        } else {
             $this->log = "nothing left to do";
	}
    return $this;
   }
   public function selectType() {
        $html = '';
        $html .= '<div id="radioset">';
	foreach ($this->types as $key=>$name) {
        $html .= '<input type="radio" id="'.$key.'" value ="'.$key.'" name="type">';
	$html .= '<label for="'.$key.'">'.$name.'</label>';
	}
	$html .= '</div>';
        $html .= ' </form>';
        return $html;
   }
   public function selectStudy() {
	$username = $_SESSION['username'];
	$roles = studyRoles($username);
	$studies = array_keys($roles);
	$html =  "<div>Study:</div>";
        $html .= '<div id="radioset">';
	if (count($studies) ==0 ) {
		return "Error:  You are not permitted to access any studies";
		exit;
	} else {
          $html .= '<div id="radioset">';
          foreach ($studies as $study) {
          $html .= '<input type="radio" id="'.$study.'" value ="'.$study.'" name="study">';
          $html .= '<label for="'.$study.'">'.$study.'</label>';
	}
	$html .= '</div>';
        $html .= ' </form>';
        }
	return $html;
}
   public function selectParams() {
	if ($this->type = 'resultsanalysis') {
          $this->required_params = array('id_study');
          $this->optional_params = array('shipment_type','sample_type','id_visit','id_instrument','id_lab');
       } else {
           $this->required_params = array('id_study','shipment_type','sample_type','id_visit');
       }
          $html = '';
          foreach ($this->required_params as $param) {
            $form['required'][$param] = $this->selectParam($param);
          }
          foreach ($this->optional_params as $param) {
            $form['optional'][$param] = $this->selectParam($param);
          }
          return $form;
   }
   public function selectParam($param) {
        global $dbrw;
	$sql= "select value,id_param from filters left join params on (filters.id_param = params.id)";
	$sql .= "where id_study like '" . $this->study . "' and param = '" . $param . "'";
	$sql .= ' group by params.id';
	$sql .= " order by id_param";
	$result = mysqli_query($dbrw,$sql);
        if (!$result) {
         print 'Could not run query: ' . mysqli_error();
         return false;
         exit;
         } else {
           while ($row = mysqli_fetch_array($result)) {
             $id = $row['id_param'];
             $value = $row['value'];
             $returnArray[$id] = $value;
           }
         }
//	$html =  "<div>$param</div>";
 //       $html .= '<div id="radioset">';
//	if (count($returnArray) ==0 ) {
//		return "Error:  This study does not currently have any $param params defined";
//		exit;
//	} else {
 //         $html .= '<div id="radioset">';
//          foreach ($returnArray as $params)  {
//          $html .= '<input type="radio" id="'.$params['id_param'].'" value ="'.$params['id_param'].'" name="params">';
 //         $html .= '<label for="'.$params['id_param'].'">'.$params['value'].'</label>';
//	}
//	$html .= '</div>';
 //       $html .= ' </form>';
//        }
        return $returnArray;
   }
}
