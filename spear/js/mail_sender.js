var dt_mail_sender_list;
var action_items_header_table = '<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="editRowHeaderTable($(this))" title="Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" onclick="promptMailHeaderDeletion($(this))" title="Delete"><i class="mdi mdi-delete-variant"></i>';
$(".valid-feedback").show();
var dt_mail_headers_list = $('#table_mail_headers_list').DataTable({
    'columnDefs': [{
                    "targets": 2,
                    "className": "dt-center"
                }],
    "preDrawCallback": function(settings) {
        $('#table_mail_headers_list tbody').hide();
    },

    "drawCallback": function() {
        $('#table_mail_headers_list tbody').fadeIn(500);
    },

    "dom": ''
}); //initialize table

function addMailHeaderToTable() {
    var field_mail_sender_custome_header_name = $('#mail_sender_custome_header_name').val().trim();
    var field_mail_sender_custome_header_value = $('#mail_sender_custome_header_val').val().trim();

    if (field_mail_sender_custome_header_name == "") {
        $("#mail_sender_custome_header_name").addClass("is-invalid");
        return;
    } else
        $("#mail_sender_custome_header_name").removeClass("is-invalid");

    if (field_mail_sender_custome_header_value == "") {
        $("#mail_sender_custome_header_val").addClass("is-invalid");
        return;
    } else
        $("#mail_sender_custome_header_val").removeClass("is-invalid");

    dt_mail_headers_list.row.add([field_mail_sender_custome_header_name, field_mail_sender_custome_header_value, action_items_header_table]).draw(false);

    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
}

function promptMailHeaderDeletion(id) {
    globalModalValue = id;
    $('#modal_mail_header_delete').modal('toggle');
}

function MailHeaderDeletionAction() {
    dt_mail_headers_list.row($(globalModalValue).parents('tr')).remove().draw();
    $('#modal_mail_header_delete').modal('toggle');
}

function editRowHeaderTable(arg) {
    row_index = dt_mail_headers_list.row(arg.parents('tr')).index();
    globalModalValue = row_index;

    $('#modal_mail_sender_custome_header_name').val(dt_mail_headers_list.row(row_index).data()[0]);
    $('#modal_mail_sender_custome_header_val').val(dt_mail_headers_list.row(row_index).data()[1]);
    $('#modal_mail_header_edit').modal('toggle');
}

function editRowHeaderTableAction() {

    var field_mail_sender_custome_header_name = $('#modal_mail_sender_custome_header_name').val().trim();
    var field_mail_sender_custome_header_value = $('#modal_mail_sender_custome_header_val').val().trim();

    if (field_mail_sender_custome_header_name == "") {
        $("#modal_mail_sender_custome_header_name").addClass("is-invalid");
        return;
    } else
        $("#modal_mail_sender_custome_header_name").removeClass("is-invalid");

    if (field_mail_sender_custome_header_value == "") {
        $("#modal_mail_sender_custome_header_val").addClass("is-invalid");
        return;
    } else
        $("#modal_mail_sender_custome_header_val").removeClass("is-invalid");

    dt_mail_headers_list.row(globalModalValue).data([field_mail_sender_custome_header_name, field_mail_sender_custome_header_value, action_items_header_table]).draw(false);

    $('#modal_mail_header_edit').modal('toggle');
}

function saveMailSenderGroup(e) {

    var cust_header_name = dt_mail_headers_list.rows().data().pluck(0).toArray();
    var cust_header_val = dt_mail_headers_list.rows().data().pluck(1).toArray();

    var mail_sender_name = $('#mail_sender_name').val();
    var mail_sender_SMTP_server = $('#mail_sender_SMTP_server').val();
    var mail_sender_from = $('#mail_sender_from').val();
    var mail_sender_acc_username = $('#mail_sender_acc_username').val();
    var mail_sender_acc_pwd = $('#mail_sender_acc_pwd').val();
    var mail_sender_mailbox = $('#mail_sender_mailbox').val();

    if (!mail_sender_name.match(/^[a-z\d\-_\s]+$/i)) {
        $("#mail_sender_name").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_name").removeClass("is-invalid");

    if (mail_sender_SMTP_server == '') {
        $("#mail_sender_SMTP_server").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_SMTP_server").removeClass("is-invalid");

    if (mail_sender_from == '') {
        $("#mail_sender_from").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_from").removeClass("is-invalid");

    if (validateEmailAddress(mail_sender_acc_username) == false) {
        $("#mail_sender_acc_username").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_username").removeClass("is-invalid");

    if (mail_sender_mailbox == '') {
        $("#mail_sender_mailbox").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_mailbox").removeClass("is-invalid");

    var cust_headers = '';
    $.each(cust_header_name, function(index, value) {
        cust_headers += cust_header_name[index] + ':' + cust_header_val[index] + "$#$";
    });

    enableDisableMe(e);
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "save_sender_list",
            sender_list_id: nextRandomId,
            sender_list_mail_sender_name: mail_sender_name,
            sender_list_mail_sender_SMTP_server: btoa(mail_sender_SMTP_server),
            sender_list_mail_sender_from: btoa(mail_sender_from),
            sender_list_mail_sender_acc_username: btoa(mail_sender_acc_username),
            sender_list_mail_sender_acc_pwd: btoa(mail_sender_acc_pwd),
            mail_sender_mailbox: btoa(mail_sender_mailbox),
            sender_list_cust_headers: btoa(cust_headers)
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Saved successfully!');
            } else
                toastr.error('', data);
            enableDisableMe(e);
        });
}

function getSenderFromSenderListId(id) {
    if (id == "new") {
        getRandomId();
        return;
    } else
        nextRandomId = id;

    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "get_sender_from_sender_list_id",
            sender_list_id: id,
        },
        function(data, status) {
            if (data) {
                $('#mail_sender_name').val(data['sender_name']);

                $('#mail_sender_SMTP_server').val(data['sender_SMTP_server']);
                $('#mail_sender_from').val(data['sender_from']);
                $('#mail_sender_acc_username').val(data['sender_acc_username']);
                $('#mail_sender_mailbox').val(data['sender_mailbox']);
                cust_header_data = data['cust_headers'].split('$#$');

                $.each(cust_header_data, function(key, value) {
                    if (value != "")
                        dt_mail_headers_list.row.add([value.split(':')[0], value.split(':')[1], action_items_header_table]).draw(false);
                });
                $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            }
        });
}

function promptMailSenderCopy(id) {
    globalModalValue = id;
    $('#modal_sender_list_copy').modal('toggle');
}

function MailSenderCopyAction() {
    var modal_mail_sender_name = $('#modal_mail_sender_name').val();

    if (!modal_mail_sender_name.match(/^[a-z\d\-_\s]+$/i)) {
        $("#mail_sender_name").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_name").removeClass("is-invalid");

    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "make_copy_sender_list",
            sender_list_id: globalModalValue,
            new_sender_list_id: getRandomId(),
            new_sender_list_name: modal_mail_sender_name,
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Copy success!');
                $('#modal_sender_list_copy').modal('toggle');
                dt_mail_sender_list.destroy();
                $("#table_mail_sender_list tbody > tr").remove();
                loadTableSenderList();
            } else
                toastr.error('', 'Error making copy!');
        });
}

function promptSenderListDeletion(id) {
    globalModalValue = id;
    $('#modal_sender_list_delete').modal('toggle');
}

function senderListDeletionAction() {
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "delete_mail_sender_list_from_list_id",
            sender_list_id: globalModalValue
        },
        function(data, status) {
            if (data == "deleted") {
                $('#modal_sender_list_delete').modal('toggle');
                toastr.success('', 'Deleted successfully!');
                dt_mail_sender_list.destroy();
                $("#table_mail_sender_list tbody > tr").remove();
                loadTableSenderList();
            } else {
                toastr.error('', 'Error deleting data!');
            }
        });
}

function editRowSenderTable(arg) {
    $('#modal_mail_sender_name').val(dt_mail_sender_list.row(row_index).data()[1]);
    $('#modal_mail_sender_SMTP_server').val(dt_mail_sender_list.row(row_index).data()[2]);
    $('#modal_mail_sender_from').val(dt_mail_sender_list.row(row_index).data()[3]);
    $('#modal_mail_sender_acc_username').val(dt_mail_sender_list.row(row_index).data()[4]);
    $('#modal_sender_list_modify').modal('toggle');
}

//----------------------------------------------------

function updateTable() {
    dt_mail_sender_list.on('order.dt_mail_sender_list search.dt_mail_sender_list', function() {
        dt_mail_sender_list.column(0, {
            search: 'applied',
            order: 'applied'
        }).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
}

//-------------------------------------
function loadTableSenderList() {
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "get_sender_list"
        },
        function(data, status) {
            dt_mail_sender_list = $('#table_mail_sender_list').DataTable({
                "aaSorting": [6, 'asc'],
                'columnDefs': [{
                    "targets": 7,
                    "className": "dt-center"
                }],
                "preDrawCallback": function(settings) {
                    $('#table_mail_sender_list tbody').hide();
                },

                "drawCallback": function() {
                    $('#table_mail_sender_list tbody').fadeIn(500);
                }
            }, {
                "order": [
                    [1, 'asc']
                ]
            }); //initialize table

            if (data != "") {
                $.each(data, function(key, value) {
                    var action_items_sender_table = '<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="" onclick="document.location=\'MailSender?action=edit&sender=' + value['sender_list_id'] + '\'" data-original-title="Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Copy" onclick="promptMailSenderCopy(\'' + value['sender_list_id'] + '\')"><i class="mdi mdi-content-copy"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="" onclick="promptSenderListDeletion(\'' + value['sender_list_id'] + '\')" data-original-title="Delete"><i class="mdi mdi-delete-variant"></i></button>';
                    dt_mail_sender_list.row.add(["", value['sender_name'], value['sender_SMTP_server'], value['sender_from'], value['sender_acc_username'], value['cust_headers'].toString().replace(/(\$\#\$)/g, '</br>'), UTC2Local(value['date']), action_items_sender_table]).draw(false);
                });
            }
            dt_mail_sender_list.on('order.dt_mail_sender_list search.dt_mail_sender_list', function() {
                dt_mail_sender_list.column(0, {
                    search: 'applied',
                    order: 'applied'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        });
}

//----------------------------------------------------
function promptModalTestDelivery(){
    $('#modal_sender_list_test_mail').modal('toggle');
    
}
function modalTestDeliveryAction(e){
    var cust_header_name = dt_mail_headers_list.rows().data().pluck(0).toArray();
    var cust_header_val = dt_mail_headers_list.rows().data().pluck(1).toArray();

    var mail_sender_name = $('#mail_sender_name').val();
    var mail_sender_SMTP_server = $('#mail_sender_SMTP_server').val();
    var mail_sender_from = $('#mail_sender_from').val();
    var mail_sender_acc_username = $('#mail_sender_acc_username').val();
    var mail_sender_acc_pwd = $('#mail_sender_acc_pwd').val();
    var test_to_address = $('#modal_mail_sender_test_mail_to').val();

    if (!mail_sender_name.match(/^[a-z\d\-_\s]+$/i)) {
        $("#mail_sender_name").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_name").removeClass("is-invalid");

    if (mail_sender_SMTP_server == '') {
        $("#mail_sender_SMTP_server").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_SMTP_server").removeClass("is-invalid");

    if (mail_sender_from == '') {
        $("#mail_sender_from").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_from").removeClass("is-invalid");

    if (validateEmailAddress(mail_sender_acc_username) == false) {
        $("#mail_sender_acc_username").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_username").removeClass("is-invalid");

    if (mail_sender_acc_pwd == '') {
        $("#mail_sender_acc_pwd").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_pwd").removeClass("is-invalid");

    if (validateEmailAddress(test_to_address) == false) {
        $("#modal_mail_sender_test_mail_to").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#modal_mail_sender_test_mail_to").removeClass("is-invalid");

    var cust_headers = '';
    $.each(cust_header_name, function(index, value) {
        cust_headers += cust_header_name[index] + ':' + cust_header_val[index] + "$#$";
    });

    enableDisableMe(e);
    $.post("mail_campaign_manager", {
            action_type: "send_mail_direct",
            sender_list_mail_sender_SMTP_server: btoa(mail_sender_SMTP_server),
            sender_list_mail_sender_from: btoa(mail_sender_from),
            sender_list_mail_sender_acc_username: btoa(mail_sender_acc_username),
            sender_list_mail_sender_acc_pwd: btoa(mail_sender_acc_pwd),
            sender_list_cust_headers: btoa(cust_headers),
            test_to_address: btoa(test_to_address)
        },
        function(data, status) {
            if (data == "success"){
                toastr.success('', 'Success. Check your inbox!');
                $('#modal_sender_list_test_mail').modal('toggle');
            }
            else
                toastr.error('', data);
            enableDisableMe(e);
        });
}

function verifyMailBoxAccess(){
    var mail_sender_acc_username = $('#mail_sender_acc_username').val();
    var mail_sender_acc_pwd = $('#mail_sender_acc_pwd').val();
    var mail_sender_mailbox = $('#mail_sender_mailbox').val();

    if (mail_sender_from == '') {
        $("#mail_sender_from").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_from").removeClass("is-invalid");

    if (validateEmailAddress(mail_sender_acc_username) == false) {
        $("#mail_sender_acc_username").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_username").removeClass("is-invalid");

    if (mail_sender_mailbox == '') {
        $("#mail_sender_mailbox").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_mailbox").removeClass("is-invalid");

    if (mail_sender_acc_pwd == '') {
        $("#mail_sender_acc_pwd").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_pwd").removeClass("is-invalid");

    var cust_header_name = dt_mail_headers_list.rows().data().pluck(0).toArray();
    var cust_header_val = dt_mail_headers_list.rows().data().pluck(1).toArray();
    $.each(cust_header_name, function(index, header_name) {
        if(header_name.toUpperCase() == 'REPLY-TO')
            mail_sender_acc_username = cust_header_val[index];
    });

    $('#modal_prompts').modal('toggle');
    $("#modal_prompts_body").text("");
    $("#modal_prompts_body").attr("hidden", true);
    $("#modal_prompts_body").parent().append(displayLoader("Verifying..."));
    $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" id="modal_prompts_confirm_button" onClick="saveMailCampaignAction()">Save</button>`);

    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "verify_mailbox_access",
            mail_sender_acc_username: btoa(mail_sender_acc_username),
            mail_sender_mailbox: btoa(mail_sender_mailbox),
            mail_sender_acc_pwd: btoa(mail_sender_acc_pwd),
        },
        function(data, status) {
            $("#modal_prompts_body").attr("hidden", false);
            $("#modal_prompts_body").parent().children().remove('.loadercust');
            if (data == "")
                $("#modal_prompts_body").html("<strong>Successfully verified</strong>");
            else
                $("#modal_prompts_body").html("<strong>Error:</strong><br/>" + data);
        });   
}