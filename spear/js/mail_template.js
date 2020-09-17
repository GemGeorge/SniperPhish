var dt_mail_template_list;
var cust_timage = ''; //0=no tracker image, 1= default tracker image, 2= custom tracker image

$(function() {
    $("#bt_timage_delete").hide();

     $('#summernote').summernote({
        popover: { image: [], },
        height: 400,
        codeviewFilter: false,
        focus: true,
        lang: 'en-UK',
        cache: false,
        defaultFontName: 'Arial',
        toolbar: [
         ['style', ['style']],
         ['style', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
         ['fontname', ['fontname','fontsize']],
         ['color', ['color']],
         ['para', ['ul', 'ol', 'paragraph']],
         ['table', ['table']],
         ['insert', ['link', 'picture', 'video', 'hr']],
         ['height', ['height']],
         ['view', ['fullscreen', 'codeview',]],
        ],
        fontNames: [ 'Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Sacramento'],
        fontNamesIgnoreCheck: [ 'Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Sacramento'],
     });


    var noteBtn = `<div class="note-btn-group btn-group">
      <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-tooltip" tabindex="-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-placement="bottom" title="Insert"><i class="fa fa-long-arrow-alt-down fa-rotate-45" ></i> </button>
       <ul class="dropdown-menu">
          <li role="listitem"><a href="#" onclick="addTrackerImage('default')">Default tracker image</a></li>
          <li role="listitem"><a href="#" onclick="addTrackerImage('custom')">Custom tracker image</a></li>
          <li role="listitem"><a href="#" onclick="addBarcodeImage()">Barcode image</a></li>
          <li role="listitem"><a href="#" onclick="addQRImage()">QR image</a></li>
       </ul>
    </div>`;  

    var fileGroup = '' + noteBtn + '';
    $(fileGroup).appendTo($('.note-toolbar'));
    $('.dropdown-tooltip').tooltip({ trigger: "hover" });

    $('.accordion .panel-default .panel-heading').click(function (e) {    
        if($('#collapseOne').hasClass('show'))
            $('.accordion').removeClass("according-manage");
        else
            $('.accordion').addClass("according-manage");

        $('.accordion .panel-collapse').collapse('toggle');
    });

    $("#mail_content_type_selector").select2({
        minimumResultsForSearch: -1,
    }); 
});

function saveMailTemplate(e) {
    var attachment = {};
    if (!$('#mail_template_name').val().match(/^[a-z\d\-_\s]+$/i)) {
        $("#mail_template_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#mail_template_name").removeClass("is-invalid");

    if($('#mail_content_type_selector').val() == 'text/html')
        mail_template_content = btoa($('#summernote').summernote('code'));
    else
        mail_template_content = btoa($('#summernote').summernote('code').replace(/<\/p>|<br\/?>/gi, "\n").replace(/<\/?[^>]+(>|$)/g, ""));    //text

    $.each($("#attachments_area").children(), function(key, value) {
        attachment[$(value).attr('id').split(',')[0]] = $(value).attr('id').split(',')[1];
    });

    enableDisableMe(e);
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "save_mail_template",
            mail_template_id: nextRandomId,
            mail_template_name: $('#mail_template_name').val(),
            mail_template_subject: btoa($('#mail_template_subject').val()),
            mail_template_content: mail_template_content,
            cust_timage: cust_timage,
            attachment: btoa(JSON.stringify(attachment)),
            mail_content_type: $('#mail_content_type_selector').val()
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Saved successfully!');
            } else
                toastr.error('', data);
            enableDisableMe(e);
        });
}

function getMailTemplateFromTemplateId(id) {
    if (id == "new") {
        getRandomId();
        return;
    } else
        nextRandomId = id;

    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "get_mail_template_from_template_id",
            mail_template_id: id,
        },
        function(data, status) {
            $('#mail_template_name').val(data['mail_template_name']);
            $('#mail_template_subject').val(data['mail_template_subject']);
            $('#summernote').summernote('code', data['mail_template_content'].replace(/\n/gi, "<br/>"));
            $("#mail_content_type_selector").val(data['mail_content_type']).trigger("change");
            $.each(JSON.parse(data['attachment']), function(file_id,file_name) {
                addAttachmentLabel(file_id,file_name);
            });
            triggerAttachmentChanges();
        });
}

function promptMailTemplateDeletion(id) {
    globalModalValue = id;
    $('#modal_email_template_delete').modal('toggle');
}

function mailTemplateDeletionAction() {
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "delete_mail_template_from_template_id",
            mail_template_id: globalModalValue
        },
        function(data, status) {
            if (data == "deleted") {
                $('#modal_email_template_delete').modal('toggle');
                toastr.success('', 'Deleted successfully!');
                dt_mail_template_list.destroy();
                $("#table_mail_template_list tbody > tr").remove();
                loadTableMailTemplateList();
            } else {
                toastr.error('', 'Error deleting data!');
            }
        });
}

function promptMailTemplateCopy(id) {
    globalModalValue = id;
    $('#modal_mail_template_copy').modal('toggle');
}

function mailTemplateCopy() {
    if (!$('#modal_new_mail_template_name').val().match(/^[a-z\d\-_\s]+$/i)) {
        $("#modal_new_mail_template_name").addClass("is-invalid");
        toastr.error('', 'Empty/Unsupported character!');
        return;
    } else
        $("#modal_new_mail_template_name").removeClass("is-invalid");

    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "make_copy_mail_template",
            mail_template_id: globalModalValue,
            new_mail_template_id: getRandomId(),
            new_mail_template_name: $("#modal_new_mail_template_name").val()
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Copy success!');
                $('#modal_mail_template_copy').modal('toggle');
                dt_mail_template_list.destroy();
                $("#table_mail_template_list tbody > tr").remove();
                loadTableMailTemplateList();
            } else
                toastr.error('', 'Error making copy!');
        });
}

function addTrackerImage(type) {
    if (type == 'default')
        $('#summernote').summernote('code', $('#summernote').summernote('code') + `<img src="` + location.protocol + `//` + document.domain + `/trackmail?mid={{MID}}&cid={{CID}}"></img>`);
    else {
        globalModalValue = nextRandomId;
        $('#modal_tracker_image_uploader').modal('toggle');
    }
}

function addQRImage() {
     $('#summernote').summernote('code', $('#summernote').summernote('code') + `<img src="` + location.protocol + `//` + document.domain + `/mod?type=qr&content=<your text content here>"></img>`);
}

function addBarcodeImage() {
     $('#summernote').summernote('code', $('#summernote').summernote('code') + `<img src="` + location.protocol + `//` + document.domain + `/mod?type=bar&content=<your numeric content here>"></img>`);
}

function loadTableMailTemplateList() {
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "get_mail_template_list"
        },
        function(data, status) {     a=data;
            $.each(data, function(key, value) {
                var action_items_mail_template_table = `<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" onclick="document.location='MailTemplate?action=edit&template=` + value['mail_template_id'] + `'" title="View/Edit"><i class="mdi mdi-pencil"></i></button><button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Copy" onclick="promptMailTemplateCopy('` + value['mail_template_id'] + `')"><i class="mdi mdi-content-copy"></i></button><button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Delete" onclick="promptMailTemplateDeletion('` + value['mail_template_id'] + `')"><i class="mdi mdi-delete-variant"></i></button>`;
                var is_attachment =  Object.keys(JSON.parse(value['attachment'])).length>0? "<i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span>" : "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span>";
                $("#table_mail_template_list tbody").append("<tr><td></td><td>" + value['mail_template_name'] + "</td><td>" + value['mail_template_subject'] + "</td><td>" + $('<div>').text(value['mail_template_content']).html() + "</td><td>" + is_attachment + "</td><td data-order=\"" + UTC2LocalUNIX(value['date']) + "\">" + UTC2Local(value['date']) + "</td><td>" + action_items_mail_template_table + "</td></tr>");
            });

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

            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }).fail(function() {
        toastr.error('', 'Error getting template list!');
    });
}

//-------------------------------------
Dropzone.autoDiscover = false;
var dropzone = new Dropzone('#dropzone-img', {
    url: 'userlist_campaignlist_mailtemplate_manager',
    parallelUploads: 1,
    thumbnailHeight: 120,
    thumbnailWidth: 120,
    maxFilesize: 1,
    acceptedFiles: "image/*",
    init: function() {
        this.on("addedfile", function() {
            if (this.files[1] != null) {
                this.removeFile(this.files[0]);
            }
        });

        this.on("sending", function(file, xhr, formData) {
            formData.append("action_type", "upload_tracker_image"); // Append all the additional input data of your form here!
            formData.append("mail_template_id", nextRandomId);
        });

        this.on("success", function(file, data) {
            if (data == "success") {
                $("#bt_timage_delete").show();
                toastr.success('', 'Upload success!');
                $('#summernote').summernote('code', $('#summernote').summernote('code') + `<img src="` + location.protocol + `//` + document.domain + `/trackmail?mid={{MID}}&cid={{CID}}&mtid=` + nextRandomId + `#` + Math.floor(Math.random() * 99999) + `"></img>`); // Random value in URL required. Otherwise summernote won't refresh image to latest uploaded
            } else
                toastr.error('', 'Upload error: ' + data);
        });
    }
});

function mailTemplateTrackerImageUpload() {
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "remove_tracker_image",
            mail_template_id: nextRandomId,
        },
        function(data, status) {
            if (data) {
                if (data == "success") {
                    toastr.warning('', 'Image deleted from server!');
                    toastr.info('', 'Please remove tracker image from body');
                    $("#bt_timage_delete").hide();
                    dropzone.removeAllFiles();
                } else
                    toastr.error('', 'Error deleting image!');
            }
        });

}


//-----------------Start Attachment Manager------------------------
var dropzone_attachment = new Dropzone('#attachment-form', {
    url: 'userlist_campaignlist_mailtemplate_manager',
    parallelUploads: 1,
    previewsContainer: false,
    maxFilesize: 1,
    init: function() {
        this.on("addedfile", function() {
            if (this.files[1] != null) {
                this.removeFile(this.files[0]);
            }
        });

        this.on("sending", function(file, xhr, formData) {
            formData.append("action_type", "upload_attachment"); 
            formData.append("mail_template_id", nextRandomId);
        });

        this.on("success", function(file, data) {
            if (data['resp'] == "success") {
                addAttachmentLabel(data['file_id'],this.files[0].name);                
                triggerAttachmentChanges();               
            } else
                toastr.error('', 'Upload error');
        });
    }
});

function deleteAttachment(e){
    $.post( "userlist_campaignlist_mailtemplate_manager", { 
        action_type: "delete_attachment", 
        mail_template_id: nextRandomId,
        file_name: e.parents().eq(1).attr('id').split(',')[0]
    },
    function(data, status) {
        triggerAttachmentChanges();
    });
}

function addAttachmentLabel(file_id,file_name){
    $("#attachments_area").append(`<div class="alert alert-success alert-rounded" id="` + file_id + ',' + file_name + `">
                                    <i class="mdi mdi-attachment m-r-5"></i> 
                                    <span>` + file_name + `</span>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true" onclick="deleteAttachment($(this))">×</span> </button>
                                 </div>`);
}

function triggerAttachmentChanges(){
    $("#lb_attachment_count").text('Attachments (' + $("#attachments_area div").length + '):');
}