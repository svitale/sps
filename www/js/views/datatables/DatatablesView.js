define([
  'jquery',
  'underscore',
  'backbone',
  'i18n!nls/str',
  'backgrid',
  'views/form/FormView',
  'views/grain/GrainView',
  'controllers/template/TemplateController',
  'models/uifilter/FilterModel',
  'models/template/TemplateModel',
], function($, _, Backbone, Str, Backgrid, FormView, GrainView, Templates, FilterModel, TemplateModel){
  // customize the grainview for a grid
  var DatatablesView = GrainView.extend({
     events: {
          "click": "onClick",
     },
     getUniqueIds: function(tt_instance) {
     // an object we'll use to store the ids as keys
       var o = {};
       var selectedIds = []; 
     //
       var aSelectedTrs = tt_instance.fnGetSelected();
       aSelectedTrs.forEach(function (selected) {
           o[selected.id]=selected.id;
       });
       for(i in o) selectedIds.push(o[i]);
       return selectedIds; 
     },
     onClick: function(evt) {
       var oTT = TableTools.fnGetInstance( 'table_'+this.spot_id );
       var selected = this.getUniqueIds(oTT);
       this.model.selected = selected; 
       this.model.trigger('select');
     },

     initialize: function(options) {
        var view = this;
        var model = view.model;
        this.collection = view.model;
        var spot = options.spot;
        view.spot_id = spot.get('id');
        var compiled = options.compiled;
        view.template = compiled;
        if(view.model.toJSON) {
            view.json = view.model.toJSON();
        }



        view.render();
     },  
     genCols: function(fields) {
        var cols = [];
        for (var i = 0; i < fields.length; i++) {
            var col = {data:fields[i]};
            cols[i] = col;
        }
        return cols;
     },
     genData: function(collection,fields) {
         var data = [];
         _.each(collection.models, function(model){
             var obj = _.pick(model.attributes, fields);
             if(obj['user'] !== undefined && (obj['user'] == null)) {
                 obj['user'] = 'unknown';
             }
             obj['DT_RowId'] = model.id;
	     data.push(obj);
	 });
         return data;

     },
     render: function () {
         var view = this;
         view.$el = $('#spot_'+view.spot_id);
view.$el.unbind();
         view.height = this.$el.height();
         view.width = this.$el.width();
         view.$el.html(view.template(view));


         view.table_height = this.height - 100;
         handlers[view.spot_id](view);
         //view.renderTable();
      },
      renderTable: function(collection,fields,dom) {
         var view =  this;
         var data = view.genData(collection,fields);
         var cols = view.genCols(fields);
         //change the classes assigned to buttons
         $.extend(true, $.fn.DataTable.TableTools.classes, {
            "container" : "btn-group btn-group-xs",
            "buttons" : {
                "normal" : "btn btn-default btn-xs",
                "disabled" : "disabled"
            },
         });
         $('#table_'+view.spot_id).DataTable( {
             dom: dom,
             scrollY: view.table_height,
             data: data,
             columns:cols,
             scrollY: view.table_height,
             paginate: false,
             scrollCollapse: true,
             tableTools: {
                 sRowSelect: "os",
                 aButtons: [ "select_all", "select_none" ]
             }
         });
     }

    });
    return DatatablesView;


});
