var dt_quick_tracker_result;
var global_tracker_id = '';
var allReportColList=[];
var allReportColListSelected=[];

$(function() {
    $("#modal_export_report_selector").select2({
        minimumResultsForSearch: -1
    });
    $('#tb_quick_tracker_result_colums_list').select2().on("select2:select", function (evt) {
      var element = evt.params.data.element;
      var $element = $(element);
      $element.detach();
      $("#tb_quick_tracker_result_colums_list>optgroup").append($element);
      $(this).trigger("change");
    });
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
        url: "quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_quick_tracker_list"
        })
    }).done(function (data) {
        if(!data['error']){  // no data
            $.each(data, function(index, data_row) {
                if(data_row.start_time != undefined)
                    $("#table_quick_tracker_list tbody").append("<tr><td></td><td>" + data_row.tracker_id + "</td><td>" + data_row.tracker_name + "</td><td data-order=\"" + UTC2LocalUNIX(data_row.date) + "\">" + data_row.date + `</td><td><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="Select" data-dismiss="modal" onClick="QuickTrackerSelected(\'` + data_row.tracker_id + `\');window.history.replaceState(null,null, location.pathname + '?tracker=` + data_row.tracker_id + `');">Select</button></td>`);
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
            }
        }, {
            "order": [
                [1, 'asc']
            ]
        }); //initialize table


        dt_quick_tracker_result.on('order.dt_quick_tracker_result search.dt_quick_tracker_result', function() {
            dt_quick_tracker_result.column(0, {
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

function QuickTrackerSelected(tracker_id) {
    global_tracker_id = tracker_id;
    $.post({
        url: "quick_tracker_manager",
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
        $('#disp_tracker_start').text(data['start_time'] == ''?"Not started":UTC2Local(data['start_time']));
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
    var tb_headers = "<tr><th>No</th>";
    getAllReportColListSelected();

    $.each(allReportColListSelected, function(index, item) {
        switch (item) {
            case "cid":
                tb_headers += "<th>Client ID</th>";
                break;
            case "public_ip":
                tb_headers += "<th>Public IP</th>";
                break;
            case "mail_client":
                tb_headers += "<th>Mail Client/Browser</th>";
                break;
            case "platform":
                tb_headers += "<th>Platform</th>";
                break;
            case "all_headers":
                tb_headers += "<th>Req Headers</th>";
                break;
            case "time":
                tb_headers += "<th>Hit Time</th>";
                break;
            case "user_agent":
                tb_headers += "<th>User Agent</th>";
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
        }
    });
    tb_headers += "</tr>";
    $("#table_quick_tracker_report thead").append(tb_headers);
    tb_data = '';

    $.post({
        url: "quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_quick_tracker_data",
            tracker_id: tracker_id
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
                        case "public_ip":
                            tb_data += "<td>" + data_row.public_ip + "</td>";
                            break;
                        case "mail_client":
                            tb_data += "<td>" + data_row.mail_client + "</td>";
                            break;
                        case "platform":
                            tb_data += "<td>" + data_row.platform + "</td>";
                            break;
                        case "all_headers":
                            tb_data += "<td>" + data_row.all_headers + "</td>";
                            break;
                        case "time":
                            tb_data += "<td data-order=\"" + data_row.time + "\">" + UTC2Local(data_row.time) + "</td>";
                            break;
                        case "user_agent":
                            tb_data += "<td>" + data_row.user_agent + "</td>";
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
                    }
                });
                tb_data += '</tr>';
            });
            $("#table_quick_tracker_report tbody").append(tb_data);
        }
        
        tdt = $('#table_quick_tracker_report').DataTable({
            "bDestroy": true,
            "preDrawCallback": function(settings) {
                $('#table_quick_tracker_report tbody').hide();
            },

            "drawCallback": function() {
                $('#table_quick_tracker_report tbody').fadeIn(500);
            },

            dom: 'B<"bspace"l>frtip',
            buttons: [{
                    extend: 'csvHtml5',
                    filename: function() {
                        if ($('#Modal_export_file_name').val() == "") return $('#disp_quick_tracker_name').text();
                        else return $('#Modal_export_file_name').val();
                    },
                    exportOptions: {
                        columns: ':visible:not(:first-child)' //removes 1st SL.No column
                    }
                },
                {
                    extend: 'excelHtml5',
                    filename: function() {
                        if ($('#Modal_export_file_name').val() == "") return $('#disp_quick_tracker_name').text();
                        else return $('#Modal_export_file_name').val();
                    },
                    title: function() {
                        return $('#disp_quick_tracker_name').text();
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
                        if ($('#Modal_export_file_name').val() == "") return $('#disp_quick_tracker_name').text();
                        else return $('#Modal_export_file_name').val();
                    },
                    title: function() {
                        return $('#disp_quick_tracker_name').text();
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