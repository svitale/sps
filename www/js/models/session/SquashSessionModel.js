define([
  'underscore',
  'backbone'
], function(_, Backbone) {
    var SessionModel = Backbone.Model.extend({
        urlRoot: '/squash/api/json/sps/session',
        defaults: {
            'id': null,
            'apikey': null
        },  
        initialize: function() {
          console.log('squash session model');
        }   
    })  

  return SessionModel;

});
