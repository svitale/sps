<?php
#$pw = posix_getpwnam('opt');
$debug = false;
ini_set('session.save_path','/opt/sps/spool/session');
#posix_setuid($pw['uid']);
#posix_setgid($pw['gid']);
$pidfile = '/opt/sps/spool/printqueue.pid';
$alertfile = '/opt/sps/spool/alert.txt';
$num_printers = 1;
$fp = fopen($pidfile, 'w');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
	$now = time();
	$created_time = fileatime($pidfile)."\n";
	$age = round(($now - $created_time)/60);
	$message = "print queue already running! started $age minutes ago\n";
	fputs(STDERR, $message);
	exit;
}
//print_r($printers);
$children = array();
for ($i=0; $i<=$num_printers; $i++) {
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
function child_main($num) {
	global $debug;
	include_once('/opt/sps/include/lib.php');
	lib('Printer');
	$print_dev = New PrintDev;
	$printers = $print_dev->listPrinters();
	$printer_names = array_keys($print_dev->listPrinters());
	if (!isset($printer_names[$num])) {
		exit;
	} else {
            $printer_name = $printer_names[$num];
        }
	$printer = $printers[$printer_name];
	$my_pid = getmypid();
	print 'Starting '. $printer['name'] ." on pid: $my_pid\n";
	while (true) {
       		$loop_start = microtime(true);
                $PrintJobs = New PrintJobs;
		$jobs = $PrintJobs->listPrintJobs($printer['id']);
                $net = New PrintNet; 
		if (count($jobs) > 0) {
			$net->setPrinter($printer);
			foreach ($jobs as $job) {
				$retry = 0;
				$net->setJob($job);
				$net->setMessage($printer['name']);
				$job['status' ] = 'pending';	
				$PrintJobs->updatePrintJobDb($job);
                                while (!($net->send())){
					$retry++;
					print "Error: retry $retry\n";
					sleep(1);
                                        if($retry == 5) {
						continue(2);
					}
				}
				$job['status' ] = $net->job['status'];	
				$PrintJobs->updatePrintJobDb($job);
			}
		}
		$loop_end = microtime(true);
		$loop_interval = round(($loop_end - $loop_start)/60);
		if ($debug) {
			print 'queue for '. $printer['name'] . "took $loop_interval minutes\n";
		}
		sleep(1);
	}
}
fclose($fp);
unlink($pidfile);
?>
