var dt_quick_tracker_list;
var g_modalValue = '';
loadTableQuickTrackerList();

function modalOpenQuickTracker(flag_replace_value, tracker_id, tracker_name="") {
    g_modalValue=tracker_id==""?getRandomId():tracker_id;

    if (flag_replace_value == true){
        $("#modal_new_quick_tracker").find(".modal-title").text("Change Quick Tracker Name");
        $("#modal_quick_tracker_name").val(tracker_name);
    }
    else{
        $("#modal_new_quick_tracker").find(".modal-title").text("Create New Quick Tracker");
        $("#modal_quick_tracker_name").val('');
    }

    $('#quick_tracker_html').text('<img src="' + location.origin + '/qt?tid=' + g_modalValue + '&cid=<Client ID>"></img>');
    Prism.highlightAll();
    $('#modal_new_quick_tracker').modal('toggle');
}

function addQuickTracker(e) {
    var quick_tracker_name = $("#modal_quick_tracker_name").val();
    if (RegTest(quick_tracker_name, 'COMMON') == false) {
        $('#modal_quick_tracker_name').addClass('is-invalid');
        return;
    }

    enableDisableMe(e);
    $.post({
        url: "quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "save_quick_tracker",
            tracker_id: g_modalValue,
            quick_tracker_name: quick_tracker_name,
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            $('#modal_new_quick_tracker').modal('toggle');
            toastr.success('', 'Saved successfully!');
            loadTableQuickTrackerList();
            getRandomId();
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function promptDeleteQuickTracker(id, tracker_name) {
    g_modalValue = id;    
    $('#modal_prompts').modal('toggle');
    $("#modal_prompts_body").text("This will delete Tracker \"" + tracker_name + "\" and all it's data. This action can't be undone!");
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="deleteQuickTrackerAction()">Confirm</button>`);
}

function deleteQuickTrackerAction() {
    $.post({
        url: "quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_quick_tracker",
            tracker_id: g_modalValue
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_prompts').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            loadTableQuickTrackerList();
        }
        else
            toastr.error('', response.error);
    }); 
}

//---------------
function promptQuickTrackerDataDeletion(id, tracker_name) { // delete user data only
    g_modalValue = id;
    $('#modal_prompts').modal('toggle');
    $("#modal_prompts_body").text("This will delete data captured by Tracker \"" + tracker_name + "\", but will not delete Tracker. This action can't be undone!");
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="quickTrackerDataDeletion()">Confirm</button>`);
}

function quickTrackerDataDeletion() { // delete user data only
    $.post({
        url: "quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_quick_tracker_data",
            tracker_id: g_modalValue
        })
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Tracker data deletion success!');
            $('#modal_prompts').modal('toggle');
        }
        else
            toastr.error("", "Error: Data deletion failed!");
    }); 
}

//--------------
$(document).ready(function() {
    $(document).on("click", "button[name='quick_tracker_status_button']", function() {
        $('[data-toggle="tooltip"]').tooltip("hide");
        $.post({
            url: "quick_tracker_manager",
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({ 
                action_type: "pause_stop_quick_tracker_tracking",
                tracker_id: $(this).data('tracker_id'),
                active: $(this).data('status_value')
             })
        }).done(function (response) {
            if(response.result == "success"){
                loadTableQuickTrackerList();
            }
            else
                toastr.error("", response.error);
        }); 
    });
});


function loadTableQuickTrackerList() {
    try {
        dt_quick_tracker_list.destroy();
    } catch (err) {}
    $('#table_quick_tracker_list tbody > tr').remove();

    $.post({
        url: "quick_tracker_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_quick_tracker_list"
         })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(key, value) {
                var action_items = `<div class="d-flex no-block"><button type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Report" onClick="document.location='QuickTrackerReport?tracker=` + value.tracker_id + `'"><i class="mdi mdi-book-open"></i></button>`;

                if (value.active == 1)
                    action_items += `<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Pause/Stop Tracking" data-tracker_id="` + value.tracker_id + `" data-status_value="0" name="quick_tracker_status_button"><i class="mdi mdi-stop"></i></button>`;
                else
                    action_items += `<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Start/Resume Tracking" data-tracker_id="` + value.tracker_id + `" data-status_value="1" name="quick_tracker_status_button"><i class="mdi mdi-play"></i></button>`;

                action_items += `<div class="btn-group ml-sm-1 btn-group-table">
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="modalOpenQuickTracker(true,'` + value.tracker_id + `','` + value.tracker_name + `')">View/Edit</a>
                            <a class="dropdown-item" href="#" onClick="promptDeleteQuickTracker('` + value.tracker_id + `','` + value.tracker_name + `')">Delete Tracker</a>
                            <a class="dropdown-item" href="#" onClick="promptQuickTrackerDataDeletion('` + value.tracker_id + `','` + value.tracker_name + `')">Delete Data</a>
                        </div></div></div>`;
                
                $("#table_quick_tracker_list tbody").append("<tr><td></td><td>" + value.tracker_id + "</td><td>" + value.tracker_name + "</td><td data-order=\"" + UTC2LocalUNIX(value.date) + "\">" + UTC2Local(value.date) + "</td><td data-order=\"" + UTC2LocalUNIX(value.start_time) + "\">" + UTC2Local(value.start_time) + "</td><td data-order=\"" + UTC2LocalUNIX(value.stop_time) + "\">" + UTC2Local(value.stop_time) + "</td><td>" + action_items + "</td></tr>");
            });
        }
        
        dt_quick_tracker_list = $('#table_quick_tracker_list').DataTable({
            "bDestroy": true,
            "aaSorting": [3, 'desc'],
            'columnDefs': [{
                "targets": 6,
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_quick_tracker_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_quick_tracker_list tbody').fadeIn(500);
            }
        }); //initialize table

        dt_quick_tracker_list.on('order.dt_quick_tracker_list search.dt_quick_tracker_list', function() {
            dt_quick_tracker_list.column(0, {
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


var t1 = new ClipboardJS('#btn_copy_quick_tracker', {
    target: function(trigger) {
        return document.querySelector('#quick_tracker_html');
    }
});

t1.on('success', function(event) {
    event.clearSelection();
    event.trigger.textContent = 'Copied';
    window.setTimeout(function() {
        event.trigger.textContent = 'Copy';
    }, 2000);

});