var nextRandomId = getRandomId();
var dt_plaintext_list;
var g_modalValue = '';
var g_arr_alg = {'none':'None','base64':'Base64', 'base32':'Base32', 'base85':'Base85 (Ascii85)', 'rot13':'Rot13', 'urlencode':'URL Encode'};
var g_arr_extensions = {'None':'None (Default)', '.txt':'Text File (.txt)', '.html':'HTML (.html)', '.css':'CSS (.css)', '.js':'JS (.js)', '.vbs':'HTML (.vbs)', '.php':'PHP (.php)', '.asp':'ASP (.asp)', '.jsp':'JSP (.JSP)', '.ps1':'PowerShell (.ps1)', '.psm1':'PowerShell (.psm1)', 'custom':'Custom format'};
var g_arr_headers = {'None': 'None (Default)', 'text/plain':'text/plain', 'text/html':'text/html', 'text/xml':'text/xml', 'image/jpeg':'image/jpeg', 'image/png':'image/png', 'image/gif':'image/gif', 'application/octet-stream':'application/octet-stream', 'application/json':'application/json', 'application/x-www-form-urlencoded':'application/x-www-form-urlencoded', 'audio/mpeg':'audio/mpeg', 'audio/x-ms-wma':'audio/x-ms-wma', 'video/mpeg':'video/mpeg', 'video/mp4':'video/mp4', 'custom':'Custom Content-Type header'};

$("#lb_ht_id").text(nextRandomId);

$(function() {
    $("#alg_type_selector").select2({
        placeholder: "Select algorithm",
        minimumResultsForSearch: -1,
    });

    $("#file_extension_selector").select2({
        minimumResultsForSearch: -1,
    });

    $("#file_header_selector").select2({
        minimumResultsForSearch: -1,
    });
    Prism.highlightAll();
});

$.each(g_arr_alg, function(i, val) {
    $("#alg_type_selector").append('<option value="' + i + '">' + val + '</option>')
});
$.each(g_arr_extensions, function(i, val) {
    $("#file_extension_selector").append('<option value="' + i + '">' + val + '</option>')
});
$.each(g_arr_headers, function(i, val) {
    $("#file_header_selector").append('<option value="' + i + '">' + val + '</option>')
});

//------------------------
var fixHelperModified = function(e, tr) {
    var $originals = tr.children();
    var $helper = tr.clone();
    $helper.children().each(function(index) {
        $(this).width($originals.eq(index).width())
    });
    return $helper;
};

$("#tb_algo tbody").sortable({
    helper: fixHelperModified,
    start: function(e, ui){            
            $(this).addClass('sort-blur');       
            $(".alert-algo").addClass('sort-blur');
    },
    stop: function(event,ui){ 
        generateResult(undefined);     
        $(this).removeClass('sort-blur');    
        $(".alert-algo").removeClass('sort-blur'); 
    }
}).disableSelection();
//-----------------------

$(function() {
    loadTablePlainTextList();
});

var editor_input = CodeMirror.fromTextArea(document.getElementById('editor_input'), {
    mode: "shell",
    lineNumbers: true,
    lineWrapping: true
});
editor_input.save()
editor_input.setSize(null,150);

var editor_output = CodeMirror.fromTextArea(document.getElementById('editor_output'), {
    mode: "shell",
    lineNumbers: true,
    lineWrapping: true,
    readOnly: true
});
editor_output.save()
editor_output.setSize(null,'100%');

addAlgLabel('none');

function addAlgLabel(alg){
    if(alg != 'none'){
        $("#tb_algo>tbody").append(`<tr><td><div class="alert alert-warning alert-rounded alert-algo" id="` + alg + `">
                                    <span class="font-weight-bold">` + g_arr_alg[alg] + `</span>
                                    <button type="button" class="close"> <span aria-hidden="true" onclick="deleteAlgLabel($(this))">×</span> </button>
                                 </div>
                                 </td></tr>`);
    }
    triggerAlgChanges();
}

function deleteAlgLabel(e){
    e.closest('tr').remove();
    if($('#tb_algo>tbody>tr').length==0)
        $("#tb_algo>tbody").append(`<tr><td><div class="alert alert-warning alert-rounded alert-algo" id="none">
                                        <span class="font-weight-bold">None</span>
                                     </div>
                                     </td></tr>`);
    generateResult(undefined);
}

function triggerAlgChanges(){
   if($('#tb_algo>tbody>tr').length==0)
        $("#tb_algo>tbody").append(`<tr><td><div class="alert alert-warning alert-rounded alert-algo" id="none">
                                        <span class="font-weight-bold">None</span>
                                     </div>
                                     </td></tr>`);
    else        
        if($('#tb_algo>tbody>tr:first>td>div').attr('id') == "none" && $('#tb_algo>tbody>tr').length!=1)
            $('#tb_algo>tbody>tr:first').remove();
}

$('#alg_type_selector').on('change', function() {
    var dataval = $('#alg_type_selector').select2('data');
    addAlgLabel($('#alg_type_selector').val());
    generateResult(undefined);
});

$('#alg_type_selector').on('select2:open', function() { //allows click event for same selected item
    $('#alg_type_selector').val(null);
});

$('#file_extension_selector').on('change', function() {
    if($("#file_extension_selector").val() == "custom")
        $("#tb_extension_name").attr("disabled", false);
    else
        $("#tb_extension_name").attr("disabled", true);
});

$('#file_header_selector').on('change', function() {
    if($("#file_header_selector").val() == "custom")
        $("#tb_header_name").attr("disabled", false);
    else
        $("#tb_header_name").attr("disabled", true);
});

function getAlgNames(){
    var arr_alg=[];
    $('#tb_algo>tbody>tr').each(function(){ 
        arr_alg.push($(this).find('div').first().attr('id'));
    });
    return arr_alg;
}

function generateResult(e,quite=true){
    enableDisableMe(e);
    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_result_alg",
            arr_alg: getAlgNames(),
            in_data: btoa(editor_input.getValue())
         })
    }).done(function (data) {
        if(!data.error){  // no data
            editor_output.getDoc().setValue(atob(data.output));
            if(!quite)
                toastr.success('', 'Data loaded!');
        }
    }); 
}

function generateDownloadLink(file_extension){
    if(file_extension == 'None')
        file_extension ='';
    else
        if(file_extension=='custom')
            file_extension = $("#tb_extension_name").val();

    if(validateFields()){
        $("#link_output").text(window.location.origin + '/spear/sniperhost/out?ht=' + nextRandomId + file_extension);
        Prism.highlightAll();
    }
}

function validateFields(){
    if (!$('#tb_ht_name').val().match(/^[a-z\d\-_\s]+$/i)) {
        $("#tb_ht_name").addClass("is-invalid");
        return false;
    } else
        $("#tb_ht_name").removeClass("is-invalid");

    if(editor_input.getValue() == ""){
        $("#editor_input").next().addClass("is-invalid-codemirror");
        return false;
    } else
        $("#editor_input").next().removeClass("is-invalid-codemirror");

    if($("#file_extension_selector").val() == "custom" && $("#tb_extension_name").val().trim() == ''){
        $("#tb_extension_name").addClass("is-invalid");
        return false;
    } else
        $("#tb_extension_name").removeClass("is-invalid");

    if($("#file_header_selector").val() == "custom" && $("#tb_header_name").val().trim() == ''){
        $("#tb_header_name").addClass("is-invalid");
        return false;
    } else
        $("#tb_header_name").removeClass("is-invalid");
    return true;
}

function savePlainText(e) {
    if(validateFields() == false)
        return;

    if($("#file_extension_selector").val() == "custom")
        var file_extension = $("#tb_extension_name").val();
    else
        if($("#file_extension_selector").val() == '')
            file_extension = ".txt"; //default
        else
            var file_extension = $("#file_extension_selector").val();

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
            action_type: "save_plaintext",
            ht_id: nextRandomId,
            ht_name: $('#tb_ht_name').val(),
            arr_alg: getAlgNames(),
            in_data: btoa(editor_input.getValue()),
            file_extension: file_extension,
            file_header: file_header
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Saved successfully!');
            window.history.replaceState(null,null, location.pathname + '?ht=' + nextRandomId);
            loadTablePlainTextList();
            generateDownloadLink(file_extension);
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function viewPlainTextDetailsFromId(ht_id,quite) {
    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_plaintext_details_from_id",
            ht_id: ht_id,
         })
    }).done(function (data) {
        if(!data.error){
            nextRandomId = ht_id;
            $("#lb_ht_id").text(ht_id);
            $("#tb_ht_name").val(data['ht_name']);
            $("#tb_algo tbody > tr").remove();
            
            editor_input.getDoc().setValue(atob(data['in_data']));

            $.each(JSON.parse(data['alg']), function(i, alg) {
                addAlgLabel(alg);
            });

            if(g_arr_extensions.hasOwnProperty(data['file_extension']))
                $("#file_extension_selector").val(data['file_extension']).trigger("change");
            else{
                $("#file_extension_selector").val('custom').trigger("change");
                $("#tb_extension_name").val(data['file_extension']);
            }

            if(g_arr_headers.hasOwnProperty(data['file_header']))
                $("#file_header_selector").val(data['file_header']).trigger("change");
            else{
                $("#file_header_selector").val('custom').trigger("change");
                $("#tb_header_name").val(data['file_header']);
            }

            generateResult(undefined,quite);
            generateDownloadLink(data['file_extension']);

            window.history.replaceState(null,null, location.pathname + '?ht=' + ht_id);
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }
        else
            toastr.error('', data.error);
    }); 
}

function promptPlainTextDeletion(ht_id) {
    g_modalValue = ht_id;
    $('#modal_ht_delete').modal('toggle');
}

function plainTextDeletionAction() {
    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_plaintext",
            ht_id: g_modalValue,
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_ht_delete').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            loadTablePlainTextList();
            if(nextRandomId == g_modalValue)
                window.history.replaceState(null,null, location.pathname);
        }
        else
            toastr.error("", response.error);
    }); 
}

function copyDownloadLink(e,text, file_extension){
    if(file_extension == 'None')
        file_extension ='';
    else
        if(file_extension=='custom')
            file_extension = $("#tb_extension_name").val();

    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(window.location.origin + '/spear/sniperhost/out?ht=' + text + file_extension).select();
    document.execCommand("copy");
    $temp.remove();

    e.attr('data-original-title', 'Copied!').tooltip('show');
    e.text('Copied');
    window.setTimeout(function() {
        e.tooltip('hide').attr('data-original-title', 'Copy direct access link');
        e.text('');
    }, 2000);
}

function downloadCode() {
    var a = window.document.createElement('a');
    a.href = window.URL.createObjectURL(new Blob([editor_output.getValue()]));

    a.download = nextRandomId + '.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function loadTablePlainTextList() {
    try {
        dt_plaintext_list.destroy();
    } catch (err) {}
    $('#table_plaintext_list tbody > tr').remove();

    $.post({
        url: "sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_plaintext_list",
        })
    }).done(function (data) {
        if(!data.error){
            $.each(data, function(key, value) {
                var action_items = `<div class="d-flex no-block align-items-center"><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="View/Edit" onClick="viewPlainTextDetailsFromId('` + value.ht_id + `',false)"><i class="mdi mdi-eye"></i></button><button type="button" class="btn btn-success btn-sm mdi mdi-content-copy" data-toggle="tooltip" title="Copy direct access link" onClick="copyDownloadLink($(this),'` + value.ht_id + `','` + value.file_extension + `')"></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Delete" onClick="promptPlainTextDeletion('` + value.ht_id + `')"><i class="mdi mdi-delete-variant"></i></button>`;

                var arr_alg = [];
                $.each(JSON.parse(value['alg']), function(i, alg) {
                    arr_alg.push(g_arr_alg[alg]);
                });
                var curr_file_extension = g_arr_extensions[value['file_extension']]==undefined?'Custom ('+value.file_extension+')':g_arr_extensions[value['file_extension']];
                var curr_header = g_arr_headers[value['file_header']]==undefined?'Custom ('+value.file_header+')':g_arr_headers[value.file_header];

                $("#table_plaintext_list tbody").append("<tr><td></td><td>" + value.ht_name + "</td><td>" + arr_alg.join(", ") + "</td><td>" + curr_file_extension + "</td><td>" + curr_header +"</td><td data-order=\"" + UTC2LocalUNIX(value.date) + "\">" + UTC2Local(value.date) + "</td><td>" + action_items + "</td></tr>");
            });
        }

        dt_plaintext_list = $('#table_plaintext_list').DataTable({
            "bDestroy": true,
            "aaSorting": [5, 'desc'],
            'columnDefs': [{
                "targets": 6,
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_plaintext_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_plaintext_list tbody').fadeIn(500);
            }
        }); //initialize table


        dt_plaintext_list.on('order.dt_plaintext_list search.dt_plaintext_list', function() {
            dt_plaintext_list.column(0, {
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

function copyCodeOutput(e,copy_code_class){
    var c = new ClipboardJS('.btn_copy_codeOutput', {
        text: function(trigger) {
            return getCodeMirrorNative('.'+copy_code_class).getDoc().getValue();
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

function getCodeMirrorNative(target) {
    var _target = target;
    if (typeof _target === 'string') {
        _target = document.querySelector(_target);
    }
    if (_target === null || !_target.tagName === undefined) {
        throw new Error('Element does not reference a CodeMirror instance.');
    }

    if (_target.className.indexOf('CodeMirror') > -1) {
        return _target.CodeMirror;
    }

    if (_target.tagName === 'TEXTAREA') {
        return _target.nextSibling.CodeMirror;
    }

    return null;
};