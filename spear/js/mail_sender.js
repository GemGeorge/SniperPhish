var dt_mail_sender_list, store_info, g_dsn_type='custom';
var action_items_header_table = '<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="editRowHeaderTable($(this))" title="Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" onclick="promptMailHeaderDeletion($(this))" title="Delete"><i class="mdi mdi-delete-variant"></i>';

$(function() {
    $("#selector_common_mail_senders").select2({
        minimumResultsForSearch: -1
    });
    $('#selector_common_mail_senders').on('change', function() {
        $("#lb_selector_common_mail_sender_note").html(store_info[$(this).find(':selected').text()].info.disp_note);
    });
    $('#section_addsender').click(function(){
        g_deny_navigation = '';
    });
});

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

$("#cb_auto_mailbox").change(function() {
    if(this.checked){
        if($('#mail_sender_SMTP_server').val().trim() != '' && $('#mail_sender_SMTP_server').val().trim() != 'NA')
            $('#mail_sender_mailbox').val("{"+ $('#mail_sender_SMTP_server').val().split(":")[0]+":993/imap/ssl}INBOX");
        else{
            if($('#mail_sender_mailbox').val().trim() != 'None'){
                $('#mail_sender_mailbox').val('None'); 
                toastr.warning('', 'Failed to determine mailbox path. None value is set. Set mailbox path manually if required.');  
            }
        }
        $("#mail_sender_mailbox").prop('disabled', true);
    }
    else
        $("#mail_sender_mailbox").prop('disabled', false);
    $("#mail_sender_mailbox").removeClass("is-invalid");
});

function addMailHeaderToTable() {
    var mail_sender_custome_header_name = $('#mail_sender_custome_header_name').val().trim();
    var mail_sender_custome_header_val = $('#mail_sender_custome_header_val').val().trim();

    if (mail_sender_custome_header_name == "") {
        $("#mail_sender_custome_header_name").addClass("is-invalid");
        return;
    } else
        $("#mail_sender_custome_header_name").removeClass("is-invalid");

    if (mail_sender_custome_header_val == "") {
        $("#mail_sender_custome_header_val").addClass("is-invalid");
        return;
    } else
        $("#mail_sender_custome_header_val").removeClass("is-invalid");

    dt_mail_headers_list.row.add([mail_sender_custome_header_name, mail_sender_custome_header_val, action_items_header_table]).draw(false);
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
    var mail_sender_custome_header_name = $('#modal_mail_sender_custome_header_name').val().trim();
    var mail_sender_custome_header_val = $('#modal_mail_sender_custome_header_val').val().trim();

    if (mail_sender_custome_header_name == "") {
        $("#modal_mail_sender_custome_header_name").addClass("is-invalid");
        return;
    } else
        $("#modal_mail_sender_custome_header_name").removeClass("is-invalid");

    if (mail_sender_custome_header_val == "") {
        $("#modal_mail_sender_custome_header_val").addClass("is-invalid");
        return;
    } else
        $("#modal_mail_sender_custome_header_val").removeClass("is-invalid");

    dt_mail_headers_list.row(globalModalValue).data([mail_sender_custome_header_name, mail_sender_custome_header_val, action_items_header_table]).draw(false);

    $('#modal_mail_header_edit').modal('toggle');
}

function saveMailSenderGroup(e) {
    var cust_header_name = dt_mail_headers_list.rows().data().pluck(0).toArray();
    var cust_header_val = dt_mail_headers_list.rows().data().pluck(1).toArray();

    var mail_sender_name = $('#mail_sender_name').val().trim();
    var mail_sender_SMTP_server = $('#mail_sender_SMTP_server').val().trim();
    var mail_sender_from = $('#mail_sender_from').val().trim();
    var mail_sender_acc_username = $('#mail_sender_acc_username').val().trim();
    var mail_sender_acc_pwd = $('#mail_sender_acc_pwd').val().trim();
    var mail_sender_mailbox = $('#mail_sender_mailbox').val().trim();

    if (RegTest(mail_sender_name, "COMMON") == false) {
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

    if (mail_sender_acc_username == '') {
        $("#mail_sender_acc_username").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_username").removeClass("is-invalid");

    if (mail_sender_from == '') {
        $("#mail_sender_from").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_from").removeClass("is-invalid");

    if ($("#cb_auto_mailbox").is(':checked') && $("#mail_sender_mailbox").val() != null){
        $("#mail_sender_mailbox").removeClass("is-invalid");
        var cb_auto_mailbox = 1;
    }
    else {        
        var cb_auto_mailbox = 0;
        if (mail_sender_mailbox == '') {
            $("#mail_sender_mailbox").addClass("is-invalid");
            toastr.error('', 'Empty/unsupported character!');
            return;
        } else
            $("#mail_sender_mailbox").removeClass("is-invalid");
    }


    var cust_headers = [];
    $.each(cust_header_name, function(index, value) {
        cust_headers[cust_header_name[index]] = cust_header_val[index];
    });

    enableDisableMe(e);
    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        data: JSON.stringify({ 
            action_type: "save_sender_list",
            sender_list_id: nextRandomId,
            sender_list_mail_sender_name: mail_sender_name,
            sender_list_mail_sender_SMTP_server: mail_sender_SMTP_server,
            sender_list_mail_sender_from: mail_sender_from,
            sender_list_mail_sender_acc_username: mail_sender_acc_username,
            sender_list_mail_sender_acc_pwd: mail_sender_acc_pwd,
            mail_sender_mailbox: mail_sender_mailbox,
            cb_auto_mailbox: cb_auto_mailbox,
            sender_list_cust_headers: Object.assign({}, cust_headers),
            dsn_type: g_dsn_type
         }),
        contentType: 'application/json; charset=utf-8'
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Saved successfully!');
            window.history.replaceState(null,null, location.pathname + '?action=edit&sender=' + nextRandomId);
            g_deny_navigation = null;
        }
        else
            toastr.error('', 'Error saving data!');
        enableDisableMe(e);
    }); 
}

function getSenderFromSenderListId(id) {
    if (id == "new") {
        getRandomId();
        return;
    } 
    else
        nextRandomId = id;

    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_sender_from_sender_list_id",
            sender_list_id: id,
         })
    }).done(function (data) {
        if(!data['error']){  // no data
            $('#mail_sender_name').val(data['sender_name']);
            $('#mail_sender_SMTP_server').val(data['sender_SMTP_server']);
            $('#mail_sender_from').val(data['sender_from']);
            $('#mail_sender_acc_username').val(data['sender_acc_username']);
            $('#mail_sender_mailbox').val(data['sender_mailbox']);
            $('#cb_auto_mailbox').prop('checked', (data['auto_mailbox']==1?true:false)).trigger("change");
            $("#lb_sender_template_name").text(data['dsn_type']);
            cust_header_data = data['cust_headers'];
            g_dsn_type = data['dsn_type'];

            $.each(cust_header_data, function(header_name, header_value) {
                dt_mail_headers_list.row.add([header_name, header_value, action_items_header_table]).draw(false);
            });

            $('[data-toggle="tooltip"]').tooltip();
        }
    }); 
}

function promptMailSenderCopy(id) {
    globalModalValue = id;
    $('#modal_sender_list_copy').modal('toggle');
}

function MailSenderCopyAction() {
    var modal_mail_sender_name = $('#modal_mail_sender_name').val();

    if (RegTest(modal_mail_sender_name, "COMMON") == false) {
        $("#mail_sender_name").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_name").removeClass("is-invalid");

    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "make_copy_sender_list",
            sender_list_id: globalModalValue,
            new_sender_list_id: getRandomId(),
            new_sender_list_name: modal_mail_sender_name,
         })
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Copy success!');
            $('#modal_sender_list_copy').modal('toggle');
            dt_mail_sender_list.destroy();
            $("#table_mail_sender_list tbody > tr").remove();
            loadTableSenderList();
        }
        else
            toastr.error("", "Error making copy!<br/>" + response.error);
    }); 
}

function promptSenderListDeletion(id) {
    globalModalValue = id;
    $('#modal_sender_list_delete').modal('toggle');
}

function senderListDeletionAction() {
    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_mail_sender_list_from_list_id",
            sender_list_id: globalModalValue
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_sender_list_delete').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            dt_mail_sender_list.destroy();
            $("#table_mail_sender_list tbody > tr").remove();
            loadTableSenderList();
        }
        else
            toastr.error("", "Error deleting data!<br/>" + response.error);
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
    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_sender_list"
         })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(key, value) {
                var cust_header = "";
                if(!$.isEmptyObject(value.cust_headers)) 
                    $.each(value.cust_headers, function(header_name, header_value) {
                        cust_header += header_name + ": " + header_value + "</br>";
                    });
                else
                    cust_header = "-";

                var action_items_sender_table = '<div class="d-flex no-block align-items-center"><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="" onclick="document.location=\'MailSender?action=edit&sender=' + value['sender_list_id'] + '\'" data-original-title="Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Copy" onclick="promptMailSenderCopy(\'' + value['sender_list_id'] + '\')"><i class="mdi mdi-content-copy"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="" onclick="promptSenderListDeletion(\'' + value['sender_list_id'] + '\')" data-original-title="Delete"><i class="mdi mdi-delete-variant"></i></button></div>';

                $("#table_mail_sender_list tbody").append("<tr><td></td><td>" + value.sender_name + "</td><td>" + value.sender_SMTP_server + "</td><td>" + value.sender_from + "</td><td>" + value.sender_acc_username + "</td><td>" + cust_header +  "</td><td data-order=\"" + getTimestamp(value.date) + "\">" + (value.date==null?'-':value.date) + "</td><td>" + action_items_sender_table + "</td></tr>");
            });
        }

        dt_mail_sender_list = $('#table_mail_sender_list').DataTable({
            "aaSorting": [6, 'asc'],
            'pageLength': 20,
            'lengthMenu': [[20, 50, 100, -1], [20, 50, 100, 'All']],
            'columnDefs': [{
                "targets": 7,
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_mail_sender_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_mail_sender_list tbody').fadeIn(500);
                $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            },

            "initComplete": function() {
                $("label>select").select2({minimumResultsForSearch: -1, });
            }
        }, {
            "order": [[1, 'asc']]
        }); 

        dt_mail_sender_list.on('order.dt_mail_sender_list search.dt_mail_sender_list', function() {
            dt_mail_sender_list.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    });   
}

//----------------------------------------------------
function modalTestDeliveryAction(e){
    var cust_header_name = dt_mail_headers_list.rows().data().pluck(0).toArray();
    var cust_header_val = dt_mail_headers_list.rows().data().pluck(1).toArray();

    var mail_sender_name = $('#mail_sender_name').val().trim();
    var mail_sender_SMTP_server = $('#mail_sender_SMTP_server').val().trim();
    var mail_sender_from = $('#mail_sender_from').val().trim();
    var mail_sender_acc_username = $('#mail_sender_acc_username').val().trim();
    var mail_sender_acc_pwd = $('#mail_sender_acc_pwd').val().trim();
    var test_to_address = $('#modal_mail_sender_test_mail_to').val().trim();

    if (RegTest(mail_sender_name, "COMMON") == false) {
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

    if (mail_sender_acc_username == '') {
        $("#mail_sender_acc_username").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_username").removeClass("is-invalid");

    if (RegTest(test_to_address, "EMAIL") == false) {
        $("#modal_mail_sender_test_mail_to").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#modal_mail_sender_test_mail_to").removeClass("is-invalid");

    var cust_headers = [];
    $.each(cust_header_name, function(index, value) {
        cust_headers[cust_header_name[index]] = cust_header_val[index];
    });

    enableDisableMe(e);
    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "send_test_mail_verification",
            sender_list_id: nextRandomId,
            sender_list_mail_sender_SMTP_server: mail_sender_SMTP_server,
            sender_list_mail_sender_from: mail_sender_from,
            sender_list_mail_sender_acc_username: mail_sender_acc_username,
            sender_list_mail_sender_acc_pwd: mail_sender_acc_pwd,
            sender_list_cust_headers: Object.assign({}, cust_headers),
            test_to_address: test_to_address,
            dsn_type: g_dsn_type,
         })
    }).done(function (response) {
        if(response.result == "success")
                toastr.success('', 'Success. Check your inbox!');
        else
            toastr.error('', 'Error sending mail!<br/>' + response.error);
        $('#modal_sender_list_test_mail').modal('toggle');
        enableDisableMe(e);
    }); 
}

function verifyMailBoxAccess(){
    var mail_sender_SMTP_server = $('#mail_sender_SMTP_server').val().trim();
    var mail_sender_acc_username = $('#mail_sender_acc_username').val().trim();
    var mail_sender_acc_pwd = $('#mail_sender_acc_pwd').val().trim();
    var mail_sender_mailbox = $('#mail_sender_mailbox').val().trim();

    if (mail_sender_mailbox == '' || mail_sender_mailbox == 'None') {
        toastr.error('', 'Mailbox path incorrectly configured!');
        return;
    }

    if (mail_sender_SMTP_server == '') {
        $("#mail_sender_SMTP_server").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_SMTP_server").removeClass("is-invalid");

    if (mail_sender_acc_username == '') {
        $("#mail_sender_acc_username").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#mail_sender_acc_username").removeClass("is-invalid");

    if ($("#cb_auto_mailbox").is(':checked') && $("#mail_sender_mailbox").val() != null)
        $("#mail_sender_mailbox").removeClass("is-invalid");
    else {        
        if (mail_sender_mailbox == '') {
            $("#mail_sender_mailbox").addClass("is-invalid");
            toastr.error('', 'Empty/unsupported character!');
            return;
        } else
            $("#mail_sender_mailbox").removeClass("is-invalid");
    }

    var cust_header_name = dt_mail_headers_list.rows().data().pluck(0).toArray();
    var cust_header_val = dt_mail_headers_list.rows().data().pluck(1).toArray();
    $.each(cust_header_name, function(index, header_name) {
        if(header_name.toUpperCase() == 'REPLY-TO')
            mail_sender_acc_username = cust_header_val[index];
    });

    $('#modal_verifier').modal('toggle');
    $("#modal_verifier_body .area_data").text('Looking mailbox of ' + mail_sender_acc_username + ':');
    $("#modal_verifier_body .area_loader").html('');
    $("#modal_verifier_body .area_loader").append(displayLoader("Verifying..."));
    $.post({
        url: "manager/userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "verify_mailbox_access",
            sender_list_id: nextRandomId,
            mail_sender_acc_username: mail_sender_acc_username,
            mail_sender_acc_pwd: mail_sender_acc_pwd,
            mail_sender_mailbox: mail_sender_mailbox
        })
    }).done(function (response) {
        $("#modal_verifier_body .area_loader").find('.loadercust').remove(); 
        if(response.result == "success")
            $("#modal_verifier_body .area_loader").html("<strong>Successfully verified.<br/>A total of " + response.total_msg_count + " messages detected in the mailbox path provided.</strong>");
        else
            $("#modal_verifier_body .area_loader").html("<strong>Error: </strong><br/>" + response.error);
    }).fail(function(response){
        $("#modal_verifier_body .area_loader").html("<strong>Error: </strong><br/>" + response.statusText);
        $("#modal_verifier_body .area_loader").find('.loadercust').remove(); 
    }); 
}

//------------------Store section---------------
function getStoreList(){
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_store_list",
            type: "mail_sender",
         })
    }).done(function (data) {
        if(!data['error']){  // no data
            store_info = data;
            $.each(data, function(name) {
                $("#selector_common_mail_senders").append("<option value='" + data[name].info.dsn_type + "'>" + name + "</option>");
            });
            $('#selector_common_mail_senders').trigger("change"); 

            $.each(store_info, function(i, item) {
                if(item.info.dsn_type == g_dsn_type){
                    appySenderTemplateAttr(item.content);
                    return false;
                }
            });
        }
    }); 
}

function appySenderTemplate(){
    $("#mail_sender_SMTP_server").prop('disabled', false);
    $("#mail_sender_acc_username").val('');

    g_dsn_type = store_info[$("#selector_common_mail_senders").find(':selected').text()].info.dsn_type;
    var content = store_info[$("#selector_common_mail_senders").find(':selected').text()].content;
    $("#lb_sender_template_name").text($("#selector_common_mail_senders").find(':selected').text());

    $("#mail_sender_SMTP_server").val(content.smtp.value);

    $("#mail_sender_from").val(content.from);
    $("#mail_sender_acc_username").val(content.username);
    $("#mail_sender_mailbox").val(content.mailbox.value);
    $('#cb_auto_mailbox').prop('checked', content.mailbox.checked).trigger("change");    

    appySenderTemplateAttr(content);
    $('#modal_store').modal('toggle');
}

function appySenderTemplateAttr(content){
    $("#mail_sender_SMTP_server").prop('disabled', content.smtp.disabled);

    if($.inArray(g_dsn_type,  ['postmark','sendgrid','mailpace']) != -1){
        $("#mail_sender_acc_username").val('NA');
        $("#mail_sender_acc_username").prop('disabled', true);
    }
    else        
        $("#mail_sender_acc_username").prop('disabled', false);
}