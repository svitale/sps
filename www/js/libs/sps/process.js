/**
  * @class
  * @author Steven Vitale <svitale@upenn.edu>
*/
var Process = function (session) {
    'use strict';
    //override debug for this library
    this.initialize = function(data) {
        var callback = function (process_data) {Loader(process_data,data)};
        var url = '/sps/data/process/';
        sps.fetchJson(url,callback);
    }   

    var Loader = function(process,data) {
        if (session && session.debug) {
            console.log("loading processes");
            console.log(data);
        }
        var html = "<div id='aoprocess_button'></div>";
        html += "<div id='aoprocess_log'></div>";
        jQuery('#aoprocess').html(html);
        Button(process);
        Logged(process.logged);
    }

    var Button = function(data) {
        var valid = data.valid;
        var description = '';
        var name = '';
        var html = '';
        var forcereload = false;
        html =  '<div class="btn-toolbar">';
        html =  '<div class="btn-group">';
        html += '<button class="btn btn-inverse dropdown-toggle" ';
        html += 'data-toggle="dropdown">Process<span class="caret"></span></button>';
        html += '<ul class="dropdown-menu">';
        for (var i = 0; i < valid.length; i++) {
             description = valid[i].process_description;
             name  = valid[i].process_name;
             if (valid[i].sample_type_output) {
                 description += "+";
                 forcereload  = true;
             }
             html += '<li><a class="process_link" id="'+name+'" href="#">'+description+'</a></li>';
        }
        html += '</ul></div>';
        html += ' <div class="input-append">';
        html += '<input class="span2" id="num_daughters" type="text" value="1">';
       html += '<button id="aliquot_go" class="btn" type="button">Aliquot</button>';

       html += '</div>';
       html += '</div>';
        jQuery('#aoprocess_button').html(html);
        jQuery('.process_link').on('click', function(buttondata) {
          var process_name = buttondata.target.id;
          var process = new Process();
          process.Apply(process_name,forcereload);
        }); 
        jQuery('#aliquot_go').on('click', function(buttondata) {
            var num_daughters = jQuery('#num_daughters').val();
                postScan('npc:aliquot;'+num_daughters);
                location.reload();
        }); 
    }
    var Logged = function(data) {
         jQuery('#aoprocess_log').empty();
         jQuery('#aoprocess_log').append('<div id="ao_log_container" class="container span6"></div>')
         var processlist = [];
         var processdata = [];
         var columns = [];
         var options = {
            // autoWidth : true,
             autoHeight : true,
             enableCellNavigation : true,
             forceFitColumns : true
         };
         columns = [{
            id : 'process_name',
            name : 'name',
            field : 'process_name'
           },{
            id : 'process_description',
            name : 'description',
            field : 'process_description'
           },{
            id : 'timestamp',
            name : 'date',
            field : 'timestamp'
           }];
         var i=0;
         for (var id in data) {
             if (typeof(data[id]) === 'object') {
                 processdata[i] = {
                      id: i,
                      process_description: data[id].process_description,
                      process_name: data[id].process_name,
                      timestamp: data[id].timestamp
                 };
                 i++;
            }
         }
         if (i > 0) {
             var dataView = new Slick.Data.DataView({
                  inlineFilters : true
             });
             dataView.beginUpdate();
             dataView.setItems(processdata);
             dataView.setFilter(myFilter);
             dataView.endUpdate();
             new Slick.Grid("#ao_log_container", dataView, columns, options);
             }
             function myFilter(item) {
                  return true;
             }
    };

    this.Apply = function(data,forcereload) {
        var postdata = {'process_name':data};
        var callback = function (result) {
        //todo: reload only processed components
        window.location.reload();
        console.log("process result");
        console.log(result);
        }
        var url = '/sps/data/process/apply';
        sps.fetchJson(url,callback,postdata);
    }
}
