<?php
lib('Controller/Crf');
/**
 * Page for creating new samples and marking crf samples as received.
 * New samples can be created by spreadsheet upload or auto generation with a web form.
 * Samples are marked as received by scanning.
 *
 * ACTIONS
 * When the user submits a spreadsheet, a temporary table tmp_crf_(sessionid) is created and populated and the page is reloaded.
 * If the user imports the data, it is moved to the batch_quality table, the tmp table is deleted, $_SESSION[batchuuid] is set and the page is reloaded.
 * If the user submits the web form, data is added to the batch_quality table and $_SESSION[batchuuid] is set.
 * When the user scans a uuid in the batch_quality table, the sample is marked as received and $_SESSION[subject_array] is updated with the subject_id of the sample.
 *
 * VIEWS
 * If the table tmp_crf_(sessionid) exists when the page is loaded, the user is shown the tmp table and can import it.
 * If $_SESSION[batchuuid] is set when the page is loaded, the user is shown the data for that batch and can print labels.
 *
 * 
 * $_SESSION[subject_array] - updated when a sample is scanned and marked as received
 * $_SESSION['batchuuid'] - updated when a batch of samples is moved from the tmp table to batch_quality
 * table tmp_crf_(sessionid) - created when a spreadsheet is imported or new samples are created with the form, deleted when the samples are moved to the batch_quality table
 */
// screen broken into 2 sections
// - action container
// - data container
//<div id="datacontainer">

$id_study = $_SESSION['id_study'];
$study = getStudy($id_study);
$crfimport = "/sps/data/crf/fileimport.php";
$crf = New Crf();
$template = $crf->xls_template_path;
?>


<div id="dashboard" class="dashboard">
	<div>
		<h2>Import a Spreadsheet:</h2>
		<form action="<?php echo $crfimport?>" method="post" enctype="multipart/form-data">
		<label for="file">Worksheet:</label>
<?php
		print "<a href='$template'>template</a>"; 
?>
		<input type="file" size = "15" name="file" id="file" />
		<input class="btn" type="submit" name="submit" value="Submit" />
		</form>
	</div>
	<br/><br/>
    <?php 
    if (autoassignCohort($id_study) == 1) {
        print retNewLabelsForm($id_study);
    }
    ?>
</div>
<div id="datacontainer" class="span10">
<div id="workbook" style="width: 600px; border: 1px solid rgb(192, 192, 192); background-color: rgb(217, 223, 205);">
<div id="colnames" style="display: none; border: 1px solid rgb(192, 192, 192); background-color: rgb(217, 223, 205);"></div>
<div id="worksheet_1" style="display: none; border: 1px solid rgb(192, 192, 192); background-color: rgb(217, 223, 205);"></div>
<?php
for ($i = 30;$i >= 0;$i--) {
	echo '<div id="group_' . $i . '"></div>';
}
?>
</DIV>
</DIV>
</DIV>
<?php
if (isset($_SESSION['subject_array'])) {
	echo '<script type="text/javascript">';
	$subjects = $_SESSION['subject_array'];
	foreach($subjects as $subject) {
		//echo "$subject";
		echo "invoiceSubject('" . $subject . "');";
		echo "dashboard();";
	}
?>
</script>
<?php
} else {
	$tmptable = 'tmp_' . $_SESSION['task'] . '_' . session_id();
	if (isset($_SESSION['batchuuid']) || isset($_SESSION['tmptable'])) {
?>
		<script type="text/javascript">
        new Ajax.Updater('dashboard', 'npc.php?action=dashboard', {asynchronous:true,
		onSuccess: function() {
			new TableOrderer('datacontainer',{url : 'npc.php?action=data&format=json&type=import'});
		},
		onFailure: function() {
			alert('error setting daterange');
		},
	});
		</script>
<?php
	}
}
?>
