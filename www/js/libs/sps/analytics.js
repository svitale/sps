var Analytics = function (session) {
    'use strict';
    this.initialize = function() {
        jQuery("#taskcontainer").html("<div id='report_body'></div>");
        var callback = function(data) {
              Loader(data);
        };  
        var url = '/sps/data/analytics/';
        sps.fetchJson(url, callback);
    }; 

    var Loader = function(data) {
        console.log(data);
        var apikey = data.apikey;
        var postdata = {'apikey': apikey,'workbook': 'inventory','study': 'any','shipmenttype': 'any', 'visit': 'any', 'report': 'overview', 'quant': 'any', 'site': 'any', 'sampletype': 'any'};
        var url = '/squash/api/json/inventory/analytics';
	var callback =  function(analytics) {
console.log(analytics);
            renderReport(analytics);
        }
        sps.fetchJson(url, callback, postdata);
    }

    function renderReport(analytics) {
	var myhtml = "<div id=graph_img></div>";
	$('report_body').innerHTML = myhtml;
	for (var i = 0; i < analytics.length; i++) {
	    graphImage(analytics[i]);
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
	var sampletype = data.report.split('-');
	myhtml += '<img src="/squash/R/'+ data.path + data.report+ '.png", alt="' +data.report+ '" usemap="#graphimap" border=0/>';
	myhtml += '<a href="/squash/R/'+ data.path + data.report+ '.csv">export</a>';
	myhtml += mapHtml(data);
	$('graph_img').innerHTML = myhtml;
	makeTips(data);
    }

    function mapHtml(data) {
	var dpname, element, dpid, dprect, dplink, menu = [];
	var report = data.report;
	var elements = data.elements;
	var myhtml = '<map name="graphimap">';
	for (var i = 0; i < elements.length; i++) {
		element = elements[i];
		myhtml += '<area shape="rect" id="map_' + element.id  + '" coords="' + element.coords +'" href="#",  alt="' + element.label + '" />';
	}
	myhtml += '</map>';
	return myhtml;
    }

    function makeMenu(name,values) {
	var selected;
	var myhtml = '<p>'+lut(name)+'</p>';
	//myhtml += '<select name="'+name+'" onChange="analytics.toggleUrl(this,name)">';
	myhtml += '<select name="'+name+'" onChange="var newUrl = Analytics.toggleUrl(this,name);console.log(newUrl);">';
;
	if (values.length == 1) {
		myhtml += '<option>any</option>';
		myhtml += '<option selected="selected">' + values + '</option>';
	} else {
		myhtml += '<option selected="selected">any</option>';
		for (var i = 0; i < values.length; i++) {
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
					myhtml += '<p>-<a href="?task=analytics\
                                        &report='+available[j]+'\
					&sampletype='+sampletype+'\
					&shipmenttype='+shipmenttype+'\
					&site='+site+'&visit='+visit+'\
					&quant='+quant+'">'+ value +' </a> ';
				}
			}
		}
		myhtml += links+ '</div>';
		}
		$('map_'+element.id).addTip(myhtml, 
		{title: element.label, showOn: 'click', 
		hideTrigger: 'closeButton',fixed: true });
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
}
