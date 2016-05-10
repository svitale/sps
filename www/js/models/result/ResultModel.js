define([
  'underscore',
  'backbone',
], function(_, Backbone) {    
    var ResultModel = Backbone.Model.extend({
        initialize: function () {
            Backbone.Model.prototype.initialize.apply(this, arguments);
            this.on("change", function (model, options) {
                if (options && options.save === false) return;
                model.save();
             });
        },
        urlRoot: '/squash/api/json/analytics/results/result',
    });
    return ResultModel;
});
