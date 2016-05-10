<?php
lib('Task');
lib('Model/Results');
class RawResult extends ResultsObject{
    function __construct($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
            $handle = fopen("php://input", "r");
            $contents = '';
            while (!feof($handle)) {
                $contents .= fread($handle, 8192);
            }
            fclose($handle);
            $changed = json_decode($contents);
            $this->id = $id;
            $this->patchRecord($changed);
        }
        parent::__construct($id);
    }
}
