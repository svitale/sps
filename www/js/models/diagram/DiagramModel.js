define([
  'jquery',
  'underscore',
  'backbone',
  'collections/spots/SpotsCollection',
  'models/spot/SpotModel'
], function($, _, Backbone, SpotsCollection, SpotModel) {
  
    var DiagramModel = Backbone.Model.extend({
        urlRoot: '/butter/editor/json',
        defaults: {
            'id': null,
            'spots': null,
        },
        initialize: function() {
            console.log("initialize diagram object");
            var diagram = this;
            //*   if the diagram model is created with an id, then
            //*   fetch and extract
            if (diagram.id) {
                this.deferred = this.fetch();
                this.deferred.done(function () {
                    diagram.extract();
                });
            };
        },
        extract: function(spots) {
            console.log('extracting');
            var spots = new SpotsCollection;
            this.set('spots',spots);
            var elements = this.get('elements');
            for (var i = 0; i < elements.length; i++) {
                var element = elements[i];
                var view,object_type,object_name,template,z,id;
                if (element.data.length != 0 && !(element.data.disabled)) {
                    id = i;
                    if (element.data.view) {
                        view = element.data.view;
                    } else {
                        view = null;
                    }
                    if (element.data.object_name) {
                        object_name = element.data.object_name;
                    } else {
                        object_name = null;
                    }
                    if (element.data.object_event) {
                        object_event = element.data.object_event;
                    } else {
                        object_event = null;
                    }
                    if (element.data.object_type) {
                        object_type = element.data.object_type;
                    } else {
                        object_type = null;
                    }
                    if (element.data.template) {
                        template = element.data.template;
                    } else {
                        template  = null;
                    }
                    if (element.data.z) {
                        z = element.data.z;
                    } else {
                       z = 0;
                    }
                    var spot  = new SpotModel({
                            id: id,
                            object_type: object_type,
                            object_name: object_name,
                            object_event: object_event,
                            template: template,
                            view: view,
                            z: z,
                            x1: element.x1,
                            x2: element.x2,
                            y1: element.y1,
                            y2: element.y2,
                            width: element.width,
                            height: element.height,
                    });
                    spots.add(spot);
                }
            };
        },

    });
    return DiagramModel
    

});
