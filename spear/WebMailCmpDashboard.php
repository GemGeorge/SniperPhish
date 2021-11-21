<?php
   require_once(dirname(__FILE__) . '/session_manager.php');
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
      <link rel="stylesheet" type="text/css" href="css/prism.css"/>
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
      <link rel="stylesheet" type="text/css" href="css/summernote-lite.min.css">  
      <style type="text/css">
         .note-editable { background-color: white !important; } /*Disabled background colour*/
      </style> 
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
                     <h4 class="page-title">Web-Email Campaign Dashboard</h4>
                     <div class="ml-auto text-right">
                        <button type="button" class="btn btn-info btn-sm item_private" data-toggle="modal" data-target="#ModalCampaignList"><i class="mdi mdi-hand-pointing-right" title="Select web & mail campaigns" data-toggle="tooltip" data-placement="bottom"></i> Select Campaign</button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-sm" onclick="refreshDashboard()" title="Refresh dashboard" data-toggle="tooltip" data-placement="bottom"><i class="mdi mdi-refresh"></i></button>
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_settings">Display Settings</a>
                                <a class="dropdown-item item_private" href="#" data-toggle="modal" data-target="#modal_dashboard_link">Get Dashboard Link</a>
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
                              <div class="align-items-left col-12 d-flex no-block">
                                 <div class="row col-md-4">
                                    <div class="panel-group box bg-dark text-white accordion">
                                       <div class="panel panel-default">
                                         <div class="panel-heading card-hover">
                                             <span class="panel-title">
                                                <strong>Mail Campaign: </strong><span id="disp_camp_name">NA
                                             <span>
                                         </div>
                                         <div id="collapseOne" class="panel-collapse collapse table-dark row" data-toggle="collapse" aria-expanded="false">
                                             <div class="panel-body">
                                                <div class="table-responsive">
                                                   <table class="table table-full-width" id="table_campaign_info">
                                                      <tbody>
                                                         <tr>
                                                            <td>Campaign ID:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Created:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>User Group:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Mail Template:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Mail Sender:</td>
                                                            <td>-</td>
                                                         </tr>
                                                      </tbody>
                                                   </table>
                                                </div>
                                             </div>
                                         </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4 text-center m-t-5 m-l-20" id="disp_camp_status">                                       
                                 </div>
                                 <div class="row col-md-4 ml-auto">
                                    <div class="panel-group box bg-dark text-white accordion ml-auto">
                                       <div class="panel panel-default">
                                         <div class="panel-heading card-hover">
                                             <span class="panel-title" data-toggle="collapse">
                                                <strong>Web Tracker: </strong><span id="disp_web_tracker_name">NA
                                             <span>
                                         </div>
                                         <div id="collapseOne" class="panel-collapse collapse table-dark row">
                                             <div class="panel-body">
                                                <div class="table-responsive">
                                                   <table class="table table-full-width" id="table_web_tracker_info">
                                                      <tbody>
                                                         <tr>
                                                            <td>Tracker ID:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Created:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Track Page Visit:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Track Form Submission:</td>
                                                            <td>-</td>
                                                         </tr>
                                                         <tr>
                                                            <td>Total Pages:</td>
                                                            <td>-</td>
                                                         </tr>
                                                      </tbody>
                                                   </table>
                                                </div>
                                             </div>
                                         </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="progress m-t-15" title="Mail sending status" data-toggle="tooltip" data-placement="top" id="progressbar_status" style="height:20px; background-color:#ccccff;">
                              <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                           </div>
                           <div class="align-items-left col-12 d-flex no-block m-t-10">
                              <span><strong>Start: </strong></span><span id="disp_camp_start">NA</span>
                              <div class="align-items-right ml-auto">
                                 <span><strong>End: </strong></span><span id="disp_camp_end">NA</span>
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
                              <div class="col-md-3 m-t-30">
                                 <h5 class="card-title text-center"><span>Email Overview</span></h5>
                                 <div id="radialchart_overview_mailcamp" ></div>
                              </div>
                              <div class="col-md-6">
                                 <div class="row">
                                    <div class="col-md-4">
                                       <h5 class="card-title text-center"><span>Email Sent</span></h5>
                                       <div id="piechart_mail_total_sent" ></div>
                                    </div>
                                    <div class="col-md-4">
                                       <h5 class="card-title text-center"><span>Email Opened</span></h5>
                                       <div id="piechart_mail_total_mail_open" ></div>
                                    </div>
                                    <div class="col-md-4">
                                       <h5 class="card-title text-center"><span>Email Replied</span></h5>
                                       <div id="piechart_mail_total_replied" class="center"></div>
                                    </div>
                                 </div>
                                 <div class="row m-t-30">
                                    <div class="col-md-4">
                                       <h5 class="card-title text-center"><span>Page Visit</span></h5>
                                       <div id="piechart_total_pv" ></div>
                                    </div>
                                    <div class="col-md-4">
                                       <h5 class="card-title text-center"><span>Form Submission</span></h5>
                                       <div id="piechart_total_fs" ></div>
                                    </div>
                                    <div class="col-md-4">
                                       <h5 class="card-title text-center"><span>Suspect Entry</span></h5>
                                       <div id="piechart_total_suspect" ></div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 m-t-30">
                                 <h5 class="card-title text-center"><span>Web Overview</span></h5>
                                 <div id="radialchart_overview_webcamp" ></div>
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
                              <div class="col-md-7">
                                 <h5 class="card-title text-right m-r-20 m-t-10"><span>Campaign Result</span></h5>
                              </div>
                              <div class="align-items-right ml-auto row">                                  
                                 <button type="button" class="btn btn-success item_private" data-toggle="modal" data-target="#ModalExport"><i class="m-r-10 mdi mdi-file-export"></i> Export</button>
                              </div>
                           </div>
                           <div class="form-group row">
                              <div class="table-responsive">
                                 <table id="table_campaign_result" class="table table-striped table-bordered">
                                    <thead>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                           <div class="form-group">
                              <div class="col-md-12 row">
                                 <div class="col-md-2 ccl-1">
                                     Mail campaign info
                                 </div>
                                 <div class="col-md-2 ccl-3">
                                     Web page info
                                 </div>
                                 <div class="col-md-2 ccl-4">
                                     Form submission info
                                 </div> 
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="ModalCampaignList" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog modal-large" role="document ">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Select Campaign</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <div class="col-md-6">
                              <label for="modal_mailcamp_selector" class="control-label col-form-label">Mail Campaign:</label>
                              <select class="select2 form-control custom-select" id="modal_mailcamp_selector" style="width: 100%; height:36px;">
                                 <option></option>
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label for="modal_web_tracker_selector" class="control-label col-form-label">Web Tracker:</label>
                              <select class="select2 form-control custom-select" id="modal_web_tracker_selector" style="width: 100%; height:36px;">
                                 <option></option>
                              </select>
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-6">
                              <div class="table-responsive">
                                 <table class="table" id="modal_table_campaign_info">
                                    <tbody>
                                       <tr>
                                          <td>Campaign ID:</td>
                                          <td>-</td>
                                       </tr>
                                       <tr>
                                          <td>Campaign Name:</td>
                                          <td>-</td>
                                       </tr>
                                       <tr>
                                          <td>Created:</td>
                                          <td>-</td>
                                       </tr>
                                       <tr>
                                          <td>Start/Scheduled time:</td>
                                          <td>-</td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="table-responsive">
                                 <table class="table" id="modal_table_webtracker_info">
                                    <tbody>
                                       <tr>
                                          <td>Tracker ID:</td>
                                          <td>-</td>
                                       </tr>
                                       <tr>
                                          <td>Tracker Name:</td>
                                          <td>-</td>
                                       </tr>
                                       <tr>
                                          <td>Created:</td>
                                          <td>-</td>
                                       </tr>
                                       <tr>
                                          <td>Start/Scheduled time:</td>
                                          <td>-</td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-info" onclick="campaignSelectedValidation()">Select</button>
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
            <!-- Modal -->
            <div class="modal fade" id="modal_reply_mails" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog modal-large" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Reply Emails</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
               <div class="modal-dialog modal-large-x" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Dashboard Display Settings</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label class="col-md-2">Table data:</label>
                           <div class="col-md-5">
                              <div class="custom-control custom-radio">
                                 <input type="radio" class="custom-control-input" id="customControlValidation1" value="radio_table_data_single" name="radio_table_data" required checked>
                                 <label class="custom-control-label" for="customControlValidation1">Show first entry only</label>
                                 <i class="mdi mdi-information cursor-pointer" tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="First tracked data of users are displayed. Eg: displays user's first visit only"></i>
                              </div>
                           </div>
                           <div class="col-md-5">
                              <div class="custom-control custom-radio">
                                 <input type="radio" class="custom-control-input" id="customControlValidation2" value="radio_table_data_all" name="radio_table_data" required>
                                 <label class="custom-control-label" for="customControlValidation2">Show all entries</label>
                                 <i class="mdi mdi-information cursor-pointer" tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="All tracked data of users are displayed. Eg: displays all visits of a user"></i>
                              </div> 
                           </div>                           
                        </div>
                        <hr/>
                        <div class="form-group row m-t-30">
                           <div class="col-md-2">
                              <h6>Table colums</h6>
                              <hr/>
                           </div>
                           <div class="col-md-12 row">
                              <label for="tb_camp_result_colums_list_mcamp" class="col-md-2">Mail campaign:</label>
                              <select class="select2 col-md-10" style="width: 83%;"  multiple="multiple"  id="tb_camp_result_colums_list_mcamp">
                                 <optgroup label="User Info">
                                    <option value="id" selected>CID</option>
                                    <option value="user_name" selected>Name</option>
                                    <option value="user_email" selected>Email</option>
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
                                 </optgroup>
                                 <optgroup label="User/Mail Server IP Info">
                                    <option value="country">Country (mail)</option>
                                    <option value="city">City (mail)</option>
                                    <option value="zip">Zip (mail)</option>
                                    <option value="isp">ISP (mail)</option>
                                    <option value="timezone">Timezone (mail)</option>
                                    <option value="coordinates">Coordinates (mail)</option>
                                 </optgroup>
                              </select>                             
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-12 row">
                              <label for="tb_camp_result_colums_list_wcm" class="col-md-2">Web common:</label>
                              <select class="select2 form-control" style="width: 83%;" multiple="multiple"  id="tb_camp_result_colums_list_wcm">
                                 <optgroup label="User Info">
                                    <option value="wcm_cid">Client ID</option>
                                    <option value="wcm_session_id">Session ID</option>
                                    <option value="wcm_public_ip" selected>Public IP</option>
                                    <option value="wcm_browser" selected>Browser</option>
                                    <option value="wcm_platform" selected>Platform</option>
                                    <option value="wcm_screen_res">Screen Res</option>
                                    <option value="wcm_device_type">Device Type</option>
                                    <option value="wcm_user_agent">User Agent</option>   
                                 </optgroup>
                                 <optgroup label="User IP Info">
                                    <option value="wcm_country" selected>Country</option>
                                    <option value="wcm_city">City</option>
                                    <option value="wcm_zip">Zip</option>
                                    <option value="wcm_isp">ISP</option>
                                    <option value="wcm_timezone">Timezone</option>
                                    <option value="wcm_coordinates">Coordinates</option>
                                 </optgroup>                              
                              </select>
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-12 row">
                              <label for="tb_camp_result_colums_list_wpv" class="col-md-2">Page visit:</label>
                              <select class="select2 form-control" style="width: 83%;" multiple="multiple"  id="tb_camp_result_colums_list_wpv">
                                 <option value="wpv_activity" selected>Page Visit</option>
                                 <option value="wpv_visit_count">Visit Count</option>
                                 <option value="wpv_first_visit">First Visit</option>
                                 <option value="wpv_last_visit">Last Visit</option>
                                 <option value="wpv_visit_times">Visit Times</option>                                 
                              </select>
                           </div>
                        </div>
                        <div class="form-group row">
                           <div class="col-md-12 row">
                              <label for="tb_camp_result_colums_list_wfs" class="col-md-2">Form submission:</label>
                              <select class="select2 form-control" style="width: 83%;" multiple="multiple"  id="tb_camp_result_colums_list_wfs">
                              </select>
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
            <div class="modal fade" id="modal_dashboard_link" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog modal-large" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Dashboard Access Link</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label for="Modal_export_file_name" class="col-sm-4 text-left control-label col-form-label">Public access: </label>
                           <div class="custom-control custom-switch col-sm-2 m-t-5 text-right">
                              <label class="switch">
                                 <input type="checkbox" id="cb_act_dashboard_link">
                                 <span class="slider round"></span>
                              </label>
                           </div>
                        </div>
                        <label for="Modal_export_file_name" class=" text-left control-label col-form-label">Shareable  dashboard link (public):</label>
                        <pre><code class="language-html" id="dashboard_link_url">Error: Please select campaign first</code></pre>
                        <span class="prism_side float-right">
                           <button type="button" id="btn_copy_quick_tracker" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-content-copy" data-toggle="tooltip" title="Copy Link" data-placement="bottom"></button><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-reload" data-toggle="tooltip" title="Regenerate Link" data-placement="bottom" onclick="enableDisablePublicAccess(true)"></button>
                        </span>
                     </div>
                     <div class="modal-footer col-md-12">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_dashboard_link">Close</button>  
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
      <script src="js/libs/jquery/jquery-3.6.0.min.js"></script> 
      <script src="js/libs/jquery/jquery-ui.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script>
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!--This page JavaScript -->
      <script src="js/libs/clipboard.min.js"></script>  
      <script src="js/common_scripts.js"></script>
      <script src="js/libs/jquery/datatables.js"></script>   
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/libs/apexcharts.js"></script>
      <script src="js/libs/select2.min.js"></script>   
      <script src="js/web_mail_campaign_dashboard.js"></script>
      
      <?php
         if(isset($_GET['tk']) && isset($_GET['mcamp']) && isset($_GET['tracker']) && amIPublic($_GET['tk'],$_GET['mcamp'],$_GET['tracker']) == true)
            echo '<script>
                     var g_tk_id = "'.$_GET['tk'].'"; 
                     hideMeFromPublic(); 
                  </script>';
         else{
             echo '<script>var g_tk_id = getRandomId();</script>
               <script defer src="js/libs/jquery/dataTables.buttons.min.js"></script>
               <script defer src="js/libs/jquery/buttons.html5.min.js"></script>
               <script defer src="js/libs/pdfmake.min.js"></script>
               <script defer src="js/libs/vfs_fonts.js"></script>    
               <script defer src="js/libs/jszip.min.js"></script>';            
            isSessionValid(true);
         }
      ?>   
      <?php
         echo '<script>';

         if(isset($_GET['mcamp']) && isset($_GET['tracker']))
            echo 'var g_campaign_id ="'.doFilter($_GET['mcamp'],'ALPHA_NUM').'", g_tracker_id="'.doFilter($_GET['tracker'],'ALPHA_NUM').'";
                  loadTableCampaignList("' . doFilter($_GET['mcamp'],'ALPHA_NUM') . '","' . doFilter($_GET['tracker'],'ALPHA_NUM') . '");';
         else
            echo 'var g_campaign_id ="", g_tracker_id="";
                  loadTableCampaignList("",""); 
                  $(function() { $("#ModalCampaignList").modal("toggle"); });';
         echo '</script>';
      ?>

      <script defer src="js/libs/summernote-bs4.min.js"></script>  
      <script defer src="js/libs/toastr.min.js"></script>
      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/prism.js"></script>  
   </body>
</html>