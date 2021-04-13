var nextRandomId = getRandomId();
var dt_files_list;
var g_modalValue = '';
var gFileData ={"fname":null, "fb64":null};
var g_arr_headers = {'None': 'None (Default)', 'text/plain':'text/plain', 'text/html':'text/html', 'text/xml':'text/xml', 'image/jpeg':'image/jpeg', 'image/png':'image/png', 'image/gif':'image/gif', 'application/octet-stream':'application/octet-stream', 'application/json':'application/json', 'application/x-www-form-urlencoded':'application/x-www-form-urlencoded', 'audio/mpeg':'audio/mpeg', 'audio/x-ms-wma':'audio/x-ms-wma', 'video/mpeg':'video/mpeg', 'video/mp4':'video/mp4', 'custom':'Custom Content-Type header'};

$("#lb_hf_id").text(nextRandomId);

$(function() {
    $("#file_header_selector").select2({
        minimumResultsForSearch: -1,
    });
    loadTableFilesList();
});
$.each(g_arr_headers, function(i, val) {
    $("#file_header_selector").append('<option value="' + i + '">' + val + '</option>')
});

function getFileData(fname,fsize,ftype,fb64){    
    gFileData ={"fname":null, "fb64":null};

    if (fsize > 1024*1024*15)
        $("#upload_msg").html('<span class="badge badge-pill badge-danger">File size exceeded. Max file size is 15MB</span>');
    else{
        gFileData.fname = fname;
        gFileData.fb64 = fb64;
        $("#upload_msg").html('<span class="badge badge-pill badge-success">' + fname + ' uploaded!</span>');
        if($("#collapseOne").hasClass('show'))
            $("#collapseOne").collapse('toggle');
    }    
}

$('#file_header_selector').on('change', function() {
    if($("#file_header_selector").val() == "custom")
        $("#tb_header_name").prop('disabled', false);
    else
        $("#tb_header_name").prop('disabled', true);
});

function saveFile(e) {
    if (!$('#tb_hf_name').val().match(/^[a-z\d\-_\s]+$/i)) {
        $("#tb_hf_name").addClass("is-invalid");
        return false;
    } else
        $("#tb_hf_name").removeClass("is-invalid");

    if($("#file_header_selector").val() == "custom")
        var file_header = $("#tb_header_name").val();
    else
        if($("#file_header_selector").val() == '')
            file_header = "text/plain"; //default
        else
            var file_header = $("#file_header_selector").val();

    enableDisableMe(e);
    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "save_file",
            hf_id: nextRandomId,
            hf_name: $('#tb_hf_name').val(),
            file_name: gFileData.fname,
            file_header: file_header,
            file_b64: gFileData.fb64
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Saved successfully!');
            window.history.replaceState(null,null, location.pathname + '?ht=' + nextRandomId);
            loadTableFilesList();
            viewFileDetailsFromId(nextRandomId,true);
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function viewFileDetailsFromId(hf_id,quite) {
    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_file_details_from_id",
            hf_id: hf_id,
         })
    }).done(function (data) {
        if(!data.error){  // no data
            nextRandomId = hf_id;
            $("#lb_hf_id").text(hf_id);
            $("#tb_hf_name").val(data['hf_name']);
            $("#tb_header_name").val('');

            if(g_arr_headers.hasOwnProperty(data['file_header']))
                $("#file_header_selector").val(data['file_header']).trigger("change");
            else{
                $("#file_header_selector").val('custom').trigger("change");
                $("#tb_header_name").val(data['file_header']);
            }

            generateDownloadLink();

            if(!quite)
                toastr.success('', 'Data loaded!');

            $("#upload_msg").text("Drop the binary here or click to upload");
            window.history.replaceState(null,null, location.pathname + '?hf=' + hf_id);
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }
    }); 
}

function generateDownloadLink(){
    if ($('#tb_hf_name').val().match(/^[a-z\d\-_\s]+$/i)) {
        $("#link_output").text(window.location.origin + '/spear/sniperhost/out?hf=' + nextRandomId);
        Prism.highlightAll();
    }
}

function promptFileDeletion(hf_id) {
    g_modalValue = hf_id;
    $('#modal_hf_delete').modal('toggle');
}

function fileDeletionAction() {
    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_file",
            hf_id: g_modalValue,
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_hf_delete').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            loadTableFilesList();
            if(nextRandomId == g_modalValue){
                $("#upload_msg").text("Drop the file here or click to upload");
                $("#link_output").empty();
                window.history.replaceState(null,null, location.pathname);
            }
        }
        else
            toastr.error("", response.error);
    }); 
}

function loadTableFilesList() {
    try {
        dt_files_list.destroy();
    } catch (err) {}
    $('#table_file_list tbody > tr').remove();

    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_file_list",
        })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(key, value) {
                var action_items = `<div class="d-flex no-block align-items-center"><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="View/Edit" onClick="viewFileDetailsFromId('` + value.hf_id + `',false)"><i class="mdi mdi-eye"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Delete" onClick="promptFileDeletion('` + value.hf_id + `')"><i class="mdi mdi-delete-variant"></i></button>`;

                var curr_header = g_arr_headers[value.file_header]==undefined?'Custom ('+value.file_header+')':g_arr_headers[value.file_header];

                $("#table_file_list tbody").append("<tr><td></td><td>" + value.hf_name + "</td><td>" + value.file_original_name + "</td><td>" + curr_header +"</td><td data-order=\"" + UTC2LocalUNIX(value.date) + "\">" + UTC2Local(value.date) + "</td><td>" + action_items + "</td></tr>");
            });
        }

        dt_files_list = $('#table_file_list').DataTable({
            "bDestroy": true,
            "aaSorting": [4, 'desc'],
            'columnDefs': [{
                "targets": 5,
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_file_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_file_list tbody').fadeIn(500);
            }
        }); //initialize table
        
        dt_files_list.on('order.dt_files_list search.dt_files_list', function() {
            dt_files_list.column(0, {
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

//-------Start ClipboarJS -----

function copyCode(e,copy_code_class){
    var c = new ClipboardJS('.btn_copy', {
        target: function(trigger) {
            return document.querySelector('.'+copy_code_class);
        }
    });

    c.on('success', function(event) {
        event.clearSelection();
        if(event.text.trim()=='')
            return;
        e.attr('data-original-title', 'Copied!').tooltip('show');
        event.trigger.textContent = 'Copied';
        window.setTimeout(function() {
            e.tooltip('hide').attr('data-original-title', 'Copy');
            event.trigger.textContent = '';
        }, 2000);
    });    
}
