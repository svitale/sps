define([
  'underscore',
  'backbone',
  'models/spsrawresult/SpsRawResultModel',
  'views/error/ErrorView'
], function(_, Backbone, SpsRawResultModel, ErrorView){

    var SpsResultsCollection = Backbone.Collection.extend({
        url: function() {
           var uri = '/sps/data/LISResults';
           var route = Backbone.history.fragment;
           var filter = route.replace("task/lis/","");
           var filter_array = filter.split("/");
           var query = {}; 
           for (var i = 0; i <  filter_array.length; i += 2) {
               query[filter_array[i]] = filter_array[i+1];
           }   
           var url_query = $.param(query);
           if (url_query) {
               uri = uri + '?' + url_query;
           }   
           return uri;
        },  
        comparator: function(item) {
            return item.get('objectName');
        },  
        model: SpsRawResultModel,
        initialize: function() {
           console.log('init result collection');
        },
        doChange: function(evt)  {
       //     console.log(evt);
        },
        doEvent: function(name) {
             //var collection = this;
             this.doAction(name);
             this.deferred.error(function (er) {
                 new ErrorView({er:er});
             });
        },
        doAction: function(name) {
             var selected = [];
             this.forEach(function (result) {
                 selected.push(result.get('id'));
             });
             this.selected = selected;
             console.log(name + 'ing...');
             this.deferred = this.fetch({
                 data : {selected:selected,action:name},
                 type: 'POST'
             });
        }
    }); 

  return SpsResultsCollection;
});
