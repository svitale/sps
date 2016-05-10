<?php 
lib('dbi');
lib('Model/SpsObjects');
//placeholder for revised importer
class Randomizer {
    var $num_targets = 1;
    var $targets = array();
    var $selected = null;
    var $obscured = null;
    var $increment = 0;
    #the range for obscuring dates
    var $max_days = 90;
    var $min_days = 14;
    function __construct($id) {
        if($_POST && $_POST['action']) {
          $this->selected = $id;
          $this->action= $_POST['action'];
          $this->performAction();
        }
   }
   function chooseTargets() {
       global $sps;
       lib('Model/Inventory');
       $highlighted = $sps->highlighted;
       if (!$highlighted) {
           $highlighted = array();
       }
       if ($highlighted['randomize']) {
           $targets = $highlighted['randomize'];
       } else {
           $targets  = $this->targets;;
       }
       $container = new ContainerModel();
       $container->id = $this->parent;
       $contents = $container->getContainedIds();
       $shuffled = shuffle($contents);
       for ($i=0;$i < $this->num_targets;$i++) {
           $id_item = $contents[$i];
           $targets[] = $id_item;
           $this->selected = $id_item;
       }
       $highlighted['randomize'] = $targets;
       $sps->highlighted = $highlighted;
       $this->targets = $targets;
   }
   function obfuscateByUuid($id_uuid) {
       $requested_fields = array('id');
       $by_field = array('id_uuid'=>$id_uuid);
       $record  = Db::retSingleRecord('items',$requested_fields,$by_field);
       $id = $record->id;
       $this->obfuscate($id);
       return $id;
   }
   function obfuscate($id) {
       #default prefix for randomized samples is null;
       $prefix = '';
       $requested_fields = array('id_subject','id_study');
       #get the subject id for this subject
       $item  = Db::retSingleRecord('items',array('id_subject','id_study'),array('id'=>$id));
       $id_subject = $item->id_subject;
       $id_study = $item->id_study;
       # check for subject in replicates table
       $reps_fields = array('obscured','increment');
       $reps_filter = array('original'=>$id_subject,'id_study'=>$id_study);
       $replicate  = Db::retSingleRecord('replicates',$reps_fields,$reps_filter);
       if(!$replicate) {
           if($id_study == 'CRIC') {
               $prefix = '0901';
           }
           #params for choosing new id
           $str_len= strlen($id_subject) - strlen($prefix);
           $min = 1;
           $max = pow(10,$str_len) -1;
           //$max = (10**$str_len)-1;
           $rand = str_pad(rand($min,$max),$str_len,0,STR_PAD_LEFT);
           $obscured = $prefix . $rand; 
           #choose a random number between min and max diff days
           $increment = rand($this->min_days,$this->max_days);
           $replicate  = Db::retSingleRecord('replicates',$reps_fields,$reps_filter);
           $insert_array = array(
               'original'=>$id_subject,
               'obscured'=>$obscured,
               'increment'=>intval($increment),
               'id_study'=>$id_study,
           );
           Db::insert('replicates',$insert_array);
       }
       $replicate  = Db::retSingleRecord('replicates',$reps_fields,$reps_filter);
       $this->spawnObfuscated($replicate,$id);
   }
   function performAction() {
           if($this->action == 'obfuscate') {
               $id = $this->selected;
               $this->obfuscate($id);
           }
   }
   function spawnObfuscated($replicate,$id) {
       lib('Printer');
       global $dbrw,$sps;
       $item = Db::retSingleRecord('items',array('date_visit'),array('id'=>$id));
       $uuid = new_uuid();
       $id_subject = $replicate->obscured;
       $date_visit = $this->addDayswithdate($item->date_visit,$replicate->increment);
       $sql =  "insert into batch_quality (";
       $sql .= "id_uuid,id_parent,id_subject,id_study,id_visit,";
       $sql .= "name_created,date_visit,date_collection,date_receipt,shipment_type,";
       $sql .= "sample_type,quant_thaws,type,notes) (SELECT '$uuid',id_uuid,";
       $sql .= "'$id_subject',id_study,id_visit,name_created,'$date_visit',";
       $sql .= "date_collection,date_receipt,shipment_type,sample_type,";
       $sql .= "quant_thaws,type,notes from `items` WHERE `id` = '$id')";
       $result = mysql_query($sql);
       if (!$result) {
          echo 'Could not run query: ' . mysql_error();
          exit;
       }
       $daughter_id = mysql_insert_id();
       $printer = New PrintDev();
       $printer = $sps->printer;
       $spooler = New PrintJobs();
       $spooler->printer_id =  $printer->printer_id;
       $spooler->spoolPrintJob($daughter_id, 'batch_quality');
       
   }
function addDayswithdate($date,$days){
$days = 100;

    $date = strtotime("+".$days." days", strtotime($date));
    return  date("Y-m-d", $date);

}
}
?>
