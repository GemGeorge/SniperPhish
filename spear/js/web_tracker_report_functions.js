var g_tracker_id = "";
var tdt;
var allReportColList=[];
var report_cols_html=[];
selector_comm_cols_html = `<optgroup label="User Info">
					<option value="cid" selected>Client ID</option>
					<option value="session_id">Session ID</option>
					<option value="public_ip" selected>Public IP</option>
					<option value="time" selected>Hit Time</option>
					<option value="browser" selected>Browser</option>
					<option value="platform" selected>Platform</option>
					<option value="screen_res" selected>Screen Res</option>
					<option value="device_type">Device Type</option>
					<option value="user_agent">User Agent</option>
				</optgroup>
				<optgroup label="User IP Info">
					<option value="country" selected>Country</option>
					<option value="city">City</option>
					<option value="zip">Zip</option>
					<option value="isp">ISP</option>
					<option value="timezone">Timezone</option>
					<option value="coordinates">Coordinates</option>
				</optgroup>`; //common cols

$("#reportTypeSelector").select2({
	minimumResultsForSearch: -1,
});
$("#modal_export_report_selector").select2({
	minimumResultsForSearch: -1
});
$('#tb_report_colums_list').select2().on("select2:select", function (evt) {
  var element = evt.params.data.element;
  var $element = $(element);
  $element.detach();
  $("#tb_report_colums_list>optgroup").append($element);
  $(this).trigger("change");
});

$("#tb_report_colums_list").parent().find("ul.select2-selection__rendered").sortable({
	containment: 'parent',
	update: function() {
		getAllReportColListSelected();
	}
});

function getAllReportColListSelected(){
	var allReportColList=[];
    allReportColListSelected=[];

    $.each($("#tb_report_colums_list").find("option"), function () {
	    allReportColList[$(this).text()] = $(this).val();
	});

    $.each($("#tb_report_colums_list").parent().find("ul.select2-selection__rendered").children("li[title]"), function () {
        allReportColListSelected.push(allReportColList[this.title]);
    });
}

$('#reportTypeSelector').on('change', function() {
	$('#tb_report_colums_list').empty();
	$('#tb_report_colums_list').append(report_cols_html[$(this)[0].selectedIndex]);

	$('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
	$('#tb_report_colums_list').trigger("change");
	loadTableWebTrackerResult(g_tracker_id);
});

$(document).ready(function() {
	$.post({
        url: "web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_web_tracker_list_for_modal"
        })
    }).done(function (data) {
        if(!data['error']){  // no data
            $.each(data, function(index, data_row) {
                $("#Modal_table_tracker_list tbody").append("<tr><td></td><td>" + data_row['tracker_id'] + "</td><td>" + data_row['tracker_name'] + "</td><td data-order=\"" +  UTC2Local(data_row['date']) + "\">" + data_row['date'] + `</td><td><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="Select" data-dismiss="modal" onClick="webTrackerSelected(\'` + data_row['tracker_id'] + `\');window.history.replaceState(null,null, location.pathname + '?tracker=` + data_row['tracker_id'] + `');">Select</button></td>`);
            });
        }
        
        dt = $('#Modal_table_tracker_list').DataTable({
			"bDestroy": true,
			"pageLength": 5,
			"lengthMenu": [5, 10, 20, 50, 100],
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
		$("label>select").select2({
            minimumResultsForSearch: -1,
        });
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

	getAllReportColListSelected();

	var tb_headers = "<tr><th>No</th>";
	$.each(allReportColListSelected, function(index, item) {
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
			case "screen_res":
				tb_headers += "<th>Screen Res</th>";
				break;	
			case "device_type":
				tb_headers += "<th>Device Type</th>";
				break;	
			case "country":
                tb_headers += "<th>Country</th>";
                break;
            case "city":
                tb_headers += "<th>City</th>";
                break;
            case "zip":
                tb_headers += "<th>Zip</th>";
                break;
            case "isp":
                tb_headers += "<th>ISP</th>";
                break;
            case "timezone":
                tb_headers += "<th>Timezone</th>";
                break;
            case "coordinates":
                tb_headers += "<th>Coordinates</th>";
                break;
			default:
				if (item.startsWith("Field"))
					tb_headers += "<th>" + item + "</th>";
		}
	});
	tb_headers += "</tr>";
	$("#table_tracker_report thead").append(tb_headers);
	tb_data = '';

	$.post({
        url: "tracker_report_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_table_webpage_visit_form_submission",
			page: web_page,
			tracker_id: g_tracker_id
        })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(index, data_row) {
                tb_data += '<tr><td></td>';
                $.each(allReportColListSelected, function(i, column) {
                    switch (column) {
                        case "cid":
                            tb_data += "<td>" + data_row.cid + "</td>";
                            break;
                        case "session_id":
							tb_data += "<td>" + data_row.session_id + "</td>";
							break;
						case "public_ip":
							tb_data += "<td>" + data_row.public_ip + "</td>";
							break;
						case "user_agent":
							tb_data += "<td>" + data_row.user_agent + "</td>";
							break;
						case "time":
							tb_data += "<td data-order=\"" + data_row.time + "\">" + UTC2Local(data_row.time) + "</td>";
							break;
						case "browser":
							tb_data += "<td>" + data_row.browser + "</td>";
							break;
						case "platform":
							tb_data += "<td>" + data_row.platform + "</td>";
							break;
						case "screen_res":
							tb_data += "<td>" + data_row.screen_res + "</td>";
							break;
						case "device_type":
							tb_data += "<td>" + data_row.device_type + "</td>";
							break;
						case "country":
						case "city":
						case "zip":
						case "isp":
						case "timezone":
						case "coordinates":
							if(data_row.ip_info[column] == null)
			                    tb_data += "<td>-</td>";
			                else
			                    tb_data += "<td>" + data_row.ip_info[column] + "</td>";
			                break;
						default: var data_val = data_row.form_field_data[column.split('-').pop()];
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

			dom: 'B<"bspace"l>frtip',
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
						return $('#disp_web_tracker_name').text();
					},
					exportOptions: {
						columns: ':visible:not(:first-child)' //removes 1st SL.No column
					}
				},
				{
					extend: 'pdfHtml5',
					orientation: 'landscape',
                	pageSize: 'LEGAL',
					filename: function() {
						if ($('#Modal_export_file_name').val() == "") return $('#disp_web_tracker_name').text() + "_" + $('#reportTypeSelector').val();
						else return $('#Modal_export_file_name').val();
					},
					title: function() {
						return $('#disp_web_tracker_name').text();
					},
					exportOptions: {
						columns: ':visible:not(:first-child)' //removes 1st SL.No column
					}
				}
			],

			initComplete: function() {
				var $buttons = $('.dt-buttons').hide();
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
		$("label>select").select2({
            minimumResultsForSearch: -1,
        });
    }); 
}

function webTrackerSelected(tracker_id) {
	if(tracker_id == ''){
        toastr.warning('', 'Tracker not selected');
        return;
    }
	g_tracker_id = tracker_id;

	$.post({
        url: "tracker_report_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_web_tracker_from_id",
            tracker_id: tracker_id
        })
    }).done(function (data) {
    	report_cols_html=[];
        $('#disp_web_tracker_name').text(data.tracker_name);
        $('#Modal_export_file_name').val(data.tracker_name);
		$('#disp_tracker_start').text(data.start_time == ''?"Not started":UTC2Local(data.start_time));
        if (data['active'] == 0)
            $('#disp_tracker_status').html(`<span class="badge badge-pill badge-success" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> Stopped</span>`)
        else
            $('#disp_tracker_status').html(`<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> In-progress</span>`)
        
        var tracker_step_data = data.tracker_step_data;
       	$("#reportTypeSelector").empty();
       	$("#reportTypeSelector").append('<option value=0>Page Visit</option>');
       	report_cols_html[0] = selector_comm_cols_html;
       	$.each(tracker_step_data.web_forms.data, function(i, wf_data) {
       		$("#reportTypeSelector").append('<option value=' + (i+1) + '>Page ' + (i+1) + ' (' + wf_data.page_name + ')</option>');   
       		report_cols_html[i+1] = selector_comm_cols_html;	//adding common cols html
       		if(Object.keys(wf_data.form_fields_and_values).length > 0){
       			report_cols_html[i+1] += '<optgroup label="Form Input Fields">';
	       		$.each(wf_data.form_fields_and_values, function(field_type, form_field) {
	       			if(field_type != "FSB")
	       				report_cols_html[i+1] += '<option value="Field-' + form_field.idname + '" selected>Field-' + form_field.idname + '</option>';
	       		}); 
	       		report_cols_html[i+1] += '</optgroup>';
	       	}
       	});

       	$('#tb_report_colums_list').empty();
		$('#tb_report_colums_list').append(report_cols_html[0]);
		$('#tb_report_colums_list').trigger("change");
		loadTableWebTrackerResult(tracker_id);		 
	}); 
}