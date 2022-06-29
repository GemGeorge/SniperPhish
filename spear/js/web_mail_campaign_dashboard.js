var dt_web_mail_campaign_result, web_mailcamp_list;
var chart_live_mailcamp;
var radialchart_overview_mailcamp, piechart_mail_total_sent, piechart_mail_total_mail_open, piechart_mail_total_replied;
var reply_emails = [], form_field_cols=[];
var g_tb_data_single = true;
var allReportColList=[], allReportColListSelected=[];
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

var dic_all_col={rid:'RID', user_name:'Name', user_email:'Email', sending_status:'Status', send_time:'Sent Time', send_error:'Send Error',mail_open:'Mail Open',mail_open_count:'Mail(open count)',mail_first_open:'Mail(first open)',mail_last_open:'Mail(last open)',mail_open_times:'Mail(all open times)',public_ip:'Public IP',user_agent:'User Agent',mail_client:'Mail Client',platform:'Platform',device_type:'Device Type',all_headers:'HTTP Headers',mail_reply:'Mail Reply',mail_reply_count:'Mail (reply count)',mail_reply_content:'Mail (reply content)', country:'Country', city:'City', zip:'Zip', isp:'ISP', timezone:'Timezone', coordinates:'Coordinates', wcm_rid:'RID (W)', wcm_session_id:'Session ID', wcm_public_ip:'Public IP (W)', wcm_browser:'Browser', wcm_platform:'Platform (W)', wcm_screen_res:'Screen Res', wcm_device_type:'Device Type', wcm_user_agent:'User Agent (W)', wcm_country:'Country (W)', wcm_city:'City (W)', wcm_zip:'Zip (W)', wcm_isp:'ISP (W)', wcm_timezone:'Timezone (W)', wcm_coordinates:'Coordinates (W)', wpv_activity:'Page Visit', wpv_visit_count:'Visit Count', wpv_first_visit:'First Visit', wpv_last_visit:'Last Visit', wpv_visit_times:'Visit Times', wfs_activity:'Form Submission', wfs_submission_count:'Submission Count', wfs_first_submission:'First Submission', wfs_last_submission:'Last Submission', wfs_submission_times:'Submission Times'};

$(function() {
    loadTableCampaignList();
    Prism.highlightAll();
});

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
    $.each(web_mailcamp_list.mailcamp_list, function(key, mailcamp) {
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
    $.each(web_mailcamp_list.webtracker_list, function(key, webtracker) {
        if(webtracker['tracker_id'] == tracker_id){
            $('#modal_table_webtracker_info > tbody > tr:eq(0) > td:eq(1)').text(webtracker['tracker_id']);
            $('#modal_table_webtracker_info > tbody > tr:eq(1) > td:eq(1)').text(webtracker['tracker_name']);
            $('#modal_table_webtracker_info > tbody > tr:eq(2) > td:eq(1)').text(webtracker['date']);
            $('#modal_table_webtracker_info > tbody > tr:eq(3) > td:eq(1)').text(webtracker['start_time']!=''?webtracker['start_time']:"NA");
        }            
    });
});

//------------------------------------------------
//Select 1
$("#tb_camp_result_colums_list_mcamp").select2().on("select2:select", function(evt) {   //requires to function re-ordering
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).find('optgroup').append($element);
    $(this).trigger("change");
});

//Select 2
$("#tb_camp_result_colums_list_wcm").select2().on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).find('optgroup').append($element);
    $(this).trigger("change");
});

//Select 3
$("#tb_camp_result_colums_list_wpv").select2().on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

//Select 4
$("#tb_camp_result_colums_list_wfs").select2().on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

//Select 5
$("#tb_camp_result_colums_list_wfs_data").select2().on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$.each(["tb_camp_result_colums_list_mcamp","tb_camp_result_colums_list_wcm","tb_camp_result_colums_list_wpv","tb_camp_result_colums_list_wfs","tb_camp_result_colums_list_wfs_data"], function(i, sel_item){
    $("#"+sel_item).parent().find("ul.select2-selection__rendered").sortable({
        containment: 'parent',
        update: function() {
            getAllReportColListSelected();
        }
    });    
});

function getAllReportColListSelected(){
    allReportColListSelected=[];

    $.each(["tb_camp_result_colums_list_mcamp","tb_camp_result_colums_list_wcm","tb_camp_result_colums_list_wpv","tb_camp_result_colums_list_wfs","tb_camp_result_colums_list_wfs_data"], function(i, sel_item){
        $.each($('#'+sel_item).find("option"), function () {
            allReportColList[$(this).text()] = $(this).val();
        });

        $.each($('#'+sel_item).parent().find("ul.select2-selection__rendered").children("li[title]"), function () {
            allReportColListSelected.push(allReportColList[this.title]);
        });
    });
}
//----------------------------------------------

function refreshDashboard(f_refresh=false) {
    if (g_campaign_id != '' && g_tracker_id != '')
        campaignSelected(g_campaign_id, g_tracker_id, f_refresh);
    else
        toastr.error('', 'Campaign not selected');
}

function startLoaders() {
    $.each(['chart_live_mailcamp','radialchart_overview_mailcamp','piechart_mail_total_sent','piechart_mail_total_mail_open','piechart_mail_total_replied','table_campaign_result','piechart_total_pv','piechart_total_fs','piechart_total_suspect','radialchart_overview_webcamp'], function(i, id){
        $('#'+id).attr('hidden', true);
        $('#'+id).parent().find(".loader").length==0?$('#'+id).parent().append(displayLoader("Loading...")):null;
    });
}

function loadTableCampaignList() {
     $.post({
        url: "manager/web_mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_campaign_list_web_mail",
        })
    }).done(function (data) {
        if(data){
            web_mailcamp_list = data;
            if(!data.mailcamp_list.error){
                $.each(data.mailcamp_list, function(key, value) {
                    if(value.camp_status == 2 || value.camp_status == 3 || value.camp_status == 4) //removes inactive and scheduled
                        $("#modal_mailcamp_selector").append(`<option value="` + value.campaign_id + `">` + value.campaign_name + `</option>`);
                });
            }
            if(!data.webtracker_list.error){
                $.each(data.webtracker_list, function(key, value) {
                    if(!(value['start_time'] == undefined || value.start_time == '')) //removes not started
                        $("#modal_web_tracker_selector").append(`<option value="` + value.tracker_id + `">` + value.tracker_name + `</option>`);
                });

                if (g_campaign_id == '' && g_tracker_id == '')
                    $("#ModalCampaignList").modal("toggle");
                else{
                    $("#modal_mailcamp_selector").val(g_campaign_id).trigger("change");
                    $("#modal_web_tracker_selector").val(g_tracker_id).trigger("change");
                }
            }
        }
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
        campaignSelected($("#modal_mailcamp_selector").val(),$("#modal_web_tracker_selector").val(),false);
        $('#ModalCampaignList').modal('toggle');
        window.history.replaceState(null,null, location.pathname + '?mcamp=' + $("#modal_mailcamp_selector").val() + '&tracker=' + $("#modal_web_tracker_selector").val());
    }
}

function campaignSelected(campaign_id,tracker_id,f_refresh=false) {
    g_campaign_id = campaign_id;
    g_tracker_id = tracker_id;

    $('input[name="radio_table_data"]:checked').val() == "radio_table_data_single"?g_tb_data_single=true:g_tb_data_single=false;

    $.each([chart_live_mailcamp,radialchart_overview_mailcamp,piechart_mail_total_sent,piechart_mail_total_mail_open,piechart_mail_total_replied,dt_web_mail_campaign_result,radialchart_overview_webcamp,piechart_total_pv,piechart_total_fs,piechart_total_suspect], function(i, graph){
        try {
            graph.destroy();
        } catch (err) {}
    });

    startLoaders();
    getAccessInfo();
    $('#table_campaign_result thead').empty();
    $("#table_campaign_result tbody > tr").remove();
    updateMailCampGraphs();
    //------------------------

    $.post({
        url: "manager/web_mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_web_mail_tracker_from_id",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id
        })
    }).done(function (data) {
        $("#disp_camp_name").text(data.mailcamp_info.campaign_name);
        $("#disp_web_tracker_name").text(data.webtracker_info.tracker_name);   
        $('#disp_camp_status').html(camp_status_def[data.mailcamp_info.camp_status]);
        $('#disp_camp_start').text(data.mailcamp_info.scheduled_time);
        $('#disp_camp_end').text(data.mailcamp_info.stop_time=='-'?' In-progress':data.mailcamp_info.stop_time);
        $('#Modal_export_file_name').val(data.mailcamp_info.campaign_name);
        g_page_count = data.webtracker_info.tracker_step_data.web_forms.count;
        var tracker_step_data = data.webtracker_info.tracker_step_data;

        $('#table_campaign_info > tbody > tr:eq(0) > td:eq(1)').text(campaign_id);
        $('#table_campaign_info > tbody > tr:eq(1) > td:eq(1)').text(data.mailcamp_info.date);
        $('#table_campaign_info > tbody > tr:eq(2) > td:eq(1)').text(data.mailcamp_info.campaign_data.user_group.name + " (ID :" + data.mailcamp_info.campaign_data.user_group.id + ")");
        $('#table_campaign_info > tboDy > tr:eq(3) > td:eq(1)').text(data.mailcamp_info.campaign_data.mail_template.name + " (ID: " + data.mailcamp_info.campaign_data.mail_template.id + ")");
        $('#table_campaign_info > tbody > tr:eq(4) > td:eq(1)').text(data.mailcamp_info.campaign_data.mail_sender.name + " (ID: " + data.mailcamp_info.campaign_data.mail_sender.id + ")");

        $('#table_web_tracker_info > tbody > tr:eq(0) > td:eq(1)').text(tracker_id);
        $('#table_web_tracker_info > tbody > tr:eq(1) > td:eq(1)').text(data.webtracker_info.date);
        $('#table_web_tracker_info > tbody > tr:eq(2) > td:eq(1)').text("Yes");
        $('#table_web_tracker_info > tbody > tr:eq(3) > td:eq(1)').text(g_page_count>0?"Yes":"No");
        $('#table_web_tracker_info > tbody > tr:eq(4) > td:eq(1)').text(g_page_count);

        if(f_refresh == false){
            $("#tb_camp_result_colums_list_wfs_data").empty();        
        
            for (var i=1; i<=g_page_count; i++)
                $("#tb_camp_result_colums_list_wfs").append(`<option value="SPPage-` + i + `" selected>Page-` + i + ` Submission</option>`);

            $.each(tracker_step_data.web_forms.data, function(i, page_n) {
                $.each(page_n.form_fields_and_values, function(field_type, form_field) {
                    if(field_type != "FSB"){
                        $("#tb_camp_result_colums_list_wfs_data").append('<option value="Field-' + form_field.idname + '" selected>Field-' + form_field.idname + '</option>');
                        form_field_cols.push('Field-' + form_field.idname);
                    }
                });
            });
            $("#tb_camp_result_colums_list_wfs").trigger("change");
            $('#tb_camp_result_colums_list_wfs_data').trigger("change");
        }

        updateWebCampGraphs(data.mailcamp_info.campaign_data.user_group.id);
        getTimelineData(data.mailcamp_info.campaign_data.user_group.id);
        loadTableCampaignResult();
    }); 
}

function updateMailCampGraphs(){
    $.post({
        url: "manager/mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_campaign_from_campaign_list_id",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id,
        })
    }).done(function (data) {
        if(!data.error){            
            var sent_failed_count=data.live_mcamp_data.sent_failed_count;
            var sent_success_count=data.live_mcamp_data.sent_success_count;
            var sent_mail_count = sent_failed_count + sent_success_count;
            var mail_open_count = data.live_mcamp_data.mail_open_count;

            updateProgressbar(data.camp_status, data.campaign_data.mail_sender.id, data.campaign_data.user_group.id, data.campaign_data.mail_template.id, sent_mail_count, sent_success_count, sent_failed_count, mail_open_count);
        }
        else
            toastr.warning('', data.live_mcamp_data.error);            
    }); 
}

function updateWebCampGraphs(user_group_id){
    $.post({
        url: "manager/web_mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_webcamp_graph_data",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id,
            user_group_id: user_group_id,
            page_count: g_page_count
        })
    }).done(function (data) {
        if(!data.error){ 
            var total_user_email_count = data.total_user_count;           
            var total_pv = data.total_pv;
            var total_fs = data.total_fs;
            var total_pv_nonmatch = data.total_suspect_pv;
            var total_fs_nonmatch = data.total_suspect_fs;
            var fs_counts = data.fs_counts;

            var pv_percent = +(total_pv*100/total_user_email_count).toFixed(2); //+ => to number (unary plus)
            var pv_nonmatch_percent = +((total_pv_nonmatch*100/(total_pv_nonmatch+total_fs_nonmatch)).toFixed(2));
            pv_nonmatch_percent=isNaN(pv_nonmatch_percent)?0:pv_nonmatch_percent;   //divide by zero issue

            var fs_nonmatch_percent = 100-pv_nonmatch_percent;

            updatePieOverViewWeb(total_user_email_count,pv_percent,fs_counts);
            updatePieTotalPV(total_user_email_count,total_pv,pv_percent);
            updatePieTotalFS(total_user_email_count,total_fs);
            updatePieTotalSuspected(total_pv_nonmatch,total_fs_nonmatch,pv_nonmatch_percent,fs_nonmatch_percent);
        }
        else
            toastr.warning('', data.live_mcamp_data.error);            
    }); 
}

function updateProgressbar(mailcamp_status, sender_list_id, user_group_id, mail_template_id, sent_mail_count, sent_success_count, sent_failed_count, mail_open_count) {
    $.post({
        url: "manager/mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_user_group_data",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id
        })
    }).done(function (data) {
        if(!data.error){
            $('#user_group_name').val(data.user_group_name);

            var total_user_email_count = Object.keys(data.user_data).length;
            var sent_mail_percent = +(sent_mail_count / total_user_email_count * 100).toFixed(2);
            var sent_mail_success_percent = +(sent_success_count / total_user_email_count * 100).toFixed(2);

            $("#progressbar_status").children().width(sent_mail_percent + "%");
            $("#progressbar_status").children().text(sent_mail_count + "/" + total_user_email_count + " (" + sent_mail_percent + "%)");
            if (sent_mail_percent == 100)
                $("#progressbar_status").children().addClass("bg-success");
            else
                $("#progressbar_status").children().removeClass("bg-success");

            updatePieTotalSent(total_user_email_count, sent_mail_count, sent_failed_count);

            var mail_open_percent = +(mail_open_count / total_user_email_count * 100).toFixed(2);;
            updatePieTotalMailOpen(total_user_email_count, mail_open_count, mail_open_percent);
            updatePieOverViewEmail(sent_mail_success_percent, mail_open_percent);
            if (mailcamp_status != 0 && $('input[name="radio_mail_reply_check"]:checked').val() == "reply_yes")
                updatePieTotalMailReplied(total_user_email_count);
            else{
                $("#piechart_mail_total_replied").attr("hidden", false);
                $("#piechart_mail_total_replied").parent().children().remove('.loadercust');
            }
        }
        else
            toastr.error('', data.error);        
    });
}

function getTimelineData(user_group_id){
    $.post({
        url: "manager/web_mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_timeline_data_web",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id,
            user_group_id: user_group_id
        })
    }).done(function (data) {
        if(!data.error){
            updateLiveCampData(data.scatter_data_mail, data.scatter_data_web, data.timestamp_conv, data.timezone);
        }
    }); 
}

function updateLiveCampData(scatter_data_mail, scatter_data_web, timestamp_conv, timezone) {
    $("#chart_live_mailcamp").attr("hidden", false);
    $("#chart_live_mailcamp").parent().children().remove('.loadercust');

    var y_axis_labels = {0:"", 1:"Mail Campaign", 2:"Page Visit", 3:"Form Submission"};
    var graph_data = {'in_progress': {"hit_time":[],"user_email":[],"user_name":[]},
                       'sent_success': {"hit_time":[],"user_email":[],"user_name":[]},
                       'send_error': {"hit_time":[],"user_email":[],"user_name":[]},
                       'mail_open': {"hit_time":[],"user_email":[],"user_name":[]},
                       'page_visit': {"hit_time":[],"user_email":[],"user_name":[]},
                       'form_submission': {"hit_time":[],"user_email":[],"user_name":[],"page":[]}};

    $.each(scatter_data_mail, function(i, item) {
        switch (item['sending_status']) {
            case 1:     //In progress
                graph_data.in_progress.hit_time.push([Number(item.send_time), 1]);
                graph_data.in_progress.user_email.push(item.user_email);
                graph_data.in_progress.user_name.push(item.user_name);
                break;
            case 2:     //Send success
                graph_data.sent_success.hit_time.push([Number(item.send_time), 1]);
                graph_data.sent_success.user_email.push(item.user_email);
                graph_data.sent_success.user_name.push(item.user_name);
                break;
            case 3:     //Send error
                graph_data.send_error.hit_time.push([Number(item.send_time), 1]);
                graph_data.send_error.user_email.push(item.user_email);
                graph_data.send_error.user_name.push(item.user_name);
                break;
        }
        
        var arr_mail_open_times = item.mail_open_times;
        $.each(arr_mail_open_times, function(i, mail_open_time) {            
            if(g_tb_data_single){
                if ($.inArray(item['user_email'],  graph_data.mail_open.user_email) <= -1 ) {
                    graph_data.mail_open.hit_time.push([Number(mail_open_time), 1]);
                    graph_data.mail_open.user_email.push(item.user_email);
                    graph_data.mail_open.user_name.push(item.user_name);
                }
            }
            else{
                graph_data.mail_open.hit_time.push([Number(mail_open_time), 1]);
                graph_data.mail_open.user_email.push(item.user_email);
                graph_data.mail_open.user_name.push(item.user_name);
            }
        });
    });

    //Page visit
    var dummy_rid=[];
    $.each(scatter_data_web.pv, function(i, user_info) {
        var rid = Object.keys(user_info)[0];
        if(g_tb_data_single){            
            if ($.inArray(rid, dummy_rid) <= -1 ) {
                graph_data.page_visit.hit_time.push([Number(user_info[rid].time), 2]);
                graph_data.page_visit.user_email.push(user_info[rid].user_email);
                graph_data.page_visit.user_name.push(user_info[rid].user_name);
            }
            dummy_rid.push(rid);
        }
        else{
            graph_data.page_visit.hit_time.push([Number(user_info[rid].time), 2]);
            graph_data.page_visit.user_email.push(user_info[rid].user_email);
            graph_data.page_visit.user_name.push(user_info[rid].user_name);
        }
    });

    //Form submision
    dummy_rid=[];
    $.each(scatter_data_web.fs, function(i, user_info) {
        var rid = Object.keys(user_info)[0];
        if(g_tb_data_single){
            if ($.inArray(rid, dummy_rid) <= -1 ) {
                graph_data.form_submission.hit_time.push([Number(user_info[rid].time), 3]);
                graph_data.form_submission.user_email.push(user_info[rid].user_email);
                graph_data.form_submission.user_name.push(user_info[rid].user_name);
                //graph_data.form_submission.page.push(entry.page);
            }
            dummy_rid.push(rid);
        }
        else{
            graph_data.form_submission.hit_time.push([Number(user_info[rid].time), 3]);
            graph_data.form_submission.user_email.push(user_info[rid].user_email);
            graph_data.form_submission.user_name.push(user_info[rid].user_name);
            //graph_data.form_submission.page.push(entry.page);
        }
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
                    return Unix2StdDateTime(val/1000,timezone);
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
                var localdate = timestamp_conv[ w.config.series[seriesIndex].data[dataPointIndex][0]];
                switch (seriesIndex) {
                    case 0:     //In progress
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.in_progress.user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.in_progress.user_email[dataPointIndex] + `</div>`;
                        break;
                    case 1:     //Sent success
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.sent_success.user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.sent_success.user_email[dataPointIndex] + `</div>`;
                        break;
                    case 2:     //Mail opened
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.mail_open.user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.mail_open.user_email[dataPointIndex] + `</div>`;
                        break;
                    case 3:     //Send error
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.send_error.user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.send_error.user_email[dataPointIndex] + `</div>`;
                        break;
                    case 4:
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.page_visit.user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.page_visit.user_email[dataPointIndex] + `</div>`;
                        break;
                    case 5:
                        return `<div class="chart-tooltip">Time: ` + localdate + "<br/>Name: " + graph_data.form_submission.user_name[dataPointIndex] + ` <br/>Email: ` + graph_data.form_submission.user_email[dataPointIndex] + `</div>`;
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
        $('#summernote_reply_mail' + i).summernote('code', reply_emails['msg_info'][mail_id]['msg_body'][i].replace(/\r\n/g, "<br/>"));
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

    var sent_percent = +((sent_mail_count-sent_failed_count) / total_user_email_count * 100).toFixed(2);
    var non_sent_percent = +(100 - sent_percent).toFixed(2);
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
                    size: '80%',
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
                                return +(sent_mail_count/total_user_email_count*100).toFixed(2) + "% (" + (sent_mail_count-sent_failed_count) + "/" + total_user_email_count + ")";
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
    var non_open_percent = +(100 - open_mail_percent).toFixed(2);;
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
                    size: '80%',
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

function updatePieTotalMailReplied(total_user_email_count) {
    $.post({
        url: "manager/mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_mail_replied",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id,
        }),
    }).done(function (data) {
        loadTableCampaignResult();
        $("#piechart_mail_total_replied").attr("hidden", false);
        $("#piechart_mail_total_replied").parent().children().remove('.loadercust');
        if (!data.error) {
            window.reply_emails = data;

            var reply_count_unique = Object.keys(data.msg_info).length;
            var reply_percent = +(reply_count_unique / total_user_email_count * 100).toFixed(2);
            var non_reply_percent = +(100 - reply_percent).toFixed(2);
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
                            size: '80%',
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
            radialchart_overview_mailcamp.updateSeries(arr_chart_data)
        }
        else{
            toastr.error('', data.error);
            $("#piechart_mail_total_replied").text('Loading error!');
        }
    }).fail(function(response) {
        toastr.error('',  response.statusText);
        $("#piechart_mail_total_replied").parent().children().remove('.loadercust');
    });  
}

function loadTableCampaignResult() {
    try {
        dt_web_mail_campaign_result.destroy();
    } catch (err) {}

    $("#table_campaign_result").attr("hidden", false);
    $("#table_campaign_result").parent().children().remove('.loadercust');

    $('#table_campaign_result thead').empty();
    $("#table_campaign_result tbody > tr").remove();


    getAllReportColListSelected();
    $('input[name="radio_table_data"]:checked').val() == "radio_table_data_single"?g_tb_data_single=true:g_tb_data_single=false;

    var arr_tb_heading=[];  
    arr_tb_heading.push({ data: 'sn', title: "SN" });

    $.each(allReportColListSelected, function(index, item) {
        if (item.startsWith("Field-"))
            arr_tb_heading.push({ data: item, title : item});
        else
            if (item.startsWith("SPPage-"))
                arr_tb_heading.push({ data: item, title : item.substring(2) + ' Submission'});
            else
                arr_tb_heading.push({ data: item, title : dic_all_col[item]});
    });

    var no_sort_col=[0];    //0=sn field
    $.each(allReportColListSelected, function(index, item) {
        if(item.startsWith('wcm') || item.startsWith('wpv') || item.startsWith('wfs') || item.startsWith('Field-') || item.startsWith('SPPage-'))
            no_sort_col.push(index+1);
    });

    dt_web_mail_campaign_result = $('#table_campaign_result').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            url:'manager/web_mail_campaign_manager',
            type: "POST",
            contentType: "application/json; charset=utf-8",
            data: function (d) {   //request parameters here
                    d.action_type = 'multi_get_live_campaign_data_web_mail';
                    d.tk_id = g_tk_id;
                    d.campaign_id = g_campaign_id;
                    d.tracker_id = g_tracker_id;
                    d.selected_col = allReportColListSelected;
                    d.tb_data_single = g_tb_data_single;
                    return JSON.stringify(d);
                },
            dataSrc: function ( resp ){
                for (var i=0; i<resp.data.length; i++){
                    resp.data[i]['sn'] = i+1;
                    if(resp.data[i].mail_open==true)
                        resp.data[i].mail_open = "<center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i></center>";
                    else
                        resp.data[i].mail_open = "<center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i></center>";
                    resp.data[i].sending_status= camp_table_status_def[resp.data[i].sending_status];

                    if(Object.keys(reply_emails).length >= 0 &&  reply_emails.hasOwnProperty('msg_info') && reply_emails.msg_info.hasOwnProperty(resp.data[i].user_email) ){
                        resp.data[i].mail_reply = `<center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i></center>`;
                        resp.data[i].mail_reply_count = reply_emails.msg_info[resp.data[i].user_email].msg_time.length;
                        resp.data[i].mail_reply_content = `<center><i class="fas fa-eye fa-lg cursor-pointer" data-toggle="tooltip" title="View" onclick="viewReplyMails('` + resp.data[i].user_email + `')"></i></center>`;
                    }
                    else{
                        resp.data[i].mail_reply = `<center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i></center>`;
                        resp.data[i].mail_reply_count = 0;
                    }

                    if(resp.data[i].wpv_activity==true)
                        resp.data[i].wpv_activity = "<center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i></center>";
                    else
                        resp.data[i].wpv_activity = "<center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i></center>";

                    if(resp.data[i].wfs_activity==true)
                        resp.data[i].wfs_activity = "<center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i></center>";
                    else
                        resp.data[i].wfs_activity = "<center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i></center>";

                    for (key in resp.data[i]) {
                        if (key.startsWith('SPPage-'))
                            if(resp.data[i][key]==true)
                                resp.data[i][key] = "<center><i class='fas fa-check fa-lg text-success' data-toggle='tooltip' title='Yes'></i></center>";
                            else
                                resp.data[i][key] = "<center><i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i></center>";
                    }
                }
                return resp.data
            }
        },
        'columns': arr_tb_heading,
        'pageLength': 20,
        'lengthMenu': [[20, 50, 100, 500, 1000, -1], [20, 50, 100, 500, 1000, "All"]],
        'aoColumnDefs': [{'bSortable': false, 'aTargets': no_sort_col}],
        drawCallback: function() {
            applyCellColors();
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            $("label>select").select2({minimumResultsForSearch: -1, });
        }
    });
}

function applyCellColors(){
    allReportColListSelected.unshift(""); // adds SLNO colum to match index
    dt_web_mail_campaign_result.columns().every( function ( colIdx, tableLoop, rowLoop ) {  

        dt_web_mail_campaign_result.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
            var col_name=allReportColListSelected[colIdx];
            var cell = dt_web_mail_campaign_result.cell({ row: rowIdx, column: colIdx }).node();

            if(col_name.startsWith('wcm_') || col_name.startsWith('wpv_')){                
                if(rowIdx%2 == 0)
                    $(cell).addClass('ccl-3');
            }
            else
            if((col_name.startsWith('wfs_') || col_name.startsWith('SPPage-')) && rowIdx%2 == 0){
                if(rowIdx%2 == 0)
                    $(cell).addClass('ccl-4');
            }
            else
            if(col_name.startsWith('Field-') && rowIdx%2 == 0)
                $(cell).addClass('ccl-2');
        });     
    });
}

//==========Start Web tracker charts=========================
function updatePieOverViewWeb(total_user_email_count,lpv_percent,fs_counts) {
    $("#radialchart_overview_webcamp").attr("hidden", false);
    $("#radialchart_overview_webcamp").parent().children().remove('.loadercust');

    var matched_pages = {page_name_series:[], page_count_percent_series:[]};
    matched_pages.page_count_percent_series[0] = lpv_percent;
    matched_pages.page_name_series[0] = 'Page visit';

    $.each(fs_counts, function(page_n, fs_count_page_n) {
        matched_pages.page_count_percent_series[page_n] = +(fs_count_page_n/total_user_email_count*100).toFixed(2);
        matched_pages.page_name_series[page_n] = 'P' + page_n + ' submission';
    });

    var options = {
        series:  matched_pages.page_count_percent_series, 
        chart: {
            type: 'radialBar',
            width: '115%'
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                //offsetX: -15,
                hollow: {
                    size: '45%',
                },
                dataLabels: {
                    show: true,
                        name: {
                        fontSize: '14px',
                    },
                    value: {
                        show: true,
                        formatter: function (val) {
                            return val + '%'
                        },
                        fontSize: '12px',
                    },
                  },
            },
        },
        legend: {
            show: true,
            position: 'bottom',
            floating: false,
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

    var lpv_not_percent = +(100 - pv_percent).toFixed(2);
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
                    size: '80%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '12px',
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
    var lps_not_percent = +(100 - lps_percent).toFixed(2);
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
                    size: '80%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '12px',
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
                    size: '80%',
                    labels: {
                        show: true,
                        name: {
                            show: false,
                        },
                        value: {
                            show: true,
                            fontSize: '12px',
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
        colors: ['#4CAF50', '#e6b800'],
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

//------------------------Public Access---------------

var t1 = new ClipboardJS('#btn_copy_quick_tracker', {
    target: function(trigger) {
        return document.querySelector('#dashboard_link_url');
    }
});

t1.on('success', function(event) {
    event.clearSelection();
    event.trigger.textContent = 'Copied';
    window.setTimeout(function() {
        event.trigger.textContent = '';
    }, 2000);

});

$("#cb_act_dashboard_link").change(function() {
    enableDisablePublicAccess();
});

function enableDisablePublicAccess(new_tk_id=false){
    if(g_campaign_id == "" || g_tracker_id == ""){
        toastr.error('', 'Error: No campaign selected');
        $('#cb_act_dashboard_link').prop('checked', false);
        return;
    }
    $('#cb_act_dashboard_link').closest('.modal-content').find('.modal-footer').append(displayLoader("Updating...","small"))
    $.post({
        url: "manager/session_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "manage_dashboard_access",  
            ctrl_val:$('#cb_act_dashboard_link').prop('checked'),
            tk_id : new_tk_id==true?getRandomId():g_tk_id,
            campaign_id: g_campaign_id,         
            tracker_id: g_tracker_id,
         })
    }).done(function (response) {
        if(response.result == "success"){
            g_tk_id = response.tk_id;
            if($("#cb_act_dashboard_link").prop('checked'))
                toastr.success('', 'Public access link activated!');   
            else
                toastr.warning('', 'Public access link deactivated!'); 
        }
        else
            toastr.error('', 'Error changing public access');

        $('#dashboard_link_url').html(location.href.split('?')[0] + "?mcamp=" + g_campaign_id + "&tracker=" + g_tracker_id + "&tk=" + g_tk_id);
        $("#modal_dashboard_link").find('.loader').remove();
    });    
}

function getAccessInfo(){
    $.post({
        url: "manager/session_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_access_info",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,         
            tracker_id: g_tracker_id,
         })
    }).done(function (response) {
        if(response.pub_access == true){
            $('#cb_act_dashboard_link').prop('disbled', false);
            $('#cb_act_dashboard_link').prop('checked', true);
            g_tk_id=response.tk_id;
        }
        else
            $('#cb_act_dashboard_link').prop('checked', false);
        
        $('#dashboard_link_url').html(location.href.split('?')[0] + "?mcamp=" + g_campaign_id + "&tracker=" + g_tracker_id + "&tk=" + g_tk_id);
    });
}

function hideMeFromPublic(){
    $(".left-sidebar").hide();
    $(".topbar").hide();
    $(".page-wrapper").css('margin-left',0);
    $(".item_private").hide();
}

function exportReportAction(e) {
    if(dt_web_mail_campaign_result.rows().count() > 0){
        var file_name = $('#Modal_export_file_name').val().trim();
        var file_format = $('#modal_export_report_selector').val();
        getAllReportColListSelected();

        if(file_format == 'csv')
            content_type='text/csv';
        else
        if(file_format == 'pdf')
            content_type='application/pdf';
        else
        if(file_format == 'html')
            content_type='text/html';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'manager/web_mail_campaign_manager', true);
        xhr.responseType = 'arraybuffer';
        
        enableDisableMe(e);
        xhr.send(JSON.stringify({ 
            action_type: "download_report",
            campaign_id: g_campaign_id,
            tracker_id: g_tracker_id,
            selected_col: allReportColListSelected,
            dic_all_col: dic_all_col,
            file_name: file_name,
            file_format: file_format,
            tb_data_single: g_tb_data_single
        }));

        xhr.onload = function() {
            if (this.status == 200) {
                var link=document.createElement('a');
                link.href = window.URL.createObjectURL(new Blob([this.response],{ type: content_type}));
                link.download=file_name + '.' + file_format;
                link.click();
                $('#ModalExport').modal('toggle');
            }
            enableDisableMe(e);
        };
    }
    else
        toastr.error('', 'Table is empty!');
}