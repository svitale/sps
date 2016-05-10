<?php
include_once('lib.php');
class Logger{
    var $message = null;
    public function initialize() {
        if (isset($_SESSION['devmode']) || isset($_SERVER['SHELL'])) {
            if (!is_null($this->message)) {
                print_r($this->message);
                print "\n";
            }
        }
    }
}
