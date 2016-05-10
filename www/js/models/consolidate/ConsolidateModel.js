define([
  'underscore',
  'backbone',
  'models/freezer/FreezerModel',
  'collections/freezers/FreezersCollection',
], function(_, Backbone, FreezerModel, FreezersCollection) {
  
    var ConsolidateModel = Backbone.Model.extend({
        urlRoot: '/sps/data/Consolidate',
        defaults: {
            'diagramId': 10,
            'freezers': null,
            'low_cutoff': 0,
            'high_cutoff': 99
        },  
        parse: function(response) {
         var consolidate = this;
         console.log("freezers collection Parse Called");
         var freezers = new FreezersCollection(response.freezers);
         response.freezers = freezers;
         return response;
        },
        initialize: function() {
            console.log("initialize consolidate object");
            var consolidate = this;
            consolidate.freezer = new FreezerModel();
            this.listenTo(consolidate,"change:freezer",consolidate.freezerChangeHandler);
            this.listenTo(consolidate,"change:low_cutoff",function(){consolidate.calc()});
            this.listenTo(consolidate,"change:high_cutoff",function(){consolidate.calc()});
            this.deferred = this.fetch();
            this.deferred.done(function() {
                var freezers = consolidate.get('freezers');
            });
        },  
        freezerChangeHandler: function(model,id) {
         var consolidate = this;
         var low_cutoff = this.get('low_cutoff');
         var high_cutoff = this.get('high_cutoff');
            console.log('freezer');
            var freezers = consolidate.get('freezers');
            var freezer = freezers._byId[id];
            var deferred = freezer.fetch();
            deferred.done(function() {
              freezers.forEach(function(one) {
                one.selected = '';
              });
              freezer.condense(low_cutoff,high_cutoff);
              consolidate.freezer = freezer;
              freezer.selected = 'selected';
              consolidate.trigger('rerender');
            });
        },
        calc: function() {
              var freezers = this.get('freezers');
              var low_cutoff = this.get('low_cutoff');
              var high_cutoff = this.get('high_cutoff');
              freezers.forEach(function(freezer) {
                if(freezer.selected) {
                  freezer.condense(low_cutoff,high_cutoff);
                }
              })
             this.trigger('rerender');
        }
    }); 

    return ConsolidateModel;


});
