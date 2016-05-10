define([
  'underscore',
  'backbone',
], function(_, Backbone, ResultsCollection) {
    var AnnotModel = Backbone.Model.extend({
        urlRoot: '/finch/annot',
        defaults: {
            'name': 'Untitled',
            'description': null,
            'projectid': null,
            'log': null,
            'resultids': []
        },  
        initialize: function(options) {
            console.log('initialize annot model');
            var model = this;
            var log = model.get('log');
            if(log == null) {
              model.set('log',this.mkLog());
            }
            var resultids = model.get('resultids');
            
        },  
        mkLog: function() {
          var log = {
            date:this.getDate(),
            user:'unknown',
            message:'created'
          }
          return log;
        },
        append: function() {
            var active_results = this.combined.get('results');
            active_results.forEach(function (result) {
                results.add(result);
            });
            this.model.trigger('rerender');
        },
        getDate: function() {
          var currentdate = new Date();
          var date = currentdate.toLocaleDateString();
          var time = currentdate.toLocaleTimeString();
          return date + ' ' + time;
       }
    });
    return AnnotModel;

});
