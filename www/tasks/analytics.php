<?php
$username = $_SESSION['username'];
if (!in_array('admin',$sps->auth->roles) && !in_array('analytics',$sps->auth->roles)) {
        print 'Unauthorized';
        exit;
}
if (isset($_GET['report'])) {
	$report = $_GET['report'];
} else {
	$report =  'overview';
}
if (isset($_GET['sampletype'])) {
	$sampletype = $_GET['sampletype'];
} else {
	$sampletype =  'any';
}
if (isset($_GET['shipmenttype'])) {
	$shipmenttype = $_GET['shipmenttype'];
} else {
	$shipmenttype =  'any';
}
if (isset($_GET['visit'])) {
	$visit = $_GET['visit'];
} else {
	$visit =  'any';
}
if (isset($_GET['quant'])) {
	$quant = $_GET['quant'];
} else {
	$quant =  'any';
}
if (isset($_GET['site'])) {
	$site = $_GET['site'];
} else {
	$site =  'any';
}
if (isset($_SESSION['id_study'])) {
	$study = $_SESSION['id_study'];
} else {
	$study =  'any';
}
?>
<script src="js/libs/opentip/opentip.js" type="text/javascript"></script>
<script type="text/javascript">
var workbook = 'inventory';
var report = '<?php print $report;?>';
var visit = '<?php print $visit;?>';
var sampletype = '<?php print $sampletype;?>';
var shipmenttype = '<?php print $shipmenttype;?>';
var quant = '<?php print $quant;?>';
var site = '<?php print $site;?>';
var study = '<?php print $study;?>';
//var myData = {'test': ['354313','12'], 'workbook':[workbook], 'report': report, 'visit': visit, 'sampletype'};
var myFilters = {'workbook':workbook,'report':report,'visit':visit,'sampletype':sampletype,'shipmenttype':shipmenttype,'quant':quant,'site':site,'study':study};
//var myData = new Array();
var filterOn = new Array();
for (key in myFilters) {
    filterOn.push(key + '=' + myFilters[key]);
}
filterOn.join('&');
showReport(filterOn);
function showReport(filterOn) {
var url = '/squash_old/api/json/inventory/analytics';
	new Ajax.Request(url, {
	  method: 'post',
parameters: myFilters,
	  requestHeaders: {Accept: 'application/json'},
	  onSuccess: function(transport){
	    var json = transport.responseText.evalJSON(true);
	    renderJson(json);
		},
	  onFailure: function(transport){
$('report_body').innerHTML = "Error: could not connect to server";
	}
	});
}
function renderJson(data) {
	var myhtml = "<div id=graph_img></div>";
	$('report_body').innerHTML = myhtml;
	for (i = 0; i < data.length; i++) {
		graphImage(data[i]);
	}
}
function lut(code) {
var friendly_name;
var codenames = [];
codenames['byvisit'] = 'Visit/Site';
codenames['alqfreq'] = 'Frequency';
codenames['sampletype'] = 'Sample Type';
codenames['quant'] = 'Volume';
codenames['visit'] = 'Visit ID';
codenames['shipmenttype'] = 'Shipment Type';
return codenames[code];
}
function graphImage(data) {
	var filters = data.filters;
	for (var key in filters) {
		makeMenu(key,filters[key])
	}
	var myhtml = '';
	sampletype = data.report.split('-');
	myhtml += '<img src="/squash_old/static/'+ data.path + data.report+ '.png", alt="' +data.report+ '" usemap="#graphimap" border=0/>';
	myhtml += '<a href="/squash_old/static/'+ data.path + data.report+ '.csv">export</a>';
	myhtml += mapHtml(data);
	$('graph_img').innerHTML = myhtml;
	makeTips(data);
}
function mapHtml(data) {
	var dpname, dpid, dprect, dplink, menu = [];
	var report = data.report;
	var elements = data.elements;
	var myhtml = '<map name="graphimap">';
	for (i = 0; i < elements.length; i++) {
		element = elements[i];
		myhtml += '<area shape="rect" id="map_' + element.id  + '" coords="' + element.coords +'" href="#",  alt="' + element.label + '" />';
	}
	myhtml += '</map>';
	return myhtml;
}
function makeMenu(name,values) {
	var selected;

	myhtml = '<p>'+lut(name)+'</p>';
	myhtml += '<select class="btn" name="'+name+'" onChange="toggleUrl(this,name)">';
	if (values.length == 1) {
		myhtml += '<option>any</option>';
		myhtml += '<option selected="selected">' + values + '</option>';
	} else {
		myhtml += '<option selected="selected">any</option>';
		for (i = 0; i < values.length; i++) {
			myhtml += '<option>' + values[i] + '</option>';
		}
	}
	$('analyticsform').insert(myhtml);
}
function makeTips(data) {
	var elements = data.elements;
	var available = data.available;
	var myhtml,sampletype,shipmenttype,quant,site,visit;
	var links = ''; 
	if (data.rparams.sampletype) {
		sampletype = data.rparams.sampletype;
	}
	if (data.rparams.shipmenttype) {
		shipmenttype = data.rparams.shipmenttype;
	} 
	if (data.rparams.quant) {
		quant = data.rparams.quant;
	}
	if (data.rparams.visit) {
		visit = data.rparams.visit;
	}
	if (data.rparams.site) {
		site = data.rparams.site;
	}
	if (data.rparams.quant) {
		quant = data.rparams.quant
	}
	for (i = 0; i < elements.length; i++) {
		if(available=="") {
			myhtml = '<div></div>';
		element = elements[i];
		} else {
		myhtml = '<div><i>Sub-reports:</i>';
		element = elements[i];
		subreports = element.subreports;
		colvars = element.colvars;
		for (k = 0; k < colvars.length; k++) {
			colvar = colvars[k];
			options = colvar.options;	
			values = colvar.values;	
			for (l = 0; l < values.length; l++) {
					value = values[l];
				if (colvar.options == 'shipmenttype') {
					shipmenttype = value;
				}
				if (colvar.options == 'visit') {
					visit = value;
				}
				if (colvar.options == 'sampletype') {
					sampletype = value;
				}
				if (colvar.options == 'quant') {
					quant = value;
				}
				if (colvar.options == 'site') {
					site = value;
				}
			}
		}
		for (k = 0; k < subreports.length; k++) {
		for (j = 0; j < available.length; j++) {
			myhtml += '<div>'+lut(available[j])+'</div>';
				subreport = subreports[k];
				values = subreport.values;	
				options = subreport.options;	
				for (l = 0; l < values.length; l++) {
					value = values[l];
				if (subreport.options == 'shipmenttype') {
					shipmenttype = value;
				}
				if (subreport.options == 'visit') {
					visit = value;
				}
				if (subreport.options == 'sampletype') {
					sampletype = value;
				}
				if (subreport.options == 'quant') {
					quant = value;
				}
				if (subreport.options == 'site') {
					site = value;
				}
					myhtml += '<p>-<a href="?task=analytics&report='+available[j]+'&sampletype='+sampletype+'&shipmenttype='+shipmenttype+'&site='+site+'&visit='+visit+'&quant='+quant+'">'+ value +' </a> ';
				}
			}
		}
		myhtml += links+ '</div>';
		}
		$('map_'+element.id).addTip(myhtml, {title: element.label, showOn: 'click', hideTrigger: 'closeButton',fixed: true });
	}
}
function toggleUrl(selectobj,name) {
	var param;
	var oldurl = document.location.toString();
	oldurl = oldurl.split("#")[0];
	var newurl = oldurl.split("?")[0];
	var params = oldurl.split("?")[1];
	var value = document.getElementsByName(name)[0].value;
	newurl += '?'+name+'='+value;
	if(params) {
		params = params.split("&");
		for (i = 0; i < params.length; i++) {
			param = params[i].split("=");
			if (param[0] != name) {
				newurl += '&'+param[0]+'='+param[1];
			}
		}
	}
	window.location = newurl;
}
</script>
<div id='report_body'></div>
</body>
