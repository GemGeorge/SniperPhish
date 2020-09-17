$(document).ready(function() {
    $("#report_timezoneSelector").select2({
    minimumResultsForSearch: -1
    });
    $("#report_date_format").select2({
        minimumResultsForSearch: -1
    });
    $("#report_space_format").select2({
        minimumResultsForSearch: -1
    });
    $("#report_time_format").select2({
        minimumResultsForSearch: -1
    });
    $("#sniperphish_timezoneSelector").select2({
        minimumResultsForSearch: -1
    });
    $("#lb_sniperphish_time_format").show();
    $("#lb_report_time_format").show();
    $("#timezone_warning").show();
    
    $("#report_date_format").val('DD-MM-YYYY').trigger("change");
    $("#report_time_format").val('hh:mm:ss A').trigger("change");
    getSetingsValues();
});

function modifyAccount(e){
    var setting_field_uname = $("#setting_field_uname").val();
    var setting_field_mail = $("#setting_field_mail").val();
	var setting_field_old_pwd = $("#setting_field_old_pwd").val();
	var setting_field_new_pwd = $("#setting_field_new_pwd").val();
	var setting_field_confirm_pwd = $("#setting_field_confirm_pwd").val();
	
	if(setting_field_new_pwd != setting_field_confirm_pwd){
		toastr.error('', 'Confirm password does not match!');
		return;
	}

    enableDisableMe(e);
	$.post("settings_manager", {
            action_type: "modify_account",
            setting_field_uname: setting_field_uname,
            setting_field_mail: setting_field_mail,
            setting_field_old_pwd: setting_field_old_pwd,
            setting_field_new_pwd: setting_field_new_pwd,
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Information updated successfully!');
                $("#setting_field_old_pwd").val('');
                $("#setting_field_new_pwd").val('');
                $("#setting_field_confirm_pwd").val('');
            } else {
                toastr.error('', data);
            }
            enableDisableMe(e);
        });
}

//------------------------Timezone Section----------------------------
$(document).ready(function() {
    const _t = (s) => {
        if (i18n !== void 0 && i18n[s]) {
            return i18n[s];
        }

        return s;
    };

    const timezones = [
        "Etc/GMT+12",
        "Pacific/Midway",
        "Pacific/Honolulu",
        "America/Juneau",
        "America/Dawson",
        "America/Boise",
        "America/Chihuahua",
        "America/Phoenix",
        "America/Chicago",
        "America/Regina",
        "America/Mexico_City",
        "America/Belize",
        "America/Detroit",
        "America/Indiana/Indianapolis",
        "America/Bogota",
        "America/Glace_Bay",
        "America/Caracas",
        "America/Santiago",
        "America/St_Johns",
        "America/Sao_Paulo",
        "America/Argentina/Buenos_Aires",
        "America/Godthab",
        "Etc/GMT+2",
        "Atlantic/Azores",
        "Atlantic/Cape_Verde",
        "GMT",
        "Africa/Casablanca",
        "Atlantic/Canary",
        "Europe/Belgrade",
        "Europe/Sarajevo",
        "Europe/Brussels",
        "Europe/Amsterdam",
        "Africa/Algiers",
        "Europe/Bucharest",
        "Africa/Cairo",
        "Europe/Helsinki",
        "Europe/Athens",
        "Asia/Jerusalem",
        "Africa/Harare",
        "Europe/Moscow",
        "Asia/Kuwait",
        "Africa/Nairobi",
        "Asia/Baghdad",
        "Asia/Tehran",
        "Asia/Dubai",
        "Asia/Baku",
        "Asia/Kabul",
        "Asia/Yekaterinburg",
        "Asia/Karachi",
        "Asia/Kolkata",
        "Asia/Kathmandu",
        "Asia/Dhaka",
        "Asia/Colombo",
        "Asia/Almaty",
        "Asia/Rangoon",
        "Asia/Bangkok",
        "Asia/Krasnoyarsk",
        "Asia/Shanghai",
        "Asia/Kuala_Lumpur",
        "Asia/Taipei",
        "Australia/Perth",
        "Asia/Irkutsk",
        "Asia/Seoul",
        "Asia/Tokyo",
        "Asia/Yakutsk",
        "Australia/Darwin",
        "Australia/Adelaide",
        "Australia/Sydney",
        "Australia/Brisbane",
        "Australia/Hobart",
        "Asia/Vladivostok",
        "Pacific/Guam",
        "Asia/Magadan",
        "Pacific/Fiji",
        "Pacific/Auckland",
        "Pacific/Tongatapu"
    ];

    const i18n = {
        "Etc/GMT+12": "International Date Line West",
        "Pacific/Midway": "Midway Island, Samoa",
        "Pacific/Honolulu": "Hawaii",
        "America/Juneau": "Alaska",
        "America/Dawson": "Pacific Time (US and Canada); Tijuana",
        "America/Boise": "Mountain Time (US and Canada)",
        "America/Chihuahua": "Chihuahua, La Paz, Mazatlan",
        "America/Phoenix": "Arizona",
        "America/Chicago": "Central Time (US and Canada)",
        "America/Regina": "Saskatchewan",
        "America/Mexico_City": "Guadalajara, Mexico City, Monterrey",
        "America/Belize": "Central America",
        "America/Detroit": "Eastern Time (US and Canada)",
        "America/Indiana/Indianapolis": "Indiana (East)",
        "America/Bogota": "Bogota, Lima, Quito",
        "America/Glace_Bay": "Atlantic Time (Canada)",
        "America/Caracas": "Caracas, La Paz",
        "America/Santiago": "Santiago",
        "America/St_Johns": "Newfoundland and Labrador",
        "America/Sao_Paulo": "Brasilia",
        "America/Argentina/Buenos_Aires": "Buenos Aires, Georgetown",
        "America/Godthab": "Greenland",
        "Etc/GMT+2": "Mid-Atlantic",
        "Atlantic/Azores": "Azores",
        "Atlantic/Cape_Verde": "Cape Verde Islands",
        "GMT": "Dublin, Edinburgh, Lisbon, London",
        "Africa/Casablanca": "Casablanca, Monrovia",
        "Atlantic/Canary": "Canary Islands",
        "Europe/Belgrade": "Belgrade, Bratislava, Budapest, Ljubljana, Prague",
        "Europe/Sarajevo": "Sarajevo, Skopje, Warsaw, Zagreb",
        "Europe/Brussels": "Brussels, Copenhagen, Madrid, Paris",
        "Europe/Amsterdam": "Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
        "Africa/Algiers": "West Central Africa",
        "Europe/Bucharest": "Bucharest",
        "Africa/Cairo": "Cairo",
        "Europe/Helsinki": "Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius",
        "Europe/Athens": "Athens, Istanbul, Minsk",
        "Asia/Jerusalem": "Jerusalem",
        "Africa/Harare": "Harare, Pretoria",
        "Europe/Moscow": "Moscow, St. Petersburg, Volgograd",
        "Asia/Kuwait": "Kuwait, Riyadh",
        "Africa/Nairobi": "Nairobi",
        "Asia/Baghdad": "Baghdad",
        "Asia/Tehran": "Tehran",
        "Asia/Dubai": "Abu Dhabi, Muscat",
        "Asia/Baku": "Baku, Tbilisi, Yerevan",
        "Asia/Kabul": "Kabul",
        "Asia/Yekaterinburg": "Ekaterinburg",
        "Asia/Karachi": "Islamabad, Karachi, Tashkent",
        "Asia/Kolkata": "Chennai, Kolkata, Mumbai, New Delhi",
        "Asia/Kathmandu": "Kathmandu",
        "Asia/Dhaka": "Astana, Dhaka",
        "Asia/Colombo": "Sri Jayawardenepura",
        "Asia/Almaty": "Almaty, Novosibirsk",
        "Asia/Rangoon": "Yangon Rangoon",
        "Asia/Bangkok": "Bangkok, Hanoi, Jakarta",
        "Asia/Krasnoyarsk": "Krasnoyarsk",
        "Asia/Shanghai": "Beijing, Chongqing, Hong Kong SAR, Urumqi",
        "Asia/Kuala_Lumpur": "Kuala Lumpur, Singapore",
        "Asia/Taipei": "Taipei",
        "Australia/Perth": "Perth",
        "Asia/Irkutsk": "Irkutsk, Ulaanbaatar",
        "Asia/Seoul": "Seoul",
        "Asia/Tokyo": "Osaka, Sapporo, Tokyo",
        "Asia/Yakutsk": "Yakutsk",
        "Australia/Darwin": "Darwin",
        "Australia/Adelaide": "Adelaide",
        "Australia/Sydney": "Canberra, Melbourne, Sydney",
        "Australia/Brisbane": "Brisbane",
        "Australia/Hobart": "Hobart",
        "Asia/Vladivostok": "Vladivostok",
        "Pacific/Guam": "Guam, Port Moresby",
        "Asia/Magadan": "Magadan, Solomon Islands, New Caledonia",
        "Pacific/Fiji": "Fiji Islands, Kamchatka, Marshall Islands",
        "Pacific/Auckland": "Auckland, Wellington",
        "Pacific/Tongatapu": "Nuku'alofa"
    }
    const dateTimeUtc = moment("2017-06-05T19:41:03Z").utc();
    document.querySelector("#sniperphish_timezoneSelector").innerHTML = dateTimeUtc.format("ddd, DD MMM YYYY HH:mm:ss");
    document.querySelector("#report_timezoneSelector").innerHTML = dateTimeUtc.format("ddd, DD MMM YYYY HH:mm:ss");

    const selectorOptions = moment.tz.names()
        .filter(tz => {
            return timezones.includes(tz)
        })
        .reduce((memo, tz) => {
            memo.push({
                name: tz,
                offset: moment.tz(tz).utcOffset()
            });

            return memo;
        }, [])
        .sort((a, b) => {
            return a.offset - b.offset
        })
        .reduce((memo, tz) => {
            const timezone = tz.offset ? moment.tz(tz.name).format('Z') : '';

            return memo.concat(`<option value="${tz.name}">(GMT${timezone}) ${_t(tz.name)}</option>`);
        }, "");

    document.querySelector("#sniperphish_timezoneSelector").innerHTML = selectorOptions;
    document.querySelector("#report_timezoneSelector").innerHTML = selectorOptions;
    document.querySelector("#sniperphish_timezoneSelector").value = "Asia/Kuala_Lumpur"; 
    document.querySelector("#report_timezoneSelector").value = "Asia/Kuala_Lumpur";    
});

var date_formats={
    'MM DD YY': '04 04 20', 
    'MMMM DD YY': 'April 04 20', 
    'MMMM Do YY': 'April 4th 20', 
    'MMMM Do YYYY': 'April 4th 2020', 

    'DD MM YY': '04 04 20', 
    'Do MM YY': '4th 04 20', 
    'DD MMMM YY': '04 April 20', 
    'Do MMMM YY': '4th April 20', 
    'DD MM YYYY': '04 04 2020',

    'YY MM DD': '20 04 04', 
    'YYYY MM DD': '2020 04 04', 
    'YY MMMM DD': '20 April 04', 
    'YYYY MMMM DD': '2020 April 04', 

    'MM/DD/YY': '04/04/20', 
    'MMMM/DD/YY': 'April/04/20', 
    'MMMM/Do/YY': 'April/4th/20', 
    'MMMM/Do/YYYY': 'April/4th/2020', 

    'DD/MM/YY': '04/04/20', 
    'Do/MM/YY': '4th/04/20', 
    'DD/MMMM/YY': '04/April/20', 
    'Do/MMMM/YY': '4th/April/20', 
    'DD/MM/YYYY': '04/04/2020',

    'YY/MM/DD': '20/04/04', 
    'YYYY/MM/DD': '2020/04/04', 
    'YY/MMMM/DD': '20/April/04', 
    'YYYY/MMMM/DD': '2020/April/04',

    'MM-DD-YY': '04-04-20', 
    'MMMM-DD-YY': 'April-04-20', 
    'MMMM-Do-YY': 'April-4th-20', 
    'MMMM-Do-YYYY': 'April-4th-2020', 

    'DD-MM-YY': '04-04-20', 
    'Do-MM-YY': '4th-04-20', 
    'DD-MMMM-YY': '04-April-20', 
    'Do-MMMM-YY': '4th-April-20', 
    'DD-MM-YYYY': '04-04-2020',

    'YY-MM-DD': '20-04-04', 
    'YYYY-MM-DD': '2020-04-04', 
    'YY-MMMM-DD': '20-April-04', 
    'YYYY-MMMM-DD': '2020-April-04',

    'Unix Timestamp-seconds': '1586079408',
    'Unix Timestamp-milliseconds': '1586079408510',
};
var time_formats={
    'hh:mm': '05:38', 
    'HH:mm': '17:38', 
    'hh:mm:ss': '05:38:30', 
    'HH:mm:ss': '17:38:30',   
    'hh:mm A': '05:38 PM', 
    'hh:mm:ss A': '05:38:30 PM', 
    'HH:mm:ss.SSS': '17:46:53.152', 
    'hh:mm:ss.SSS A': '05:46:53.154 PM', 
    'HH:mm:ss:SSS': '17:46:53:156', 
    'hh:mm:ss:SSS A': '05:46:53:159 PM', 
};
$.each(date_formats, function(key, value) {   
     $('#report_date_format').append($("<option></option>").attr("value",key).text(key + " (" + value + ")")); 
});
$.each(time_formats, function(key, value) {   
     $('#report_time_format').append($("<option></option>").attr("value",key).text(key + " (" + value + ")")); 
});

function timeSelected(){
    var date_fromat;
    var space_format;
    var time_format = $("#report_time_format").val();    

    switch($("#report_date_format").val()){
        case 'Unix Timestamp-seconds': $("#lb_report_time_format").text(moment().unix());return;
        case 'Unix Timestamp-milliseconds': $("#lb_report_time_format").text(moment().valueOf());return;
        default: date_fromat = $("#report_date_format").val();
    }

    switch($("#report_space_format").val()){
        case 'space': space_format = ' '; break;
        case 'comma': space_format = ','; break;
        case 'comaspace': space_format = ', ';
    }
    $("#lb_report_time_format").text(moment().format(date_fromat + space_format + time_format));
}
//------------------------------------------------------


function modifyUserSettings(e){
    var timezone_format = $("#report_timezoneSelector").val() + ',' + moment.tz($("#report_timezoneSelector").val()).utcOffset() * 60;
    var date_fromat = $("#report_date_format").val();
    var space_format = $("#report_space_format").val();
    var time_format = $("#report_time_format").val();
    
    enableDisableMe(e);
    $.post("settings_manager", {
            action_type: "modify_user_settings",
            timezone_format: timezone_format,
            date_fromat: date_fromat,
            space_format: space_format,
            time_format: time_format,
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Settings saved successfully!');
            } else {
                toastr.error('', data);
            }
            enableDisableMe(e);
        });
}
function modifySniperPhishSettings(e){
    var timezone_format = $("#sniperphish_timezoneSelector").val() + ',' + moment.tz($("#sniperphish_timezoneSelector").val()).utcOffset() * 60;
    
    enableDisableMe(e);
    $.post("settings_manager", {
            action_type: "modify_sniperphish_settings",
            timezone_format: timezone_format,
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Settings saved successfully!');
            } else {
                toastr.error('', data);
            }
            enableDisableMe(e);
        });   
}

function getSetingsValues(){
    $.post("settings_manager", {
            action_type: "get_settings"
        },
        function(data, status) {
            if (data) {
                $("#report_timezoneSelector").val(data['report_time_zone'].split(',')[0]).trigger("change");
                $("#report_date_format").val(data['report_time_format'].split(',')[0]).trigger("change");
                $("#report_space_format").val(data['report_time_format'].split(',')[1]).trigger("change");
                $("#report_time_format").val(data['report_time_format'].split(',')[2]).trigger("change");
                $("#sniperphish_timezoneSelector").val(data['sniperphish_time_zone'].split(',')[0]).trigger("change");
                $("#setting_field_mail").val(data['contact_mail']);
            } else {
                toastr.error('', 'Error getting settings');
            }
        });  
}