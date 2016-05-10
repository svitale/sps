<?php
lib('Project');
if (isset($_GET['type']) && $_GET['type'] != 'null') {
	$type = $_GET['type'];
} else {
	$type = null;
}
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
	$callback = $_GET['callback'];
}
if (isset($_GET['id']) && $_GET['id'] != 'null') {
	$id = $_GET['id'];
} else {
	$id = null;
}
if (isset($_GET['study']) && $_GET['study'] != '') {
	$study = $_GET['study'];
} else {
	$study = null;
}
if (isset($_GET['params']) && $_GET['params'] != '') {
	$params = $_GET['params'];
} else {
	$params = null;
}
$project =  New Project();
$project->type = $type;
$project->callback = $callback;
$project->id = $id;
$project->study = $study;
$project->params = $params;
$initialized = $project->initialize();
$message = $initialized->message;
print "$callback(" . json_encode($initialized) . ");";
