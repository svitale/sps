<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (!in_array('admin',$sps->auth->roles)) {
    header('HTTP/1.0 403 Access Denied');
    exit;
}
$roles = clone $sps->auth;
if (isset($_POST['rolename'])) {
    $roles->rolename = $_POST['rolename'];
}
if (isset($_POST['username'])) {
    $roles->username = $_POST['username'];
}
if (isset($_POST['enabled'])) {
    $enabled = $_POST['enabled'];
}
$roles->id_study = $sps->active_study->id_study;
if ($enabled == 'true') {
    $return  = array('grant'=>$roles->rolename,'success'=>$roles->grantRole());
} else {
    $return  = array('revoke'=>$roles->rolename,'success'=>$roles->revokeRole());
}
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($return) . ");";
?>
