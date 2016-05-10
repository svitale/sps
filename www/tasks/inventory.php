<?php
lib('inventoryview');
if (isset($_SESSION['containerid'])) {
	echo '<script type="text/javascript">';
	echo "postId(" . $_SESSION['containerid'] . ",'tableId')";
	echo "</script>";
} else {
?>
    <div>
        <h2>Search by Worksheet</h2>
        <form action="util/find_items.php" method="post"
            enctype="multipart/form-data">
            <label for="file">Search using a worksheet </label> (use this <a href="demo/findtemplate.xls">template</a>)
            <input type="file" size = "15" name="file" id="file" />
            <input type="submit" name="submit" value="Submit" />
        </form>
    </div>
    
    <br/>
<?php
	$searchFields = array(
		'id_study',
		'sample_type',
		'id_subject',
		'shipment_type',
		'id_visit'
	);
	foreach($searchFields as $searchField) {
		if (isset($_POST[$searchField]) and (strlen(mysql_real_escape_string(trim($_POST[$searchField]))) > 0)) {
			$_SESSION[$searchField] = mysql_real_escape_string(trim($_POST[$searchField]));
			echo "<br/>set $searchField = " . $_SESSION[$searchField];
		}
	}
	if (isset($_SESSION['Detailid'])) {
		echo "<script type=\"text/javascript\">";
		echo "new TableOrderer('taskcontainer',{url : 'npc.php?action=data&format=json&type=detailId', filter:'top', paginate:'top', pageCount:20});";
		echo "</script>";
	} else if (((isset($_SESSION['id_subject']) && ($_SESSION['id_subject'] != '%')) && (isset($_SESSION['id_study']) && ($_SESSION['id_study'] != '%'))) || ((isset($_SESSION['shipment_type']) && ($_SESSION['shipment_type'] != '%')) && (isset($_SESSION['id_visit']) && ($_SESSION['id_visit'] != '%')) && (isset($_SESSION['id_study']) && ($_SESSION['id_study'] != '%')) && (isset($_SESSION['sample_type']) && ($_SESSION['sample_type'] != '%')))) {
		echo "<script type=\"text/javascript\">";
		echo "new TableOrderer('taskcontainer',{url : 'npc.php?action=data&format=json&type=search', filter:'top', paginate:'top', pageCount:1000});";
		echo "</script>";
	}
}
?>
