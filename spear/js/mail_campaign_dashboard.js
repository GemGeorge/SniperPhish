var dt_mail_campaign_list;
var dt_mail_campaign_result;

var chart_live_mailcamp;
var radialchart_overview_mailcamp, piechart_mail_total_sent, piechart_mail_total_mail_open, piechart_mail_total_replied;

var g_campaign_id = '', g_tb_data_single = true;
var data_mail_live;
var reply_emails = [];
$("#tb_mailcamp_result_colums_list").select2();
$("#modal_export_report_selector").select2({
    minimumResultsForSearch: -1,
});   

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

$("#tb_mailcamp_result_colums_list").on("select2:select", function(evt) {
    var element = evt.params.data.element;
    var $element = $(element);
    $element.detach();
    $(this).append($element);
    $(this).trigger("change");
});

$('#tb_mailcamp_result_colums_list').select2({
    placeholder: 'Select a month'
}).on("select2:select", function(evt) {
    var id = evt.params.data.id;
    var element = $(this).children("option[value=" + id + "]");
    moveElementToEndOfParent(element);
    $(this).trigger("change");
});

var ele = $("#tb_mailcamp_result_colums_list").parent().find("ul.select2-selection__rendered");
ele.sortable({
    containment: 'parent',
    update: function() {
        orderSortedValues();
    }
});

orderSortedValues = function() {
    var value = ''
    $("#tb_mailcamp_result_colums_list").parent().find("ul.select2-selection__rendered").children("li[title]").each(function(i, obj) {
        var element = $("#tb_mailcamp_result_colums_list").children('option').filter(function() {
            return $(this).html() == obj.title
        });
        moveElementToEndOfParent(element)
    });
};

moveElementToEndOfParent = function(element) {
    var parent = element.parent();

    element.detach();

    parent.append(element);
};

$(function() {
    loadTableCampaignList();
});

function refreshDashboard() {
    $('input[name="radio_table_data"]:checked').val() == "radio_table_data_single"?g_tb_data_single=true:g_tb_data_single=false;
    if (g_campaign_id != '')
        campaignSelected(g_campaign_id);

    loadTableCampaignList();
}

function loadTableCampaignList() {
    try {
        dt_mail_campaign_list.destroy();
    } catch (err) {}
    $("#table_mail_campaign_list tbody > tr").remove();

    $.post("mail_campaign_manager", {
            action_type: "get_campaign_list"
        },
        function(data, status) {
            if (data != "") {
                $.each(data, function(key, value) {
                    if(value['camp_status'] == 2 || value['camp_status'] == 3 || value['camp_status'] == 4){ // removes inactive or scheduled
                        action_items_campaign_table = `<button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Select" data-dismiss="modal" onClick="campaignSelected('` + value['campaign_id'] + `'); window.history.replaceState(null,null, location.pathname + '?mcamp=` + value['campaign_id'] + `');">Select</button>`;
                        $("#table_mail_campaign_list tbody").append("<tr><td></td><td>" + value['campaign_name'] + "</td><td data-order=\"" + UTC2LocalUNIX(value['scheduled_time']) + "\">" + UTC2Local(value['scheduled_time']) + "</td><td>" + camp_status_def[value['camp_status']] + "</td><td>" + action_items_campaign_table + "</td></tr>");
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
    $("#table_mail_campaign_result").attr("hidden", true);
    $("#table_mail_campaign_result").parent().append(displayLoader("Loading..."));
}

function campaignSelected(campaign_id) {
    window.g_campaign_id = campaign_id;
    data_mail_live = '';
    //-------------
    $.each([chart_live_mailcamp,radialchart_overview_mailcamp,piechart_mail_total_sent,piechart_mail_total_mail_open,piechart_mail_total_replied,dt_mail_campaign_result], function(i, graph){
        try {
            graph.destroy();
        } catch (err) {}
    });

    startLoaders();
    $('#table_mail_campaign_result thead').empty();
    $("#table_mail_campaign_result tbody > tr").remove();
    //------------------------

    $.post("mail_campaign_manager", {
            action_type: "multi_get_campaign_from_campaign_list_id__get_live_campaign_data",
            campaign_id: campaign_id,
        },
        function(data, status) {
            if(data['live_campaign_data']['resp']){
                toastr.warning('', data['live_campaign_data']['resp']);
                return;
            }
            data_mail_live = data['live_campaign_data'];
            $('#disp_camp_name').text(data['campaign_data']['campaign_name']);
            $('#disp_camp_start').text(UTC2Local(data['campaign_data']['scheduled_time'].toString()));
            $('#disp_camp_status').html(camp_status_def[data['campaign_data']['camp_status']]);
            //-----------------------------     
            
            var sent_failed_count = data_mail_live.filter(x => x['sending_status'] === 3).length;
            var sent_success_count =data_mail_live.filter(x => x['sending_status'] === 2).length;
            var sent_mail_count = sent_failed_count + sent_success_count;
            updateProgressbar(data['campaign_data']['camp_status'],data['campaign_data']['mail_sender'].split(',')[0],data['campaign_data']['user_group'].split(',')[0],data['campaign_data']['mail_template'].split(',')[0], sent_mail_count, sent_success_count, sent_failed_count);
        }).fail(function() {
        toastr.error('', 'Error getting campaign data!');
    });
}

function updateProgressbar(mailcamp_status, sender_list_id, user_group_id, mail_template_id, sent_mail_count, sent_success_count, sent_failed_count) {
    $.post("userlist_campaignlist_mailtemplate_manager.php", {
            action_type: "get_user_group_from_group_Id",
            user_group_id: user_group_id,
        },
        function(data, status) {
            if (data) {
                $('#user_group_name').val(data['user_group_name']);

                var total_user_email_count = data['user_email'].split(',').length;
                var sent_mail_percent = +(sent_mail_count / total_user_email_count * 100).toFixed(2);
                var sent_mail_success_percent = +(sent_success_count / sent_mail_count * 100).toFixed(2);

                $("#progressbar_status").children().width(sent_mail_percent + "%");
                $("#progressbar_status").children().text(sent_mail_count + "/" + total_user_email_count + " (" + sent_mail_percent + "%)");
                if (sent_mail_percent == 100)
                    $("#progressbar_status").children().addClass("bg-success");
                else
                    $("#progressbar_status").children().removeClass("bg-success");

                updateLiveMailCampData(data);
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
                    updatePieTotalMailReplied(sender_list_id, user_group_id , mail_template_id);

            }
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        });
}


function updateLiveMailCampData(user_group_data) {
    $("#chart_live_mailcamp").attr("hidden", false);
    $("#chart_live_mailcamp").parent().children().remove('.loadercust');

    var col_name = user_group_data['user_name'].split(',');
    var col_email = user_group_data['user_email'].split(',');
    var graph_data = {'in_progress': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'sent_success': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'send_error': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]},
                       'mail_open': {"hit_time":[],"mailto_user_email":[],"mailto_user_name":[]}};

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
                    return UTC2Local(val);
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
            if (data) {
                window.reply_emails = data;
                $("#piechart_mail_total_replied").attr("hidden", false);
                $("#piechart_mail_total_replied").parent().children().remove('.loadercust');
                loadTableCampaignResult();

                var total_user_email_count = data['total_user_email_count'];
                var reply_count_unique = Object.keys(data['msg_info']).length;
                var reply_percent = reply_count_unique / total_user_email_count * 100;
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
                radialchart_overview_mailcamp.updateSeries(arr_chart_data)
            }
        });
}

function loadTableCampaignResult() {
    if (data_mail_live == "") {
        toastr.error('', 'Campaign inactive/not selected');
        return;
    }
    try {
        dt_mail_campaign_result.destroy();
    } catch (err) {}

    $("#table_mail_campaign_result").attr("hidden", false);
    $("#table_mail_campaign_result").parent().children().remove('.loadercust');

    $('#table_mail_campaign_result thead').empty();
    $("#table_mail_campaign_result tbody > tr").remove();

    var tb_headers = "<tr><th>No</th>";
    var tb_data = "";

    var tb_mailcamp_result_colums_list = $('#tb_mailcamp_result_colums_list').val();

    $.each(tb_mailcamp_result_colums_list, function(i, item) {
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
        }
    });
    tb_headers += "</tr>";
    $("#table_mail_campaign_result thead").append(tb_headers);

    $.each(data_mail_live, function(i, item) {
        tb_data += "<tr><td></td>";
        $.each(tb_mailcamp_result_colums_list, function(i, column) {
            //---Start setting default column values
            if (column == "mail_open" || column == "mail_reply")
                item[column] = "<i class='fas fa-times fa-lg text-danger' data-toggle='tooltip' title='No'></i><span hidden>No</span>";
            if(column == "mail_open_count")
                item[column] = '0'; //requires '0' and not 0, unknown issue
            //----End setting default column values
            if($.inArray(column,['mailto_user_name','mailto_user_email','sending_status','send_time','send_error','public_ip','user_agent','mail_client','platform','all_headers'])> -1 && item[column] == undefined)
                tb_data += "<td>-</td>";
            else
            if ((column == "mail_open" || column == "mail_open_count" || column == "mail_first_open" || column == "mail_last_open" || column == "mail_open_times") && item['mail_open_times'] != undefined && item['mail_open_times'] != '') {
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
            } else
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
            if (item[column] != undefined && item[column] != '')
                tb_data += "<td>" + item[column] + "</td>";
            else
                tb_data += "<td>-</td>";

        });
        tb_data += "</tr>";
    });
    $("#table_mail_campaign_result tbody").append(tb_data);

    dt_mail_campaign_result = $('#table_mail_campaign_result').DataTable({
        "bDestroy": true,
        "preDrawCallback": function(settings) {
            $('#table_mail_campaign_result tbody').hide();
        },

        "drawCallback": function() {
            $('#table_mail_campaign_result tbody').fadeIn(500);
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
    $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
}