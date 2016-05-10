define([
  'jquery',
  'underscore',
  'backbone',
  'collections/panels/PanelsCollection',
  'views/grain/GrainView'

], function($, _, Backbone, PanelsCollection, GrainView){

    var PanelsCollectionView = GrainView.extend({
        initialize: function(options) {
            //console.log("creating template view");
            view = this;
            var model = view.model;
            var spot = options.spot;
            view.spot_id = spot.get('id');
            var compiled = options.compiled;
            view.template = compiled;
            if(view.model.toJSON) {
                view.json = view.model.toJSON();
            }   
            for (var name in view.json) {
              var date = view.json[name].assayDate;
              date = date.substring(0,10)
              console.log(date);
              view.json[name].assayDate = date;
              if (view.json[name].user == null) {
              view.json[name].user = 'unknown'
              }
            }
            view.render();
        }, 
    });
   return PanelsCollectionView;
});
