var dt_quick_tracker_result;
var g_tracker_id = "";
var allReportColList= allReportColListSelected=[];
var dic_all_col={rid:'RID', public_ip:'Public IP',mail_client:'Mail Client/Browser',platform:'Platform',device_type:'Device Type',all_headers:'HTTP Headers',user_agent:'User Agent', time:'Hit Time', country:'Country', city:'City', zip:'Zip', isp:'ISP', timezone:'Timezone', coordinates:'Coordinates'};

$("#modal_export_report_selector").select2({
    minimumResultsForSearch: -1
}); 

$('#tb_quick_tracker_result_colums_list').select2().on("select2:select", function (evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).find('optgroup').append($element);
    $(this).trigger("change");
});

$.each($("#tb_quick_tracker_result_colums_list").find("option"), function () {
    allReportColList[$(this).text()] = $(this).val();
});

$("#tb_quick_tracker_result_colums_list").parent().find("ul.select2-selection__rendered").sortable({
    containment: 'parent',
    update: function() {
        getAllReportColListSelected()
    }
});

function getAllReportColListSelected(){
    allReportColListSelected=[];
    $.each($("#tb_quick_tracker_result_colums_list").parent().find("ul.select2-selection__rendered").children("li[title]"), function () {
        allReportColListSelected.push(allReportColList[this.title]);
    });
}
//--------------------------------
$(document).ready(function() {
    $.post({
        url: "manager/quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_quick_tracker_list"
        })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(index, data_row) {
                if(data_row.start_time != undefined)
                    $("#table_quick_tracker_list tbody").append("<tr><td></td><td>" + data_row.tracker_id + "</td><td>" + data_row.tracker_name + "</td><td data-order=\"" + getTimestamp(data_row.date) + "\">" + data_row.date + `</td><td><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="Select" data-dismiss="modal" onClick="QuickTrackerSelected(\'` + data_row.tracker_id + `\');window.history.replaceState(null,null, location.pathname + '?tracker=` + data_row.tracker_id + `');">Select</button></td>`);
            });
        }
        
        dt_quick_tracker_result = $('#table_quick_tracker_list').DataTable({
            "bDestroy": true,
            "pageLength": 5,
            "lengthMenu": [5, 10, 20, 50, 100],
            "aaSorting": [3, 'desc'],
            "preDrawCallback": function(settings) {
                $('#table_quick_tracker_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_quick_tracker_list tbody').fadeIn(500);
                $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            },

            "initComplete": function() {
                $('label>select').select2({minimumResultsForSearch: -1, });
            }
        }, {
            "order": [[1, 'asc']]
        }); //initialize table

        dt_quick_tracker_result.on('order.dt_quick_tracker_result search.dt_quick_tracker_result', function() {
            dt_quick_tracker_result.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    }); 
});

function QuickTrackerSelected(tracker_id) {
    g_tracker_id = tracker_id;
    $.post({
        url: "manager/quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_quick_tracker_from_id",
            tracker_id: tracker_id
        })
    }).done(function (data) {
        $('#disp_quick_tracker_name').text(data.tracker_name);
        $('#Modal_export_file_name').val(data.tracker_name);
        if (data['active'] == 0)
            $('#disp_tracker_status').html(`<span class="badge badge-pill badge-success" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> Stopped</span>`)
        else
            $('#disp_tracker_status').html(`<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> In-progress</span>`)
        $('#disp_tracker_start').text(data.start_time == ''?"Not started":data.start_time);
        $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
    }); 
    loadTableQuickTrackerResult(tracker_id)
}

function loadTableQuickTrackerResult(tracker_id) {
    try {
        tdt.destroy();
    } catch (err) {}
    $('#table_quick_tracker_report thead').empty();
    $('#table_quick_tracker_report tbody > tr').remove();
    getAllReportColListSelected();

    var arr_tb_heading=[];  
    arr_tb_heading.push({ data: 'sn', title: "SN" });
    $.each(allReportColListSelected, function(index, item) {
        arr_tb_heading.push({ data: item, title : dic_all_col[item]});
    });

    tdt = $('#table_quick_tracker_report').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            url:'manager/quick_tracker_manager',
            type: "POST",
            contentType: "application/json; charset=utf-8",
            data: function (d) {   //request parameters here
                    d.action_type = 'get_quick_tracker_data';
                    d.tracker_id = tracker_id;                    
                    d.selected_col = allReportColListSelected;
                    return JSON.stringify(d);
                },
            dataSrc: function ( resp ){
                for (var i=0; i<resp.data.length; i++){
                    resp.data[i]['sn'] = i+1;
                }

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

function exportReportAction(e) {
    if(tdt.rows().count() > 0){
        var file_name = $('#Modal_export_file_name').val().trim();
        var file_format = $('#modal_export_report_selector').val();
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
        xhr.open('POST', 'manager/quick_tracker_manager', true);
        xhr.responseType = 'arraybuffer';
        
        enableDisableMe(e);
        xhr.send(JSON.stringify({ 
            action_type: "download_report",
            tracker_id: g_tracker_id,
            selected_col: allReportColListSelected,
            dic_all_col: dic_all_col,
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