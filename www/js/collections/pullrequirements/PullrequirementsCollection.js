define([
  'jquery',
  'underscore',
  'backbone',
], function($, _, Backbone){
     var PullrequirementsCollection  = Backbone.Collection.extend({
       url: '/finch/pullrequirements',

        initialize: function() {
          console.log('creating pullrequirements collection');
        }   
    }); 
    return PullrequirementsCollection;
});
