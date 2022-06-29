var g_tracker_id = "";
var tdt;
var report_cols_html=[];
var dic_all_col={rid:'RID', session_id:'Session ID', public_ip:'Public IP', user_agent:'User Agent', time:'Hit Time', browser:'Browser', platform: 'Platform', screen_res:'Screen Res', device_type:'Device Type', country:'Country', city:'City', zip:'Zip', isp:'ISP', timezone:'Timezone', coordinates:'Coordinates'};
selector_comm_cols_html = `<optgroup label="User Info">
					<option value="rid" selected>Client ID</option>
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
	$(this).find('optgroup').append($element);
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
        url: "manager/web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_web_tracker_list_for_modal"
        })
    }).done(function (data) {
        if(!data['error']){  // no data
            $.each(data, function(index, data_row) {
                $("#Modal_table_tracker_list tbody").append("<tr><td></td><td>" + data_row['tracker_id'] + "</td><td>" + data_row['tracker_name'] + "</td><td data-order=\"" +  data_row['date'] + "\">" + data_row['date'] + `</td><td><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="Select" data-dismiss="modal" onClick="webTrackerSelected(\'` + data_row['tracker_id'] + `\');window.history.replaceState(null,null, location.pathname + '?tracker=` + data_row['tracker_id'] + `');">Select</button></td>`);
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
				$('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
			},

			"initComplete": function() {
	            $('label>select').select2({minimumResultsForSearch: -1, });
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
    }); 
});

function exportReport(){
	$('#Modal_export_file_name').val(g_tracker_id + '_' + $('#disp_web_tracker_name').text() + '_' + $('#reportTypeSelector')[0].selectedIndex);  
    $('#ModalExport').modal('toggle');
}

function exportReportAction(e) {
    if(tdt.rows().count() > 0){
        var file_name = $('#Modal_export_file_name').val().trim();
        var file_format = $('#modal_export_report_selector').val();
        var web_page = $('#reportTypeSelector')[0].selectedIndex;
        getAllReportColListSelected();

        if(file_format == 'csv')
        	content_type='text/csv';
        else
    	if(file_format == 'pdf')
    		content_type='application/pdf';
    	else
        if(file_format == 'html')
            content_type='text/html';

    	var xhr = new XMLHttpRequest();
	    xhr.open('POST', 'manager/tracker_report_manager', true);
	    xhr.responseType = 'arraybuffer';
	    
	    enableDisableMe(e);
		xhr.send(JSON.stringify({ 
			action_type: "download_report",
			tracker_id: g_tracker_id,
			selected_col: allReportColListSelected,
			dic_all_col: dic_all_col,
			page: web_page,
			file_name: file_name,
			file_format: file_format
		}));

		xhr.onload = function() {
			if (this.status == 200) {
				var link=document.createElement('a');
				link.href = window.URL.createObjectURL(new Blob([this.response],{ type: content_type}));
				link.download=file_name + '.' + file_format;
				link.click();
				$('#ModalExport').modal('toggle');
	       }
	       enableDisableMe(e);
	    };
	}
	else
		toastr.error('', 'Table is empty!');
}

//--------Reports Area--------
function loadTableWebTrackerResult(g_tracker_id) {
	var web_page = $('#reportTypeSelector')[0].selectedIndex;
	try {
		tdt.destroy();
	} catch (err) {}
	$('#table_tracker_report thead').empty();
	$('#table_tracker_report tbody > tr').remove();

	getAllReportColListSelected();
	var arr_tb_heading=[];	
	arr_tb_heading.push({ data: 'sn', title: "SN" });

	$.each(allReportColListSelected, function(index, item) {
		if (item.startsWith("Field-"))
			arr_tb_heading.push({ data: item, title : item});
		else
			arr_tb_heading.push({ data: item, title : dic_all_col[item]});
	});
	
    tdt = $('#table_tracker_report').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
			url:'manager/tracker_report_manager',
			type: "POST",
			contentType: "application/json; charset=utf-8",
			data: function (d) {   //request parameters here
                    d.action_type="get_table_webpage_visit_form_submission";
                    d.page=web_page;
                    d.tracker_id=g_tracker_id;
                    d.selected_col=allReportColListSelected;
                    return JSON.stringify(d);
                },
            dataSrc: function ( resp ){
                for (var i=0; i<resp.data.length; i++)
                    resp.data[i]['sn'] = i+1;
                return resp.data
            }
        },
        'columns': arr_tb_heading,
        'pageLength': 20,
        'lengthMenu': [[20, 50, 100, 500, 1000, -1], [20, 50, 100, 500, 1000, "All"]],
        'aoColumnDefs': [{'bSortable': false, 'aTargets': [0]}],
        drawCallback:function(){
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        },

        "initComplete": function() {
            $('label>select').select2({minimumResultsForSearch: -1, });
        }
	});
}

function webTrackerSelected(tracker_id) {
	if(tracker_id == ''){
        toastr.warning('', 'Tracker not selected');
        return;
    }
	g_tracker_id = tracker_id;

	$.post({
        url: "manager/tracker_report_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_web_tracker_from_id",
            tracker_id: tracker_id
        })
    }).done(function (data) {
    	report_cols_html=[];
        $('#disp_web_tracker_name').text(data.tracker_name);
		$('#disp_tracker_start').text(data.start_time);
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