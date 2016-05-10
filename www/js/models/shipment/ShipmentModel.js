define([
  'underscore',
  'backbone',
  'models/spicoli/SpicoliModel',
  'collections/spicoli/SpicoliCollection',
], function(_, Backbone, SpicoliModel, SpicoliCollection) {
    var ShipmentModel = Backbone.Model.extend({
        urlRoot: '/finch/shipment',
        defaults: {
            'id': null,
            'site': null,
            'username': null,
            'tracking_number': null,
            'handler': null,
            'delivery_date': null,
            'parcel': new SpicoliCollection()
        },  
        initialize: function(options) {
            var model = this;
            var parcel = model.get('parcel');
            console.log('initialize shipping model');
            if(options) {
                if(options.site) { 
                 this.set('site',options.site);
                }
            };
/*
            model.listenTo(model,'all',function(evt) {
                console.log('evt');
                console.log(evt);
            });
*/
            model.listenTo(model,'attach',function() {
              //  parcel = model.get('parcel');
                console.log('attach');
                var spicoli = new SpicoliModel();
                parcel.add(spicoli);
              //  this.set('parcel',parcel);
                //model.save();
            });
            var parcel = model.get('parcel');
            this.listenTo(this,'save',function() {
                console.log('save event');
                model.save();
            });
            //parcel listeners
            this.listenTo(parcel,'save',function() {
                console.log('parcel wants me to save');
                model.set('parcel',parcel);
                model.save();
            });
        },  
        // register a specimen collection with this shipment 
    });
    return ShipmentModel;
   


});
