<?php
error_reporting(E_ERROR); 
$pw = posix_getpwnam('root');

//$debug = false;
$debug =true; 
if ($debug) {
	$insert = false;
} else {
	$insert = true;
}
$recurse = true;
posix_setuid($pw['uid']);
posix_setgid($pw['gid']);
$pidfile = '/opt/sps/tmp/importqueue.pid';
$alertfile = '/opt/sps/tmp/importalert.txt';
//* to disable a project, comment out its name
$projects[] = array(
	//'name'=>'CricSclerostin',
	'template'=>'CricSclerostin',
	'parser'=>'include/Xlt/CricSclerostin.php',
	'dst'=>'results_raw',
	'tmptable'=>'sclerostin',
        'path'=>'/tmp/CricSclerostin'
);
$projects[] = array(
//	'name'=>'Biocon',
	'template'=>'biocon_batch_2',
	'parser'=>'include/Xlt/Biocon.php',
	'dst'=>'results_raw',
	'tmptable'=>'biocon_batch_2',
        'path'=>'/opt/sps/db/biocon-043014/data'
);
$projects[] = array(
	//'name'=>'Biocon_2',
	'template'=>'biocon_c501',
	'parser'=>'include/Xlt/Biocon.php',
	'dst'=>'results_raw',
	'tmptable'=>'biocon_c501',
        'path'=>'/opt/sps/db/biocon-050214/data'
);
$projects[] = array(
	'name'=>'raderpulls',
	'parser'=>'include/Xlt/import_pull.php',
	'dst'=>'pull_requirements',
	'tmptable'=>'import_pull',
        'path'=>'/tmp/import_pull'
);
$num_processes = count($projects);
$fp = fopen($pidfile, 'w');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
	$now = time();
	$created_time = fileatime($pidfile)."\n";
	$age = round(($now - $created_time)/60);
	$message = "queue already running! started $age minutes ago\n";
	fputs(STDERR, $message);
	exit;
}
$children = array();
for ($i=0; $i<=$num_processes; $i++) {
	if (($pid = pcntl_fork()) == 0) {
		exit(child_main($i));
	} else {
		$children[] = $pid;
	}
}
fwrite($fp, posix_getpid());
foreach ($children as $pid) {
	$pid = pcntl_wait($status);
	if (pcntl_wifexited($status)) {
		$code = pcntl_wexitstatus($status);
		print "pid $pid returned exit code: $code\n";
	} else {
		print "pid $pid was unnaturally terminated\n";
	}
}
fclose($fp);
unlink($pidfile);



function child_main($num) {
	global $debug,$projects;
	$my_pid = getmypid();
	print "Starting number: $num on pid: $my_pid\n";
	while (true) {
       		$loop_start = microtime(true);
		if (count($projects[$num]) > 0 && $projects[$num]['name']) {
			doImport($projects[$num]);
		}
		$loop_end = microtime(true);
		$loop_interval = round(($loop_end - $loop_start)/60);
		if ($debug) {
			print "$path queue took $loop_interval minutes\n";
		}
		sleep(10);
	}
}

function doImport($project) {
    global $limit,$projects,$debug,$insert;
    $root_dir = '/opt/sps';
    $XLTemplate = $project['template'];
    $tmptable = $project['tmptable'];
    $dst = $project['dst'];
    $parser = $root_dir .'/'. $project['parser'];
    $name = $project['name'];
    $path = $project['path'];
    if (is_file($path)) {
        $in_files = array($path);
        $num_files = 1; 
        $limit = 100;
    } else if (is_dir($path)) {
		$processed = $project['path'] . '/processed.txt';
		$pf = fopen($processed, 'a+');
	 	if (!$pf || !flock($pf, LOCK_EX | LOCK_NB)) {
		       print "error: can't get lock on processed file";
    		}
    		while (($buffer = fgets($pf, 4096)) !== false) {
			$previous[] =str_replace(PHP_EOL,'',$buffer);
    		}
		$in_files = fileList($path,'xls',$recurse);
		$num_files = count($in_files);
		if($num_files == 0) {
			$in_files = fileList($path,'xlsx',$recurse);
			$num_files = count($in_files);
		}
		if (!isset($limit))  {
			$limit = $num_files; 
		}       
    }


    print "Processing $limit of $num_files files\n";
    print "Template: $XLTemplate\n";
    print "Parser: $parser\n";
    $docs = array();
    for($i = 0; $i < $limit; $i++) {
        $file = str_replace(PHP_EOL,'',$in_files[$i]);
	if (in_array($file,$previous)) {
	    print "Skipping already imported $file\n";
	} else { 
            $docs[] = $file;
	    if ($insert) {
                fwrite($pf,"$file\n");
	    }
	}

    }
    fclose($pf);
    foreach ($docs as $doc) {
	print "reading $doc \n";
	if (($pid = pcntl_fork()) == 0) {
		exit(child_sub($XLTemplate,$doc,$tmptable,$parser));
	}
	$pid = pcntl_wait($status);
        if (pcntl_wifexited($status)) {
                $code = pcntl_wexitstatus($status);
                print "pid $pid returned exit code: $code\n";
        } else {
                print "pid $pid was unnaturally terminated\n";
        }   
    }
	if (isset($dst) && count($docs) > 0) {
	    print "importing $limit docs\n";
	    if (($pid = pcntl_fork()) == 0) {
		exit(doDbImport($tmptable,$dst,$name));
    	    }
	    $pid = pcntl_wait($status);
            if (pcntl_wifexited($status)) {
                $code = pcntl_wexitstatus($status);
                print "pid $pid returned exit code: $code\n";
            } else {
                print "pid $pid was unnaturally terminated\n";
            }   
	}
}


function doDbImport($tmptable,$dst,$project_name) {
        include_once('/opt/sps/include/lib.php');
	global $dbrw,$debug,$insert;
	mysqli_select_db($dbrw, $config['db_name']);
	//
	// temporary hack!
	$sql = "update $tmptable set uqc = 'standard' where layout_plate like 'STD%'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		exit;
	} 
	$sql = "update $tmptable set uqc = 'control' where layout_plate like 'CTL%'";
	$result = mysqli_query($dbrw,$sql);
	if (!$result) {
		echo 'Could not run query: ' . mysqli_error($dbrw);
		exit;
	} 

	$sql = "insert into `$dst` (id_uuid,id_assay,value,value_measured,value_calculated,cv,position_plate,";
	$sql .= "layout_plate,id_barcode,position_source,barcode_source,datetime_assay,name_plate,id_instrument,";
	$sql .= "project_name,uqc,id_study,id_rungroup,units,id_subject) (select id_uuid,id_assay,value,value_measured,value_calculated,cv,";
	$sql .= "position_plate,layout_plate,id_barcode,position_source,barcode_source,datetime_assay,name_plate,";
	$sql .= "id_instrument,'$project_name',uqc,id_study,id_rungroup,units,id_subject from `$tmptable` ";
	$sql .= "where position_plate REGEXP  '^[A-Z][0-9]' or (id_subject is not null and id_subject != 'unknown')";
	$sql .= ")";
//	if ($debug) {
//               print "$sql";
//	}
	if ($insert) {
		$result = mysqli_query($dbrw,$sql);
		if (!$result) {
			echo 'Could not run query: ' . mysqli_error($dbrw);
			exit;
		} 
	}
	$sql = "truncate table $tmptable";
	$result = mysqli_query($dbrw,$sql);
}

function child_sub($XLTemplate,$doc,$tmptable,$parser) {
        include_once('/opt/sps/include/lib.php');
	global $dbrw,$config,$debug,$insert;
	lib('PHPExcel');
	lib('ranges');
	lib('dbcleanup');
	lib('Xlt');
	include($parser);
}



function fileList($dir,$ext,$recurse){
    $list = array();
    if ($recurse) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $name => $object){
		if (strtolower(pathinfo($object->getFilename(), PATHINFO_EXTENSION)) == strtolower($ext)) {
                $list[] = $name;
            }
        }
    } else {
        foreach (new DirectoryIterator($dir) as $object) {
            if($object->isDot()) continue;
		if (strtolower(pathinfo($object->getFilename(), PATHINFO_EXTENSION)) == strtolower($ext)) {
               $list[] = $object->getPathname();
            }
        }
    }
    return $list;
}
?>
