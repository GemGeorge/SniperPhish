var chart_web_pie;
var chart_email_hit;
var f_all_empty = false;
var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

$("#graph_overview").html(displayLoader("Loading..."));
$("#graph_timeline_all").html(displayLoader("Loading..."));
getGraphsData();

function getGraphsData() {
    $.post({
        url: "manager/home_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_home_graphs_data"
        })
    }).done(function (data) {
        var count_mailcamp = data.campaign_info.mailcamp?data.campaign_info.mailcamp.length:0;
        var count_mailcamp_active=0, count_webtracker_active=0, count_quicktracker_active=0;
        var html_cont = `<div class="text-center align-items-center m-t-40">
                             <span class="col-md-5 badge badge-pill badge-warning"><h4>No data</h4></span>
                          </div>`;

        if(count_mailcamp){
            count_mailcamp_active = data.campaign_info.mailcamp.filter(function(x) {
                return x.camp_status == 1 || x.camp_status == 2 || x.camp_status == 4;
            }).length;
        }

        count_webtracker = data.campaign_info.webtracker?data.campaign_info.webtracker.length:0;
        if(count_webtracker){
            count_webtracker_active = data.campaign_info.webtracker.filter(function(x) {
                return x.active == 1;
            }).length;
        }

        count_quicktracker = data.campaign_info.quicktracker?data.campaign_info.quicktracker.length:0;
            if(count_quicktracker){
            count_quicktracker_active = data.campaign_info.quicktracker.filter(function(x) {
                return x.active == 1;
            }).length;
        }

        if(count_mailcamp==0 && count_webtracker==0 && count_quicktracker==0)
            f_all_empty=true;

        $('#lb_mailcamp').text('Total: ' + count_mailcamp + ', Active: ' + count_mailcamp_active);
        $('#lb_webtracker').text('Total: ' + count_webtracker + ', Active: ' + count_webtracker_active);
        $('#lb_quicktracker').text('Total: ' + count_quicktracker + ', Active: ' + count_quicktracker_active);


        $("#graph_timeline_all").html(html_cont);
        if(data.campaign_info.webtracker.length == 0 && data.campaign_info.mailcamp.length == 0  && data.campaign_info.quicktracker.length == 0)
            $("#graph_overview").html(html_cont);
        else{
            $("#graph_overview").html('');
            renderOverviewGraph(data.campaign_info, data.timestamp_conv);  
            $('#graph_overview').css("height","400px");

            if(data.campaign_info.webtracker.some(o => o.start_time!='-') || data.campaign_info.mailcamp.some(o => o.scheduled_time!='-') || data.campaign_info.quicktracker.some(o => o.start_time!='-')){
                $("#graph_timeline_all").html('');
                renderTimelineAllGraph(data.campaign_info,data.timestamp_conv,data.timezone);
                $('#graph_timeline_all').css("height","400px");
            }   
        }
    }); 
}

function getDateMMDDYYYY(unix_timestamp){
    if(unix_timestamp == '-')
        return '-';
    else{
        var ts_milli = new Date(unix_timestamp * 1000);
        var year = ts_milli.getFullYear();
        var month = months[ts_milli.getMonth()];
        var date = ts_milli.getDate();
        return date + '/' + month + '/' + year;
    }
}

function getDTStd(date_string){
    var date_split = date_string.split('/');
    return (date_split[0] + '-' + months.indexOf(date_split[1]) + '-' + date_split[2]);
}

function renderOverviewGraph(cmp_info, timestamp_conv) {
    date_arr = {
        'all': [],
        'webtracker': [],
        'mailcamp': [],
        'quicktracker': []
    };

    $.each(cmp_info['webtracker'], function(key, value) { 
        date = getDateMMDDYYYY(timestamp_conv[value.date]);
        date_arr.webtracker.push(date);

        if (date_arr.all.indexOf(date) == -1)
            date_arr.all.push(date);
    });

    $.each(cmp_info['mailcamp'], function(key, value) {
        date = getDateMMDDYYYY(timestamp_conv[value.date]);
        date_arr.mailcamp.push(date);
        if (date_arr.all.indexOf(date) == -1)
            date_arr.all.push(date);
    });

    $.each(cmp_info['quicktracker'], function(key, value) {
        date = getDateMMDDYYYY(timestamp_conv[value.date]);
        date_arr.quicktracker.push(date);
        if (date_arr.all.indexOf(date) == -1)
            date_arr.all.push(date);
    });

    date_arr.all.sort();
    graph_data_all_count = {
        'webtracker': [date_arr.webtracker.length],
        'mailcamp': [date_arr.mailcamp.length],
        'quicktracker': [date_arr.quicktracker.length]
    };

    $.each(date_arr.all, function(i, value) {
        array_val_count = date_arr.webtracker.filter(function(x) {
            return x === value;
        }).length;
        graph_data_all_count.webtracker[i] = array_val_count;

        array_val_count = date_arr.mailcamp.filter(function(x) {
            return x === value;
        }).length;
        graph_data_all_count.mailcamp[i] = array_val_count;

        array_val_count = date_arr.quicktracker.filter(function(x) {
            return x === value;
        }).length;
        graph_data_all_count.quicktracker[i] = array_val_count;
    });

    var options = {
        series: [{
            name: 'Mail Campaign',
            data: graph_data_all_count.mailcamp
        }, {
            name: 'Web Tracker',
            data: graph_data_all_count.webtracker
        }, {
            name: 'Quick Tracker',
            data: graph_data_all_count.quicktracker
        }],
        chart: {
            type: 'bar',
            height: 350,
            stacked: true,
            toolbar: {
                show: true
            },
            zoom: {
                enabled: true
            }
        },
        yaxis: {
            show: true,
            forceNiceScale: true,
            labels: {
                formatter: (value) => {
                    return Math.round(value * 100) / 100
                },
            },
            title: {
                text: 'Campaign count',
                rotate: 90,
                offsetX: 0,
                offsetY: 0,
                style: {
                    fontSize: '12px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fontWeight: 600,
                    cssClass: 'apexcharts-yaxis-title',
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
                return `<div class="chart-tooltip"><strong>` + w.config.series[seriesIndex].name + `</strong><br/>Date: ` + getDTStd(w.config.xaxis.categories[dataPointIndex]) + ` <br/>Count: ` + w.config.series[seriesIndex].data[dataPointIndex] + `</div>`;
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    position: 'bottom',
                    offsetX: -10,
                    offsetY: 0
                }
            }
        }],
        plotOptions: {
            bar: {
                horizontal: false,
            },
        },
        xaxis: {
            type: 'datetime',
            categories: date_arr.all, //MM/DD/YYYY
            labels: {
                formatter: function(value, timestamp) {
                    return Unix2StdDate(timestamp)
                },
            },
            tickAmount: 10
        },
        legend: {
            position: 'bottom',
            offsetY: 5,
        },
        fill: {
            opacity: 1
        },
    };

    graph_overview = new ApexCharts(document.querySelector("#graph_overview"), options);
    graph_overview.render();
}

function renderTimelineAllGraph(cmp_info,timestamp_conv, timezone) { 
    var time_arr = {
        'webtracker': [],
        'mailcamp': [],
        'quicktracker': []
    };
    var current_time = moment().tz(timezone).valueOf();

    level = 0;
    $.each(cmp_info['webtracker'], function(key, value) {
        if (value.start_time != '-') {
            start_time = timestamp_conv[value.start_time]*1000;

            if (value.stop_time == '-')
                stop_time=current_time;
            else
                stop_time=timestamp_conv[value.stop_time]*1000;
                    
            time_arr.webtracker.push({
                x: level++ + '',
                y: [
                    start_time,
                    stop_time
                ],
                z: [value.tracker_id, value.tracker_name, value.stop_time]  //stores actual value.stop_time
            });
        }
    });

    level = 0;
    $.each(cmp_info['mailcamp'], function(key, value) {
        if ((value.camp_status == 2 || value.camp_status == 3 || value.camp_status == 4)) {
            start_time = timestamp_conv[value.scheduled_time]*1000;

            if (value.stop_time == '-')
                stop_time=current_time;
            else
                stop_time=timestamp_conv[value.stop_time]*1000;

            time_arr.mailcamp.push({
                x: level++ + '',
                y: [
                    start_time ,
                    stop_time 
                ],
                z: [value.campaign_id, value.campaign_name, value.stop_time]  //stores actual value.stop_time
            });
        }
    });

    level = 0;
    $.each(cmp_info['quicktracker'], function(key, value) {
        if (value.start_time != '' && value.start_time != undefined) {
            start_time = timestamp_conv[value.start_time]*1000;

            if (value.stop_time == '-')
                stop_time=current_time;
            else
                stop_time=timestamp_conv[value.stop_time]*1000;            

            time_arr.quicktracker.push({
                x: level++ + '',
                y: [
                    start_time ,
                    stop_time 
                ],
                z: [value.tracker_id, value.tracker_name, value.stop_time]  //stores actual value.stop_time
            });
        }
    });

    var options = {
        series: [{
                name: 'Mail Campaign',
                data: time_arr.mailcamp
            },
            {
                name: 'Web Tracker',
                data: time_arr.webtracker
            },
            {
                name: 'Quick Tracker',
                data: time_arr.quicktracker
            }
        ],
        chart: {
            height: 450,
            type: 'rangeBar',
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '80%'
            }
        },
        xaxis: {
            type: 'datetime',
            labels: {
                formatter: function(value, timestamp) {
                    return Unix2StdDate(value)
                },
            },
            tickAmount: 10,
        },
        stroke: {
            width: 1
        },

        legend: {
            position: 'bottom',
            horizontalAlign: 'center'
        },
        yaxis: {
            show: true,
            labels: {
                formatter: (value) => {
                    return Math.round(Number(value))
                },
            },
            title: {
                text: 'Campaigns',
                rotate: 90,
                offsetX: 0,
                offsetY: 0,
                style: {
                    fontSize: '12px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fontWeight: 600,
                    cssClass: 'apexcharts-yaxis-title',
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
                var st=w.config.series[seriesIndex].data[dataPointIndex].y[0]/1000;  //unix timestamp
                var et=w.config.series[seriesIndex].data[dataPointIndex].y[1]/1000;  //unix timestamp

                st = Unix2StdDateTime(st,timezone);
                if(w.config.series[seriesIndex].data[dataPointIndex].z[2] == '-')   //if not ended
                    et = 'Ꝏ';
                else
                    et = Unix2StdDateTime(et,timezone);

                return `<div class="chart-tooltip"><strong>` + w.config.series[seriesIndex].name + `</strong><br/>Name: ` + w.config.series[seriesIndex].data[dataPointIndex].z[1] + ` (ID: ` + w.config.series[seriesIndex].data[dataPointIndex].z[0] + `)<br/>Run: ` + st + ' to ' + et + `</div>`;
            },

        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
                var a = moment(val[0]);
                var b= moment(val[1]);
                var diff_hrs = b.diff(a, 'hours', true);
                return diff_hrs.toFixed(2) + (diff_hrs > 1 ? ' hrs' : ' hr');
            },
            style: {
                colors: ['#f3f4f5', '#fff']
            }
        },
    };

    graph_timeline_all = new ApexCharts(document.querySelector("#graph_timeline_all"), options);
    graph_timeline_all.render();
}