define([
  'jquery',
  'underscore',
  'backbone',
  'controllers/template/TemplateController',
], function($, _, Backbone, Templates){

    var DiagramView = Backbone.View.extend({
        tagName: "div",
        className: "diagram-area",
        diagram: this.model,
        initialize: function(options) {
            diagram = this.model;
            diagram.extract();
            console.log('creating diagram view');
            this.project = options.project;
            diagram.set('project',project);
            this.render();
        },
        render: function() {
            var view = this;
            view.spots = this.model.spots.models;
            Templates.getTemplate('diagram', function(err, template) {
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
            "click .bitn": "doButton"
        },
        doButton: function(event) {
            var button = event.target.id;
            console.log(event);
            this[button]();
        },
    });
    return DiagramView;


});
