define([
  'backbone',
  'models/template/TemplateModel'
], function(Backbone, TemplateModel){
     var TemplatesCollection  = Backbone.Collection.extend({
        model: TemplateModel,
        initialize: function() {
          console.log('creating templates collection');
          this.names = {};
          this.promises = [];
          this.refetch = false;
        },   
        grab: function(template_name) {
            if (!this._byId[template_name] || this.refetch) {
                console.log('grabbing');
                if(this._byId[template_name]) this.remove(template_name);
                var template = new TemplateModel({id:template_name});
                this.add(template);
                this.promises.push(template.fetch());
            }
        }

    }); 
    return TemplatesCollection;
});
