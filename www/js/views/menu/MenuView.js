define([
  'jquery',
  'underscore',
  'backbone',
  'bootstrap',
  'views/status/ErrorView',
], function($, _, Backbone, bootstrap, ErrorView){

    var MenuView = Backbone.View.extend({
        initialize: function() {
            var view = this;
            this.render();
        },

        render: function() {
            console.log('rendering menu view');
        },
        // build the spot lattice for the target
        events: {
            "click": "doClick",
            "change": "doChange"
        },
        doClick: function(evt) { 
        //    console.log(evt);
        //    console.log(this);
        },
        changeStudy: function(study) {
            console.log('study is ' + study);
            $.ajax({
                type: "POST",
                url: "npc.php?action=filter",
                data: { id_study: study }
             }).done(function () {
                window.location.reload();
             });
        },
        filter: function(name,value) {
            console.log(name + ' is ' + value);
            var data = {}
            data[name] = value;
            $.ajax({
                type: "POST",
                url: "/sps/data/",
                data: {'filters':data}
             }).done(function () {
//                window.location.reload();
             });
        },
        doChange: function(evt) { 
           //   console.log(evt);
           //special case:  look for change of study name
           var id = evt.target.value;
           var name = evt.target.name;
           if(name == 'study') {
             this.changeStudy(id);
             return;
           } else {
             this.filter(name,id);
           }
           console.log('changing ' + name + ' to ' + id);



        
            var url = Backbone.history.fragment;
            var vars = url.split('/');
            var found = false;
            if (vars.length % 2 == 0) {
                url = '';
                for (var i = 0; i < vars.length/2; i++) {
                    var var_name = vars[i*2];
                    var var_val = vars[i*2 + 1];
                    if(name == var_name) {
                        var_val = id;
                        found = true;
                    }
                url = url + '/' + var_name + '/' +  var_val;
                }
                if (!found) {
                    url = url + '/' + name + '/' +id;
                }

            Backbone.history.navigate(url,true);
            }
        },
        setValsFromUri: function()  {
            var url = Backbone.history.fragment;
            var vars = url.split('/');
            if (vars.length % 2 == 0) {
                for (var i = 0; i < vars.length/2; i++) {
                    var var_name = vars[i*2];
                    var var_val = vars[i*2 + 1];
                }
            }
        }
    
    });
    return MenuView;
});
