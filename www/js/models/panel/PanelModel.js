define([
  'underscore',
  'backbone',
  'collections/results/ResultsCollection',
], function(_, Backbone, ResultsCollection) {

    var PanelModel = Backbone.Model.extend({
        defaults: {
            maxcv: null,
            curvepoints: null,
            selected: true
	},
        initialize: function(options) {
            console.log('creating panel');
	    if(options && options.id){
                this.id = options.id;
	    };
        },
        urlRoot: '/squash/api/json/analytics/results/panel/',
	parse: function(response) {
       	 console.log("Parse Called");
       	 response.results  = new ResultsCollection(response.results);
       	 return response;
    	},
    });
    return PanelModel;
});
