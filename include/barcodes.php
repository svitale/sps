<?php
//
$command = substr($_POST['value'],4);
$command = strtolower($command);
$splitstring = explode(";",$command);
$action = $splitstring[0];
if (isset($splitstring[1])) {
	$arg1 = $splitstring[1];
}
//
global $sps;
switch ($action) {
case 'printblanks':
printBlanks(1, 2, 'blank');
break;

case 'mplexresults':
?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=zip&type=export&usetemplate=mplexresults";
	//-->
	</SCRIPT>
<?php
break;

case 'clonecont':
      duplicateContainer();
break;
case 'autoprint':
	$_SESSION['autoprint'] = '1';
	$_SESSION['select_first_missing'] = '1';
	?>
	<SCRIPT language="JavaScript">
	<!--
	alert('autoprint is on');
	//-->
	</SCRIPT>
	<?php
break;
case 'fwselect1st':
	$_SESSION['select_first_missing'] = '1';
	?>
	<SCRIPT language="JavaScript">
	<!--
	alert('select_first_missing is on');
	//-->
	</SCRIPT>
	<?php
break;
case 'elisaduplicate':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=xls&type=export&usetemplate=eliza";
	//-->
	</SCRIPT>
	<?php
break;
case 'tgfdup':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=xls&type=export&usetemplate=tgfdup";
	//-->
	</SCRIPT>
	<?php
break;
case 'il10export':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=xls&type=export&usetemplate=elizaSinglicate";
	//-->
	</SCRIPT>
	<?php
break;
case 'gen5bcs':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=txt&type=linearbcs&flag=true";
	//-->
	</SCRIPT>
	<?php
break;
case 'manualelisadup':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=xls&type=export&usetemplate=manualelisadup";
	//-->
	</SCRIPT>
	<?php
break;
case 'manualelisasing':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=txt&type=export&usetemplate=manualelisasing&flag=true";
	//-->
	</SCRIPT>
	<?php
break;
case 'cxcl12dup':
	?>
	<SCRIPT language="JavaScript">
	<!--
	window.location="npc.php?action=data&format=xls&type=export&usetemplate=cxcl12dup";
	//-->
	</SCRIPT>
	<?php
break;
case 'thawtube':
	if (($_SESSION['Detailid'] > 0) && (($_SESSION['DetailpostTable']) == 'items')) {
		thaw($_SESSION['Detailid'],'1');
		topView($_SESSION['Detailid']);
		echo '<SCRIPT language="JavaScript">';
		echo "getItemId('".$_SESSION['Detailid']."')";
		echo '</SCRIPT>';
	} else {
		echo "don't know what to thaw";
	}
break;
case 'setprinter':
	if (isset($arg1)) {
		$_SESSION['printer_name'] = strtoupper($arg1);
		echo '<SCRIPT language="JavaScript">';
		echo "window.location.reload();";
		echo '</SCRIPT>';
	}
break;
case 'hemolyzse_full':
	if (($_SESSION['Detailid'] > 0) && (($_SESSION['DetailpostTable']) == 'items')) {
		helolyze($_SESSION['Detailid'],'1');
		topView($_SESSION['Detailid']);
		echo '<SCRIPT language="JavaScript">';
		echo "getItemId('".$_SESSION['Detailid']."')";
		echo '</SCRIPT>';
	} else {
		echo "don't know what to change hemolyzation of";
	}
break;
case 'aliquot':
		//todo - consolidate with webroot/util/make_daughters.php
	lib('Process');
    if (($_SESSION['Detailid'] > 0)) {
     	if ((isset($arg1)) && ($arg1 > 0 ) && ($arg1 < 20 )) {
		$daughters = $arg1;
	} else {
		$daughters = 1;
	}
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
                $daughter_id = $daughter_ids[$i];
                $spooler->spoolPrintJob($daughter_id, 'batch_quality');
            }    
        }
    }
break;
case 'proc':
        lib('Process');
        global $sps; 
        if (isset($arg1)) {
                $process_name = strtoupper($arg1);
        } else {
                print "No Process specified";
                exit;
        }
        if (($_SESSION['containerid'] > 0) && (($_SESSION['containertype']) == 'box')) {
                $parent_container = $_SESSION['containerid'];
                $id_study = $_SESSION['id_study'];
                $printer_name = $_SESSION['printer_name'];
      //          session_destroy();
       //         session_start();
                $_SESSION['task'] = 'crf';
                $_SESSION['id_study'] = $id_study;
                $_SESSION['printer_name'] = $printer_name;
                print "<SCRIPT language='JavaScript'>\n";
                print "<!--\n";
                print "window.location='util/processbox.php?id=$parent_container&process_name=$process_name'\n";
                print "//-->\n";
                print "</SCRIPT>\n";
        } else {
                $id = $_SESSION['Detailid'];
                $table = $_SESSION['DetailpostTable'];
                $id_study = $_SESSION['id_study'];
                $printer_name = $_SESSION['printer_name'];
                session_destroy();
                session_start();
                $_SESSION['task'] = 'crf';
                $_SESSION['id_study'] = $id_study;
                $_SESSION['printer_name'] = $printer_name;
                print "<SCRIPT language='JavaScript'>\n";
                print "<!--\n";
                print "window.location='util/processtube.php?id=$id&table=$table&process_name=$process_name'\n";
                print "//-->\n";
                print "</SCRIPT>\n";
        }
break;

case 'alq_box':
	lib('process');
	if (($_SESSION['containerid'] > 0) && (($_SESSION['containertype']) == 'box')) {
		if (isset($arg1) && $arg1 > 0 && $arg1 < 20 ) {
			$num_daughters = $arg1;
		} else {
			$num_daughters = 1;
		}
    		$parent_container = $_SESSION['containerid'];
		print "<SCRIPT language='JavaScript'>\n";
		print "<!--\n";
		print "window.location='util/aliquotbox.php?id=$parent_container&num_daughters=$num_daughters'\n";
		print "//-->\n";
		print "</SCRIPT>\n";
	}
break;



case  'vol_cur':
	if (isset($arg1) && ($arg1 > 0 ) && ($arg1 < 20 ) && (isset($_SESSION['Detailid'])) ) {
		$value = $arg1;
		if (($_SESSION['Detailid'] > 0) && ($_SESSION['DetailpostTable'] == 'items')) {
			vol_cur($_SESSION['Detailid'],$value,$_SESSION['DetailpostTable']);
                        topView($_SESSION['Detailid']);
                        echo '<SCRIPT language="JavaScript">';
                        echo "getItemId('".$_SESSION['Detailid']."')";
                        echo '</SCRIPT>';

		}
	} else {
		echo "invalid value for volume ".$command;
	}
break;
case  'vol_init':
	$value = $arg1;
	if (($value > 0 ) && ($value < 20 )) {
		if ($_SESSION['Detailid'] > 0) {
			vol_init($_SESSION['Detailid'],$value,$_SESSION['DetailpostTable']);
                        topView($_SESSION['Detailid']);
                        echo '<SCRIPT language="JavaScript">';
                        echo "getItemId('".$_SESSION['Detailid']."')";
                        echo '</SCRIPT>';

		}
	} else {
		echo "invalid value for volume ".$command;
	}
break;



// pick a number of random tubes from a container (boxes only for now)
case 'randomize':
    lib('Controller/Randomizer');
    if (($_SESSION['containerid'] > 0) && (($_SESSION['containertype']) == 'box')) {
        if (isset($arg1) && $arg1 > 0) {
            $num = $arg1;
        } else {
            $num = 1;
        }
        $parent_container = $_SESSION['containerid'];
        $chooser = new Randomizer();
        $chooser->num_Targets = 1;
        $chooser->parent = $parent_container;
        $chooser->chooseTargets();
        $selected_id = $chooser->last_selected;
       // $return = json_encode($sps);
       // $return;
        print "<SCRIPT language='JavaScript'>\n";
		print "window.location.reload();";
//	print "window.location='util/aliquotbox.php?id=$parent_container&num_daughters=$num_daughters'\n";
	print "</SCRIPT>\n";
    }
break;

    default:
			echo '<SCRIPT language="JavaScript">';
			echo "alert('not a recognized barcode')";
			echo '</SCRIPT>';
}
?>
