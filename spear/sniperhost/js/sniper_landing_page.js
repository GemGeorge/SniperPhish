var nextRandomId = getRandomId();
var dt_landpage_list;
var bt_media = `<div class="note-btn-group btn-group note-style">
        <button type="button" class="note-btn btn btn-light btn-sm note-btn-bold" tabindex="-1" title="Link" data-toggle="tooltip" data-placement="bottom" onclick="$('#modal_media_link_text').val($('#summernote').summernote('createRange').toString());$('#modal_media_link').modal('toggle');">
            <i class="note-icon-link"></i>
        </button>
        <button type="button" class="note-btn btn btn-light btn-sm note-btn-italic" tabindex="-1" title="Picture" data-toggle="tooltip" data-placement="bottom" onclick="$('#modal_media_pic').modal('toggle');">
            <i class="note-icon-picture"></i>
        </button>
        <button type="button" class="note-btn btn btn-light btn-sm note-btn-underline" tabindex="-1" title="Video" data-toggle="tooltip" data-placement="bottom" onclick="$('#modal_media_video').modal('toggle');">
            <i class="note-icon-video"></i>
        </button>
    </div>`;

var bt_more_tools = `<div class="note-btn-group btn-group">
        <button type="button" class="note-btn btn btn-light btn-sm dropdown-toggle dropdown-tooltip" tabindex="-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-placement="bottom" title="Insert" onclick="$('.dropdown-tooltip').tooltip('hide');" ><i class="fas fa-truck"></i></button>
        <div class="note-dropdown-menu dropdown-menu note-check" role="list">
            <a class="dropdown-item" href="#" onclick="addMoreOptions('web_tracker_link')" role="listitem">
                Link to Web Tracker
            </a>
            <a class="dropdown-item" href="#" onclick="addMoreOptions('qr_ir')" role="listitem" data-border="top">
                QR code (inline remote)
            </a>
            <a class="dropdown-item" href="#" onclick="addMoreOptions('qr_b64')" role="listitem">
                QR code (inline Base64)
            </a>
            <a class="dropdown-item" href="#" onclick="addMoreOptions('qr_att')" role="listitem">
                QR code (inline attachment)
            </a>
            <a class="dropdown-item" href="#" onclick="addMoreOptions('bar_ir')" role="listitem" data-border="top">
                Bar code (inline remote)
            </a>
            <a class="dropdown-item" href="#" onclick="addMoreOptions('bar_b64')" role="listitem">
                Bar code (inline Base64)
            </a>
            <a class="dropdown-item" href="#" onclick="addMoreOptions('bar_att')" role="listitem">
                Bar code (inline attachment)
            </a>
        </div>
    </div>`; 

$('#summernote').summernote({
    popover: { image: [], },
    height: 500,
    codeviewFilter: false,
    lang: 'en-UK',
    cache: false,
    defaultFontName: 'Arial',
    disableDragAndDrop:true,
    toolbar: [
        ['style', ['style']],
        ['fontname', ['fontname','fontsize','color']],
        ['style', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['hr', ['hr','table','height']],
        ['custButton', ['media']],
        ['view', ['fullscreen', 'codeview',]],
        ['custButton', ['moretools']],
    ],
    fontNames: [ 'Arial', 'Serif', 'Sans', 'Arial Black', 'Courier', 'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Sacramento'],
    fontNamesIgnoreCheck: ['Arial', 'Serif', 'Sans', 'Arial Black', 'Courier', 'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Sacramento'],
    codemirror: {
        mode: 'text/html',
        htmlMode: true,
        lineNumbers: true,
        lineWrapping: true,
    },

    buttons: {
        media: bt_media,
        moretools: bt_more_tools
    },
}).on("summernote.enter", function(we, e) {
    $(this).summernote("pasteHTML", "<br><br>");
    e.preventDefault();
});

$('#summernote').summernote('code', 'HTML contents here...');
$("#lb_lp_id").text(nextRandomId);

$(function() {
    loadTableLandPageList();
    $("#web_tracker_selector").select2({
        minimumResultsForSearch: -1,
    }); 

    $.post({
        url: "../manager/web_tracker_generator_list_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_link_to_web_tracker"
        })
    }).done(function (data) {
        if(data.error)
            $("#web_tracker_selector").append("<option value='Empty'>Empty</option>");
        else
            $.each(data, function() {
                $("#web_tracker_selector").append("<option value='" + this.first_page + "'>" + this.tracker_name + "</option>");
            });
    }); 

    $('#summernote').parent().find('.note-editable').click(function(){
        g_deny_navigation = '';
    });
});


function loadTableLandPageList() {
    try {
        dt_landpage_list.destroy();
    } catch (err) {}
    $('#table_landpage_list tbody > tr').remove();

    $.post({
        url: "manager/sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_landpage_list",
        })
    }).done(function (data) {
        if(!data.error){
            $.each(data, function(key, value) {
                var action_items = `<div class="d-flex no-block align-items-center"><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="View/Edit" onClick="viewLandPageDetailsFromId('` + value.hlp_id + `',false)"><i class="mdi mdi-eye"></i></button><button type="button" class="btn btn-success btn-sm mdi mdi-content-copy" data-toggle="tooltip" title="Copy direct access link" onClick="copyAccessLink($(this),'` + value.page_file_name + `')"></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Delete" onClick="promptLandPageDeletion('` + value.hlp_id + `')"><i class="mdi mdi-delete-variant"></i></button>`;

                $("#table_landpage_list tbody").append("<tr><td></td><td>" + value.page_name + "</td><td>" + value.page_file_name +"</td><td data-order=\"" + getTimestamp(value.date) + "\">" + value.date + "</td><td>" + action_items + "</td></tr>");
            });
        }

        dt_landpage_list = $('#table_landpage_list').DataTable({
            "bDestroy": true,
            'columnDefs': [{
                "targets": 4,
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_landpage_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_landpage_list tbody').fadeIn(500);
                $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            },

            "initComplete": function() {
                $('label>select').select2({minimumResultsForSearch: -1, });
            }
        }); //initialize table

        dt_landpage_list.on('order.dt_landpage_list search.dt_landpage_list', function() {
            dt_landpage_list.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();        
    });
}


function saveLandPage(e) {
    if(validateFields() == false)
        return;

    var page_name = $('#tb_page_name').val();
    var page_file_name = $('#tb_page_file_name').val();
    var page_content = $('#summernote').summernote('code');    

    enableDisableMe(e);
    $.post({
        url: "manager/sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "save_landpage",
            hlp_id: nextRandomId,
            page_name: page_name,
            page_file_name: page_file_name,
            page_content: btoa(page_content)
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Saved successfully!');
            window.history.replaceState(null,null, location.pathname + '?lp=' + nextRandomId);
            loadTableLandPageList();
            generateAccessLink(page_file_name);
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function validateFields(){
    if($('#tb_page_name').val() == ''){
        $("#tb_page_name").addClass("is-invalid");
        return false;
    } else
        $("#tb_page_name").removeClass("is-invalid");

    if($('#tb_page_file_name').val() == ''){
        $("#tb_page_file_name").addClass("is-invalid");
        return false;
    } else
        $("#tb_page_file_name").removeClass("is-invalid");
    return true;
}

function viewLandPageDetailsFromId(hlp_id,quite) {
    $.post({
        url: "manager/sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_landpage_details_from_id",
            hlp_id: hlp_id,
         })
    }).done(function (data) {
        if(!data.error){
            nextRandomId = hlp_id;
            $("#lb_ht_id").text(hlp_id);
            $("#tb_page_name").val(data.page_name);
            $("#tb_page_file_name").val(data.page_file_name);
            $('#summernote').summernote('code', data.page_content);
            generateAccessLink(data.page_file_name);

            window.history.replaceState(null,null, location.pathname + '?lp=' + hlp_id);
            $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' });
        }
        else
            toastr.error('', data.error);
    }); 
}

function promptLandPageDeletion(ht_id) {
    g_modalValue = ht_id;
    $('#modal_lp_delete').modal('toggle');
}

function landPageDeletionAction() {
    $.post({
        url: "manager/sniperhost_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_landpage",
            hlp_id: g_modalValue,
         })
    }).done(function (response) {
        if(response.result == "success"){
            $('#modal_lp_delete').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            loadTableLandPageList();
            if(nextRandomId == g_modalValue)
                window.history.replaceState(null,null, location.pathname);
        }
        else
            toastr.error("", response.error);
    }); 
}

function generateAccessLink(page_file_name){
    if(validateFields()){
        $("#link_output").text(window.location.origin + '/spear/sniperhost/lp_pages/' + page_file_name);
        Prism.highlightAll();
    }
}

function copyAccessLink(e, page_file_name){
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(window.location.origin + '/spear/sniperhost/lp_pages/' + page_file_name).select();
    document.execCommand("copy");
    $temp.remove();

    e.attr('data-original-title', 'Copied!').tooltip('show');
    e.text('Copied');
    window.setTimeout(function() {
        e.tooltip('hide').attr('data-original-title', 'Copy direct access link');
        e.text('');
    }, 2000);
}

function addMoreOptions(val){
    if(val == "web_tracker_link")
        $('#modal_web_tracker_selection').modal('toggle');
    else
        $('#summernote').summernote('pasteHTML', `<img src="` + location.protocol + `//` + document.domain + `/mod?type=` + val + `&content=<your text here>&img_name=code.png"></img>`);
}

function linkWebTracker(){
    $('#summernote').summernote('restoreRange');
    var url = $("#web_tracker_selector").val();
    if(url == "Empty")
        toastr.error('', 'Error: Please create web tracker first');
    else{
        switch($("#web_tracker_style_selector").val()){
            case "1": $('#summernote').summernote('pasteHTML', `<a href="` + url + "?rid={{RID}}" + `">` + url + "?rid={{RID}}" + `</a>`); break;
            case "2": $('#summernote').summernote('pasteHTML', `<a href="` + url + "?rid={{RID}}" + `">` + url + `</a>`); break;
            case "3": $('#summernote').summernote('pasteHTML', `<span>Please visit our website <a href="` + url + "?rid={{RID}}" + `">here</a></span>`); break;
        }
    }
    $('#modal_web_tracker_selection').modal('toggle');
}

function insertMedia(type){    
    $('#summernote').summernote('restoreRange');
    if(type == "link")
    {
        if(isValidURL($("#modal_media_link_url").val())){
            $("#modal_media_link_url").removeClass("is-invalid");
            $('#summernote').summernote('pasteHTML', `<a href="` + $("#modal_media_link_url").val() + `">` + ($("#modal_media_link_text").val().trim()?$("#modal_media_link_text").val():$("#modal_media_link_url").val()) + `</a>`);
            $('#modal_media_link').modal('toggle');
        }
        else
            $("#modal_media_link_url").addClass("is-invalid");           

    }
    else
    if(type == "pic")
    {
        if(isValidURL($("#modal_media_pic_url").val())){
            $("#modal_media_pic_url").removeClass("is-invalid");
            $('#summernote').summernote('pasteHTML', `<img src="` + $("#modal_media_pic_url").val() + `"></img>`);
            $('#modal_media_pic').modal('toggle');
        }
        else
            $("#modal_media_pic_url").addClass("is-invalid");           

    }
    else
    if(type == "video"){
        if(isValidURL($("#modal_media_video_url").val())){
            $("#modal_media_video_url").removeClass("is-invalid");
            $('#summernote').summernote('pasteHTML', `<video controls="" src="` + $("#modal_media_video_url").val() + `" style="width: 600px;">Browser not supported HTML5 video.</video>`);
            $('#modal_media_video').modal('toggle');
        }
        else            
            $("#modal_media_video_url").addClass("is-invalid");
    }
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