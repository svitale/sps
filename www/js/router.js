// Filename: router.js
define([
  'jquery',
  'underscore',
  'backbone',
  'models/session/SpsSessionModel',
  'models/project/ProjectModel',
  'views/project/ProjectView',
  'models/task/TaskModel',
  'views/task/TaskView',
  'views/pulladmin/PulladminView',
  'views/status/SpinnerView',
  'views/status/ErrorView',
], function($, _, Backbone, ProjectModel, SpsSessionModel, ProjectView, TaskModel, TaskView, PulladminView, SpinnerView, ErrorView) {
  var Router = Backbone.Router.extend({
      routes: {
        "": "index",
        "task/pulladmin": "pulladmin",
        "task/pulladmin/:id": "pulladmin",
        "task/:name": "task",
        "task/:name/project/:projectId": "task",
        // todo:  figure out wildcard
        // ugh! hate that I have to do this!
        "task/:name/:a/:b/:c/:d/:e/:f/:h/:i": "task",
        "task/:name/:a/:b/:c/:d/:e/:f": "task",
        "task/:name/:a/:b/:c/:d": "task",
        "task/:name/:a/:b": "task",
      },  
      index: function() {
          console.log('nothing to see here yet');
      },
      task: function(name,projectId) {
          var el = jQuery("#taskcontainer");
          var spinner = new SpinnerView({el:el});
          console.log('task is ' +name);
          // initialize the task 
          var task = new TaskModel({name:name,projectId:projectId});
          new TaskView({
               el: el,
               model: task,
               spinner: spinner
          }); 
      },

      pulladmin: function(id) {
          var el = jQuery("#taskcontainer");
          //var spinner = new SpinnerView({el:el});
          console.log('task is pulladmin');
          // initialize the task 
          new PulladminView({
               el: el,
               selected: id
           //    model: task,
           //    spinner: spinner
          }); 
      },

  });
  return Router;
});
