Ajax.Replacer = Class.create(Ajax.Updater, {
	initialize: function ($super, container, url, options) {
		options = options || {};
		options.onComplete = (options.onComplete || Prototype.emptyFunction).wrap(function (proceed, transport, json) {
			$(container).replace(transport.responseText);
			proceed(transport, json);
		})
		$super(container, url, options);
	}
})
/*
var DropDownMenu = Class.create();
DropDownMenu.prototype = {
	initialize: function (menuElement) {
		menuElement.childElements().each(function (node) {
			// if there is a submenu
			var submenu = $A(node.getElementsByTagName("ul")).first();
			if (submenu != null) {
				// make sub-menu invisible
				Element.extend(submenu).setStyle({
					display: 'none'
				});
				// toggle the visibility of the submenu
				node.onmouseover = node.onmouseout = function () {
					Element.toggle(submenu);
				}
			}
		});
	}
};
*/
function editResult(id,table) {
	Modalbox.show('npc.php?action=resultdetail&id=' + id + '&table=' + table + '', {
		title: 'Result Detail',
		width: 300
	});
}
function editResultValue(id,table) {
	new Ajax.InPlaceEditor('value', 'npc.php?action=ed', {
		formClassName: 'left_column',
		callback: function (form, value) {
			return 'value=' + escape(value) + '&id=' + id + '&field=value&table=' + table +''
		},
		onFailure: function () {
			alert('error setting value');
		},
		onComplete: function () {
			new Ajax.Replacer('result_' + id + '', 'npc.php?action=checkresult&id=' + id + '&table=' + table + '&role=reviewed&value=0', {
				asynchronous: true
			});
			editResult(id,table)
		}
	})
}
function hideClass(className) {
	results = $$('tr:not([class~=selected])');
	results.each(function (element) {
		if (element.hasClassName(className)) {
			Element.hide(element.parentNode);
		};
	});
}
function processClass(className) {
	results = $$('tr.selected td div.dp');
	results.each(function (element) {
		dp = element.id.replace(/\D/g, "");
		new Ajax.Updater('result_' + dp + '', 'npc.php?' + dp + '', {
			asynchronous: true,
			parameters: Form.serialize('daterange')
		});
	});
}
function checkResult(cb, id, role) {
	if (cb.checked) {
		var status = "1"
	} else {
		var status = "0"
	}
	new Ajax.Replacer('result_' + id + '', 'npc.php?action=checkresult&id=' + id + '&role=' + role + '&value=' + status + '', {
		asynchronous: true,
		parameters: Form.serialize('daterange')
	});
}
function checkChildren(element, role) {
	row = element.parentNode.parentNode;
	if (element.checked) {
		var status = "1"
	} else {
		var status = "0"
	}
	//dps = Selector.findChildElements(row, ['td div.dp'])
	dps = Selector.findChildElements(row, ['div.dp'])
	dps.each(function (dp) {
		id = dp.id.replace(/\D/g, "");
		new Ajax.Replacer('result_' + id + '', 'npc.php?action=checkresult&id=' + id + '&role=' + role + '&value=' + status + '', {
			asynchronous: true,
			parameters: Form.serialize('daterange')
		});
	});
}
function updateResult(id,table) {
	var notes = $F('notes');
	new Ajax.Request('npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=ed&table=' + table + '&id=' + id + '&field=notes&value=' + notes + '',
		onFailure: function () {
			alert('error recording note');
		}
	});
}
function updateResultsNote(id,table) {
	var notes = $F('notes');
	new Ajax.Request('npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=ed&table=' + table + '&id=' + id + '&field=notes&value=' + notes + '',
		onFailure: function () {
			alert('error recording note');
		}
	});
}
function invoiceSubject(id_subject) {
	new Ajax.Request('npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=invoicesubject&id_subject=' + id_subject + '',
		onSuccess: function (resp) {
			processUpdatesFromResponse(resp);
			setstatus('updating..');
			setfocus('scanIn');
			doClear('scanIn');
			setstatus('<A href="javascript: resetSession()">reset</A>');
		},
		onError: function (resp) {
			alert("Oops, there's been an error.");
		},
		parameters: "name=Fred"
	});
}
/* ajax.Request */
function ajaxRequestPaid(url, data) {
	var aj = new Ajax.Request(
	url, {
		method: 'get',
		parameters: data,
		onComplete: getResponsePaid
	});
}
/* ajax.Response */
function getResponsePaid(oReq) {
	$('paidDiv').innerHTML = oReq.responseText;
}
function processUpdatesFromResponse(resp) {
	var updates; // The updates from the response
	var id; // Each ID in the response
	var elm; // Each matching element
        if (resp.responseText) {
                                var html = "<h1>Error: SPS says:</h1>";
                                html += resp.responseText;
                                //jQuery('#taskcontainer').html(html);
        }
	updates = resp.responseJSON;
	if (updates) {
		for (id in updates) {
			elm = $(id);
			if (elm) {
				elm.update(updates[id]);
			}
		}
	}
}
function setfocus(element) {
	$(element).focus()
}
function setstatus(message) {
	$('status_action').innerHTML = message;
}
function doClear(element) {
	$(element).focus()
	$(element).value = ""
}
function highlightCell(element) {
	$(element).style.border = '1px solid #999999';
}
function selectedCell(element) {
	$(element).style.backgroundColor = 'yellow';
}
function resetCell(element) {
	$(element).style.border = '1px solid #000000';
}
function resetSession() {
    var sps = new Sps();
    sps.reset();
}
function addship() {
	new Ajax.Updater('changeme', 'npc.php?action=appendbatch', {
		asynchronous: true,
		parameters: Form.serialize('addship'),
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error adding shipment');
		}
	})
}
function fitstandards(mapdir) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=fitcurve',
		asynchronous: false,
		onSuccess: function () {
			new Ajax.Updater('processcontainer', 'processing/imagemap/map.php', {
			asynchronous: true,
			onFailure: function () {
				alert('error fitting curve');
			}
			})
		}
		});
	}
function daterange() {
	var datestart = $F('start');
	var dateend = $F('end');
	//	new Ajax.Updater('daterange', 'npc.php?action=daterange&startdate='+startdate+'&enddate='+enddate+'', {asynchronous:true, parameters:Form.serialize('daterange'),
	new Ajax.Updater('daterange', 'npc.php?action=daterange', {
		asynchronous: true,
		parameters: Form.serialize('daterange'),
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error setting daterange');
		}
	})
}
function newresult(id,table,assay) {
	new Ajax.Updater('detailcontainer', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&id_assay=' + assay + '&table=' + table + '&action=newresult',
		evalScripts: true,
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error adding order')
			setfocus('scanIn');
		}
	});
}
function deleteorder(id) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&action=deleteorder',
		evalScripts: true,
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error adding order')
			setfocus('scanIn');
		}
	});
}
function neworder(id,table) {
	new Ajax.Updater('detailcontainer', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&table=' + table + '&action=neworder',
		evalScripts: true,
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error adding order')
			setfocus('scanIn');
		}
	});
}
function setdates() {
	new Ajax.Updater('daterange', 'npc.php?action=daterange', {
		asynchronous: true,
		parameters: Form.serialize('daterange'),
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error setting daterange');
		}
	})
}




function importBatch(table) {
	new Ajax.Updater('dashboard', 'npc.php?action=importbatch&value=' + table + '', {
		asynchronous: true,
		onSuccess: function () {
			alert('imported');
		},
		onFailure: function () {
			alert('error importing');
		}
	})
}
function remship(id, id_subject) {
	new Ajax.Updater('changeme', 'npc.php?action=disjoinbatch', {
		asynchronous: true,
		parameters: Form.serialize('addship'),
		postBody: 'action=disjoinbatch&id=' + id + '&id_subject=' + id_subject + '',
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error deleting shipment');
		}
	})
}
function remitem(id) {
	new Ajax.Updater('changeme', 'npc.php', {
		asynchronous: true,
		parameters: Form.serialize('addship'),
		postBody: 'action=remitem&id=' + id + '',
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error deleting item');
		}
	})
}
function setbatch(id_subject, id_visit, date_visit) {
	new Ajax.Updater('changeme', 'npc.php', {
		asynchronous: true,
		parameters: Form.serialize('addship'),
		postBody: 'action=setbatch&id_subject=' + id_subject + '&id_visit=' + id_visit + '&date_visit=' + date_visit + '',
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error deleting shipment');
		}
	})
}
function aliquot(id, id_subject) {
	new Ajax.Updater('changeme', 'npc.php', {
		asynchronous: true,
		parameters: Form.serialize('addship'),
		postBody: 'action=aliquot&id=' + id + '&id_subject=' + id_subject + '',
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error deleting shipment');
		}
	})
}
function remid(id) {
	new Ajax.Updater('changeme', 'npc.php', {
		asynchronous: true,
		parameters: Form.serialize('addship'),
		postBody: 'action=disjoinbatch&id=' + id + '',
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error deleting shipment');
		}
	})
}
function thaw(value) {
	new Ajax.Updater('quant_thaws', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=thaw&value=' + value + ''
	})
}
function alq(daughters) {
	new Ajax.Updater('alq_tot', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=alq&table=items&daughters=' + daughters + ''
	})
}
function showMenu() {
	statusMenu = document.getElementById('hiddenStatusMenu');
	if (statusMenu.value == 0) {
		statusMenu.value = 1;
		Effect.toggle('searchmenu', 'appear');
		return false;
	}
}
function hideMenu() {
	statusMenu = document.getElementById('hiddenStatusMenu');
	if (statusMenu.value == 1) {
		statusMenu.value = 0;
		Effect.toggle('searchmenu', 'appear');
		return false;
	}
}
function postUuid() {
	var uuid = $F('scanIn');
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=scan&uuid=' + uuid + '',
		onSuccess: function () {
			//			getItemUuid(uuid);
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('boxView')) {} else {
		setTimeout("Element.show('boxView')", 300);
	}
	setstatus('updating..');
	setfocus('scanIn');
	doClear('scanIn');
}
function newUuid() {
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=scan',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('boxView')) {} else {
		setTimeout("Element.show('boxView')", 300);
	}
	setstatus('updating..');
	setfocus('scanIn');
	doClear('scanIn');
}
function postScan(scanIn) {
    // make scan field read-only untill processing is complete
    $('scanIn').disabled = "disabled";
    $('scanIn').value = "Working...";
	new Ajax.Updater('actioncontainer', 'npc.php', {
		asynchronous: false,
		method: 'post',
		evalScripts: true,
		postBody: 'action=scan&value=' + scanIn + '',
		onSuccess: function (data) {
			console.log(data);
                },
        onComplete: function () {
            doClear('scanIn');
            $('scanIn').disabled = false;
            setfocus('scanIn');
        }
	});
}
function newContainer() {
    // make scan field read-only untill processing is complete
	new Ajax.Updater('actioncontainer', 'npc.php', {
		asynchronous: false,
		method: 'post',
		evalScripts: true,
		postBody: 'action=newcontainer',
        	onComplete: function () {
	            setfocus('scanIn');
		    Element.hide('newcontainerbutton');
        	}
	});
}
function postId(id) {
        jQuery('#detailcontainer').empty();
        jQuery('#containercontainer').empty();
	new Ajax.Updater('actioncontainer', 'npc.php', {
		method: 'post',
		asynchronous: false,
		evalScripts: true,
		postBody: 'action=scan&value=' + id + '&type=tableId',
		onSuccess: function () {}
	});
	setfocus('scanIn');
	doClear('scanIn');
}
function invoiceDetail(uuid) {
	new Ajax.Updater('worksheet_1', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=detail&table=batch_quality&uuid=' + uuid + '',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
		}
	});
	if (Element.visible('worksheet_1')) {} else {
		setTimeout("Element.show('worksheet_1')", 300);
	}
	setstatus('updating..');
	setfocus('scanIn');
	doClear('scanIn');
}
function alqid(id, table, daughters) {
	new Ajax.Updater('crfBox', 'npc.php', {
		method: 'post',
		postBody: 'action=alq&id=' + id + '&table=' + table + '&daughters=' + daughters + '',
		onSuccess: function () {
			window.location.reload();
			setstatus('<A href="javascript: resetSession()">reset</A>');
		}
	});
	if (Element.visible('crfBox')) {} else {
		setTimeout("Element.show('crfBox')", 300);
	}
	setstatus('updating..');
	setfocus('scanIn');
	doClear('scanIn');
}
function alqandprint(id, table, daughters) {
	new Ajax.Updater('crfBox', 'npc.php', {
		method: 'post',
		postBody: 'action=alq&id=' + id + '&table=' + table + '&daughters=' + daughters + '',
		onSuccess: function () {
			window.location.reload();
			setstatus('<A href="javascript: resetSession()">reset</A>');
		}
	});
	if (Element.visible('crfBox')) {} else {
		setTimeout("Element.show('crfBox')", 300);
	}
	setstatus('updating..');
	setfocus('scanIn');
	doClear('scanIn');
}
function alqbatch(id_subject) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=alqbatch&id_subject=' + id_subject + '',
		asynchronous: false,
		onSuccess: function () {
			if (id_subject == 'all') {
			window.location.reload();
			} else {
			invoiceSubject(id_subject);
			}
		},
		onFailure: function () {
			alert('error creating aliquots');
		}
	});
}
function replaceBox(id) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=replacebox&id=' + id + '',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error creating aliquots');
		}
	});
}



function groupScan(id,barcodes) {
        new Ajax.Request('npc.php', {
                method: 'post',
                postBody: 'action=replacebox&id=' + id + '',
                asynchronous: false,
                onSuccess: function () {
                        for (bc in barcodes) {
                                postScan(barcodes[bc]);
                        }
                        window.location.reload();
                },
                onFailure: function () {
                        alert('error creating box');
                }
        });
}
function alqall() {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=alqbatch',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload()
		},
		onFailure: function () {
			alert('error creating aliquots');
		}
	});
}
function printalqs(subject) {
                        var printjob = new Printjob(subject);
                        printjob.type = 'batchdaughters';
                        printjob.subject = subject;
                        printjob.submitJob();
//	new Ajax.Request('npc.php', {
//		method: 'post',
//		postBody: 'action=printaliquots&id_subject=' + id_subject + '',
//		asynchronous: false,
//		onSuccess: function () {
//			alert('labels spooled');
//		},
//		onFailure: function () {
//			alert('error printing aliquots');
//		}
//	});
}
function printBatch(batchid) {
                        var printjob = new Printjob(batchid);
                        printjob.type = 'batch';
                        printjob.batchid  = batchid;
                        printjob.submitJob();
}
function jobJson(job) {
	alert(job.printer_name);
}

function batchJob(id) {
var url = '/sps/data/print/queue.php';
        new Ajax.Request(url, {
          method: 'post',
          parameters: {'id': id, 'type' : 'batch', 'action' : 'submit'},
          requestHeaders: {Accept: 'application/json'},
          onSuccess: function(transport){
            var json = transport.responseText.evalJSON(true);
            jobJson(json);
                },
          onFailure: function(transport){
$('report_body').innerHTML = "Error: could not connect to server";
        }
        });
}

function printSingleItem(id) {
var url = '/sps/data/print/queue.php';
        new Ajax.Request(url, {
          method: 'post',
          parameters: {'id': id, 'type' : 'item', 'action' : 'submit'},
          requestHeaders: {Accept: 'application/json'},
          onSuccess: function(transport){
            var json = transport.responseText.evalJSON(true);
            jobJson(json);
                },
          onFailure: function(transport){
$('report_body').innerHTML = "Error: could not connect to server";
        }
        });
}



function dashboard() {
	new Ajax.Updater('dashboard', 'npc.php?action=dashboard', {
		asynchronous: true,
		onFailure: function () {
			alert('error updating dashboard');
		}
	})
}



function postid() {
	var id = $F('idIn');
	new Ajax.Updater('boxInfo', 'npc.php', {
		method: 'post',
		postBody: 'action=search&id=' + id + '',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('boxInfo')) {} else {
		setTimeout("Element.show('boxInfo')", 300);
	}
	setstatus('updating..');
	setfocus('idIn');
	doClear('idIn');
}
function findsamples() {
	var id = $F('idIn');
	new Ajax.Updater('detailcontainer', 'npc.php', {
		method: 'post',
		postBody: 'action=findsamples&id=' + id + '',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('detailcontainer')) {} else {
		setTimeout("Element.show('detailcontainer')", 300);
	}
	setstatus('updating..');
	setfocus('idIn');
	doClear('idIn');
}
function printBlanks() {
	var quant = $F('idIn');
	var format = $F('formatIn');
	var copies = $F('copiesIn');
	new Ajax.Updater('staticcontainer', 'npc.php', {
		method: 'post',
		postBody: 'action=printblanks&quant=' + quant + '&copies='+copies+'&format='+format+'',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('miscInfo')) {} else {
		setTimeout("Element.show('miscInfo')", 300);
	}
	setstatus('updating..');
	setfocus('idIn');
	doClear('idIn');
}
function benchView() {
	new Ajax.Updater('dashboard', 'npc.php', {
		method: 'post',
		postBody: 'action=benchview'
	});
}
function selectUuid(uuid) {
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=scan&uuid=' + uuid + '',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('boxView')) {} else {
		setTimeout("Element.show('boxView')", 300);
	}
	setstatus('updating..');
	seeBoxStatus();
	setfocus('scanIn');
	doClear('scanIn');
	benchView();
}
function selectId(id) {
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=scan&id=' + id + '',
		onSuccess: function () {
			setstatus('<A href="javascript: resetSession()">reset</A>');
			benchView();
		}
	});
	if (Element.visible('boxView')) {} else {
		setTimeout("Element.show('boxView')", 300);
	}
	setstatus('updating..');
	seeBoxStatus();
	setfocus('scanIn');
	doClear('scanIn');
	benchView();
			getItemId(id);
}
function postSubjectId() {
	var id = $F('scanIn');
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=summary&id=' + id + ''
	});
	if (Element.visible('boxView')) {} else {
		setTimeout("Element.show('boxView')", 300);
	}
	setfocus('scanIn');
	doClear('scanIn');
}
function newType(id, type) {
new Ajax.Request('npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'id=' + id + '&type=' + type + '&action=newType',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
		},
		onFailure: function () {
			alert('error setting type');
		}
	});
}

function newParam(id, param) {
	new Ajax.Updater('taskcontainer', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'id=' + id + '&param=' + param + '&action=newParam',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}


function newFreezer(id, freezer) {
	new Ajax.Updater('staticcontainer', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'id=' + id + '&freezer=' + freezer + '&action=newFreezer',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}







function setFreezer(id, freezer) {
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'id=' + id + '&freezer=' + freezer + '&action=setFreezer',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload()
			//setfocus('scanIn');
			//doClear('scanIn');
		},
		onFailure: function () {
			alert('error setting freezer');
		}
	});
}
function selectFreezer(shelfid, freezerid) {
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		postBody: 'freezerid=' + freezerid + '&shelfid=' + shelfid + '&action=selectFreezer',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}
function newDest(id, destination) {
new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&destination=' + destination + '&action=newDest',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}


function skipSpot(container, spot, hide) {
new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=skip&container=' + container + '&spot=' + spot + '&hide=' + hide,
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(container);
		},
		onFailure: function () {
			alert('error skipping spot');
		}
	});
}

function placeSpot(container, j, k, clear) {
new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=place&container=' + container + '&j=' + j + '&k=' + k + '&clear=' + clear,
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(container);
		},
		onFailure: function () {
			alert('error placing spot');
		}
	});
}


function newSampleType(id, type) {
new Ajax.Request('npc.php', {
//	new Ajax.Updater('actioncontainer', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&type=' + type + '&action=newSampleType',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
		},
		onFailure: function () {
			alert('error setting sample type');
		}
	});
}







function setItemParams(id, type, width, hight, comment1) {
	new Ajax.Updater('staticicontainer', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&type=' + type + '&width=' + width + '&hight=' + hight + '&comment1=' + comment1 + '&action=associd',
		asynchronous: false,
		onSuccess: function () {
console.log('foo');
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
			benchView();
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}
function addFreezer() {
	var id = $F('id');
	var itemtype = $F('itemtype');
	var width = $F('width');
	var hight = $F('hight');
	var comment1 = $F('comment1');
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&type=' + itemtype + '&width=' + width + '&hight=' + hight + '&comment1=' + comment1 + '&action=associd',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
			doClear('scanIn');
			postId(id);
			benchView();
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}
function printlabel(id, table) {
	new Ajax.Updater('dashboard', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&table=' + table + '&action=printlabel',
		evalScripts: true,
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error printing')
			setfocus('scanIn');
		}
	});
}

function printcontents(id) {
                        var printjob = new Printjob();
                        printjob.type = 'boxcontents';
                        printjob.table_id  = id;
                        printjob.submitJob();
}

function printarray(id) {
	new Ajax.Updater('status_action', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&action=printarray',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error printing')
			setfocus('scanIn');;
		}
	});
}
function exportcontents(id, table) {
	new Ajax.Updater('status_action', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&table=' + table + '&action=exportcontents',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error printing')
			setfocus('scanIn');;
		}
	});
}

function exportContents() {
	new Ajax.Updater('status_action', 'npc.php', {
		method: 'post',
		postBody: 'action=data&format=xls',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error in export')
			setfocus('scanIn');;
		}
	});
}



function exportboxlist(freezer, shelf, rack) {
	new Ajax.Updater('status_action', 'npc.php', {
		method: 'post',
		postBody: 'freezer=' + freezer + '&shelf=' + shelf + '&rack=' + rack + '&action=exportboxlist',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error printing')
			setfocus('scanIn');;
		}
	});
}
function manifest(id) {
	new Ajax.Updater('status_action', 'npc.php', {
		method: 'post',
		postBody: 'action=manifest&id=' + id + '',
		asynchronous: false,
		onSuccess: function () {
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('error printing')
			setfocus('scanIn');;
		}
	});
}
function cleardestination(id) {
	new Ajax.Updater('status_action', 'npc.php', {
		method: 'post',
		postBody: 'id=' + id + '&action=cleardestination',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload()
			setfocus('scanIn');
		},
		onFailure: function () {
			alert('could not clear location')
			setfocus('scanIn');;
		}
	});
}



     function clearlocation(id) {
                new Ajax.Updater('detailcontainer', 'npc.php', {method: 'post', postBody: 'id='+id+'&action=clearlocation',  asynchronous: false,
                onSuccess: function() {
                        window.location.reload()
                        setfocus('scanIn');
                },
                onFailure: function() {
                        alert('could not clear location')
                        setfocus('scanIn');
                        ;
                },
 });
}


function seeBox() {
	var uuid = $F('scanIn');
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		postBody: 'action=box'
	});
	if (Element.visible('boxView')) {} else {
		setTimeout("Element.show('boxView')", 300);
	}
	seeBoxStatus();
	setfocus('scanIn');
	doClear('scanIn');
}
function seeBoxStatus() {
	new Ajax.Updater('boxStatus', 'npc.php', {
		method: 'post',
		postBody: 'action=boxstatus'
	});
	if (Element.visible('boxStatus')) {} else {
		setTimeout("Element.show('boxStatus')", 300);
	}
}
function clearText(field) {
	field.value = ""
}
function getInventory(container, format, id_parent) {
	new Ajax.Updater(container, 'npc.php', {
		method: 'post',
		postBody: 'action=getInventory&format=' + format + '&parent=' + id_parent + '',
		evalScripts: true
	});
}
function filterType(filter) {
	new Ajax.Updater('typeSelect_' + filter + '', 'npc.php', {
		method: 'post',
		postBody: 'action=getType&filter=' + filter + ''
	});
}
function filterVar(variable, table, mod) {
	new Ajax.Request('npc.php?action=filtervar&variable=' + variable + '&table=' + table + '&mod=' + mod + '', {
		onSuccess: function (transport) {
			var data = transport.responseText;
			new Effect.Fade('varSelect_' + variable + '', {
				afterFinish: function () {
					$('varSelect_' + variable + '').update(data);
					new Effect.SlideDown('varSelect_' + variable + '', {
						duration: 1.0
					});
				}
			});
		}
	});
}
function setParam(param, value, mod) {
	new Ajax.Updater('varSelect_' + param, 'npc.php', {
		method: 'post',
		postBody: 'action=setparam&param=' + param + '&value=' + value + '&mod=' + mod + ''
	});
}

function hideParam(element) {
//alert(+ element +);
}



function displayFreezer(freezer) {
	new Ajax.Updater('shelfList', 'npc.php', {
		method: 'post',
		postBody: 'action=getFreezer&freezer=' + freezer + ''
	});
	if (Element.visible('shelfList')) {} else {
		setTimeout("Element.show('shelfList')", 300);
	}
}
function toggleClip() {
	if (Element.visible('ClipMenu')) {
		Effect.Fade('ClipMenu', {
			duration: 0.1
		});
	} else {
		new Ajax.Updater('ClipMenu', 'clip.php', {
			method: 'post',
			postBody: 'action=getClip'
		});
		setTimeout("Effect.Appear('ClipMenu', { duration: 0.1 })", 300);
	}
}
function displayShelf(freezer, shelf) {
	new Ajax.Updater('rackList', 'npc.php', {
		method: 'post',
		postBody: 'action=getShelf&freezer=' + freezer + '&shelf=' + shelf + ''
	});
	if (Element.visible('rackList')) {} else {
		setTimeout("Element.show('rackList')", 300);
	}
	Element.hide('boxList');
	Element.hide('boxInfo');
	Element.hide('boxEdit');
	Element.hide('textEdit');
}
function displayRack(freezer, shelf, rack) {
	new Ajax.Updater('boxList', 'npc.php', {
		method: 'post',
		postBody: 'action=getRack&freezer=' + freezer + '&shelf=' + shelf + '&rack=' + rack + ''
	});
	if (Element.visible('boxList')) {} else {
		setTimeout("Element.show('boxList')", 300);
	}
	Element.hide('boxInfo');
	Element.hide('boxEdit');
	Element.hide('textEdit');
}
function displayBox(freezer, shelf, rack, box) {
	new Ajax.Updater('boxInfo', 'npc.php', {
		method: 'post',
		postBody: 'action=getBox&freezer=' + freezer + '&shelf=' + shelf + '&rack=' + rack + '&box=' + box + ''
	});
	if (Element.visible('boxInfo')) {} else {
		setTimeout("Element.show('boxInfo')", 300);
	}
	edBox(freezer, shelf, rack, box);
}
function reconcile() {
	new Ajax.Updater('boxInfo', 'npc.php', {
		method: 'post',
		postBody: 'action=reconcile'
	});
	if (Element.visible('boxInfo')) {} else {
		setTimeout("Element.show('boxInfo')", 300);
	}
	edBox(freezer, shelf, rack, box);
}
function fillBox(boxuuid, position, sampleuuid) {
	new Ajax.Updater('boxInfo', 'npc.php', {
		method: 'post',
		postBody: 'action=fillBox&boxuuid=' + boxUuid + '&positino=' + position + '&sampleuuid=' + sampleuuid + ''
	});
}
//function getItemId(id) {
//var store = new Store();
//store.makeActiveObject(id);
//}
function getItemId(id) {
	new Ajax.Updater('detailcontainer', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=detail&table=items&id=' + id + ''
	});
	Effect.Appear('detailcontainer', {
		duration: 0.3
	});
}
function getItemUuid(uuid) {
	new Ajax.Updater('detailcontainer', 'npc.php', {
		method: 'post',
		evalScripts: true,
		postBody: 'action=detail&table=items&uuid=' + uuid + ''
	});
	Effect.Appear('detailcontainer', {
		duration: 0.3
	});
}
function mouseOverWell(subjectid) {
	new Ajax.Updater('wellList', 'npc.php', {
		method: 'post',
		postBody: 'action=showWell&freezer=' + freezer + '&shelf=' + shelf + '&rack=' + rack + '&box=' + box + '&column' + column + '&row=' + row + ''
	});
}
function edBox(freezer, shelf, rack, box) {
	new Ajax.Updater('boxEdit', 'npc.php', {
		method: 'post',
		postBody: 'action=edBox&freezer=' + freezer + '&shelf=' + shelf + '&rack=' + rack + '&box=' + box + ''
	});
	if (Element.visible('boxEdit')) {} else {
		setTimeout("Element.show('boxEdit')", 300);
	}
}
function hideAll() {
	Element.hide('shelfList');
	Element.hide('rackList');
	Element.hide('boxInfo');
	Element.hide('boxEdit');
	Element.hide('boxList');
	Element.hide('textEdit');
}
function displayTray(day, month) {
	new Ajax.Updater('trayList', 'npc.php', {
		method: 'post',
		postBody: 'action=displayTray&&d=' + day + '&m=' + month + ''
	});
	if (Element.visible('trayList')) {} else {
		setTimeout("Element.show('trayList')", 300);
	}
}
function refreshDay() {
	new Ajax.Updater('eventList', 'npc.php', {
		method: 'post',
		postBody: 'action=startDay'
	});
}
function displaySubject(subid) {
	new Ajax.Updater('meetSub', 'npc.php', {
		method: 'post',
		postBody: 'action=meetSub&&subid=' + subid + ''
	});
	if (Element.visible('meetSub')) {} else {
		setTimeout("Effect.Appear('meetSub')", 300);
	}
}
function loadSummary(id) {
	new Ajax.Updater('Summary', 'npc.php', {
		method: 'post',
		postBody: 'action=summary&&id=' + id + ''
	});
	if (Element.visible('Summary')) {} else {
		setTimeout("Effect.Appear('Summary')", 300);
	}
}
function displayProtocol(irbid) {
	new Ajax.Updater('meetProto', 'npc.php', {
		method: 'post',
		postBody: 'action=meetProto&&irbid=' + irbid + ''
	});
	if (Element.visible('meetProto')) {} else {
		setTimeout("Effect.Appear('meetProto')", 300);
	}
}
function confirmAppt() {
	new Ajax.Updater('confirm', 'npc.php', {
		method: 'post',
		postBody: 'action=confirmappt'
	});
}
function clearTimes() {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=ses_clear_times',
		asynchronous: false,
		onSuccess: refreshDay()
	});
}
function startTransfer(family) {
	new Ajax.Updater('transferList', 'npc.php', {
		method: 'post',
		postBody: 'action=transfer&family=' + family + ''
	});
}
function addEvent(day, month, year, body) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=addEvent&d=d+&m=m&y=m&body=body',
		onSuccess: highlightEvent(day)
	});
	$('evtBody').value = '';
}
function highlightEvent(day) {
	Element.Hide('addEventForm');
}
function setTask(task) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=settask&task=' + task + '',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error setting task');
		}
	});
}
function filterSam(id, value) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=filtersam&' + id + '=' + value + '',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error setting type');
		}
	});
	displayFreezer();
}
function filter(id, value) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=filter&'+id+'='+value+'',
                asynchronous: false,
        onSuccess: function() {
//			window.location.reload();
	},
        onFailure: function() {
            alert('error setting type');
        }
    }); 
}

function filterTarget(id,value) {
        new Ajax.Request('npc.php',  {
                method: 'post',
                postBody: 'action=filter&'+id+'='+value+'',
                asynchronous: false,
                onFailure: function() {
                        alert('error setting type')
                        ;
                },
        });
        }

	        function setVar(variable,value,select) {
        new Ajax.Request('npc.php',  {
                method: 'post',
                postBody: 'action=setvar&variable='+variable+'&value='+value+'&select='+select+'',
                asynchronous: false,
                onFailure: function() {
                        alert('error setting variable')
                        ;
                },
        });
        }




        function filtersamVisit(visit) {
        new Ajax.Request('npc.php',  {
		postBody: 'action=filter&' + id + '=' + value + '',
		asynchronous: false,
		onSuccess: function () {
			//			window.location.reload();
		},
		onFailure: function () {
			alert('error setting type');
		}
	});
}
function filterTarget(id, value) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=filter&' + id + '=' + value + '',
		asynchronous: false,
		onFailure: function () {
			alert('error setting type');
		}
	});
}
function setVar(variable, value, select) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=setvar&variable=' + variable + '&value=' + value + '&select=' + select + '',
		asynchronous: false,
		onFailure: function () {
			alert('error setting variable');
		}
	});
}
function filtersamVisit(visit) {
	new Ajax.Request('npc.php', {
		method: 'post',
		postBody: 'action=filtersam&visit=' + visit + '',
		asynchronous: false,
		onSuccess: function () {
			window.location.reload();
		},
		onFailure: function () {
			alert('error setting visit');
		}
	});
}

var offsetx = 12;
var offsety = 8;
function newelement(newid) {
	if (document.createElement) {
		var el = document.createElement('div');
		el.id = newid;
		with(el.style) {
			display = 'none';
			position = 'absolute';
		}
		el.innerHTML = '&nbsp;';
		document.body.appendChild(el);
	}
}
var ie5 = (document.getElementById && document.all);
var ns6 = (document.getElementById && !document.all);
var ua = navigator.userAgent.toLowerCase();
var isapple = (ua.indexOf('applewebkit') != -1 ? 1 : 0);
function getmouseposition(e) {
	if (document.getElementById) {
		var iebody = (document.compatMode && document.compatMode != 'BackCompat') ? document.documentElement : document.body;
		pagex = (isapple == 1 ? 0 : (ie5) ? iebody.scrollLeft : window.pageXOffset);
		pagey = (isapple == 1 ? 0 : (ie5) ? iebody.scrollTop : window.pageYOffset);
		mousex = (ie5) ? event.x : (ns6) ? clientX = e.clientX : false;
		mousey = (ie5) ? event.y : (ns6) ? clientY = e.clientY : false;
		var lixlpixel_tooltip = document.getElementById('tooltip');
		lixlpixel_tooltip.style.left = (mousex + pagex + offsetx) + 'px';
		lixlpixel_tooltip.style.top = (mousey + pagey + offsety) + 'px';
	}
}
function tooltip(tip) {
	if (!document.getElementById('tooltip')) newelement('tooltip');
	var lixlpixel_tooltip = document.getElementById('tooltip');
	lixlpixel_tooltip.innerHTML = tip;
	lixlpixel_tooltip.style.display = 'block';
	document.onmousemove = getmouseposition;
}
function askUuid(newbox) {
	if (Element.visible('scanIn')) {} else {
		setTimeout("Element.show('scanIn')", 300);
	}
}
function exit() {
	document.getElementById('tooltip').style.display = 'none';
}
//Invoked from the inventory tab; displays the contents of a container.
function freezerSelect(freezer) {
	//alert("freezerSelect:" + freezer);
	var dobj = document.getElementById('freezerView');
	dobj.innerHTML = "<h1>Searching for freezer " + freezer + "...</h1>";
	Element.show("freezerView");
	Element.hide("shelfView");
	Element.hide("rackView");
	Element.hide("boxView");
	new Ajax.Updater('freezerView', 'npc.php', {
		method: 'post',
		parameters: {
			'action': 'freezerSelect',
			'freezername': freezer
		}
	});
}
function shelfSelect(freezer, shelfId) {
	//alert("shelfSelect:" + freezer + ", " + shelfId);
	var dobj = document.getElementById('shelfView');
	dobj.innerHTML = "<h1>Searching for shelf " + shelfId + "...</h1>";
	Element.show("shelfView");
	Element.hide("rackView");
	Element.hide("boxView");
	//user is searching for all orphaned shelves, hide the freezer view
	if (((freezer.length == 0) || (freezer == "null")) && ((shelfId.length == 0) || (shelfId == "null"))) {
		Element.hide("freezerView");
	}
	new Ajax.Updater('shelfView', 'npc.php', {
		method: 'post',
		parameters: {
			'action': 'shelfSelect',
			'freezername': freezer,
			'shelfid': shelfId
		}
	});
}
function rackSelect(freezer, shelfId, rackId) {
	//alert("rackSelect:" + freezer + ", " + shelfId + ", " + rackId);
	var dobj = document.getElementById('rackView');
	dobj.innerHTML = "<h1>Searching for rack " + rackId + "...</h1>";
	Element.show("shelfView");
	Element.show("rackView");
	Element.hide("boxView");
	//user is searching for all orphaned racks, hide the freezer and shelf views
	if (((freezer.length == 0) || (freezer == "null")) && ((shelfId.length == 0) || (shelfId == "null")) && ((rackId.length == 0) || (rackId == "null"))) {
		Element.hide("freezerView");
		Element.hide("shelfView");
	}
	new Ajax.Updater('rackView', 'npc.php', {
		method: 'post',
		parameters: {
			'action': 'rackSelect',
			'freezername': freezer,
			'shelfid': shelfId,
			'rackid': rackId
		}
	});
}
function boxSelect(freezer, shelfId, rackId, boxId) {
	//alert("boxSelect:" + freezer + ", " + shelfId + ", " + rackId + ", " + boxId);
	var dobj = document.getElementById('boxView');
	dobj.innerHTML = "<h1>Searching for box " + boxId + "...</h1>";
	Element.show("shelfView");
	Element.show("rackView");
	Element.show("boxView");
	// user is searching for orphaned boxes, hide freezer, shelf and rack views
	if (((freezer.length == 0) || (freezer == "null")) && ((shelfId.length == 0) || (shelfId == "null")) && ((rackId.length == 0) || (rackId == "null")) && ((boxId.length == 0) || (boxId == "null"))) {
		Element.hide("freezerView");
		Element.hide("shelfView");
		Element.hide("rackView");
	}
	new Ajax.Updater('boxView', 'npc.php', {
		method: 'post',
		parameters: {
			'action': 'boxSelect',
			'freezername': freezer,
			'shelfid': shelfId,
			'rackid': rackId,
			'boxid': boxId
		}
	});
}
// handles the 'detail' button on the inventory page
function inventoryDetail(uuid) {
	//alert("inventoryDetail(" + uuid + ")");
	postScan(uuid);
}

// handles the 'detail' button on the inventory page
function inventoryDetailId(in_id) {
	//alert("inventoryDetailId(" + in_id + ")");
	postId(in_id);
}
/*
function scrollBar(scrollbar_content,scrollbar_track,scrollbar_handle) {
var scrollbar_handle = new Control.Slider(scrollbar_handle,scrollbar_track,{
axis: 'vertical',
onSlide: function(v) { scrollVertical(v, scrollbar_content, scrollbar_handle); },
onChange: function(v) { scrollVertical(v, scrollbar_content, scrollbar_handle); }
	}); 
}
function scrollVertical(value, element, slider) {
	 element.scrollTop = Math.round(value/slider.maximum*(element.scrollHeight-element.offsetHeight));
}
*/
