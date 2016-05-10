define([
  'underscore',
  'backbone'
], function(_, Backbone) {
    var SpsSessionModel = Backbone.Model.extend({
        urlRoot: '/sps/data/SpsSession',
        initialize: function() {
          console.log('sps session model');
        }   
    });  
    return SpsSessionModel;
});
