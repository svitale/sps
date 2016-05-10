define([
  'jquery',
  'underscore',
  'backbone',
  'models/tracking/TrackingModel'
], function($, _, Backbone, TrackingModel){
     var TrackingCollection  = Backbone.Collection.extend({
        url: '/sps/data/tracking/',
        model: TrackingModel,
        initialize: function() {
          console.log('creating shipments collection');
        }   
    }); 
    return TrackingCollection;
});
