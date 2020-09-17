var globalModalValue = '';
var dt_mail_campaign_list;

$(function() {
    $("#userGroupSelector").select2({
        minimumResultsForSearch: -1
    });
    $("#mailTemplateSelector").select2({
        minimumResultsForSearch: -1
    });
    $("#mailSenderSelector").select2({
        minimumResultsForSearch: -1
    });
    $("#lb_campaign_time_val").show();


    $('#datetimepicker_launch').datetimepicker({
        format: 'DD-MM-YYYY hh:mm A',
        defaultDate: new Date(),
        icons: {
            time: "fa fa-clock",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        },

    });
});

function pullMailCampaignFieldData() {
    $.post("mail_campaign_manager", {
            action_type: "pull_mail_campaign_field_data"
        },
        function(data, status) {
            if (data != "failed") {
                var user_group = data['user_group'];
                var mail_template = data['mail_template'];
                var mail_sender = data['mail_sender'];

                $.each(user_group, function(key) {
                    $('#userGroupSelector').append('<option value="' + user_group[key]["user_group_id"] + '">' + user_group[key]['user_group_name'] + '</option>');
                });

                $.each(mail_template, function(key) {
                    $('#mailTemplateSelector').append('<option value="' + mail_template[key]["mail_template_id"] + '">' + mail_template[key]['mail_template_name'] + '</option>');
                });

                $.each(mail_sender, function(key) {
                    $('#mailSenderSelector').append('<option value="' + mail_sender[key]["sender_list_id"] + '">' + mail_sender[key]['sender_name'] + '</option>');
                });

            } else
                toastr.error('', data);
        });
}

function getMailCampaignFromCampaignListId(id) {
    if (id == "new") {
        getRandomId();
        return;
    } else
        nextRandomId = id;

    $.when(pullMailCampaignFieldData()).then(function() {
        $.post("mail_campaign_manager", {
                action_type: "get_campaign_from_campaign_list_id",
                campaign_id: id,
            },
            function(data, status) {
                if (data) {
                    $('#mail_campaign_name').val(data['campaign_name']);

                    try {
                        $("#userGroupSelector").val(data['user_group'].toString().split(",")[0]);
                        $("#userGroupSelector").trigger('change');
                    } catch (err) {}

                    try {
                        $("#mailTemplateSelector").val(data['mail_template'].toString().split(",")[0]);
                        $("#mailTemplateSelector").trigger('change');
                    } catch (err) {}

                    try {
                        $("#mailSenderSelector").val(data['mail_sender'].toString().split(",")[0]);
                        $("#mailSenderSelector").trigger('change');
                    } catch (err) {}

                    $("#datetimepicker_launch").val(UTC2Local(data['scheduled_time'].toString()));

                    $('#range_campaign_time_min').val(data['msg_interval'].split('-')[0]);
                    $('#range_campaign_time_max').val(data['msg_interval'].split('-')[1]);
                    $('#tb_campaign_time_val').val(data['msg_interval']);
                    $('#tb_campaign_msg_retry').val(data['msg_fail_retry']);
                    $('#range_campaign_msg_retry').val(data['msg_fail_retry']);
                }
            });
    });
}

function promptSaveMailCampaign() {
    if ($("#cb_act_deact_campaign").is(':checked') && ($("#datetimepicker_launch").data("DateTimePicker").date()) <= (new Date($.now()))) {
        $('#modal_prompts').modal('toggle');
        $("#modal_prompts_body").text("");
        $("#modal_prompts_body").append("The scheduled time is in past. This will start the campaign immediately. Do you want to save and start campaign \"" + $('#mail_campaign_name').val() + "\"?<br/><br/><i>Note: This will delete previous results of this campaign</i>");
        $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="saveMailCampaignAction()">Save</button>`);
    } else
        saveMailCampaignAction();
}

function saveMailCampaignAction() {
    var selector_values = [];
    var mail_campaign_name = $('#mail_campaign_name').val();
    selector_values[0] = $('#userGroupSelector').val() + "," + $('#userGroupSelector :selected').text();
    selector_values[1] = $('#mailTemplateSelector').val() + "," + $('#mailTemplateSelector :selected').text();
    selector_values[2] = $('#mailSenderSelector').val() + "," + $('#mailSenderSelector :selected').text();

    var launch_time = moment.utc($("#datetimepicker_launch").data("DateTimePicker").date()).format('DD-MM-YYYY hh:mm A')
    var msg_interval = $('#tb_campaign_time_val').val();
    var msg_fail_retry = $('#range_campaign_msg_retry').val();

    if ($("#cb_act_deact_campaign").is(':checked'))
        var cb_act_deact_campaign = 1;
    else
        var cb_act_deact_campaign = 0;

    if (!mail_campaign_name.match(/^[a-z\d\-_\s]+$/i)) {
        $("#mail_campaign_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#mail_campaign_name").removeClass("is-invalid");

    if ($('#userGroupSelector').val() == null) {
        $("#userGroupSelector").parent().css("border", "1px solid red");
        toastr.error('', 'None selected!');
        return;
    } else
        $("#userGroupSelector").parent().css("border", "0px");

    if ($('#mailTemplateSelector').val() == null) {
        $("#mailTemplateSelector").parent().css("border", "1px solid red");
        toastr.error('', 'None selected!');
        return;
    } else
        $("#mailTemplateSelector").parent().css("border", "0px");

    if ($('#mailSenderSelector').val() == null) {
        $("#mailSenderSelector").parent().css("border", "1px solid red");
        toastr.error('', 'None selected!');
        return;
    } else
        $("#mailSenderSelector").parent().css("border", "0px");

    enableDisableMe($("#bt_saveMailCamp"));

    $.post("mail_campaign_manager", {
            action_type: "save_campaign_list",
            campaign_id: nextRandomId,
            mail_campaign_name: mail_campaign_name,
            mail_campaign_user_group: btoa(selector_values[0]),
            mail_campaign_mail_template: btoa(selector_values[1]),
            mail_campaign_mail_sender: btoa(selector_values[2]),
            mail_campaign_scheduled_time: btoa(launch_time),
            msg_interval: btoa(msg_interval),
            msg_fail_retry: msg_fail_retry,
            camp_status: cb_act_deact_campaign
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Saved successfully!');
                setTimeout(function() {
                    document.location = "MailCampaignList";
                }, 1000);
            } else
                toastr.error('', data);
                $("#bt_saveMailCamp").attr('disabled', false);
            enableDisableMe($("#bt_saveMailCamp"));
        });
}

function promptMailCampActDeact(id, campaign_name, action_value, curr_element) {
    $('#modal_prompts').modal('toggle');
    if (action_value == 0)
        $("#modal_prompts_body").html("Activate Email campaign \"" + campaign_name + "\"?<br/><br/><i>Note: If you have run this campaign before, this will delete previous results of the campaign. Instead, create a copy of the campaign and run.</i>");
    if (action_value == 1)
        $("#modal_prompts_body").text("Deactivate Email campaign \"" + campaign_name + "\"?");
    if (action_value == 2 || action_value == 4)
        $("#modal_prompts_body").text("Stop running Email campaign \"" + campaign_name + "\"?");

    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="mailCampStartStopAction('` + id + `','` + campaign_name + `','` + action_value + `')">Confirm</button>`);
    globalModalValue = curr_element;
}

function mailCampStartStopAction(id, campaign_name, action_value) {
    if (action_value == 2 || action_value == 4) // if in progress or mail sending completed, stop it and finish
        var new_action_value = 3;
    if (action_value == 1) //if scheduled, then inactivate it
        var new_action_value = 0;
    if (action_value == 0) //if inactive, then schedule it
        var new_action_value = 1;

    $.post("mail_campaign_manager", {
            action_type: "start_stop_mailCampaign",
            campaign_id: id,
            action_value: new_action_value
        },
        function(data, status) {
            if (data == "success") {
                loadTableCampaignList();
                $('#modal_prompts').modal('toggle');

                if (new_action_value == 3)
                    toastr.success('', 'Success. Campaign Stopped!');
                if (new_action_value == 0)
                    toastr.success('', 'Success. Cmapaign deactivated!');
                if (new_action_value == 1)
                    toastr.success('', 'Success. Campaign scheduled!');
            } else
                toastr.error('', data);

            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        });

}

function promptMailCampaignDeletion(id, campaign_name) {
    globalModalValue = id;
    $('#modal_prompts').modal('toggle');
    $("#modal_prompts_body").text("This will delete Email campaign \"" + campaign_name + "\" and the action can't be undone!");
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="mailCampaignDeletionAction()">Confirm</button>`);
}

function mailCampaignDeletionAction() {
    $.post("mail_campaign_manager", {
            action_type: "delete_campaign_from_campaign_id",
            campaign_id: globalModalValue
        },
        function(data, status) {
            if (data == "success") {
                $('#modal_prompts').modal('toggle');
                toastr.success('', 'Deleted successfully!');
                loadTableCampaignList();
            } else {
                toastr.error('', 'Error deleting data!');
            }
        });
}

function promptMailCampaignCopy(id) {
    globalModalValue = id;
    $('#modal_mail_campaign_copy').modal('toggle');
}

function mailCampaignCopyAction() {
    var modal_mail_campaign_name = $('#modal_mail_campaign_name').val();

    if (!modal_mail_campaign_name.match(/^[a-z\d\-_\s]+$/i)) {
        $("#modal_mail_campaign_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#modal_mail_campaign_name").removeClass("is-invalid");

    $.post("mail_campaign_manager", {
            action_type: "make_copy_campaign_list",
            campaign_id: globalModalValue,
            new_campaign_id: getRandomId(),
            new_campaign_name: modal_mail_campaign_name
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Copy success!');
                $('#modal_mail_campaign_copy').modal('toggle');
                loadTableCampaignList();
            } else
                toastr.error('', 'Error making copy!');
        });
}

//-------------------------------------
function loadTableCampaignList() {
    try {
        dt_mail_campaign_list.destroy();
    } catch (err) {}
    $('#table_mail_campaign_list tbody > tr').remove();

    $.post("mail_campaign_manager", {
            action_type: "get_campaign_list"
        },
        function(data, status) {
            if(!data['resp']){  // no data response
                $.each(data, function(key, value) {
                    var action_items_campaign_table = `<div class="d-flex no-block align-items-center"><button type="button" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Report" onclick="document.location='MailCmpDashboard?mcamp=` + value['campaign_id'] + `'"><i class="mdi mdi-book-open"></i></button>`;
                    if (value['camp_status'] == 0)
                        action_items_campaign_table += `<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Activate" onClick="promptMailCampActDeact('` + value['campaign_id'] + `','` + value['campaign_name'] + `','` + value['camp_status'] + `',$(this))"><i class="mdi mdi-play"></i></button>`;
                    if (value['camp_status'] == 2 || value['camp_status'] == 4)
                        action_items_campaign_table += `<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Stop and Finish" onClick="promptMailCampActDeact('` + value['campaign_id'] + `','` + value['campaign_name'] + `','` + value['camp_status'] + `',$(this))"><i class="mdi mdi-stop"></i></button>`;
                    if (value['camp_status'] == 3)
                        action_items_campaign_table += `<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Start action disabled" disabled><i class="mdi mdi-play"></i></button>`;
                    if (value['camp_status'] == 1)
                        action_items_campaign_table += `<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Deactivate" onClick="promptMailCampActDeact('` + value['campaign_id'] + `','` + value['campaign_name'] + `','` + value['camp_status'] + `',$(this))"><i class="mdi mdi-stop"></i></button>`;
                    
                    if(value['camp_status'] == 0 || value['camp_status'] == 3)
                        var option_edit = `<a class="dropdown-item" href="#" onClick="document.location='MailCampaignList?action=edit&campaign=` + value['campaign_id'] + `','` + value['campaign_name'] + `'">Edit</a>`;
                    else
                        var option_edit = `<a class="dropdown-item" href="#" disabled>Edit</a>`;

                    action_items_campaign_table += `<div class="btn-group ml-sm-1">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More</button>
                            <div class="dropdown-menu">` +
                                option_edit +                             
                                `<a class="dropdown-item" href="#" onClick="promptMailCampaignDeletion('` + value['campaign_id'] + `','` + value['campaign_name'] + `')">Delete</a>
                                <a class="dropdown-item" href="#" onClick="promptMailCampaignCopy('` + value['campaign_id'] + `','` + value['campaign_name'] + `')">Copy</a>
                        </div></div></div>`;

                    switch (value['camp_status']) {
                        case "0":
                            var camp_status = `<span class="ff badge badge-pill badge-dark" data-toggle="tooltip" title="Not scheduled"><i class="mdi mdi-alert"></i> Inactive</span>`;
                            break;
                        case "1":
                            var camp_status = `<span class="badge badge-pill badge-warning" data-toggle="tooltip" title="Scheduled"><i class="mdi mdi-timer"></i> Scheduled</span>`;
                            break;
                        case "2":
                            var camp_status = `<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Phishing status"><i class="mdi mdi-fish"></i> In-progress</span> <span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Mail sending status"><i class="mdi mdi-email"></i> In-progress</span>`;
                            break;
                        case "3":
                            var camp_status = `<span class="badge badge-pill badge-success" data-toggle="tooltip" title="Phishing status"><i class="mdi mdi-fish"></i> Completed</span>`;
                            break;
                        case "4":
                            var camp_status = `<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Phishing status"><i class="mdi mdi-fish"></i> In-progress</span> <span class="badge badge-pill badge-success" data-toggle="tooltip" title="Mail sending status"><i class="mdi mdi-email"></i> Completed</span>`;
                            break;
                    }                
                    
                    $("#table_mail_campaign_list tbody").append("<tr><td></td><td>" + value['campaign_name'] + "</td><td>" + value['user_group'].toString().split(",")[1] + "</td><td>" + value['mail_template'].toString().split(",")[1] + "</td><td>" + value['mail_sender'].toString().split(",")[1] + "</td><td data-order=\"" + UTC2LocalUNIX(value['date']) + "\">" + UTC2Local(value['date']) + "</td><td data-order=\"" + UTC2LocalUNIX(value['scheduled_time']) + "\">" + UTC2Local(value['scheduled_time']) + "</td><td data-order=\"" + UTC2LocalUNIX(value['stop_time']) + "\">" + UTC2Local(value['stop_time']) + "</td><td>"+ camp_status + "</td><td>" + action_items_campaign_table + "</td></tr>");
                });
            }

            dt_mail_campaign_list = $('#table_mail_campaign_list').DataTable({
                "aaSorting": [5, 'desc'],
                'columnDefs': [{
                    "targets": [8,9],
                    "className": "dt-center"
                }],
                "preDrawCallback": function(settings) {
                    $('#table_mail_campaign_list tbody').hide();
                },

                "drawCallback": function() {
                    $('#table_mail_campaign_list tbody').fadeIn(500);
                }
            }); //initialize table

            dt_mail_campaign_list.on('order.dt_mail_campaign_list search.dt_mail_campaign_list', function() {
                dt_mail_campaign_list.column(0, {
                    search: 'applied',
                    order: 'applied'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }).fail(function() {
        toastr.error('', 'Error getting campaign list!');
    });
}

function rangeCampTimeChange(e) {
    range_campaign_time_min = Number($('#range_campaign_time_min').val());
    range_campaign_time_max = Number($('#range_campaign_time_max').val());

    if (range_campaign_time_max < range_campaign_time_min && event.srcElement.id == "range_campaign_time_max")
        $('#range_campaign_time_min').val(range_campaign_time_max);
    else
    if (range_campaign_time_max < range_campaign_time_min && event.srcElement.id == "range_campaign_time_min")
        $('#range_campaign_time_max').val(range_campaign_time_min);

    $('#tb_campaign_time_val').val(("0000" + range_campaign_time_min).slice(-4) + "-" + ("0000" + range_campaign_time_max).slice(-4));
}

$('#tb_campaign_time_val').on('keyup', function() {
    var min = $('#tb_campaign_time_val').val().split('-')[0];
    var max = $('#tb_campaign_time_val').val().split('-')[1];

    if(!Number(max) || min>max)
        $("#tb_campaign_time_val").addClass("is-invalid");
     else{
        $("#tb_campaign_time_val").removeClass("is-invalid");
        $('#range_campaign_time_min').val(min);
        $('#range_campaign_time_max').val(max);
    }
});

function rangeCampRetryFailChange(e){
    $('#tb_campaign_msg_retry').val($('#range_campaign_msg_retry').val());
}
$('#tb_campaign_msg_retry').on('keyup', function() {
    var tb_campaign_msg_retry = $('#tb_campaign_msg_retry').val();

    if(isNaN(tb_campaign_msg_retry) || tb_campaign_msg_retry>10 || tb_campaign_msg_retry<0 || tb_campaign_msg_retry == "")
        $("#tb_campaign_msg_retry").addClass("is-invalid");
     else{
        $("#tb_campaign_msg_retry").removeClass("is-invalid");
        $('#range_campaign_msg_retry').val(tb_campaign_msg_retry);
    }
});