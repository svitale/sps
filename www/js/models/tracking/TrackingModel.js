define([
  'underscore',
  'backbone',
  'collections/sites/SitesCollection',
  'models/shipment/ShipmentModel',
  'collections/shipments/ShipmentsCollection'
], function(_, Backbone, SitesCollection, ShipmentModel, ShipmentsCollection) {
    var TrackingModel = Backbone.Model.extend({
        urlRoot: '/sps/data/tracking',
        defaults: {
            'shipments': null,
            'sites': null,
            'site': null
        },  
       parse: function(response) {
         console.log("Tracking model Parse Called");
         var sites = new SitesCollection(response.sites);
         response.sites = sites;
         var shipments = new ShipmentsCollection(response.shipments, {parse: true});
         response.shipments = shipments;
         return response;
        },  
        initialize: function() {
                console.log('initialize tracking model');
        },  
    });
    return TrackingModel;
   


});
