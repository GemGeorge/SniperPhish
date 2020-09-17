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
      <link rel="stylesheet" type="text/css" href="css/jquery.steps.css">
      <link rel="stylesheet" type="text/css" href="css/steps.css">
      <link rel="stylesheet" type="text/css" href="css/select2.min.css">
      <link rel="stylesheet" type="text/css" href="css/dropzone.min.css">
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
      <!-- include summernote css/js -->
      <link rel="stylesheet" type="text/css" href="css/summernote.css">
      <style> 
         .tab-header{ list-style-type: none; }
      </style>
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
      <link rel="stylesheet" type="text/css" href="css/prism.css"/>
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
                     <h4 class="page-title">Email Templates</h4>
                  </div>
               </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid" id="section_view_mail_template_list">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-12">
                           <button type="button" class="btn btn-info" onclick="document.location='MailTemplate?action=add&template=new'"><i class="fas fa-plus"></i> New Email Template</button>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12 m-t-20">
                           <div class="row">
                              <div class="table-responsive">
                                 <table id="table_mail_template_list" class="table table-striped table-bordered">
                                    <thead>
                                       <tr>
                                          <th>#</th>
                                          <th>Mail Template Name</th>
                                          <th>Email Subject</th>
                                          <th>Email Body</th>
                                          <th>Attachment</th>
                                          <th>Date Created</th>
                                          <th>Actions</th>
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
            <div class="container-fluid" id="section_add_mail_template">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <!--<h5 class="card-title">Tracker Templates</h5>-->
                     <div class="row">
                        <div class="col-md-9">
                           <div class="form-group row">
                              <label for="mail_template_name" class="col-md-2 text-left control-label col-form-label">Template Name:</label>
                              <div class="col-md-5">
                                 <input type="text" class="form-control" id="mail_template_name" placeholder="Email Template Name">
                              </div>
                              <div class="col-md-2">
                                 <button type="button" class="btn btn-info" onclick="saveMailTemplate($(this))"><i class="fa fas fa-save"> </i> Save </button>
                              </div>
                           </div>
                           <div class="form-group row">
                              <label for="mail_template_subject" class="col-md-2 text-left control-label col-form-label">Email Subject:</label>
                              <div class="col-md-5">
                                 <input type="text" class="form-control" id="mail_template_subject" placeholder="Email Subject">
                              </div>
                              <label for="mail_template_name" class="col-md-2 text-left control-label col-form-label">Message type:</label>
                              <div class="col-md-3 align-items-left row">
                                 <select class="select2 form-control custom-select" id="mail_content_type_selector" style="height: 36px;width: 100%;">
                                    <option value="text/html" selected>Text/HTML</option>
                                    <option value="text/plain">Plain text</option>
                                 </select>
                              </div>
                           </div>
                        </div>                   
                     </div>
                     <div class="row m-t-10">
                        <div class="col-md-12 row">
                           <div class="col-md-9">                             
                              <div id="summernote"></div>                              
                           </div> 
                           
                           <div class="col-md-3"> 
                              <div class="panel-group box bg-dark text-white accordion">
                                 <div class="panel panel-default">
                                   <div class="panel-heading card-hover">
                                        <span class="panel-title">
                                            <span>Keywords</span>
                                        <span>
                                   </div>
                                   <div id="collapseOne" class="panel-collapse collapse table-dark row show" data-toggle="collapse" aria-expanded="false">
                                       <div class="panel-body">
                                          <div class="table-responsive">
                                             <table class="table table-full-width">
                                                <tbody>
                                                   <tr>
                                                      <td>{{CID}}</td>
                                                      <td>Target user's unique ID</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{MID}}</td>
                                                      <td>Mailcampaign ID</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{NAME}}</td>
                                                      <td>Name of target user</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{NOTES}}</td>
                                                      <td>Notes of user</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{EMAIL}}</td>
                                                      <td>Target user email</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{FROM}}</td>
                                                      <td>Sender email address</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{TRACKINGURL}}</td>
                                                      <td>Tracker image URL</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{TRACKER}}</td>
                                                      <td>Tracker image HTML</td>
                                                   </tr>
                                                   <tr>
                                                      <td>{{BASEURL}}</td>
                                                      <td>SniperPhish base URL</td>
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
                     <div class="row">
                        <div class="col-md-12 row">
                           <div class="col-md-9">
                              <div class="form-group row">
                                 <label class="col-md-4 text-left control-label col-form-label m-t-5">
                                    <span id="lb_attachment_count">Attachments (0): </span>
                                 </label> 
                                 <div class="col-md-8 align-items-right text-right float-right m-t-10">
                                     <button type="button" class="btn btn-info btn-sm" onclick='$("#attachment-form").trigger("click")' title="Add attachment" data-toggle="tooltip"><i class="mdi mdi-attachment"></i> Insert</button>
                                  </div>
                              </div>
                              <div class="form-group" id="attachments_area">
                              </div>
                           </div>
                        </div>
                     </div>

                     <form class="dropzone needsclick" id="attachment-form" hidden>
                     </form>

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
            <div class="modal fade" id="modal_email_template_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        This will delete email template and the action can't be undone!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="mailTemplateDeletionAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_mail_template_copy" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Enter new email template name</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row  m-t-20">
                           <label for="modal_new_mail_template_name" class="col-sm-3 control-label col-form-label">Email Template Name</label>
                           <div class="col-sm-7">
                              <input type="text" class="form-control" id="modal_new_mail_template_name" placeholder="Email Template Name Here">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="mailTemplateCopy()"><i class="mdi mdi-content-copy"></i> Copy</button>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal fade" id="modal_tracker_image_uploader" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Tracker image uploader</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div id="dropzone" class="text-center">
                           <form class="dropzone needsclick" id="dropzone-img">
                              <div class="dz-message needsclick">    
                                Drop files here or click to upload
                              </div>
                            </form>
                        </div>
                     </div>

                     <div class="modal-footer" >
                        <button type="button" id="bt_timage_delete" class="btn btn-danger" onclick="mailTemplateTrackerImageUpload()">Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
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
      <script src="js/libs/jquery/jquery-3.4.1.min.js"></script> <!--Compatibility issue with summernote-->
      <script src="js/libs/js.cookie.min.js"></script>   
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <!-- slimscrollbar scrollbar JavaScript -->
      <!--Wave Effects -->
      <script src="js/libs/waves.js"></script>
      <!--Menu sidebar -->
      <script src="js/libs/sidebarmenu.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="js/libs/jquery/datatables.js"></script>     
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/libs/dropzone.min.js"></script>
      <script src="js/libs/toastr.min.js"></script>
      <script src="js/libs/select2.min.js"></script>
      <!-- include summernote css/js -->
      <script src="js/libs/summernote.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/mail_template.js"></script>
      <?php
         echo '<script>';
         
         if(isset($_GET['action'])){
            if(isset($_GET['template'])){
               if($_GET['action'] == 'edit' && $_GET['template'] != 'new')    //edit existing
                  echo '$("#section_add_mail_template").show();
                        $("#section_view_mail_template_list").hide();';
                        
         
               if($_GET['action'] == 'add' && $_GET['template'] == 'new')     //new one
                  echo '$("#section_view_mail_template_list").hide();
                        $("#section_add_mail_template").show();';
               }
               echo '$(document).ready(function() {getMailTemplateFromTemplateId("' . $_GET['template'] . '");});';
         
         }
         else
            echo '$("#section_add_mail_template").hide();
                  loadTableMailTemplateList();';   
         
         echo '</script>';
         ?>
   </body>
</html>