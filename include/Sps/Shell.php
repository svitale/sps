<?php
class Shell extends Sps{
    function __construct() {
        $this->entity = 'shell';
        $this->username = 'script';
        if (!isset($_SERVER['SHELL'])) {
             //make sure we're running from a shell!
             header('HTTP/1.1 401 Unauthorized');
             print "Error: this library is only intended for Shell use!";
             exit;
        }
        parent::__construct();
        $auth = $this->auth;
        $auth->roles = array('lab');
	$this->auth = $auth;
    }
    function print_label($data) {
        lib('Printer');
        $job = New PrintJobs;
        if (isset($data['id'])) {
            $id = $data['id'];
            $job->printer_id =  '4';
            $job->copies = 1;
            $status = $job->getPrintJob($id);
	} else {
           $job->printer_id =  '4';
           $job->copies = 1;
           $status = $job->spoolStaticJob($data);
	}
        return $status;

     }
    function data_array() {
	if ($_POST['workbook'] == 'inventory') {
		return inventory_array();
	} else if ($_POST['workbook'] == 'results') {
		return results_array();
	} else {
		return array();
	}
    }
    function results_array() {
	global $_POST;
	if(isset($_POST['report'])) {
		$report = $_POST['report'];
	} else {
		return false;				
		exit;
	}
	if(isset($_POST['study']) && $_POST['study'] != 'any') {
			$params[] = 'id_study=\''.$_POST['study'].'\'';
	}
			$params[] = "date_format(results.`datetime_assay`, '%Y-%m')=date_format(now(), '%Y-%m') ";
	if ($report == 'currentmonth') {
		$sql = "select count(*) records,id_assay assayname,id_study from results ";
		if(isset($params)) {
			$sql .= 'where ' . implode($params," and ");
		}
		$sql .= ' group by id_study,id_assay';
	} else {
		exit;
	}
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$returnArray[] = $row;
	}
	if (isset($returnArray)) {
		return $returnArray;
	} else {
		return false;
	}
    }
    function inventory_array() {
         global $sps;
         $id_study = $sps->active_study->id_study;
                $report = null;
	if ($report == 'alqfreq') {
		$sql = "select sample_type sampletype,shipment_type shipmenttype,subject,id_visit visit,quant,total,tcl_1,tcl_2,niddk from distro ";
	} elseif ($report == 'samplesbysubject') {
		$sql = "select id_study study,sample_type sampletype,shipment_type shipmenttype,subject,id_visit visit,sum(total) total from distro ";
		$sql .= ' group by id_study,sample_type,shipment_type,subject,id_visit order by id_study,subject,id_visit,sample_type,shipment_type';
	} else {
		$sql = 'select sample_type sampletype,shipment_type shipmenttype,id_visit visit,total,tcl_1,tcl_2,niddk,quant from distro_by_site ';
		$sql .= "where id_study = '$id_study'";
                foreach ($sps->filters as $filter=>$val) {
		$sql .= "and $filter = '$val'";
                }
	}
//*
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$returnArray[] = $row;
	}
	if (isset($returnArray)) {
		return $returnArray;
	} else {
		return false;
	}
    }
    // get command line arguments
    function getArgs() {
     $args = $_SERVER['argv'];
     $out = array();
     $last_arg = null;
        for($i = 1, $il = sizeof($args); $i < $il; $i++) {
            if( (bool)preg_match("/^--(.+)/", $args[$i], $match) ) { 
             $parts = explode("=", $match[1]);
             $key = preg_replace("/[^a-z0-9]+/", "", $parts[0]);
                if(isset($parts[1])) {
                 $out[$key] = $parts[1];    
                }   
                else {
                 $out[$key] = true;    
                }   
             $last_arg = $key;
            }   
            else if( (bool)preg_match("/^-([a-zA-Z0-9]+)/", $args[$i], $match) ) { 
                for( $j = 0, $jl = strlen($match[1]); $j < $jl; $j++ ) { 
                 $key = $match[1]{$j};
                 $out[$key] = true;
                }   
             $last_arg = $key;
            }   
            else if($last_arg !== null) {
             $out[$last_arg] = $args[$i];
            }   
        }   
     return $out;
    }
}
?>
