var dt_simple_tracker_list;
var g_modalValue = '';

function modalOpenSimpleTracker(flag_replace_value, tracker_id) {
    g_modalValue=tracker_id==""?getRandomId():tracker_id;

    if (flag_replace_value == true)
        $("#modal_new_simple_tracker div div div .modal-title").text("Change Simple Tracker Name");
    else
        $("#modal_new_simple_tracker div div div .modal-title").text("Create New Simple Tracker");

    $('#simple_tracker_html').text('<img src="' + location.origin + '/st?tid=' + g_modalValue + '&cid=<client ID>"></img>');
    Prism.highlightAll();
    $('#modal_new_simple_tracker').modal('toggle');
}

function addSimpleTracker(e) {
    var simple_tracker_name = $("#modal_simple_tracker_name").val();
    if (simple_tracker_name == "") {
        $('#modal_simple_tracker_name').addClass('is-invalid');
        return;
    }

    enableDisableMe(e);
    $.post("simple_tracker_manager", {
            action_type: "save_simple_tracker",
            tracker_id: g_modalValue,
            simple_tracker_name: simple_tracker_name,

        },
        function(data, status) {
            if (data == "success") {
                $('#modal_new_simple_tracker').modal('toggle');
                toastr.success('', 'Saved successfully!');
                loadTableSimpleTrackerList();
                getRandomId();
            } else {
                toastr.error('', 'Error saving data!');
            }
            enableDisableMe(e);
        });
}


$(document).ready(function() {
    loadTableSimpleTrackerList();
});


function deleteSimpleTracker(tracker_id) {
    g_modalValue = tracker_id;
    $('#modal_simple_tracker_delete').modal('toggle');
}

function deleteSimpleTrackerAction() {
    $.post("simple_tracker_manager", {
            action_type: "delete_simple_tracker",
            tracker_id: g_modalValue
        },
        function(data, status) {
            if (data == "deleted") {
                $('#modal_simple_tracker_delete').modal('toggle');
                toastr.success('', 'Deleted successfully!');
                loadTableSimpleTrackerList();
            } else {
                toastr.error('', 'Error deleting data!');
            }
        });
}

$(document).ready(function() {
    $(document).on("click", "button[name='simple_tracker_status_button']", function() {
        $.post("simple_tracker_manager", {
                action_type: "pause_stop_simple_tracker_tracking",
                tracker_id: $(this).data('tracker_id'),
                active: $(this).data('status_value')
            },
            function(data, status) {
                if (data == "success") {
                    loadTableSimpleTrackerList();
                }
            });
    });
});


function loadTableSimpleTrackerList() {
    try {
        dt_simple_tracker_list.destroy();
    } catch (err) {}
    $('#table_simple_tracker_list tbody > tr').remove();

    $.post("simple_tracker_manager", {
            action_type: "get_simple_tracker_list"
        },
        function(data, status) {
            if(!data['resp']){  // no data response
                $.each(data, function(key, value) {
                    if (value['active'] == 1)
                        var resume_button = `<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Pause/Stop Tracking" data-tracker_id="` + value['tracker_id'] + `" data-status_value="0" name="simple_tracker_status_button"><i class="mdi mdi-stop"></i></button>`;
                    else
                        var resume_button = `<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Start/Resume Tracking" data-tracker_id="` + value['tracker_id'] + `" data-status_value="1" name="simple_tracker_status_button"><i class="mdi mdi-play"></i></button>`;
                    var action_items = `<div><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="View/Edit" onClick="modalOpenSimpleTracker(true,'` + value['tracker_id'] + `')"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Delete" onClick="deleteSimpleTracker('` + value['tracker_id'] + `')"><i class="mdi mdi-delete-variant"></i></button><button type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Report" onClick="document.location='SimpleTrackerReport?tracker=` + value['tracker_id'] + `'"><i class="mdi mdi-book-open"></i></button>` + resume_button + `</div>`;
                    
                    $("#table_simple_tracker_list tbody").append("<tr><td></td><td>" + value['tracker_id'] + "</td><td>" + value['tracker_name'] + "</td><td data-order=\"" + UTC2LocalUNIX(value['date']) + "\">" + UTC2Local(value['date']) + "</td><td data-order=\"" + UTC2LocalUNIX(value['start_time']) + "\">" + UTC2Local(value['start_time']) + "</td><td data-order=\"" + UTC2LocalUNIX(value['stop_time']) + "\">" + UTC2Local(value['stop_time']) + "</td><td>" + action_items + "</td></tr>");
                });
            }
            
            dt_simple_tracker_list = $('#table_simple_tracker_list').DataTable({
                "bDestroy": true,
                "aaSorting": [3, 'desc'],
                'columnDefs': [{
                    "targets": 6,
                    "className": "dt-center"
                }],
                "preDrawCallback": function(settings) {
                    $('#table_simple_tracker_list tbody').hide();
                },

                "drawCallback": function() {
                    $('#table_simple_tracker_list tbody').fadeIn(500);
                }
            }); //initialize table


            dt_simple_tracker_list.on('order.dt_simple_tracker_list search.dt_simple_tracker_list', function() {
                dt_simple_tracker_list.column(0, {
                    search: 'applied',
                    order: 'applied'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }).fail(function() {
        toastr.error('', 'Error getting trackers list!');
    });
}


var t1 = new ClipboardJS('#btn_copy_simple_tracker', {
    target: function(trigger) {
        return document.querySelector('#simple_tracker_html');
    }
});

t1.on('success', function(event) {
    event.clearSelection();
    event.trigger.textContent = 'Copied';
    window.setTimeout(function() {
        event.trigger.textContent = 'Copy';
    }, 2000);

});