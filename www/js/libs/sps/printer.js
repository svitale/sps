/**
  * @class
  * @author Steven Vitale <svitale@upenn.edu>
*/
var Printjob = function (session) {
    'use strict';
    /**
    * Project_header_id of project.
    * @fieldOf Project#
    * @private
    * @default null
    */
// todo - remove any code that uses table_id instead of uuid
    this.table_id = false;
    this.batchid = false;
    this.subject = false;
    this.table = false;
    this.type = false;
    this.uuid = false;
    this.submitJob = function() {
       // if(session && session.debug) {
            console.log('submit job');
            console.log(this);
        //}
        var url = '/sps/data/printer/submit';
        var jobview = this.jobview;
        var postdata =  {
           'type':this.type,
           'subject':this.subject,
           'table':this.table,
           'uuid':this.uuid,
           'table_id':this.table_id,
           'batchid':this.batchid
        }
        var callback = function (data) {checkOnJob(data,jobview,0)};
        sps.fetchJson(url,callback,postdata);
    }
    var checkOnJob = function(data,jobview,count) {
        var retry_interval = 1000;
        var retry_limit = 5;
        var url = '/sps/data/printer/status';
        var postdata =  {'jobid':data.id};
        var callback = function (data) {checkOnJob(data,jobview,count)};
       // if(session && session.debug) {
            console.log('checking on job ' + count + " of " +retry_limit);
            console.log(data);
       // }
        var print_status = data.status;
        var button_value;
        count++;
        if (print_status == 'spooled' || print_status == 'init') {
            button_value = 'printing..';
            jQuery('#'+jobview).attr("disabled", "disabled");
            jQuery('#'+jobview).prop('value', button_value);
            if (count < retry_limit) {
                setTimeout(function() {
                    sps.fetchJson(url,callback,postdata);
                }, retry_interval);
            } else {
                button_value = 'timeout';
                jQuery('#'+jobview).attr("disabled", "disabled");
                jQuery('#'+jobview).prop('value', button_value);
            }
        } else {
            jQuery('#'+jobview).attr("disabled", "disabled");
            jQuery('#'+jobview).prop('value', print_status);
       }
    }
    this.setPrinter = function(printer_name) {
        var url = '/sps/data/printer/set';
        var postdata =  {'printer_name':printer_name};
        var callback = function (data) {console.log(data)};
        sps.fetchJson(url,callback,postdata);
    }
}
