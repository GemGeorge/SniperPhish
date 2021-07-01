var globalModalValue = nextRandomId ='';
var cookie_c_data = JSON.parse(atob(decodeURIComponent(Cookies.get('c_data'))));
var date_space_format = {"space": " ", "comma": ",", "comaspace":", "};
var space_format = date_space_format[cookie_c_data.time_format.space];

$(function() {
    checkSniperPhishProcess();
    $('[data-toggle="tooltip"]').tooltip({
        trigger : 'hover'
    });
    $('[data-toggle="tooltip"]').on('click mouseleave', function () {
      $('[data-toggle="tooltip"]').tooltip('hide');
    });
});
function displayLoader(dis_val,type="normal"){
    if(type == "small")
        return `<div class="loader">
                  <div class="loader--blue">
                      <div></div>
                      <div></div>
                      <div></div>
                  </div>` + dis_val + `
               </div>`;
    else
        return `<div class="loadercust loader-checking">
                    <div class="loader" >
                    <div class="loader--blue">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div><strong>` + dis_val + `</strong>
                    <div class="loader--green">
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

function UTC2Local(in_val){     //Converts Unix and format 'DD-MM-Y hh:mm A' in UTC to local
    if(in_val == '' || in_val == undefined)
        return '-';

    var time_format = getDateTimeFormat();

    if(moment(in_val, 'DD-MM-Y hh:mm A', true).isValid())       
        return moment.utc(in_val, "DD-MM-Y hh:mm A").tz(cookie_c_data.time_zone.timezone).format(time_format); //timezone => Asia/Kuala_Lumpur
    else
        return moment.unix(in_val/1000+(+cookie_c_data.time_zone.value)).utc().format(time_format);
}

function Local2LocalUNIX(in_val){     //Converts Local date to Unix local 
    if(in_val == '' || in_val == undefined)
        return '-';

    var time_format = getDateTimeFormat();

    return moment(in_val, time_format).unix();
}

function getDateTimeFormat(type=''){
    if(type == 'dateonly')
        return cookie_c_data.time_format.date;
    else
        if(type == 'tzonly')
            return cookie_c_data.time_zone.timezone;
    else
        return cookie_c_data.time_format.date + space_format + cookie_c_data.time_format.time;
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
    if(window.location.href.indexOf('?') == -1){    // works only in main pages
        setTimeout(function (){
            $.post({
                url: window.location.origin + "/spear/home_manager",
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify({ 
                        action_type: "check_process",
                    })
                }).done(function (data) {
                    if (data.result == false) 
                        addAlert('process');
            });           

        }, 1000);        
    }
}

function startSniperPhishService(e){
    $.post({
            url: window.location.origin + "/spear/home_manager",
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({ 
                    action_type: "start_process",
                })
            }).done(function (data) {
                if (data.result) {
                    toastr.success('', 'Service started successfully!');
                    removeAlert("process",e);
                } else
                    toastr.error('', data.error);
        });
}


function addAlert(alert){
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

        if($("#top_notifier div").length == 1){  //if no notifications. 1= top_notifier count element
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

function getBase64ofFile(fun_name,file,el) {
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function () {
        fun_name(file.name,file.size,file.type,reader.result,el);
    };
}

function fadeAni(area=".card") {
    this.toggle = !this.toggle;
    $(area).stop().fadeTo(400, this.toggle ? 0.1 : 1);
}

/*File drop add effects on drag */
$('.dropzone').bind({
     dragover: function(ev) {
         $(this).addClass('dropzone-drag');
         ev.preventDefault();
     },
     
     dragleave: function() {
        $(this).removeClass('dropzone-drag');
     }
});

/*File drop remove effect on upload*/
function uploadFile(ev,upload_fn,el){
    var file = ev.dataTransfer.items[0].getAsFile();
    getBase64ofFile(upload_fn,file,el)
    ev.preventDefault();
    $(el).removeClass('dropzone-drag');
}
//---------------------
function isValidURL(url) {
  try {
    new URL(url);
  } catch (e) {
    return false;
  }
  return true;
}

function RegTest(str,type){
    var pattern;
    switch (type){
        case 'NUM' : pattern = /^\d+$/; break;
        case 'ALPHA' : pattern = /^[a-z]+$/i; break;
        case 'ALPHA_NUM' : pattern = /^[a-z\d\-_\s]+$/i; break;
        case 'COMMON' : pattern = /^[a-z\d\-_\s()@.*+-]+$/i; break;
        case 'EMAIL' : pattern = /(?!.*\.{2})^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i; break;
    }
    return pattern.test(str);
}

/*------Idle Timer------*/
var idleMax = 3600; // Logout after x seconds of IDLE. (in sec). 3600=1hr
var idleTime = 0;
var timerInterval = 60000 // idle check interval  (in ms). 60000=1 minute
var idleInterval = setInterval("idleTimerFun()", timerInterval);  
$("body").mousemove(function( event ) {
    idleTime = 0; // reset to zero
});

function idleTimerFun() {
    idleTime++;
    if (idleTime > idleMax){
        $.post({
            url: window.location.origin + "/spear/session_manager",
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({ 
                    action_type: "terminate_session"
                })
            });
                  
        clearTimeout(idleInterval); //stop timer 
        $('footer').append(`<div class="modal fade" id="modal_relogin" data-keyboard="false" data-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
           <div class="modal-dialog">
              <div class="modal-content">
                 <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                 </div>
                 <div class="modal-body">
                    <div class="row">
                       <div class="col-12">
                          <div class="input-group mb-3">
                             <div class="input-group-prepend">
                                <span class="input-group-text bg-success text-white" id="basic-addon1"><i class="fa fas fa-user"></i></span>
                             </div>
                             <input type="text" class="form-control form-control-lg" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" required>
                          </div>
                          <div class="input-group mb-3">
                             <div class="input-group-prepend">
                                <span class="input-group-text bg-warning text-white" id="basic-addon2"><i class="fa fas fa-key"></i></span>
                             </div>
                             <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" required>
                          </div>
                       </div>
                    </div>
                 </div>
                 <div class="modal-footer">
                    <button type="button" class="btn btn-info" onclick="doReLogin()">Login</button>
                 </div>
              </div>
           </div>
        </div>`);
        $('#modal_relogin').modal('toggle');
    }
} 

function doReLogin(){
    var username = $("input[name=username]").val();
    var pwd = $("input[name=password]").val();

    $.post({
        url: window.location.origin + "/spear/session_manager",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
                action_type: "re_login",
                username: username,
                pwd: pwd
            })
        }).done(function (data) {
            if (data.result == 'success') {
                $('#modal_relogin').modal('toggle');
                idleTime = 0;
                idleInterval = setInterval("idleTimerFun()", timerInterval); 
                $("input[name=password]").val('');  //clear relogin pwd field
            }                 
    });
}