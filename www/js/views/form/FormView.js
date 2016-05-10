define([
  'jquery',
  'underscore',
  'backbone',
  'controllers/template/TemplateController',
  'views/status/ErrorView'
], function($, _, Backbone, Templates, ErrorView){

    var FormView = Backbone.View.extend({
        events: {
            "keyUp": "processKey",
            "click .button": "onButtonClick",
            "click input[type=radio]": "onRadioClick",
            "change input[type=text]": "onTextChange",
            "change textarea": "onTextChange",
            "change .select": "onSelect",
            "change .range": "onChangeRange",
            "submit": "submit",
        },
        processKey: function(e) { 
            if(e.which === 13) {// enter key
               // this.submit();
            }
        },
        submit: function(evt) { 
            evt.stopPropagation();
            if (this.doSubmit) {
                evt.preventDefault();
                this.doSubmit(evt);
            }
        },

        onButtonClick: function(evt) {
            var view = this;
		console.log('onButtonClick');
                evt.stopPropagation();
                switch(evt.target.name) {
	            case 'remove':
                        view.model.destroy();
                        view.$el.slideUp();
                    break;
	            case 'attach':
                       view.model.trigger('attach');
                    break;
                    case 'render':
			view.model.trigger('rerender');
                    break;
                    case 'save':
			view.model.trigger('save');
                    break;
                    case 'append':
			view.model.trigger('append');
                    break;
                    case 'addsub':
			view.model.trigger('addsub');
                    break;
                    case 'delsub':
			view.model.trigger('delsub');
                    break;
                    case 'import':
			view.model.trigger('import');
                    break;
                    case 'ignore':
			view.model.trigger('ignore');
                    break;
		    case 'delete':
                        view.$el.slideUp();
			view.model.destroy();
                    break;
                    case 'echo':
                        console.log(this);
                    break;
                }
        },
        onSelect: function(evt) {
                console.log('onSelect');
		this.onChange(evt);
        },
        onChangeRange: function(evt) {
                console.log('onChangeRange');
		this.onChange(evt);
        },
        onRadioClick: function(evt) {
                //console.log('onRadioClick');
		this.onChange(evt);
	},
        onTextChange: function(evt) {
                //console.log('onTextChange');
		this.onChange(evt);
	},
        onChange: function(evt) {
            evt.stopPropagation();
            var attributeName = evt.target.name;
            var value = evt.target.value; 
            var attribute = {};
            attribute[attributeName] = value;
            // simple attribute change
            if (this.model && !this.model.doChange) {
                var model = this.model; 
	        model.set(attribute);
                model.trigger('save');
            } else if (this.model && this.model.doChange) {
               this.model.doChange(evt);
            } else if (this.doChange) {
               this.doChange(evt);
            } else {
                console.log('Error:  onChange fired but doChange not defined');
            }
	},
    });
    return FormView;
});
