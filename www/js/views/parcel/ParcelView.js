define([
  'jquery',
  'underscore',
  'backbone',
  'views/form/FormView',
  'views/spicoli/SpicoliView',
  'views/status/ErrorView',
], function($, _, Backbone, FormView, SpicoliView, ErrorView){
    var ParcelView = FormView.extend({
        // tagName: 'ul',
        initialize: function() {
            console.log("creating parcel view");
	},
        render: function() {
            view = this;
            view.$el.empty();
            this.collection.each(function(spicoli){
               var subview = new SpicoliView({model: spicoli});
                view.$el.append(subview.$el);
             });
        }

    });
    return ParcelView;
});
