<?php 
lib('ranges');
lib('dbcleanup');
lib('dbi');
//placeholder for revised importer
class SpsImportSpreadsheet {
    var $referrer = null;
    var $template_file = null;
    var $source_file = null;
    var $tmptable = null;
    var $dsttable = null;
    var $parent = null;
   function retNamedRanges() {
        return findNamedRanges($this->template_file,$this->parent);
   }
   function retNamedRanges() {
        return makeNamedRanges($this->source_file,$this->parent);
   }
   function retFormattedArray($rangeArray) {
        return formatArray($this->source_file,$rangeArray);
   }
   function retRawSql($rangeArray)  {
        return rangeInsertSQL($rangeArray,$this->tmptable);
   }
}
?>
