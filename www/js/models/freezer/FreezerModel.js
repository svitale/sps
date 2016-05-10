define([
  'underscore',
  'backbone',
  'collections/shelves/ShelvesCollection',
], function(_, Backbone, Shelves) {
  
    var FreezerModel = Backbone.Model.extend({
      urlRoot: '/sps/data/freezer',
        defaults: {
      },  
      parse: function(response) {
        console.log('parsing freezer model');
        return response;
      },
      initialize: function() {
        console.log("initialize freezer object");
        freezer = this;
      },  
        condense: function(low_cutoff,high_cutoff) {
           console.log('condensing freezer');
           var shelves = this.get('shelves');
           this.boxes = []
           if(shelves) {
             for (var i = 0; i < shelves.length; i++) {
                 var freezer_name;
                 if(shelves[i].freezer) {
                     freezer_name = shelves[i].freezer;
                 }
                 if(shelves[i].racks) {
                     for (var j = 0; j < shelves[i].racks.length; j++) {
                         if(shelves[i].racks[j] && shelves[i].racks[j].boxes) {
                             for (var k = 0; k < shelves[i].racks[j].boxes.length; k++) {
                                var box =  shelves[i].racks[j].boxes[k];
                                // only show boxes with open spaces
                                if (box.percent_full >= low_cutoff &&  box.percent_full <= high_cutoff) {
                               // if (box.percent_full > 0 &&  box.percent_full <= cutoff) {
                                    box.shelf = i+1;
                                    box.rack = j+1;
                                    box.box = k+1;
                                    box.freezer_name = freezer_name;
                                this.boxes.push(box);
}
                             }
                         }
                     } 
                 }
             }
           }
        }
    }); 
    return FreezerModel;
   


});
