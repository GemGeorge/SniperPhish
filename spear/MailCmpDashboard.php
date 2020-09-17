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
      <link rel="stylesheet" type="text/css" href="css/select2.min.css">
      <!-- Custom CSS -->
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
      <style type="text/css">
         .note-editable { background-color: white !important; } /*Disabled background colour*/
      </style>
      <!-- include summernote css/js -->
      <link rel="stylesheet" type="text/css" href="css/summernote.css">       
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
                     <h4 class="page-title">Email Campaign Dashboard</h4>
                     <div class="ml-auto text-right">
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#ModalCampaignList"><i class="mdi mdi-hand-pointing-right" title="Select mail campaign" data-toggle="tooltip" data-placement="bottom"></i> Select Campaign</button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-info" onclick="refreshDashboard()" title="Refresh dashboard" data-toggle="tooltip" data-placement="bottom"><i class="mdi mdi-refresh"></i></button>
                            <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_settings">Settings</a>
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
               <!-- Sales Cards  -->
               <!-- ============================================================== -->
               <div class="row">
                    <div class="col-12">
                        <div class="card">
                              <div class="card-body">
                                 <div class="row">
                                    <div class="align-items-left col-12 d-flex no-block row">
                                       <div class="col-md-4">
                                          <span><strong>Campaign: </strong></span><span id="disp_camp_name">NA</span>
                                       </div>  
                                       <div class="col-md-4 text-center m-l-5" id="disp_camp_status">                            
                                       </div>  
                                       <div class="align-items-right ml-auto row">                                  
                                          <div>
                                             <span><strong>Start: </strong></span><span id="disp_camp_start">NA</span>
                                          </div> 
                                       </div>
                                    </div>                                    
                                 </div>
                                 <div class="progress m-t-15" title="Sending status" data-toggle="tooltip" data-placement="top" id="progressbar_status" style="height:20px; background-color:#ccccff;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                                 </div>
                              </div>
                        </div>
                    </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="card">
                        <div class="card-body">
                           <h5 class="card-title "><span>Campaign Timeline</span></h5>
                           <div id="chart_live_mailcamp">                           
                              <apexchart type="scatter" height="350"/>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">   
                     <div class="card">
                        <div class="card-body">     
                           <div class="align-items-left col-12 d-flex no-block">             
                              <div class="col-md-3">                             
                                 <h5 class="card-title text-center"><span>Email Overview</span></h5> 
                                 <div id="radialchart_overview_mailcamp" ></div>
                              </div>
                                 
                              <div class="col-md-3">    
                                 <h5 class="card-title text-center"><span>Email Sent</span></h5>
                                 <div id="piechart_mail_total_sent" ></div>
                              </div>
                              <div class="col-md-3">
                                 <h5 class="card-title text-center"><span>Email Opened</span></h5>
                                 <div id="piechart_mail_total_mail_open" ></div>
                              </div>
                              <div class="col-md-3">                           
                                 <h5 class="card-title text-center"><span>Email Replied</span></h5>
                                 <div id="piechart_mail_total_replied" ></div>
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
                                 <div class="form-group align-items-left col-12 d-flex no-block">
                                    <div class="col-md-0 row">
                                       <label class="m-t-10 m-r-5"> Colums:</label>
                                    </div>  
                                    <div class="col-md-9">  
                                       <select class="select2 form-control m-t-16" style="width: 100%;" multiple="multiple"  id="tb_mailcamp_result_colums_list">
                                       <option value="mailto_user_name" selected>Name</option>
                                       <option value="mailto_user_email" selected>Email</option>
                                       <option value="sending_status" selected>Status</option>
                                       <option value="send_time" selected>Sent Time</option>
                                       <option value="send_error">Send Error</option>
                                       <option value="mail_open" selected>Mail Open</option>
                                       <option value="mail_open_count">Mail(open count)</option>
                                       <option value="mail_first_open">Mail(first open)</option>
                                       <option value="mail_last_open" >Mail(last open)</option>
                                       <option value="mail_open_times">Mail(all open times)</option>
                                       <option value="public_ip">Public IP</option>
                                       <option value="user_agent">User Agent</option>
                                       <option value="mail_client">Mail Client</option>
                                       <option value="platform">Platform</option>
                                       <option value="all_headers">HTTP Headers</option>
                                       <option value="mail_reply">Mail Reply</option>
                                       <option value="mail_reply_count">Mail (reply count)</option>
                                       <option value="mail_reply_content">Mail (reply content)</option>
                                       </select>                                     
                                    </div>  
                                    <div class="col-md-1">
                                       <button type="button" class="btn btn-success mdi mdi-reload " data-toggle="tooltip" data-placement="top" title="Refresh table" onclick="loadTableCampaignResult(data_mail_live)"></button>
                                    </div>
                                    <div class="align-items-right ml-auto">
                                       <div class="row">                                  
                                          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#ModalExport"><i class="m-r-10 mdi mdi-file-export"></i> Export</button>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <div class="table-responsive">
                                       <table id="table_mail_campaign_result" class="table table-striped table-bordered">
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
            </div>
            <!-- Modal -->
            <div class="modal fade" id="ModalCampaignList" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true ">
               <div class="modal-dialog modal-large" role="document ">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Select Email Campaign</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true ">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <div class="table-responsive">
                              <table id="table_mail_campaign_list" class="table table-striped table-bordered">
                                 <thead>
                                    <tr>
                                       <th>#</th>
                                       <th>Tracker Name</th>
                                       <th>Date Created</th>
                                       <th>Status</th>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <div class="col-sm-6">
                              <input type="text" class="form-control m-t-5" id="Modal_export_file_name" placeholder="File Name">
                           </div>
                           <div class="col-sm-5 m-t-5">
                              <select class="select2 form-control"  style="height: 36px;width: 100%;" id="modal_export_report_selector">
                                 <option selected>Export file type</option>
                                 <option id="csv">Export as CSV</option>
                                 <option id="excel">Export as XLS</option>
                                 <option id="pdf">Export as PDF</option>
                              </select>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_reply_mails" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog modal-large" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Reply Emails</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body" id="modal_reply_mails_body" >
                        <ul class="nav nav-tabs" role="tablist">  
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content tabcontent-border">
                           
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_settings" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content" style="width: 120%;">
                     <div class="modal-header">
                        <h5 class="modal-title">Dashboard settings</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label class="col-md-2">Table data:</label>
                           <div class="col-md-4">
                              <div class="custom-control custom-radio">
                                 <input type="radio" class="custom-control-input" id="customControlValidation1" value="radio_table_data_single" name="radio_table_data" required checked>
                                 <label class="custom-control-label" for="customControlValidation1">First entry</label>
                                 <i class="mdi mdi-information cursor-pointer" data-container="body" data-toggle="popover" data-placement="top" data-content="First tracked data of users are displayed. Eg: displays user's first visit only"></i>
                              </div>
                              <div class="custom-control custom-radio">
                                 <input type="radio" class="custom-control-input" id="customControlValidation2" value="radio_table_data_all" name="radio_table_data" required>
                                 <label class="custom-control-label" for="customControlValidation2">All entries</label>
                                 <i class="mdi mdi-information cursor-pointer" data-container="body" data-toggle="popover" data-placement="top" data-content="All tracked data of users are displayed. Eg: displays all visits of a user"></i>
                              </div> 
                           </div>                           
                        </div>                        
                     </div>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-info" onclick="$('#modal_settings').modal('toggle');refreshDashboard();">Apply</button>
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
      <script src="js/libs/jquery/jquery-ui.min.js"></script>
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
      <script src="js/libs/jquery/excanvas.js"></script>
      <script src="js/libs/jquery/jquery.flot.js"></script>
      <script src="js/libs/jquery/jquery.flot.pie.js"></script>
      <script src="js/libs/jquery/jquery.flot.time.js"></script>
      <script src="js/libs/jquery/jquery.flot.stack.js"></script>
      <script src="js/libs/jquery/jquery.flot.crosshair.js"></script>
      <script src="js/libs/toastr.min.js"></script>
      <script src="js/libs/jquery/jquery.flot.tooltip.min.js"></script>
      <script src="js/libs/jquery/datatables.js"></script>
      <script src="js/libs/jquery/dataTables.buttons.min.js"></script>
      <script src="js/libs/jquery/buttons.html5.min.js"></script>
      <script src="js/libs/pdfmake.min.js"></script>    
      <script src="js/libs/vfs_fonts.js"></script>    
      <script src="js/libs/jszip.min.js"></script>      
      <script src="js/libs/select2.min.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/libs/apexcharts.js"></script>
      <script src="js/libs/summernote.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/mail_campaign_dashboard.js"></script>
      <?php
         echo '<script>';
         
         if(isset($_GET['mcamp']))
            echo '$(function() { campaignSelected("' . $_GET['mcamp'] . '"); });';
         
         echo '</script>';
      ?>

   </body>
</html>