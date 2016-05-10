define([
  'underscore',
  'backbone',
  'models/spicoli/SpicoliModel',
  'collections/spicoli/SpicoliCollection',
], function(_, Backbone, SpicoliModel, SpicoliCollection) {
    var PullModel = Backbone.Model.extend({
        urlRoot: '/sps/data/pull',
        defaults: {
        },  
        initialize: function(options) {
            var model = this;
/*
            model.listenTo(model,'all',function(evt) {
                console.log(evt);
            });
*/
        },  
    });
    return PullModel;
   


});
