define([
  'jquery',
  'underscore',
  'backbone',
  'bootstrap',
  'views/status/ErrorView',
], function($, _, Backbone, bootstrap, ErrorView){

    var ButtonView = Backbone.View.extend({
        initialize: function(options) {
            this.spot = options.spot;
            this.spot_id = this.spot.get('id');
            this.render();
        },

        render: function() {
            var view = this;
            view.$el = $('#spot_'+view.spot_id);
            //remove any previously bound event listeners
            view.$el.unbind();

        },
        events: {
            "click": "doClick",
            "change": "doChange"
        },
        doClick: function(evt) { 
//            console.log(evt);
//            console.log(this);
            var event_name = this.spot.get('object_event');
            this.model.doEvent(event_name);
        },
    });
    return ButtonView;
});
