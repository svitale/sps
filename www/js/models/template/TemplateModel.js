define([
  'jquery',
  'underscore',
  'backbone',
  'mustache',
], function($, _, Backbone, Mustache) {    
    var TemplateModel = Backbone.Model.extend({
        parse: function() {
            var response  = this.ajax.responseText;
            console.log('parse called');
            var compiledTemplate = Mustache.compile(response);
            this.set('compiled',compiledTemplate);
        },
        initialize: function() {
           model = this;
           console.log('initializing template model for ' +this.id);
        },
        fetch: function() {
            model = this;
            var ajax = $.get('/sps/templates/' + this.id + '.html');
            var ajax = jQuery.ajax({
                cache: false,
                url: '/sps/templates/' + this.id + '.html',
            });
            model.ajax = ajax;
            return ajax;

        },
    });
    return TemplateModel;
});

