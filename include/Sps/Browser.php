<?php
class Browser extends Sps{
    function __construct() {
        $this->entity = 'browser';

	//username is coming from local web server
	if (isset($_SERVER['REMOTE_USER'])) {
        	$this->username = $_SERVER['REMOTE_USER'];
	//username is coming from proxy server
	} else if (isset($_SERVER['HTTP_REMOTE_USER'])) {
        	$this->username = $_SERVER['HTTP_REMOTE_USER'];
	} else if (isset($_SERVER['HTTP_X_FORWARDED_USER'])) {
        	$this->username = $_SERVER['HTTP_X_FORWARDED_USER'];
	}
        parent::__construct();
	if (isset($_SESSION['apikey'])) {
	     $this->apikey = ($_SESSION['apikey']);
	}
    }
    function __destruct() {
	//$this->tokenize();
	$this->apikey = $this->tokenize();
	if ($this->apikey) {
		$_SESSION['apikey'] = $this->apikey;
	}
	if (isset($this->highlighted)  && $this->highlighted) {
		$_SESSION['highlighted'] = $this->highlighted;
	}
    }
}
