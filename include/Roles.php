<?php
    function has_role($role_name) {
        global $sps;
        $id_study = $sps->active_study->id_study;
        $roles = New Roles();
        $roles->username = $sps->username;
        $roles->id_study = $sps->active_study->id_study;
	$study_roles= $roles->retStudyRoles();
	if (isset($study_roles) && array_key_exists($role_name,$study_roles)) {
		return true;
	} else {
		return false;
	}
      }
    function allowed_studies() {
        global $sps;
        $roles = New Roles();
        $roles->username = $sps->username;
        return $roles->retAllowedStudies();
    }
    function listUsers() {
        global $dbrw; 
        $users = array();
        $sql = "select username from users";
        $result = mysqli_query($dbrw,$sql);
        while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row['username'];
        }
        return $users;
    }


class Roles {
    var $username = null;
    var $id_study = null;
    var $rolename = null;
    public function retAllowedStudies() {
        global $dbrw;
        $studies = array();
        $sql = "select distinct(id_study) id_study from roles where username = '$this->username' ";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_array($result)) {
            $studies[] =  $row['id_study'];
        }
        if (in_array('any',$studies)) {
            $studies = array();
            $studies[] = 'any';
            $sql = "select id_study from studies";
            $result = mysqli_query($dbrw,$sql);
            if (!$result) {
                $this->error = 'Could not run query: ' . mysqli_error($dbrw);
                return false;
                exit;
            }
            while ($row = mysqli_fetch_array($result)) {
                $studies[] =  $row['id_study'];
            }
        }
        return $studies;
    }
    public function retStudyRoles() {
        global $dbrw;
        $role_names = array();
        if (!$this->id_study) {
            print "Error: id_study not set\n";
            return false;
            exit;
        }
        $sql = "select id_study,role from roles where username = '$this->username' and ( ";
        $sql .= "id_study = '$this->id_study' ";
        if ($this->id_study != 'any') {
            $sql .= " or id_study = 'any' ";
        }
        $sql .= ") ";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_array($result)) {
           $role_name =  $row['role'];
           $role_study =  $row['id_study'];
           if (!isset($role_names[$role_name]) || $role_names[$role_name] != 'any'){
               $role_names[$role_name] = $role_study;
           }
        }   
        return $role_names;
    }
    public function grantRole() {
        global $dbrw;
        $sql = "insert into roles (username,id_study,role) values ('$this->username','$this->id_study','$this->rolename')";
	$result = mysqli_query($dbrw,$sql);
        $id = mysqli_insert_id($dbrw);
        if ($id > 0) {
            return true;
        } else {
            return false; 
        } 
    }

    /**
     * get access list for a user/study/role
     * @param array $roles
     * @return access list as array
     **/
    public function revokeRole() {
        global $dbrw;
        $sql = "delete from roles where username = '$this->username'and role = '$this->rolename'";
	$sql .= "and id_study = '$this->id_study'";
	$result = mysqli_query($dbrw,$sql);
        if (mysqli_affected_rows($dbrw) >= 1) {
		return true;
	} else {
		return false;
	}
    }
    public function listUsers() {
        global $dbrw;
        $sql = "select username from users";
        $result = mysqli_query($dbrw,$sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $return[] = $row['username'];
        }
        return $return;
     }
}
