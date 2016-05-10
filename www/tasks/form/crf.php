<?php
/**
 * Handle forms from tasks/crf.php.  Executes code based on $_POST[form_action] then sets
 * the header to the referer address.
 *
 * TODO: better error handling, right now just displays an error message and requires user to click back button.
 * 
 * $_POST[form_action] = generateLabels
 *      Generate new samples.  First populate the table tmp_crf_[sessionid],
 *      then import to the batch_quality table with importBatch().
 *
 */
global $sps,$dbrw;
$referrer = $_SERVER['HTTP_REFERER'];
$id_study = $_SESSION['id_study'];

if(array_key_exists('create_by_encounter',$sps->task_behavior)) {
    $by = 'encounter';
} else {
    $by = 'subject';
}  


/**
* Generate new samples based on form values
*
* $_POST[id_subject] - query redcap to get the info for this particular subject
*/
if ($_POST['form_action'] == 'REDCAPImport') {
    if ($_GET['imported'] == 'false') { 
        lib('REDCap');
        lib('Process');

        $collection_ids = array($_POST['id_collection']);
        $redcap =  New REDCap();
        $redcap_specimens = $redcap->getCRFSpecimens($collection_ids);
        $process = New Process();
        $tmptable = createTmp();
        $process->tmptable = $tmptable;
        if(!$process->startTransaction()) {
            print "Unable to start transaction: ";
            print $process->errorMsg;
            return;
        }


        foreach ($redcap_specimens as $specimen) {
        	for ($i = 0; $i < ($specimen['quantity']); ++$i) {
            	$specimen['id_uuid'] = new_uuid(); 
                if (!$process->createRedcapSample($specimen)) {
                    print "Unable to import samples: ";
                    print $process->errorMsg;
                    if(!$process->rollbackTransaction()) {
                        print "could not roll back";
                    }
                    return;
                }
            }
        }
        $process->commitTransaction();
    } else {
        $id_subject = $_POST['id_subject'];
        $sql = "select id_batch from batch_quality where id_subject = '$id_subject' order by id desc limit 1;";
    $result = mysql_query($sql);
    if (!$result) {
        return -1;
    }
    $row = mysql_fetch_object($result);
    $id_batch = $row->id_batch;
    $_SESSION['batchuuid'] = $id_batch;
    }

} else if ($_POST['form_action'] == 'REDCAPStudyUpdate') {
    lib('REDCap');
    lib('Process');

    $old_id_study = $_POST['old_id_study'];
    $new_id_study = $_POST['new_id_study'];
    $form_id_subject = $_POST['form_id_subject'];

    $process = New Process();
    if(!$process->updateStudyForSubjects($form_id_subject, $old_id_study, $new_id_study)) {
        print "Unable to update study for subject $form_id_subject:";
        print $process->errorMsg;
        return;
    }

/**
 * Generate new samples based on form values.
 * 
 * $_POST[numpackets] - the number of patients to generate samples for
 * $_POST[(samplename)num] - the numbers of each type of sample to create per patient
 * $_POST[(item field names)] - the values to be set for every new sample
*/
} else if ($_POST['form_action'] == 'generateLabels') {
    $sql = "select sample_type from crf where id_study = '$id_study'";
    $sql .= " order by num_order";
    $result = mysql_query($sql);
    if (!$result) {
        return -1;
    }
    while ($row = mysql_fetch_array($result)) {
            $sample_type = $row['sample_type'];
            $sampletypes[$sample_type] = 0;
    }
    $itemfields = array("id_visit"=>'', "shipment_type"=>'');
    
    //validate input
    if (isset($_POST['id_subject'])) {
        $id_subject = mysql_real_escape_string(trim($_POST['id_subject']));
    } else if (isset($_POST['id_encounter']) && strlen($_POST['id_encounter'])> 0) {
        $id_encounter = mysql_real_escape_string(trim($_POST['id_encounter']));
    } else if (isset($_POST['numpackets']) && is_numeric($_POST['numpackets']) && ($_POST['numpackets'] > 0)) {
        $numpackets = mysql_real_escape_string(trim($_POST['numpackets']));
    } else {
	$numpackets = 1;
      //  echo "Samples will not be generated, invalid number of subjects.";
       // return;
    }
    
    foreach (array_keys($itemfields) as $field) {
        $itemfields[$field] = mysql_real_escape_string(trim($_POST[$field]));
    }
    
    $totalsamples = 0;
    foreach (array_keys($sampletypes) as $sampletype) {
        $varname = 'num'.$sampletype;
        if (isset($_POST[$varname]) && is_numeric($_POST[$varname]) && ($_POST[$varname] >= 0)) {
            $sampletypes[$sampletype] = $_POST[$varname];
            $totalsamples += $_POST[$varname];
        } else {
            echo "Samples will not be generate, invalid number of $varname samples.";
            return;
        }
    }
    // limit the number of samples that can be created at once
    if (isset($numpackets) && is_numeric($numpackets) && ($numpackets > 0)) {
        $totalsamples = $totalsamples*$numpackets;
    }
    if ($totalsamples > 500) {
        echo "Samples will not be generated, cannot create more then 500 samples at once.";
        return;
    }
    
    
    //get next ID
    if (isset($id_subject) && (strlen($id_subject) > 0)) {
	    $next_id = $id_subject;
	    $numpackets = 1;
    } else if (isset($id_encounter)) {
	    $next_id = $id_encounter;
	    $numpackets = 1;
    } else if ($by == 'encounter') {
            $count = 0;
            $a_z = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $max_tries = 100;
            while($count < $max_tries) {
                $count++;
                $next_id = $a_z[rand(0,25)] . $a_z[rand(0,25)] . rand(0,9) . rand(0,9);
                $sql = "select id from batch_quality where id_study = '$id_study' and id_encounter = '$next_id'";
                $result = mysqli_query($dbrw,$sql);
                if(mysqli_num_rows($result) == 0) {
                  break;
                }
            }
            if($count == $max_tries) {
	        echo "Samples cannot be generated, encounter id could not be found";
      	        return;
            }
    } else {
	    $next_id = getNextIdSubject($id_study);
            $numpackets = $_POST['numpackets'];
	    if ($next_id < 1) {
	        echo "Samples cannot be generated, this study does not use numeric subject ids.";
      	         return;
    	    }
    }
    
    
    // add data to the temp table
    $tmptable = createTmp();
    for ($packetcnt = 0; $packetcnt < $numpackets; $packetcnt++) {
        $username = $_SESSION[username];
        $sql = "insert into `$tmptable` (id_$by, id_study, name_created, sample_type, id_uuid";
        foreach (array_keys($itemfields) as $field) {
            $sql .= ", $field";
        }
        $sql .= ") values";
            
        foreach(array_keys($sampletypes) as $sampletype) {
            for ($samplectr=0; $samplectr<$sampletypes[$sampletype]; $samplectr++) {
                $sql .= "('$next_id', '$id_study', '$username', '$sampletype', '".new_uuid(). "'";
                foreach (array_keys($itemfields) as $field) {
                    $sql .= ", '" . $itemfields[$field] . "'";
                }
                $sql .= "),";
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 1); // remove trailing comma
        
        if (!mysql_query($sql)) {
            echo "could not perform query: " . mysql_error();
            echo "<br/>$sql";
            mysql_query("DROP TABLE IF EXISTS `$tmptable`");
            return;
        }
        $next_id++;
    }
}
    //importBatch(false);
header("Location: " . $referrer);

function createTmp() {
    // create the temp table
    $tmptable = 'tmp_crf_'.session_id();
    $dsttable = 'batch_quality';
    if (!mysql_query("DROP TABLE IF EXISTS `$tmptable`")) {
        echo 'Could not run query: ' . mysql_error();
        exit;
    }
    $query = 'create table if not exists `' . $tmptable . '` like ' . $dsttable;
    $result = mysql_query($query);
    if (!$result) {
        echo 'Could not run query: ' . mysql_error();
        exit;
    }
    $_SESSION['tmptable'] = $tmptable;
    mysql_query("alter table `" . $tmptable . "` drop index id_uuid");
    return $tmptable;
}

?>

