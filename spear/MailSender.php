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
      <link rel="stylesheet" type="text/css" href="css/style.min.css">
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
      <style> 
         .tab-header{ list-style-type: none; }
      </style>
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
      <script src="js/libs/clipboard.min.js"></script>  
      <link href="css/prism.css" rel="stylesheet" />
      <script src="js/libs/prism.js"></script>
      <script src="js/libs/jszip.min.js"></script>        
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
                     <h4 class="page-title">Email Sender List</h4>
                  </div>
               </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid" id="section_view_list">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-12">
                           <button type="button" class="btn btn-info" onclick="document.location='MailSender?action=add&sender=new'"><i class="fas fa-plus"></i> New Mail Sender</button>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12 m-t-20">
                           <div class="row">
                              <div class="table-responsive">
                                 <table id="table_mail_sender_list" class="table table-striped table-bordered">
                                    <thead>
                                       <tr>
                                          <th>#</th>
                                          <th>Name</th>
                                          <th>SMTP Server</th>
                                          <th>From</th>
                                          <th>Acc Username</th>
                                          <th>Custom Headers</th>
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
            </div>
            <div class="container-fluid" id="section_addsender">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <div class="col-md-12">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="form-group row">
                                 <label for="mail_sender_name" class="col-sm-3 text-left control-label col-form-label">Name:*</label>
                                 <div class="col-sm-7">
                                    <input type="text" class="form-control" id="mail_sender_name" placeholder="Sender name">
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <label for="mail_sender_SMTP_server" class="col-sm-3 text-left control-label col-form-label">SMTP Server:*</label>
                                 <div class="col-sm-7">
                                    <input type="text" class="form-control" id="mail_sender_SMTP_server" placeholder="smtp.mailserver.com:25">
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <label for="mail_sender_from" class="col-sm-3 text-left control-label col-form-label">From: *</label>
                                 <div class="col-sm-7">
                                    <input type="text" class="form-control" id="mail_sender_from" placeholder="Name <username@mailserver.com>">
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <label for="mail_sender_acc_username" class="col-sm-3 text-left control-label col-form-label">Account Username:*</label>
                                 <div class="col-sm-7">
                                    <input type="text" class="form-control" id="mail_sender_acc_username" placeholder="Email account">
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <label for="mail_sender_acc_pwd" class="col-sm-3 text-left control-label col-form-label">Account Password:*</label>
                                 <div class="col-sm-7">
                                    <input type="password" class="form-control" id="mail_sender_acc_pwd" placeholder="Email account password">
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <label for="mail_sender_mailbox" class="col-sm-3 text-left control-label col-form-label">Mailbox:</label>
                                 <div class="col-sm-7">
                                    <input type="text" class="form-control" id="mail_sender_mailbox" placeholder="{smtp.mailserver.com:993/imap/ssl}INBOX">
                                    <div class="text-right m-t-5">
                                       <i class="mdi mdi-information cursor-pointer" data-container="body" data-toggle="popover" data-placement="top" data-content="Mailbox path receving replies from users. Mailbox of email account provided in 'Account username' is selected by default if no mai header 'REPLY-TO' is specified. Ref: https://www.php.net/manual/en/function.imap-open.php"></i>
                                       <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Verify mailbox access" onclick="verifyMailBoxAccess()">Verify</button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="row">
                                    <label class="text-left control-label col-form-label">Custom mail header: </label>
                              </div>
                              <div class="row">                                 
                                 <div class="col-sm-5">
                                    <input type="text" class="row form-control" id="mail_sender_custome_header_name" placeholder="X-Header Name">
                                 </div>
                                 <div class="col-sm-5">
                                    <input type="text" class="form-control" id="mail_sender_custome_header_val" placeholder="Header value">
                                 </div>
                                 <div class="align-items-right ml-auto">
                                    <button type="button" class="btn btn-success" onclick="addMailHeaderToTable()"><i class="mdi mdi-plus-outline"></i> Add</button>
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <div class="table-responsive m-t-10">
                                    <table id="table_mail_headers_list" class="table table-striped table-bordered">
                                       <thead>
                                          <tr>
                                             <th>Header Name</th>
                                             <th>Header Value</th>
                                             <th>Actions</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                              <div class="form-group row">
                                 <button type="button" class="btn btn-success" onclick="promptModalTestDelivery()"><i class="mdi mdi-check-all"></i> Send Test Mail</button>                              
                                 <div class="align-items-right ml-auto">
                                    <button type="button" class="btn btn-info" onclick="saveMailSenderGroup($(this))"><i class="fa fas fa-save"></i> Save</button>
                                 </div>
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
            <div class="modal fade" id="modal_mail_header_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        This will delete mail header!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="MailHeaderDeletionAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_mail_header_edit" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Edit Custom Email Header</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row  m-t-20">
                           <label for="modal_mail_sender_custome_header_name" class="col-sm-3  control-label col-form-label">Header Name</label>
                           <div class="col-sm-7">
                              <input type="text" class="form-control" id="modal_mail_sender_custome_header_name" placeholder="Header Name Here">
                           </div>
                        </div>
                        <div class="form-group row  m-t-20">
                           <label for="modal_mail_sender_custome_header_val" class="col-sm-3  control-label col-form-label">Header Value</label>
                           <div class="col-sm-7">
                              <input type="text" class="form-control" id="modal_mail_sender_custome_header_val" placeholder="Header Name Here">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="editRowHeaderTableAction()"><i class="mdi mdi-content-save"></i> Save</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_sender_list_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        This will delete selected sender!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="senderListDeletionAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_sender_list_copy" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Enter new sender list name</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row  m-t-20">
                           <label for="modal_mail_sender_name" class="col-sm-3  control-label col-form-label">Email Sender Name</label>
                           <div class="col-sm-7">
                              <input type="text" class="form-control" id="modal_mail_sender_name" placeholder="Email Sender Name Here">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="MailSenderCopyAction()"><i class="mdi mdi-content-copy"></i> Copy</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_sender_list_test_mail" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Test Mail Delivery</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row m-t-20">
                           <label for="modal_mail_sender_test_mail_to" class="col-sm-4  control-label col-form-label">Enter To address:</label>
                           <div class="col-sm-7">
                              <input type="text" class="form-control" id="modal_mail_sender_test_mail_to" placeholder="Email address of test recipient">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="modalTestDeliveryAction($(this))"><i class="fa fas fa-paper-plane"></i> Send</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_prompts" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                     <div class="modal-body" id="modal_prompts_body">
                        content...
                     </div>
                     <div class="modal-footer" >                        
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
      <script src="js/libs/jquery/jquery.steps.min.js"></script>
      <script src="js/libs/jquery/jquery.validate.min.js"></script>
      <script src="js/libs/jquery/datatables.js"></script>   
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/mail_sender.js"></script>
      <script src="js/libs/toastr.min.js"></script>
      <?php
         echo '<script>';
         if(isset($_GET['action'])){
            if(isset($_GET['sender'])){ 
               if($_GET['action'] == 'add' && $_GET['sender'] == 'new'){
                  echo '$("#section_view_list").hide();
                  $(document).ready(function() {getSenderFromSenderListId("' . $_GET['sender'] . '");});';
               }
               if($_GET['action'] == 'edit' && $_GET['sender'] != 'new'){
                  echo '$("#section_view_list").hide();
                  $(document).ready(function() {getSenderFromSenderListId("' . $_GET['sender'] . '");});';
               }
            }
         }
         else
            echo '$("#section_addsender").hide();
            $(document).ready(function() {loadTableSenderList();});';
         echo '</script>';
         ?>
   </body>
</html>