define([
  'underscore',
  'backbone',
  'collections/math/OperationCollection',
  'models/math/FormulaModel',
  'models/math/VarModel'
], function(_, Backbone, Operation, Formula, Var) {
    var CurveModel = Backbone.Model.extend({
         initialize: function() {
                console.log('init curve model');
                var vars = this.attributes.vars;
                var function_name = this.attributes.name;
                var operation = new Operation();
                for (var i = 0; i < vars.length; i++) {
                    var name = vars[i].name;
                    var value = vars[i].value;
                    var math_var = new Var({
                        name: name,
                        value: value
                    });
                    operation.add(math_var);
                }
                operation.formula = new Formula({
                    id: function_name,
                    vars: operation.models
                });
                this.attributes.curvepoints = operation.perform();
            }
    });
    return CurveModel;
});
