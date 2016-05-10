<?php
lib('dbi');
global $id_study,$dbrw;
$sql = "select description,name from process_header where id_study = '$id_study'";
$result = mysqli_query($dbrw,$sql);
$i = 0;
$html = '<table><td>';
while ($row = mysqli_fetch_array($result)) {
$description =  $row['description'];
$code = 'npc:proc;' . $row['name'];
$col = ($i % 3)."\n";
$img_html = '<div style="text-align:center;">'.$description;
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
