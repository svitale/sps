<?php
$show_params = array("id_study","instrument");
if (!isset($_SESSION['datestart'])) {
$_SESSION['datestart'] = date("Y-m-d");
}
?>

<hr />

<div id="resultscontainer"></div>



<script>
new TableOrderer('resultscontainer',{url : 'npc.php?action=retreceipt' });
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


