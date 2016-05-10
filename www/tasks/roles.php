<?php
$username = $_SESSION['username'];
if (!in_array('admin',$sps->auth->roles)) {
	print 'Unauthorized';
	exit;
}
?>
<div id="rolescontainer"></div>
<script>
new TableOrderer('rolescontainer',
	{
		url : '/sps/data/roles/',
		filter:'top',
		paginate:'top',
		pageCount:30,
	}
)
</script>
