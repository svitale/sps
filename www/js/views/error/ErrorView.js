define([
  'jquery',
  'underscore',
  'backbone',
  'bootstrap',
  'views/status/ErrorView',
], function($, _, Backbone, bootstrap, ErrorView){

    var ErrorView = Backbone.View.extend({
        initialize: function(options) {
            var view = this;
            console.log('initialize error view');
            console.log(options);
            console.log(view);
           // this.render();
        },

        render: function() {
            console.log('rendering errorview');
        },
    });
    return ErrorView;
});
