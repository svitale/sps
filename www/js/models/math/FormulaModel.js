define([
  'underscore',
  'backbone'
], function(_, Backbone) {
  
    /// Start MathFormula
    var FormulaModel = Backbone.Model.extend({
        defaults: {
            apply: null,
            vars: null
        },
        initialize: function() {
            console.log('creating Math Formula Model');
            if (this.attributes.vars) {
                for (var i = 0; i < this.attributes.vars.length; i++) {
                    var attr = this.attributes.vars[i].attributes;
                    this[attr.name] = attr.value;
                }
            }
            if (this.id == 'fourpl') {
                if (this.b && this.cc && this.d && this.e && this.rsquared) {
                    this.apply = this.fourpl;
                } else {
                    console.log("Error: the required variables have not been set!");
                }
            }
        },
        fourpl: function() {
            //      var y = (this.cc+((this.d-this.cc)/(1+Math.exp((this.b*Math.log(x))-(this.b*Math.log(this.e))))));
            var resolution = 10;
            var max = 10;//this.max;
            var min = .12;//this.min;
            var stepsize = (max - min) / resolution;
            var point = min;
            var points = []
            while (point < this.max) {
                var x = point;
                var y = (this.cc + ((this.d - this.cc) / (1 + Math.exp(this.b * Math.log(x) - this.b * Math.log(this.e)))));
                points.push({
                    objectName: null,
                    x: x,
                    y: y
                });
                point = point + stepsize;
            }
            return points;
        }
    });
    return FormulaModel;
    /// End MathFormula

});
