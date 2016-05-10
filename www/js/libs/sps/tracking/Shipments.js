/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */


(function($, _, Backbone, sps) {


    var TrackingCollection  = Backbone.Collection.extend({
	model: TrackingModel,
        url: '/sps/data/tracking/',
        initialize: function() {
          console.log('creating shipments collection');
        }
    });
    Sps.TrackingCollection = TrackingCollection;

    var TrackingModel = Backbone.Model.extend({
        urlRoot: '/sps/data/tracking/',
        defaults: {
            'id': null,
            'site': null,
            'tracking_number': null,
            'handler': null,
            'delivery_date': null
        },
        initialize: function() {
		console.log('initialize tracking model');
        },
    });
    Sps.TrackingModel = TrackingModel;

    var TrackingView = Backbone.View.extend({
        initialize: function() {
            console.log("creating grid view");
            this.$el = jQuery("#taskcontainer");
            this.render();
        },

        render: function() {
            var view = this;
console.log(this);
            Sps.Templates.getTemplate('shipments', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                    handler['shipments'](view);
                }
            });
        },
        events: {
            "change": "doSelect"
        },
        doSelect: function(evt) {}
    });
    Sps.TrackingView = TrackingView;


})(this.jQuery, this._, this.Backbone, this.sps);
