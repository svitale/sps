<?php
lib('Task');
lib('Model/ShipmentModel');
lib('mongodb');
class Tracking{
   /** 
   * ]
   */
    
    function __construct() {
        $this->shipments = $this->fetchShipments();
        $this->sites = $this->listSites();
    }

 public function listSites($site=false) {
    $array = array();
    if($site) {
                $array[] = array('id'=>$site,'name'=>$site);
    } else {
                $array[] = array('id'=>'0101','name'=>'0101');
                $array[] = array('id'=>'0201','name'=>'0201');
                $array[] = array('id'=>'0202','name'=>'0202');
                $array[] = array('id'=>'0302','name'=>'0302');
                $array[] = array('id'=>'0303','name'=>'0303');
                $array[] = array('id'=>'0304','name'=>'0304');
                $array[] = array('id'=>'0401','name'=>'0401');
                $array[] = array('id'=>'0402','name'=>'0402');
                $array[] = array('id'=>'0403','name'=>'0403');
                $array[] = array('id'=>'0501','name'=>'0501');
                $array[] = array('id'=>'0601','name'=>'0601');
                $array[] = array('id'=>'0701','name'=>'0701');

    }
    return $array;
 }
 public function fetchShipments($site=false) {
        global $mongodb;
        $db = $mongodb->finch;
        $collection = $db->shipments;
	//if ($site) {
	//}
	$array = array();
        foreach ($collection->find() as $document) {
            $_id = $document['_id'];
            $id = $document['_id']->{'$id'};
            unset($document['_id']);
            $shipment = $document;
            $shipment['id'] = $id;
            $array[] = $shipment;
        }       
	return $array;
    }       
 public function fetchShipment($id) {
    $shipment = new Shipment();
    $shipment->id = $id;
    $shipment->fetch();
    return $shipment;
 }
 public function createShipment($site) {
    $shipment = new Shipment();
    $shipment->create();
    $shipment->site = $site;
    $shipment->modify();
    return $shipment;
 }
 public function deleteShipment($id) {
    $shipment = new Shipment();
    $shipment->id = $id;
    $shipment->delete();
    return $shipment;
 }

}
