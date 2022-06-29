getSetingsValues();

$("#selector_timezone").select2({
    minimumResultsForSearch: -1
});
$("#selector_date_format").select2({
    minimumResultsForSearch: -1
});
$("#selector_space_format").select2({
    minimumResultsForSearch: -1
});
$("#selector_time_format").select2({
    minimumResultsForSearch: -1
});

$("#lb_selector_time_format").show();

$(function() {
    $('#selector_timezone, #selector_date_format, #selector_space_format, #selector_time_format').on("change", function(e) {
        timeSelected();
    });
});

//------------------------Timezone Section----------------------------
$(document).ready(function() {
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
    
    const _t = (s) => {
        if (i18n !== void 0 && i18n[s]) {
            return i18n[s];
        }
        return s;
    };

    const dateTimeUtc = moment("2017-06-05T19:41:03Z").utc();
    $("#selector_timezone").html(dateTimeUtc.format("ddd, DD MMM YYYY HH:mm:ss"));

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

    $("#selector_timezone").html(selectorOptions);
    $("#selector_timezone").val("Asia/Kuala_Lumpur");    
});

var date_formats={
    'd-m-y': '15-05-21',
    'd/m/y': '15/05/21',

    'd-m-o': '15-05-2021',
    'd/m/o': '15/05/2021',

    'd m y': '15 05 21',
    'd m o': '15 05 2021',
    'm d y': '05 15 21', 
    'm d o': '05 15 2021', 
    'F d y': 'May 15 21',
    'F d o': 'May 15 2021', 
 
    'd F y': '15 May 21', 
    'd F o': '15 May 2021', 
    'jS F y': '15th May 21', 
    'jS F o': '15th May 2021', 

    'y m d': '21 05 15', 
    'Y m d': '2021 05 15', 
    'y F d': '21 May 15', 
    'o F d': '2021 May 15', 
 
    'F/d/y': 'May/15/21', 
    'F/d/o': 'May/15/2021', 
    'F/jS/y': 'May/15th/21', 
    'F/jS/o': 'May/15th/2021', 

    'm/F/y': '15/May/21', 
    'm/F/o': '15/May/2021', 
    'jS/F/y': '4th/May/21', 
    'jS/F/o': '4th/May/2021',

    'm/d/y': '05/15/21',

    'o/m/d': '2021/05/15', 
    'y/F/d': '21/May/15', 

    'm-d-y': '05-15-21', 
    'm-d-o': '05-15-2021', 
    'F-d-y': 'May-15-21',
    'F-d-o': 'May-15-2021', 

    'm-F-y': '15-May-21', 
    'm-F-o': '15-May-2021', 
    'jS-F-y': '4th-May-21', 
    'jS-F-o': '4th-May-2021',

    'y-m-d': '21-05-15', 
    'Y-m-d': '2021-05-15', 
    'y-F-d': '21-May-15', 
    'o-F-d': '2021-May-15', 

    'Unix Timestamp-seconds': '1586079408',
    'Unix Timestamp-milliseconds': '1586079408510',
};
var selector_time_formats={
    'h:i': '02:38', 
    'H:i': '14:38', 
    'h:i:s': '02:38:30', 
    'H:i:s': '14:38:30',   
    'h:i a': '02:38 pm', 
    'h:i A': '02:38 PM', 
    'h:i:s a': '02:38:30 pm', 
    'h:i:s A': '02:38:30 PM', 
    'h:i:s.v': '02:38:30.152', 
    'H:i:s.v': '14:38:30.152', 
    'h:i:s:v': '02:38:30.152', 
    'H:i:s:v': '14:38:30.152', 
    'h:i:s:v a': '02:38:30.152 pm', 
    'h:i:s:v A': '02:38:30.152 PM',  
};
$.each(date_formats, function(name, value) {   
    $('#selector_date_format').append($("<option></option>").attr("value",name).text(value)); 
});
$.each(selector_time_formats, function(name, value) {   
    $('#selector_time_format').append($("<option></option>").attr("value",name).text(value)); 
});

function timeSelected(time_zone=null){
    var space_format;
    var date_format = $("#selector_date_format").val();
    var time_format = $("#selector_time_format").val(); 
    if(time_zone == null)
        time_zone = $("#selector_timezone").val();

    switch($("#selector_space_format").val()){
        case 'space': space_format = ' '; break;
        case 'comma': space_format = ','; break;
        case 'comaspace': space_format = ', ';
    }

    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_date_time_display",
            time_zone: time_zone,
            date_time_format: date_format + space_format + time_format,
        }),
    }).done(function (response) {
        $("#lb_selector_time_format").text(response.result);
    }); 
}
//------------------------------------------------------

function modifyTimeStampSettings(e){
    var time_zone = { "timezone":$("#selector_timezone").val(), "value":moment.tz($("#selector_timezone").val()).utcOffset() * 60};
    var time_format = {"date":$("#selector_date_format").val(), "space": $("#selector_space_format").val(), "time":$("#selector_time_format").val()};
    
    enableDisableMe(e);
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "modify_timestamp_settings",
            time_zone: time_zone,
            time_format: time_format,
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Settings saved successfully!');   
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function getSetingsValues(){
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_timestamp_settings"
         })
    }).done(function (data) {
        if(!data.error){  // no data error
            $("#selector_timezone").val(data.time_zone.timezone).trigger("change");
            $("#selector_date_format").val(data.time_format.date).trigger("change");
            $("#selector_space_format").val(data.time_format.space).trigger("change");
            $("#selector_time_format").val(data.time_format.time).trigger("change");
            $("#setting_field_mail").val(data.contact_mail);
            $("#tb_sp_url").val(data.baseurl);
            timeSelected(data.time_zone.timezone);
        }
    }); 
}

function modifySPBaseURL(e){   
    var baseurl = $("#tb_sp_url").val();
    if (!isValidURL(baseurl)) {
        $('#tb_sp_url').addClass('is-invalid');
        return;
    } 
    else
        $('#tb_sp_url').removeClass('is-invalid');

    enableDisableMe(e);
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "modify_SP_base_URL",
            baseurl: baseurl
        }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'URL updated successfully!');   
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

function clearJunkSPData(e){    
    enableDisableMe(e);
    $.post({
        url: "manager/settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "clear_junk_SP_data",
        }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Junk data cleared successfully!');   
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}