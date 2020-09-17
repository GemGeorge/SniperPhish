$("#tb_simple_tracker_result_colums_list").select2();
var dt_simple_tracker_result;
var global_tracker_id = '';

$("#modal_export_report_selector").select2({
    minimumResultsForSearch: -1
});
$("#tb_simple_tracker_result_colums_list").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);

    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_simple_tracker_result_colums_list').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});
var ele = $("#tb_simple_tracker_result_colums_list").parent().find("ul.select2-selection__rendered");
ele.sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues();
    }
});

orderSortedValues = function() {
    var value = ''
    $("#tb_simple_tracker_result_colums_list").parent().find("ul.select2-selection__rendered").children("li[title]").each(function(i, obj) {

        var element = $("#tb_simple_tracker_result_colums_list").children('option').filter(function() {
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


//--------------------------------
$(document).ready(function() {
    $.post("simple_tracker_manager", {
            action_type: "get_simple_tracker_list"
        },
        function(data, status) {
            $.each(data, function(index, data_row) {
                if(data_row['start_time']!=undefined)
                    $("#table_simple_tracker_list tbody").append("<tr><td></td><td>" + data_row['tracker_id'] + "</td><td>" + data_row['tracker_name'] + "</td><td data-order=\"" + UTC2LocalUNIX(data_row['date']) + "\">" + data_row['date'] + `</td><td><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="Select" data-dismiss="modal" onClick="simpleTrackerSelected(\'` + data_row['tracker_id'] + `\');window.history.replaceState(null,null, location.pathname + '?tracker=` + data_row['tracker_id'] + `');">Select</button></td>`);
            });


            dt_simple_tracker_result = $('#table_simple_tracker_list').DataTable({
                "bDestroy": true,
                "pageLength": 5,
                "lengthMenu": [5, 10, 20, 50, 100],
                "aaSorting": [3, 'desc'],
                "preDrawCallback": function(settings) {
                    $('#table_simple_tracker_list tbody').hide();
                },

                "drawCallback": function() {
                    $('#table_simple_tracker_list tbody').fadeIn(500);
                }
            }, {
                "order": [
                    [1, 'asc']
                ]
            }); //initialize table


            dt_simple_tracker_result.on('order.dt_simple_tracker_result search.dt_simple_tracker_result', function() {
                dt_simple_tracker_result.column(0, {
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

function simpleTrackerSelected(tracker_id) {
    global_tracker_id = tracker_id;
    $.post("simple_tracker_manager", {
            action_type: "get_simple_tracker_from_id",
            tracker_id: tracker_id
        },
        function(data, status) {
            $('#disp_simple_tracker_name').text(data['tracker_name']);
            if (data['active'] == 0)
                $('#disp_tracker_status').html(`<span class="badge badge-pill badge-dark" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> Stopped</span>`)
            else
                $('#disp_tracker_status').html(`<span class="badge badge-pill badge-dark" data-toggle="tooltip" title="Tracking status"><i class="mdi mdi-watch-vibrate"></i> In-progress</span>`)
            $('#disp_tracker_start').text(data['start_time'] == ''?"Not started":UTC2Local(data['start_time']));
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }).fail(function() {
        toastr.error('', 'Error getting tracker data!');
    });
    loadTableSimpleTrackerResult(tracker_id);
}


function loadTableSimpleTrackerResult(tracker_id) {
    try {
        tdt.destroy();
    } catch (err) {}
    $('#table_simple_tracker_report thead').empty();
    $('#table_simple_tracker_report tbody > tr').remove();
    var report_colums_list = $('#tb_simple_tracker_result_colums_list').val();

    var tb_headers = "<tr><th>No</th>";

    $.each(report_colums_list, function(index, item) {
        switch (item) {
            case "cid":
                tb_headers += "<th>Client ID</th>";
                break;
            case "public_ip":
                tb_headers += "<th>Public IP</th>";
                break;
            case "mail_client":
                tb_headers += "<th>Mail Client</th>";
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
        }
    });
    tb_headers += "</tr>";
    $("#table_simple_tracker_report thead").append(tb_headers);
    tb_data = '';

    $.post("simple_tracker_manager", {
            action_type: "get_simple_tracker_data",
            tracker_id: tracker_id
        },
        function(data, status) {
            if (data['resp']) 
                toastr.warning('', data['resp']);
            else{
                $.each(data, function(index, data_row) {
                    tb_data += '<tr><td></td>';
                    $.each(report_colums_list, function(i, column) {
                        switch (column) {
                            case "cid":
                                tb_data += "<td>" + data_row['cid'] + "</td>";
                                break;
                            case "public_ip":
                                tb_data += "<td>" + data_row['public_ip'] + "</td>";
                                break;
                            case "mail_client":
                                tb_data += "<td>" + data_row['mail_client'] + "</td>";
                                break;
                            case "platform":
                                tb_data += "<td>" + data_row['platform'] + "</td>";
                                break;
                            case "all_headers":
                                tb_data += "<td>" + data_row['all_headers'] + "</td>";
                                break;
                            case "time":
                                tb_data += "<td data-order=\"" + data_row['time'] + "\">" + UTC2Local(data_row['time']) + "</td>";
                                break;
                            case "user_agent":
                                tb_data += "<td>" + data_row['user_agent'] + "</td>";
                                break;
                        }
                    });
                    tb_data += '</tr>';
                });
                $("#table_simple_tracker_report tbody").append(tb_data);
            }            

            tdt = $('#table_simple_tracker_report').DataTable({
                "bDestroy": true,
                "preDrawCallback": function(settings) {
                    $('#table_simple_tracker_report tbody').hide();
                },

                "drawCallback": function() {
                    $('#table_simple_tracker_report tbody').fadeIn(500);
                },

                dom: 'Blfrtip',
                buttons: [{
                        extend: 'csvHtml5',
                        filename: function() {
                            if ($('#Modal_export_file_name').val() == "") return $('#disp_simple_tracker_name').text();
                            else return $('#Modal_export_file_name').val();
                        },
                        exportOptions: {
                            columns: ':visible:not(:first-child)' //removes 1st SL.No column
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        filename: function() {
                            if ($('#Modal_export_file_name').val() == "") return $('#disp_simple_tracker_name').text();
                            else return $('#Modal_export_file_name').val();
                        },
                        title: function() {
                            return $('#disp_simple_tracker_name').text();
                        },
                        exportOptions: {
                            columns: ':visible:not(:first-child)' //removes 1st SL.No column
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        filename: function() {
                            if ($('#Modal_export_file_name').val() == "") return $('#disp_simple_tracker_name').text();
                            else return $('#Modal_export_file_name').val();
                        },
                        title: function() {
                            return $('#disp_simple_tracker_name').text();
                        },
                        exportOptions: {
                            columns: ':visible:not(:first-child)' //removes 1st SL.No column
                        }
                    }
                ],

                initComplete: function() {
                    var $buttons = $('.dt-buttons').hide();
                    $('#modal_export_report_selector').on('change', function() {
                        if ($('#disp_simple_tracker_name').text() == "NA") {
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
        }).fail(function() {
        toastr.error('', 'Error getting trackers data!');
    });
}