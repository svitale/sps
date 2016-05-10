define([
  'jquery',
  'underscore',
  'backbone',
  'models/freezer/FreezerModel',

], function($, _, Backbone, FreezerModel){

  var FreezersCollection = Backbone.Collection.extend({
    model: FreezerModel,
	
    comparator: function(item) {
        return item.get('name');
    },
    initialize: function(options) {
       console.log('creating freezers collection');
       this._meta = {};
    },
    meta: function(prop, value) {
        if (value === undefined) {
            return this._meta[prop]
        } else {
            this._meta[prop] = value;
        }
    },
  });

    
  return FreezersCollection;
});
