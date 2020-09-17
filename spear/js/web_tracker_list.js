var dt_web_tracker_list;

$(function() {
    loadTableWebTrackerList();
});

//---------------
function webTrackerActDeactAction(tracker_id, action_value){
    action_value == 0 ? new_action_value=1 : new_action_value = 0;
    $.post("web_tracker_generator_list_manager", {
        action_type: "pause_stop_web_tracker_tracking",
        tracker_id: tracker_id,
        active: new_action_value
    },
    function(data, status) {
        if (data == "success") {
            $('#modal_prompts').modal('toggle');
            loadTableWebTrackerList();
            if (new_action_value == 1)
                toastr.success('', 'Success. Tracking started!');
            else
                toastr.success('', 'Success. Tracking stopped!');
        }
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
    $("#modal_prompts_body").text("This will delete Tracker \"" + tracker_name + "\" and the action can't be undone!");
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="webTrackerDeletionAction()">Confirm</button>`);
}

function webTrackerDeletionAction() {
    $.post("web_tracker_generator_list_manager", {
            action_type: "delete_web_tracker",
            tracker_id: g_modalValue
        },
        function(data, status) {
            if (data == "deleted") {
                $('#modal_prompts').modal('toggle');
                toastr.success('', 'Deleted successfully!');
                loadTableWebTrackerList();
            } else {
                toastr.error('', 'Error deleting tracker!');
            }
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

    if (!modal_web_tracker_name.match(/^[a-z\d\-_\s]+$/i)) {
        $("#modal_mail_campaign_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#modal_web_tracker_name").removeClass("is-invalid");

    $.post("web_tracker_generator_list_manager", {
            action_type: "make_copy_web_tracker",
            tracker_id: globalModalValue,
            new_tracker_id: getRandomId(),
            new_tracker_name: modal_web_tracker_name
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Copy success!');
                $('#modal_copy_web_tracker').modal('toggle');
                loadTableWebTrackerList();
            } else
                toastr.error('', 'Error making copy!');
        });
}
//---------------
//---------------
function downloadWebTrackerFromId(tracker_id) {
    $.post("web_tracker_generator_list_manager", {
            action_type: "get_web_tracker_codes",
            tracker_id: tracker_id
        },
        function(data, status) {
            var zip = new JSZip();

            var js_code_link =  `<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>\r\n<script src="` + location.origin + `/mod?tlink=` + tracker_id + `"></script>`;

            var form_data = JSON.parse(data.content_html);
            var webpage_data = JSON.parse(data.tracker_step_data).web_forms;
            $(form_data).each(function(i, obj) {
                zip.file(webpage_data[i].page_url.split('/').pop(), `<!DOCTYPE html>\r\n` + js_code_link + "\r\n" + obj.replace("<!DOCTYPE html>", ""));
            });

            // Generate the zip file asynchronously
            zip.generateAsync({
                type: "blob"
            }).then(function(content) {
                // Force down of the Zip file
                var a = window.document.createElement('a');
                a.href = window.URL.createObjectURL(new Blob([content], {
                    type: 'application/octet-stream'
                }));
                a.download = 'public_html.zip';
                document.body.appendChild(a);
                a.click();

                // Remove anchor from body
                document.body.removeChild(a);
            });   
        });
}

function trackerLinkCopy(tracker_id){
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(`<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>\r\n<script src="` + location.origin + `/mod?tlink=` + tracker_id + `"></script>`).select();
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

    $.post("web_tracker_generator_list_manager", {
            action_type: "get_web_tracker_list"
        },
        function(data, status) {
            if(!data['resp']){  // no data response
                $.each(data, function(key, value) {
                    var action_items_web_tracker_table = `<div class="d-flex no-block"><button type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Report" onclick="document.location='TrackerReport?tracker=` + value['tracker_id'] + `'"><i class="mdi mdi-book-open"></i></button>`;

                    if (value['active'] == 0)
                        action_items_web_tracker_table += `<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Start/Resume Tracking" onClick="promptWebTrackerActDeact('` + value['tracker_id'] + `','` + value['tracker_name'] + `','` + value['active'] + `')"><i class="mdi mdi-play"></i></button>`;
                    else
                        action_items_web_tracker_table += `<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Pause/Stop Tracking" onClick="promptWebTrackerActDeact('` + value['tracker_id'] + `','` + value['tracker_name'] + `','` + value['active'] + `')"><i class="mdi mdi-stop"></i></button>`;
            
                    action_items_web_tracker_table += `<div class="btn-group ml-sm-1">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="document.location='TrackerGenerator?tracker=` + value['tracker_id'] + `'">Edit</a>
                                <a class="dropdown-item" href="#" onClick="promptWebTrackerDeletion('` + value['tracker_id'] + `','` + value['tracker_name'] + `')">Delete</a>
                                <a class="dropdown-item" href="#" onClick="promptWebTrackerCopy('` + value['tracker_id'] + `','` + value['tracker_name'] + `')">Copy</a>
                                <a class="dropdown-item" href="#" onClick="trackerLinkCopy('` + value['tracker_id'] + `')">Copy Tracker Link</a>
                                <a class="dropdown-item" href="#" onClick="downloadWebTrackerFromId('` + value['tracker_id'] + `')">Download</a>
                            </div></div></div>`;

                    var tracker_step_data = JSON.parse(value['tracker_step_data']);
                    $("#table_web_tracker_list tbody").append("<tr><td></td><td>" + value['tracker_id'] + "</td><td>" + value['tracker_name'] + "</td><td data-order=\"" + UTC2LocalUNIX(value['date']) + "\">" + tracker_step_data.web_forms.count + "</td><td>" + UTC2Local(value['date']) + "</td><td data-order=\"" + UTC2LocalUNIX(value['start_time']) + "\">" + UTC2Local(value['start_time']) +  "</td><td data-order=\"" + UTC2LocalUNIX(value['stop_time']) + "\">" + UTC2Local(value['stop_time']) + "</td><td>" + action_items_web_tracker_table + "</td></tr>");
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
        }).fail(function() {
        toastr.error('', 'Error getting trackers list!');
    });
}
         
