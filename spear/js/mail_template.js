var dt_mail_template_list, store_info;
var g_tracker_image_type = ''; //0=no tracker image, 1= default tracker image, 2= custom tracker image
var g_sender_list;

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


$(function() {
    $('.accordion .panel-default .panel-heading').click(function (e) {
        $(this).parent().find('.panel-collapse').collapse('toggle');
    });

    $("#mail_content_type_selector").select2({
        minimumResultsForSearch: -1,
    }); 
    $("#mail_sender_selector").select2({
        minimumResultsForSearch: -1,
    }); 

    $("#web_tracker_selector").select2({
        minimumResultsForSearch: -1,
    }); 

    $("#web_tracker_style_selector").select2({
        minimumResultsForSearch: -1,
    }); 

    $("#selector_sample_mailtemplates").select2({
        minimumResultsForSearch: -1,
    }); 

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_sender_list"
         })
    }).done(function (data) {
        if(!data.error){
            g_sender_list = data;
            $.each(g_sender_list, function() {
                $("#mail_sender_selector").append("<option value='" + this.sender_list_id + "'>" + this.sender_name + "</option>");
            });
        }
    });

    $.post({
        url: "web_tracker_generator_list_manager",
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
});

function addRemoveTrackerImage(mode){
    if(mode == "addDefault"){
        if(getTrackerImageType() == 2) // custom tracker            
            $('#summernote').summernote('code', $('#summernote').summernote('code').replace(/<img [^>]*src="http:*\/\/.*\/tmail\?.*?>/gi, "{{TRACKER}}")); //replace custom
        else
        if(getTrackerImageType() == 0) // no tracker
            $('#summernote').summernote('code', $('#summernote').summernote('code') + "{{TRACKER}}");
        $("#lb_tracker_image").text("Default tracker added");
        g_tracker_image_type = 1;
    }
    else
    if(mode == "addCustom"){        
        $("#tracker-img-uploader").trigger("click"); 
    }
    else{
        $('#summernote').summernote('code', $('#summernote').summernote('code').replace(/<img [^>]*src="http:*\/\/.*\/tmail\?.*?>/gi, "").replace("{{TRACKER}}",""));
        $("#lb_tracker_image").text("None added");
        g_tracker_image_type = 0;
    }
}

function uploadTrackerImage(fname,fsize,ftype,fb64){
    if (fsize > 1024*1024*4) {
        toastr.error('', 'File size exceeded. Max image size is 4MB');
        return;
    }
    if (!ftype.startsWith("image")) {
        toastr.error('', 'File is not an image');
        return;
    }

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "upload_tracker_image",
            mail_template_id: nextRandomId,
            file_name: fname,
            file_b64: fb64,
        })
    }).done(function (response) {
        if(response.result == "success"){
            if(getTrackerImageType() == 2){ // custome tracker
                $('#summernote').summernote('code', $('#summernote').summernote('code').replace(/<img [^>]*src="http:*\/\/.*\/tmail\?.*?\"/gi, `<img src="` + location.protocol + `//` + document.domain + `/tmail?mid={{MID}}&cid={{CID}}&mtid=` + nextRandomId + "_" + Math.floor(Math.random()*9999) + "\""));
                $("#lb_tracker_image").text("Default tracker added");
             }
            else{
                if(getTrackerImageType() == 1){ // default tracker
                    $('#summernote').summernote('code', $('#summernote').summernote('code').replace("{{TRACKER}}", `<img src="` + location.protocol + `//` + document.domain + `/tmail?mid={{MID}}&cid={{CID}}&mtid=` + nextRandomId + "_" + Math.floor(Math.random()*9999) + `"></img>`));
                    $("#lb_tracker_image").text("Custom tracker added");
                }
                else    //no tracker case
                    $('#summernote').summernote('code', $('#summernote').summernote('code') + `<img src="` + location.protocol + `//` + document.domain + `/tmail?mid={{MID}}&cid={{CID}}&mtid=` + nextRandomId + "_" + Math.floor(Math.random()*9999) + `"></img>`);
            }
        }
        else
            toastr.error('', 'Error uploading image!<br/>' + response.error);
    }); 
}

function getTrackerImageType(){
    if($('#summernote').summernote('code').includes("{{TRACKER}}"))
        return 1; //default
    else
    if($('#summernote').summernote('code').includes(`<img src="` + location.protocol + `//` + document.domain + `/tmail?`))
        return 2; //custom
    else
        return 0; //no tracker image
}

function saveMailTemplate(e) {
    if (RegTest($('#mail_template_name').val(), "COMMON") == false) {
        $("#mail_template_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#mail_template_name").removeClass("is-invalid");

    mail_template_content = $('#summernote').summernote('code');

    enableDisableMe(e);
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "save_mail_template",
            mail_template_id: nextRandomId,
            mail_template_name: $('#mail_template_name').val(),
            mail_template_subject: $('#mail_template_subject').val(),
            mail_template_content: mail_template_content,
            timage_type: getTrackerImageType(),
            attachments: catchAttachments(),
            mail_content_type: $('#mail_content_type_selector').val()
        })
    }).done(function (response) {
        if(response.result == "success"){
            if(getTrackerImageType() == 0)
                toastr.warning('', 'No tracker detected. Template saved!');
            else
                toastr.success('', 'Saved successfully!');
            window.history.replaceState(null,null, location.pathname + '?action=edit&template=' + nextRandomId);
        }
        else
            toastr.error('', 'Error saving data!<br/>' + response.error);
        enableDisableMe(e);
    }); 
}

function getMailTemplateFromTemplateId(id) {
    if (id == "new") {
        getRandomId();
        return;
    } else
        nextRandomId = id;

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_mail_template_from_template_id",
            mail_template_id: id,
        })
    }).done(function (data) {
        $('#mail_template_name').val(data.mail_template_name);
        $('#mail_template_subject').val(data.mail_template_subject);
        $('#summernote').summernote('code', data.mail_template_content);//.replace(/\n/gi, "<br/>"));
        $("#mail_content_type_selector").val(data.mail_content_type).trigger("change");
        $.each(data.attachment, function(i,file) {
            addAttachmentLabel(file.file_id, file.file_name, file.file_disp_name, file.inline);
        });

        if(data.timage_type == 0)
            $("#lb_tracker_image").text("None added");
        else
        if(data.timage_type == 1)
            $("#lb_tracker_image").text("Default tracker added");
        else
            $("#lb_tracker_image").text("Custom tracker added");
        triggerAttachmentChanges();
    }); 
}

function promptMailTemplateDeletion(id) {
    globalModalValue = id;
    $('#modal_email_template_delete').modal('toggle');
}

function mailTemplateDeletionAction() {
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_mail_template_from_template_id",
            mail_template_id: globalModalValue
        })
    }).done(function (response) {
       if(response.result == "success"){
            $('#modal_email_template_delete').modal('toggle');
            toastr.success('', 'Deleted successfully!');
            dt_mail_template_list.destroy();
            $("#table_mail_template_list tbody > tr").remove();
            loadTableMailTemplateList();
        }
        else
            toastr.error("", "Error deleting data!<br/>" + response.error);
    }); 
}

function promptMailTemplateCopy(id) {
    globalModalValue = id;
    $('#modal_mail_template_copy').modal('toggle');
}

function mailTemplateCopy() {
    if (RegTest($('#modal_new_mail_template_name').val(), "COMMON") == false) {
        $("#modal_new_mail_template_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#modal_new_mail_template_name").removeClass("is-invalid");

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "make_copy_mail_template",
            mail_template_id: globalModalValue,
            new_mail_template_id: getRandomId(),
            new_mail_template_name: $("#modal_new_mail_template_name").val()
         })
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Copy success!');
            $('#modal_mail_template_copy').modal('toggle');
            dt_mail_template_list.destroy();
            $("#table_mail_template_list tbody > tr").remove();
            loadTableMailTemplateList();
        }
        else
            toastr.error("", "Error making copy!<br/>" + response.error);
    }); 
}

function loadTableMailTemplateList() {
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_mail_template_list"
         })
    }).done(function (data) {
           if(!data['error']){  // no data
            $.each(data, function(key, value) {
                var action_items_mail_template_table = `<div class="d-flex no-block align-items-center"><button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="document.location='MailTemplate?action=edit&template=` + value.mail_template_id + `'" title="View/Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Copy" onclick="promptMailTemplateCopy('` + value.mail_template_id + `')"><i class="mdi mdi-content-copy"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Delete" onclick="promptMailTemplateDeletion('` + value.mail_template_id + `')"><i class="mdi mdi-delete-variant"></i></button></div>`;

                var is_attachment =  Object.keys(value.attachment).length>0? "<i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span>" : "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span>";
                $("#table_mail_template_list tbody").append("<tr><td></td><td>" + value.mail_template_name + "</td><td>" + value.mail_template_subject + "</td><td>" + $('<div>').text(value.mail_template_content).html() + "...</td><td>" + is_attachment + "</td><td data-order=\"" + UTC2LocalUNIX(value.date) + "\">" + UTC2Local(value.date) + "</td><td>" + action_items_mail_template_table + "</td></tr>");
            });
        }
        
        dt_mail_template_list = $('#table_mail_template_list').DataTable({
            "bDestroy": true,
            "aaSorting": [5, 'desc'],
            'columnDefs': [{
                "targets": [4,6],
                "className": "dt-center"
            }],
            "preDrawCallback": function(settings) {
                $('#table_mail_template_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_mail_template_list tbody').fadeIn(500);
            }
        }); //initialize table

        dt_mail_template_list.on('order.dt_mail_template_list search.dt_mail_template_list', function() {
            dt_mail_template_list.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();
        $("label>select").select2({
            minimumResultsForSearch: -1,
        });

        $('[data-toggle="tooltip"]').tooltip({
            trigger : 'hover'
        })  
    });   
}

//-----------------Start Attachment Manager------------------------
function uploadAttachments(fname,fsize,ftype,fb64){    
    if (fsize > 1024*1024*15) {
        toastr.error('', 'File size exceeded. Max image size is 15MB');
        return;
    }

    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "upload_attachments",
            mail_template_id: nextRandomId,
            file_name: fname,
            file_b64: fb64,
        })
    }).done(function (response) {
        if(response.result == "success"){
            addAttachmentLabel(response.file_id, fname, fname, false);                
            triggerAttachmentChanges();      
        }
        else
            toastr.error('', 'Error uploading file!<br/>' + response.error);
    }); 
}

function catchAttachments(){
    var attachments = [];
    $($("#attachments_area").children()).each(function(){
        $(this).data('att_info').inline=$(this).find('input[name="cb_att_inline"]').is(':checked');
        $(this).data('att_info').file_disp_name=$(this).find('input[name="disp_name"]').val();
        attachments.push($(this).data('att_info'));
    });
    return attachments;
}

function addAttachmentLabel(file_id,file_name, file_disp_name, inline){
    if(inline)
        var inline_attr = 'checked';
    else
        var inline_attr = '';

    $("#attachments_area").append(`<div class="row">
                                    <div class="col-md-5">
                                       <div class="form-control alert alert-success alert-rounded ">
                                          <i class="mdi mdi-attachment m-r-5"></i> 
                                          <span>` + file_name + `</span>
                                       </div>
                                    </div>
                                    <div class="col-md-5">
                                       <input type="text" name="disp_name" class="form-control" placeholder="Name to show" value="` + file_disp_name + `">
                                    </div>
                                    <div class="custom-control custom-switch col-md-1 m-t-5 text-right">
                                     <label class="switch">
                                         <input type="checkbox" name="cb_att_inline" ` + inline_attr + `>
                                         <span class="slider round" data-toggle="tooltip" title="Inline attachment" data-placement="top"></span>
                                     </label>
                                   </div>
                                   <div class="col-md-1 text-right">
                                       <button type="button" class="btn btn-danger btn-sm" title="Remove attachment" data-toggle="tooltip" onclick="removeAttachment(this)"><i class="mdi mdi-close"></i></button>
                                    </div>
                                 </div>`);
    $("#attachments_area").children(":last").data('att_info', { file_id: file_id, file_name: file_name});   //remaining info is added from catchAttachments()
    $('[data-toggle="tooltip"]').tooltip();
}

function removeAttachment(e){
    $('[data-toggle="tooltip"]').tooltip("hide");
    $(e).closest('.row').remove();
    triggerAttachmentChanges();
}
function triggerAttachmentChanges(){
    setTimeout(function() { //fun requires to update count
        $("#lb_attachment_count").text('Attachments (' + $("#attachments_area>div").length + '):');
    }, 100);
    
}
//-----------------End Attachment Manager------------------------
function uploadMailBodyFiles(fname,fsize,ftype,fb64,el){   
    if (fsize > 1024*1024*15) {
        toastr.error('', 'File size exceeded. Max image size is 15MB');
        return;
    }

    $(el).closest('.modal-content').find('.modal-footer').append(displayLoader("Uploading...","small"))
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "upload_mail_body_files",
            mail_template_id: nextRandomId,
            file_name: fname,
            file_b64: fb64,
        })
    }).done(function (response) {
        if(response.result == "success"){
            if(fb64.split(',')[0].includes("data:image"))
                $('#summernote').summernote('pasteHTML', `<img src="` + location.protocol + '//' + document.domain + '/mod?mbf=' + response.mbf + `"></img>`);
            else
                $('#summernote').summernote('pasteHTML', `<video controls="" src="` + location.protocol + '//' + document.domain + '/mod?mbf=' + response.mbf + `" style="width: 600px;">Browser not supported HTML5 video.</video>`);
            $(el).closest('.modal').modal('toggle');
        }
        else
            toastr.error('', 'Error uploading file!<br/>' + response.error);
        $(el).closest('.modal-content').find('.loader').remove();
    }); 
}

function linkWebTracker(){
    $('#summernote').summernote('restoreRange');
    var url = $("#web_tracker_selector").val();
    if(url == "Empty")
        toastr.error('', 'Error: Please create web tracker first');
    else{
        switch($("#web_tracker_style_selector").val()){
            case "1": $('#summernote').summernote('pasteHTML', `<a href="` + url + "?cid={{CID}}" + `">` + url + "?cid={{CID}}" + `</a>`); break;
            case "2": $('#summernote').summernote('pasteHTML', `<a href="` + url + "?cid={{CID}}" + `">` + url + `</a>`); break;
            case "3": $('#summernote').summernote('pasteHTML', `<span>Please visit our website <a href="` + url + "?cid={{CID}}" + `">here</a></span>`); break;
        }
    }
    $('#modal_web_tracker_selection').modal('toggle');
}
//---------------------------------------------------------

function modalTestDeliveryAction(e){
    var test_to_address = $("#modal_mail_sender_test_mail_to").val();
    var sender_data;

    if($("#mail_sender_selector").val() == null){
        toastr.error('', 'No sender list created. Unable to send mail');
        return;
    }

    if (RegTest(test_to_address, "EMAIL") == false) {
        $("#modal_mail_sender_test_mail_to").addClass("is-invalid");
        toastr.error('', 'Empty/unsupported character!');
        return;
    } else
        $("#modal_mail_sender_test_mail_to").removeClass("is-invalid");

    if($('#mail_content_type_selector').val() == 'text/html')
        mail_template_content = $('#summernote').summernote('code');
    else
        mail_template_content = $('#summernote').summernote('code').replace(/<\/p>|<br\/?>/gi, "\n").replace(/<\/?[^>]+(>|$)/g, "");    //text

    $.each(g_sender_list, function() {
        if(this.sender_list_id == $("#mail_sender_selector").val())
            sender_data = this;
    });

    enableDisableMe(e);
    $.post({
        url: "userlist_campaignlist_mailtemplate_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "send_test_mail_sample",
            sender_list_id: sender_data.sender_list_id,
            smtp_server: sender_data.sender_SMTP_server,
            sender_from: sender_data.sender_from,
            sender_username: sender_data.sender_acc_username,
            sender_pwd: "",
            cust_headers: sender_data.cust_headers,
            test_to_address: test_to_address,
            smtp_enc_level: sender_data.smtp_enc_level,
            mail_subject: $('#mail_template_subject').val(),
            mail_body: mail_template_content,         
            attachments: catchAttachments(),
            mail_content_type: $('#mail_content_type_selector').val()
         })
    }).done(function (response) {
        if(response.result == "success")
                toastr.success('', 'Success. Check your inbox!');
        else
            toastr.error('', 'Error sending mail!<br/>' + response.error);
        $('#modal_test_mail').modal('toggle');
        enableDisableMe(e);
    }); 
}

//---------------------------------------------------

$('#summernote').summernote({
    popover: { image: [], },
    height: 400,
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
$('#summernote').summernote('fontName', 'Arial');
$('#summernote').summernote('reset');
$(window).scrollTop(0); //avoids focus
$('.dropdown-tooltip').tooltip(); //add tooltip for custom menu

function addMoreOptions(val){
    if(val == "web_tracker_link")
        $('#modal_web_tracker_selection').modal('toggle');
    else
        $('#summernote').summernote('pasteHTML', `<img src="` + location.protocol + `//` + document.domain + `/mod?type=` + val + `&content=<your text here>&img_name=code.png"></img>`);
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
//------------------Store section---------------

function getStoreList(){
    $.post({
        url: "settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_store_list",
            type: "mail_template",
         })
    }).done(function (data) {
        if(!data['error']){  // no data
            store_info = data;
            $.each(data, function(name) {
                $("#selector_sample_mailtemplates").append("<option value='" + name + "'>" + name + "</option>");
            });
            $('#selector_sample_mailtemplates').trigger("change");    
        }
    }); 
}

$('#selector_sample_mailtemplates').on('change', function() {
    $("#lb_selector_common_mail_sender_note").html(store_info[this.value].disp_note);
});

function insertMailTemplate(){
    $.post({
        url: "settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_store_list",
            type: "mail_template",
            name: $("#selector_sample_mailtemplates").val()
         })
    }).done(function (data) {
        if(!data.error){ 
            $('#mail_template_name').val($("#selector_sample_mailtemplates").val());
            $('#mail_template_subject').val(data.mail_template_subject);
            $('#summernote').summernote('code', data.mail_template_content.replace("http://localhost", location.protocol + `//` + document.domain));
            $("#mail_content_type_selector").val(data.mail_content_type).trigger("change");

            if(data.timage_type == 0)
                $("#lb_tracker_image").text("None added");
            else
            if(data.timage_type == 1)
                $("#lb_tracker_image").text("Default tracker added");
            else
                $("#lb_tracker_image").text("Custom tracker added");
            triggerAttachmentChanges();
            $('#ModalStore').modal('toggle');
        }
    }); 
}