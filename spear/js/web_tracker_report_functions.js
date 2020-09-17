var g_tracker_id = "";
var tdt;

var report_cols=[];
report_cols[0] = `<option value="cid" selected>Client ID</option>
                      <option value="session_id">Session ID</option>
                      <option value="public_ip" selected>Public IP</option>
                      <option value="internal_ip" selected>Private IP</option>
                      <option value="time" selected>Hit Time</option>
                      <option value="browser">Browser</option>
                      <option value="platform">Platform</option>
                      <option value="user_agent" selected>User Agent</option>`; //common cols
var reportTypeSelector_cols = [];
reportTypeSelector_cols[0] = ['no', 'cid', 'public_ip', 'internal_ip', 'time', 'user_agent'].slice();


$("#reportTypeSelector").select2({
	minimumResultsForSearch: -1,
});
$("#modal_export_report_selector").select2({
	minimumResultsForSearch: -1
});

$("#tb_report_colums_list").on("select2:select", function(evt) {
	var element = evt.params.data.element;
	var $element = $(element);

	$element.detach();
	$(this).append($element);
	$(this).trigger("change");
});

$('#tb_report_colums_list').select2({
}).on("select2:select", function(evt) {
	var id = evt.params.data.id;
	var element = $(this).children("option[value=" + id + "]");
	moveElementToEndOfParent(element);
	$(this).trigger("change");
});
var ele = $("#tb_report_colums_list").parent().find("ul.select2-selection__rendered");
ele.sortable({
	containment: 'parent',
	update: function() {
		orderSortedValues();
	}
});

orderSortedValues = function() {
	var value = ''
	$("#tb_report_colums_list").parent().find("ul.select2-selection__rendered").children("li[title]").each(function(i, obj) {

		var element = $("#tb_report_colums_list").children('option').filter(function() {
			return $(this).html() == obj.title
		});
		moveElementToEndOfParent(element)
	});
};

moveElementToEndOfParent = function(element) {
	var parent = element.parent();
	element.detach();
	parent.append(element);
};

$(document).ready(function() {
	$('#reportTypeSelector').on('change', function() {
		$('#tb_report_colums_list').empty();
		$('#tb_report_colums_list').append(report_cols[$(this)[0].selectedIndex]);
		$('#tb_report_colums_list').val(reportTypeSelector_cols[$(this)[0].selectedIndex]).trigger('change');

		$('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
		loadTableWebTrackerResult(g_tracker_id);
	});

	$.post("web_tracker_generator_list_manager", {
			action_type: "get_web_tracker_list_for_modal"
		},
		function(data, status) {
		    if(!data['resp'])
    			$.each(data, function(index, data_row) {
                    $("#Modal_table_tracker_list tbody").append("<tr><td></td><td>" + data_row['tracker_id'] + "</td><td>" + data_row['tracker_name'] + "</td><td data-order=\"" +  UTC2Local(data_row['date']) + "\">" + data_row['date'] + `</td><td><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="Select" data-dismiss="modal" onClick="webTrackerSelected(\'` + data_row['tracker_id'] + `\');window.history.replaceState(null,null, location.pathname + '?tracker=` + data_row['tracker_id'] + `');">Select</button></td>`);
                });

			dt = $('#Modal_table_tracker_list').DataTable({
				"bDestroy": true,
				"pageLength": 5,
				'columnDefs': [{
                    "targets": [4],
                    "className": "dt-center"
                }],
				"preDrawCallback": function(settings) {
					$('#Modal_table_tracker_list tbody').hide();
				},

				"drawCallback": function() {
					$('#Modal_table_tracker_list tbody').fadeIn(500);
				}
			}); //initialize table

			dt.on('order.dt search.dt', function() {
				dt.column(0, {
					search: 'applied',
					order: 'applied'
				}).nodes().each(function(cell, i) {
					cell.innerHTML = i + 1;
				});
			}).draw();

			$('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
	}).fail(function() {
        toastr.error('', 'Error getting tracker list!');
    });
});


//--------Reports Area--------

function loadTableWebTrackerResult(g_tracker_id) {
	var web_page = $('#reportTypeSelector')[0].selectedIndex;
	try {
		tdt.destroy();
	} catch (err) {}
	$('#table_tracker_report thead').empty();
	$('#table_tracker_report tbody > tr').remove();
	//var report_colums_list = $('#tb_report_colums_list').val();
	reportTypeSelector_cols[web_page] = $('#tb_report_colums_list').val();

	var tb_headers = "<tr><th>No</th>";
	$.each(reportTypeSelector_cols[web_page], function(index, item) {
		switch (item) {
			case "cid":
				tb_headers += "<th>Client ID</th>";
				break;
			case "session_id":
				tb_headers += "<th>Session ID</th>";
				break;
			case "public_ip":
				tb_headers += "<th>Public IP</th>";
				break;
			case "internal_ip":
				tb_headers += "<th>Private IP</th>";
				break;
			case "user_agent":
				tb_headers += "<th>User Agent</th>";
				break;
			case "time":
				tb_headers += "<th>Hit Time</th>";
				break;
			case "browser":
				tb_headers += "<th>Browser</th>";
				break;
			case "platform":
				tb_headers += "<th>Platform</th>";
				break;	
			default:
				if (item.startsWith("Field"))
					tb_headers += "<th>" + item + "</th>";
		}
	});
	tb_headers += "</tr>";
	$("#table_tracker_report thead").append(tb_headers);
	tb_data = '';

	$.post("tracker_report_manager", {
			action_type: "get_table_webpage_visit_form_submission",
			page: web_page,
			tracker_id: g_tracker_id
		},
		function(data, status) {
			if (data['resp']) 
                toastr.warning('', data['resp']);
            else{          
	            $.each(data, function(index, data_row) {
	                tb_data += '<tr><td></td>';
	                $.each(reportTypeSelector_cols[web_page], function(i, column) {
	                    switch (column) {
	                        case "cid":
	                            tb_data += "<td>" + data_row['cid'] + "</td>";
	                            break;
	                        case "session_id":
								tb_data += "<td>" + data_row['session_id'] + "</td>";
								break;
							case "public_ip":
								tb_data += "<td>" + data_row['public_ip'] + "</td>";
								break;
							case "internal_ip":
								tb_data += "<td>" + data_row['internal_ip'] + "</td>";
								break;
							case "user_agent":
								tb_data += "<td>" + data_row['user_agent'] + "</td>";
								break;
							case "time":
								tb_data += "<td data-order=\"" + data_row['time'] + "\">" + UTC2Local(data_row['time']) + "</td>";
								break;
							case "browser":
								tb_data += "<td>" + data_row['browser'] + "</td>";
								break;
							case "platform":
								tb_data += "<td>" + data_row['platform'] + "</td>";
								break;
							default: var data_val = JSON.parse(data_row['form_field_data'])[column.split('-').pop()];
									if(data_val == undefined)
										data_val = "-";

									if(data_val == true)
										tb_data += "<td><center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></center></td>";
									else
									if(data_val == false)
										tb_data += "<td><center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span></center></td>";
									else
									tb_data += "<td>" + data_val + "</td>";														
	                    }
	                });
	                tb_data += '</tr>';
	            });
	            $("#table_tracker_report tbody").append(tb_data);    
            }    

			tdt = $('#table_tracker_report').DataTable({
				"bDestroy": true,
				"preDrawCallback": function(settings) {
					$('#table_tracker_report tbody').hide();
				},

				"drawCallback": function() {
					$('#table_tracker_report tbody').fadeIn(500);
				},

				dom: 'Blfrtip',
				buttons: [{
						extend: 'csvHtml5',
						filename: function() {
							if ($('#Modal_export_file_name').val() == "") return $('#disp_web_tracker_name').text() + "_" + $('#reportTypeSelector').val();
							else return $('#Modal_export_file_name').val();
						},
						exportOptions: {
							columns: ':visible:not(:first-child)' //removes 1st SL.No column
						}
					},
					{
						extend: 'excelHtml5',
						filename: function() {
							if ($('#Modal_export_file_name').val() == "") return $('#disp_web_tracker_name').text() + "_" + $('#reportTypeSelector').val();
							else return $('#Modal_export_file_name').val();
						},
						title: function() {
							return $('#disp_web_tracker_name').text()
						},
						exportOptions: {
							columns: ':visible:not(:first-child)' //removes 1st SL.No column
						}
					},
					{
						extend: 'pdfHtml5',
						filename: function() {
							if ($('#Modal_export_file_name').val() == "") return $('#disp_web_tracker_name').text() + "_" + $('#reportTypeSelector').val();
							else return $('#Modal_export_file_name').val();
						},
						title: function() {
							return $('#disp_web_tracker_name').text()
						},
						exportOptions: {
							columns: ':visible:not(:first-child)' //removes 1st SL.No column
						}
					}
				],

				initComplete: function() {
					var $buttons = $('.dt-buttons').hide();
					$('#modal_export_report_selector').on('change', function() {
						if ($('#disp_web_tracker_name').text() == "") {
							toastr.error('', 'Tracker Not Selected');
							return;
						}
						var btnClass = $(this).find(":selected")[0].id ? '.buttons-' + $(this).find(":selected")[0].id : null;
						if (btnClass) $buttons.find(btnClass).click();
					})
				}
			}, {
				"order": [
					[1, 'asc']
				]
			}); //initialize table


			tdt.on('order.tdt search.tdt', function() {
				tdt.column(0, {
					search: 'applied',
					order: 'applied'
				}).nodes().each(function(cell, i) {
					cell.innerHTML = i + 1;
				});
			}).draw();
			$('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
		});
}

function webTrackerSelected(tracker_id) {
	if(tracker_id == ''){
        toastr.warning('', 'Tracker not selected');
        return;
    }
	g_tracker_id = tracker_id;

	$.post("tracker_report_manager", {
            action_type: "get_web_tracker_from_id",
            tracker_id: tracker_id
        },
        function(data, status) {
			$('#disp_web_tracker_name').text(data['tracker_name']);
			$('#disp_tracker_start').text(data['start_time'] == ''?"Not started":UTC2Local(data['start_time']));
            if (data['active'] == 0)
                $('#disp_tracker_status').html(`<span class="badge badge-pill badge-dark" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> Stopped</span>`)
            else
                $('#disp_tracker_status').html(`<span class="badge badge-pill badge-dark" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> In-progress</span>`)
            
            var tracker_step_data = JSON.parse(data['tracker_step_data']);
           	$("#reportTypeSelector").empty();
           	$("#reportTypeSelector").append('<option value=0>Page Visit</option>');
           	$.each(tracker_step_data.web_forms.data, function(i, wf_data) {
           		$("#reportTypeSelector").append('<option value=' + (i+1) + '>Page ' + (i+1) + ' (' + wf_data.page_name + ')</option>');   
           		report_cols[i+1] = report_cols[0];	//adding common cols html
           		reportTypeSelector_cols[i+1]=reportTypeSelector_cols[0].slice(); //adding common cols name values
           		$.each(wf_data.form_fields_and_values, function(field_type, form_field) {
           			if(field_type != "FSB"){
           				report_cols[i+1] += '<option value="Field-' + form_field.idname + '" selected>Field-' + form_field.idname + '</option>';
           				reportTypeSelector_cols[i+1].push('Field-' + form_field.idname);
           			}
           		}); 
           	});

			$('#reportTypeSelector').trigger('change');
        }).fail(function() {
        toastr.error('', 'Error getting tracker data!');
    });
}