/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */
var Store = function(session) {
	'use strict';
	this.initialize = function() {
		var callback = function(store) {
			Loader(store);
		};
		var url = '/sps/data/store/';
		var scanner_html = '<input type="text" id="scanIn" name="scanIn" autocomplete="off" class="input" value="" onchange="var store = new Store();store.scanner();"/>';
		sps.fetchJson(url, callback);
		jQuery('#scanner').html(scanner_html);
	};

	this.scanner = function() {
		var code = jQuery('#scanIn').val();
		var url;
		var sps = new Sps();
		var callback;
		var postdata;
		var scanned = [];
		scanned = sps.ScannedCodeParser(code);
		if (scanned.codetype == 'process') {
			var process = new Process();
			process.Apply(scanned.codevalue);
		} else if (scanned.codetype == 'uuid') {
			callback = function(data) {
				Loader(data);
			};
			postdata = {
				'id_uuid' : scanned.codevalue,
				'interface' : 'scanner'
			};
			url = '/sps/data/ao/store';
			jQuery('scanIn').disabled = "disabled";
			jQuery('scanIn').value = "Working...";
			sps.fetchJson(url, callback, postdata);
			doClear('scanIn');
			jQuery('scanIn').disabled = false;
			setfocus('scanIn');
		} else {
			console.log('can not figure out what to do with code:' + code);
			doClear('scanIn');
		}
	};

	var Loader = function(store) {
                jQuery('#taskcontainer').empty();
                jQuery('#taskcontainer').append('<div id="detailcontainer"></div>');
                jQuery('#taskcontainer').append('<div id="batchcontainer"></div>');
		if (store.active_object.id) {
                    console.log("loading active object");
                    var ao = new activeObject();
                    ao.orientation = "vertical"
                    ao.detail(store);
		}
	};
        this.makeActiveObject = function(id) {
                var callback = function(store) {
                    var ao = new activeObject();
                    ao.detail(store);
                };  
                var postdata = { 
                        'id' : id,
                };  
                var url = '/sps/data/ao/selector';
                sps.fetchJson(url, callback, postdata);
        }; 
        this.alterActiveObjectFieldValue = function(field,value) {
                var callback = function(store) {
                    var ao = new activeObject();
                    ao.detail(store);
                };  
                var postdata = { 
                        'field' : field,
                        'value' : value,
                };  
                var url = '/sps/data/ao/alter';
                sps.fetchJson(url, callback, postdata);
        }; 
        this.obfuscate = function(id) {
                var url = '/sps/data/Randomizer/'+id;
                jQuery.post( url, { action: 'obfuscate' })
                    .done(function( data ) {
                     console.log(data);
                     });
                jQuery('#cue').html('obfuscating' + id);
        };

}
