/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */


(function($, _, Backbone, sps) {

    //a thing with a location.  eg. a tube in a box, in a rack...
    var InventoryModel = Backbone.Model.extend({
        url: '/sps/data/inventoryModel/',
        initialize: function() {
		console.log('initialize collection model');
		this.attributes.positionName = this.rowName(this.attributes.subdiv4,this.attributes.subdiv5);
		if (this.attributes.box_uuid) {
			this.attributes.boxBarcode = this.attributes.box_uuid.substring(0,7);
		} else {
			this.attributes.boxBarcode = null;
		}
        },
        rowName:  function(row,col){
            var s = "";
            while(row >= 1) {
                s = String.fromCharCode((row-1) % 26 + 97) + s;
                row = Math.floor((row-1) / 26) - 1;
            }
            return s.toUpperCase() + col;
        }
    });
    Sps.InventoryModel = InventoryModel;

    //a bunch of things with common qualities.  a rack full of boxes
    var InventoryCollection  = Backbone.Collection.extend({
	defaults: {
		id_study: null,
		sample_type: null,
		id_subjectt: null
	},
        url: '/sps/data/inventoryCollection/',
        
	model: InventoryModel,
        initialize: function() {
          console.log('creating inventory collection');
        }
    });
    Sps.InventoryCollection = InventoryCollection;


    //a bunch of things with common qualities.  a rack full of boxes
    var InventorySearch  = Backbone.Model.extend({
         parse: function(response) {
             console.log("Parse Called");
             response.records   = new Sps.InventoryCollection(response.records);             return response;
        },       

        url: '/sps/data/InventorySearch/',
        initialize: function() {
          console.log('creating shipments collection');
        }     
    });     
    Sps.InventorySearch = InventorySearch;

    //most of this will be handed off to the template
    var InventorySearchView = Backbone.View.extend({
        initialize: function() {
            console.log("creating search response view");
console.log(this);
            this.$el = jQuery("#taskcontainer");
            this.render();
        },

        render: function() {
            var view = this;
            Sps.Templates.getTemplate('inventory', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                    handlers['inventory'](view);
                }
            });
        },
        events: {
            "change": "doSelect"
        },
        doSelect: function(evt) {}
    });
    Sps.InventorySearchView = InventorySearchView;


    //most of this will be handed off to the template
    var InventoryView = Backbone.View.extend({
        initialize: function() {
            console.log("creating grid view");
            this.$el = jQuery("#taskcontainer");
            this.render();
        },

        render: function() {
            var view = this;
            Sps.Templates.getTemplate('item', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                    handler['inventory'](view);
                }
            });
        },
        events: {
            "change": "doSelect"
        },
        doSelect: function(evt) {}
    });
    Sps.InventoryView = InventoryView;

    //a bunch of things with common qualities.  a rack full of boxes
    var Shipments  = Backbone.Model.extend({
         parse: function(response) {
             console.log("Parse Called");
             response.records   = new Sps.InventoryCollection(response.records);             return response;
        },       

        url: '/sps/data/PendingShipments/',
        initialize: function() {
          console.log('creating pending shipments collection');
        }     
    });     
    Sps.Shipments = Shipments;

    //most of this will be handed off to the template
    var PendingShipmentsView = Backbone.View.extend({
        initialize: function() {
            console.log("creating pending shipments search response view");
console.log(this);
            this.$el = jQuery("#taskcontainer");
            this.render();
        },

        render: function() {
            var view = this;
            Sps.Templates.getTemplate('pendingshipments', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                    Sps.handler['pendingshipments'](view);
                }
            });
        },
        events: {
            "change": "doSelect"
        },
        doSelect: function(evt) {}
    });
    Sps.PendingShipmentsView = PendingShipmentsView;


})(this.jQuery, this._, this.Backbone, this.sps);
