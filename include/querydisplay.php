<?php
/**
 *Display the results of a query in a javascript table.
 * @param string $query
 * @param string $divId The creates a div with this name to display the query
 * @param array $toCharArray Convert the fields listed from number to characters
 * @param bool $filter Should the table have a filter
 * @param bool $search Should the query have field searching
 * @param bool $csvExport Should it be possible to export the table in csv format
 */
function displayQuery($query, $divId = "querydiv", $toCharArray, $filter = true, $search = true, $csvExport = false) {
	$result = mysql_query($query);
	if (!$result) {
		echo "<br/>unable to perform query: " . mysql_error();
		return;
	}
	if (mysql_affected_rows() == 0) {
		echo "<br/><b>0 results found</b>";
		return;
	}
	echo "<div id='$divId'></div>";
	echo "<script type=\"text/javascript\">";
	echo "var test = new Array();";
	$sArray = "data = new Array(";
	while ($row = mysql_fetch_assoc($result)) {
		$sArray.= "{";
		foreach(array_keys($row) as $key) {
            if (is_array($toCharArray) and in_array($key, $toCharArray))
                $sArray.= "\"$key\": \"" . num2chr($row[$key]) . "\",";
            else
                $sArray.= "\"$key\": \"" . $row[$key] . "\",";
        }
		$sArray = substr($sArray, 0, strlen($sArray) - 1) . "} ,";
	}
	$sArray = substr($sArray, 0, strlen($sArray) - 1) . ");";
	echo "$sArray";
	echo "new TableOrderer('$divId',{data: data, paginate:true, pageCount:10";
	if ($filter) echo ", filter:true ";
	if ($search) echo ", search:true ";
	echo "});";
	echo "</script>";
	
	if($csvExport) {
		echo '<a href="npc.php?action=data&format=csv&type=export">export csv</a>';
	}
	
	return;
}


/**
 *Display a table in a javascript table
 * @param string $table table name
 * @param string $divId
 * @param array $display_fields
 * @param array $edit_fields
 * @param array $where
 */
function displayTable($table, $divId, $display_fields, $edit_fields, $where) {
	$query = "select id, " . implode(", ", $display_fields) . ", " . implode(", ", $edit_fields) . " from $table where true and " . implode("and " , $where);
	$result = mysql_query($query);
	if (!$result) {
		echo "<br/>unable to perform query: " . mysql_error();
		return;
	}
	if (mysql_affected_rows() == 0) {
		echo "<br/><b>0 results found</b>";
		return;
	}
	
	echo "<div id='$divId'></div>";
	echo "<script type=\"text/javascript\">";
	echo "var test = new Array();";
	$sArray = "data = new Array(";
	while ($row = mysql_fetch_assoc($result)) {
		$sArray.= "{";
		foreach(array_keys($row) as $key) {
			if (in_array($key, $edit_fields)){
				$field = $key;
				$width = 30;
				$id = $row['id'];
				$updateString = "
					new Ajax.InPlaceEditor('" . $field . "',
						'npc.php?action=ed&table=$table',
						{formClassName: 'left_column', size: " . $width . ", callback: function(form, value) { return 'value=' + escape(value)+'&id=" . $id . "&field=" . $field . "'}})";
				$sArray.= "\"$key\": \"" . "$updateString" . "\",";
			}
			elseif (in_array($key, $display_fields)){
				if (is_array($toCharArray) and in_array($key, $toCharArray))
					$sArray.= "\"$key\": \"" . num2chr($row[$key]) . "\",";
				else
					$sArray.= "\"$key\": \"" . $row[$key] . "\",";
			}
        }
		$sArray = substr($sArray, 0, strlen($sArray) - 1) . "} ,";
	}
	$sArray = substr($sArray, 0, strlen($sArray) - 1) . ");";
	echo "$sArray";
	echo "new TableOrderer('$divId',{data: data, paginate:true, pageCount:10";
	if ($filter) echo ", filter:true ";
	if ($search) echo ", search:true ";
	echo "});";
	echo "</script>";
	
	echo "<br/><br/>$updateString";
	return;
}
?>