define([
  'underscore',
  'backbone',
  'models/spot/SpotModel'
], function(_, Backbone, SpotModel){

    SpotCollection = Backbone.Collection.extend({
        model: SpotModel
    });
   return SpotCollection;
});
