<?php
	//todo - consolidate with lib/barcodes aliquot function
    lib('Process');
    if (($_SESSION['Detailid'] > 0)) {
	$daughters = 1;
        $invObject = New InventoryObject();
        $invObject->table = $_SESSION['DetailpostTable'];
        $invObject->id = $_SESSION['Detailid'];
        $invObject->fetcher();
        $process = New Process();
        $process->tmptable  = 'batch_quality';
        $daughter_ids = $process->aliquotTube($invObject,$daughters);
        
        if ($sps->task == 'crf') {
		print '<SCRIPT language="JavaScript">';
		print "window.location.reload();";
	//	print '</SCRIPT>';
        } else {
            if (!$sps->printer) {
                print "error: no printer selected";
                exit;
            }    
            $printer = New PrintDev();
            $printer = $sps->printer;
            $spooler = New PrintJobs();
            $spooler->printer_id =  $printer->printer_id;
            foreach ($daughter_ids as $daughter_id) {
              //  $daughter_id = $daughter_ids[$i];
                $spooler->spoolPrintJob($daughter_id, 'batch_quality');
            }    
        }
    }
