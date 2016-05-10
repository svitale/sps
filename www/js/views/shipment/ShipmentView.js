define([
  'jquery',
  'underscore',
  'backbone',
  'views/parcel/ParcelView',
  'views/form/FormView',
  'views/status/ErrorView'
], function($, _, Backbone, ParcelView, FormView, ErrorView){

    var ShipmentView = FormView.extend({
        initialize: function(options) {
            console.log("creating shipment view");
            var view = this;
            view.sites = new Backbone.Collection(options.sites.toJSON());
            view.shipments_template = options.shipments_template;
            view.parcel = view.model.get('parcel');
            this.listenTo(this.model,'rerender',function(evt) {
                console.log('rerendering');
                view.$el.empty();
                view.render();
            });
            this.listenTo(view.parcel,'add',function(evt) {
                console.log('parcel event');
                if (view.parcel.length == 1 ) {
                    view.subview = new ParcelView({collection: view.parcel});
                    view.subview.setElement(view.$('.parcel')).render();
                } else if (view.parcel.length > 1 ) {
                    view.subview.render();
                }
            });
            view.render();
	},

        render: function() {
            var view = this;
            // the parcel is a collection of specimin collections (spicolis)
            //var parcel = view.model.get('parcel');
            view.listenTo(view.parcel,'add',function(evt) {
               view.model.save();
            });
            view.listenTo(view.parcel,'remove',function() {
               view.model.save();
            });
           //set button style to correspond to change state
           if(view.model.changedAttributes()){
               view.btn_style = 'btn-default';
            } else {
               view.btn_style = 'btn-warning';
            }
	    for (var i = 0; i < this.model.sites.length; i++) {
                if (view.model.sites.models[i].attributes.name == view.model.attributes.site) {
                    view.sites.models[i].selected = true;
		}
            }
            // handler is the shipper (ups, fedex, etc.)
            // create links for handler
	    var handler = this.model.get("handler");
            var tracking_number = this.model.get('tracking_number');
            if (handler == 'ups') {
                this.tracking_ref = 'http://forwarding.ups-scs.com/tracking/trackformaction.asp?optTYPE=SHIPNUM&PRO1='+tracking_number;
                this.is_ups = true;
	    }
            if (handler == 'fedex') {
                this.tracking_ref = 'http://www.fedex.com/Tracking?language=english&cntry_code=us&tracknumbers='+tracking_number;
                this.is_fedex = true;
            }


            this.$el.html(view.shipments_template(this));
            if (view.parcel.length > 0 ) {
                view.subview = new ParcelView({collection: view.parcel});
                view.subview.setElement(view.$('.parcel')).render();
            }
            return this;

        },  
    });
    return ShipmentView;
});
