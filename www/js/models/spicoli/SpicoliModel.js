define([
  'underscore',
  'backbone',
], function(_, Backbone) {
//Transient Object to represent contents of a shipment
    var SpicoliModel = Backbone.Model.extend({
        defaults: {
            'id_subject': null,
            'num_tubes': null
        },  
        initialize: function() {
        var model = this;
                console.log('initialize spicoli model');
        },  
    });
    return SpicoliModel;
});
