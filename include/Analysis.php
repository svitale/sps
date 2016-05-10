<?php
lib('dbi');
class Analysis{
    //
    //
    // 
    var $type = null;
    var $id = null;
    var $html = null;
    var $message = null;
   public function selectParams() {
     //   var $html = '';
        $html .= '<form style="margin-top: 1em;">';
        $html .= '<div id="radioset">';
	foreach ($this->types as $key=>$name) {
        $html .= '<input type="radio" id="'.$key.'" name="radio"><label for="'.$key.'">'.$name.'</label>';
	}
	$html .= '</div>';
        $html .= ' </form>';
        return $html;
   }
}
