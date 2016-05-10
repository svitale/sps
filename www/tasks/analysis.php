<?php
$show_params = array("id_study","instrument");
//if (!isset($_SESSION['datestart'])) {
//$_SESSION['datestart'] = date("Y-m-d");
//}
if (isset($_GET['id_rungroup'])) {
$_SESSION['id_rungroup'] = $_GET['id_rungroup'];
}
?>

<hr />

<div id="resultscontainer"></div>



<script>
new TableOrderer('actioncontainer',
	{
		url : 'npc.php?action=data&format=json&type=review',
		filter:'top',
		paginate:'top',
		pageCount:30,
	}
)
</script>

<script type="text/javascript" charset="utf-8">
var node = new Element('div', {className: 'warning', style: 'border: 1px solid #0F0; display:none'}).update(
new Element('p').update('Are you sure to delete this item?')
).insert(
new Element('input', {type: 'button', value: 'Yes, delete it!', id: 'deleteBut'})
).insert(
new Element('span').update(' or ')
).insert(
new Element('input', {type: 'button', value: 'No, leave it', id: 'cancelBut'})
);
var hideObserver = Modalbox.hide.bindAsEventListener(Modalbox);
function setObservers() {
	$('deleteBut').observe('click', hideObserver);
	$('cancelBut').observe('click', hideObserver);
};
function removeObservers() {
	$('deleteBut').stopObserving('click', hideObserver);
	$('cancelBut').stopObserving('click', hideObserver);
}
</script>


