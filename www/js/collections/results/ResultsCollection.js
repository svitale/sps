define([
  'underscore',
  'backbone',
  'models/result/ResultModel'
], function(_, Backbone, ResultModel){
    var ResultsCollection = Backbone.Collection.extend({
        comparator: function(item) {
            return item.get('objectName');
        },  
        model: ResultModel,
        initialize: function() {
            console.log('init result collection');
        },
        doChange: function(evt)  {
            console.log(evt);
        }
    }); 

  return ResultsCollection;
});
