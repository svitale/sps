define([
  'underscore',
  'backbone',
], function(_, Backbone) {    
    var SpsRawResultModel = Backbone.Model.extend({
        urlRoot: '/sps/data/RawResult',
        initialize: function () {
            Backbone.Model.prototype.initialize.apply(this, arguments);
            this.on("change", function (model, options) {
                if (options && options.save === false || options.patch == true ) return;
                var result = model.save(model.changed,{patch:true});
             });
        },
    });
    return SpsRawResultModel;
});
