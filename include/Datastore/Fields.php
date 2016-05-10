<?php
/*
	Declares Database field names
	Any fields added here should be 
	added to their respective tables
	(batch_quality,items,locations,results,results_raw)
*/
class SpsFields{
    function __construct($table=null) {
        $sps_field = New SpsField();
        if ($table == null) {
          return false;
        } else {
            $cols = Db::retTableColumns($table);
            foreach (New DbDictionary() as $word=>$entry) {
                if (in_array($entry['name'],$cols)) {
	            $this_field = clone $sps_field;
                    $this_field->name = $entry['name'];
                    $this_field->description = $entry['description'];
                    $this_field->type = $entry['type'];
                    $this_field->attr = $entry['attr'];
                    $this->$word=$this_field;
               }
           }
	}
          return true;
    }
}

