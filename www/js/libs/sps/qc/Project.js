/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */


(function($, _, Backbone, sps) {

var ProjectRouter = Backbone.Router.extend({

  routes: {
    //"project/:set/p:page": "search"   // #search/kiwis/p7
    "project/:projectId/:setId/:panelId": "project",   // #project/1/1/7
    "project/:projectId/:setId": "project",   // #project/1/1/7
    "project/:projectId": "project",   // #project/1/1/7
  },

  project: function(projectId, setId, panelId) {
      if(projectId > 0) {
            var project = new Sps.ProjectModel({id:projectId});
            project.fetch({
                success: function() {
                    new Sps.ProjectView({
                        el: jQuery("#projectcontainer"),
                        model: project
                    });
               }
           });
     };
  },

});
   Sps.ProjectRouter = ProjectRouter;

    var MetaModel = Backbone.Model.extend({
        urlRoot: '/squash/api/json/analytics/metaman/',
        initialize: function() {
          console.log('session model');
	}
    })
    Sps.MetaModel = MetaModel;

    //sync sps and squash
    var SessionModel = Backbone.Model.extend({
        urlRoot: '/squash/api/json/sps/session',
        defaults: {
            'id': null,
            'apikey': null
        },
        initialize: function() {
          console.log('session model');
	}
    })
    Sps.SessionModel = SessionModel;


    var ProjectCollection = Backbone.Collection.extend({
        url: '/squash/api/json/analytics/project',
        initialize: function() {
            console.log('initalize ProjectCollection')
            // store some important object properties
            Sps.metaman = [];
            Sps.metaman['result'] = new Sps.MetaModel({id:'result'});
            Sps.metaman['result'].fetch()
        },
        doSelect: function(evt) {
            var id = evt.target.value;
            var project = this.get(id);
            project = new ProjectModel(project);
            project.fetch({
                success: function() {
                    new Sps.ProjectView({
                        el: jQuery("#projectcontainer"),
                        model: project
                    });
                }
            });
        }
    });
    Sps.ProjectCollection = ProjectCollection;


    /* Project Model and view */

    var ProjectModel = Backbone.Model.extend({
        urlRoot: '/squash/api/json/analytics/project',
        defaults: {
            'id': null,
            'type': 'results',
            'state': 'refresh',
            'types': ['results' /*,'inventory'*/ ],
            'views': null,
            'buttons': null
        },

        initialize: function() {
            console.log("initialize project object");
        },
        handleDiagramData: function(collection) {
            console.log('handle diagram data');
            new Sps.PanelCollectionView({
                collection:collection
            });
        },

        refresh: function() {
            console.log("refreshing Project");
            this.set("state", "refresh");
            this.save();
        },
        reset: function() {
            console.log("resetting Project");
            this.set("state", "reset");
            this.save();
        },

        modify: function() {
            console.log("modify Project");

        },
      savestate: function() {
          var path = 'project';
          if (Sps.project) {
              path += '/' + Sps.project.id;
              if (Sps.set) {
                  path += '/' + Sps.set.id;
                  if (Sps.panel) {
                      path += '/' + Sps.panel.id;
                  }
          }
          Sps.projectRouter.navigate(path, {trigger: false, replace: true}); 
      }
  }
    });
    Sps.ProjectModel = ProjectModel;

    var ProjectView = Backbone.View.extend({
        initialize: function() {
            console.log('Creating project view');
            var diagramId = this.model.get('diagramId');
            diagram = new DiagramModel({
                id: diagramId
            });
            var thiz = this;
            diagram.fetch({
                success: function() {
                    thiz.model.diagram = diagram;
                    thiz.render();
                }
            });
            Sps.project = this.model;
	    Sps.project.savestate();
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
                    return;
                }
                view.$el.html(template(view));
                new DiagramView({
                    el: jQuery("#diagramcontainer"),
                    'model': view.model.diagram,
                    'project': view.model
                });
            });

        }

    });
    Sps.ProjectView = ProjectView;

    var ProjectCollectionView = Backbone.View.extend({
        initialize: function() {
            console.log('ProjectCollectionView');
            this.render();
        },
        render: function() {
            var view = this;
            Sps.Templates.getTemplate('menu', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                }
                view.$el.html(template(view));
		view.postrender();
            });
        },

        postrender: function() {
          Sps.projectRouter = new Sps.ProjectRouter();
          Backbone.history.start(); 
        },
        events: {
            "change": "doSelect"
        },

        doSelect: function(evt) {
            this.collection.doSelect(evt);
        }

    });
    Sps.ProjectCollectionView = ProjectCollectionView;
    /* ................................ */


    /* Diagram Model and view */

    var DiagramModel = Backbone.Model.extend({
        urlRoot: '/butter/editor/json',
        defaults: {
            'id': null,
        },
        initialize: function() {
            console.log("initialize diagram object");
        },
        extract: function() {
            console.log('extracting');
            this.spots = new SpotCollection;
            var elements = this.get('elements');
            for (var i = 0; i < elements.length; i++) {
                var element = elements[i];
                var model_name,template,z,id;
                if (element.data.length != 0 && !(element.data.disabled)) {
                    if (element.data.view) {
                        id = element.data.view;
                    } else {
			id = i;
		    }
                    if (element.data.model_name) {
                        model_name = element.data.model_name;
                    } else {
                        model_name = null;
                    }
                    if (element.data.template) {
                        template = element.data.template;
                    } else {
                        template  = null;
                    }
                    if (element.data.z) {
                        z = element.data.z;
                    } else {
                       z = 0;
                    }
                    var spot  = new SpotModel({
                            id: id,
                            model_name: model_name,
                            template: template,
			    z: z,
                            x1: element.x1,
                            x2: element.x2,
                            y1: element.y1,
                            y2: element.y2,
                            width: element.width,
                            height: element.height,
                    });
                    this.spots.add(spot);
                }
            };
            Sps.handler = [];
            Sps.spots = this.spots;
        }

    });

    var DiagramView = Backbone.View.extend({
        tagName: "div",
        className: "diagram-area",
        diagram: this.model,
        initialize: function() {
            diagram = this.model;
            diagram.extract();
            console.log('creating diagram view');
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
                view.afterRender();
            });
        },
        afterRender: function() {
            //todo set up event listeners for this
            console.log("Diagram done rendering!");
            //var spots = this.model.spots.models;
            // iterate through all spots, make a list of unique controllers
            // also, make a list of all templates
            var panelCollection = Sps.project.get('panels');
            Sps.project.handleDiagramData(panelCollection);
        },
        events: {
            "click .bitn": "doButton"
        },
        doButton: function(event) {
            var button = event.target.id;
console.log(event);
            this[button]();
        },
        refreshProject: function() {
            console.log("refreshing project");
            var project = this.model.project;
            project.refresh();
        },
        resetProject: function() {
            console.log("resetting project");
            var project = this.model.project;
            project.reset();
        },
        modifyProject: function() {
            console.log("modifying project");
            var project = this.model.project;
        },
        affirmResults: function() {
            if (Sps.panel) {
                Sps.panel.affirm();
            }
        }
    });

    /* ................................ */



    /********** Spot Model and View (used by Diagram) *********************************/


    var SpotModel = Backbone.Model.extend({
        defaults: {
            'model_name': null,
            'template': null,
            'z': null,
            'x1': null,
            'y1': null,
            'x2': null,
            'y2': null,
            'width': null,
            'height': null
        },
        initialize: function() {
            console.log("initialize spot element");
        }
    });

    var SpotCollection = Backbone.Collection.extend({
        model: SpotModel
    });

    /* ................................ */


    /********** Component Views (used by widgets) *********************************/

    var ErrorView = Backbone.View.extend({
        initialize: function() {
            this.render();
        },
        render: function() {
            this.$el.html('<div class="alert alert-error"><strong>Error:</strong> ' + this.options.message + '</div>');
        }
    });

    var LoadingView = Backbone.View.extend({
        initialize: function() {
            this.render();
        },
        render: function() {
            this.$el.append('<img class="loader" src="../images/ajax-loader.gif" alt="Loading" />');
        }
    });

    var Templates = {};
    Templates.baseUrl = '/sps/templates/';
    Templates.getTemplate = function(template, cb) {
        jQuery.ajax({
            cache: false,
            url: Templates.baseUrl + template + '.html',
            success: function(body) {
                var compiledTemplate = Mustache.compile(body);
                Templates[template] = compiledTemplate;
                cb(null, compiledTemplate);
            },
            error: function(xhr) {
                cb(xhr);
            }
        });
    };
    Sps.Templates = Templates;

    // store the display parameters for datasets
    var DisplayModeModel = Backbone.Model.extend({
        defaults: {
            'log_x': false,
            'log_y': false
        },

        initialize: function() {
            console.log("initialize display model");
        },
    });
    Sps.DisplayModeModel = DisplayModeModel;


})(this.jQuery, this._, this.Backbone, this.sps);
