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
                     <h4 class="page-title">Email Sender List</h4>
                     <div class="ml-auto text-right" id="store-area">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#ModalStore"><i class="fa fas fa-warehouse"></i> Store</button>
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
            <div class="container-fluid" id="section_view_list">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-12">
                           <button type="button" class="btn btn-info btn-sm" onclick="document.location='MailSender?action=add&sender=new'"><i class="fas fa-plus"></i> New Mail Sender</button>
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
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group row">                                              
                              <label for="mail_sender_name" class="col-md-2 text-left control-label col-form-label">Mail Sender Name: </label>
                              <div class="col-md-4">              
                                 <input type="text" class="form-control" id="mail_sender_name">
                              </div>
                              <div class="col-md-6 text-right">
                                 <button type="button" class="btn btn-info" onclick="saveMailSenderGroup($(this))"><i class="fa fas fa-save"></i> Save</button>
                              </div>                             
                           </div>                    
                        </div>
                     </div>
                     <hr/>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group row">
                                    <label for="mail_sender_SMTP_server" class="col-sm-3 text-left control-label col-form-label">SMTP Server:*</label>
                                    <div class="col-sm-7">
                                       <input type="text" class="form-control" id="mail_sender_SMTP_server" placeholder="smtp.mailserver.com:25">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="mail_sender_acc_username" class="col-sm-3 text-left control-label col-form-label">SMTP Username:*</label>
                                    <div class="col-sm-7">
                                       <input type="text" class="form-control" id="mail_sender_acc_username" placeholder="Email account">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="mail_sender_acc_pwd" class="col-sm-3 text-left control-label col-form-label">SMTP Password:*</label>
                                    <div class="col-sm-7">
                                       <input type="password" class="form-control" id="mail_sender_acc_pwd" placeholder="Email account password">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="mail_sender_from" class="col-sm-3 text-left control-label col-form-label">From Address: *</label>
                                    <div class="col-sm-7">
                                       <input type="text" class="form-control" id="mail_sender_from" placeholder="Name <username@mailserver.com>">
                                    </div>
                                 </div>                                 
                                 <div class="form-group row">
                                    <label for="mail_sender_mailbox" class="col-sm-3 text-left control-label col-form-label">Mailbox:</label>
                                    <div class="col-sm-7">
                                       <div class="row">
                                          <label class="col-sm-6 text-left control-label col-form-label">Auto mailbox lookup</label>
                                          <div class="custom-control custom-switch m-t-5 ml-auto row">
                                             <label class="switch">
                                                <input type="checkbox" id="cb_auto_mailbox" checked>
                                                <span class="slider round"></span>
                                             </label>
                                          </div>
                                       </div>

                                       <input type="text" class="form-control" id="mail_sender_mailbox" placeholder="{imap.mailserver.com:993/imap/ssl}INBOX" disabled="">
                                       <div class="text-right m-t-5">
                                          <i class="mdi mdi-information cursor-pointer" data-container="body" data-toggle="popover" data-placement="top" data-content="Mailbox path receiving replies from users. Mailbox of email account provided in 'Account username' is selected by default if no mai header 'REPLY-TO' is specified. Ref: https://www.php.net/manual/en/function.imap-open.php"></i>
                                          <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Verify mailbox access" onclick="verifyMailBoxAccess()">Verify</button>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group row">
                                    <label class="text-left control-label col-form-label">Custom mail header:</label>
                                 </div>
                                 <div class="form-group row">                                 
                                    <div class="col-sm-5">
                                       <input type="text" class="row form-control" id="mail_sender_custome_header_name" placeholder="X-Header Name">
                                    </div>
                                    <div class="col-sm-5">
                                       <input type="text" class="form-control" id="mail_sender_custome_header_val" placeholder="Header value">
                                    </div>
                                    <div class="col-sm-2 text-right">
                                       <button type="button" class="btn btn-success" onclick="addMailHeaderToTable()"><i class="mdi mdi-plus-outline"></i> Add</button>
                                    </div>
                                 </div>
                                 <div class="form-group row m-r-0">
                                    <div class="table-responsive">
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
                                    <label for="mail_sender_mailbox" class="col-sm-3 row text-left control-label col-form-label">SMTP Encryption:</label>
                                    <div class="col-md-7 row">
                                       <div class="range-slider col-md-7 m-t-15">
                                          <input class="input-range input-range1" oninput="rangeSMTPEncryption(this.id)" onchange="rangeSMTPEncryption(this.id)" id="range_SMTP_enc_level" type="range" value="2" min="0" max="2">
                                       </div> 
                                       <label class="col-md-4 m-l-10 text-info text-left control-label col-form-label" id="lb_smtp_enc">TLS</label>
                                    </div>
                                 </div>
                                 <hr/>
                                 <div class="form-group row">
                                    <div class="col-sm-12 text-right">
                                       <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_sender_list_test_mail"><i class="fa fas fa-paper-plane"></i> Send Test Mail</button>
                                    </div> 
                                 </div>
                              </div>
                           </div>
                           <hr/>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row m-t-20">
                           <label for="modal_mail_sender_test_mail_to" class="col-sm-4  control-label col-form-label">Enter To address:</label>
                           <div class="col-sm-8">
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
            <div class="modal fade" id="modal_verifier" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body" id="modal_verifier_body">
                     </div>
                     <div class="modal-footer" >                        
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="ModalStore" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Common Senders</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row m-t-20">
                           <label for="selector_common_mail_senders" class="col-sm-4  control-label col-form-label">Select mail sender:</label>
                              <div class="col-md-8">
                                 <select class="select2 form-control custom-select" id="selector_common_mail_senders" style="height: 36px;width: 100%;">
                                 </select>
                              </div>
                        </div>
                        <i id="lb_selector_common_mail_sender_note"></i>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="insertCommonSender()"><i class="mdi mdi-arrow-bottom-left"></i> Insert</button>
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
      <script src="js/libs/jquery/jquery-3.6.0.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script> 
      <!-- Bootstrap tether Core JavaScript -->
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="js/libs/jquery/datatables.js"></script>  
      <script src="js/common_scripts.js"></script>
      <script src="js/mail_sender.js"></script>
      <?php
         echo '<script>';
         if(isset($_GET['action']) && isset($_GET['sender']) && $_GET['sender'] != ''){
            echo '$("#section_view_list").hide();
                  getSenderFromSenderListId("' . doFilter($_GET['sender'],'ALPHA_NUM') . '");
                  getStoreList();';
         }
         else{
            echo '$("#section_addsender").hide();
                  loadTableSenderList();';            
            echo '$("#store-area").hide();';
         }
         echo '</script>';
      ?>

      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/moment.min.js"></script>
      <script defer src="js/libs/moment-timezone-with-data.min.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
      <script defer src="js/libs/popper.min.js"></script>
      <script defer src="js/libs/bootstrap.min.js"></script>
      <script defer src="js/libs/select2.min.js"></script>
   </body>
</html>