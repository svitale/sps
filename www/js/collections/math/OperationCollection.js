define([
  'underscore',
  'backbone',
  'models/math/VarModel'
], function(_, Backbone, VarModel){

    /// Start MathOperation
    var OperationCollection = Backbone.Collection.extend({
        model: VarModel,
        initialize: function() {
            console.log('creating Math Operation Collection');
        },
        perform: function(data) {
            return this.formula.apply(data);
        }
    });
    return OperationCollection;
});
