/**
 * @class
 * @author Steven Vitale <svitale@upenn.edu>
 */
var Roles = function(session) {
        'use strict';
	this.initialize = function() {
		var callback = function(roles) {
			Loader(roles);
		};
		var url = '/sps/data/roles/';
		sps.fetchJson(url, callback);
       };
       var setRole = function(username,rolename,enabled) {
                var url='/sps/data/roles/set.php';
		var callback = function(data) {
                    if (!data.success) {
                        alert("error setting role");
                    }
                }
                var postdata = {'rolename':rolename, 'username':username, 'enabled':enabled};
		sps.fetchJson(url, callback, postdata);
               
       }
       var Loader = function(data) {
            jQuery('#taskcontainer').empty();
            jQuery('#taskcontainer').append('<div id="rolescontainer" class="span4"></div>')
            var userlist = [];
            var columns = []; 
            var options = {
                    autoWidth : true,
                    autoHeight : true,
                    enableCellNavigation : true,
                    forceFitColumns : true
            };
            columns.push({
                    id : 'user',
                    name : 'User',
                    field : 'username'
            });
            for (var i = 0; i < data.role_names.length; i++) {
                    columns.push({
                   //      width: 30,
                         id : data.role_names[i],
                         name : data.role_names[i],
                         field : data.role_names[i],
			 formatter :
                          function(row, cell, value, columnDef, dataContext) {
                              var role_name = columnDef.field;
                              var username = dataContext.username;
                              var inherited = false;
                              var user;
                              var checked = "";
                              var disabled = "";
                              var html = "";
                              user = dataContext.user;
                              if (username === session.auth.username) {
                                 disabled = "disabled";
                              }
                              if (user[role_name]) {
                                 checked = "checked";
                                 if (user[role_name].inherited && user[role_name].inherited==1) {
                                     inherited = true;
                                 }
                              }
                              if (inherited) {
                                  html +=  "<img src='images/star10.gif'>";
                              } else {
                                  html += "<input id='"+username+"' value='"+role_name+"' class='roles_checkbox' type='checkbox' ";
                                  html += disabled +" "+ checked +" />";
                              }
                              return html;
                          }
                    });
            }
            var userdata = [];
            i=0;
            for (var username in data.user_roles) {
                userdata[i] = {
                     id: i, 
                     username: username,
                     user: data.user_roles[username],
                };
	        i++;
            }
            var dataView = new Slick.Data.DataView({
                    inlineFilters : true
            });
            function myFilter(item) {
                    return true;
            }
            dataView.beginUpdate();
            dataView.setItems(userdata);
            dataView.setFilter(myFilter);
            dataView.endUpdate();
            new Slick.Grid("#rolescontainer", dataView, columns, options);
            jQuery(".roles_checkbox").on("click", function(){
               var enabled = null;
               if(jQuery(this).is(':checked')){
                   enabled = true;
               }
               setRole(this.id,this.value,enabled)
            });
        };
}
