<?php
include_once('/usr/local/sps/include/lib.php');
lib('Controller/Consolidate');

class FreezerEval{
    var $average_shelf_percentage = 0;
    var $average_rack_percentage = 0;
    var $average_box_percentage = 0;
    var $min_rack_percentage = 100;
    var $min_box_percentage = 100;
    var $total_shelves = 0;
    var $total_racks = 0;
    var $total_boxes = 0;
    var $total_tubes = 0;
    function __construct($freezer) {
        $this->genStats($freezer);
    }
    function genStats($freezer) {
        $freezer->fetchShelves();
        $total_shelf_pts = 0;
        $total_rack_pts = 0;
        $total_box_pts = 0;
        $num_shelves = 0;
        $num_racks = 0;
        $num_boxes = 0;
        $num_tubes = 0;
        foreach($freezer->shelves as $shelf) {
            $num_shelves++;
            $total_shelf_pts = $total_shelf_pts + $shelf->percent_full;
            foreach($shelf->racks as $rack) {
                $num_racks++;
                $total_rack_pts = $total_rack_pts + $rack->percent_full;
                if ($rack->percent_full < $this->min_rack_percentage) {
                    $this->min_rack_percentage = $rack->percent_full;
                }
                foreach($rack->boxes as $box) {
                    $num_boxes++;
                    $total_box_pts = $total_box_pts + $box->percent_full;
                    if ($box->percent_full < $this->min_box_percentage) {
                        $this->min_box_percentage = $box->percent_full;
                    }
                    $num_tubes =  $num_tubes + $box->utilization;
                }
            }
        }
        $this->name = $freezer->name;
        $this->total_racks = $num_racks;
        $this->total_boxes = $num_boxes;
        $this->total_shelves = $num_shelves;
        $this->total_tubes = $num_tubes;
         
        $this->average_shelf_percentage = $total_shelf_pts/$num_shelves;
        $this->average_rack_percentage = $total_rack_pts/$num_racks;
        $this->average_box_percentage = $total_box_pts/$num_boxes;
    }
   
}



$consolidate = new Consolidate();
$res = false;
foreach($consolidate->freezers as $freezer) {
preg_match('/^TCL-/', $freezer->name, $matches);
if($matches[0]){
$freezer->fetchShelves();
$freezer_eval = new FreezerEval($freezer);
print $freezer_eval->name;
print ",";
print $freezer_eval->average_shelf_percentage;
print ",";
print $freezer_eval->average_rack_percentage;
print ",";
print $freezer_eval->average_box_percentage;
print ",";
print $freezer_eval->min_rack_percentage;
print ",";
print $freezer_eval->min_box_percentage;
print ",";
print $freezer_eval->total_shelves;
print ",";
print $freezer_eval->total_racks;
print ",";
print $freezer_eval->total_boxes;
print ",";
print $freezer_eval->total_tubes;
print "\n";
}


}
