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
      <!-- Custom CSS -->
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
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
                     <!--<h4 class="page-title">Home</h4> -->
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
               <!-- Sales Cards  -->
               <!-- ============================================================== -->
               <div class="row">
                  <div class="col-sm-12 col-md-4 col-lg-4">
                     <div class="bg-dark card card-hover">
                        <div class="card-body">
                           <div class="d-flex">
                              <div class="mr-3 align-self-center text-white"><i class="mdi mdi-email mdi-36px"></i></div>
                              <div class="align-self-center">
                                 <h7 class="text-white mt-2 mb-0">Mail Campaign</h7>
                                 <h5 class="mt-0 text-white" id="lb_mailcamp">Total: 0, Active: 0</h5>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-sm-12 col-md-4 col-lg-4">
                     <div class="bg-dark card card-hover">
                        <div class="card-body">
                           <div class="d-flex">
                              <div class="mr-3 align-self-center text-white"><i class="mdi mdi-web mdi-36px"></i></div>
                              <div class="align-self-center">
                                 <h7 class="text-white mt-2 mb-0">Web Trackers</h7>
                                 <h5 class="mt-0 text-white" id="lb_webtracker">Total: 0, Active: 0</h5>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-sm-12 col-md-4 col-lg-4">
                     <div class="bg-dark card card-hover">
                        <div class="card-body">
                           <div class="d-flex">
                              <div class="mr-3 align-self-center text-white"><i class="mdi mdi-watch-vibrate mdi-36px"></i></div>
                              <div class="align-self-center">
                                 <h7 class="text-white mt-2 mb-0">Simple Trackers</h7>
                                 <h5 class="mt-0 text-white" id="lb_simpletracker">Total: 0, Active: 0</h5>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="card">
                        <div class="card-body">
                           <h5 class="card-title">Campaigns</h5>
                           <div id="graph_overview" style="height: 400px;"></div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="card">
                        <div class="card-body">
                           <h5 class="card-title">Campaigns Timeline</h5>
                           <div id="graph_timeline_all" style="height: 400px;"></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="ModalTracker" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true ">
               <div class="modal-dialog" role="document ">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Select Tracker</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true ">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <div class="table-responsive">
                              <table id="Modal_table_trackers" class="table table-striped table-bordered">
                                 <thead>
                                    <tr>
                                       <th>#</th>
                                       <th>ID</th>
                                       <th>Tracker Name</th>
                                       <th>Date Created</th>
                                       <th>Action</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                 </tbody>
                              </table>
                           </div>
                           <div class="ml-auto text-right">
                              <button type="button" class="btn btn-info" data-toggle="modal" data-target="#ModalTracker" onclick="templateSelected('','All active web trackers')"><i class="mdi mdi-auto-fix"></i> Select All trackers</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <!-- Modal -->
            <div class="modal fade" id="ModalEmailTracker" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true ">
               <div class="modal-dialog" role="document ">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Select Email Tracker</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true ">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <div class="table-responsive">
                              <table id="Modal_email_table_trackers" class="table table-striped table-bordered">
                                 <thead>
                                    <tr>
                                       <th>#</th>
                                       <th>ID</th>
                                       <th>Tracker Name</th>
                                       <th>Date Created</th>
                                       <th>Action</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                 </tbody>
                              </table>
                           </div>
                           <div class="ml-auto text-right">
                              <button type="button" class="btn btn-info" data-toggle="modal" data-target="#ModalTracker" onclick="emailTemplateSelected ('','All Active Email Trackers')"><i class="mdi mdi-auto-fix"></i> Select All trackers</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
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
      <script src="js/libs/sparkline.js"></script>
      <!--Wave Effects -->
      <script src="js/libs/waves.js"></script>
      <!--Menu sidebar -->
      <script src="js/libs/sidebarmenu.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!--This page JavaScript -->
      <!-- Charts js Files -->
      <script src="js/libs/jquery/jquery.flot.js"></script>
      <script src="js/libs/toastr.min.js"></script>
      <script src="js/libs/jquery/jquery.flot.tooltip.min.js"></script>
      <script src="js/libs/jquery/datatables.js"></script>
      <script src="js/libs/apexcharts.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/home_functions.js"></script>
   </body>
</html>