<?php
class Api extends Sps{
    function __construct() {
        $this->entity = 'api';
        if (isset($_POST['apikey'])) {
            $apikey = $_POST['apikey'];
        } else {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
        //static api keys
        if ($apikey  == 'cvTIfIStv_kZpmZ2ikAKQ6pZ85d2F4XU'){
            $this->username = 'api';
            parent::__construct();
        //dynamic api keys
        } else if (isUuid($apikey)) {
            $tokenized = apc_fetch($apikey);
            if (!$tokenized) {
                header('HTTP/1.1 401 Unauthorized');
                exit;
            }
            $this->username = $tokenized->username;
            parent::__construct();
            $this->active_study = $tokenized->active_study;
            $this->filters = $tokenized->filters;
            $this->task = $tokenized->task;
/*
            $this->state = $tokenized->state;
            $this->username = $tokenized->username;
            $this->auth = $tokenized->auth;
            $this->filters = $tokenized->filters;
            $this->settings = $tokenized->settings;
            $this->printer = $tokenized->printer;
            foreach ($this->filters as $key=>$value) {
                $_SESSION[$key] = $value;
            }
*/
        } else {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
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
}
?>
