define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone){
 var ErrorView = Backbone.View.extend({
        initialize: function(data,response) {
            this.$el = $('#taskcontainer');
            this.render(response.responseText);
            console.log('error processing model:');
            console.log(data);
        },
        render: function(server_response) {
            this.$el.html('<div class="alert">' + server_response + '</div>');
        }
    });


  return ErrorView;
});
