define([
  'jquery',
  'underscore',
  'backbone',
  'models/annot/AnnotModel',
  'collections/results/ResultsCollection'
], function($, _, Backbone, AnnotModel, ResultsCollection){
     var AnnotsCollection  = Backbone.Collection.extend({
       url: '/finch/annots',
        model: AnnotModel,
        initialize: function(options) {
          if(options && options.projectid !== undefined) {
             this.projectid = options.projectid;
          }
            console.log('initialize annots collection');
            this.results = new ResultsCollection();
            this.listenTo(this,'addsub',this.addsub);
            this.listenTo(this,'delsub',this.delsub);
            this.listenTo(this,'select',this.selectsomething);
            this.listenTo(this,'append',this.appendselected);
        },  

        delsub: function() {
          var model = new AnnotModel({id:this.selected.id});
          delete this.selected;
          this.remove(model);
          model.destroy();
          this.trigger('rerender')
        },

        addsub: function() {
           console.log('add');
           var collection = this;
           var callback = function() {
              collection.trigger('rerender')
           }
          console.log('adding a new annot');
          var annot = new AnnotModel({'projectid':this.projectid});
          annot.save();
          this.add(annot);
          this.deferred = this.fetch();
          this.deferred.done(function() {
              callback();
          });
        },
        selectsomething: function() {
          console.log('selecting');
          console.log(this.selected);
        },
        appendselected: function() {
          console.log('appending');
          var combined  = this.combined;
          var all_results = combined.get('all_results'); 
          console.log(this.selected);
          var collection = new ResultsCollection;
          for (var key in this.selected) {
            var result_id = this.selected[key];
            var result = all_results._byId[result_id];
            collection.add(result);
          }
          console.log(collection);
//todo:  save and recover this data from finch
        },

        doChange: function(evt) {
            var collection = this;
            var all_results = this.combined.get('all_results');
            if (evt.target === undefined && evt.target.value === undefined && evt.target.name === undefined) {
              return false;
            }
            var val = evt.target.value;
            var name = evt.target.name;
            if(name == 'annot') {
              for (var i = 0; i < evt.target.length; i++) {
                var p_id = evt.target[i].value;
                if (evt.target[i].selected) {
                    var model = this._byId[p_id];
                    this.selected = model;
                }
              }   
            }
            if((name == 'name' || name == 'description') && this.selected !== undefined) {
              this.selected.set(name,val);
              this.selected.save();
            }
            collection.trigger('rerender');
            
        },
        remSub: function(callback) {
          console.log('removing an annot');
          this.forEach(function (annot) {
              if (annot.get('selected')) {
                  annot.destroy();
              }
          });
          this.deferred = this.fetch();
          this.deferred.done(function() {
              callback();
          });
        },
    }); 
    return AnnotsCollection;
});
