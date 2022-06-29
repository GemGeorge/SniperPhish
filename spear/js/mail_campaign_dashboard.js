var dt_mail_campaign_list, dt_mail_campaign_result;
var chart_live_mailcamp, radialchart_overview_mailcamp, piechart_mail_total_sent, piechart_mail_total_mail_open, piechart_mail_total_replied;
var g_tb_data_single = true;
var reply_emails = {};
var allReportColList=[], allReportColListSelected=[];
var dic_all_col={rid:'RID', user_name:'Name', user_email:'Email', sending_status:'Status', send_time:'Sent Time', send_error:'Send Error',mail_open:'Mail Open',mail_open_count:'Mail(open count)',mail_first_open:'Mail(first open)',mail_last_open:'Mail(last open)',mail_open_times:'Mail(all open times)',public_ip:'Public IP',user_agent:'User Agent',mail_client:'Mail Client',platform:'Platform',device_type:'Device Type',all_headers:'HTTP Headers',mail_reply:'Mail Reply',mail_reply_count:'Mail (reply count)',mail_reply_content:'Mail (reply content)', country:'Country', city:'City', zip:'Zip', isp:'ISP', timezone:'Timezone', coordinates:'Coordinates'};

$("#tb_camp_result_colums_list_mcamp").select2();
$("#modal_export_report_selector").select2({
    minimumResultsForSearch: -1,
});   

$(function() {
    Prism.highlightAll();
    loadTableCampaignList();
});

var camp_status_def = {
    0: `<span class="badge badge-pill badge-dark" data-toggle="tooltip" title="Not scheduled"><i class="mdi mdi-alert"></i> Inactive</span>`,
    1: `<span class="badge badge-pill badge-warning" data-toggle="tooltip" title="Scheduled"><i class="mdi mdi-timer"></i> Scheduled</span>`,
    2: `<span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Mail sending status"><i class="mdi mdi-email"></i> In-progress</span> <span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Phishing campaign status"><i class="mdi mdi-fish"></i> In-progress</span>`,
    3: `<span class="badge badge-pill badge-success" data-toggle="tooltip" title="Phishing campaign status"><i class="mdi mdi-fish"></i> Completed</span>`,
    4: `<span class="badge badge-pill badge-success" data-toggle="tooltip" title="Mail sending status"><i class="mdi mdi-email"></i> Completed</span> <span class="badge badge-pill badge-primary" data-toggle="tooltip" title="Phishing campaign status"><i class="mdi mdi-fish"></i> In-progress</span>`
};

var camp_table_status_def = {
    1: `<td><span class="badge badge-pill badge-primary" data-toggle="tooltip" title="In-progress">In-progress</td>`,
    2: `<td><span class="badge badge-pill badge-success" data-toggle="tooltip" title="Sent">Sent</td>`,
    3: `<td><span class="badge badge-pill badge-danger" data-toggle="tooltip" title="Error">Error</td>`,
    4: `<td><i class="fas fa-clock fa-lg" data-toggle="tooltip" title="Waiting..."></i><span hidden>Waiting...</span></td>`
};

var ele = $("#tb_camp_result_colums_list_mcamp").parent().find("ul.select2-selection__rendered");
ele.sortable({
    containment: 'parent',
    update: function() {
        getAllReportColListSelected();
    }
});

function getAllReportColListSelected(){
    allReportColListSelected=[];

    $.each($("#tb_camp_result_colums_list_mcamp").find("option"), function () {
        allReportColList[$(this).text()] = $(this).val();
    });

    $.each($("#tb_camp_result_colums_list_mcamp").parent().find("ul.select2-selection__rendered").children("li[title]"), function () {
        allReportColListSelected.push(allReportColList[this.title]);
    });
}

function refreshDashboard() {
    if (g_campaign_id != '')
        campaignSelected(g_campaign_id);
    loadTableCampaignList();
}

function loadTableCampaignList() {
    try {
        dt_mail_campaign_list.destroy();
    } catch (err) {}
    $("#table_mail_campaign_list tbody > tr").remove();

    $.post({
        url: "manager/mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: 'get_campaign_list'
        })
    }).done(function (data) {
        if(!data.error){  // no data
            $.each(data, function(key, value) {
                if(value.camp_status == 2 || value.camp_status == 3 || value.camp_status == 4){ // removes inactive or scheduled
                    action_items_campaign_table = `<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Select" data-dismiss="modal" onClick="campaignSelected('` + value.campaign_id + `'); window.history.replaceState(null,null, location.pathname + '?mcamp=` + value.campaign_id + `');">Select</button>`;
                    $("#table_mail_campaign_list tbody").append("<tr><td></td><td>" + value.campaign_name + "</td><td data-order=\"" + getTimestamp(value.scheduled_time) + "\">" + value.scheduled_time + "</td><td>" + camp_status_def[value.camp_status] + "</td><td>" + action_items_campaign_table + "</td></tr>");
                }
            });
        }
        
        dt_mail_campaign_list = $('#table_mail_campaign_list').DataTable({
            "aaSorting": [2, 'desc'],
            "pageLength": 5,
            "lengthMenu": [5, 10, 20, 50, 100],
            'columnDefs': [{
                "targets": [3, 4],
                "className": "text-center"
            }, ],
            "preDrawCallback": function(settings) {
                $('#table_mail_campaign_list tbody').hide();
            },

            "drawCallback": function() {
                $('#table_mail_campaign_list tbody').fadeIn(500);
                $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            },

            "initComplete": function() {
                $("label>select").select2({minimumResultsForSearch: -1, });
            }
        }); //initialize table

        dt_mail_campaign_list.on('order.dt_mail_campaign_list search.dt_mail_campaign_list', function() {
            dt_mail_campaign_list.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();        
    }); 
}

function viewReplyMails(mail_id) {
    $('#modal_reply_mails').modal('toggle');
    $("#modal_reply_mails_body ul").html("");
    $("#modal_reply_mails_body div").html("");

    $.each(reply_emails.msg_info[mail_id].msg_time, function(i, item) {
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
        $('#summernote_reply_mail' + i).summernote('code', (reply_emails.msg_info[mail_id]['msg_body'])[i].replace(/\r\n/g, "<br/>"));
        $('#summernote_reply_mail' + i).summernote('disable');
    });
}

function startLoaders() {
    $.each(['chart_live_mailcamp','radialchart_overview_mailcamp','piechart_mail_total_sent','piechart_mail_total_mail_open','piechart_mail_total_replied','table_mail_campaign_result'], function(i, id){
        $('#'+id).attr('hidden', true);
        $('#'+id).parent().find(".loader").length==0?$('#'+id).parent().append(displayLoader("Loading...")):null;
    });
}

function campaignSelected(campaign_id) {
    g_campaign_id = campaign_id;
    //-------------
    $.each([chart_live_mailcamp,radialchart_overview_mailcamp,piechart_mail_total_sent,piechart_mail_total_mail_open,piechart_mail_total_replied,dt_mail_campaign_result], function(i, graph){
        try {
            graph.destroy();
        } catch (err) {}
    });

    startLoaders();
    getAccessInfo();
    $('#table_mail_campaign_result thead').empty();
    $("#table_mail_campaign_result tbody > tr").remove();
    loadTableCampaignResult();
    //------------------------
    
    $.post({
        url: "manager/mail_campaign_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_campaign_from_campaign_list_id",
            tk_id : g_tk_id,
            campaign_id: g_campaign_id,
        })
    }).done(function (data) {
        if(!data.error){
            $('#disp_camp_name').text(data.campaign_name);
            $('#disp_camp_start').text(data.scheduled_time);
            $('#disp_camp_status').html(camp_status_def[data.camp_status]);
            $('#Modal_export_file_name').val(data.campaign_name);
            //-----------------------------     
            
            var sent_failed_count=data.live_mcamp_data.sent_failed_count;
            var sent_success_count=data.live_mcamp_data.sent_success_count;
            var sent_mail_count = sent_failed_count + sent_success_count;
            var mail_open_count = data.live_mcamp_data.mail_open_count;

            updateProgressbar(data.camp_status, data.campaign_data.mail_sender.id, data.campaign_data.user_group.id, data.campaign_data.mail_template.id, sent_mail_count, sent_success_count, sent_failed_count, mail_open_count);
            updateLiveMailCampData(data.live_mcamp_data.scatter_data, data.live_mcamp_data.timestamp_conv, data.timezone);
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
        $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
    }); 
}


function updateLiveMailCampData(scatter_data, timestamp_conv, timezone) {
    $("#chart_live_mailcamp").attr("hidden", false);
    $("#chart_live_mailcamp").parent().children().remove('.loadercust');

    var graph_data = {'in_progress': {"hit_time":[],"user_email":[],"user_name":[]},
                       'sent_success': {"hit_time":[],"user_email":[],"user_name":[]},
                       'send_error': {"hit_time":[],"user_email":[],"user_name":[]},
                       'mail_open': {"hit_time":[],"user_email":[],"user_name":[]}};

    $.each(scatter_data, function(i, item) {
        switch (item.sending_status) {
            case 1:     //In progress
                graph_data.in_progress.hit_time.push([Number(item.send_time), 1]);
                graph_data.in_progress.user_email.push(item.user_email);
                graph_data.in_progress.user_name.push(item.user_name);
                break;
            case 2:     //Sent success
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
                if ($.inArray(item.user_email,  graph_data.mail_open.user_email) <= -1 ) {
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

    var options = {
        chart: {
            height: 130,
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
        ],
        dataLabels: {
            enabled: false,
        },
        grid: {
            xaxis: {
                showLines: true,
            },
            yaxis: {
                showLines: true,
            },
        },
        xaxis: {
            type: 'datetime',
            labels: {
                formatter: function(val) {
                    return Unix2StdDateTime(val/1000,timezone);
                },
                tickPlacement: 'on'
            },
            tooltip: {
                enabled: false,
            },
        },
        yaxis: {
            max: 5,
            show: false,
        },
        tooltip: {
            custom: function({
                series,
                seriesIndex,
                dataPointIndex,
                w
            }) {
                var localdate = timestamp_conv[w.config.series[seriesIndex].data[dataPointIndex][0]];
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
                }
            }
        },
        grid: {
            show: false,
            padding: {
                left: 50,
                right: 15
            }
        },
        legend: {
            show: true,
            height: 30,
        },
        colors: ['#7460ee', '#4CAF50', '#e6b800', '#FA4443']
    }

    chart_live_mailcamp = new ApexCharts(
        document.querySelector("#chart_live_mailcamp"),
        options
    );
    chart_live_mailcamp.render();
}

function updatePieOverViewEmail(sent_mail_percent, open_mail_percent) {
    $("#radialchart_overview_mailcamp").attr("hidden", false);
    $("#radialchart_overview_mailcamp").parent().children().remove('.loadercust');

    var options = {
        series: [sent_mail_percent, open_mail_percent, 0], //value 0 updated in another function
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
        dt_mail_campaign_result.destroy();
    } catch (err) {}

    $("#table_mail_campaign_result").attr("hidden", false);
    $("#table_mail_campaign_result").parent().children().remove('.loadercust');

    $('#table_mail_campaign_result thead').empty();
    $("#table_mail_campaign_result tbody > tr").remove();

    getAllReportColListSelected();
    $('input[name="radio_table_data"]:checked').val() == "radio_table_data_single"?g_tb_data_single=true:g_tb_data_single=false;

    var arr_tb_heading=[];  
    arr_tb_heading.push({ data: 'sn', title: "SN" });

    $.each(allReportColListSelected, function(index, item) {
        if (item.startsWith("Field"))
            arr_tb_heading.push({ data: item, title : 'Field-' + item});
        else
            arr_tb_heading.push({ data: item, title : dic_all_col[item]});
    });

    dt_mail_campaign_result = $('#table_mail_campaign_result').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            url:'manager/mail_campaign_manager',
            type: "POST",
            contentType: "application/json; charset=utf-8",
            data: function (d) {   //request parameters here
                    d.action_type = 'multi_get_mcampinfo_from_mcamp_list_id_get_live_mcamp_data';
                    d.tk_id = g_tk_id;
                    d.campaign_id = g_campaign_id;
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
                }
                return resp.data
            }
        },
        'columns': arr_tb_heading,
        'pageLength': 20,
        'lengthMenu': [[20, 50, 100, 500, 1000, -1], [20, 50, 100, 500, 1000, "All"]],
        'aoColumnDefs': [{'bSortable': false, 'aTargets': [0]}],
        drawCallback:function(){
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            $("label>select").select2({minimumResultsForSearch: -1, });
        }
    });
}

function exportReportAction(e) {
    if(dt_mail_campaign_result.rows().count() > 0){
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
        xhr.open('POST', 'manager/mail_campaign_manager', true);
        xhr.responseType = 'arraybuffer';

        enableDisableMe(e);        
        xhr.send(JSON.stringify({ 
            action_type: "download_report",
            campaign_id: g_campaign_id,
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
    if(g_campaign_id == ""){
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

        $('#dashboard_link_url').html(location.href.split('?')[0] + "?mcamp=" + g_campaign_id + "&tk=" + g_tk_id);
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
         })
    }).done(function (response) {
        if(response.pub_access == true){
            $('#cb_act_dashboard_link').prop('disbled', false);
            $('#cb_act_dashboard_link').prop('checked', true);
            g_tk_id=response.tk_id;
        }
        else
            $('#cb_act_dashboard_link').prop('checked', false);
        
        $('#dashboard_link_url').html(location.href.split('?')[0] + "?mcamp=" + g_campaign_id + "&tk=" + g_tk_id);
    });
}

function hideMeFromPublic(){
    $(".left-sidebar").hide();
    $(".topbar").hide();
    $(".page-wrapper").css('margin-left',0);
    $(".item_private").hide();
}

