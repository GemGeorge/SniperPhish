var row_index;
var dt_user_group_list;
var action_items = '<button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="" onclick="dt_user_list.row( $(this).parents(\'tr\')).remove().draw();" data-original-title="Delete"><i class="mdi mdi-delete-variant"></i></button> <button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="" onclick="editRow($(this))" data-original-title="Edit"><i class="mdi mdi-pencil"></i></button>';

dt_user_list = $('#table_user_list').DataTable({
    'columnDefs': [{
                    "targets": 4,
                    "className": "text-center"
                }],
    "preDrawCallback": function(settings) {
        $('#table_user_list tbody').hide();
    },

    "drawCallback": function() {
        $('#table_user_list tbody').fadeIn(500);
    },
    dom: 'B<"bspace"l>frtip',
    buttons: [{
            extend: 'csvHtml5',
            filename: function() {
                return $('#Modal_export_file_name').val();
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

$(function() {
    $("label>select").select2({
        minimumResultsForSearch: -1,
    });

    $("#modal_export_report_selector").select2({
        minimumResultsForSearch: -1,
    }); 
});  

function addUserToTable() {
    var field_name = $('#tablevalue_name').val();
    var field_email = $('#tablevalue_email').val();

    if (field_name == "")
        field_name = "Empty";

    if (RegTest(field_email, "EMAIL") == false) {
        $("#tablevalue_email").addClass("is-invalid");
        return;
    } else
        $("#tablevalue_email").removeClass("is-invalid");

    dt_user_list.row.add(["", field_name.trim(), field_email.trim(), $('#tablevalue_notes').val().trim(), action_items]).draw(false);
    updateTable();
}

function updateTable() {
    dt_user_list.on('order.dt_user_list search.dt_user_list', function() {
        dt_user_list.column(0, {
            search: 'applied',
            order: 'applied'
        }).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
}

function editRow(arg) {
    row_index = dt_user_list.row(arg.parents('tr')).index();

    $('#modal_tablevalue_name').val(dt_user_list.row(row_index).data()[1]);
    $('#modal_tablevalue_email').val(dt_user_list.row(row_index).data()[2]);
    $('#modal_tablevalue_notes').val(dt_user_list.row(row_index).data()[3]);
    $('#modal_modify_row').modal('toggle');
}

function editRow_action() {
    var field_name = $('#modal_tablevalue_name').val();
    var field_email = $('#modal_tablevalue_email').val();

    if (field_name == "")
        field_name = "Empty";

    if (RegTest(field_email, "EMAIL") == false) {
        $("#modal_tablevalue_email").addClass("is-invalid");
        return;
    } else
        $("#modal_tablevalue_email").removeClass("is-invalid");

    dt_user_list.row(row_index).data(['', field_name.trim(), field_email.trim(), $('#modal_tablevalue_notes').val().trim(), action_items]);
    $('#modal_modify_row').modal('toggle');
    updateTable();
}

function addUserFromFile() {
    $('input[type=file]').trigger('click');
}

$('input[type=file]').change(function() {
    var file = $('#fileinput').prop('files')[0];

    var fileExtension = ['csv', 'txt', 'lst', 'rtf'];
    if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
        toastr.error('', 'Unsupported fiel type!');
        return;
    }

    if (file) {
        var reader = new FileReader();
        reader.readAsText(file, "UTF-8");
        reader.onload = function(evt) {
            var arr_full_contents = evt.target.result.split(/\r?\n|\r/);
            for (var i = 0; i < arr_full_contents.length; i++) {
                var data_name='';
                var data_email='';
                var data_notes='';

                var arr_row_contents = arr_full_contents[i].split(',');

                if (RegTest(arr_row_contents[0], "EMAIL") == true) { //if no name
                    data_email = arr_row_contents[0];
                    if (arr_row_contents[1] != undefined) //2nd colum value is in notes section
                        data_notes = arr_row_contents[1];
                }
                else
                    if (RegTest(arr_row_contents[1], "EMAIL") == true) { //if 2nd column is email
                        if (arr_row_contents[0] != undefined) //set empty if 1st colum is empty
                            data_name = arr_row_contents[0];
                        data_email = arr_row_contents[1];
                        if (arr_row_contents[2] != undefined) //set empty if 2nd colum is empty
                            data_notes = arr_row_contents[2];
                    }

                if(data_email.trim() != '')
                    dt_user_list.row.add(["", data_name.trim(), data_email.trim(), data_notes.trim(), action_items]).draw(false);
            }
            updateTable();
        }
        reader.onerror = function(evt) {
            toastr.error('', 'Error reading file!');
        }
    }
});

function saveUserGroup(e) {
    if (RegTest($('#user_group_name').val(),'COMMON') == false) {
        $("#user_group_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#user_group_name").removeClass("is-invalid");

    if (!dt_user_list.data().any()) {
        toastr.error('', 'Table is empty!');
        return;
    }

    var userData = [];
    dt_user_list.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
        userData.push({"name": this.data()[1], email: this.data()[2], notes: this.data()[3]});
    } );

    enableDisableMe(e);
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "save_user_group",
            user_group_id: nextRandomId,
            user_group_name: $('#user_group_name').val(),
            user_data:  userData,
        })
    }).done(function (response) {
        if(response.result == "success")
            toastr.success('', 'Saved successfully!');
        else
            toastr.error('', 'Error saving data!');
        enableDisableMe(e);
    }); 
}

function getUserGroupFromGroupId(id) {
    if (id == "new") {
        getRandomId();
        return;
    } else
        nextRandomId = id;

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_user_group_from_group_Id",
            user_group_id: id,
         })
    }).done(function (data) {
        if(!data.error){  // no data
            $('#user_group_name').val(data.user_group_name);
            $('#Modal_export_file_name').val(data.user_group_name);

            $.each(data.user_data, function(key, value) {
               dt_user_list.row.add(["", value.name, value.email, value.notes, action_items]).draw(false);
            });
            updateTable();
        }
    }); 
}

function promptUserGroupDeletion(id) {
    globalModalValue = id;
    $('#modal_user_group_delete').modal('toggle');
}

function userGroupDeletionAction() {
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_user_group_from_group_id",
            user_group_id: globalModalValue
         })
    }).done(function (response) {
        if(response.result == "success"){
           $('#modal_user_group_delete').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            dt_user_group_list.destroy();
            $("#table_user_group_list tbody > tr").remove();
            loadTableUserGroupList();
        }
        else
            toastr.error("", response.error);
    }); 
}

function promptUserGroupCopy(id) {
    globalModalValue = id;
    $('#modal_user_group_copy').modal('toggle');
}

function UserGroupCopy() {
    if (RegTest($('#modal_new_user_group_name').val(), 'COMMON') == false) {
        $("#modal_new_user_group_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#modal_new_user_group_name").removeClass("is-invalid");

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "make_copy_user_group",
            user_group_id: globalModalValue,
            new_user_group_id: getRandomId(),
            new_user_group_name: $("#modal_new_user_group_name").val()
         })
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Copy success!');
            $('#modal_user_group_copy').modal('toggle');
            dt_user_group_list.destroy();
            $("#table_user_group_list tbody > tr").remove();
            loadTableUserGroupList();
        }
        else
            toastr.error("", response.error);
    }); 
}

//-------------------------------------
function loadTableUserGroupList() {
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_user_group_list"
         })
    }).done(function (data) {
         dt_user_group_list = $('#table_user_group_list').DataTable({
            "bDestroy": true,
            "aaSorting": [3, 'asc'],
            'columnDefs': [{
                "targets": 4,
                "className": "dt-center"
            }],
            
            "preDrawCallback": function(settings) {
                $('#table_user_group_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_user_group_list tbody').fadeIn(500);
            },
            
        }); //initialize table

        if(!data['error']){  // no data
             $.each(data, function(key, value) {
                var action_items_user_group_table = `<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="document.location='MailUserGroup?action=edit&user=` + value.user_group_id + `'" title="View/Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Copy" onclick="promptUserGroupCopy('` + value.user_group_id + `')"><i class="mdi mdi-content-copy"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Delete" onclick="promptUserGroupDeletion('` + value.user_group_id + `')"><i class="mdi mdi-delete-variant"></i></button>`;
                dt_user_group_list.row.add(["", value.user_group_name, Object.keys(value.user_data).length, UTC2Local(value.date), action_items_user_group_table]).draw(false);
            });
        }
        dt_user_group_list.on('order.dt_user_group_list search.dt_user_group_list', function() {
            dt_user_group_list.column(0, {
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