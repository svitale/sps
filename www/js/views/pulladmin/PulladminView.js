define([
  'jquery',
  'underscore',
  'backbone',
  'views/form/FormView',
  'views/menu/MenuView',
  'controllers/template/TemplateController',
  'collections/pulls/PullsCollection',
  'collections/pullrequirements/PullrequirementsCollection',
  'views/error/ErrorView',
], function($, _, Backbone, FormView, MenuView, Templates, PullsCollection, Reqs, ErrorView){
    var PulladminView = FormView.extend({
        initialize: function(options) {
            console.log("creating pulladmin view");
            var view = this;
            var pulls = new PullsCollection();
            var lmenuel = $('#lmenu');
            //disable all listeners 
            lmenuel.unbind();
            var menu =  new MenuView({el:lmenuel});
            view.collection = pulls;
            pulls.deferred =  pulls.fetch();
            pulls.deferred.done(function () {
                if (options && options.selected) {
                    view.selected = pulls._byId[options.selected];
                    view.selected.selected = 'selected';
                    var reqs = new Reqs();
                    var fetch_data = {pull_id:options.selected};
                    reqs.deferred = reqs.fetch({data:fetch_data});
                    reqs.deferred.done(function () {
                       view.reqs = reqs;
                       view.render();
                    });
                } else { 
                    view.render();
                }

            });
        },

        render: function() {
            var view = this;
            Templates.getTemplate('pulladmin', function(err, template) {
                if (err) {
                    new ErrorView({
                        el: view.$el,
                        message: 'Error loading template file'
                    });
                    return;
                }
                view.$el.html(template(view));
                if (view.reqs) {
                   view.renderReqs(); 
                }
            });
            return this;
        },
        doChange: function(evt) {
            if (evt.type == 'change') {
                var id = evt.target.value;
                Backbone.history.navigate('/task/pulladmin/'+id,true); 
            }
        },
       renderReqs: function() {
           var view = this;
           console.log(view.$el);
           var reqs = this.reqs.toJSON();
/*
           view.$el('#example').dataTable( {
               "dom": '<"top">rt<"bottom">fi',
               "scrollY": "265px",
               "paginate": false,
               "scrollCollapse": true,
               "data": reqs,
               "columns": [
                   { "data": "id" },
               ]
           });
*/

        },  


    });
    return PulladminView;
});
