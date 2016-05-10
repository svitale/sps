define([
  'jquery',
  'underscore',
  'backbone',
  'router',
  'mustache',
  'models/result/ResultModel',
  'collections/results/ResultsCollection',
  'controllers/template/TemplateController',
  'views/status/ErrorView',
], function($, _, Backbone, Router, ResultModel, ResultsCollection, Mustache, TemplateController, ErrorView){

    var ConsolidateView = Backbone.View.extend({
        initialize: function() {
            console.log('ConsolidateView');
            this.render();
        },  

        render: function() {
        },
    }); 
  return ConsolidateView;
});
