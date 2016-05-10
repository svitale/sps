<form id="daterange">



<div id="popup_daterange">

    <input id="datestart" image="calendar_date_select/calendar.gif" name="datestart" type="hidden"/>
<div id="cds_placeholder_start"></div>
    <input id="dateend" image="calendar_date_select/calendar.gif" name="dateend" type="hidden"/>
<div id="cds_placeholder_end"></div>
<script type="text/javascript">
//<![CDATA[
new CalendarDateSelect( $('cds_placeholder_start').previous(), {embedded:true, time:true, year_range:10, format:'iso_date' } ); 
new CalendarDateSelect( $('cds_placeholder_end').previous(), {embedded:true, time:true, year_range:10, format:'iso_date'} ); 
//]]>
</script>
<?
//<input type="button" value="set" onclick="$('popup_daterange').innerHTML = 'Refreshing...'; daterange();$('daterange').reset();$('daterange').activate();return false;" 
?>
<input type="button" value="set range" onclick="setdates()">
<div> <p><a class="popup_closebox" href="#">Close box</a></p>
</form>
</div>
</div>
                </span></p>


