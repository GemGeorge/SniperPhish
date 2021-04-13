var chart_web_pie;
var chart_email_hit;
var f_all_empty = false;

$("#graph_overview").html(displayLoader("Loading..."));
$("#graph_timeline_all").html(displayLoader("Loading..."));
getGraphsData();

function getGraphsData() {
    $.post({
        url: "home_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_home_graphs_data"
        })
    }).done(function (data) {
        var count_mailcamp = data.mailcamp?data.mailcamp.length:0;
        var count_mailcamp_active=0, count_webtracker_active=0, count_quicktracker_active=0;
        var html_cont = `<div class="text-center align-items-center m-t-40">
                             <span class="col-md-5 badge badge-pill badge-warning"><h4>No data</h4></span>
                          </div>`;

        if(count_mailcamp){
            count_mailcamp_active = data.mailcamp.filter(function(x) {
                return x.camp_status === 1 || x.camp_status === 2 || x.camp_status === 4;
            }).length;
        }

        count_webtracker = data.webtracker?data.webtracker.length:0;
        if(count_webtracker){
            count_webtracker_active = data.webtracker.filter(function(x) {
                return x.active === 1;
            }).length;
        }

        count_quicktracker = data.quicktracker?data.quicktracker.length:0;
            if(count_quicktracker){
            count_quicktracker_active = data.quicktracker.filter(function(x) {
                return x.active === 1;
            }).length;
        }

        if(count_mailcamp==0 && count_webtracker==0 && count_quicktracker==0)
            f_all_empty=true;

        $('#lb_mailcamp').text('Total: ' + count_mailcamp + ', Active: ' + count_mailcamp_active);
        $('#lb_webtracker').text('Total: ' + count_webtracker + ', Active: ' + count_webtracker_active);
        $('#lb_quicktracker').text('Total: ' + count_quicktracker + ', Active: ' + count_quicktracker_active);


        $("#graph_timeline_all").html(html_cont);
        if(data.webtracker.length == 0 && data.mailcamp.length == 0  && data.quicktracker.length == 0)
            $("#graph_overview").html(html_cont);
        else{
            $("#graph_overview").html('');
            renderOverviewGraph(data);  
            $('#graph_overview').css("height","400px");

            if(data.webtracker.some(o => o.start_time!=null) || data.mailcamp.some(o => o.scheduled_time!=null) || data.quicktracker.some(o => o.start_time!=null)){
                $("#graph_timeline_all").html('');
                renderTimelineAllGraph(data);
                $('#graph_timeline_all').css("height","400px");
            }   
        }
    }); 
}

function renderOverviewGraph(cmp_info) {
    date_arr = {
        'all': [],
        'webtracker': [],
        'mailcamp': [],
        'quicktracker': []
    };

    $.each(cmp_info['webtracker'], function(key, value) {
        date = moment.unix(UTC2LocalUNIX(value.date) / 1000).format("MM/DD/YYYY");
        date_arr.webtracker.push(date);

        if (date_arr.all.indexOf(date) == -1)
            date_arr.all.push(date);
    });

    $.each(cmp_info['mailcamp'], function(key, value) {
        date = moment.unix(UTC2LocalUNIX(value.date) / 1000).format("MM/DD/YYYY");
        date_arr.mailcamp.push(date);
        if (date_arr.all.indexOf(date) == -1)
            date_arr.all.push(date);
    });

    $.each(cmp_info['quicktracker'], function(key, value) {
        date = moment.unix(UTC2LocalUNIX(value.date) / 1000).format("MM/DD/YYYY");
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
                return `<div class="chart-tooltip"><strong>` + w.config.series[seriesIndex].name + `</strong><br/>Date: ` + moment(w.config.xaxis.categories[dataPointIndex],'MM/DD/YYYY').format(getDateTimeFormat('dateonly')) + ` <br/>Count: ` + w.config.series[seriesIndex].data[dataPointIndex] + `</div>`;
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
                    return LocalUNIX2LocalDate(timestamp)
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

function renderTimelineAllGraph(cmp_info) {
    var time_arr = {
        'webtracker': [],
        'mailcamp': [],
        'quicktracker': []
    };

    level = 0;

    $.each(cmp_info['webtracker'], function(key, value) {
        if (value.start_time != '' && value.start_time != undefined) {
            start_time = UTC2LocalUNIX(value.start_time);
            if (value.stop_time == '' || value.stop_time == undefined)
                stop_time = moment().tz(getDateTimeFormat('tzonly')).valueOf();
            else
                stop_time = UTC2LocalUNIX(value.stop_time);

            time_arr.webtracker.push({
                x: level++ + '',
                y: [
                    start_time,
                    stop_time
                ],
                z: [value.tracker_id, value.tracker_name]
            });
        }
    });

    level = 0;
    $.each(cmp_info['mailcamp'], function(key, value) {
        if ((value.camp_status == 2 || value.camp_status == 3 || value.camp_status == 4)) {
            start_time = UTC2LocalUNIX(value.scheduled_time);
            if (value.stop_time == '' || value.stop_time == undefined)
                stop_time = moment().tz(getDateTimeFormat('tzonly')).valueOf();
            else
                stop_time = UTC2LocalUNIX(value.stop_time);

            time_arr.mailcamp.push({
                x: level++ + '',
                y: [
                    start_time,
                    stop_time
                ],
                z: [value.campaign_id, value.campaign_name]
            });
        }
    });

    level = 0;
    $.each(cmp_info['quicktracker'], function(key, value) {
        if (value.start_time != '' && value.start_time != undefined) {
            start_time = UTC2LocalUNIX(value.start_time);
            if (value.stop_time == '' || value.stop_time == undefined)
                stop_time = moment().tz(getDateTimeFormat('tzonly')).valueOf();
            else
                stop_time = UTC2LocalUNIX(value.stop_time);

            time_arr.quicktracker.push({
                x: level++ + '',
                y: [
                    start_time,
                    stop_time
                ],
                z: [value.tracker_id, value.tracker_name]
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
                    return LocalUNIX2LocalDate(value)
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
                return `<div class="chart-tooltip"><strong>` + w.config.series[seriesIndex].name + `</strong><br/>Name: ` + w.config.series[seriesIndex].data[dataPointIndex].z[1] + ` (ID: ` + w.config.series[seriesIndex].data[dataPointIndex].z[0] + `)<br/>Run: ` + LocalUNIX2Local(w.config.series[seriesIndex].data[dataPointIndex].y[0]) + ' to ' + LocalUNIX2Local(w.config.series[seriesIndex].data[dataPointIndex].y[1]) + `</div>`;
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