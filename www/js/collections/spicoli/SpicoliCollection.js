define([
  'jquery',
  'underscore',
  'backbone',
  'models/spicoli/SpicoliModel'
], function($, _, Backbone, SpicoliModel){
     var SpicoliCollection  = Backbone.Collection.extend({
        model: SpicoliModel,
        initialize: function() {
        },   
    }); 
    return SpicoliCollection;
});
