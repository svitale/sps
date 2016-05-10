define([
  'jquery',
  'underscore',
  'backbone',
  'controllers/template/TemplateController',
  'd3',
], function($, _, Backbone, Templates, d3){

    var QcView = Backbone.View.extend({
        initialize: function(options) {
            console.log("creating qc view");
            this.$el = jQuery('#spot_QcView');
            this.project = options.project;
            var assays = project.get('assays');
            this.controls = assays[0].controls;
            this.render();
        },  

        render: function() {
            this.height = this.$el.height();
            this.width = this.$el.width();
            var view = this;

            Templates.getTemplate('qcview', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    }); 
                    return;
                } else {
                    view.$el.html(template(view));
                    handler['plotter'](view);
                }   
            }); 
        },  
        events: {
            "change": "doSelect"
        },  
        doSelect: function(evt) {}
    }); 
  return QcView;
});
