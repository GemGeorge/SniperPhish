<?php
   require_once(dirname(__FILE__) . '/db.php');
   require_once(dirname(__FILE__) . '/common_functions.php');
   if(isset($_GET['token'])){  
      if(!isTokenValid($conn,$_GET['token']))
        die("Incorrect request");
   }
   else
    die();
?>
<!DOCTYPE html>
<html dir="ltr">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- Tell the browser to be responsive to screen width -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- Favicon icon -->
      <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
      <title>SniperPhish - The Web-Email Spear Phishing Toolkit</title>
      <!-- Custom CSS -->
      <link rel="stylesheet" type="text/css" href="css/select2.min.css">
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
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
                  <span class="db"><img src="images/logo-icon2x.png" alt="logo" /><img src="images/logo.png" alt="logo" /></span>
               </div>
            </div>
         </div>
         <div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark req-box">
            <div class="auth-box bg-dark req-box">
               <form class="form-horizontal m-t-20" id="doPwdReset">
                  <div class="row border-top border-secondary">
                     <div class="col-12">
                        <div class="form-group p-t-20">
                           <div id="inst_fields">
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fa fas fa-key"></i></span>
                                 </div>
                                 <input type="password" class="form-control form-control-lg" placeholder="New Password" id="tb_pwd" aria-label="Username" aria-describedby="basic-addon1" required>
                              </div>
                              <div class="input-group mb-3">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon2"><i class="fa fas fa-key"></i></span>
                                 </div>
                                 <input type="password" class="form-control form-control-lg" placeholder="Confirm Password" id="tb_pwd_confirm" aria-label="Password" aria-describedby="basic-addon1" required>
                              </div>                              
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row border-top border-secondary">
                     <div class="col-12">
                        <div class="form-group">
                           <div class="p-t-20">
                              <button class="btn btn-info float-right" id="bt_reset_pwd" type="submit"><i class="fa fas"></i> Change</button>
                           </div>
                        </div>
                     </div>
                     <div id="lb_msg" class="m-t-10"></div>
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
      <script src="js/libs/jquery/jquery-3.6.0.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script>
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <!-- ============================================================== -->
      <!-- This page plugin js -->
      <!-- ============================================================== -->
      <script src="js/libs/select2.min.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script>
        $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
        $(".preloader").fadeOut();
        // ============================================================== 

        $("#doPwdReset").submit(function(event) {
            event.preventDefault();
            
            if($("#tb_pwd").val() != $("#tb_pwd_confirm").val()){
              $("#lb_msg").html('<span class="text-danger">Passwords are not matching.</span>');
              return;
            }

            $("#bt_reset_pwd i").toggleClass('fa-spinner fa-spin');
            $.post("pwd_manager", {
                    action_type: "do_change_pwd",
                    new_pwd: $("#tb_pwd").val(),
                    token: location.search.split("?token=")[1],
                },
                function(data, status) {
                    $("#bt_reset_pwd i").toggleClass('fa-spinner fa-spin');

                    if (data != "success")
                        $("#lb_msg").html('<span class="text-danger">' + data + '</span>');
                    else {
                        $("#lb_msg").html('<span class="text-success">Password reset successs. SniperPhish will rediect to login screen in few seconds..</span>');
                        setTimeout(function() {
                            document.location = location.origin + '/spear';
                        }, 3000);
                    }   
                });
        });
      </script>
   </body>
</html>