<?php
lib('Task');
lib('Model/Shipment');
lib('mongodb');
class Shipment{
   /** 
   * ]
   */
 public function fetchShipment($id) {
    $shipment = new ShipmentModel();
    $shipment->id = $id;
    $shipment->fetch();
    return $shipment;
 }
 public function createShipment($site) {
    $shipment = new ShipmentModel();
    $shipment->create();
    $shipment->site = $site;
    $shipment->modify();
    return $shipment;
 }
 public function deleteShipment($id) {
    $shipment = new ShipmentModel();
    $shipment->id = $id;
    $shipment->delete();
    return $shipment;
 }

}
