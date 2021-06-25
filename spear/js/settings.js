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
    

function modifyAccount(e){
    var setting_field_uname = $("#setting_field_uname").val();
    var setting_field_mail = $("#setting_field_mail").val();
	var setting_field_old_pwd = $("#setting_field_old_pwd").val();
	var setting_field_new_pwd = $("#setting_field_new_pwd").val();
	var setting_field_confirm_pwd = $("#setting_field_confirm_pwd").val();

    if(RegTest(setting_field_mail, 'EMAIL') == false){
        $("#setting_field_mail").addClass("is-invalid");
        return;
    } else
        $("#setting_field_mail").removeClass("is-invalid");

    if (setting_field_old_pwd == "") {
        $("#setting_field_old_pwd").addClass("is-invalid");
        return;
    } else
        $("#setting_field_old_pwd").removeClass("is-invalid");
	
	if(setting_field_new_pwd != setting_field_confirm_pwd){
		toastr.error('', 'Confirm password does not match!');
		return;
	}

    enableDisableMe(e);
    $.post({
        url: "settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "modify_account",
            setting_field_uname: setting_field_uname,
            setting_field_mail: setting_field_mail,
            setting_field_old_pwd: setting_field_old_pwd,
            setting_field_new_pwd: setting_field_new_pwd,
         }),
    }).done(function (response) {
        if(response.result == "success"){ 
            toastr.success('', 'Information updated successfully!');   
            $("#setting_field_old_pwd").val('');
            $("#setting_field_new_pwd").val('');
            $("#setting_field_confirm_pwd").val('');
        }
        else
            toastr.error('', response.error);
        enableDisableMe(e);
    }); 
}

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
var selector_time_formats={
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
$.each(date_formats, function(name, value) {   
     $('#selector_date_format').append($("<option></option>").attr("value",name).text(name + " (" + value + ")")); 
});
$.each(selector_time_formats, function(name, value) {   
     $('#selector_time_format').append($("<option></option>").attr("value",name).text(name + " (" + value + ")")); 
});

function timeSelected(){
    var date_format;
    var space_format;
    var time_format = $("#selector_time_format").val();    

    switch($("#selector_date_format").val()){
        case 'Unix Timestamp-seconds': $("#lb_selector_time_format").text(moment().unix());return;
        case 'Unix Timestamp-milliseconds': $("#lb_selector_time_format").text(moment().valueOf());return;
        default: date_format = $("#selector_date_format").val();
    }

    switch($("#selector_space_format").val()){
        case 'space': space_format = ' '; break;
        case 'comma': space_format = ','; break;
        case 'comaspace': space_format = ', ';
    }
    $("#lb_selector_time_format").text(moment().format(date_format + space_format + time_format));
}
//------------------------------------------------------


function modifyUserSettings(e){
    var time_zone = { "timezone":$("#selector_timezone").val(), "value":moment.tz($("#selector_timezone").val()).utcOffset() * 60};
    var time_format = {"date":$("#selector_date_format").val(), "space": $("#selector_space_format").val(), "time":$("#selector_time_format").val()};
    
    enableDisableMe(e);
    $.post({
        url: "settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "modify_user_settings",
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
        url: "settings_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "get_settings"
         })
    }).done(function (data) {
        if(!data.error){  // no data error
            $("#selector_timezone").val(data.time_zone.timezone).trigger("change");
            $("#selector_date_format").val(data.time_format.date).trigger("change");
            $("#selector_space_format").val(data.time_format.space).trigger("change");
            $("#selector_time_format").val(data.time_format.time).trigger("change");
            $("#setting_field_mail").val(data.contact_mail);
        }
    }); 
}

function checkUpdates(e){
    enableDisableMe(e);
    $.post({
        url: "https://sniperphish.com/update_info",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "check_updates"
        })
    }).done(function (data) {
        if(!data.error){
            if(isNewerVersion(curr_version,data.latest_version))
                $("#lb_new_version_status").html("New version available. Click <a href ='" + data.download_link + "'>here</a> to download. " + data.msg );
            else
                $("#lb_new_version_status").text("You are using the latest version.");
        }
        enableDisableMe(e);
    }); 
    
}

function isNewerVersion (oldVer, newVer) {
  const oldParts = oldVer.split('.')
  const newParts = newVer.split('.')
  for (var i = 0; i < newParts.length; i++) {
    const a = ~~newParts[i] // parse int
    const b = ~~oldParts[i] // parse int
    if (a > b) return true
    if (a < b) return false
  }
  return false
}