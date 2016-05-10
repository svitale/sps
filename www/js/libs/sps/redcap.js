/**
  * @class
  * @author Steven Vitale <svitale@upenn.edu>
*/
var Redcap = function () {
    'use strict';
 //   var callBack = function () {};
    /**
    * Task type for project.
    * @fieldOf Project#
    * @private
    * @default null
    */
    var type = null;

    /**
    * Project_header_id of project.
    * @fieldOf Project#
    * @private
    * @default null
    */
    var id = null;

    /**
    * Project creation dialog.
    * @fieldOf Project#
    * @private
    * @default null
    */
    var message = null;

    /**
    * List of available task types for new projects.
    * @fieldOf Project#
    * @private
    * @default null
    */
    var subjects = {};

    this.collectionsLoader = function (imported) {
        var callback = function (rc_data) {loadCrfForm(rc_data,imported)};
        var url  = '/sps/data/redcap/collections.php';
        var postdata = {'imported':imported};
        sps.fetchJson(url,callback,postdata);
    }

    this.updatedStudiesLoader = function() {
        var callback = function (rc_data) {loadUpdatedStudiesForm(rc_data)};
        var url  = '/sps/data/redcap/updatedstudies.php';
        sps.fetchJson(url,callback);
    }
   

    var loadCrfForm = function(data,imported) {

        var subjects;
        var subject;
        var record;
        var html;
        if (imported) {
            html = "<h2>Previous REDCap Collections</h2>";
        } else {
            html = "<h2>New REDCap Collections</h2>";
        }
        if (data.subjects.length > 0) {
            html += "<div id='form'></div>";
            subjects = data.subjects;
            //TODO: move style to CSS
            html += "<div><table style=\"border:1px solid black;border-collapse:collapse;\">";
            html += "<tr><th style=\"border:1px solid black;\">Study</th>";
            html += "<th style=\"border:1px solid black;\">Subject ID</th>";
            if (!imported) {
                html += "<th style=\"border:1px solid black;\">Redcap ID</th>";
                html += "<th style=\"border:1px solid black;\">Study-Subject ID</th>";
                html += "<th style=\"border:1px solid black;\">Date Visit</th>";
                html += "<th style=\"border:1px solid black;\">Sample Types</th>";
                html += "<th style=\"border:1px solid black;\">Sample Source</th>";
            } else {
                html += "<th style=\"border:1px solid black;\">Started</th>";
            }
            html += "<th/></tr>";
            for (record in subjects) {
             subject = subjects[record];
             if (subject.id_study) {
               if (!subject.sample_source) {
                   subject.sample_source = '';
               }
               html += "<tr><td style=\"border:1px solid black;\">"+subject.id_study+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.id_subject+"</td>";
            if (!imported) {
               html += "<td style=\"border:1px solid black;\">"+subject.id_collection+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.id_ancillary+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.date_visit+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.sample_types+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.sample_source+"</td>";
            } else {
               html += "<td style=\"border:1px solid black;\">"+subject.time_created+"</td>";
            }
               html += '<td style=\"border:1px solid black;\">';
               html += '<form action="tasks/form/crf.php?imported='+imported+'" method="post">';
               html += '<input type="hidden" name="id_collection" value="'+subject.id_collection+'">';
               html += '<input type="hidden" name="id_subject" value="'+subject.id_subject+'">';
               html += '<input type="hidden" name="form_action" value="REDCAPImport">';
               html += '<input type="hidden" name="num_packets" value="1">';
            if (!imported) {
               html += '<input type="submit" name="submit" value="Import Collection"></td>';
            } else {
               html += '<input type="submit" name="submit" value="Resume Import"></td>';
            }
               html += '</tr></form>';
             }
            }
            html += "</table></div>";
            console.log(subjects);
      } else {
          html += "None"; 
      }
if (imported) {
            jQuery( "#redcapcrf_imported" ).html(html);
} else {
            jQuery( "#redcapcrf" ).html(html);
}
    }

    var loadUpdatedStudiesForm = function(data) {
        var subjects;
        var subject;
        var record;
        var html = "<h2>Updated Studies</h2>"
        console.log(data);
        //see if we got an error message
        if (typeof data.subjects === "undefined") {
          alert ("subjects")
        }
        if (typeof data.subjects === "error") {
          alert ("error")
        }
        //TODO: move style to CSS
        if (data.subjects.length > 0) {
            subjects = data.subjects;
            html += "<h2>Updated Studies</h2>"
            html += "<div><table style=\"border:1px solid black;border-collapse:collapse;\">";
            html += "<tr><th style=\"border:1px solid black;\">Redcap ID</th>";
            html += "<th style=\"border:1px solid black;\">Original Study</th>";
            html += "<th style=\"border:1px solid black;\">New Study</th>";
            html += "<th style=\"border:1px solid black;\">Subject ID</th>";
            html += "<th style=\"border:1px solid black;\">Study-Subject ID</th>";
            html += "<th style=\"border:1px solid black;\">Date Visit</th>";
            html += "<th/></tr>";
            for (record in subjects) {
             subject = subjects[record];
             if (subject.id_study) {
               html += "<tr><td style=\"border:1px solid black;\">"+subject.id_collection+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.original_study+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.id_study+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.id_subject+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.id_ancillary+"</td>";
               html += "<td style=\"border:1px solid black;\">"+subject.date_visit+"</td>";
               html += '<td style=\"border:1px solid black;\">';
               html += '<input type="hidden" name="id_collection" value="'+subject.id_collection+'">';
               html += '<input type="hidden" name="form_id_subject" value="'+subject.id_subject+'">';
               html += '<input type="hidden" name="old_id_study" value="'+subject.original_study+'">';
               html += '<input type="hidden" name="new_id_study" value="'+subject.id_study+'">';
               html += '<input type="hidden" name="form_action" value="REDCAPStudyUpdate">';
               html += '<input type="hidden" name="num_packets" value="1">';
               html += '<input type="submit" name="submit" value="Update Samples"></td>';
               html += '</tr></form>';
             }
            }
            console.log(subjects);
            html += "</table></div>";
        } else {
            html += "None";
        }
        jQuery( "#redcapupdatedstudy" ).html(html);
    }
}
