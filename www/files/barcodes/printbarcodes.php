<?php
$codeArray = array(
//$codeArray = array('Results'=>array(
'New Container Like Last Scanned'=>'npc:clonecont',
'Export Manual Elisa'=>'npc:manualelisasing',
//),
//$codeArray = array('Initial Volume'=>array(
'Thaw tube'=>'npc:thawtube',
'Initial Volume = .25 ml'=>'npc:vol_init;.25',
'Initial Volume = .50 ml'=>'npc:vol_init;.50',
'Initial Volume = .75 ml'=>'npc:vol_init;.75',
'Initial Volume = 1.0 ml'=>'npc:vol_init;1',
'Initial Volume = 1.25 ml'=>'npc:vol_init;1.25',
'Initial Volume = 1.50 ml'=>'npc:vol_init;1.50',
'Initial Volume = 1.75 ml'=>'npc:vol_init;1.75',
'Initial Volume = 2 ml'=>'npc:vol_init;2',
'Initial Volume = 3 ml'=>'npc:vol_init;3',
'Initial Volume = 4 ml'=>'npc:vol_init;4',
'Initial Volume = 5 ml'=>'npc:vol_init;5',
'Initial Volume = 6 ml'=>'npc:vol_init;6',
'Initial Volume = 7 ml'=>'npc:vol_init;7',
'Initial Volume = 8 ml'=>'npc:vol_init;8',
'Initial Volume = 9 ml'=>'npc:vol_init;9',
'Initial Volume = 10 ml'=>'npc:vol_init;10',
//),
//array(' Volume'=>array(
'Current Volume = .25 ml'=>'npc:vol_cur;.25',
'Current Volume = .50 ml'=>'npc:vol_cur;.50',
'Current Volume = .75 ml'=>'npc:vol_cur;.75',
'Current Volume = 1.0 ml'=>'npc:vol_cur;1',
'Current Volume = 1.25 ml'=>'npc:vol_cur;1.25',
'Current Volume = 1.50 ml'=>'npc:vol_cur;1.50',
'Current Volume = 1.75 ml'=>'npc:vol_cur;1.75',
'1 Aliquot'=>'npc:aliquot;1',
'2 Aliquot'=>'npc:aliquot;2',
'aliquot entire box'=>'npc:alq_box');
$i = 0;
$html = '<table><td>';
foreach ($codeArray as $key=>$code) {
$col = ($i % 3)."\n";
$img_html = '<div style="text-align:center;">'.$key;
$img_html .= '<br>';
$img_html .= '<img src="/sps/util/bcimage.php?code='.$code.'"></div>';
$img_html .= '</br></br>';
if (($i % 3) == 0) {
$html .= "<tr>";
}
$html .= $img_html;
if (($i % 3) == 2) {
$html .= "</tr>\n";
}
$i++;
}
$html .= '</td></table>';
print $html;
