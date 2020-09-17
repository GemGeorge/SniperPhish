var globalModalValue = '';
var nextRandomId = '';
$(function() {
    checkSniperPhishProcess();
    $('[data-toggle="tooltip"]').click(function () {
          $('[data-toggle="tooltip"]').tooltip("hide");
    });
});
function displayLoader(dis_val){
    return `<div class="loadercust loader-checking">
                    <div class="loader" >
                    <div class="loader--blue">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div><strong>`+dis_val+
                    `</strong><div class="loader--green">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div></div>`;
}
function getRandomId() {
    nextRandomId = Math.random().toString(36).substring(2, 8);
    return nextRandomId;
}

function validateEmailAddress(email) {
    var expression = /(?!.*\.{2})^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return expression.test(String(email).toLowerCase());
}

function UTC2Local(in_val){     //Converts Unix and format 'DD-MM-Y hh:mm A' in UTC to local
    if(in_val == '' || in_val == undefined)
        return '-';

    var cookie_c_data = atob(decodeURIComponent(Cookies.get('c_data'))).split(',');

    switch(cookie_c_data[3]){
        case 'space': space_format = ' '; break;
        case 'comma': space_format = ','; break;
        case 'comaspace': space_format = ', ';
    }

    var rep_time_format = cookie_c_data[2] + space_format + cookie_c_data[4];

    if(moment(in_val, 'DD-MM-Y hh:mm A', true).isValid())       
        return moment.utc(in_val, "DD-MM-Y hh:mm A").tz(cookie_c_data[0]).format(rep_time_format); //eg: cookie_c_data[0]=Asia/Kuala_Lumpur
    else
        return moment.unix(in_val/1000+(+cookie_c_data[1])).utc().format(rep_time_format);
}

function Local2LocalUNIX(in_val){     //Converts Local date to Unix local 
    if(in_val == '' || in_val == undefined)
        return '-';

    var cookie_c_data = atob(decodeURIComponent(Cookies.get('c_data'))).split(',');

    switch(cookie_c_data[3]){
        case 'space': space_format = ' '; break;
        case 'comma': space_format = ','; break;
        case 'comaspace': space_format = ', ';
    }

    var rep_time_format = cookie_c_data[2] + space_format + cookie_c_data[4];

    return moment(in_val, rep_time_format).unix();
}

function getDateTimeFormat(type=''){
    var cookie_c_data = atob(decodeURIComponent(Cookies.get('c_data'))).split(',');

    switch(cookie_c_data[3]){
        case 'space': space_format = ' '; break;
        case 'comma': space_format = ','; break;
        case 'comaspace': space_format = ', ';
    }
    if(type == 'dateonly')
        return cookie_c_data[2];
    else
        if(type == 'tzonly')
            return cookie_c_data[0];
    else
        return cookie_c_data[2] + space_format + cookie_c_data[4];
}

function UTC2LocalUNIX(in_val){ // 02-05-2020 07:10 AM => 1588403400000
    return moment.utc(in_val, "DD-MM-Y hh:mm A").tz(getDateTimeFormat('tzonly')).valueOf();
}

function LocalUNIX2LocalDate(in_val,format=''){ // 1588403400000 => 02-05-2020
    return moment(in_val).format(getDateTimeFormat('dateonly'));
}

function LocalUNIX2Local(in_val,format=''){ // 1588403400000 => 02-05-2020 03:10:00 PM
    return moment(in_val).format(getDateTimeFormat());
}
//---------------------------------
Array.prototype.forEach.call(document.body.querySelectorAll("*[data-mask]"), applyDataMask);
function applyDataMask(field) {
    var mask = field.dataset.mask.split('');

    // For now, this just strips everything that's not a number
    function stripMask(maskedData) {
        function isDigit(char) {
            return /\d/.test(char);
        }
        return maskedData.split('').filter(isDigit);
    }

    // Replace `_` characters with characters from `data`
    function applyMask(data) {
        return mask.map(function(char) {
            if (char != '_') return char;
            if (data.length == 0) return char;
            return data.shift();
        }).join('')
    }

    function reapplyMask(data) {
        return applyMask(stripMask(data));
    }

    function changed() {
        var oldStart = field.selectionStart;
        var oldEnd = field.selectionEnd;

        field.value = reapplyMask(field.value);

        field.selectionStart = oldStart;
        field.selectionEnd = oldEnd;
    }

    field.addEventListener('click', changed)
    field.addEventListener('keyup', changed)
}
//----------------------------------------------


//----------------------------------------------
function checkSniperPhishProcess(){
   // if(window.location.href.indexOf('?') == -1){    // works only in main pages
        setTimeout(function (){
            $.post(window.location.origin + "/spear/home_manager", {
                action_type: "check_process",
            },
            function(data, status) {
                if (data != "success") 
                    addAlert('process')
            });

        }, 1000);
        
  //  }
}

function startSniperPhishService(e){
    $.post(window.location.origin + "/spear/home_manager", {
            action_type: "start_process",
        },
        function(data, status) {
            if (data == "success") {
                toastr.success('', 'Service started successfully!');
                removeAlert("process",e);
            } else
                toastr.error('', data);
        });
}


function addAlert(alert){//console.log($("#top_notifier div").length);

    $("#top_notifier").append(`<span class="alert-count">` + ($("#top_notifier div").length+1) + `</span>`);

    $("#top_notifier").append(`<div class="dropdown-menu dropdown-menu-right mailbox animated fadeInDown" aria-labelledby="2"></div>`);
               
    if(alert == "process")
        $("#top_notifier div").append(`<a href="#" onclick="startSniperPhishService($(this))" class="dropdown-item">
                           <div class="d-flex no-block align-items-center p-10">
                                <span class="btn btn-danger btn-circle"><i class="mdi mdi-alert"></i></span>
                                <div class="m-l-15">
                                    <h5 class="m-b-0">SniperPhish service</h5>
                                    <span class="mail-desc">Service is not running. Click here to start.</span> 
                                </div>
                           </div>
                        </a>`);
}

function removeAlert(alert,e){
    if(alert == "process"){
        e.remove();
        if($("#top_notifier div").length>0)
            $("#top_notifier").append(`<span class="alert-count">` + ($("#top_notifier div").length-1) + `</span>`);

        if($("#top_notifier div").length == 1){  //if no notificqations. 1= top_notifier count element
            $(".alert-count").remove();
            $("#top_notifier a li").remove();
        }
    }
}

//Button loader
function enableDisableMe(e){
    if(!e)
        return;
    if(!e.is('[disabled=disabled]'))
        e.attr('disabled', true);
    else
        e.attr('disabled', false);
    e.children(":first").toggleClass('fa-spinner fa-spin');
}


$('body').on('click', function (e) {
    if (!$('[data-toggle="popover"]').is(e.target))
        $('[data-toggle="popover"]').popover('hide');
});