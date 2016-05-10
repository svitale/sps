define([
  'jquery',
  'underscore',
  'backbone',
  'models/diagram/DiagramModel',
  'views/diagram/DiagramView',
  'controllers/template/TemplateController',
  'views/status/ErrorView',
], function($, _, Backbone, DiagramModel, DiagramView, Templates, ErrorView){

    var ProjectView = Backbone.View.extend({
        initialize: function() {
            console.log('Creating project view');
            var diagramId = this.model.get('diagramId');
            diagram = new DiagramModel({
                id: diagramId
            });
            var view = this;
            diagram.fetch({
                success: function() {
                    view.model.attributes.diagram = diagram;
                    view.render();
                }
            });
            project = this.model;
        },

        render: function() {
            console.log('rendering project view');
            var view = this;
            Templates.getTemplate('project', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                } else {
                    view.$el.html(template(view));
		}
            });

        },
        renderDiagramView: function(diagram_model,project_model) {
              new DiagramView({
                 el: jQuery("#diagramcontainer"),
                'model': diagram_model,
                'project': project_model
             });
        },

    });
    return ProjectView;
});
