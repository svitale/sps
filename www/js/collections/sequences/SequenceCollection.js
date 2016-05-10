define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone){
  var SequenceCollection = Backbone.Collection.extend({
        doSelect: function(evt) {
        },   
        doChange: function(evt) {
            var found = false;
            for (var i = 0; i < evt.target.length; i++) {
                var p_id = evt.target[i].value;
                var model = this._byId[p_id];
                if(model) {
                    var selected = evt.target[i].selected;
                    model.set('selected',selected);
                    if (selected) {
                        found = true;
                    }
                 }
            }   
            if(found) {
                this.trigger('update');
            } else {
                console.log('nothing seems to have been selected');
            }   
       },

    }); 
    return SequenceCollection;
});
