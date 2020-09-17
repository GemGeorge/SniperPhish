var dt_mail_campaign_result;
var tb_camp_result_colums_list;
var web_mailcamp_list, data_mail_live, data_web_live, data_matched, data_not_matched;
var chart_live_mailcamp;
var radialchart_overview_mailcamp, piechart_mail_total_sent, piechart_mail_total_mail_open, piechart_mail_total_replied;
var reply_emails = [], form_field_cols=[];
var g_campaign_id ='', g_tracker_id='', g_tb_data_single = true;
var camp_status_def = {
    0: `<span class="badge badge-pill badge-dark" data-toggle="tooltip" title="Not scheduled"><i class="mdi mdi-alert"></i> Inactive</span>`,
    1: `<span class="badge badge-pill badge-warning" data-toggle="tooltip" title="Scheduled"><i class="mdi mdi-timer"></i> Scheduled</span>`,
    2: `<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Phishing campaign status"><i class="mdi mdi-fish"></i> In-progress</span> <span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Mail sending status"><i class="mdi mdi-email"></i> In-progress</span>`,
    3: `<span class="badge badge-pill badge-success" data-toggle="tooltip" title="Phishing campaign status"><i class="mdi mdi-fish"></i> Completed</span>`,
    4: `<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Phishing campaign status"><i class="mdi mdi-fish"></i> In-progress</span> <span class="badge badge-pill badge-success" data-toggle="tooltip" title="Mail sending status"><i class="mdi mdi-email"></i> Completed</span>`
};

var camp_table_status_def = {
    1: `<td><span class="badge badge-pill badge-primary" data-toggle="tooltip" title="In-progress">In-progress</td>`,
    2: `<td><span class="badge badge-pill badge-success" data-toggle="tooltip" title="Sent">Sent</td>`,
    3: `<td><span class="badge badge-pill badge-danger" data-toggle="tooltip" title="Error">Error</td>`,
    4: `<td><i class="fas fa-clock fa-lg" data-toggle="tooltip" title="Waiting..."></i><span hidden>Waiting...</span></td>`
};


$("#modal_mailcamp_selector").select2({
    minimumResultsForSearch: -1,
    placeholder: "Select Mail Campaign",
}); 
$("#modal_web_tracker_selector").select2({
    minimumResultsForSearch: -1,
    placeholder: "Select Web Tracker",
});  
$("#modal_export_report_selector").select2({
    minimumResultsForSearch: -1,
});     


$('.accordion .panel-default .panel-heading').click(function (e) {    
    if($('#collapseOne').hasClass('show'))
        $('.accordion').removeClass("according-manage");
    else
        $('.accordion').addClass("according-manage");

    $('.accordion .panel-collapse').collapse('toggle');
});

$('#modal_mailcamp_selector').on('change', function() {
    campaign_id = this.value; 
    $.each(web_mailcamp_list['mailcamp_list'], function(key, mailcamp) {
        if(mailcamp['campaign_id'] == campaign_id){
            $('#modal_table_campaign_info > tbody > tr:eq(0) > td:eq(1)').text(mailcamp['campaign_id']);
            $('#modal_table_campaign_info > tbody > tr:eq(1) > td:eq(1)').text(mailcamp['campaign_name']);
            $('#modal_table_campaign_info > tbody > tr:eq(2) > td:eq(1)').text(mailcamp['date']);
            $('#modal_table_campaign_info > tbody > tr:eq(3) > td:eq(1)').text(mailcamp['scheduled_time']!=''?mailcamp['scheduled_time']:"NA");
        }            
    });
});

$('#modal_web_tracker_selector').on('change', function() {
    tracker_id = this.value;
    $.each(web_mailcamp_list['webtracker_list'], function(key, webtracker) {
        if(webtracker['tracker_id'] == tracker_id){
            $('#modal_table_webtracker_info > tbody > tr:eq(0) > td:eq(1)').text(webtracker['tracker_id']);
            $('#modal_table_webtracker_info > tbody > tr:eq(1) > td:eq(1)').text(webtracker['tracker_name']);
            $('#modal_table_webtracker_info > tbody > tr:eq(2) > td:eq(1)').text(webtracker['date']);
            $('#modal_table_webtracker_info > tbody > tr:eq(3) > td:eq(1)').text(webtracker['start_time']!=''?webtracker['start_time']:"NA");
        }            
    });
});

//------------------------------------------------
$("#tb_camp_result_colums_list_mcamp").select2();
$("#tb_camp_result_colums_list_wcm").select2();
$("#tb_camp_result_colums_list_wpv").select2();
$("#tb_camp_result_colums_list_wfs").select2();

//Select 1
$("#tb_camp_result_colums_list_mcamp").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_camp_result_colums_list_mcamp').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});

$("#tb_camp_result_colums_list_mcamp").parent().find("ul.select2-selection__rendered").sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues("#tb_camp_result_colums_list_mcamp");
    }
});

orderSortedValues = function(item) {
    var value = ''
    $(item).parent().find("ul.select2-selection__rendered").children("li[title]").each(function(i, obj) {
        var element = $(item).children('option').filter(function() {
            return $(this).html() == obj.title
        });
        moveElementToEndOfParent(element)
    });
};

//Select 2
$("#tb_camp_result_colums_list_wcm").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_camp_result_colums_list_wcm').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});

$("#tb_camp_result_colums_list_wcm").parent().find("ul.select2-selection__rendered").sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues("#tb_camp_result_colums_list_wcm");
    }
});

//Select 3
$("#tb_camp_result_colums_list_wpv").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_camp_result_colums_list_wpv').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});

$("#tb_camp_result_colums_list_wpv").parent().find("ul.select2-selection__rendered").sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues("#tb_camp_result_colums_list_wpv");
    }
});

//Select 4
$("#tb_camp_result_colums_list_wlps").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_camp_result_colums_list_wlps').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});

$("#tb_camp_result_colums_list_wlps").parent().find("ul.select2-selection__rendered").sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues("#tb_camp_result_colums_list_wlps");
    }
});

//Select 5
$("#tb_camp_result_colums_list_wfps").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_camp_result_colums_list_wfps').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});

$("#tb_camp_result_colums_list_wfps").parent().find("ul.select2-selection__rendered").sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues("#tb_camp_result_colums_list_wfps");
    }
});

moveElementToEndOfParent = function(element) {
    var parent = element.parent();
    element.detach();
    parent.append(element);
};
//----------------------------------------------

function refreshDashboard() {
    $('input[name="radio_table_data"]:checked').val() == "radio_table_data_single"?g_tb_data_single=true:g_tb_data_single=false;
    if (g_campaign_id != '' && g_tracker_id != '')
        campaignSelected(g_campaign_id,g_tracker_id);
    else
        toastr.error('', 'Campaign not selected');
}

function startLoaders() {
    $("#chart_live_mailcamp").attr("hidden", true);
    $("#chart_live_mailcamp").parent().append(displayLoader("Loading..."));
    $("#radialchart_overview_mailcamp").attr("hidden", true);
    $("#radialchart_overview_mailcamp").parent().append(displayLoader("Loading..."));
    $("#piechart_mail_total_sent").attr("hidden", true);
    $("#piechart_mail_total_sent").parent().append(displayLoader("Loading..."));
    $("#piechart_mail_total_mail_open").attr("hidden", true);
    $("#piechart_mail_total_mail_open").parent().append(displayLoader("Loading..."));
    $("#piechart_mail_total_replied").attr("hidden", true);
    $("#piechart_mail_total_replied").parent().append(displayLoader("Loading..."));
    $("#table_campaign_result").attr("hidden", true);
    $("#table_campaign_result").parent().append(displayLoader("Loading..."));

    $("#piechart_total_pv").attr("hidden", true);
    $("#piechart_total_pv").parent().append(displayLoader("Loading..."));
    $("#piechart_total_fs").attr("hidden", true);
    $("#piechart_total_fs").parent().append(displayLoader("Loading..."));
    $("#piechart_total_suspect").attr("hidden", true);
    $("#piechart_total_suspect").parent().append(displayLoader("Loading..."));
    $("#radialchart_overview_webcamp").attr("hidden", true);
    $("#radialchart_overview_webcamp").parent().append(displayLoader("Loading..."));
}

function loadTableCampaignList(campaign_id,tracker_id) {
    $.post("web_mail_campaign_manager", {
            action_type: "get_campaign_list_web_mail"
        },
        function(data, status) {
            web_mailcamp_list = data;
            $.each(data['mailcamp_list'], function(key, value) {
                if(value['camp_status'] == 2 || value['camp_status'] == 3 || value['camp_status'] == 4) //removes inactive and scheduled
                    $("#modal_mailcamp_selector").append(`<option value="` + value['campaign_id'] + `">` + value['campaign_name'] + `</option>`);
            });
            $.each(data['webtracker_list'], function(key, value) {
                if(!(value['start_time'] == undefined || value['start_time'] == '')) //removes not started
                    $("#modal_web_tracker_selector").append(`<option value="` + value['tracker_id'] + `">` + value['tracker_name'] + `</option>`);
            });

            if(campaign_id != '' && tracker_id != '')                
                campaignSelected(campaign_id,tracker_id,true);
    });
}

function campaignSelectedValidation(){    
    var f_error=false;
    $("#modal_mailcamp_selector").data('select2').$selection.addClass("select2-selection");
    $("#modal_mailcamp_selector").data('select2').$selection.removeClass("select2-is-invalid");
    $("#modal_web_tracker_selector").data('select2').$selection.addClass("select2-selection");
    $("#modal_web_tracker_selector").data('select2').$selection.removeClass("select2-is-invalid");

    
    if($("#modal_mailcamp_selector").val() == ''){
        $("#modal_mailcamp_selector").data('select2').$selection.removeClass("select2-selection");
        $("#modal_mailcamp_selector").data('select2').$selection.addClass("select2-is-invalid");
        f_error = true;
    }   
    if($("#modal_web_tracker_selector").val() == ''){
        $("#modal_web_tracker_selector").data('select2').$selection.removeClass("select2-selection");
        $("#modal_web_tracker_selector").data('select2').$selection.addClass("select2-is-invalid");
        f_error = true;
    }   
    if(!f_error){
        campaignSelected($("#modal_mailcamp_selector").val(),$("#modal_web_tracker_selector").val(),true);
        $('#ModalCampaignList').modal('toggle');
        window.history.replaceState(null,null, location.pathname + '?mcamp=' + $("#modal_mailcamp_selector").val() + '&tracker=' + $("#modal_web_tracker_selector").val());
    }
}

function campaignSelected(campaign_id,tracker_id,f_refresh=false) {
    g_campaign_id = campaign_id;
    g_tracker_id = tracker_id;
    var mailcamp_info = web_mailcamp_list['mailcamp_list'].filter(i => i.campaign_id === campaign_id)[0];
    var webtracker_info = web_mailcamp_list['webtracker_list'].filter(i => i.tracker_id === tracker_id)[0]; 
    var user_group_id = mailcamp_info['user_group'].split(',')[0];
    $("#disp_camp_name").text(mailcamp_info['campaign_name']);
    $("#disp_web_tracker_name").text(webtracker_info['tracker_name']);   
    $('#disp_camp_status').html(camp_status_def[mailcamp_info['camp_status']]);
    $('#disp_camp_start').text(UTC2Local(mailcamp_info['scheduled_time']));
    $('#disp_camp_end').text(UTC2Local(mailcamp_info['stop_time']));

    $('#table_campaign_info > tbody > tr:eq(0) > td:eq(1)').text(mailcamp_info['campaign_id']);
    $('#table_campaign_info > tbody > tr:eq(1) > td:eq(1)').text(mailcamp_info['date']);
    $('#table_campaign_info > tbody > tr:eq(2) > td:eq(1)').text(mailcamp_info['user_group'].split(',')[1] + " (ID :" + user_group_id + ")");
    $('#table_campaign_info > tboDy > tr:eq(3) > td:eq(1)').text(mailcamp_info['mail_template'].split(',')[1] + " (ID: " + mailcamp_info['mail_template'].split(',')[0] + ")");
    $('#table_campaign_info > tbody > tr:eq(4) > td:eq(1)').text(mailcamp_info['mail_sender'].split(',')[1] + " (ID: " + mailcamp_info['mail_sender'].split(',')[0] + ")");

    var tracker_step_data = JSON.parse(webtracker_info ['tracker_step_data']);
    g_page_count = tracker_step_data ['web_forms']['count'];
    $('#table_web_tracker_info > tbody > tr:eq(0) > td:eq(1)').text(webtracker_info['tracker_id']);
    $('#table_web_tracker_info > tbody > tr:eq(1) > td:eq(1)').text(webtracker_info['date']);
    $('#table_web_tracker_info > tbody > tr:eq(2) > td:eq(1)').text("Yes");
    $('#table_web_tracker_info > tbody > tr:eq(3) > td:eq(1)').text(g_page_count>0?"Yes":"No");
    $('#table_web_tracker_info > tbody > tr:eq(4) > td:eq(1)').text(g_page_count);

    $.each([chart_live_mailcamp,radialchart_overview_mailcamp,piechart_mail_total_sent,piechart_mail_total_mail_open,piechart_mail_total_replied,dt_mail_campaign_result,radialchart_overview_webcamp,piechart_total_pv,piechart_total_fs,piechart_total_suspect], function(i, graph){
        try {
            graph.destroy();
        } catch (err) {}
    });

    startLoaders();
    $('#table_campaign_result thead').empty();
    $("#table_campaign_result tbody > tr").remove();
    $.post("web_mail_campaign_manager", {
            action_type: "multi_get_live_campaign_data_web_mail",
            campaign_id: campaign_id,
            tracker_id: tracker_id,
            user_group_id: user_group_id
        },
        function(data, status) {
            data_mail_live = data['mailcamp_live'];
            data_web_live = data['webtracker_live'];
            data_matched = data['matched'];
            data_not_matched = data['not_matched'];

            if(f_refresh){
                $("#tb_camp_result_colums_list_wfs").empty();
                $("#tb_camp_result_colums_list_wfs").append(`<option value="wfs_activity" selected>Form Submission</option>
                                     <option value="wfs_submission_count">Submission Count</option>
                                     <option value="wfs_first_submission" selected>First Submission</option>
                                     <option value="wfs_last_submission">Last Submission</option>
                                     <option value="wfs_submission_times">Submission Times</option>`);
               

                for (var i=1; i<=g_page_count; i++)
                   $("#tb_camp_result_colums_list_wfs").append(`<option value="Page-` + i + `" selected>P` + i + ` Submission</option>`);

                $.each(tracker_step_data.web_forms.data, function(i, page_n) {
                    $.each(page_n.form_fields_and_values, function(field_type, form_field) {
                        if(field_type != "FSB"){
                            $("#tb_camp_result_colums_list_wfs").append('<option value="Field-' + form_field.idname + '" selected>Field-' + form_field.idname + '</option>');
                            form_field_cols.push('Field-' + form_field.idname);
                        }
                    });
                });
            }
              
            var sent_failed_count = data_mail_live.filter(x => x['sending_status'] === 3).length;
            var sent_success_count = data_mail_live.filter(x => x['sending_status'] === 2).length;
            var sent_mail_count = sent_failed_count + sent_success_count;
            updateProgressbar(mailcamp_info['camp_status'], mailcamp_info['mail_sender'].split(',')[0],mailcamp_info['user_group'].split(',')[0], mailcamp_info['mail_template'].split(',')[0], sent_mail_count, sent_success_count, sent_failed_count);
        }).fail(function() {
        toastr.error('', 'Error getting campaign data!');
    });
}

function updateProgressbar(mailcamp_status, sender_list_id, user_group_id, mail_template_id, sent_mail_count, sent_success_count, sent_failed_count) {
    $.post("userlist_campaignlist_mailtemplate_manager", {
            action_type: "get_user_group_from_group_Id",
            user_group_id: user_group_id,
        },
        function(data, status) {
            $('#user_group_name').val(data['user_group_name']);

            var total_user_email_count =  data['user_email'].split(',').length;
            var sent_mail_percent = +(sent_mail_count / total_user_email_count * 100).toFixed(2);
            var sent_mail_success_percent = +(sent_success_count / sent_mail_count * 100).toFixed(2);

            $("#progressbar_status").children().width(sent_mail_percent + "%");
            $("#progressbar_status").children().text(sent_mail_count + "/" + total_user_email_count + " (" + sent_mail_percent + "%)");
            if (sent_mail_percent == 100)
                $("#progressbar_status").children().addClass("bg-success");
            else
                $("#progressbar_status").children().removeClass("bg-success");

            updateLiveCampData(data, user_group_id);
            updatePieTotalSent(total_user_email_count, sent_mail_count, sent_failed_count);

            var open_mail_count = 0;
            $.each(data_mail_live, function(i, item) {
                if (item['mail_open_times'] != undefined)
                    open_mail_count++;
            });
            var open_mail_percent = +(open_mail_count / total_user_email_count * 100).toFixed(2);;
            updatePieTotalMailOpen(total_user_email_count, open_mail_count, open_mail_percent);
            updatePieOverViewEmail(sent_mail_success_percent, open_mail_percent);
            if (mailcamp_status != 0)
                updatePieTotalMailReplied(sender_list_id, user_group_id, mail_template_id);

            //-------------------------------------------------------
            var total_pv = Object.keys(data_matched.page_visit).length;
            var total_fs = Object.keys(data_matched.form_submission).length;
            var total_pv_nonmatch = data_not_matched.page_visit.length;
            var total_fs_nonmatch = data_not_matched.form_submission.length;

            var pv_percent = +(total_pv*100/total_user_email_count).toFixed(2); //+ => to number (unary plus)
            //var fs_percent = +(total_fs*100/total_user_email_count).toFixed(2);
            var pv_nonmatch_percent = +((total_pv_nonmatch*100/(total_pv_nonmatch+total_fs_nonmatch)).toFixed(2));
            pv_nonmatch_percent=isNaN(pv_nonmatch_percent)?0:pv_nonmatch_percent;   //divide by zero issue
            var fs_nonmatch_percent = 100-pv_nonmatch_percent;

            updatePieOverViewWeb(total_user_email_count,pv_percent);
            updatePieTotalPV(total_user_email_count,total_pv,pv_percent);
            updatePieTotalFS(total_user_email_count,total_fs);
            updatePieTotalSuspected(total_pv_nonmatch,total_fs_nonmatch,pv_nonmatch_percent,fs_nonmatch_percent);
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        });
}

function updateLiveCampData(user_group_data) {
    $("#chart_live_mailcamp").attr("hidden", false);
    $("#chart_live_mailcamp").parent().children().remove('.loadercust');

    var col_name = user_group_data['user_name'].split(',');
    var col_email = user_group_data['user_email'].split(',');
    var y_axis_labels = {0:"", 1:"Mail Campaign", 2:"Page Visit", 3:"Form Submission"};
    var graph_data = {'in_progress': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'sent_success': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'send_error': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'mail_open': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'page_visit': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'form_submission': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[],"page":[]}};

    $.each(data_mail_live, function(i, item) {
        switch (item['sending_status']) {
            case 1:     //In progress
                graph_data.in_progress.hit_time.push([Number(item['send_time']), 1]);
                graph_data.in_progress.mailto_user_email.push(item['mailto_user_email']);
                graph_data.in_progress.mailto_user_name.push(item['mailto_user_name']);
                break;
            case 2:     //Sent success
                graph_data.sent_success.hit_time.push([Number(item['send_time']), 1]);
                graph_data.sent_success.mailto_user_email.push(item['mailto_user_email']);
                graph_data.sent_success.mailto_user_name.push(item['mailto_user_name']);
                break;
            case 3:     //Send error
                graph_data.send_error.hit_time.push([Number(item['send_time']), 1]);
                graph_data.send_error.mailto_user_email.push(item['mailto_user_email']);
                graph_data.send_error.mailto_user_name.push(item['mailto_user_name']);
                break;
        }
        
        var arr_mail_open_times = $.parseJSON(item['mail_open_times']);
        $.each(arr_mail_open_times, function(i, mail_open_time) {            
            if(g_tb_data_single){
                if ($.inArray(item['mailto_user_email'],  graph_data.mail_open.mailto_user_email) <= -1 ) {
                    graph_data.mail_open.hit_time.push([Number(mail_open_time), 1]);
                    graph_data.mail_open.mailto_user_email.push(item['mailto_user_email']);
                    graph_data.mail_open.mailto_user_name.push(item['mailto_user_name']);
                }
            }
            else{
                graph_data.mail_open.hit_time.push([Number(mail_open_time), 1]);
                graph_data.mail_open.mailto_user_email.push(item['mailto_user_email']);
                graph_data.mail_open.mailto_user_name.push(item['mailto_user_name']);
            }
        });
    });

    //Page visit
    $.each(data_matched['page_visit'], function(user_mail_id, visits) {
        $.each(visits, function(visits, visit) {
            if(g_tb_data_single){
                if ($.inArray(user_mail_id,  graph_data.page_visit.mailto_user_email) <= -1 ) {
                    graph_data.page_visit.hit_time.push([Number(visit['time']), 2]);
                    graph_data.page_visit.mailto_user_email.push(user_mail_id);
                    graph_data.page_visit.mailto_user_name.push(visit['mailto_user_name']);
                }
            }
            else{
                graph_data.page_visit.hit_time.push([Number(visit['time']), 2]);
                graph_data.page_visit.mailto_user_email.push(user_mail_id);
                graph_data.page_visit.mailto_user_name.push(visit['mailto_user_name']);
            }
        });
    });

    //Form submision
    $.each(data_matched['form_submission'], function(user_mail_id, single_user_data) {
        $.each(single_user_data, function(visits, page_n) {
            $.each(page_n, function(i, entry) {  //all submissions in nth page
                if(g_tb_data_single){
                    if ($.inArray(user_mail_id,  graph_data.form_submission.mailto_user_email) <= -1 ) {
                        graph_data.form_submission.hit_time.push([Number(entry['time']), 3]);
                        graph_data.form_submission.mailto_user_email.push(user_mail_id);
                        graph_data.form_submission.mailto_user_name.push(entry['mailto_user_name']);
                        graph_data.form_submission.page.push(entry['page']);
                    }
                }
                else{
                    graph_data.form_submission.hit_time.push([Number(entry['time']), 3]);
                    graph_data.form_submission.mailto_user_email.push(user_mail_id);
                    graph_data.form_submission.mailto_user_name.push(entry['mailto_user_name']);
                    graph_data.form_submission.page.push(entry['page']);
                }
            });
        });
    });
    var options = {
        chart: {
            height: 180,
            type: 'scatter',
        },
        series: [{
                name: 'In-progress',
                data: graph_data.in_progress.hit_time
            },
            {
                name: 'Success',
                data: graph_data.sent_success.hit_time
            },
            {
                name: 'Opened',
                data: graph_data.mail_open.hit_time
            },
            {
                name: 'Error',
                data: graph_data.send_error.hit_time
            },
            {
                name: 'Page visit',
                data: graph_data.page_visit.hit_time
            },
            {
                name: 'Form submission',
                data: graph_data.form_submission.hit_time
            },
        ],
        dataLabels: {
            enabled: false,
        },
        xaxis: {
            type: 'datetime',
            labels: {
                formatter: function(val) {
                    return UTC2Local(val);
                },
                rotate: 0,
            },
            tooltip: {
                enabled: false,
            },
            axisBorder: {
                show: true,
                color: '#78909C',
                height: 1,
                width: '100%',
            },
            axisTicks: {
                show: true,
                borderType: 'solid',
                color: '#78909C',
                height: 6,
                offsetX: 0,
                offsetY: 0
            },
        },
        yaxis: {
            max: 3,
            min:0,
            show: true,
            tickAmount:3,
            labels: {
                show: true, align: 'right',
                minWidth: 0,
                maxWidth: 160,
                style: {
                    fontSize: '12px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fontWeight: 400,
                    cssClass: 'apexcharts-yaxis-label',
                },
                formatter: (value) => {
                    return y_axis_labels[value] 
                },
            }, 
        },
        tooltip: {
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                var localdate = UTC2Local(w.config.series[seriesIndex].data[dataPointIndex][0]);
                switch (seriesIndex) {
                    case 0:     //In progress
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.in_progress.mailto_user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.in_progress.mailto_user_email[dataPointIndex] + `</div>`;
                        break;
                    case 1:     //Sent success
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.sent_success.mailto_user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.sent_success.mailto_user_email[dataPointIndex] + `</div>`;
                        break;
                    case 2:     //Mail opened
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.mail_open.mailto_user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.mail_open.mailto_user_email[dataPointIndex] + `</div>`;
                        break;
                    case 3:     //Send error
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.send_error.mailto_user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.send_error.mailto_user_email[dataPointIndex] + `</div>`;
                        break;
                    case 4:
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.page_visit.mailto_user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.page_visit.mailto_user_email[dataPointIndex] + `</div>`;
                        break;
                    case 5:
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.form_submission.mailto_user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.form_submission.mailto_user_email[dataPointIndex] + `</div>`;
                        break;
                }
            }
        },
        grid: {
            show: true,
            padding: {
                left: 10,
                right: 15
            },
            xaxis: {
                showLines: true,
            },
            yaxis: {
                showLines: true,
            },
        },
        legend: {
            show: true,
            height: 30,
        },
        colors: ['#7460ee', '#4CAF50', '#e6b800', '#FA4443', '#008FFB', '#3F51B5', '#2E294E'],
    }

    chart_live_mailcamp = new ApexCharts(
        document.querySelector("#chart_live_mailcamp"),
        options
    );
    chart_live_mailcamp.render();
}

function viewReplyMails(mail_id) {
    $('#modal_reply_mails').modal('toggle');
    $("#modal_reply_mails_body ul").html("");
    $("#modal_reply_mails_body div").html("");

    $.each(reply_emails['msg_info'][mail_id]['msg_time'], function(i, item) {
        if (i == 0) {
            $("#modal_reply_mails_body ul").first().append(`<li class="nav-item tab-header"> <a class="nav-link active" data-toggle="tab" href="#mail_body_` + i + `" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Message ` + (i + 1) + `</span></a> </li>`);
            $("#modal_reply_mails_body div").first().append(`<div class="tab-pane active m-t-15" id="mail_body_` + i + `" role="tabpanel">
                    <div class="row m-b-5">             
                        <div class="col-md-6">  
                            <span class="card-title">Sender: ` + mail_id + `</span>
                        </div>                     
                        <div class="col-md-6 align-items-right ml-auto">                           
                            <span>Time: ` + item + `</span>
                        </div>
                    </div>
                    <div id="summernote_reply_mail` + i + `" ></div>
                </div>`);
        } else {
            $("#modal_reply_mails_body ul").first().append(`<li class="nav-item tab-header"> <a class="nav-link" data-toggle="tab" href="#mail_body_` + i + `" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Message ` + (i + 1) + `</span></a> </li>`);
            $("#modal_reply_mails_body div").first().append(`<div class="tab-pane m-t-15" id="mail_body_` + i + `" role="tabpanel">
                        <div class="row m-b-5">             
                        <div class="col-md-6">  
                            <span class="card-title">Sender: ` + mail_id + `</span>
                        </div>                     
                        <div class="col-md-6 align-items-right ml-auto">                           
                            <span>Time: ` + item + `</span>
                        </div>
                    </div>
                    <div id="summernote_reply_mail` + i + `" ></div>
                </div>`);
        }

        $('#summernote_reply_mail' + i).summernote({
            popover: { image: [], },
            followingToolbar: true,
            height: 350,
            codeviewFilter: false,
            focus: true,
            lang: 'en-UK',
            cache: false,
            defaultFontName: 'Arial',
            toolbar: [],
        });
        $('#summernote_reply_mail' + i).summernote('code', atob((reply_emails['msg_info'][mail_id]['msg_body'])[i]).replace(/\r\n/g, "<br/>"));
        $('#summernote_reply_mail' + i).summernote('disable');
    });
}

function updatePieOverViewEmail(sent_mail_percent, open_mail_percent) {
    $("#radialchart_overview_mailcamp").attr("hidden", false);
    $("#radialchart_overview_mailcamp").parent().children().remove('.loadercust');

    var options = {
        series: [sent_mail_percent, open_mail_percent, 0], //value 0 updated in anotehr function
        chart: {
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                //offsetX: -15,
                hollow: {
                    size: '45%',
                },
                dataLabels: {
                    name: {
                        fontSize: '14px',
                    },
                    value: {
                        fontSize: '12px',
                    },
                }
            }
        },
        legend: {
            show: true,
            position: 'bottom',
            floating: true,
            itemMargin: {
                horizontal: 5,
                vertical: 2,
            },
            onItemClick: {
                toggleDataSeries: true
            },
            onItemHover: {
                highlightDataSeries: true
            },

        },
        labels: ['Sent', 'Opened', 'Replied'],
        colors: ['#4CAF50', '#e6b800', '#F86624'],
    };

    radialchart_overview_mailcamp = new ApexCharts(
        document.querySelector("#radialchart_overview_mailcamp"),
        options
    );
    radialchart_overview_mailcamp.render();
}

function updatePieTotalSent(total_user_email_count, sent_mail_count, sent_failed_count) {
    $("#piechart_mail_total_sent").attr("hidden", false);
    $("#piechart_mail_total_sent").parent().children().remove('.loadercust');

    var sent_percent = +((sent_mail_count-sent_failed_count) / sent_mail_count * 100).toFixed(2);;
    var non_sent_percent = 100 - sent_percent;
    var options = {
        series: [sent_percent, non_sent_percent],
        chart: {
            type: 'donut',
        },
        plotOptions: {
            pie: {
                offsetY: 0,
                customScale: 1,
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '14px',
                            formatter: function(val) {
                                return val + "%";
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                return +(sent_mail_count/total_user_email_count*100).toFixed(2) + "% (" + sent_mail_count + "/" + total_user_email_count + ")";
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false
        },
        tooltip: {
            enabled: true,
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                if (seriesIndex == 0)
                    return `<div class="chart-tooltip">Sent success: ` + series[seriesIndex] + `%</div>`;
                else
                    return `<div class="chart-tooltip">Send error: ` + series[seriesIndex] + `%</div>`;
            },
        },
        colors: ['#4CAF50', '#d9d9d9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    piechart_mail_total_sent = new ApexCharts(
        document.querySelector("#piechart_mail_total_sent"),
        options
    );
    piechart_mail_total_sent.render();
}

function updatePieTotalMailOpen(total_user_email_count, open_mail_count, open_mail_percent) {
    $("#piechart_mail_total_mail_open").attr("hidden", false);
    $("#piechart_mail_total_mail_open").parent().children().remove('.loadercust');

    var non_open_percent = 100 - open_mail_percent;
    var options = {
        series: [open_mail_percent, non_open_percent],
        chart: {
            type: 'donut',
        },
        plotOptions: {
            pie: {
                offsetY: 0,
                customScale: 1,
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '14px',
                            formatter: function(val) {
                                return val + "%";
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                return w.globals.series[0] + "% (" + open_mail_count + "/" + total_user_email_count + ")";
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false
        },
        tooltip: {
            enabled: true,
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                if (seriesIndex == 0)
                    return `<div class="chart-tooltip">Opened: ` + series[seriesIndex] + `%</div>`;
                else
                    return `<div class="chart-tooltip">Not opened: ` + series[seriesIndex] + `%</div>`;
            },
        },
        colors: ['#e6b800', '#d9d9d9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    piechart_mail_total_mail_open = new ApexCharts(
        document.querySelector("#piechart_mail_total_mail_open"),
        options
    );
    piechart_mail_total_mail_open.render();
}

function updatePieTotalMailReplied(sender_list_id, user_group_id, mail_template_id) {
    $.post("mail_campaign_manager", {
            action_type: "get_mail_replied",
            sender_list_id: sender_list_id,
            user_group_id: user_group_id,
            mail_template_id: mail_template_id
        },
        function(data, status) {
            window.reply_emails = data;
            $("#piechart_mail_total_replied").attr("hidden", false);
            $("#piechart_mail_total_replied").parent().children().remove('.loadercust');
            loadTableCampaignResult();

            var total_user_email_count = data['total_user_email_count'];
            var reply_count_unique = Object.keys(data['msg_info']).length;
            var reply_percent = +(reply_count_unique / total_user_email_count * 100).toFixed(2);
            var non_reply_percent = 100 - reply_percent;
            var options = {
                series: [reply_percent, non_reply_percent],
                chart: {
                    type: 'donut',
                },
                plotOptions: {
                    pie: {
                        offsetY: 0,
                        customScale: 1,
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: {
                                    show: false,
                                },
                                value: {
                                    show: true,
                                    fontSize: '14px',
                                    formatter: function(val) {
                                        return val + "%";
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return w.globals.series[0] + "% (" + reply_count_unique + "/" + total_user_email_count + ")";
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false
                },
                tooltip: {
                    enabled: true,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        if (seriesIndex == 0)
                            return `<div class="chart-tooltip">Replied: ` + series[seriesIndex] + `%</div>`;
                        else
                            return `<div class="chart-tooltip">Not Replied: ` + series[seriesIndex] + `%</div>`;
                    },
                },
                colors: ['#F86624', '#d9d9d9'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            piechart_mail_total_replied = new ApexCharts(
                document.querySelector("#piechart_mail_total_replied"),
                options
            );
            piechart_mail_total_replied.render();

            var arr_chart_data = radialchart_overview_mailcamp.w.globals.series.slice();
            arr_chart_data[2] = reply_percent;
            radialchart_overview_mailcamp.updateSeries(arr_chart_data);            
        });
}

function loadTableCampaignResult() {
    try {
        dt_mail_campaign_result.destroy();
    } catch (err) {}

    $("#table_campaign_result").attr("hidden", false);
    $("#table_campaign_result").parent().children().remove('.loadercust');

    $('#table_campaign_result thead').empty();
    $("#table_campaign_result tbody > tr").remove();

    var tb_headers = "<tr><th>No</th>";
    var tb_data = "";

    tb_camp_result_colums_list = $('#tb_camp_result_colums_list_mcamp').val().concat($('#tb_camp_result_colums_list_wcm').val()).concat($('#tb_camp_result_colums_list_wpv').val()).concat($('#tb_camp_result_colums_list_wfs').val());

    $.each(tb_camp_result_colums_list, function(i, item) {
        switch (item) {
            case "mailto_user_name":
                tb_headers += "<th>Name</th>";
                break;
            case "mailto_user_email":
                tb_headers += "<th>Email</th>";
                break;
            case "sending_status":
                tb_headers += "<th class='class_sel_col'>Status</th>";
                break;
            case "send_time":
                tb_headers += "<th>Sent Time</th>";
                break;
            case "send_error":
                tb_headers += "<th>Send Error</th>";
                break;
            case "mail_open":
                tb_headers += "<th class='class_sel_col'>Mail Open</th>";
                break;
            case "mail_open_count":
                tb_headers += "<th>Mail(open count)</th>";
                break;
            case "mail_first_open":
                tb_headers += "<th>Mail(first open)</th>";
                break;
            case "mail_last_open":
                tb_headers += "<th>Mail(last open)</th>";
                break;
            case "mail_open_times":
                tb_headers += "<th>Mail(all open times)</th>";
                break;
            case "public_ip":
                tb_headers += "<th>Public IP</th>";
                break;
            case "user_agent":
                tb_headers += "<th>User Agent</th>";
                break;
            case "mail_client":
                tb_headers += "<th>Mail Client</th>";
                break;
            case "platform":
                tb_headers += "<th>Platform</th>";
                break;
            case "all_headers":
                tb_headers += "<th>HTTP Headers</th>";
                break;
            case "mail_reply":
                tb_headers += "<th class='class_sel_col'>Mail Reply</th>";
                break;
            case "mail_reply_count":
                tb_headers += "<th class='class_sel_col'>Mail (reply count)</th>";
                break;
            case "mail_reply_content":
                tb_headers += "<th class='class_sel_col'>Mail (reply content)</th>";
                break;

            case "wcm_cid":
                tb_headers += "<th>Client ID</th>";
                break;
            case "wcm_session_id":
                tb_headers += "<th>Session ID</th>";
                break;
            case "wcm_public_ip":
                tb_headers += "<th>Public IP</th>";
                break;
            case "wcm_internal_ip":
                tb_headers += "<th>Private IP</th>";
                break;
            case "wcm_user_agent":
                tb_headers += "<th>User Agent</th>";
                break;

            case "wpv_activity":
                tb_headers += "<th class='class_sel_col'>Page Visit</th>";
                break;
            case "wpv_visit_count":
                tb_headers += "<th>Visit Count</th>";
                break;
            case "wpv_first_visit":
                tb_headers += "<th>First Visit</th>";
                break;
            case "wpv_last_visit":
                tb_headers += "<th>Last Visit</th>";
                break;
            case "wpv_visit_times":
                tb_headers += "<th>Visit Times</th>";
                break;

            case "wfs_activity":
                tb_headers += "<th class='class_sel_col'>Form Submission</th>";
                break;
            case "wfs_submission_count":
                tb_headers += "<th>Submission Count</th>";
                break;
            case "wfs_first_submission":
                tb_headers += "<th>First Submission</th>";
                break;
            case "wfs_last_submission":
                tb_headers += "<th>Last Submission</th>";
                break;
            case "wfs_submission_times":
                tb_headers += "<th>Submission Times</th>";
                break;
            default:
                if (item.startsWith("Field-"))
                    tb_headers += "<th>" + item + "</th>";
                else
                if (item.startsWith("Page-"))
                    tb_headers += "<th>P" + item.split('-')[1] + " Submission</th>";
        }
    });
    tb_headers += "</tr>";
    $("#table_campaign_result thead").append(tb_headers);

    $.each(data_mail_live, function(i, item) {
        tb_data += "<tr><td></td>";
        $.each(tb_camp_result_colums_list, function(i, column) {
            //---Start setting default column values
            if(column == "mail_open" || column == "mail_reply")
                item[column] = "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span>";
            if(column == "mail_open_count")
                item[column] = '0'; //requires '0' and not 0, unknown issue
            //----End setting default column values
            if($.inArray(column,['mailto_user_name','mailto_user_email','sending_status','send_time','send_error','public_ip','user_agent','mail_client','platform','all_headers'])> -1 && item[column] == undefined)
                tb_data += "<td>-</td>";
            else
            if ((column == "mail_open" || column == "mail_open_count" || column == "mail_first_open" || column == "mail_last_open" || column == "mail_open_times") && item['mail_open_times'] != undefined) {
                arr_mail_open_times = $.parseJSON(item['mail_open_times']); // string "[ 1598077087853, 1598077091716 ]"  => JS array
                mail_open_first_timestamp = arr_mail_open_times[0];
                mail_open_last_timestamp = arr_mail_open_times[arr_mail_open_times.length - 1];
                $.each(arr_mail_open_times, function(i, sitem) {
                    arr_mail_open_times[i] = UTC2Local(sitem);
                });

                switch (column) {
                    case "mail_open":
                        tb_data += arr_mail_open_times.length > 0 ? "<td ><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></td>" : "<td>No</td>"; // else condition is not effective
                        break;
                    case "mail_open_count":
                        tb_data += "<td>" + arr_mail_open_times.length + "</td>";
                        break;
                    case "mail_first_open":
                        tb_data += "<td data-order=\"" + mail_open_first_timestamp + "\">" + arr_mail_open_times[0] + "</td>";
                        break;
                    case "mail_last_open":
                        tb_data += "<td data-order=\"" + mail_open_last_timestamp + "\">" + arr_mail_open_times[arr_mail_open_times.length - 1] + "</td>";
                        break;
                    case "mail_open_times":
                        tb_data += "<td data-order=\"" + arr_mail_open_times[0] + "\">" + arr_mail_open_times + "</td>";
                }
            } 
            else
            if ((column == "mail_reply" || column == "mail_reply_count" || column == "mail_reply_content")) {
                switch (column) {
                    case "mail_reply":
                        tb_data += reply_emails['msg_info'][item['mailto_user_email']] ? "<td><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></td>" : "<td><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span></td>";
                        break;
                    case "mail_reply_count":
                        tb_data += reply_emails['msg_info'][item['mailto_user_email']] ? "<td>" + reply_emails['msg_info'][item['mailto_user_email']]['msg_time'].length + "</td>" : "<td>0</td>";
                        break;
                    case "mail_reply_content":
                        if (reply_emails['msg_info'][item['mailto_user_email']])
                            tb_data += `<td><i class="fas fa-eye fa-lg cursor-pointer" data-toggle="tooltip" title="View" onclick="viewReplyMails('` + item['mailto_user_email'] + `')"></i></td>`;
                        else
                            tb_data += "<td>-</td>";
                }
            } 
            else 
            if(column=='public_ip' || column=='user_agent' || column=='mail_client' || column=='platform' || column=='all_headers'){
                if(g_tb_data_single == true)
                    tb_data += "<td>" + $.parseJSON(item[column])[0] + "</td>";
                else
                    tb_data += "<td>" + $.parseJSON(item[column]).join(",\r\n") + "</td>";
            }
            else
            if (column == 'sending_status'){
                if(item[column] == undefined || item[column] == '')
                    tb_data += camp_table_status_def[4];
                else
                    tb_data += camp_table_status_def[item[column]];
            }                
            else
            if (column == 'send_time')
                tb_data += "<td data-order=\"" + item[column] + "\">" + UTC2Local(item[column]) + "</td>";
            else
            if(column.startsWith('wcm_') || column.startsWith('wpv_') || column.startsWith('wfs_') || column.startsWith('Page-'))
                tb_data += auxData_wcm(column,item);
            else
            if(column.startsWith('Field-'))
                tb_data += auxData_form_fields(column,item);
            else
            if (item[column] != undefined && item[column] != '')
                tb_data += "<td>" + item[column] + "</td>";
            else
                tb_data += "<td>-</td>";

        });
        tb_data += "</tr>";
    });
    $("#table_campaign_result tbody").append(tb_data);

    dt_mail_campaign_result = $('#table_campaign_result').DataTable({
        "bDestroy": true,
        "preDrawCallback": function(settings) {
            $('#table_campaign_result tbody').hide();
        },

        "drawCallback": function() {
            $('#table_campaign_result tbody').fadeIn(500);
        },
        'columnDefs': [{
            "targets": ["class_sel_col"],
            "className": "dt-center"
        }],

        dom: 'Blfrtip',
        buttons: [{
                extend: 'csvHtml5',
                filename: function() {
                    if ($('#Modal_export_file_name').val() == "") return $('#disp_camp_name').text();
                    else return $('#Modal_export_file_name').val();
                },
                exportOptions: {
                    columns: ':visible:not(:first-child)' //removes 1st SL.No column
                }
            },
            {
                extend: 'excelHtml5',
                filename: function() {
                    if ($('#Modal_export_file_name').val() == "") return $('#disp_camp_name').text();
                    else return $('#Modal_export_file_name').val();
                },
                title: function() {
                    return $('#disp_camp_name').text();
                },
                exportOptions: {
                    columns: ':visible:not(:first-child)' //removes 1st SL.No column
                }
            },
            {
                extend: 'pdfHtml5',
                filename: function() {
                    if ($('#Modal_export_file_name').val() == "") return $('#disp_camp_name').text();
                    else return $('#Modal_export_file_name').val();
                },
                title: function() {
                    return $('#disp_camp_name').text();
                },
                exportOptions: {
                    columns: ':visible:not(:first-child)' //removes 1st SL.No column
                }
            }
        ],

        initComplete: function() {
            var $buttons = $('.dt-buttons').hide();
            $('#modal_export_report_selector').on('change', function() {
                var btnClass = $(this).find(":selected")[0].id ? '.buttons-' + $(this).find(":selected")[0].id : null;
                if (btnClass) $buttons.find(btnClass).click();
            })
        }
    }); //initialize table


    dt_mail_campaign_result.on('order.dt_mail_campaign_result search.dt_mail_campaign_result', function() {
        dt_mail_campaign_result.column(0, {
            search: 'applied',
            order: 'applied'
        }).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
    applyCellColors();
    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
}

function auxData_wcm(column,m_item){
    var tb_data = [];
    $.each(data_web_live['page_visit'], function(i, wpv_item){
        if(m_item['id'] == wpv_item['cid'])
            switch(column.substring(4)){
                case 'cid': tb_data.push(wpv_item['cid']);
                            break;
                case 'session_id': tb_data.push(wpv_item['session_id']);
                            break;
                case 'public_ip': tb_data.push(wpv_item['public_ip']);
                            break;
                case 'internal_ip': tb_data.push(wpv_item['internal_ip']);
                            break;
                case 'user_agent': tb_data.push(wpv_item['user_agent']);
                            break;   
            }                             
    }); 

    if(column.startsWith('wcm_')){
        if(g_tb_data_single == true)
            return "<td>" + (tb_data[0]==undefined || tb_data[0]==''?'-':tb_data[0]) + "</td>"; 
        else
            return "<td>" + (tb_data.length==0?'-':tb_data.join(",\r\n")) + "</td>"; 
    }
    else
    if(column.startsWith('wpv_'))
        return auxData_wpv(column,m_item);
    else
    if(column.startsWith('wfs_') || column.startsWith('Page-'))
        return auxData_wfs(column,m_item);    
}

function auxData_wpv(column,m_item){ 
    var tb_data = '';
    var hit_times = [];
    $.each(data_web_live['page_visit'], function(i, wpv_item){
        if(m_item['id'] == wpv_item['cid'])
            switch(column.substring(4)){
                case 'activity':            //Store data in array for each hits
                case 'visit_count':
                case 'first_visit':
                case 'last_visit':
                case 'visit_times':   hit_times.push(UTC2Local(wpv_item['time']));
                            break;                       
            }                                    
    });

    switch(column.substring(4)){
        case 'activity': tb_data += hit_times.length>0 ? "<td><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></td>" : "<td><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span></td>";
                            break;
        case 'visit_count': tb_data += hit_times.length>0 ? "<td>"+hit_times.length+"</td>":"<td>0</td>";
                            break;
        case 'first_visit': tb_data += hit_times.length>0 ? "<td data-order=\"" + Local2LocalUNIX(hit_times[0]) + "\">"+hit_times[0]+"</td>":"<td>-</td>";
                            break;
        case 'last_visit': tb_data += hit_times.length>0 ? "<td data-order=\"" + Local2LocalUNIX(hit_times[hit_times.length-1]) + "\">"+hit_times[hit_times.length-1]+"</td>" : "<td>-</td>";
                            break;
        case 'visit_times': tb_data += hit_times.length>0 ? "<td>"+hit_times.join(",\r\n")+"</td>":"<td>-</td>";
                            break;
    }
    if(tb_data != '')
        return  tb_data;
    else
        return "<td>-</td>";
}

function auxData_wfs(column,m_item){ 
    var tb_data = 0;
    var hit_times = [];
    $.each(data_web_live['form_submit'], function(i, wfs_item){
        if(m_item['id'] == wfs_item['cid']){
            if(column.startsWith('Page-')){ //Page submit info
                if(column.split('-')[1] == wfs_item['page'])
                    tb_data++;
            }
            else
                switch(column.substring(4)){
                    case 'activity':            //Store data in array for each hits
                    case 'submission_count':
                    case 'first_submission':
                    case 'last_submission':
                    case 'submission_times':   hit_times.push(UTC2Local(wfs_item['time']));
                            break;  
                }       
        }                             
    });

    if(column.startsWith('Page-')){ //Page submit info
        tb_data = tb_data>0 ? "<td><center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></center></td>" : "<td><center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span></center></td>";
    }
    else
        switch(column.substring(4)){
            case 'activity': tb_data += hit_times.length>0 ? "<td><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></td>" : "<td><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span></td>";
                                    break;
            case 'submission_count': tb_data += hit_times.length>0 ? "<td>"+hit_times.length+"</td>":"<td>0</td>";
                                break;
            case 'first_submission': tb_data += hit_times.length>0 ? "<td data-order=\"" + Local2LocalUNIX(hit_times[0]) + "\">"+hit_times[0]+"</td>":"<td>-</td>";
                                    break;
            case 'last_submission': tb_data += hit_times.length>0 ? "<td data-order=\"" + Local2LocalUNIX(hit_times[hit_times.length-1]) + "\">"+hit_times[hit_times.length-1]+"</td>" : "<td>-</td>";
                                    break;
            case 'submission_times': tb_data += hit_times.length>0 ? "<td>"+hit_times.join(",\r\n")+"</td>":"<td>-</td>";
                                    break;
        }

    if(tb_data != '')
        return  tb_data;
    else
        return "<td>-</td>";
}

function auxData_form_fields(column,m_item){ 
    var hit_items = [];
    var tb_data='';
    $.each(data_web_live['form_submit'], function(i, wfs_item){
        if(m_item['id'] == wfs_item['cid']){
            if(JSON.parse(wfs_item.form_field_data)[column.substring(6)])
                hit_items.push(JSON.parse(wfs_item.form_field_data)[column.substring(6)]);
        }
                   
    });
    
    if(g_tb_data_single == true){
        if(hit_items[0] == undefined)
            tb_data = "<td>-</td>";
        else
        if(hit_items[0] == true)
            tb_data = "<td><center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i><span hidden>Yes</span></center></td>";
        else
        if(hit_items[0] == false)
            tb_data = "<td><center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span></center></td>";
        else
            tb_data += "<td>" + hit_items[0] + "</td>";  
    }
    else
        tb_data += hit_items.length>0 ? "<td>"+hit_items.join(",\r\n")+"</td>":"<td>-</td>";

    return tb_data;
}


function applyCellColors(){
    tb_camp_result_colums_list.unshift(""); // adds SLNO colum to match index
    dt_mail_campaign_result.columns().every( function ( colIdx, tableLoop, rowLoop ) {  

        dt_mail_campaign_result.rows().every( function ( rowIdx, tableLoop, rowLoop ) {

            switch(tb_camp_result_colums_list[colIdx]){
                case "mailto_user_name": 
                case "mailto_user_email": 
                case "sending_status": 
                case "send_time": 
                case "send_error": 
                case "mail_open": 
                case "mail_open_count": 
                case "mail_first_open": 
                case "mail_last_open": 
                case "mail_open_times": 
                case "public_ip": 
                case "user_agent": 
                case "mail_client": 
                case "platform": 
                case "all_headers": 
                case "mail_reply": 
                case "mail_reply_count": 
                case "mail_reply_content": //applies default color
                        break;

                case "wcm_cid": 
                case "wcm_session_id": 
                case "wcm_public_ip": 
                case "wcm_internal_ip": 
                case "wcm_user_agent":
                case "wpv_activity":
                case "wpv_visit_count":
                case "wpv_first_visit":
                case "wpv_last_visit":
                case "wpv_visit_times":
                        var cell = dt_mail_campaign_result.cell({ row: rowIdx, column: colIdx }).node();
                        if(rowIdx%2 == 0)
                            $(cell).addClass('ccl-3');
                    break; 
                case "wfs_activity":
                case "wfs_submission_count":
                case "wfs_first_submission":
                case "wfs_last_submission":
                case "wfs_submission_times":
                        var cell = dt_mail_campaign_result.cell({ row: rowIdx, column: colIdx }).node();
                        if(rowIdx%2 == 0)
                            $(cell).addClass('ccl-3');
                    break; 
                default:    var cell = dt_mail_campaign_result.cell({ row: rowIdx, column: colIdx }).node();
                            if(tb_camp_result_colums_list[colIdx].startsWith('Page-') && rowIdx%2 == 0)
                                    $(cell).addClass('ccl-3');
                            else
                            if(tb_camp_result_colums_list[colIdx].startsWith('Field-') && rowIdx%2 == 0)
                                    $(cell).addClass('ccl-4');
            }     
        });     
    });
}

//==========Start Web tracker charts=========================
function updatePieOverViewWeb(total_user_email_count,total_lpv_percent) {
    $("#radialchart_overview_webcamp").attr("hidden", false);
    $("#radialchart_overview_webcamp").parent().children().remove('.loadercust');

    var matched_pages =  {page_count_series:[], page_count_percent_series:[], page_name_series:[]};
    matched_pages.page_count_percent_series[0] = total_lpv_percent;
    matched_pages.page_name_series[0] = 'Page visit';

    for(var i=1; i<=g_page_count; i++){
        matched_pages.page_count_series[i]=0;
        matched_pages.page_name_series[i] = 'P' + i + ' submission';
    }

    $.each(data_matched.form_submission, function(mail_id, user){
        for(var i=1; i<=g_page_count; i++)
            if(user[i]!=undefined){
                matched_pages.page_count_series[i]+=1; //unique count. All entry count is user[i].length
                matched_pages.page_count_percent_series[i]= +(matched_pages.page_count_series[i]/total_user_email_count*100).toFixed(2);
            }
    });

    var options = {
        series: matched_pages.page_count_percent_series, 
        chart: {
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                //offsetX: -15,
                hollow: {
                    size: '45%',
                },
                dataLabels: {
                    name: {
                        fontSize: '14px',
                    },
                    value: {
                        fontSize: '12px',
                    },
                },dataLabels: {
                      show: true,
                        value: {
                            show: true,
                            formatter: function (val) {
                                return val + '%'
                            }
                        },
                  },
            },
        },
        legend: {
            show: true,
            position: 'bottom',
            offsetY: 8,
            floating: true,
            itemMargin: {
                horizontal: 5,
                vertical: 2,
            },
            onItemClick: {
                toggleDataSeries: true
            },
            onItemHover: {
                highlightDataSeries: true
            },

        },
        labels: matched_pages.page_name_series,
        colors: ['#4CAF50', '#e6b800', '#F86624'],
    };

    radialchart_overview_webcamp = new ApexCharts(
        document.querySelector("#radialchart_overview_webcamp"),
        options
    );
    radialchart_overview_webcamp.render();
}

function updatePieTotalPV(total_user_email_count, total_pv, pv_percent) {
    $("#piechart_total_pv").attr("hidden", false);
    $("#piechart_total_pv").parent().children().remove('.loadercust');

    var lpv_not_percent = 100 - pv_percent;
    var options = {
        series: [pv_percent, lpv_not_percent],
        chart: {
            type: 'donut',
        },
        plotOptions: {
            pie: {
                offsetY: 0,
                customScale: 1,
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '14px',
                            formatter: function(val) {
                                return val + "%";
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                return pv_percent + "% (" + total_pv + "/" + total_user_email_count + ")";
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false
        },
        tooltip: {
            enabled: true,
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                if (seriesIndex == 0)
                    return `<div class="chart-tooltip">Page visit: ` + series[seriesIndex] + `%</div>`;
                else
                    return `<div class="chart-tooltip">Page non-visit: ` + series[seriesIndex] + `%</div>`;
            },
        },
        colors: ['#4CAF50', '#d9d9d9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    piechart_total_pv = new ApexCharts(
        document.querySelector("#piechart_total_pv"),
        options
    );
    piechart_total_pv.render();
}

function updatePieTotalFS(total_user_email_count, lps_count) {
    $("#piechart_total_fs").attr("hidden", false);
    $("#piechart_total_fs").parent().children().remove('.loadercust');

    var lps_percent = +(lps_count / total_user_email_count * 100).toFixed(2);
    var lps_not_percent = 100 - lps_percent;
    var options = {
        series: [lps_percent, lps_not_percent],
        chart: {
            type: 'donut',
        },
        plotOptions: {
            pie: {
                offsetY: 0,
                customScale: 1,
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '14px',
                            formatter: function(val) {
                                return val + "%";
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                return lps_percent + "% (" + lps_count + "/" + total_user_email_count + ")";
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false
        },
        tooltip: {
            enabled: true,
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                if (seriesIndex == 0)
                    return `<div class="chart-tooltip">Form submission: ` + series[seriesIndex] + `%</div>`;
                else
                    return `<div class="chart-tooltip">Form non submission: ` + series[seriesIndex] + `%</div>`;
            },
        },
        colors: ['#e6b800', '#d9d9d9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    piechart_total_fs = new ApexCharts(
        document.querySelector("#piechart_total_fs"),
        options
    );
    piechart_total_fs.render();
}

function updatePieTotalSuspected(total_pv_nonmatch, total_fs_nonmatch, pv_nonmatch_percent, fs_nonmatch_percent) {
    $("#piechart_total_suspect").attr("hidden", false);
    $("#piechart_total_suspect").parent().children().remove('.loadercust');

    var options = {
        series: [pv_nonmatch_percent, fs_nonmatch_percent],
        chart: {
            type: 'donut',
        },
        plotOptions: {
            pie: {
                offsetY: 0,
                customScale: 1,
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '14px',
                            formatter: function(val) {
                                return val==pv_nonmatch_percent? "Count: " + total_pv_nonmatch: "Count: " + total_fs_nonmatch;
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function(w) {
                                return total_pv_nonmatch + ":" + total_fs_nonmatch;
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false
        },
        tooltip: {
            enabled: true,
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                if (seriesIndex == 0)
                    return `<div class="chart-tooltip">Page visits: ` + total_pv_nonmatch  + `</div>`;
                else
                    return `<div class="chart-tooltip">Form submissions: ` + total_fs_nonmatch + `</div>`;
            },
        },
        colors: ['#F86624', '#d9d9d9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    piechart_total_suspect = new ApexCharts(
        document.querySelector("#piechart_total_suspect"),
        options
    );
    piechart_total_suspect.render();
}