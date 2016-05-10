<?php
include('/opt/sps/include/lib.php');
#include('/share/devel/sps/include/lib.php');
lib('studytemplate');
$id_study = 'CollOS';
$uncommon_fields = array('collection_time','treatment');

// parameters and filters
$sample_types = array('blood plasma','blood serum','blood','urine');
$destinations = array('LOCAL');
// $instruments = array();
$sample_sources = array();
// $shipment_types = array('Local');
// $visits = array();
$users = array('dimitra');
$substudy_array = array("818658-B");
$autoassign_cohort =false;

// printers
// add fitz printer with id=19
$printers= array(19); // see print_devices for a list of printers.  LRC uses the same printers as promis for now


// get things back to square-one
rmStudy($id_study);
addStudy($id_study, $autoassign_cohort);


/*
* Task behaviors - changes how tasks are rendered/print/process/do random stuff
*
* task - the task to apply the parameter to.  if blank, apply to all
* name - the parameter applied to the task
* description - doesn't do anything. 
*
* Possible names:
* 	jsonify - How the page is loaded.  The default is to look in www/tasks for .php files.  If 'jsonify' is specified, use .js file instead
*	?? - don't print parent samples
*	?? - use different CRF layout
*/
$behavior = New Behavior();
$behavior->id_study = $id_study;
$behavior->task = 'analysis';
$behavior->name = 'jsonify';
$behavior->description = 'Jsonification';
$behavior->setBehavior();
// 
$behavior->name = 'squashify';
$behavior->description = 'Squash Jsonification';
$behavior->setBehavior();
//
$behavior->name = 'router';
$behavior->description = 'use router';
$behavior->setBehavior();
//
$behavior->task = 'tracking';
$behavior->name = 'router';
$behavior->description = 'use router';
$behavior->setBehavior();
//
$behavior->task = 'pulladmin';
$behavior->name = 'router';
$behavior->description = 'use router';
$behavior->setBehavior();
//
// show the pulladmin menu
$behavior->name = 'pulladmin';
$behavior->description = 'show it in the menu';
$behavior->setBehavior();
//
$paramIDs = array();
$paramIDs = array();
/**
* 
* @see include/InventoryObject.php
* @see Model/fields.php
*/
//addUncommonFields($uncommon_fields);

// @NOTE: everything must be it's own substudy
foreach ($substudy_array as $study) {
	$id_param = addSubStudy($study);
	addStudyFilter($id_study,$id_param);
	$paramIDs[$study]  = $id_param;
}
// add sample types to params if they don't already exist;
foreach ($sample_types as $sample_type) {
	$crf_quant = 1;
	$id_param = addSampleType($sample_type,$id_study,$crf_quant);
	addStudyFilter($id_study,$id_param);
	$paramIDs[$sample_type]  = $id_param;
}
foreach ($sample_sources as $sample_source) {
	$id_param = addSampleSource($sample_source,$id_study);
	addStudyFilter($id_study,$id_param);
	$paramIDs[$sample_source]  = $id_param;
}
foreach ($destinations as $destination) {
	$id_param = addDestination($destination);
	addStudyFilter($id_study,$id_param);
}
// foreach ($visits as $visit) {
// 	$id_param = addIdVisit($visit);
// 	addStudyFilter($id_study,$id_param);
// }
// foreach ($shipment_types as $shipment_type) {
// 	$id_param = addShipmentType($shipment_type);
// 	addStudyFilter($id_study,$id_param);
// }
// foreach ($instruments as $instrument) {
// 	$id_param = addInstrument($instrument);
// 	addStudyFilter($id_study,$id_param);
// }


addStudyPrinters($printers); 

foreach ($users as $user) {
	addUser($id_study, $user);
}
