define([
  'jquery',
  'underscore',
  'backbone',
  'controllers/template/TemplateController',
  'models/template/TemplateModel',
  'collections/templates/TemplatesCollection',
  'views/menu/MenuView',
  'views/button/ButtonView',
  'views/grain/GrainView',
  'views/annots/AnnotsView',
  'views/angular/AngularView',
  'views/lisimport/LisimportView',
  'views/panels/PanelsCollectionView',
  'views/datatables/DatatablesView',
  'views/status/ErrorView',
  'mustache',
], function($, _, Backbone, Templates, TemplateModel, TemplatesCollection, MenuView, ButtonView, GrainView,  AnnotsView, AngularView, LisimportView, PanelsCollectionView, DatatablesView, ErrorView, Mustache){

    var TaskView = Backbone.View.extend({
        initialize: function(options) {


            console.log('initialize task view');
            var view = this;
            view.project = view.model.project;
            var spinner = options.spinner;
            var lmenuel = $('#lmenu');
            //disable all listeners 
            lmenuel.unbind();
            var menu =  new MenuView({el:lmenuel});
            menu.setValsFromUri();
            view.el = $("#taskcontainer");
            // wait until the task diagram is available before proceeding
            if(this.model.deferred) {
              this.model.deferred.done(function() {
                var diagram = view.model.diagram;
                diagram.deferred.done(function () {
                    var spots = diagram.get('spots');
                    view.spots = spots.toJSON();
                    var render = view.render();
                    spinner.end();
                });
            });
            } else  {
                    var render = view.render();
                    spinner.end();
            }
        },

        render: function() {
            console.log('rendering task view');
            view = this;
            var ajax = Templates.getTemplate('task', function(err, template) {
                view.$el.html(template(view));
            }); 
            if(view.model.diagram) {
                ajax.done(function () {
                    var diagram = view.model.diagram;
                    var spots = diagram.get('spots');
                    view.spotsRender(spots);

                });
            }
            
        },
        // build the spot lattice for the target
        spotsRender: function(spots) {

            // if refetch_templates is set to true, don't force a reload of all templates
            // should only be set to false in development

            var view = this;
            var target = this.model.target;
            var named_objects =  {};
            //the root object is addes as 'target'
            named_objects['target'] = [];
            var templates = new TemplatesCollection;
            var objects = new Backbone.Collection;
            objects.add(target);

            //create objects and promises for the templates
            // which objects and to where?
            spots.forEach(function (spot) {
                var spot_id = spot.get('id');

                // the object_name dictates which part of the model we'll 
                // this can be either the entire target or one of its attributes
                var object_name = spot.get('object_name');
                if(object_name) {

                    // if we haven't seen this object name before, and its name to
                    // as a key in named_objects[] and the object to our collection
                    if(!named_objects[object_name]) {
                        named_objects[object_name] = [];
                        var object = target.get(object_name);
                        objects.add(object);
                    }

                    //add this spot_id to the key in named_objects
                    named_objects[object_name].push(spot_id)

                } else {
                //if no object_name is provided, add this spot id to the list of views we'll pass target to
                    named_objects['target'].push(spot_id);
                }
                   
                // the template_name dictates which template we'll pass to the view
                var template_name = spot.get('template');
                //view.loadTemplates(template_name,template_names,template_promises,true);
                if(template_name) {

                    // this sets templates.promises, and its behavior is influenced by the refetch_templates 
                    // variable (default: true); 
                    templates.grab(template_name);
                }
                
            });

            //once templates are available, render the views
            $.when.apply($, templates.promises).done(function() {
                for (key in named_objects) {
                    var object
                    if (key == 'target') {
                        object = target;
                    } else {
                        object = target.get(key);
                    }
                    //  the spots this object is destined for
                    var spot_ids = named_objects[key];
                    object.subspots = new Backbone.Collection;
                    for (var i =0; i < spot_ids.length; i++) {
                        var spot_id = spot_ids[i];
                        var subspot = spots.get(spot_id);
                        object.subspots.add(subspot);
                    }
                    if(object.subspots) {
                        view.renderObject(object,templates);
                    }
               };
               //reset promises
               templates.promises=[]
           });

           // wait for changes to target so we can update the dependant views
            target.off('change');
            view.listenTo(target,'change',function() {
                for (var attr_name in target.changed) {
                    var changed_obj = target.get(attr_name);
                    var spotobj = named_objects[attr_name];
                    //if a spot is bound to to this view, rerender it;
                    if (spotobj) {
                        var changed_spots = new Backbone.Collection;
                        spotobj.map(function (spotId) {
                            var spot = spots._byId[spotId];
                            changed_spots.add(spot);
                        });
                        changed_obj.subspots  = changed_spots;
                        changed_spots.forEach(function (subspot) {
                            var template_name  = subspot.get('template')
                            templates.refetch = true;
                            templates.grab(template_name);
                        });

                         $.when.apply($, templates.promises).done(function() {
                            view.renderObject(changed_obj,templates);
                            templates.promises=[]
                        });
                    }
                }
            });
         },
         renderObject: function(object,templates) {
             var view = this;
             // wait for the 'rerender' event 
             if(object.on) {
                 object.off('rerender');
                 view.listenTo(object,'rerender',function() {
                     console.log('rerender');
                     view.renderObject(object,templates);
                });
             }

             // iterate over the spots that this object is passed to
             object.subspots.forEach(function (spot) {
                 var spot_id = spot.get('id');
                 var template_name = spot.get('template');
                 var object_name = spot.get('object_name');
                 var button = spot.get('button');
                 var template = templates.get(template_name);



                 var View;
                 //var View = GrainView;
                 var compiled,source;
                 // if template exists, go through this rigmarole
                 if(template) {
                     if(!template.get('compiled')) {
                         template.parse();
                     }
                     compiled = template.get('compiled');
                     source = template.get('source');
                 }
                 var view_name =  spot.get('view');
                 if (view_name) {
                      if (view_name == 'PanelsCollection') {
                          View = PanelsCollectionView;
                      } else if (view_name == 'Lisimport') {
                          View = LisimportView;
                      } else if (view_name == 'Datatables') {
                          View = DatatablesView;
                      } else if (view_name == 'Annots') {
                          View = AnnotsView;
                      } else if (view_name == 'Angular') {
                          View = AngularView;
                      } else {
                         console.log('view ' + view_name + ' not found');
                          View = GrainView;
                      }
                 } else {
                          View = GrainView;
                 }

                 new View({model:object,project:view.project,spot:spot,compiled:compiled});
                 // a listener for the rerender event
                 if(model.on) {
                     model.off('rerender');
                     model.on('rerender',function() {
                         model.subspots.forEach(function (spot) {
                             var template = templates.get(template_name);
                             template.parse();
                             var compiled = template.get('compiled');
                             new View({model:model,spot:spot,compiled:compiled});
                         });
                     });
                 }


                 //  let ButtonView take over the div element defined in template 
                 //  if it's a button
                 if(button) {
                     new ButtonView({model:model,spot:spot});
                 }
             })
          }

    });
    return TaskView;
});
