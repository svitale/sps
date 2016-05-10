<?php
// returns true if the current user has the specified role
class Auth {
    var $username = null;
    var $roles = null;
    var $studies = null;
    function __construct($username=null) {
        if ($username && (in_array($username,$this->listUsers()) || $username == 'script' )) {
           $this->username = $username;
           //TODO: rewrite anything that accesses this session variable directly
           $_SESSION['username'] = $this->username;
        } else {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
    }
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
    public function retStudyRoles($id_study) {
        global $dbrw;
        $role_names = array();
        $sql = "select sum(if(id_study='any',1,0)) inherited,role from roles where username = '$this->username' and ( ";
        $sql .= "id_study = '$id_study' ";
        if ($id_study != 'any') {
            $sql .= " or id_study = 'any' ";
        }
        $sql .= ") group by role";
        $result = mysqli_query($dbrw,$sql);
        if (!$result) {
            $this->error = 'Could not run query: ' . mysqli_error($dbrw);
            return false;
            exit;
        }
        while ($row = mysqli_fetch_array($result)) {
           if ($id_study == 'any') {
               $inherited = 0;
           } else {
               $inherited = $row['inherited'];
           }
           $role_names[$row['role']] = array('inherited'=>$inherited);
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
            $return[] = 'api';
        return $return;
     }
}
