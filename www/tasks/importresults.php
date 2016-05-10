<?php 
lib('Xlt');
?>
<div id="dashboard" class="dashboard">
	<div>
		<form action="npc.php?action=importspreadsheet" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Import Results</legend>
<?php
		$fileArray = listFiles($template_dir ,'xlt');
		if (count($fileArray) > 0) {
	?>	
			<div><label>Format: <select name="usetemplate">
				<option value="defaultreader">auto</option>;
	<?php
			foreach ($fileArray as $file) {
				echo '			<option value="'.$file.'">'.$file.'</option>';
			}
			echo '		</select></label></div>';
		}
?>
			<div>
				<label for="file">Worksheet:</label>
				<a href="xltemplates/default.xlt">template</a> 
				<input type="file" size = "15" name="file" id="file" />
				<input type="submit" name="submit" value="Submit" />
			</div>
		</form>
	</div>
</div>  
<div id="datacontainer"></div>
<?php
if (isset($_SESSION['tmptable'])) {
?>
                <script type="text/javascript">
        new Ajax.Updater('dashboard', 'npc.php?action=dashboard', {asynchronous:true,
                onSuccess: function() {
                        //new TableOrderer('datacontainer',{url : 'npc.php?action=data&format=json&type=import'});
			new TableOrderer('datacontainer',{url : 'npc.php?action=data&format=json&type=import', filter:'top', paginate:'top', pageCount:81});
                },
                onFailure: function() {
                        alert('error setting datacontainer');
                },
        });




                </script>
<?php



} else {
dashBoard();
}
