<?php
	$members = mysql_query("SELECT * FROM `batch_quality` WHERE `id_batch` LIKE '$batchUuid' order by `sequence`");
	if (!$members) {
		echo 'Could not run query: ' . mysql_error();
		exit;
	}
    if(mysql_num_rows($members) >0) {
    for($i=0; $i<mysql_num_rows($members); $i++) {
    extract(mysql_fetch_array($members), EXTR_PREFIX_ALL, 'member');
?>
<TR>
<TD WIDTH=28><div  class="cellData" id="id_subject_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>';"><?php echo $member_id_subject?></div>
<TD WIDTH=28><div  class="cellData" id="date_receipt_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>';"><?php echo $member_date_receipt?></div>
<TD WIDTH=28><div  class="cellData" id="date_visit_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>';"><?php echo $member_date_visit?></div>
<TD WIDTH=28><div  class="cellData" id="id_visit_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>';"><?php echo $member_id_visit?></div>
<TD WIDTH=27><div  class="cellData" id=id_alq_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>';"><?php echo $member_id_alq?></div>
</TD>


<TD WIDTH=93><div  class="cellData" id="sample_type_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_sample_type?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('sample_type_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=sample_type'}});
</script>
</TD>



<TD WIDTH=60><div  class="cellData" id="quant_init_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_quant_init?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('quant_init_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=quant_init'}});
</script>
</TD>






<TD WIDTH=36><div  class="cellData" id="quality_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_quality?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('quality_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=quality'}});
</script>
</TD>


<TD WIDTH=45><div  class="cellData" id="error_temp_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_error_temp?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('error_temp_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=error_temp'}});
</script>
</TD>


<TD WIDTH=51><div  class="cellData" id="error_label_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_error_label?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('error_label_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=error_label'}});
</script>
</TD>

<TD WIDTH=39><div  class="cellData" id="error_volume_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_error_volume?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('error_volume_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=error_volume'}});
</script>
</TD>

<TD WIDTH=45><div  class="cellData" id="error_damage_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_error_damage?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('error_damage_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=error_damage'}});
</script>
</TD>

<TD WIDTH=39><div  class="cellData" id="error_delay_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_error_delay?></div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('error_delay_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=error_delay'}});
</script>
</TD>

<TD WIDTH=28><div  class="cellData" id="error_other_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;"><?php echo $member_error_other?> </div>
<script type='text/javascript'>
new Ajax.InPlaceEditor('error_other_<?php echo $i;?>', 'npc.php?action=crfed',{callback: function(form, value) { return 'value=' + escape(value)+'&uuid=<?php echo $member_id_uuid;?>&field=error_other'}});
</script>
</TD>





<TD WIDTH=40><div  class="cellData" id="function_<?php echo $i?>" style="background-color: '<?php echo $bgColor ?>'; cursor: pointer;">
<input type="button" value="X" onclick="$('changeme').innerHTML = 'Refreshing...'; remship('<?php echo $member_id?>','0');return false;">
<?
if ($member_id_parent == '0') {
?>
<input type="button" value="A" onclick="alqid('<?php echo $member_id ?>','batch_quality','1')">
} else {
?>
<input type="button" value="print label" onclick="printlabel('<?php echo $member_id ?>','batch_quality')">
<?
}
?>
</TD>



</TR>
<?
	}
}

	?>
	</TABLE>
	</DIV>
<? 
