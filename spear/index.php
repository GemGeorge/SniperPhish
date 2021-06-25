<?php
   require_once(dirname(__FILE__) . '/session_manager.php');
   if(isSessionValid() == true){
      header("Location: Home");
      die();
  }
   
  if (!empty($_POST['username']) && !empty($_POST['password'])) {
      if(validateLogin($_POST['username'],$_POST['password']) == true){
         createSession(true,$_POST['username']);
         setInfoCookie();  //c_data cookie sets
         header("Location: Home");
      }  
   }
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
         <div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark">
            <div class="auth-box bg-dark border-top border-secondary">
               <div id="loginform">
                  <div class="text-center p-t-20 p-b-20">
                     <span class="db"><img src="images/logo-icon2x.png" alt="logo" /><img src="images/logo.png" alt="logo" /> v<?php getSniperPhishVersion(); ?></span>
                  </div>
                  <!-- Form -->
                  <form class="form-horizontal m-t-20" id="loginform" action="index" method="post" onsubmit="doLogin()">
                     <div class="row p-b-30">
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
                     <div class="row border-top border-secondary">
                        <div class="col-12">
                           <div class="form-group">
                              <div class="p-t-20">
                                 <button class="btn btn-info" id="to-recover" type="button"><i class="fa fa-lock m-r-5"></i> Lost password?</button>
                                 <button class="btn btn-info float-right" name="login" type="submit" ><i class="fa fas"></i> Login</button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
               <div id="recoverform">
                  <div class="text-center">
                     <span class="text-white">Enter your e-mail address below and we will send you instructions how to recover a password.</span>
                  </div>
                  <div class="row m-t-20">
                     <!-- Form -->
                     <form class="col-12" id="recoveryform" action="index">
                        <!-- email -->
                        <div class="input-group mb-3">
                           <div class="input-group-prepend">
                              <span class="input-group-text bg-danger text-white" id="basic-addon1"><i class="mdi mdi-email-outline"></i></span>
                           </div>
                           <input type="email" class="form-control form-control-lg" id="tb_recoverymail" placeholder="Email Address" aria-label="Username" aria-describedby="basic-addon1" required>
                        </div>
                        <!-- pwd -->
                        <div class="row m-t-20 p-t-20 border-top border-secondary">
                           <div class="col-12">
                              <a class="btn btn-success" href="#" id="to-login">Back To Login</a>
                              <button class="btn btn-info float-right" type="submit" name="recovery"><i class="fa fas"></i> Recover</button>
                           </div>
                        </div>
                        <div id="lb_msg" class="m-t-10"></div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <!-- ============================================================== -->
         <!-- Login box.scss -->
         <!-- ============================================================== -->
         <!-- ============================================================== -->
         <!-- Page wrapper scss in scafholding.scss -->
         <!-- ============================================================== -->
         <!-- ============================================================== -->
         <!-- Page wrapper scss in scafholding.scss -->
         <!-- ============================================================== -->
         <!-- ============================================================== -->
         <!-- Right Sidebar -->
         <!-- ============================================================== -->
         <!-- ============================================================== -->
         <!-- Right Sidebar -->
         <!-- ============================================================== -->
      </div>
      <!-- ============================================================== -->
      <!-- All Required js -->
      <!-- ============================================================== -->
      <script src="js/libs/jquery/jquery-3.6.0.min.js"></script>
      <!-- ============================================================== -->
      <!-- This page plugin js -->
      <!-- ============================================================== -->
      <script>
         $(".preloader").fadeOut();
         // ============================================================== 
         // Login and Recover Password 
         // ============================================================== 
         $('#to-recover').on("click", function() {
             $("#loginform").slideUp();
             $("#recoverform").fadeIn();
         });
         $('#to-login').click(function(){             
             $("#recoverform").hide();
             $("#loginform").fadeIn();
         });

         function doLogin(){
            e= $('[name ="login"]');
            if($('[name ="username"]').val()=='' || $('[name ="password"]').val()=='')
               return;
             if(!e.is('[disabled=disabled]'))
                 e.attr('disabled', true);
             else
                 e.attr('disabled', false);
             e.children(":first").toggleClass('fa-spinner fa-spin');
         }

        $("#recoveryform").submit(function(e) {
            e.preventDefault();
            $('[name ="recovery"]').children(":first").toggleClass('fa-spinner fa-spin');

            $.post({
                url: "pwd_manager",
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify({ 
                    action_type: "send_pwd_reset",
                    contact_mail: $("#tb_recoverymail").val()
                })
            }).done(function (data) {
                $('[name ="recovery"]').children(":first").toggleClass('fa-spinner fa-spin');
                if(data.result == "success"){
                    $("#lb_msg").html('<span class="text-success">If the email is valid, you will receive a password reset link now. Please note the link is valid for 48hrs only.</span>');
                    $("#tb_recoverymail").val('');
                }
                else
                    $("#lb_msg").html('<span class="text-danger">' + data.error + '</span>');
              });
            }); 
      </script>
   </body>
</html>