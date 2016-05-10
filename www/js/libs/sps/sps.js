/*
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */
var Sps = function() {
	'use strict';
        this.debug = true;
	this.initialize = function(postdata) {
		var url = '/sps/data/';
		var callback = function(data) {
			sps.Loader(data);
		};
		this.fetchJson(url, callback, postdata);
	};
        this.reset = function () {
            var postdata = {'reset':'true'};
            this.initialize(postdata);
        };
	 this.setFilters = function(filters) {
                var postdata = {'filters':filters};
		var url = '/sps/data/';
		var callback = function(data) {
			sps.Loader(data);
		};
		this.fetchJson(url, callback, postdata);
	};

	this.Loader = function(data) {
            var isios= ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
            var behavior = data.settings;
            data.debug = this.debug;
                if (this.debug) {
		    console.log("loading sps/session data");
                }
                if (data.task && behavior.jsonify) {
			handleTask(data);
		        jQuery('#footer').html("<span>SPS -- Jsonified</span>");
                } else {
		        console.log("not jsonified");
                        if (data.state == 'reset') {
                          location.reload();
                        } else if (data.state == 'update') {
                           location.reload();
                       }
                }
             this.Menu();
             var bodyKeysPressed = ''; 
             var uuidRegex = /[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/;
             jQuery('body').keypress(function (event) {
                 if(isios) {
                     if (event.which === 13) {
                         var uuid = bodyKeysPressed;
                         bodyKeysPressed = ''; 
                         postScan(uuid);
                     } else {
                         bodyKeysPressed += String.fromCharCode(event.which);
                         bodyKeysPressed = bodyKeysPressed.substring((bodyKeysPressed.length > 36) ? (bodyKeysPressed.length - 36) : 0);
                     }
                 } else  {
                   if((event.target.nodeName.toLowerCase() === 'body')) {
                     if (event.which === 13) {
                         var uuid = bodyKeysPressed;
                         bodyKeysPressed = ''; 
                         postScan(uuid);
                     } else {
                         bodyKeysPressed += String.fromCharCode(event.which);
                         bodyKeysPressed = bodyKeysPressed.substring((bodyKeysPressed.length > 36) ? (bodyKeysPressed.length - 36) : 0);
                         jQuery('#scanIn').attr('value',bodyKeysPressed);
                     }
                   }   
                 }
             });
             jQuery('#scanIn').change(function(event) {
                 var uuid = event.target.value;
                 postScan(uuid);
             });

	};

	this.fetchJson = function(url, callback, postdata) {
		jQuery.ajax({
			dataType : 'jsonp',
			type : 'post',
			data : postdata,
			url : url,
			context : this,
			success : function(data) {
				callback(data);
			},
			error : function(data) {
				var html = "<h1>Error: SPS says:</h1>";
				html += data.responseText;
				jQuery('#taskcontainer').html(html);
			},
		});
	};

	var handleTask = function(data) {
		console.log('handing off to ' + data.task);
		if (data.task == 'crf') {
			var crf = new Crf(data);
			crf.initialize();
		} else if (data.task == 'store') {
			var store = new Store(data);
			store.initialize();
		} else if (data.task == 'analytics') {
			var analytics = new Analytics(data);
			analytics.initialize();
		} else if (data.task == 'roles') {
			var roles = new Roles(data);
			roles.initialize();
		} else if (data.task == 'inventory') {
		var inventory = new Sps.InventorySearch();
		new Sps.SpinnerView({el: jQuery("#taskcontainer"), model:inventory});
   		var response = inventory.fetch({
				data: data.filters,
        			success: function () {
			    		new Sps.InventorySearchView({el: jQuery("#taskcontainer"), model:inventory});
        			},
				error: function () {
					var html = "<h1>Error: SPS says:</h1>";
					html += response.responseText;
					jQuery('#taskcontainer').html(html);
				}
   			})
		} else if (data.task == 'pendingshipments') {
		var shipments = new Sps.Shipments();
		new Sps.SpinnerView({el: jQuery("#taskcontainer"), model:shipments});
   		var response = shipments.fetch({
				data: data.filters,
        			success: function () {
			    		new Sps.PendingShipmentsView({el: jQuery("#taskcontainer"), model:shipments});
        			},
				error: function () {
					var html = "<h1>Error: SPS says:</h1>";
					html += response.responseText;
					jQuery('#taskcontainer').html(html);
				}
   			})
		} else if (data.task == 'analysis') {
		} else if (data.task == 'projects') {
			var projects = new Results(data);
			projects.initialize();
		} else if (data.task == 'qc') {
			var project = new Sps.ProjectModel({id:2, 'apikey':data.apikey});
   			project.fetch({
        			success: function (user) {
			    		new Sps.ProjectView({el: jQuery("#taskcontainer"), model:project});
        			}
   			});
		} else if (data.task == 'tracking') {
			var shipments = new Sps.TrackingCollection;
   			shipments.fetch({
        			success: function (user) {
			    	new Sps.TrackingCollectionView({el: jQuery("#taskcontainer"), collection:shipments});
        		}
   			});
		} else {
                        console.log('can not figure out what to do with task='+data.task);
                }
                
	};
	this.ScannedCodeParser = function(data) {
		var codetype;
		var codevalue;
		var uuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
		var process = /^proc:.*$/i;
		var npc = /^npc:.*$/i;
		var npc_process = /^npc:proc:.*$/i;
		if (uuid.test(data)) {
			codevalue = data;
			codetype = 'uuid';
		} else if (process.test(data)) {
			codevalue = data.replace(/^proc:/, '');
			codetype = 'process';
		} else if (npc_process.test(data)) {
			codevalue = data.replace(/^npc:proc;/, '');
			codetype = 'process';
		} else if (npc.test(data)) {
			codevalue = data.replace(/^npc:/, '');
			codetype = 'npc';
                        npcBarcodeHandler(data);
		} else {
			codetype = 'unknown';
			codevalue = null;
		}
		return {
			'codetype' : codetype,
			'codevalue' : codevalue
		};
	};
      var npcBarcodeHandler = function(data) {
          //TODO: make this not so hacky
          if (document.getElementById("actioncontainer") === null) {
               jQuery('#taskcontainer').prepend('<div id="actioncontainer"><div/>');
          }   
          postScan(data);
      }
      this.Menu = function() {
          if ((jQuery('#daterange').length) > 0) {
             dateFilter();
          }
          //jQuery('#scanIn').focus();
       jQuery('#reset_button').on('click', function() {
            var sps = new Sps();
            sps.reset();
         });  
      } 
      var dateFilter = function() { 
          jQuery(function() {
              jQuery( "#datestart" ).datepicker({
                 defaultDate: "+1w",
	         changeMonth: true,
                 numberOfMonths: 2,
                 dateFormat: "yy-mm-dd",
                 onClose: function( selectedDate ) {
                    jQuery( "#dateend" ).datepicker( "option", "minDate", selectedDate );
                 }
              });
             jQuery( "#dateend" ).datepicker({
                 defaultDate: "+1w",
                 changeMonth: true,
                 numberOfMonths: 2,
                 dateFormat: "yy-mm-dd",
                 onClose: function( selectedDate ) {
                    jQuery( "#datestart" ).datepicker( "option", "maxDate", selectedDate );
                 }
              });
      })
        jQuery('#go').on('click', function(buttondata) {
           var datestart = jQuery( "#datestart" ).val();
           var dateend = jQuery( "#dateend" ).val();
           var filters = {'datestart': datestart, 'dateend': dateend};
           sps = new Sps();
           sps.setFilters(filters);
        });
      }


    jQuery.i18n.properties({
         name:'dict', 
         path:'/sps/i18n/',
         mode:'both',
         language:'en', 
     });     

};
(function($, _, Backbone, sps) {


    var Templates = {};
    Templates.baseUrl = '/sps/templates/';
    Templates.getTemplate = function(template, cb) {
        jQuery.ajax({
            cache: false,
            url: Templates.baseUrl + template + '.html',
            success: function(body) {
                var compiledTemplate = Mustache.compile(body);
                Templates[template] = compiledTemplate;
                cb(null, compiledTemplate);
            },
            error: function(xhr) {
                cb(xhr);
            }
        });
    };
    Sps.Templates = Templates;


    var SpinnerView = Backbone.View.extend({
        initialize: function() {
            console.log("a spinner to watch while you wait");
            this.$el = jQuery("#taskcontainer");
            this.render();
        },

        render: function() {
            var view = this;
            Sps.Templates.getTemplate('spinner', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                }
            });
        },
    });
    Sps.SpinnerView = SpinnerView;




})(this.jQuery, this._, this.Backbone, this.sps);
