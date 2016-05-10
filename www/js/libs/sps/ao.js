/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */
var activeObject = function(session) {
    'use strict';
    var orientation;
    this.detail = function(data) {
        var object = data.active_object;
        var tracked_fields = data.tracked_fields;
        var html,aotitle,aoelement,aoelement_id,hidden,field;
        var disabled,selected,bgcolor;
        console.log("orientation is "+this.orientation);
        if (this.orientation === 'horizontal') {
           var width = 8;
        } else if (this.orientation === 'vertical') {
           var width = 4;
        }
        jQuery('#detailcontainer').empty();
        html =  '<div id="activeobjectcontainer" class="container span10 active_object">';
        html += '      <div id="aoelements_left" class="span4">'
        html += '          <table id="aoelementstable_left"></table>';
        html += '      </div>';
        html += '      <div id="aoelements_right" class="span4">'
        html += '          <table id="aoelementstable_right"></table>';
        html += '      </div>';
        html += '   <div id="aoprocess" class="span10"></div>'
        html += '</div>';
        jQuery('#detailcontainer').append(html);
        var table_left  = true;
        for ( var element in tracked_fields) {
            field = tracked_fields[element];
            field.value = object[element];
            if ((field.format != "hidden") && (field.format != "batch")) {
                if (field.format === 'ro' || field.format === 'detail') {
                    disabled = ' disabled ';
                } else {
                    disabled = '';
                }
                aoelement_id = 'element_' + element;
                aoelement = '<tr>';
                aoelement += '<td>'+ field.comment +'</td>';
                aoelement += '<td>';
                if (!field.options) {
                    aoelement += '<input  type="text" ';
                    aoelement += disabled;
                    aoelement += ' id="' + aoelement_id +'" value="'+field.value+'">';
                } else {
                    aoelement += '<select id="' + aoelement_id +'" >';
                    aoelement += '<option value=""></option>';
                    for ( var i = 0; i < field.options.length; i++) {
                    if (field.value == field.options[i]) {
                        selected = "selected";
                    } else {
                        selected = "";
                    }
                    aoelement += '<option value="'+field.options[i]+'" '+selected+'>'+field.options[i]+'</option>';
                    }
                    aoelement += '</select>';
                   }
                aoelement += "</td>";
                aoelement += "</tr>";
		if (table_left) {
                	jQuery('#aoelementstable_left').append(aoelement);
			table_left = false;
                } else {
                	jQuery('#aoelementstable_right').append(aoelement);
			table_left = true;
                }
                if (field.format === 'rw') {
                	if (field.type == 'date') {
		    		jQuery( "#"+aoelement_id ).datepicker({dateFormat: "yy-mm-dd" });
                	}
		    		jQuery( "#"+aoelement_id ).attr("onchange", "ao=new activeObject();ao.alter('"+element+"',this.value)");
		}
            }
        };
        if (object.type == 'tube') {
            var sps_process = new Process(session);
            sps_process.initialize();
        }
    };  
    this.alter = function (field,value) {
        var url = '/sps/data/ao/alter';
        var postdata = {
            'field' : field,
            'value' : value 
        };
        var callback = function(session) {
               var ao = new activeObject(session);
               var taskname = session.task
           if (taskname == 'crf') {
               var crf = session.crf;
               ao.detail(crf);
           } else if (taskname == 'store') {
               var store = session.store;
               ao.detail(store);
           }
        }
	sps.fetchJson(url, callback, postdata);
    }
}
