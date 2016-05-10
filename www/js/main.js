// Author: Thomas Davis <thomasalwyndavis@gmail.com>
// Filename: main.js

// Require.js allows us to configure shortcut alias
// Their usage will become more apparent futher along in the tutorial.
require.config({
  urlArgs: "v=3",
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
        deps: ['jquery', 'backbone', 'underscore'],
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
    },
    'angular' : {'exports' : 'angular'},
     'angularRoute': ['angular'],

  },
/*
 shim: {
    'backgrid': {
      deps: ['jquery', 'backbone', 'underscore'],
      exports: 'Backgrid'
    },
   'angular' : {'exports' : 'angular'},
   'angularRoute': ['angular'],
   'bootstrap': {
      deps: ["jquery"]
    }    
  },    
*/

  priority: [
    "angular"
  ]   
});
window.name = "NG_DEFER_BOOTSTRAP!";

require( [
'jquery',
  'underscore',
  'backbone',
  'router',
	'angular',
	'app',
	'routes',
  'datatables',
  'tabletools'
], function($, _, Backbone, Router, angular, app, routes) {
//angular.element(document.getElementById('foof'));
    var initialize = function(){
        console.log('loading main.js')
        this.router = new Router;
        Backbone.history.start();
  };
  angular.element().ready(function() {
        initialize();
	angular.resumeBootstrap([app['name']]);
  });

});
