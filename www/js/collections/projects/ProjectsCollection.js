define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone){
  var ProjectsCollection = Backbone.Collection.extend({
        url: '/squash/api/json/analytics/project',
        initialize: function() {
            console.log('initalize ProjectCollection')
        },  
        //react to changes in from  projectscollectionview
        doSelect: function(evt) {
            var id = evt.target.value;
            var project = this.get(id);
            this.selected = project;

//            Backbone.history.navigate('/task/analysis/'+project.id,true); 
        }   
    }); 
    return ProjectsCollection;
});
