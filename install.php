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
                  <span class="db"><img src="spear/images/logo-icon2x.png" alt="logo" /><img src="spear/images/logo.png" alt="logo" /> v0.4.1 beta</span>
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
                                 <input type="text" class="form-control form-control-lg" placeholder="MySQL DB Name" id="tb_db_name" aria-label="Username" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon2"><i class="fa fas fa-server"></i></span>
                                 </div>
                                 <input type="text" class="form-control form-control-lg" placeholder="MySQL DB Host" id="tb_db_host" value="localhost" aria-label="MySQL DB Host" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fa fas fa-user"></i></span>
                                 </div>
                                 <input type="text" class="form-control form-control-lg" placeholder="DB Username" id="tb_db_user_name" aria-label="Username" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon2"><i class="fa fas fa-key"></i></span>
                                 </div>
                                 <input type="password" class="form-control form-control-lg" placeholder="DB User Password" id="tb_db_user_pwd" aria-label="Password" aria-describedby="basic-addon1">
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-white" id="basic-addon2"><i class="fa fa-envelope"></i></span>
                                 </div>
                                 <input type="email" class="form-control form-control-lg" placeholder="Your Email" id="tb_contact_mail" aria-label="Your Email" aria-describedby="basic-addon1" required>
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
                              <button class="btn btn-info float-right" id="bt_install" type="submit" disabled><i class="fa fas"></i> Install</button>
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
      <script src="spear/js/libs/jquery/jquery-3.5.1.min.js"></script>
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
            $("#inst_fields").hide();
            $.post("install_manager", {
                    action_type: "check_requirements",
                },
                function(data, status) {
                    $.each(data, function(req, val) {
                        if (req != 'code')
                            $("#tb_install tbody").append("<tr><td>" + req + "</td><td>" + val + "</td></tr>");
                    })
                    if (data['code'] == true) //Error 
                        $("#tb_install tbody tr:first th:nth-child(2)").html("<i class='fas fa-exclamation-triangle text-danger'></i>");
                    else {
                        $("#tb_install tbody tr:first th:nth-child(2)").html("<i class='fas fa-check fa-lg text-success'></i>");
                        $("#inst_fields").show(500);
                        $("#bt_install").attr('disabled', false);
                    }   
                });
        });

        $("#doInstall").submit(function(event) {
            event.preventDefault();
            
            $("#bt_install").attr('disabled', true);
            $("#bt_install i").toggleClass('fa-spinner fa-spin');
            $.post("install_manager", {
                    action_type: "do_install",
                    db_name: $("#tb_db_name").val(),
                    db_host: $("#tb_db_host").val(),
                    db_user_name: $("#tb_db_user_name").val(),
                    db_user_pwd: $("#tb_db_user_pwd").val(),
                    user_contact_mail: $("#tb_contact_mail").val(),
                    timezone_format: $("#sniperphish_timezoneSelector").val() + ',' + moment.tz($("#sniperphish_timezoneSelector").val()).utcOffset() * 60,
                },
                function(data, status) {
                    $("#bt_install i").toggleClass('fa-spinner fa-spin');

                    if (data != "success")
                        $("#lb_error").html('<span class="text-danger">' + data + '</span>');
                    else {
                        $("#lb_error").html('<span class="text-success">Installation successs. SniperPhish will rediect to login screen in few seconds..</span>');
                        setTimeout(function() {
                            document.location = location.origin + '/spear';
                        }, 3000);
                    }              
                    $("#bt_install").attr('disabled', false);
                });
        });




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
            document.querySelector("#sniperphish_timezoneSelector").value = "Asia/Kuala_Lumpur";
        });

      </script>
   </body>
</html>