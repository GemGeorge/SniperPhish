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
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
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
                     <h4 class="page-title">SniperPhish Log</h4>
                     <div class="ml-auto text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fas fa-cog"></i></button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_prompts">Clear Log</a>
                              <a class="dropdown-item" href="#" data-toggle="modal" data-target="#ModalExport">Export Log</a>
                            </div>
                        </div>
                     </div>
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
                     <div class="row">
                        <div class="col-md-12 m-t-20">
                            <div class="row">
                              <div class="table-responsive">
                                 <table id="table_log" class="table table-striped table-bordered">
                                    <thead>
                                       <tr>
                                          <th>#</th>
                                          <th>Username</th>
                                          <th>Log</th>
                                          <th>IP</th>
                                          <th>Date Time</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
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
            <!-- Modal -->
            <div class="modal fade" id="modal_prompts" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        This will delete all logs and the action can't be undone!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="clearLogAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>            
            <!-- Modal -->
            <div class="modal fade" id="ModalExport" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Export Log</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label for="Modal_export_file_name" class="col-sm-3 text-left control-label col-form-label">File Format: </label>
                           <div class="col-sm-9 custom-control">
                              <select class="select2 form-control"  style="height: 36px;width: 100%;" id="modal_export_log_selector">
                                 <option value="csv">Export as CSV</option>
                                 <option value="pdf">Export as PDF</option>
                                 <option value="html">Export as HTML</option>
                              </select>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="exportLogAction($(this))"><i class="fas fa-file-export"></i> Export</button>
                     </div>
                  </div>
               </div>
            </div>
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
      <script src="js/libs/jquery/datatables.js"></script>  
      <script src="js/common_scripts.js"></script>
      <script src="js/settings_log.js"></script>
      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
   </body>
</html>