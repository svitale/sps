define([
  'underscore',
  'backbone',
  'models/analysis/AnalysisModel',
  'models/annot/AnnotModel',
  'collections/panels/PanelsCollection',
  'collections/annots/AnnotsCollection',
  'views/qc/QcView',
], function(_, Backbone, AnalysisModel, AnnotModel, PanelsCollection, AnnotsCollection, QcView) {
  
    //the step-parent task for analysis
    var ProjectModel = Backbone.Model.extend({
        urlRoot: '/squash/api/json/analytics/project',
        defaults: {
            'id': null,
            'type': 'qc',
            'state': 'refresh',
            'assays': null,
            'annot': null,
            'annots': null,
        },  
       parse: function(response) {
         console.log("Parse Called");
         response.panels  = new PanelsCollection(response.panels);
         return response;
        },  

        initialize: function() {
            console.log("initialize project object");
            var project = this;
            this.deferred = this.fetch();
            //* wait until the project has been fetched from the server
            //* before setting variable values and running analysis
            this.deferred.done(function (){
                //load all of the annotations that have been created
                // for this project in finch
               var annots = new AnnotsCollection({projectid:project.id});
               project.set('annots',annots);
               var queryData = { 
                 projectid: project.id,
                 include: ['name','description'],
                }  

                annots.deferred = annots.fetch({data: queryData});
/*
                    project.listenTo(annots,'update',function () {
                        var annot = annots._byId[annots.selected];
                        var deferred = annot.fetch();
                        deferred.done(function () {
                          annot.combined = project.get('combined');
                          project.set('annot',annot);
                        });
                         
                        
                    })
                });
*/
                var annot = new AnnotModel();
                project.set('annot',annot);
                // define the active annotation for the project
                // a blank one at first

                if(project.get('type') == 'qc') {
                    var assays = project.get('assays');
                    if (assays) {
                    //* dirty hack to just set the first assay
                    //* as the 'active' assay
                        project.set('assay',assays[0]);
                    }


                    //* create the variables we need to run analysis
                    var analysis_vars_array = []; 
                    analysis_vars_array.push({
                        method_name: 'westgard',
                        project: project,
                    }); 
                    analysis_vars_array.push({
                        method_name: 'combined',
                        project: project,
                        panels: project.get('panels'),
                    }); 
                    //*  run the analysis, setting a project attribute 
                    //*  with the method name that points to the analysis model
                    for (var i = 0; i < analysis_vars_array.length; i++) {
                      var analysis_vars = analysis_vars_array[i];
                      var analysis = new AnalysisModel(analysis_vars);
                      project.set(analysis_vars['method_name'],analysis);
                    }
/*
                    var panels = project.get('panels');
                    panels.on('update',function () {
                        project.recombinePanels();
                    });
*/
                }
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
            this.save(model.changed,{patch:true});
        },  

        modify: function() {
            console.log("modify Project");

        },  

    }); 
    return ProjectModel;


});
