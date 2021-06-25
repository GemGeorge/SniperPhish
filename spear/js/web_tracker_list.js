var dt_web_tracker_list;

loadTableWebTrackerList();

//---------------
function webTrackerActDeactAction(tracker_id, action_value){
    action_value == 0 ? new_action_value=1 : new_action_value = 0;
    $.post({
        url: "web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "pause_stop_web_tracker_tracking",
            tracker_id: tracker_id,
            active: new_action_value
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_prompts').modal('toggle');
            loadTableWebTrackerList();
            if (new_action_value == 1)
                toastr.success('', 'Success. Tracking started!');
            else
                toastr.success('', 'Success. Tracking stopped!');
        }
        else
            toastr.error("", response.error);
    }); 
}

function promptWebTrackerActDeact(id, tracker_name, action_value) {
    $('#modal_prompts').modal('toggle');
    if (action_value == 0)
        $("#modal_prompts_body").text("Start/Resume tracker \"" + tracker_name + "\"?");
    if (action_value == 1)
        $("#modal_prompts_body").text("Pause/Stop tracker \"" + tracker_name + "\"?");
    
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="webTrackerActDeactAction('` + id + `','` + action_value + `')">Confirm</button>`);
}
//---------------
//---------------
function promptWebTrackerDeletion(id, tracker_name) {
    g_modalValue = id;
    $('#modal_prompts').modal('toggle');
    $("#modal_prompts_body").text("This will delete Tracker \"" + tracker_name + "\" and all it's data. This action can't be undone!");
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="webTrackerDeletionAction()">Confirm</button>`);
}

function webTrackerDeletionAction() {
    $.post({
        url: "web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_web_tracker",
            tracker_id: g_modalValue
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_prompts').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            loadTableWebTrackerList();
        }
        else
            toastr.error('', response.error);
    }); 
}
//---------------
//---------------
function promptWebTrackerCopy(id) {
    globalModalValue = id;
    $('#modal_copy_web_tracker').modal('toggle');
}

function webTrackerCopyAction() {
    var modal_web_tracker_name = $('#modal_web_tracker_name').val();

    if (RegTest(modal_web_tracker_name, 'COMMON') == false) {
        $("#modal_mail_campaign_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#modal_web_tracker_name").removeClass("is-invalid");

    $.post({
        url: "web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "make_copy_web_tracker",
            tracker_id: globalModalValue,
            new_tracker_id: getRandomId(),
            new_tracker_name: modal_web_tracker_name
        })
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Copy success!');
            $('#modal_copy_web_tracker').modal('toggle');
            loadTableWebTrackerList();
        }
        else
            toastr.error("", response.error);
    }); 
}
//---------------
//---------------
function promptWebTrackerDataDeletion(id, tracker_name) { // delete user data only
    g_modalValue = id;
    $('#modal_prompts').modal('toggle');
    $("#modal_prompts_body").text("This will delete data captured by Tracker \"" + tracker_name + "\", but will not delete Tracker. This action can't be undone!");
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="webTrackerDataDeletionAction()">Confirm</button>`);
}

function webTrackerDataDeletionAction() { // delete user data only
    $.post({
        url: "web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_web_tracker_data",
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

function trackerLinkCopy(tracker_id){
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(`<script src="` + location.origin + `/mod?tlink=` + tracker_id + `"></script>`).select();
    document.execCommand("copy");
    $temp.remove();

    toastr.success('', 'Copy success!');
}
//---------------
//---------------
function loadTableWebTrackerList() {  
    try {
        dt_web_tracker_list.destroy();
    } catch (err) {}
    $('#table_web_tracker_list tbody > tr').remove();

    $.post({
        url: "web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_web_tracker_list"
        })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(key, value) {
                var action_items_web_tracker_table = `<div class="d-flex no-block"><button type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Report" onclick="document.location='TrackerReport?tracker=` + value.tracker_id + `'"><i class="mdi mdi-book-open"></i></button>`;

                if (value.active == 0)
                    action_items_web_tracker_table += `<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Start/Resume Tracking" onClick="promptWebTrackerActDeact('` + value.tracker_id + `','` + value.tracker_name + `','` + value.active + `')"><i class="mdi mdi-play"></i></button>`;
                else
                    action_items_web_tracker_table += `<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Pause/Stop Tracking" onClick="promptWebTrackerActDeact('` + value.tracker_id + `','` + value.tracker_name + `','` + value.active + `')"><i class="mdi mdi-stop"></i></button>`;
        
                action_items_web_tracker_table += `<div class="btn-group ml-sm-1 btn-group-table">
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="document.location='TrackerGenerator?tracker=` + value.tracker_id + `'">Edit</a>
                            <a class="dropdown-item" href="#" onClick="promptWebTrackerDeletion('` + value.tracker_id + `','` + value.tracker_name + `')">Delete Tracker</a>
                            <a class="dropdown-item" href="#" onClick="promptWebTrackerDataDeletion('` + value.tracker_id + `','` + value.tracker_name + `')">Delete Data</a>
                            <a class="dropdown-item" href="#" onClick="promptWebTrackerCopy('` + value.tracker_id + `','` + value.tracker_name + `')">Copy Tracker</a>
                            <a class="dropdown-item" href="#" onClick="trackerLinkCopy('` + value.tracker_id + `')">Copy Tracker Link</a>
                        </div></div></div>`;

                var tracker_step_data = JSON.parse(value.tracker_step_data);
                $("#table_web_tracker_list tbody").append("<tr><td></td><td>" + value.tracker_id + "</td><td>" + value.tracker_name + "</td><td data-order=\"" + UTC2LocalUNIX(value.date) + "\">" + tracker_step_data.web_forms.count + "</td><td>" + UTC2Local(value.date) + "</td><td data-order=\"" + UTC2LocalUNIX(value.start_time) + "\">" + UTC2Local(value.start_time) +  "</td><td data-order=\"" + UTC2LocalUNIX(value['stop_time']) + "\">" + UTC2Local(value.stop_time) + "</td><td>" + action_items_web_tracker_table + "</td></tr>");
            });
        }
        
        dt_web_tracker_list = $('#table_web_tracker_list').DataTable({
            "bDestroy": true,
            "aaSorting": [3, 'desc'],
            'columnDefs': [{
                "targets": [4,7],
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_web_tracker_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_web_tracker_list tbody').fadeIn(500);
            }
        }); //initialize table

        dt_web_tracker_list.on('order.dt_web_tracker_list search.dt_web_tracker_list', function() {
            dt_web_tracker_list.column(0, {
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
         
