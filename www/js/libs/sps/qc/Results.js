/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */


(function($, _, Backbone, sps) {

    // this breaks down to a bunch of panels that are somehow related
    var PanelCollectionView = Backbone.View.extend({
        initialize: function() {
            console.log("creating set view");
            this.$el = jQuery('#PanelCollectionView');
            Sps.set = this.model;
            // use history 
	    Sps.project.savestate();
            // levy jennings qc plot
            var assay = this.collection[0].assay;
	    var qc = new Sps.AnalysisModel();
            qc.fetch({
		data: { 
			object_id: Sps.project.id,
			object_type: 'project',
			method_name: 'westgard'
		},
                success: function() {
                    qc.collection = this.model;
                    qc.assay = assay;
                    new Sps.QcView({
                        model: qc
                    });
                }
            });

            this.render();
        },

        render: function() {
            var view = this;
            view.height = this.$el.height();
            view.width = this.$el.width();
            Sps.Templates.getTemplate('set', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                }
                view.$el.html(template(view));
            });
            this.doSelect();
        },
        events: {
	    //listen for select/multiselect
            "change": "doSelect"
        },
        doSelect: function(evt) {
            var full_collection = new PanelCollection(this.collection);
            var panel = new Sps.PanelModel();
            var results  = new Sps.ResultCollection();
	    var assay;
            var num_selected = 0;
	    if(evt) {
                for (var i = 0; i < evt.target.length; i++) {
                    if (evt.target[i].selected) {
	    	        var p_id = evt.target[i].value;
            		var p = full_collection.get(p_id);
			results.add(p.attributes.results);
			assay = p.attributes.assay;
			num_selected++;
		    }
                }
                // for now, we'll only generate a curve if one and only one panel is selected
                if (num_selected == 1) {
                    var curve = new Sps.CurveModel(p.attributes.curve);
                    panel.attributes.curvepoints = curve.attributes.curvepoints;
                }
            } else {
		full_collection.models.each(function(p){
			results.add(p.attributes.results);
			assay = p.attributes.assay;
		});
            }
            panel.attributes.results = results;
	    panel.attributes.assay = assay;
	    new Sps.AnalysisView({model: panel});
        }
    });
    Sps.PanelCollectionView = PanelCollectionView;


    var PanelCollection = Backbone.Collection.extend({
    comparator: function(item) {
        return item.get('name');
    },
        initialize: function(options) {
            console.log('creating panelcollection');
	}
    });

    // panels are groups of results that get sent out for display as graphs, grids, etc


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
    Sps.ResultModel = ResultModel;

    var ResultCollection = Backbone.Collection.extend({
    comparator: function(item) {
        return item.get('objectName');
    },
	model: Sps.ResultModel,
        initialize: function() {
	console.log('init result collection');
	}
	});
    Sps.ResultCollection = ResultCollection;



    var PanelModel = Backbone.Model.extend({
        defaults: {
            maxcv: null,
            curvepoints: null
	},
        initialize: function(options) {
            console.log('creating panel');
	    if(options && options.id){
                this.id = options.id;
	    };
        },
        urlRoot: '/squash/api/json/analytics/results/panel/',
	parse: function(response) {
       	 console.log("Parse Called");
       	 response.results  = new Sps.ResultCollection(response.results);
       	 return response;
    	},
    });
    Sps.PanelModel = PanelModel;

    var CurveModel = Backbone.Model.extend({
         initialize: function() {
 		console.log('init curve model');
                var vars = this.attributes.vars;
                var function_name = this.attributes.name;
                var operation = new Sps.MathOperation();
                for (var i = 0; i < vars.length; i++) {
                    var name = vars[i].name;
                    var value = vars[i].value;
                    var math_var = new Sps.MathVar({
                        name: name,
                        value: value
                    });
                    operation.add(math_var);
                }
                operation.formula = new Sps.MathFormula({
                    id: function_name,
                    vars: operation.models
                });
                this.attributes.curvepoints = operation.perform();
            }
    });
    Sps.CurveModel = CurveModel;

    var AnalysisView = Backbone.View.extend({
        initialize: function() {
            this.display_mode =  new Sps.DisplayModeModel();
            console.log("creating analysis view");
            //this.$el = jQuery('#QcView');
	     // pass the model off to templates
             for (var i = 0; i < Sps.spots.length; i++) {
		var spot = Sps.spots.models[i];
		var model_name = spot.get('model_name');
                if (model_name == 'PanelModel') {
			var template_view =  new Sps.TemplateView({
                            model: this.model,
                            spot: spot
			})
                } else if (model_name == 'AssayModel') {
			var template_view =  new Sps.TemplateView({
                            model: this.model.attributes.assay,
                            spot: spot
			})
                }
             }
        },
    });
    Sps.AnalysisView = AnalysisView;

   var AnalysisModel = Backbone.Model.extend({
       urlRoot: '/squash/api/json/analytics/analysis',
   });
   Sps.AnalysisModel = AnalysisModel;

    var QcView = Backbone.View.extend({
        initialize: function() {
            console.log("creating qc view");
            this.$el = jQuery('#QcView');
            this.render();
        },

        render: function() {
            this.height = this.$el.height();
            this.width = this.$el.width();
            var view = this;

            Sps.Templates.getTemplate('qcview', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                    handler['plotter'](view);
                }
            });
        },
        events: {
            "change": "doSelect"
        },
        doSelect: function(evt) {}
    });
    Sps.QcView = QcView;

    var TemplateView = Backbone.View.extend({
        initialize: function(options) {
            console.log("creating template view");
            this.spot = options.spot;
	    this.render();
        },
        render: function() {
            var view = this;
            view.$el = jQuery('#'+view.spot.id);
	    view.height = view.$el.height();
	    view.width = view.$el.width();
	    //view.$el = jQuery('#'+this.spot.id);
            Sps.Templates.getTemplate(this.spot.get('template'), function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                } else {
                    view.$el.html(template(view));
                    if (Sps.handler[view.spot.id]) {
                        Sps.handler[view.spot.id](view);
                    }
                }
            });
        },
        events: {
            "change": "doChange"
        },
         events: {
            "click .btn": "doClick"
        },  
        doClick: function(event) {
console.log(event);
        },  
        doChange: function(event) {
console.log(event);
        },  

    });
    Sps.TemplateView = TemplateView;

    /// Start MathOperation
    var MathOperation = Backbone.Collection.extend({
        model: MathVar,
        initialize: function() {
            console.log('creating MathOperation');
        },
        perform: function(data) {
            return this.formula.apply(data);
        }
    });
    Sps.MathOperation = MathOperation;
    //\ End MathOperation


    /// Start MathFormula
    var MathFormula = Backbone.Model.extend({
        defaults: {
            apply: null,
            vars: null
        },
        initialize: function() {
            console.log('creating MathFormula');
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
    Sps.MathFormula = MathFormula;
    /// End MathFormula


    /// Start MathVar
    var MathVar = Backbone.Model.extend({
        defaults: {
            'name': null,
            'value': null
        },
        initialize: function() {
            //console.log('creating MathVar');
        }
    });
    Sps.MathVar = MathVar;
    //\ End MathVar




})(this.jQuery, this._, this.Backbone, this.sps);
