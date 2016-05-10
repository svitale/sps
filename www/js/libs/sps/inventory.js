/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */
var Inventory = function(session) {
	'use strict';
	this.initialize = function() {
		var callback = function(data) {
			Loader(data);
		};
		var url = '/sps/data/inventory/';
                var postdata = {'limit':100, 'start':0};
		sps.fetchJson(url, callback, postdata);
	};
	var Loader = function(data) {
           if (session && session.debug) {
               console.log("loading inventory");
           }
	   jQuery('#taskcontainer').empty();
	   jQuery('#taskcontainer').append('<div id="inventorycontainer"></div>');
           handleInventory(data);
         }

	var handleInventory = function(data) {
         if (data.total == 0) {
		jQuery('#inventorycontainer').html("No Records found");
         } else {
            var matched = data.records;
            var col;
            var field;
            var cols = [];
            var active_object;
            for (var element in data.fields) {
                field=data.fields[element] 
                if (field.format == 'ro'  || field.format == 'rw' || field.format == 'batch') {
                    col = [];
                    col.id = field.field;
                    col.name = field.comment;
                    col.field = field.field;
                    cols.push(col);
                 }
             }

		cols.push({
			id : "id_uuid",
			name : "Barcode",
			field : "id_uuid",
                        formatter : 
	                  function(row, cell, value, columnDef, dataContext) {
                                  return value.substr(0,8);
	                   }
		});

		cols.push({
			id : "freezer",
			name : "Freezer",
			field : "freezer",
		});
		cols.push({
			id : "subdiv1",
			name : "shelf",
			field : "subdiv1",
		});
		cols.push({
			id : "subdiv2",
			name : "rack",
			field : "subdiv2",
		});
		cols.push({
			id : "subdiv3",
			name : "box",
			field : "subdiv3",
		});
		cols.push({
			id : "subdiv4",
			name : "row",
			field : "subdiv4",
		});
		cols.push({
			id : "subdiv5",
			name : "col",
			field : "subdiv5",
		});

		renderGroup(matched, "inventorycontainer", cols,  active_object);
            }
	};
	var renderGroup = function(samplelist, elementid, columns, ao) {
                var grid;
                var selectedRows = [];
		var options = {
			autoWidth : true,
			autoHeight : true,
			enableCellNavigation : true,
			forceFitColumns : true,
                        editable: false,
                        enableAddRow: true,
			enableTextSelectionOnCells: true,
                        autoEdit: false
		};
		var html = '<div id="' + elementid + '_common_grid"></div>';
		html += '<div id="' + elementid + '_sample_grid"></div>';


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
}
