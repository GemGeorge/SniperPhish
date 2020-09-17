<?php
    @ob_start();
    session_start();
?>
<!DOCTYPE html>
<?php
   require_once(dirname(__FILE__) . '/session_manager.php');
   checkSession(false);
?>
<html dir="ltr" lang="en">
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
      <link type="text/css" href="css/jquery.steps.css" rel="stylesheet">
      <link type="text/css" href="css/steps.css" rel="stylesheet">
      <link rel="stylesheet" type="text/css" href="css/select2.min.css">
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
      <script src="js/libs/clipboard.min.js"></script>  
   </head>
   <body>
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
      <!-- Main wrapper - style you can find in pages.scss -->
      <!-- ============================================================== -->
      <div id="main-wrapper">
         <!-- ============================================================== -->
         <!-- Topbar header - style you can find in pages.scss -->
         <!-- ============================================================== -->
         <?php include_once 'z_menu.php' ?>
         <!-- ============================================================== -->
         <!-- End Left Sidebar - style you can find in sidebar.scss  -->
         <!-- ============================================================== -->
         <!-- ============================================================== -->
         <!-- Page wrapper  -->
         <!-- ============================================================== -->
         <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
               <div class="row">
                  <div class="col-12 d-flex no-block align-items-center">
                     <h4 class="page-title">Settings</h4>
                  </div>
               </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs" role="tablist">
                     <li class="nav-item tab-header"> <a class="nav-link active" data-toggle="tab" href="#user_settings" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">User Settings</span></a> </li>
                     <li class="nav-item tab-header"> <a class="nav-link" data-toggle="tab" href="#sniperphish_settings" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">SniperPhish Settings</span></a> </li>
                     <li class="nav-item tab-header"> <a class="nav-link" data-toggle="tab" href="#account_info" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Account Info</span></a> </li>
                     <li class="nav-item tab-header"> <a class="nav-link" data-toggle="tab" href="#about" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">About</span></a> </li>
                  </ul>
                  <!-- Tab panes -->
                  <div class="tab-content tabcontent-border">
                     <div class="tab-pane active" id="user_settings" role="tabpanel">
                        <div class="col-md-12">
                           <form class="form-horizontal">
                              <div class="card-body">
                                 <!--<h4 class="card-title">Edit Account Info</h4>-->
                                 <div class="form-group row">
                                    <label for="report_timezoneSelector" class="col-md-2 text-left control-label col-form-label">Display Timezone:</label>
                                    <div class="col-md-6">
                                       <select class="select2 form-control custom-select" id="report_timezoneSelector" style="height: 36px;width: 100%;">
                                       </select>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="report_time_format" class="col-md-2 text-left control-label col-form-label">Display Time Format:</label>
                                    <div class="col-md-4">
                                       <select class="select2 form-control custom-select" onchange="timeSelected()" id="report_date_format" style="height: 36px;width: 100%;">
                                       </select>
                                       <div class="valid-feedback" id="lb_report_time_format"></div>
                                    </div>
                                    <div class="col-md-2">
                                       <select class="select2 form-control custom-select" onchange="timeSelected()" id="report_space_format" style="height: 36px;width: 100%;">
                                          <option value="space">(Space)</option>
                                          <option value="comma">,(Comma)</option>
                                          <option value="comaspace" selected>, (Comma+Space)</option>
                                       </select>
                                    </div>
                                    <div class="col-md-4">
                                       <select class="select2 form-control custom-select" onchange="timeSelected()" id="report_time_format" style="height: 36px;width: 100%;">
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="border-top">
                                 <div class="card-body">
                                    <button type="button" class="btn btn-info" onclick="modifyUserSettings($(this))"><i class="fa fas fa-save"></i> Save</button>
                                 </div>
                              </div>
                           </form>
                        </div>
                     </div>
                     <div class="tab-pane" id="sniperphish_settings" role="tabpanel">
                        <div class="col-md-12">
                           <form class="form-horizontal">
                              <div class="card-body">
                                 <!--<h4 class="card-title">Edit Account Info</h4>-->
                                 <div class="form-group row">
                                    <label for="sniperphish_timezoneSelector" class="col-md-2 text-left control-label col-form-label">Default Timezone:</label>
                                    <div class="col-md-5">
                                       <select class="select2 form-control custom-select" id="sniperphish_timezoneSelector" style="height: 36px;width: 100%;">
                                       </select>
                                       <div class="invalid-feedback" id="timezone_warning">
                                          <i>Note: changing this value may affect finished/ongoing campaigns</i>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="border-top">
                                 <div class="card-body">
                                    <button type="button" class="btn btn-info" onclick="modifySniperPhishSettings($(this))"><i class="fa fas fa-save"></i> Save</button>
                                 </div>
                              </div>
                           </form>
                        </div>
                     </div>
                     <div class="tab-pane" id="account_info" role="tabpanel">
                        <div class="col-md-6">
                           <form class="form-horizontal">
                              <div class="card-body">
                                 <!--<h4 class="card-title">Edit Account Info</h4>-->
                                 <div class="form-group row">
                                    <label for="setting_field_uname" class="col-sm-3 text-left control-label col-form-label">Username:</label>
                                    <div class="col-sm-9">
                                       <input type="text" class="form-control" id="setting_field_uname" value="Admin" disabled>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="setting_field_mail" class="col-sm-3 text-left control-label col-form-label">Email:</label>
                                    <div class="col-sm-9">
                                       <input type="text" class="form-control" id="setting_field_mail">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="setting_field_old_pwd" class="col-sm-3 text-left control-label col-form-label">Current Password:</label>
                                    <div class="col-sm-9">
                                       <input type="password" class="form-control" id="setting_field_old_pwd" placeholder="Current Password Here">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="setting_field_new_pwd" class="col-sm-3 text-left control-label col-form-label">New Password:</label>
                                    <div class="col-sm-9">
                                       <input type="password" class="form-control" id="setting_field_new_pwd" placeholder="New Password Here">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="setting_field_confirm_pwd" class="col-sm-3 text-left control-label col-form-label">Confirm Password:</label>
                                    <div class="col-sm-9">
                                       <input type="password" class="form-control" id="setting_field_confirm_pwd" placeholder="Confirm Password Here">
                                    </div>
                                 </div>
                              </div>
                              <div class="border-top">
                                 <div class="card-body">
                                    <button type="button" class="btn btn-info" onclick="modifyAccount($(this))"><i class="fa fas fa-save"></i> Save</button>
                                 </div>
                              </div>
                           </form>
                        </div>
                     </div>
                     <div class="tab-pane" id="about" role="tabpanel">
                        <center>
                        <br/>
                        <br/>
                        <div class="p-20">
                           <a class="navbar-brand" href="#">
                              <!-- Logo icon -->
                              <b class="logo-icon p-l-10">
                              <img src="images/logo-icon.png" alt="homepage" class="light-logo" />
                              </b>
                              <span class="logo-text">
                              <img src="images/logo-text.png" alt="homepage" class="light-logo" />
                              </span>
                           </a>
                           <h5>Version 0.4.1 beta</h5>
                           <h5>The Web-Email Spear Phishing Toolkit</h5>
                           <p>Developed by Gem George</p>
                           <br/>
                           <br/>
                           <br/>
                           <br/>
                        </div>
                        <center>
                     </div>
                  </div>
               </div>
               <!-- ============================================================== -->
               <!-- End PAge Content -->
               <!-- ============================================================== -->
               <!-- ============================================================== -->
               <!-- Right sidebar -->
               <!-- ============================================================== -->
               <!-- .right-sidebar -->
               <!-- ============================================================== -->
               <!-- End Right sidebar -->
               <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <?php include_once 'z_footer.php' ?>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
         </div>
         <!-- ============================================================== -->
         <!-- End Page wrapper  -->
         <!-- ============================================================== -->
      </div>
      <!-- ============================================================== -->
      <!-- End Wrapper -->
      <!-- ============================================================== -->
      <!-- ============================================================== -->
      <!-- All Jquery -->
      <!-- ============================================================== -->
      <script src="js/libs/jquery/jquery-3.5.1.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script>
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <!-- slimscrollbar scrollbar JavaScript -->
      <script src="js/libs/sparkline.js"></script>
      <!--Wave Effects -->
      <script src="js/libs/waves.js"></script>
      <!--Menu sidebar -->
      <script src="js/libs/sidebarmenu.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="js/libs/toastr.min.js"></script>
      <script src="js/libs/select2.min.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/account_settings.js"></script>
   </body>
</html>