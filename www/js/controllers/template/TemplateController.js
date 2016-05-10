define([
  'jquery',
  'underscore',
  'backbone',
  'mustache',
], function($, _, Backbone, Mustache) {    
    var TemplateController = {}; 
    TemplateController.baseUrl = '/sps/templates/';
    TemplateController.getTemplate = function(template, cb) {
        var ajax = jQuery.ajax({
            cache: false,
            url: TemplateController.baseUrl + template + '.html',
            success: function(body) {
                var compiledTemplate = Mustache.compile(body);
                TemplateController[template] = compiledTemplate;
                cb(null, compiledTemplate);
            },  
            error: function(xhr) {
                cb(xhr);
            }   
        }); 
        return ajax;
    };  
    return TemplateController;
});
