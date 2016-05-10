/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */
var Crf = function(session) {
	'use strict';
	this.initialize = function() {
		var callback = function(crf) {
			Loader(crf);
		};
		var url = '/sps/data/crf/';
		var scanner_html = '<input type="text" id="scanIn" name="scanIn" autocomplete="off" class="input" value="" onchange="var crf = new Crf();crf.scanner();"/>';
		sps.fetchJson(url, callback);
		jQuery('#scanner').html(scanner_html);
	};

	this.scanner = function() {
		var code = jQuery('#scanIn').val();
		var url;
		var callback;
		var postdata;
		var scanned = [];
		scanned = sps.ScannedCodeParser(code);
		if (scanned.codetype == 'process') {
			var process = new Process(session);
			process.Apply(scanned.codevalue);
		} else if (scanned.codetype == 'npc') {
                    //TODO: make this not so hacky
                    console.log("legacy code -- ignoring");
                    console.log(scanned);
		} else if (scanned.codetype == 'uuid') {
			callback = function(crf) {
				Loader(crf);
			};
			postdata = {
				'id_uuid' : scanned.codevalue,
				'interface' : 'scanner'
			};
			url = '/sps/data/ao/';
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

	var Loader = function(crf) {
                if (session && session.debug) {
                    console.log("loading crf");
                    console.log(crf);
                }
                var state = crf.state;
		jQuery('#taskcontainer').empty();
		jQuery('#taskcontainer').append('<div id="batchcontainer"></div>');
		jQuery('#taskcontainer').append('<div id="detailcontainer"></div>');
		if (crf.error) {
                        var warning = '<span class="label label-warning">';
                        warning += crf.message;
                        warning += '</span>';
		        jQuery('#taskcontainer').html(warning);
		} else if (state == 'stage') {
			console.log('found tmptable' + crf.tmptable);
			Import();
		} else if (state == 'batch') {
			console.log('loading batch');
			Batch(session,crf);
		} else if (session.active_study.id_extdb_header) {
			console.log('handing off to extdb loader');
			ExtDbLoader(session.active_study.id_extdb_header);
		} else {
			console.log('falling back to file import');
			fileImporter(crf.xls_template);
		}
		if (crf.active_object) {
			console.log("loading active object");
                        var ao = new activeObject(session);
			ao.orientation = "horizontal"
			ao.detail(crf);
		}
	};

	var fileImporter = function(data) {
		var link = '<a href="/sps/data/crf/' + data + '">template</a>';
		var html = '<h2>scan something or use the  ' + link + ':</h2>';
		html += '<div><form action="/sps/data/crf/fileimport.php" method="post" enctype="multipart/form-data">';
		html += '<label for="file">Import a File</label>';
		html += '<input type="file" size = "15" name="file" id="file" />';
		html += '<input type="submit" name="submit" value="Submit" />';
		html += '</form></div>';
		jQuery('#taskcontainer').html(html);
	};
	var ExtDbLoader = function() {
		jQuery('#batchcontainer').append('<div id=redcapcrf></div>');
		jQuery('#batchcontainer').append('<div id=redcapcrf_imported></div>');
		jQuery('#batchcontainer').append(
				'<br/><div id=redcapupdatedstudy></div>');
		var redcap = new Redcap();
		redcap.updatedStudiesLoader();
		redcap.collectionsLoader(false);
		redcap.collectionsLoader(true);
	};
	var Batch = function(session,crf) {
		console.log("get batch");
                if (session && session.debug) {
		 	console.log(crf);
                }
		var url = '/sps/data/crf/batch';
		var callback = function(crf) {
			handleBatch(crf);
		};
		sps.fetchJson(url, callback);
	};
	var Import = function() {
		console.log("get import batch");
		var url = '/sps/data/crf/batch';
		var callback = function(batch_data) {
			handleImport(batch_data);
		};
		sps.fetchJson(url, callback);
	};
	var updateBatch = function(data) {
		console.log("update batch");
		var url = '/sps/data/crf/update';
		var postdata = {
			'update' : data
		};
		var callback = function(batch_data) {
			handleUpdate(batch_data);
		};
		sps.fetchJson(url, callback, postdata);
	};
	var handleBatch = function(data) {
            if (session && session.debug) {
	 	console.log(data);
            }
            var col;
            var field;
            var cols = [];
            var active_object;
            if (data.active_object) {
                active_object = data.active_object;
            } else {
                active_object = false;
            }
            
            for (var element in data.fields) {
                col = [];
                field=data.fields[element] 
                if (field.format == 'ro'  || field.format == 'rw' || field.format == 'batch') {
                    col.id = element;
                    col.name = field.description;
                    col.field = element;
                    cols.push(col);
                 }
             }
		for (var i = 0; i < data.batch.length; i++) {
			var elementid = 'batch_' + data.batch[i].id;
			jQuery('#batchcontainer')
					.append('<div id=' + elementid + ' class="span10"></div>');
			renderGroup(data.batch[i], elementid, data.batchid, cols,  active_object);
		}
		var print_all_button = "<input class='print_all' id='batch_print_button' type='button' value='print all' />";
		jQuery('#batchcontainer').append("<div id='batch_apply' class='span10'></div>");
		jQuery('#batch_apply').append(print_all_button);
		jQuery('.print_all').on('click', function(buttondata) {
			var printjob = new Printjob(session);
			printjob.jobview = buttondata.target.id;
			printjob.type = 'batch';
			printjob.batchid = data.batchid;
			printjob.submitJob();
		});
		jQuery('.print_one').on('click', function(buttondata) {
			var printjob = new Printjob(session);
			printjob.jobview = buttondata.target.id;
			printjob.type = 'single';
			printjob.table = 'batch_quality';
			printjob.uuid = buttondata.target.name;
			printjob.submitJob();
		});
		jQuery('.delete').on('click', function(data) {
			var object_uuid = data.target.id;
			removeFromBatch(object_uuid);
		});
		function buttonFormatter(row, cell, value, columnDef, dataContext) {
			var html  = "<input class='delete' type='button'  id='"
					+ value + "' value='x' />";
			html += "<input class='print_one' type='button' id='print"+row+"' name='"
					+ value + "' value='print' />";
			return html;
		}
		;
		function detailButtonFormatter(row, cell, value, columnDef, dataContext) {
			var button = "<input class='detail' type='button'  id='"
					+ value + "' value='more' />";
					+ "</span>";
			return button;
		}
		;
	};
	var handleImport = function(data) {
		var columns = [ {
			id : "i",
			name : "#",
			field : "i",
			width :  3
		}, {
			id : "id_study",
			name : "Study",
			field : "id_study",
			width : 16 
		}, {
			id : "id_subject",
			name : "Subject",
			field : "id_subject",
			width : 16 
		}, {
			id : "date_visit",
			name : "Visit Date",
			field : "date_visit",
			type : "date",
			width : 13 
		}, {
			id : "sample_type",
			name : "Sample Type",
			field : "sample_type"
		} ];
		var tracked_field;
		var field;
		var record;
		for ( var i = 0; i < data.fields.length; i++) {
                    field = data.fields[i];
                    tracked_field =  {id : i, name : field.description, field : data.fields[i] };
                    columns.push(tracked_field);
                }
		var elementid = 'batch_';
		jQuery('#batchcontainer').append('<div id=' + elementid + '></div>');
		renderGroup(data.records, elementid, 'batchname', columns, false);
		var html = '<h2>The following Records will be imported</h2>';
		jQuery('#detailcontainer').html(html);
		var button = "<input class='import' type='button'  id='"
				"' value='import' />";
		jQuery('#detailcontainer').append(button);
		jQuery('.import').on('click', function(buttondata) {
			var update = {
				'import' : buttondata.target.id
			};
                        jQuery('#detailcontainer').empty();
			updateBatch(update);
		});

	};
	var handleUpdate = function(data) {
		jQuery('#batchcontainer').empty();
		Batch(session);
	};

	var renderGroup = function(samplelist, elementid, batchid, columns, ao) {
                var grid;
                var name='foo';
                var selectedRows = [];
		var options = {
			autoWidth : true,
			autoHeight : true,
			enableCellNavigation : true,
			forceFitColumns : true,
                        editable: false,
                        enableAddRow: true,
                        autoEdit: false
		};
		var html = '<h4>Subject:' +name + '</h4>';
		html += '<div id="' + elementid + '_common_grid"></div>';

		var sampledata = [];
		for ( var i = 0; i < samplelist.length; i++) {
			samplelist[i]["i"] =  i + 1;
			if (ao && ao.id_uuid == samplelist[i].id_uuid) {
                            selectedRows = [i];
                        };
		}
		jQuery('#' + elementid).html(html);
		var dataView = new Slick.Data.DataView({
			inlineFilters : true
		});
		dataView.beginUpdate();
		dataView.setItems(samplelist);
		dataView.endUpdate();
                grid =  new Slick.Grid("#" + elementid + "_sample_grid", dataView, columns,options);
                grid.setData(samplelist);
                if (ao) {
                    grid.setSelectionModel(new Slick.RowSelectionModel());
                    var selectedData = [],
                    selectedRecord = [],
		    activeUUID = ao.id_uuid,
                    selectedIndexes;
                    grid.onSelectedRowsChanged.subscribe(function() { 
                       selectedIndexes = grid.getSelectedRows();
                       selectedData = grid.getData()[selectedIndexes[0]];
                       if (selectedData) {
                          activeUUID = selectedData.id_uuid;
                          makeActiveObject(activeUUID);
                        }
                     });
                    grid.resetActiveCell();
                    grid.setSelectedRows(selectedRows);
                 }
                    grid.render();
	};
	var makeActiveObject = function(id_uuid) {
		var callback = function(crf) {
                    var ao = new activeObject(session);
   	 		ao.detail(crf);
		};
		var postdata = {
			'id_uuid' : id_uuid,
			'interface' : 'button'
		};
		var url = '/sps/data/ao/selector';
		sps.fetchJson(url, callback, postdata);
	};
	var addToBatch = function(id_uuid,batchid) {
		var callback = function(data) {
                console.log('add to batch');
                location.reload();
		};
		var postdata = {
                        'update' : {'addtobatch' : batchid, 'id_uuid' : id_uuid}
		};
		var url = '/sps/data/crf/update';
		sps.fetchJson(url, callback, postdata);
        }
	var removeFromBatch = function(id_uuid,batchid) {
		var callback = function(data) {
                console.log('remove from batch');
                location.reload();
		};
		var postdata = {
                        'update' : {'removefrombatch' : true, 'id_uuid' : id_uuid}
		};
		var url = '/sps/data/crf/update';
		sps.fetchJson(url, callback, postdata);
        }
}
