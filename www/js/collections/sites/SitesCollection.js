define([
  'jquery',
  'underscore',
  'backbone',
], function($, _, Backbone){
    var SitesCollection  = Backbone.Collection.extend({
        url: '/sps/data/sites/',
        initialize: function() {
          console.log('creating sites collection');
        }   
    }); 
    return SitesCollection;
});
