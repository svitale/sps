<?php 
$templatedir = "xltemplates/instruments/";
?>
<div id="dashboard" class="dashboard">
	<div>
		<form action="npc.php?action=importspreadsheet" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Import Orders</legend>
				<label for="file">Worksheet:</label>
				<input type="file" size = "15" name="file" id="file" />
				<input type="hidden" name="usetemplate" value="orders" />
				<input type="submit" name="submit" value="Submit" />
			</div>
		</form>
	</div>
</div>  
<div id="datacontainer"></div>
<?
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
<?



} else {
dashBoard();
}
