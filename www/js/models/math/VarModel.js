define([
  'underscore',
  'backbone'
], function(_, Backbone) {
  
    var VarModel = Backbone.Model.extend({
        defaults: {
            'name': null,
            'value': null
        },
        initialize: function() {
            //console.log('creating MathVar');
        }
    });
  return VarModel;

});
