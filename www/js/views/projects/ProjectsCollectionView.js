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

    var ProjectsCollectionView = Backbone.View.extend({
        initialize: function() {
            console.log('ProjectsCollectionView');
            this.render();
        },  

        render: function() {
            var view = this;
            TemplateController.getTemplate('menu', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                }
                view.$el.html(template(view));
            });
        },

        events: {
            "change": "doSelect"
        },  

        doSelect: function(evt) {
            this.collection.doSelect(evt);
        }   

    }); 
  return ProjectsCollectionView;
});
