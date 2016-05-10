<?php
if (isset($_GET['callback']) && $_GET['callback'] != 'null') {
    $callback = $_GET['callback'];
} else {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (!in_array('admin',$sps->auth->roles)) {
echo "not authorized";
exit;
}
$role_names = array('lab','analytics','admin');
$authObj = clone $sps->auth;
$userArray = $authObj->listUsers();
$rolesArray = array();
foreach($userArray as $user) {
        $id_study = $sps->active_study->id_study;
        $authObj->username = $user;
        $roles = $authObj->retStudyRoles($id_study);
        $rolesArray[$user] = $roles;
}
$returnArray = array('role_names'=>$role_names,'user_roles'=>$rolesArray);
header('content-type: application/json; charset=utf-8');
print "$callback(" . json_encode($returnArray) . ");";
?>
