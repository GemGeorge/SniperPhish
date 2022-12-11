var dt_user_list;
var g_modalValue='';
loadTableUserList();
getCurrentUser();

function getCurrentUser() {
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_current_user",
         })
    }).done(function (data) {
        if(!data.error){
            $('#lb_name').text(data.name);
            $('#lb_uname').text(data.username);
            $('#lb_mail').text(data.contact_mail);
            $('#lb_created_date').text(data.date);
            $('#user_dp').attr('src','/spear/images/users/' + data.dp_name + '.png');
            $('.pro-pic').attr('src','/spear/images/users/' + data.dp_name + '.png');
            $('#bt_edit_current_user').click(function(){
                prompModifyUser(data.id, data.name, data.username, data.contact_mail, data.dp_name);
            });
        }
        else
            toastr.error('', data.error);
    }); 
}

function addUserAction(e){
    var name = $("#tb_add_name").val().trim();
    var username = $("#tb_add_uname").val().trim();
    var mail = $("#tb_add_mail").val().trim();
    var new_pwd = $("#tb_add_pwd").val().trim();
    var confirm_pwd = $("#tb_add_confirm_pwd").val().trim();
    var dp_name =$('input[name="rb_add_dp"]:checked').val();
    var current_pwd = $("#tb_add_current_pwd").val().trim();

    if(name == ''){
        $("#tb_add_name").addClass("is-invalid");
        return;
    } else
        $("#tb_add_name").removeClass("is-invalid");

    if(username == ''){
        $("#tb_add_uname").addClass("is-invalid");
        return;
    } else
        $("#tb_add_uname").removeClass("is-invalid");

    if(RegTest(mail, 'EMAIL') == false){
        $("#tb_add_mail").addClass("is-invalid");
        return;
    } else
        $("#tb_add_mail").removeClass("is-invalid");

    if (current_pwd == '') {
        $("#tb_add_current_pwd").addClass("is-invalid");
        return;
    } else
        $("#tb_add_current_pwd").removeClass("is-invalid");

    if (new_pwd == '' || confirm_pwd =='') {
        toastr.error('', 'New password can not be empty!');
        return;
    }
    
    if(!isPwdSecure(new_pwd, confirm_pwd, '#tb_add_pwd', '#tb_add_confirm_pwd'))
        return;

    enableDisableMe(e);
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "add_account",
            name: name,
            username: username,
            mail: mail,
            dp_name: dp_name,
            new_pwd: new_pwd,
            current_pwd: current_pwd,
        }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Information updated successfully!');   
            $("#tb_add_new_pwd").val('');
            $("#tb_add_confirm_pwd").val('');
            $('#ModalAddUser').modal('toggle');
            loadTableUserList();
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function modifyUserAction(e){
    var name = $("#tb_update_name").val().trim();
    var username = $("#tb_update_uname").val().trim();
    var mail = $("#tb_update_mail").val().trim();
	var current_pwd = $("#tb_update_current_pwd").val().trim();
	var new_pwd = $("#tb_update_new_pwd").val().trim();
	var confirm_pwd = $("#tb_update_confirm_pwd").val().trim();
    var dp_name =$('input[name="rb_update_dp"]:checked').val();

    if(name == ''){
        $("#tb_update_name").addClass("is-invalid");
        return;
    } else
        $("#tb_update_name").removeClass("is-invalid");

    if(RegTest(mail, 'EMAIL') == false){
        $("#tb_update_mail").addClass("is-invalid");
        return;
    } else
        $("#lb_update_mail").removeClass("is-invalid");

    if (current_pwd == '') {
        $("#tb_update_current_pwd").addClass("is-invalid");
        return;
    } else
        $("#tb_update_current_pwd").removeClass("is-invalid");

	if(!(new_pwd=='' && confirm_pwd==''))
        if(!isPwdSecure(new_pwd, confirm_pwd, '#tb_add_pwd', '#tb_add_confirm_pwd'))
            return;

    enableDisableMe(e);
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "modify_account",
            name: name,
            username: username,
            mail: mail,
            dp_name: dp_name,
            new_pwd: new_pwd,
            current_pwd: current_pwd,
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Information updated successfully!');   
            $("#tb_update_current_pwd").val('');
            $("#tb_update_new_pwd").val('');
            $("#tb_update_confirm_pwd").val('');
            $('#ModalModifyUser').modal('toggle');
            loadTableUserList();
            getCurrentUser();
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function deleteAccountAction() {
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_account",
            id: g_modalValue
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_prompts').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            $('#ModalUserDelete').modal('toggle');
            loadTableUserList();
        }
        else
            toastr.error('', response.error);
    }); 
}

function promptDeleteAccount(id) {
    g_modalValue = id;
    $('#ModalUserDelete').modal('toggle');
}

function prompModifyUser(id,name,username,email,dp_name) {
    g_modalValue = id;
    $("#tb_update_name").val(name);
    $("#tb_update_uname").val(username);
    $("#tb_update_mail").val(email);
    $("#modal_title_name").text(name);
    $("input[name=rb_update_dp]").val([dp_name]);

    $('#ModalModifyUser').modal('toggle');
}

function loadTableUserList() {
    try {
        dt_user_list.destroy();
    } catch (err) {}
    $('#table_user_list tbody > tr').remove();

    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_user_list"
        })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(key, value) {
                if(value.id == 1)
                    var action_items = `<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="prompModifyUser('`+value.id+`','`+value.name+`','`+value.username+`','`+value.contact_mail+`','`+value.dp_name+`')" title="View/Edit"><i class="mdi mdi-pencil"></i></button>`;
                else
                    var action_items = `<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="prompModifyUser('`+value.id+`','`+value.name+`','`+value.username+`','`+value.contact_mail+`','`+value.dp_name+`')" title="View/Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Delete" onclick="promptDeleteAccount('` + value.id + `')"><i class="mdi mdi-delete-variant"></i></button>`;

                $("#table_user_list tbody").append("<tr><td></td><td>" + value.name + "</td><td>" + value.username + "</td><td>" + value.contact_mail + "</td><td data-order=\"" + getTimestamp(value.date) + "\">" + value.date + "</td><td data-order=\"" + getTimestamp(value.last_login) + "\">" + value.last_login + "</td><<td>" + action_items + "</td></tr>");
            });
        }
        
        dt_user_list = $('#table_user_list').DataTable({
            "bDestroy": true,
            'pageLength': 20,
            'lengthMenu': [[20, 50, 100, -1], [20, 50, 100, 'All']],
            "preDrawCallback": function(settings) {
                $('#table_user_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_user_list tbody').fadeIn(500);
                $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
                $("label>select").select2({minimumResultsForSearch: -1, });
            }
        });

        dt_user_list.on('order.dt_user_list search.dt_user_list', function() {
            dt_user_list.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
    });   
}

function isPwdSecure(new_pwd, confirm_pwd, new_pwd_field, confirm_pwd_field){
    var f_valid = true;

    if(new_pwd != new_pwd.trim() || confirm_pwd != confirm_pwd.trim()){
        toastr.error('', 'Blank spaces at start/end is not permitted!');
        f_valid = false;
    }
    else{
        new_pwd = new_pwd.trim();
        confirm_pwd = confirm_pwd.trim();
        if(new_pwd != confirm_pwd){
            toastr.error('', 'Confirm password does not match!');
            f_valid = false;
        }
        else
        if(new_pwd.length<8){
            toastr.error('', 'Password length should be atleast 8 characters!');
            f_valid = false;
        }
    }

    if(f_valid){
        $(new_pwd_field).removeClass("is-invalid");
        $(confirm_pwd_field).removeClass("is-invalid");
    }
    else{
        $(new_pwd_field).addClass("is-invalid");
        $(confirm_pwd_field).addClass("is-invalid");
    }
    return f_valid;
}