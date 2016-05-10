define([
  'jquery',
  'underscore',
  'backbone',
  'models/panel/PanelModel',
  'collections/results/ResultsCollection',
], function($, _, Backbone, PanelModel, ResultsCollection) {
  
  var FilterModel = Backbone.Model.extend({
        defaults: {
            apply: null,
            vars: null
        },  
        initialize: function(options) {
            console.log('creating filter');
            this.target = options.target;
        },
        doChange: function(evt) {
            console.log('for event:')
            console.log(evt)
            console.log('against')
            console.log(this.target)
        } 
  });

  return FilterModel;

});
