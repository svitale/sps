define([
  'jquery',
  'underscore',
  'backbone',
  'i18n!nls/str',
  'backgrid',
  'views/form/FormView',
  'controllers/template/TemplateController',
  'models/uifilter/FilterModel',
  'models/template/TemplateModel',
], function($, _, Backbone, Str, Backgrid, FormView, Templates, FilterModel, TemplateModel){
//*  render the target object into the div specified by butternut

    var LisimportView = FormView.extend({
        defaults: {
        },
        initialize: function(options) {
           console.log("creating lisimport view");
            view = this;
            var model = view.model;
            var spot = options.spot;
            this.spot_id = spot.get('id');
            var compiled = options.compiled;
            view.template = compiled;
            if(view.model.toJSON) {
                view.json = view.model.toJSON();
            }


            var records = model.get('records');
            this.setProgressStats();
            this.listenTo(records,'select',function(evt) {
                this.setProgressStats();
                this.render();
             });



            view.render();
        },
        setProgressStats: function () {
            var num_tot=0,num_sel=0,num_imp=0,per_sel=0,per_imp=0,per_ign=0;
            var records = this.model.get('records');
            if (records && records.selected) {
                num_sel = records.selected.length;
            }
            if (records) {
                num_tot =  records.length;
            }
            if (num_tot > 0 && num_sel > 0) {
              per_sel = num_sel/num_tot * 100;
            } 
            this.num_tot = num_tot;
            this.num_sel = num_sel;
            this.num_imp = num_imp;
            this.per_sel = per_sel;
            this.per_imp = per_imp;
            this.per_ign = per_ign;
        },
        render: function() {
            var view = this;
            view.$el = $('#spot_'+view.spot_id);
            this.height = this.$el.height();
            this.width = this.$el.width();
            view.$el.html(view.template(view));
        },


    });
    return LisimportView;


});
