define([
  'jquery',
  'underscore',
  'backbone',
  'controllers/template/TemplateController',
], function($, _, Backbone, Templates){

    var SpinnerView = Backbone.View.extend({
        initialize: function() {
            console.log("a spinner to watch while you wait");
            this.elmId = 'nS_' + (Math.floor(Math.random() * 100000));
            this.$el.after('<div id="'+this.elmId+'" class="spinner"></div>');
            this.render();
        },  

        render: function() {
            //$(this.elmId).html(this.loader({width: 130, height: 130, container: "#"+this.elmId, id: "loader"}));
            var rad = (this.$el.width())/(10);
            this.loader({rad: rad, container: "#"+this.elmId, id: "loader"});
        },


        loader: function(config) {
            var radius = Math.min(config.rad) / 2;
            var tau = 2 * Math.PI;
            var arc = d3.svg.arc()
                    .innerRadius(radius*0.5)
                    .outerRadius(radius*0.9)
                    .startAngle(0);

            var svg = d3.select(config.container).append("svg")
                .attr("id", config.id)
                .attr("width", config.rad)
                .attr("height", config.rad)
                .append("g")
                .attr("transform", "translate(" + config.rad / 2 + "," + config.rad / 2 + ")")

            var background = svg.append("path")
                .datum({endAngle: 0.33*tau})
                .style("fill", "#4D4D4D")
                .attr("d", arc)
                .call(spin, 1500)

            function spin(selection, duration) {
                selection.transition()
                .ease("linear")
                .duration(duration)
                .attrTween("transform", function() {
                    return d3.interpolateString("rotate(0)", "rotate(360)");
                });

              setTimeout(function() { spin(selection, duration); }, duration);
            }

            function transitionFunction(path) {
                path.transition()
                    .duration(7500)
                    .attrTween("stroke-dasharray", tweenDash)
                    .each("end", function() { d3.select(this).call(transition); });
            }

        },
        end: function() {
            $('#'+this.elmId).remove();
        },
    }); 
    return SpinnerView;
});
