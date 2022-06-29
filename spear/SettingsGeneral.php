<?php
   require_once(dirname(__FILE__) . '/manager/session_manager.php');
   isSessionValid(true);
?>
<!DOCTYPE html>
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
                     <h4 class="page-title">SniperPhish General Settings</h4>
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
                  <div class="card-body">
                        <div class="form-group row">
                           <div class="col-md-12">
                              <h6 class="hbar">Timezone & Time Format</h6> 
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-12">
                              <i class="small">The date and time of campaign results displayed will change according to timezone and time format set.</i> 
                           </div>
                           <label for="selector_timezone" class="col-md-2 text-left control-label col-form-label">Display Timezone:</label>
                           <div class="col-md-5">
                              <select class="select2 form-control custom-select" id="selector_timezone" style="height: 36px;width: 100%;">
                              </select>
                           </div>
                           <div class="col-md-5 text-right">
                               <button type="button" class="btn btn-info" onclick="modifyTimeStampSettings($(this))"><i class="fa fas fa-save"></i> Save</button>
                           </div>
                        </div>  
                        <div class="form-group row">
                           <label for="report_selector_time_format" class="col-md-2 text-left control-label col-form-label">Display Time Format:</label>
                           <div class="col-md-3">
                              <select class="select2 form-control custom-select" id="selector_date_format" style="height: 36px;width: 100%;">
                              </select>
                              <div class="valid-feedback" id="lb_selector_time_format"></div>
                           </div>
                           <div class="col-md-2">
                              <select class="select2 form-control custom-select" id="selector_space_format" style="height: 36px;width: 100%;">
                                 <option value="space">(Space)</option>
                                 <option value="comma">,(Comma)</option>
                                 <option value="comaspace" selected>, (Comma+Space)</option>
                              </select>
                           </div>
                           <div class="col-md-2">
                              <select class="select2 form-control custom-select" id="selector_time_format" style="height: 36px;width: 100%;">
                              </select>
                           </div>
                        </div>

                        <div class="form-group row">
                           <div class="col-md-12">
                              <h6 class="hbar">SniperPhish Primary URL</h6> 
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-12">
                              <i class="small">This will act as the primary URL to receive webhooks in all trackers. This should be reachable to target users.</i> 
                           </div> 
                           <label for="selector_timezone" class="col-md-2 text-left control-label col-form-label">SniperPhish base URL:</label>
                           <div class="col-md-5 text-left">
                              <input type="text" class="form-control" id="tb_sp_url"> 
                           </div>
                           <div class="col-md-2 text-left">
                              <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" onclick="$('#tb_sp_url').val(location.origin);toastr.success('', 'Generated successfully!');" title="Generate URL based on current domain"><i class="fas fa-sync"></i></button>
                           </div>
                           <div class="col-md-3 text-right">
                               <button type="button" class="btn btn-info" onclick="modifySPBaseURL($(this))"><i class="fa fas fa-save"></i> Save</button>
                           </div>
                        </div> 

                        <div class="form-group row">
                           <div class="col-md-12">
                              <h6 class="hbar">Junk Data</h6> 
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-12">
                                 <i class="small">This will clear junk files and orphaned records.</i> 
                              </div> 
                           <label for="selector_timezone" class="col-md-2 text-left control-label col-form-label">Clear junk files and data:</label>
                           <div class="col-md-5 text-left">
                               <button type="button" class="btn btn-success" onclick="clearJunkSPData($(this))" title="" data-toggle="tooltip" data-original-title="Clear junk data"><i class="fa fas fa-recycle"></i></button>
                           </div>
                        </div>  
                     <hr/>
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
      <script src="js/libs/jquery/jquery-3.6.0.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script>
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <script src="js/libs/select2.min.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/settings_general.js"></script>      
      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
   </body>
</html>