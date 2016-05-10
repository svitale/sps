<?
function neworder($id) {
	$sql = "insert into orders (id_item,id_uuid,uuid_parent,id_barcode,id_subject,id_study,id_visit,date_visit,sample_type,user) (select id,id_uuid,id_parent,id_barcode,id_subject,id_study,id_visit,date_visit,sample_type,'' from items where id = $id)";
	$result = mysql_query($sql);
	$id = mysql_insert_id();
	if ($id > '0') {
		return true;
	} else {
		return false;
	}
}
function deleteorder($id) {
	$sql = "delete from orders where id = $id";
	$result = mysql_query($sql);
}
function newresult($id,$id_assay) {
	$sql = "insert into results (id_assay,id_item,id_uuid,id_subject,id_study,id_visit,date_visit,sample_type,datetime_assay) (select '$id_assay',id,id_uuid,id_subject,id_study,id_visit,date_visit,sample_type,now() from items where id = $id)";
	$result = mysql_query($sql);
	$id = mysql_insert_id();
	if ($id > '0') {
		return true;
	} else {
		return false;
	}
}
?>
