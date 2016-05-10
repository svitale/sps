define([
  'jquery',
  'underscore',
  'backbone',
  'models/shipment/ShipmentModel'
], function($, _, Backbone, ShipmentModel){
     var ShipmentsCollection  = Backbone.Collection.extend({
       url: '/finch/shipments/',
        model: ShipmentModel,
        parse: function(response) {
            for (var i = 0; i < response.length; i++) {
                var model = response[i];
                model.parcel = new Backbone.Collection(model.parcel);
             }
        return response;
        },  

        initialize: function() {
            this.listenTo(this,'add',function(evt) {
                console.log('add');
                this.trigger('rerender');
            }); 

          console.log('creating shipments collection');
        }   
    }); 
    return ShipmentsCollection;
});
