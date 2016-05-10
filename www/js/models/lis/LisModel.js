define([
  'jquery',
  'underscore',
  'backbone',
  'collections/spsresults/SpsResultsCollection',
  'collections/sequences/SequenceCollection',
  'models/sequence/SequenceModel',
], function($, _, Backbone, SpsResultsCollection, SequenceCollection, SequenceModel) {
  
    var LisModel = Backbone.Model.extend({
        url: '/sps/data/LIS',
        defaults: {
            'diagramId': 13,
            'sequenced': null,
        },  
        
       parse: function(response) {
         var model = this;
         console.log("LisModel Parse Called");
         var records = new SpsResultsCollection(response.records);
         response.records = records;
         var sequenced = new SequenceCollection();
         //response.sequenced is the grouped/ordered array of record ids
         response.sequenced.forEach(function (set) {
             //the set is an id array
             var sequence = new SequenceModel(set);
             var collection = new SpsResultsCollection();
             set.records.forEach(function (record) {
                 collection.add(records.get(record.id));
             });
             //now we've got a populated collection of records
             sequence.set('records',collection);
             sequenced.add(sequence);
         });
         response.sequenced = sequenced;
         response.all_records = response.records.toJSON();
         return response;
        },
        

        initialize: function(options) {
            // if a query is specified, embed it into the url
            if (options && options.query) {
                this.url = this.url + "?" +$.param(options.query) 
            }
            console.log("initialize lis object");
            var model  = this;
            model.deferred = model.fetch();
            model.deferred.done(function() {
                var sequenced = model.get('sequenced');
                sequenced.on('update',function() {
                    model.distill();
                });
                // wait for import event
                model.listenTo(model,'import',function() {
                    console.log('import');
                    var records = model.get('records');
                    this.processRecords('import',records);
                });
                model.listenTo(model,'ignore',function() {
                    console.log('ignore');
                    var records = model.get('records');
                    this.processRecords('ignore',records);
                });
            });
        },
        processRecords: function(action,records) {
            var data = {selected: records.selected, action: action};
            records.deferred = records.fetch({data: data, type: 'PATCH'});
            records.deferred.done(function () {
                console.log(records);
                records.trigger('rerender');
            });
        },
        distill: function() {
           var model = this;
           var sequenced =  model.get('sequenced');
           var results  = new SpsResultsCollection();
           var panel_curve;
           if (sequenced) {
               sequenced.forEach(function(p){
               if (p.get('selected')) {
                   results = p.get('records');
               }   
               }); 
           }   
           var records = model.get('records');
           model.set('records',results);
           records.trigger('rerender');
       },  

    }); 

    return LisModel;


});
