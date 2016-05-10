define([
  'jquery',
  'underscore',
  'backbone',
  'i18n!nls/str',
  'backgrid',
  'views/form/FormView',
], function($, _, Backbone, Str, Backgrid, FormView){
//*  render the target object into the div specified by butternut

  var AnnotsView = FormView.extend({
    initialize: function(options) {
      var collection = this.model;
      view = this;
      view.debug = false;
      var spot = options.spot;
      var compiled = options.compiled;
      var project = options.project;
      view.spot_id = spot.get('id');
      view.template = compiled;
      view.model.deferred.done(function() {
        var combined = project.get('combined');
        var panels = combined.get('panels');
        view.model.combined = combined;
        view.listenTo(combined,'rerender',function(evt) {
          collection.trigger('rerender');
        });
        view.render();
      });
    },
    render: function() {
      var view = this;
      view.$el = $('#spot_'+view.spot_id);
      this.height = this.$el.height();
      this.width = this.$el.width();
      handlers = [];
      view.$el.off();
      view.$el.empty();
      var results = view.model.combined.get('results');
      view.results = results.toJSON();
      view.selection =  [];
      console.log("creating annotations view");
      if (view.model.selected !== undefined) {
        var selected = view.model.selected
        view.selected_name = selected.get('name');
        view.selected_status = selected.get('status');
        view.selected_description = selected.get('description');
        view.selected_log = selected.get('log');
        view.selected_id = selected.id;
      }
      if (view.model.models !== undefined) {
        view.model.models.forEach(function(model) {
          if(view.selected_id !== undefined && model.id == view.selected_id) {
            var sel_tag = 'selected'; 
          } else {
            var sel_tag = ''; 
          }
          var name = model.get('name');
          view.selection.push({id:model.id,name:model.get('name'),selected:sel_tag});
        });
      }
      view.time = view.getTime();
      view.$el.html(view.template(view));
    }, 
    getTime: function() {
      var currentdate = new Date(); 
      //var date = currentdate.toLocaleDateString();
      var time = currentdate.toLocaleTimeString();
      return time; 
    }
  });
  return AnnotsView;
});
