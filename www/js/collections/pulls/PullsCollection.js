define([
  'jquery',
  'underscore',
  'backbone',
  'models/pull/PullModel'
], function($, _, Backbone, PullModel){
     var PullsCollection  = Backbone.Collection.extend({
       url: '/sps/data/pulls',
        model: PullModel,
        initialize: function() {
          console.log('creating pulls collection');
        }
    }); 
    return PullsCollection;
    
});
