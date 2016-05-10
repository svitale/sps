define([
  'underscore',
  'backbone',
  'views/projects/ProjectsCollectionView',
  'views/tracking/TrackingView',
  'models/diagram/DiagramModel',
  'models/project/ProjectModel',
  'collections/projects/ProjectsCollection',
  'collections/spots/SpotsCollection',
  'models/lis/LisModel',
  'models/consolidate/ConsolidateModel',
  'models/tracking/TrackingModel',
], function(_, Backbone, ProjectsMenu, TrackingView, PageDiagramModel, ProjectModel, ProjectsCollection, SpotsCollection, LisModel, ConsolidateModel, TrackingModel) {
    var TaskModel = Backbone.Model.extend({
        //todo: get rid of any of these we no longer need
        defaults: {
            'name': null,
            'spots': new SpotsCollection(),
            'consolidate': null,
            'diagramId': null,
            'diagram': null,
            'projectId': null,
            'project': null,
            'target': null,
        },  

        initialize: function(options) {
            model = this;
            var sps_session = model.get('sps_session');
            model.name = options.name;
            this.projectId = options.projectId;
            var task = this;
            console.log("initialize task object with name "+task.name);
            // todo: pull model from task name
            var route = Backbone.history.fragment;
            var filter = route.replace("task/"+task.name+"/","");
            var filter_array = filter.split("/");
            var query = {};
            for (var i = 0; i <  filter_array.length; i += 2) {
               query[filter_array[i]] = filter_array[i+1];

            }   
console.log(query);
            if (task.name == 'consolidate') {
                task.target = new ConsolidateModel();
                task.deferred = task.target.fetch();
                task.diagramId  = task.target.get('diagramId');
            } else if (task.name == 'lis') {
                task.target = new LisModel({query:query});
                task.deferred = task.target.deferred;
                task.diagramId  = task.target.get('diagramId');
            } else if (task.name == 'tracking') {
                    new TrackingView({
                        'el': jQuery("#taskcontainer"), 
                    });             
            } else if (this.name == 'qc') {
                var projects = new ProjectsCollection();
                task.projects = projects;
                projects.deferred = projects.fetch();
                projects.deferred.done(function (){ 
                    new ProjectsMenu({
                       'el': jQuery('#squashmenu'),
                       'collection': projects
                    });
                });

                if (task.projectId) {
                    var project = new ProjectModel({id:task.projectId});
                    task.project = project;
                    task.target = project;
                    project.deferred.done(function (){ 
                       task.diagramId = project.get('diagramId');
                    });
                    task.deferred = project.deferred;
                } 


            } else {
                   console.log("Don't know how to do nothing!");
            };
            if (this.deferred) {
            //once the required task element is available, build the page diagram
                this.deferred.done(function () {
                    var diagramId = task.diagramId;
                    if (!diagramId) {
                        console.log("no page diagram to render (yet)");
                    } else { 
                        console.log('using diagramId: '+diagramId);
                        task.diagram = new PageDiagramModel({id:diagramId});
console.log(task.diagram);
                        task.deferred = task.diagram.fetch();
                    }
                });
            }
        },  
    }); 
    return TaskModel;
});
