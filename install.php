<!DOCTYPE html>
<?php
    require_once(dirname(__FILE__) . '/spear/common_functions.php');
    checkInstallation();
?>
<html dir="ltr">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- Tell the browser to be responsive to screen width -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- Favicon icon -->
      <link rel="icon" type="image/png" sizes="16x16" href="spear/images/favicon.png">
      <title>SniperPhish - The Web-Email Spear Phishing Toolkit</title>
      <!-- Custom CSS -->
      <link rel="stylesheet" type="text/css" href="spear/css/select2.min.css">
      <link rel="stylesheet" type="text/css" href="spear/css/style.min.css">
   </head>
   <body>
      <div class="main-wrapper">
         <!-- ============================================================== -->
         <!-- Preloader - style you can find in spinners.css -->
         <!-- ============================================================== -->
         <div class="preloader">
            <div class="lds-ripple">
               <div class="lds-pos"></div>
               <div class="lds-pos"></div>
            </div>
         </div>
         <!-- ============================================================== -->
         <!-- Preloader - style you can find in spinners.css -->
         <!-- ============================================================== -->
         <!-- ============================================================== -->
         <!-- Login box.scss -->
         <!-- ============================================================== -->
         <div class=" d-flex no-block justify-content-center align-items-center bg-dark">
            <div class="bg-dark border-top border-secondary">
               <div class="text-center p-t-20 p-b-20">
                  <span class="db"><img src="spear/images/logo-icon2x.png" alt="logo" /><img src="spear/images/logo.png" alt="logo" /> v<?php getSniperPhishVersion(); ?></span>
               </div>
            </div>
         </div>
         <div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark req-box">
            <div class="auth-box bg-dark req-box">
               <div class="row border-top border-secondary">
                  <div class="col-12 p-t-20">
                     <div class="table-responsive">
                        <table class="table-borderless table-install" id="tb_install">
                           <tbody>
                              <tr>
                                 <th>Requirements check </th>
                                 <th><i class="fa fa-spinner fa-spin"></i></th>
                              </tr>
                           </tbody>
                        </table>
                        <div id="comm_error" class="text-danger" hidden=""></div>
                     </div>
                  </div>
               </div>
               <form class="form-horizontal m-t-20" id="doInstall">
                  <div class="row border-top border-secondary">
                     <div class="col-12">
                        <div class="form-group p-t-20">
                           <div id="inst_fields">
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fa fas fa-database"></i></span>
                                 </div>
                                 <input type="text" class="form-control form-control" placeholder="MySQL DB Name" id="tb_db_name" aria-label="Username" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon2"><i class="fa fas fa-server"></i></span>
                                 </div>
                                 <input type="text" class="form-control form-control" placeholder="MySQL DB Host" id="tb_db_host" value="localhost" aria-label="MySQL DB Host" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fa fas fa-user"></i></span>
                                 </div>
                                 <input type="text" class="form-control form-control" placeholder="DB Username" id="tb_db_user_name" aria-label="Username" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon2"><i class="fa fas fa-key"></i></span>
                                 </div>
                                 <input type="password" class="form-control form-control" placeholder="DB User Password" id="tb_db_user_pwd" aria-label="Password" aria-describedby="basic-addon1">
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon2"><i class="fa fa-envelope"></i></span>
                                 </div>
                                 <input type="email" class="form-control form-control" placeholder="Your Email" id="tb_contact_mail" aria-label="Your Email" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <select class="select2 form-control custom-select" id="sniperphish_timezoneSelector" style="height: 36px;width: 100%;">
                                 </select>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row border-top border-secondary">
                     <div class="col-12">
                        <div class="form-group">
                           <div class="p-t-20">
                              <button class="btn btn-info float-right" id="bt_install" type="submit"><i class="fa fas"></i> Install</button>
                           </div>
                        </div>
                     </div>
                     <div id="lb_error" class="m-t-10"></div>
                  </div>
              </form>
            </div>
         </div>
      </div>
       <div class="auth-wrapper  bg-dark">
            
       </div>
      <!-- ============================================================== -->
      <!-- All Required js -->
      <!-- ============================================================== -->
      <script src="spear/js/libs/jquery/jquery-3.6.0.min.js"></script>
      <script src="spear/js/libs/js.cookie.min.js"></script>
      <!-- Bootstrap tether Core JavaScript -->
      <script src="spear/js/libs/popper.min.js"></script>
      <script src="spear/js/libs/bootstrap.min.js"></script>
      <!-- ============================================================== -->
      <!-- This page plugin js -->
      <!-- ============================================================== -->
      <script src="spear/js/libs/select2.min.js"></script>
      <script src="spear/js/libs/moment.min.js"></script>
      <script src="spear/js/libs/moment-timezone-with-data.min.js"></script>
      <script>
        $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        $(".preloader").fadeOut();
        $("#sniperphish_timezoneSelector").select2({
            minimumResultsForSearch: -1
        });
        // ============================================================== 
        $(function() {
            $.post({
                url: "install_manager",
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify({ 
                    action_type: "check_requirements",
              }),
            }).done(function (data) {
                if(data.permissions.length == 0)
                    $("#tb_install tbody").append("<tr><td>Directory permissions</td><td><i class='fas fa-check fa-lg text-success'></i></td></tr>");
                else
                    $("#tb_install tbody").append('<tr><td>Directory permissions</td><td><i class="fas fa-times fa-lg text-danger cursor-pointer" data-container="body" data-toggle="popover" data-placement="top" data-html="true" data-content="Enable write permission for:<br/>' + data.permissions.join('<br/>') + '"></i></td></tr>');

                $.each(data.requirements, function(req_name, error_info) {
                  if(error_info == true)
                    $("#tb_install tbody").append('<tr><td>' + req_name + '</td><td><i class="fas fa-check fa-lg text-success"></i></td></tr>');
                    else
                      $("#tb_install tbody").append('<tr><td>' + req_name + '</td><td><i class="fas fa-times fa-lg text-danger cursor-pointer" data-container="body" data-toggle="popover" data-placement="top" data-html="true" data-content="' + error_info + '"></i></td></tr>');
                })

                if(data.error){
                    $("#tb_install tbody tr:first th:nth-child(2)").html("<i class='fas fa-exclamation-triangle text-danger'></i>");
                    $("#bt_install").attr('disabled', true);
                }
                else{
                    $("#tb_install tbody tr:first th:nth-child(2)").html("<i class='fas fa-check fa-lg text-success'></i>");
                    $("#bt_install").attr('disabled', false);
                }  

                $('[data-toggle="popover"]').popover();
            }).fail(function(error) {
                $("#tb_install tbody tr:first th:nth-child(2)").html("<i class='fas fa-exclamation-triangle text-danger'></i>");
                $("#comm_error").attr("hidden",false);

                $.get("install_manager.php", function(data2) {     
                    $("#comm_error").text(data2);             
                }).fail(function(error2) {
                    if(error2.status == 200)
                        $("#comm_error").html('The .htaccess file from web root is either a) missing, b)incorrectly configured or c)it\'s support does not enabled in your web server. SniperPhish requires web server configuration to ignore .php from URLs. You should configure .htaccess file to ignore .php extension.');
                    else
                        $("#comm_error").text('Can not access /install_manager.php. Error:' + error2.status + ' ' + error2.statusText);
                });
            });
        });

        $("#doInstall").submit(function(event) {
            event.preventDefault();
            var time_zone = { "timezone":$("#sniperphish_timezoneSelector").val(), "value":moment.tz($("#sniperphish_timezoneSelector").val()).utcOffset() * 60};
            
            $("#bt_install").attr('disabled', true);
            $("#bt_install i").toggleClass('fa-spinner fa-spin');
            $.post({
                url: "install_manager",
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify({ 
                    action_type: "do_install",
                    db_name: $("#tb_db_name").val(),
                    db_host: $("#tb_db_host").val(),
                    db_user_name: $("#tb_db_user_name").val(),
                    db_user_pwd: $("#tb_db_user_pwd").val(),
                    user_contact_mail: $("#tb_contact_mail").val(),
                    time_zone: time_zone,
                 }),
            }).done(function (data) {
                $("#bt_install i").toggleClass('fa-spinner fa-spin');

                if(!data.error){
                  $("#lb_error").html('<span class="text-success">Installation successs. SniperPhish will rediect to <a href="/spear">login page</a> in few seconds..</span>');
                    setTimeout(function() {
                        document.location = location.origin + '/spear';
                    }, 3000);
                }
                else
                  $("#lb_error").html('<span class="text-danger">' + data.error + '</span>');
           
                $("#bt_install").attr('disabled', false);
            }); 
        });

        $('html').on('click', function(e) {
          if (!$(e.target).is('.fa-times') && $(e.target).closest('.popover').length !=1 )
                $('[data-toggle="popover"]').popover('hide');  
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

            $("#sniperphish_timezoneSelector").html(selectorOptions);
            $("#sniperphish_timezoneSelector").val("Asia/Kuala_Lumpur");   
        });
      </script>
   </body>
</html>
