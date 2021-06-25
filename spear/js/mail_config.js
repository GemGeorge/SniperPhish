var nextRandomId = getRandomId();
var configData;
resetconfigData();
getMcampConfigDetails("default");

function resetconfigData(){
    configData = {'mail_sign': {'cert':{}, 'pvk':{}}, 'mail_enc': {'cert':{}}};
}

$(document).on('click', '.number-spinner button', function () {    
   var btn = $(this),
      oldValue = btn.closest('.number-spinner').find('input').val().trim(),
      newVal = 0;
   
   if (btn.attr('data-dir') == 'up') {
      newVal = parseInt(oldValue) + 1;
   } else {
      if (oldValue > 1) {
         newVal = parseInt(oldValue) - 1;
      } else {
         newVal = 1;
      }
   }
   btn.closest('.number-spinner').find('input').val(newVal);
});


$("#cb_signed_mail").change(function() {
    if(this.checked)
        $("#area_signed_mail_bt").children().prop('disabled', false);
    else
        $("#area_signed_mail_bt").children().prop('disabled', true);
});

$("#cb_encrypted_mail").change(function() {
    if(this.checked)
        $("#area_enc_mail_bt").children().prop('disabled', false);
    else
        $("#area_enc_mail_bt").children().prop('disabled', true);
});

$("#selector_config_list").select2({
    minimumResultsForSearch: -1,
    templateResult: function (data, container) {
    if (data.element) {
      $(container).addClass($(data.element).attr("class"));
    }
    return data.text;
  }
});

$("#select_recipient_type").select2({
    minimumResultsForSearch: -1
});

$("#select_msg_priority").select2({
    minimumResultsForSearch: -1
});

$('#selector_config_list').on('change', function (e, data) {    //Avoid request upon auto selection change
   if(data != undefined && data.auto_trigger == true)
        return;
    else
        getMCampConfigFromConfigId($(this).val(),false);
});

function uploadSignMailCert(fname,fsize,ftype,fb64){
    if (fsize > 1024*1024*1) {
        toastr.error('', 'File size exceeded');
        return;
    }
    if (fname.split('.').pop().toLowerCase() != 'pem') {
        toastr.error('', 'Certificate is not in .pem format');
        return;
    }
    configData.mail_sign.cert = {'name':fname, 'fb64':fb64.substr(fb64.indexOf(',')+1)};
    addAttachmentLabels("#area_signed_mail_cert", "signed_mail_cert", fname);
}

function uploadSignMailPVK(fname,fsize,ftype,fb64){
    if (fsize > 1024*1024*1) {
        toastr.error('', 'File size exceeded');
        return;
    }

    if (fname.split('.').pop().toLowerCase() != 'pem') {
        toastr.error('', 'Certificate is not in .pem format');
        return;
    }

    configData.mail_sign.pvk = {'name':fname, 'fb64':fb64.substr(fb64.indexOf(',')+1)};
    addAttachmentLabels("#area_signed_mail_pvk", "signed_mail_pvk", fname);
}

function uploadEncMailCert(fname,fsize,ftype,fb64){
    if (fsize > 1024*1024*1) {
        toastr.error('', 'File size exceeded');
        return;
    }
    if (fname.split('.').pop().toLowerCase() != 'pem') {
        toastr.error('', 'Certificate is not in .pem format');
        return;
    }
    configData.mail_enc.cert = {'name':fname, 'fb64':fb64.substr(fb64.indexOf(',')+1)};
    addAttachmentLabels("#area_encrypted_mail", "encrypted_mail_cert", fname);
}

function addAttachmentLabels(area, type, fname){
    $(area).html(`<div class="alert alert-success alert-rounded">
                                    <i class="mdi mdi-attachment m-r-5 form-control-sm"></i> 
                                    <span>Certificate ` + fname + ` uploaded</span>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="removeUploads('` + type + `')"> <span aria-hidden="true">×</span> </button>
                                 </div>`);
}

function removeUploads(file){
    switch(file){
        case 'signed_mail_cert' : configData.mail_sign.cert = {}; break;
        case 'signed_mail_pvk' : configData.mail_sign.pvk = {}; break;
        case 'encrypted_mail_cert' : configData.mail_enc.cert = {}; break;
    }
}

function saveConfigAction(e) { 
    if(nextRandomId == "new")
        nextRandomId = getRandomId();   

    if (RegTest($('#tb_config_name').val(), "COMMON") == false) {
        $("#tb_config_name").addClass("is-invalid");
        if($('#modal_new_config').hasClass('show') == false)
            $('#modal_new_config').modal('toggle');
        return;
    } else{
        $("#tb_config_name").removeClass("is-invalid");
    }

    if($("#cb_signed_mail").is(':checked') == true){
        if($.isEmptyObject(configData.mail_sign.cert)){
            toastr.error('', 'Mail signing certificate not uploaded!');
            return;
        }
        if($.isEmptyObject(configData.mail_sign.pvk)){
            toastr.error('', 'Mail signing private key not uploaded!');
            return;
        }
    }
    else
        if($.isEmptyObject(configData.mail_sign.cert) || $.isEmptyObject(configData.mail_sign.pvk)){ //clear if both files are not uplaoded
            configData.mail_sign.cert = {};
            configData.mail_sign.pvk = {};
        }

    if($("#cb_encrypted_mail").is(':checked') == true && $.isEmptyObject(configData.mail_enc.cert)){
        toastr.error('', 'Mail encryption certificate not uploaded!');
        return;
    }

    configData.batch_mail_limit = $('#tb_batch_mail_limit').val();
    configData.recipient_type = $('#select_recipient_type').val();
    configData.read_receipt = $("#cb_read_receipt").is(':checked');
    configData.non_ascii_support = $("#cb_non_ascii_support").is(':checked');
    configData.signed_mail = $("#cb_signed_mail").is(':checked');
    configData.encrypted_mail = $("#cb_encrypted_mail").is(':checked');
    configData.antiflood = {"limit": $('#tb_antiflood_limit').val(), "pause": $('#tb_antiflood_pause').val()};
    configData.msg_priority = $("#select_msg_priority").val();

    enableDisableMe(e);
    $.post({
        url: "mail_campaign_config_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "save_mcamp_config",
            mconfig_id: nextRandomId,
            mconfig_name: $('#tb_config_name').val(),
            mconfig_data: configData
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            if($('#modal_new_config').hasClass('show'))   {     
                $('#modal_new_config').modal('toggle');
            }                
            toastr.success('', 'Saved successfully!');   
            getMcampConfigDetails(nextRandomId);       
        }
        else
            toastr.error('', 'Error saving data!');
        enableDisableMe(e);
    }); 
}

function deleteConfigAction(){
    $.post({
        url: "mail_campaign_config_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "delete_mcamp_config",            
            mconfig_id: nextRandomId
         })
    }).done(function (response) {
        if(response.result == "success"){
            toastr.success('', 'Configuration deleted successfully!');   
            $('#modal_config_delete').modal('toggle');     
            getMcampConfigDetails("default")      
            getMCampConfigFromConfigId("default")
        }
        else
            toastr.error('', 'Error deleting configuration!<br/>' + response.error);
    });
}

function getMcampConfigDetails(mconfig_id){
    $.post({
        url: "mail_campaign_config_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_mcamp_config_details"
         })
    }).done(function (response) {
        if(!response['result']){  // if not error response
            $("#selector_config_list").html(`<option value="new" class="ccl-2">New Configuration</option>`);
            $("#selector_config_list").append(`<option value="default" class="ccl-2">Default Configuration</option>`);
            $.each(response, function(i, data) {
                if(data.mconfig_id != "default")
                    $("#selector_config_list").append('<option value="' + data.mconfig_id + '">' + data.mconfig_name + '</option>');
            });         
            $('#selector_config_list').val(mconfig_id).trigger('change', [{auto_trigger:true}]); 
            window.history.replaceState(null,null, location.pathname + '?config=' + mconfig_id);
        }
    }).fail(function() {
        toastr.error('', 'Error getting configuration list!');
    });
}

function getMCampConfigFromConfigId(mconfig_id,quite) {
    var mconfig_id_tmp = mconfig_id;
    resetconfigData();
    fadeAni();
    $("#section_view_list").find('*').prop('disabled', false);

    if(mconfig_id == 'default')
        $("#bt_save_config").children().prop('disabled', true);
    else
        $("#bt_save_config").children().prop('disabled', false);

    if(mconfig_id == 'new'){
        mconfig_id = "default";
        $('#modal_new_config').modal('toggle');
        $('#tb_config_name').val('');
    }
    
    $.post({
        url: "mail_campaign_config_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_mcamp_config_details_from_id",
            mconfig_id: mconfig_id
         }),
    }).done(function (data) {
        configData = data.mconfig_data;
        nextRandomId = data.mconfig_id;
        $('#selector_config_list').val(data.mconfig_id).trigger('change', [{auto_trigger:true}]);

        $('#tb_batch_mail_limit').val(configData.batch_mail_limit);
        $('#select_recipient_type').val(configData.recipient_type).change();
        $('#cb_read_receipt').trigger('click').prop('checked', configData.read_receipt);       
        $('#cb_non_ascii_support').trigger('click').prop('checked', configData.non_ascii_support); 
        $('#cb_signed_mail').prop("checked", configData.signed_mail).trigger("change");     
        $('#cb_encrypted_mail').prop('checked', configData.encrypted_mail).trigger('change');   
        $('#tb_antiflood_limit').val(configData.antiflood.limit);
        $('#tb_antiflood_pause').val(configData.antiflood.pause);
        $('#select_msg_priority').val(configData.msg_priority).change();

        if(!($.isEmptyObject(configData.mail_sign.cert) || $.isEmptyObject(configData.mail_sign.pvk))){
            addAttachmentLabels("#area_signed_mail_cert", "signed_mail_cert", configData.mail_sign.cert.name);
            addAttachmentLabels("#area_signed_mail_pvk", "encrypted_mail_cert", configData.mail_sign.pvk.name);
        }
        else{
            $("#area_signed_mail_cert").empty();
            $("#area_signed_mail_pvk").empty();
        }
        if(!$.isEmptyObject(configData.mail_enc.cert))
            addAttachmentLabels("#area_encrypted_mail", "encrypted_mail_cert", configData.mail_enc.cert.name);
        else            
            $("#area_encrypted_mail").empty();

        if(mconfig_id_tmp == 'new'){
            $('#selector_config_list').val("new").trigger('change', [{auto_trigger:true}]); 
            nextRandomId = getRandomId();  
        }
        else
            $('#tb_config_name').val(data['mconfig_name']);

        if(mconfig_id_tmp == 'default')
            $("#section_view_list").find('*').prop('disabled', true);

        window.history.replaceState(null,null, location.pathname + '?config=' + mconfig_id_tmp);
        fadeAni();
    }).fail(function() {
        toastr.error('', 'Error getting data!');
        fadeAni();
    });
}
