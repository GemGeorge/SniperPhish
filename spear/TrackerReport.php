<?php
   require_once(dirname(__FILE__) . '/session_manager.php');
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
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
      <style> 
         .tab-header{ list-style-type: none; }
      </style>
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
            <div class="page-breadcrumb breadcrumb-withbutton">
               <div class="row">
                  <div class="col-12 d-flex no-block align-items-center">
                     <h4 class="page-title">Web Tracker Reports</h4>
                     <div class="ml-auto text-right">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#ModalTracker"><i class="mdi mdi-auto-fix"></i> Select Tracker</button>
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
               <div class="row">
                  <div class="col-12">
                     <div class="card">
                        <div class="card-body">
                           <div class="row">
                              <div class="align-items-left col-12 d-flex no-block">
                                 <div class="col-md-5 row">
                                    <span><strong>Tracker: </strong></span><span id="disp_web_tracker_name">NA</span>
                                 </div>
                                 <div class="col-md-4" id="disp_tracker_status">                                       
                                 </div>
                                 <div class="align-items-right ml-auto">
                                    <div>
                                       <span><strong>Start: </strong></span><span id="disp_tracker_start">NA</span>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-12">
                     <div class="card">
                        <div class="card-body">
                           <div class="row">
                              <div class="form-group align-items-left col-12 d-flex no-block ">
                                 <div class="col-md-2" style="height:100%;">
                                    <select class="select2 form-control custom-select" id="reportTypeSelector" style="width: 100%; height:36px;">
                                    </select>
                                 </div>
                                 <div class="col-md-0">
                                    <label class="m-t-10 m-l-10"> Colums:</label>
                                 </div>
                                 <div class="col-md-7">
                                    <select class="select2 form-control m-t-15" multiple="multiple" style="height: 36px;width: 100%;" id="tb_report_colums_list">
                                       
                                    </select>
                                 </div>
                                 <div class="col-md-1">
                                    <button type="button" class="btn btn-success mdi mdi-reload " data-toggle="tooltip" data-placement="top" title="Refresh table" onclick="loadTableWebTrackerResult(g_tracker_id)"></button>
                                 </div>
                                 <div class="align-items-right ml-auto">                                  
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#ModalExport"><i class=" mdi mdi-file-export"></i> Export</button>
                                 </div>
                              </div>
                           </div>
                           <div class="form-group row">
                              <div class="table-responsive">
                                 <table id="table_tracker_report" class="table table-striped table-bordered">
                                    <thead>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                 </table>
                              </div>
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
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <!-- Modal -->
            <!-- Modal -->
            <div class="modal fade" id="ModalTracker" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true ">
               <div class="modal-dialog modal-large" role="document ">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Select Tracker</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <div class="table-responsive">
                              <table id="Modal_table_tracker_list" class="table table-striped table-bordered">
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
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="ModalExport" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Export Report</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label for="Modal_export_file_name" class="col-sm-3 text-left control-label col-form-label">File Name: </label>
                           <div class="col-sm-9 custom-control">
                              <input type="text" class="form-control" id="Modal_export_file_name">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="modal_export_report_selector" class="col-sm-3 text-left control-label col-form-label">File Format: </label>
                           <div class="col-sm-9 custom-control">
                              <select class="select2 form-control"  style="height: 36px;width: 100%;" id="modal_export_report_selector">
                                 <option value="csv">Export as CSV</option>
                                 <option value="excel">Export as XLS</option>
                                 <option value="pdf">Export as PDF</option>
                              </select>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="$('.buttons-' + $('#modal_export_report_selector').val()).click()" data-dismiss="modal"><i class=" mdi mdi-file-export"></i> Export</button>
                     </div>
                  </div>
               </div>
            </div>
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
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/jquery/jquery-ui.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script>
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="js/libs/jquery/datatables.js"></script>
      <script src="js/libs/select2.min.js"></script>
      <script src="js/common_scripts.js"></script>  
      <script src="js/web_tracker_report_functions.js"></script>
      <script>
         <?php 
            if(isset($_GET['tracker']))
               echo ('webTrackerSelected("'.doFilter($_GET['tracker'],'ALPHA_NUM').'")');  
            else
               echo '$(function() {$("#ModalTracker").modal("toggle"); });';         
         ?>     
      </script>  
      <script defer src="js/libs/moment.min.js"></script>
      <script defer src="js/libs/moment-timezone-with-data.min.js"></script>
      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
      <script defer src="js/libs/jquery/dataTables.buttons.min.js"></script>
      <script defer src="js/libs/jquery/buttons.html5.min.js"></script>
      <script defer src="js/libs/pdfmake.min.js"></script>  
      <script defer src="js/libs/vfs_fonts.js"></script>  
      <script defer src="js/libs/jszip.min.js"></script>     
   </body>
</html>