define([
  'jquery',
  'underscore',
  'backbone',
  'mustache',
  'controllers/template/TemplateController',
  'text!../../../templates/spicoli.html',
  'views/form/FormView',
  'views/status/ErrorView'
], function($, _, Backbone, Mustache, Templates,  tmplt, FormView, ErrorView){
    var SpicoliView = FormView.extend({
        initialize: function() {
            console.log("creating spicoli view");
            this.render();
	},
        render: function() {
            var subview = this;
            subview.$el.append(Mustache.compile(tmplt)(subview));
//            if (typeof yourvar != 'undefined' && handlers['parcel']) {
//                handlers['parcel'](subview);
//            }
        }
    });
    return SpicoliView;
});
