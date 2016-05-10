
<?php
if (isset($_SESSION['containerid'])) {
    $containerid = $_SESSION['containerid'];
} else {
    $containerid = 'null';
}
?>
<div id="detailcontainer" class="span6 float-right"></div>
<script type="text/javascript">
var containerid = <?php print $containerid?>;
if (containerid) {
    postId(containerid);
} else {
    var html = '<div class="col-xs-3"><div class="btn"></div><div class="row"><input type="button" class="btn span5" value="new container" onclick="newContainer()"></div></div>';
    jQuery("#actioncontainer").html(html);
     //set html div classes for layout
} 
    jQuery("#taskcontainer").addClass('row');
    jQuery("#actioncontainer").addClass('col-md-7');
    jQuery("#staticcontainer").addClass('col-md-5');
</script>
