define([
  'underscore',
  'backbone'
], function(_, Backbone) {
    var SpotModel = Backbone.Model.extend({
        defaults: {
            'object_type': null,
            'object_name': null,
            'object_event': null,
            'template': null,
            'view': null,
            'z': null,
            'x1': null,
            'y1': null,
            'x2': null,
            'y2': null,
            'width': null,
            'height': null
        },
        initialize: function() {
            console.log("initialize spot element");
            if (this.get('object_type') == 'button') {
                this.set('button',true);
                //var event_name = this.get('object_event');
                //this.set('event_name',true);
                
                
                
            }
        }
    });
    return SpotModel;

});
