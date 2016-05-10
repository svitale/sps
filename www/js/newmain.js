// Author: Thomas Davis <thomasalwyndavis@gmail.com>
// Filename: main.js

// Require.js allows us to configure shortcut alias
// Their usage will become more apparent futher along in the tutorial.
require.config({
paths: {

        'angular': 'libs/angular/angular',
        'angularRoute': 'libs/angular/angular-route',
        'text': 'libs/require/text',
        'backgrid': "libs/backbone/backgrid",
        'bootstrap': "libs/bootstrap",
        'datatables': "libs/jquery/jquery.dataTables",
        //'datatables-bootstrap': "libs/jquery/dataTables.bootstrap",
        'tabletools': "libs/jquery/dataTables.tableTools"
    },
  map: {
        '*': {
            'css': 'plugins/requirecss/css'
        }
    },

  shim: {
     'backgrid': {
        deps: ['jquery', 'backbone', 'underscore', 'css!vendor/backgrid/backgrid'],
        exports: 'Backgrid'
     },
     "bootstrap": {
        deps: ["jquery"]
     },
     "datatables": {
      deps: ["jquery"]
     },
     "datatables-bootstrap": {
      deps: ['jquery', 'datatables', 'bootstrap']
     },
    'tableTools': {
      deps: ['datatables']
    }

  },
  priority: [
    "angular"
  ]   
});

require([
  'jquery',
  'underscore',
  'backbone',
  'router',
  'datatables',
  'tabletools',
  'angular',
  'app',
  'routes'
  // Load our app module and pass it to our definition function
// main entry into sps load the session variables from server(s) and figure out
// what we're doing

],function ($, _, Backbone, Router) {
    var initialize = function(){
        console.log('loading main.js')
        this.router = new Router;
        Backbone.history.start();
  };

initialize();
});
