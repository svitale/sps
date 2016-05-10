define([
  'underscore',
  'backbone',
  'models/result/ResultModel',
  'models/curve/CurveModel',
  'collections/results/ResultsCollection'
], function(_, Backbone, ResultModel, CurveModel, ResultsCollection) {
  
   var AnalysisModel = Backbone.Model.extend({
       urlRoot: '/squash/api/json/analytics/analysis',
       defaults:  {
           'project': null,
           'method_name': null,
           'panels': null,
           'results': null,
           'remote_eval': false,
       },
       initialize: function(options) {
            var analysis = this;
            if(options.method_name){
                analysis.set('method_name',options.method_name);
            }
            if(options.panels){
                analysis.set('panels',options.panels);
            }
            if(options.project){
                analysis.set('project',options.project);
            }
            
            //* set options for individual methods
            if(options.method_name == 'westgard') {
                analysis.set('remote_eval',true);
            }

            if(analysis.get('remote_eval')) {
                var project = analysis.get('project');
                data = {
                    object_id: project.get('id'),
                    object_type: 'project',
                    method_name: analysis.get('method_name'),
                }
                analysis.deferred = analysis.fetch({data:data});
                analysis.deferred.done(function () {
                    analysis.desalinate();
                });
            } else {
                var project = analysis.get('project');
                analysis.distill()
            }
            

            //if it isn't deferred we can start listening
            if (!analysis.deferred)  {
                    var panels = analysis.get('panels');
                    if (panels.on) {
                        panels.on('update',function () {
                            console.log('panels updated');
                            analysis.distill()
                            analysis.trigger('rerender');
                        });
                    };
            }
             
       },
       // convert panels to a set of results
       distill: function() {
           var analysis = this;
           var panels =  analysis.get('panels');
           var all_results  = new ResultsCollection();
           var results  = new ResultsCollection();
           var panel_curve;
           if (panels) {
               panels.forEach(function(p){
               all_results.add(p.get('results'));
               if (p.get('selected')) {
                   results.add(p.get('results'));
                   panel_curve = p.get('curve');
               }   
               }); 
            } 
           analysis.set('results',results);
           analysis.set('all_results',all_results);
           // include the calculated curve so we can plot that
           if(panel_curve) {
               var curve  = new CurveModel(panel_curve);
               analysis.set('curve',curve);
           }
       },
       // convert server analysis  to a set of results
       desalinate: function() {
           var analysis = this;
           var results  = new ResultsCollection();
           var results_analysis = this.get('ras');
           results_analysis.forEach(function(p){
               var any = p.ramap.a;
               var result = new ResultModel(p.result);
               result.failed = any; 
               results.add(result);
           });
           analysis.set('results',results);
       },

   }); 
   return AnalysisModel;

});
