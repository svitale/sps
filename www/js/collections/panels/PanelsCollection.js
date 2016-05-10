define([
  'jquery',
  'underscore',
  'backbone',
  'models/panel/PanelModel',
  'collections/results/ResultsCollection',

], function($, _, Backbone, PanelModel, ResultsCollection){

    var PanelsCollection = Backbone.Collection.extend({
    model: PanelModel,
	
    comparator: function(item) {
        return item.get('number');
    },
    initialize: function(options) {
       console.log('creating panelcollection');
    },
    doChange: function(evt) {
        for (var i = 0; i < evt.target.length; i++) {
            var p_id = evt.target[i].value;
            var model = this._byId[p_id];
            model.set('selected',evt.target[i].selected);
        }   
        this.trigger('update');
    }   
    });

    
  return PanelsCollection;
});
