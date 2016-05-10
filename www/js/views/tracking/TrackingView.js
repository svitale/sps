define([
  'jquery',
  'underscore',
  'backbone',
  'collections/sites/SitesCollection',
  'models/tracking/TrackingModel',
  'models/shipment/ShipmentModel',
  'controllers/template/TemplateController',
  'views/shipment/ShipmentView',
  'views/error/ErrorView',
], function($, _, Backbone,SitesCollection, TrackingModel, ShipmentModel, Templates,  ShipmentView, ErrorView){
    var TrackingView = Backbone.View.extend({
        initialize: function() {
            console.log("creating tracking view");
            var view = this;
            this.model = new TrackingModel();
            this.model.fetch({
                success: function () {
                  view.render();
                  var shipments = view.model.get('shipments');
                  shipments.off('rerender');
                  view.listenTo(shipments,'rerender',function() {
                      console.log('rerender');
                      view.render();
                  }); 
                }
            });
        },

        render: function() {
            var view = this;
            Templates.getTemplate('shipments', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                }
                view.$el.html(template(view));
                view.renderShipment();
            });
            return this;
        },

        renderShipment: function() {
            var view = this;
            var model = this.model;
            var shipments = this.model.get('shipments');
            var sites = this.model.get('sites');
            Templates.getTemplate('shipment', function(err,shipments_template) {
                shipments.forEach(function(shipment){
                    shipment.sites =  sites;
                    var shipmentview = new ShipmentView({ model: shipment, sites: sites, shipments_template: shipments_template });
                    view.$('.shipments_grid').append(shipmentview.el);
                });
            });
        },
        events: {
            "click": "doButton",
        },
        doButton: function(evt) {
            if (evt.target.id == 'new') {
              //var newshipment = new ShipmentModel();
              var shipments = this.model.get('shipments');
              var sites = this.model.get('sites');
              console.log(sites);
              shipments.create();
            }
        },
    });
    return TrackingView;
});
