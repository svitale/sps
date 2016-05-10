<?
function crForm($id_subject,$key) {
	$batchuuid = $_SESSION['batchuuid'];
	$out =  '<div style="width:412px; background-color: lightgrey";>';
	$query = "SELECT count(*) as parentcount,id_parent,id_subject,date_collection,batch_quality.sample_type,batch_quality.id_visit,batch_quality.family,crf.quantity,crf.`num_order` FROM `batch_quality` left join crf on (batch_quality.family = crf.family and batch_quality.shipment_type = crf.shipment_type and batch_quality. sample_type = crf.sample_type) WHERE `id_batch` = '$batchuuid' and id_subject = '$id_subject' ";
	$query .= " group by id_subject,sample_type,id_visit,id_parent,batch_quality.family order by id_subject,crf.num_order,sample_type,id_visit,id_parent DESC;";
	$result = mysql_query($query);
	if (!$result) {
	$out = 'Could not run query: ' . mysql_error();
       exit;
        }
	$j = 0;
	while ($row = mysql_fetch_object($result)) {
        $id_parent =  $row->id_parent;
        $parentcount =  $row->parentcount;
        $date_collection=  $row->date_collection;
        $family =  $row->family;
        $id_subject =  $row->id_subject;
        $sample_type =  $row->sample_type;
        $id_visit =  $row->id_visit;
	if (isset($row->quantity)) {
        $quantity =  $row->quantity;
	} else {
        $quantity =  0;
	}
	if (isset($_SESSION['quantity']{$id_subject}{$sample_type}{$id_visit}{$family})) {
        $quantity =  ($_SESSION['quantity']{$id_subject}{$sample_type}{$id_visit}{$family});
	}
	if ($id_parent == '0') {
	$class = 'crfparent';
	} else {
	$class = 'crfchild';
	$count{$id_subject}{$sample_type}{$id_visit}{$family} = $parentcount; 
	} 



if ($id_subject != $last_subject) {
	$out .=  '<div style="width:411px">';
	$out .=  '<div style="width:100px"class="left_column">'.$id_subject.'</div>';
	$out .= '<div style="width:100px"class="left_column">'.$id_visit.'</div>';
	$out .= '<div style="width:100px"class="left_column">'.$family.'</div>';
	$out .= '<div style="width:100px"class="left_column">'.$date_collection.'</div>';
	$out .= '</div>';
}



	if ($class == 'crfparent') {
	$width = '78';
	$out .=  '<div style="width:311px">';
	$out .= '<div class="left_column" id="parentcount_'.$key.'_'.$j.'"  style="width: 25px; background-color: lightblue">'.$parentcount.'</div>';
	$out .= '<div class="'.$class.'" id="sample_type_'.$key.'_'.$j.'"  style="width: 120px;">'.$sample_type.'</div>';
if (isset($count{$id_subject}{$sample_type}{$id_visit}{$family})) {
	$out .= '<div class="'.$class.'" id="quantity_'.$key.'_'.$j.'"  style="width: 100px; background-color: lightgreen"">0</div>';
	$out .= '<div class="'.$class.'" id="alqs_'.$key.'_'.$j.'"  style="width: 38px; background-color: lightgrey"">'.$count{$id_subject}{$sample_type}{$id_visit}{$family}.'</div>';
        $_SESSION['quantity']{$id_subject}{$sample_type}{$id_visit}{$family} = 0;
} else {
	$out .= '<div class="'.$class.'" id="quantity_'.$key.'_'.$j.'"  style="width: 100px; background-color: lightgreen"">'.$quantity.'</div>';
	$out .= '<div class="'.$class.'" id="alqs_'.$key.'_'.$j.'"  style="width: 38px; background-color: lightgrey"">0</div>';
}

	$out .= "<script type='text/javascript'>
	new Ajax.InPlaceEditor('quantity_".$key."_".$j."', 'npc.php?action=alqed',{size: 3, callback: function(form, value) { return 'value=' + escape(value)+'&=quantity_".$key."_".$j."&id_subject=".$id_subject."&sample_type=".$sample_type."&id_visit=".$id_visit."&field=quantity'}})</script>";
	$out .= '</div>';
	}




        $last_subject =  $row->id_subject;
	$j++;
	}
	$out .=  '<div style="width:311px">';
	$out .= "<input type=\"button\" value=\"Aliquot\" onclick=\"alqbatch('".$id_subject."')\">";
	$out .= "<input type=\"button\" value=\"Print\" onclick=\"printalqs('".$id_subject."')\">";
	$out .= "</div>";
	$out .= $outchild;
	$out .= '</div>';
	return $out;
}
?>
