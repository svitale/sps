<?php
error_reporting(E_ERROR | E_PARSE);
class PrintDev{
        var $printer_id = null;
        var $printer_name = null;
        var $printer_model = null;
        var $printer_make = null;
        var $printer_location = null;
        var $printer_ip = null;
    public function listPrinters() {
	lib('dbi');
        global $dbrw,$sps;
        $sql = "select print_devices.id,name,connection,make,model,ip from print_filter ";
	$sql .= "left join print_devices on print_filter.print_dev_id = print_devices.id ";
        if (isset($sps) && isset($sps->active_study)) {
                $id_study = $sps->active_study->id_study;
	 	$sql .= "where print_filter.study_id = '$id_study' ";
        }
	$sql .= "group by print_devices.id ";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            echo 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['name'];
            $printers[$name] = $row;
        }
	return $printers;
    }
    public function setPrinter() {
        $_SESSION['printer'] = $this;
    }
    public function getPrinter() {
	lib('dbi');
        global $dbrw,$id_study;
        $sql = "select id,name,location,make,model,connection,ip from  print_devices ";
	if ($this->printer_id) {
             $sql .= "where id = $this->printer_id";
	} else if ($this->printer_name) {
             $sql .= "where name = '$this->printer_name'";
        }
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            echo 'Could not run query: ' . mysqli_error($dbrw);
            return false;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $printer = $row;
        }
	$this->printer_id = $printer['id'];
	$this->printer_name = $printer['name'];
	$this->printer_location = $printer['location'];
	$this->printer_make = $printer['make'];
	$this->printer_model = $printer['model'];
	$this->printer_connection = $printer['connection'];
	$this->printer_ip = $printer['ip'];
        return true;
    }
}


class PrintJobs{
     var $job = array();
     var $type= null;
     var $subject= null;
     var $printer = array();
     var $spool_dir = null;
     var $job_dir = null;
     function __construct() {
         $this->spool_dir = $GLOBALS['root_dir'] . '/spool';
     }
     public function listPrintJobs($printer_id) {
        lib('dbi');
	global $dbrw;
	$print_jobs = array();
	$sql = "select print_spool.id from print_spool left join print_devices ";
	$sql .= "on print_spool.print_dev_id  = print_devices.id ";
	$sql .= "where print_devices.id = '$printer_id' ";
	$sql .= "and print_spool.status = 'init'";

	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
	}
	while ($row = mysqli_fetch_assoc($result)) {
		$print_jobs[] = $row;
	}
	return $print_jobs;
    }

    public function purgeAllPrintJobs() {
        lib('dbi');
	global $dbrw;
	$sql = 'select id,printer_name,username,status from print_spool';
	$result = mysqli_query($sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
	}
	while ($row = mysqli_fetch_array($result)) {
		$job['id'] = $row['id'];
		print 'purging '.$job['id'] . "\n";
	}
    }
    public function updatePrintJobDb($job) {
        lib('dbi');
	global $dbrw;
	$sql = 'update print_spool set status = "' . $job['status'] .'" where id = ' . $job['id'];
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
	} else {
		return true;
	}
    }
    public function getPrintJob($id) {
        lib('dbi');
	global $dbrw;
	$job = array();
	$sql = 'select id,print_dev_id,username,status,timestamp from print_spool where id = ' . $id;
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
	}
	while ($row = mysqli_fetch_array($result)) {
		$job['id'] = $row['id'];
		$job['print_dev_id'] = $row['print_dev_id'];
		$job['username'] = $row['username'];
		$job['status'] = $row['status'];
		$job['timestamp'] = $row['timestamp'];
	}
        if (count($job) > 0) {
	    //$job['num_labels'] = 0;
	    $job['job_dir'] = $this->spool_dir . '/'. $job['id'];
	    return $job;
        } else {
           return false;
        }
    }
    public function createPrintJob() {
        lib('dbi');
	global $dbrw;
	$job = array();
	$job['status'] = 'init';
	$job['num_labels'] = '0';
	if (isset($_SESSION['username'])) {
		$job['username'] = $_SESSION['username'];
	} else {
		$job['username'] = 'automated';
	}
	$sql = 'insert into print_spool (print_dev_id,username,status) values ("' . $this->printer_id .  '","' . $job['username'] . '","'. $job['status'] . '")';

	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		return false;
		exit;
	} else {
		$job['id'] = mysqli_insert_id($dbrw);
	}
	$job['job_dir'] = $this->spool_dir . '/'. $job['id'];
	if (mkdir($job['job_dir'],0777)) {
                chmod($job['job_dir'], 0777);
		return $job;
	} else {

		return false;
	}
    }

        public function spoolStaticJob($data, $job=null) {
        lib('dbi');
	if (is_null($job)) {
		$job = $this->createPrintJob();
	}
	$job['num_labels'] = 1;
	if ($job['status'] != 'init' &&  $job['status'] != 'spooled') {
		print "unexpected job status in spool:" . $job['status'];
		$job['status'] = 'failed';
		return $job;
		exit;
	} 
        if (!is_array($data)) {
		$job['status'] = 'failed';
		return $job;
		exit;
	}
	if (isset($data['id_uuid'])) {
        	$id_uuid = $data['id_uuid'];
	} else {
		$id_uuid = "-------------------------";
	}
	if (isset($data['id_subject'])) {
        	$id_subject = $data['id_subject'];
	} else {
		$id_subject = "";
	}
	if (isset($data['sequence'])) {
        	$sequence = $data['sequence'];
	} else {
		$sequence = "";
	}
	if (isset($data['id_alq'])) {
        	$id_alq = $data['id_alq'];
	} else {
		$id_alq = "";
	}
	if (isset($data['id_study'])) {
        	$id_study = $data['id_study'];
	} else {
                $id_study = $sps->active_study->id_study;
	}
	if (isset($data['sample_type'])) {
        	$sample_type= $data['sample_type'];
	} else {
		$sample_type = "";
	}
	if (isset($data['shipment_type'])) {
        	$shipment_type = $data['shipment_type'];
	}
	$type = 'tube';
	$printfile = sprintf("%05d", $job['num_labels']) . '.txt';
	$handling = fopen($job['job_dir'] . '/' . $printfile, 'w');
	$template_path = $GLOBALS['root_dir'] . '/include/Printer/drivers';
        $printdev = New PrintDev;
      	$printdev->printer_id = $this->printer_id;
        $printdev->getPrinter();
        $template = "$template_path/$printdev->printer_make/$printdev->printer_model/bystudy/$id_study/$type".'.php';
        if (!file_exists($template)) {
	        $template = "$template_path/$printdev->printer_make/$printdev->printer_model/$type".'.php';
        }
	include ($template);
	fwrite($handling, $labelData);
        chmod($job['job_dir'] . '/' . $printfile, 0777);
	$job['status'] = 'spooled';
	return $job;	
    }

        //TODO: Clean up this mess
        public function spoolCommandLabels($labels) {
        lib('dbi');
        global $dbrw,$sps;
        if (is_null($job)) {
                $job = $this->createPrintJob();
        }   
        if ($job['status'] != 'init' &&  $job['status'] != 'spooled') {
                print "unexpected job status in spool:" . $job['status'];
                $job['status'] = 'failed';
                return $job;
                exit;
        }   
        foreach ($labels as $label) {
                $name = $label['name'];
                $value = $label['value'];
                $command = $label['command'];
                $copies = 1;
                $type = 'function';
                $job['num_labels'] =  $job['num_labels'] + 1;
                $printfile = sprintf("%05d", $job['num_labels']) . '.txt';
                $handling = fopen($job['job_dir'] . '/' . $printfile, 'w');
                $template_path = $GLOBALS['root_dir'] . '/include/Printer/drivers';
                $printdev = New PrintDev;
                $printdev->printer_id = $this->printer_id;
                $printdev->getPrinter();
                $template = "$template_path/$printdev->printer_make/$printdev->printer_model/$type".'.php';
                include ($template);
                fwrite($handling, $labelData);
                chmod($job['job_dir'] . '/' . $printfile, 0777);
        }   
        $job['status'] = 'spooled';
        return $job;    
    }  

        //TODO: Clean up this mess
        public function spoolPrintJob($id, $table, $job=null) {
        lib('dbi');
	global $dbrw,$sps;
	if (is_null($job)) {
		$job = $this->createPrintJob();
	}
	if ($job['status'] != 'init' &&  $job['status'] != 'spooled') {
		print "unexpected job status in spool:" . $job['status'];
		$job['status'] = 'failed';
		return $job;
		exit;
	} 
	$sql = "SELECT subjects.*,$table.* FROM $table left join subjects on ($table.id_subject = subjects.id_subject and $table.id_study = subjects.id_study) WHERE $table.`id` = '$id'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		$sql ="SELECT $table.* FROM $table  WHERE $table.`id` = '$id' order by id";
		$result = mysqli_query($dbrw,$sql);
	}
	while ($row = mysqli_fetch_array($result)) {
			$copies = 1;
		$job['num_labels'] =  $job['num_labels'] + 1;
		$id_uuid = "---";
		$id_subject = "---";
		$id_visit = "---";
		$id_encounter = "---";
		$encounter_code = "---";
		$id_alq = "---";
		$sequence = "---";
		$id_study = "---";
		$sample_type = "---";
		$date_visit = "---";
		$date_birth = "-";
		$shipment_type = "---";
		$gender = "-";
                if (isset($row['label_text'])) {
                    $label_text = $row['label_text'];
                } else {
                    $label_text = "";
                }
if (($job['num_labels'] % 2) == 1) {
$is_odd = true;
} else {
$is_odd = false; 
}
/*
                if (isset($row['id_parent']) && isUuid($row['id_parent'])) {
                    $aliquot_date = date('Y-m-d');;
                } else {
		    $aliquot_date = '';
		}
*/
                $print_date = date('Y-m-d');;
		$id_uuid = $row['id_uuid'];
		$id_batch = $row['id_batch'];
		$quant_init = $row['quant_init'] * 1000 . ' ul';
		$id_subject = $row['id_subject'];
		if ($table == 'items') {
			$destination = $row['destination'];
		}
		$id_visit = $row['id_visit'];
		$id_encounter = $row['id_encounter'];
		if (isset($row['encounter_code'])) {
			$encounter_code = $row['encounter_code'];
		} else {
			$encounter_code = substr($row['id_subject'], 0, 8);
		}

		$id_alq = $row['id_alq'];
		$sequence = $row['sequence'];
	        if (isset($row['id_study'])) {
                    $id_study = $row['id_study'];
                } else {
                    $id_study = $sps->active_study->id_study;
	        }
		$sample_type = $row['sample_type'];
		$sample_source = $row['sample_source'];
		$id_ancillary = $row['id_ancillary'];
		if(isset($row['date_birth'])) {
			$date_birth = $row['date_birth'];
		}
		if(isset($row['gender'])) {
			$gender = $row['gender'];
		}
		$type = $row['type'];
		$date_visit = $row['date_visit'];
		$label_text = $row['label_text'];
                $label_format = $type;
		$shipment_type = $row['shipment_type'];
		if (($table == 'items') && ($type == 'shelf')) {
			$freezer_result = mysqli_query($dbrw,"SELECT freezer,subdiv1 FROM locations WHERE `id_item` = '$id'");
			if (!$freezer_result) {
				echo 'Could not run query: ' . mysqli_error($dbrw);
				$job['status'] = 'failed';
				return $job;	
				exit;
			}
			while ($freezer_row = mysqli_fetch_array($freezer_result)) {
				$shelf = $freezer_row['subdiv1'];
				$freezer = $freezer_row['freezer'];
			}
		}
		if (($table == 'items') && ($type == 'box')) {
                    if($row['divX'] == '-8' && $row['divY'] == '12') {
                        $label_format = 'plate';
                    }
                }
		$date = strftime('%m/%d/%Y', strtotime($date_visit));
                if (isset($_SESSION['blind_one']) && $id_alq == $_SESSION['blind_one']) {
                
                    $id_subject = '';
                    $id_visit = '';
                    $date = '';
                }
		$printfile = sprintf("%05d", $job['num_labels']) . '.txt';
		$handling = fopen($job['job_dir'] . '/' . $printfile, 'w');
		$uuidShort = substr($id_uuid, 0, 8);
		$template_path = $GLOBALS['root_dir'] . '/include/Printer/drivers';
	        $printdev = New PrintDev;
        	$printdev->printer_id = $this->printer_id;
                $printdev->getPrinter();
                $template = "$template_path/$printdev->printer_make/$printdev->printer_model/bystudy/".$id_study."/$label_format".'.php';
                if (!file_exists($template)) {
	            $template = "$template_path/$printdev->printer_make/$printdev->printer_model/$type".'.php';
                }
		include ($template);
		fwrite($handling, $labelData);
                chmod($job['job_dir'] . '/' . $printfile, 0777);
	}
	mysqli_free_result($result);
	$job['status'] = 'spooled';
	return $job;	
    }


    public function batchPrintJob($id_batch) {
        lib('dbi');
	global $dbrw;
	$job = $this->createPrintJob();
	$postUuid = $id_batch;
	$sql = "SELECT id FROM `batch_quality` WHERE `id_batch` = '$id_batch' ";
        if ($this->type == 'batchdaughters') {
           $sql .= "and id_parent != '0'";
           if ($this->subject) {
               $sql .= "and id_subject = '$this->subject'";
            }
            $sql .= "order by id_subject,sample_type,shipment_type,id";
        } else {
            $sql .= "order by id";
        }
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		exit;
	}
	if (mysqli_num_rows($result) == 0) {
		return false;
		exit;
	}
	while ($row = mysqli_fetch_array($result)) {
		$job = $this->spoolPrintJob($row['id'], 'batch_quality', $job);
		if ($job['status'] == 'failed') {
			print "Failure in printing batch";
			exit;
		}
	}
    return $job;
    }

}

class PrintNet {
    var $spool_dir = null;
    // Private Variables
    private $hostAddress;
    private $hostPort;
    private $hostMessage;
    // Class Constructor
    public function __construct() {
        $this->hostAddress="127.0.0.1";
        $this->hostPort='21';
        $this->protocol='ftp';
	$this->ftpuser = 'root';
	$this->ftppass = 'root';
        $this->labelData=array();
        $this->passive=true;
        $this->spool_dir = $GLOBALS['root_dir'] . '/spool';
    }
    // Class Destructor
    public function __destruct() {
    }
 
    // Set the address of the printer
    // Can be full name or IP address
    public function setJob($job){
        $this->job=$job;
    }
    // Set Port number
    public function setPrinter($printer){
        if ($printer['connection'] == 'jetdirect') {
            $this->hostPort='9100';
            $this->protocol = 'stream';
	}
        $this->hostAddress=$printer['ip'];
        if($printer['model'] == 'BBP11') {
            $this->passive=false;
        }
    }
    // Set message to send
    public function setMessage($msg){
        $this->hostMessage=$msg;
    }
    public function send(){
        if ($this->protocol === 'stream') {
    		return $this->socketSend();
	} else {
    		return $this->ftpSend();
	}
    }

    // ftp file to printer
    public function ftpSend(){
        print 'sending job #'.$this->job['id'].' to '.$this->hostAddress."\n";
        $service_port=$this->hostPort;
        $address=gethostbyname($this->hostAddress);
	// establish ftp connection to printer
	$conn_id = ftp_connect($address);
	if(!$conn_id) {
                echo "couldn't connect to printer\n";
 		$this->job['status'] = 'failed';
		return false;
	}
        $login_result = ftp_login($conn_id, $this->ftpuser, $this->ftppass); 
	if ($login_result != 1) {
                echo "couldn't connect to printer\n";
 		$this->job['status'] = 'failed';
		return false;
        }
        ftp_pasv($conn_id, $this->passive);
	$jobDir = $this->spool_dir . '/' . $this->job['id'];
	$labelfiles = $jobDir  . '/*.txt';
        //find label files
	foreach(glob($labelfiles) as $labelfile) {
               	try  {
                        $shortname = basename($labelfile);
			ftp_put($conn_id, '/execute/' . $shortname, $labelfile, FTP_ASCII); 
			unlink($labelfile);
		} catch (Exception $e) {
			print "There was a problem printing $labelfile\n";
			print_r($e); 
			$this->job['status'] = 'failed';
			usleep('500');
			return false;
               	}
	}

 	// close the connection 
 	ftp_close($conn_id); 

       if(rmdir($jobDir)) {
               $this->job['status'] = 'finished';
               return true;
       } else {
               $this->job['status'] = 'failed';
               return false;
       }
    }
    // Connect and send message to printer
    public function socketSend(){
        print 'sending job #'.$this->job['id'].' to '.$this->hostAddress."\n";
        $service_port=$this->hostPort;
        $address=gethostbyname($this->hostAddress);
	// establish socket connection to printer
        $socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if ($socket<0) {
           echo "Error: ".socket_strerror($socket)."\n";
           return false;
        }
        $result=socket_connect($socket,$address,$service_port);
        if ($result<0) {
            echo "Error: ($result) ".socket_strerror($result)."\n";
            return false;
        }
	$jobDir = $this->spool_dir . '/' . $this->job['id'];
	$labelfiles = $jobDir  . '/*.txt';
        //find label files
	foreach(glob($labelfiles) as $labelfile) {
		while (true) {
        		//write file to printer
        		$in=file_get_contents($labelfile);
       			if (socket_write($socket,$in,strlen($in))) {
				unlink($labelfile);
				continue 2;
			} else {
				echo "There was a problem printing $labelfile\n";
				$this->job['status'] = 'failed';
				return false;
			}
              }
	}

	rmdir($jobDir);
        socket_close($socket);
	$this->job['status'] = 'finished';
	return true;
    }
}
