<?php

/**
 * Set all the default session values
 * Reset all session values when the task is changed
 */

// CAS authentication verified npc.php when CASauth.php is included
global $task, $study, $id_study, $printer_name, $username;

/**
 * Some session variables that we'll set as global
 * so we don't have to keep calling $_SESSION
 */
if (isset($_SESSION['study']) && !is_null($_SESSION['study'])) {
    $study = $_SESSION['study'];
} else {
    $study = null;
}

if (isset($_SESSION['printer_name']) && !is_null($_SESSION['printer_name'])) {
    $printer_name = $_SESSION['printer_name'];
} else {
    $printer_name = null;
}

if (isset($_SESSION['username']) && !is_null($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}


if (!isset($_SESSION['order'])) {
    $_SESSION['order'] = 'id';
}
if (!isset($_SESSION['show_menu'])) {
    $_SESSION['show_menu'] = 'yes';
}
if (isset($_SESSION['task'])) {
    $task = $_SESSION['task'];
} else {
 //   $task = false;
}



//*
$cct_users = array('bky','devotta','alwoods','bhsia','lagui');
if (in_array($username, $cct_users)) {
    if (!isset($_SESSION['printer_name'])) {
        $_SESSION['printer_name'] = 'file';
    }
    if (!isset($_SESSION['id_study'])) {
        $_SESSION['id_study'] = 'CCT';
    }
    if (!isset($_SESSION['task'])) {
        $_SESSION['task'] = 'crf';
    }
//		if (!isset($_SESSION['params']) && ($_SESSION['task'] == 'crf')) {
    if (!isset($_SESSION['params'])) {
        $SampleTypeArray = array(160, 161, 162);
        $ShipmentTypeArray = array();
        $VisitArray = array(163, 165, 166, 167, 168, 195, 196, 197, 198);
        $DestinationArray = array(164);
        $StudyArray = array(159);
        $_SESSION['params'] = array_merge($SampleTypeArray, $ShipmentTypeArray, $VisitArray, $DestinationArray, $StudyArray);
    }
}
//eQTL study
if (isset($_SESSION['id_study'])) {
if ($_SESSION['id_study'] == 'eQTL') {
	if (!isset($_SESSION['printer_name'])) {
		$_SESSION['printer_name'] = 'car-cappo-001';
	}
	if (!isset($_SESSION['id_visit'])) {
		$_SESSION['id_visit'] = 'V1Y0';
	}
		$_SESSION['params'] = array(217,16,42,219,170,230,237,239);
	if (!isset($_SESSION['params'])) {
	}
}

//demo study
if ($_SESSION['id_study'] == 'demo') {
    if (!isset($_SESSION['printer_name'])) {
        $_SESSION['printer_name'] = 'BRADY-TCL-01';
    }
    if (!isset($_SESSION['id_visit'])) {
        $_SESSION['id_visit'] = 'V1Y0';
    }
    if (!isset($_SESSION['shipment_type'])) {
        $_SESSION['shipment_type'] = 'TRANSCOLD';
    }
    $_SESSION['params'] = array(8, 10, 16, 30, 40);
}
//cgi study
if ($_SESSION['id_study'] == 'CGI') {
    if (!isset($_SESSION['printer_name'])) {
        $_SESSION['printer_name'] = 'BRADY-CRC-01';
    }
    if (!isset($_SESSION['id_visit'])) {
        $_SESSION['id_visit'] = 'V1Y0';
    }
    if (!isset($_SESSION['shipment_type'])) {
        $_SESSION['shipment_type'] = 'LOCAL';
    }
    if (!isset($_SESSION['family'])) {
        $_SESSION['family'] = 'cgi_study';
    }
    $_SESSION['params'] = array(8, 10, 16, 21, 22, 33, 47, 170);
}
//cgi study
if ($_SESSION['id_study'] == 'GCAD') {
    if (!isset($_SESSION['printer_name'])) {
        $_SESSION['printer_name'] = 'BRADY-CRC-01';
    }
    if (!isset($_SESSION['id_visit'])) {
        $_SESSION['id_visit'] = 'V1Y0';
    }
    if (!isset($_SESSION['shipment_type'])) {
        $_SESSION['shipment_type'] = 'LOCAL';
    }
    if (!isset($_SESSION['family'])) {
        $_SESSION['family'] = 'gcad_study';
    }
    $_SESSION['params'] = array(8, 10, 16, 21, 22, 33, 47, 170);
}

if ($_SESSION['id_study'] == 'CRIC') {
    if (!isset($_SESSION['params']) && (isset($_SESSION['task'])) && (($_SESSION['task'] == 'store') or ($_SESSION['task'] == 'CRF'))) {
//cric sample types
        $SampleTypeArray = array(183, 22, 33, 37, 21, 29, 34, 38, 32, 28, 31, 27, 39, 30, 43, 36, 47, 46);
        $ShipmentTypeArray = array(185, 40, 41, 42);
        $VisitArray = array(184, 17, 18, 19, 20, 13, 14, 15, 188);
        $DestinationArray = array(182, 8, 9, 7, 10, 11, 12);
        $StudyArray = array(128, 110, 113);
        $_SESSION['params'] = array_merge($SampleTypeArray, $ShipmentTypeArray, $VisitArray, $DestinationArray, $StudyArray);
    }
    if (!isset($_SESSION['params']) && (isset($_SESSION['task']) && ($_SESSION['task'] == 'results'))) {
        $AssayArray = array(110, 233, 48);
	$_SESSION['params'] = $AssayArray;
    }
}

if($_SESSION['id_study'] == 'RACE') {
if (!isset($_SESSION['params']) && ($_SESSION['task'] == 'results')) {
//cric sample types
$SampleTypeArray = array();
$VisitArray = array();
$DestinationArray = array(35,30);
$StudyArray = array(241);
$AssayArray = array(158,157,156,155,154,153,152,151,150,149,148,147,146,145,144,143,142,141,140,139,138,137,169,199,200,201);
$_SESSION['params'] = array_merge($SampleTypeArray,$VisitArray,$DestinationArray,$StudyArray,$AssayArray);
}

}

if($_SESSION['id_study'] == 'promis') {
if (!isset($_SESSION['params']) && ($_SESSION['task'] == 'results')) {
//cric sample types
        $SampleTypeArray = array();
        $VisitArray = array();
        $DestinationArray = array(35, 30);
        $StudyArray = array(112, 128, 113);
//        $AssayArray = array(158, 157, 156, 155, 154, 153, 152, 151, 150, 149, 148, 147, 146, 145, 144, 143, 142, 141, 140, 139, 138, 137, 169, 199, 200, 201,247);
 //       $_SESSION['params'] = array_merge($SampleTypeArray, $VisitArray, $DestinationArray, $StudyArray, $AssayArray);
        $_SESSION['params'] = array_merge($SampleTypeArray, $VisitArray, $DestinationArray, $StudyArray);
    }
}


//if (($_SESSION['username'] == 'jlinnett')) {
if (($_SESSION['username'] == 'jlinnett')) {
if (!isset($_SESSION['params']) && ((($_SESSION['task'] == 'analysis') || $_SESSION['task'] == 'store' ) && $_SESSION['id_study'] == 'CRIC' )) {
$_SESSION['params'] = array(110,113,193,175,245,136,246,129);
}
}


//promis users
//Sara Vanorman
if (($_SESSION['username'] == 'bmorre') ||
//Arman Qamar
        ($_SESSION['username'] == 'aqamar') ||
//Sydney Hartman
        ($_SESSION['username'] == 'hsyd') ||
//Catherine Mclaughlin
        ($_SESSION['username'] == 'catmc') ||
//Samira Farouk
        ($_SESSION['username'] == 'samira') ||
//Kevin Trindade
        ($_SESSION['username'] == 'kevintri') ||
//Megan Burke
        ($_SESSION['username'] == 'megburke')) {
    if (!isset($_SESSION['noalert'])) {
        $_SESSION['noalert'] = 1;
    }
    if (!isset($_SESSION['printer_name'])) {
        $_SESSION['printer_name'] = 'BRADY-BRB-01';
    }
    if (!isset($_SESSION['id_study'])) {
        $_SESSION['id_study'] = 'promis';
    }
    if (!isset($_SESSION['id_visit'])) {
        $_SESSION['id_visit'] = 'V1Y0';
    }
    if (!isset($_SESSION['shipment_type'])) {
        $_SESSION['shipment_type'] = 'TRANSDRY';
    }
    if (!isset($_SESSION['family'])) {
        $_SESSION['family'] = 'promis_study';
    }
    if (!isset($_SESSION['sample_type'])) {
        $_SESSION['sample_type'] = 'Serum';
    }
    if (!isset($_SESSION['task'])) {
        $_SESSION['task'] = 'store';
    }
    if (!isset($_SESSION['params'])) {
        $_SESSION['params'] = array(2, 3, 4, 16, 43, 114, 112, 116, 117, 131, 132, 191, 247);
    }
}
}
$menuColor = "rgb(210,230,230)";
function settask($task) {
		resetsession();
		$_SESSION['task'] = $task;
		return "mode - $task";
}
function resetsession() {
	if (isset($_SESSION['username'])) {
		$username = $_SESSION['username'];
	}
	if (isset($_SESSION['phpCAS'])) {
		$phpCAS = $_SESSION['phpCAS'];
	}
	if (isset($_SESSION['active_study'])) {
	    $active_study = $_SESSION['active_study'];
        }
	$id_study = $_SESSION['id_study'];
         
	if (isset($_SESSION['printer_name'])) {
		$printer_name = $_SESSION['printer_name'];
	}
	// check to see if there is a tmp table for this session 
	// drop it if so
	if (isset($_SESSION['tmptable'])) {
		$tmptable = $_SESSION['tmptable'];
		$query = "show tables like '$tmptable'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)) {
			$query = "drop table `$tmptable`";
			$result = mysql_query($query);
			if (!$result) {
				echo 'Could not run query: ' . mysql_error();
				exit;
			}
		}
	}
	session_destroy();
	session_start();
	if (isset($phpCAS)) {
		$_SESSION['phpCAS'] = $phpCAS;
	}
	if (isset($username)) {
		$_SESSION['username'] = $username;
	}
	$_SESSION['id_study'] = $id_study;
        if (isset($active_study)) {
	    $_SESSION['active_study'] = $active_study;
        }
        if (isset($printer_name)) {
	    $_SESSION['printer_name'] = $printer_name;
        }
//	if (isset($box_array)) {
//		$_SESSION['box_array'] = $box_array;
//	}

}
?>
