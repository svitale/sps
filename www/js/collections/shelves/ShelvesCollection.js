define([
  'underscore',
  'backbone',
  'models/shelf/ShelfModel'
], function(_, Backbone, ShelfModel){

    var ShelvesCollection = Backbone.Collection.extend({
       model: ShelfModel,
    initialize: function(options) {
        console.log(this);
        console.log('init');
    },
    parse: function(response) {
       console.log('parse called');
       console.log(response);
    },
       

    });
    return ShelvesCollection;
});
