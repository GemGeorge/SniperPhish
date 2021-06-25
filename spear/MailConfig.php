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
                     <h4 class="page-title">Advanced Campaign Configuration</h4>
                     <div class="ml-auto text-right col-md-3 ">                       
                        <select class="select2 form-control custom-select" id="selector_config_list" style="height: 36px;width: 100%;">
                        </select>                  
                     </div>  
                     <div class="btn-group"  id="bt_save_config" >
                         <button type="button" class="btn btn-info btn-sm" onclick="saveConfigAction($(this))"><i class="fas fa-save"></i></button>
                         <button type="button" class="btn btn-info btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                         <div class="dropdown-menu">
                             <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_new_config">Edit name</a>
                             <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_config_delete">Delete</a>
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
            <div class="container-fluid" id="section_view_list">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <div class="form-group row">
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Batch Emails</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">The number of emails to be send as batch (default is 1).</i> 
                              </div>  
                              <label for="tb_batch_mail_limit" class="col-sm-3 text-left control-label col-form-label m-t-10">Message Limit:</label>
                              <div class="col-sm-3 m-t-10">
                                 <div class="input-group number-spinner">
                                    <span class="input-group-btn">
                                       <button class="btn btn-outline-secondary btn-sm" data-dir="dwn"><span class="fas fa-minus"></span></button>
                                    </span>
                                    <input type="text" class="form-control text-center form-control-sm" value="1" id="tb_batch_mail_limit">
                                    <span class="input-group-btn">
                                       <button class="btn btn-outline-secondary btn-sm" data-dir="up"><span class="fas fa-plus"></span></button>
                                    </span>
                                 </div>       
                              </div>                                                     
                           </div>                           
                        </div>
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Recipient Type</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">The recipient type such - TO, CC or BCC (default is 'TO').</i> 
                              </div>  
                              <label for="select_recipient_type" class="col-sm-3 text-left control-label col-form-label m-t-10">Receipient Type:</label>
                              <div class="col-sm-3 m-t-10">
                                 <select class="select2 form-control custom-select" id="select_recipient_type" style="height: 36px;width: 100%;">
                                    <option value="to" selected>To</option>
                                    <option value="cc">CC</option>
                                    <option value="bcc">BCC</option>
                                 </select>  
                              </div>  
                           </div>                           
                        </div>                       
                     </div>
                     <div class="form-group row">
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Read Receipts</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">Request read receipts for message. By default, the read receipt is sent to the "From" address specified in the sender configuration.</i> 
                              </div>  
                              <label for="cb_read_receipt" class="col-sm-4 text-left control-label col-form-label m-t-10">Read Receipt:</label>
                              <div class="custom-control custom-switch m-t-15 row">
                                 <label class="switch">
                                    <input type="checkbox" id="cb_read_receipt">
                                    <span class="slider round"></span>
                                 </label>
                              </div>
                           </div>                           
                        </div>
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Non ASCII Support</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">Supports Punycode transcription for non ASCII characters (such as Arabic/Chinese). Enable this only if the domain/email contains non ASCII characters. Note that your outbound SMTP server must support the SMTPUTF8 extension.</i> 
                              </div>  
                              <label for="cb_non_ascii_support" class="col-sm-4 text-left control-label col-form-label m-t-10">Non ASCII domain/Email:</label>
                              <div class="custom-control custom-switch m-t-15 row">
                                 <label class="switch">
                                    <input type="checkbox" id="cb_non_ascii_support">
                                    <span class="slider round"></span>
                                 </label>
                              </div>
                           </div>                           
                        </div>
                     </div>
                     <div class="form-group row">
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Signed Email</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">The certificate and private key must be PEM encoded, and can be either created using OpenSSL or obtained at an official Certificate Authority (CA).</i> 
                              </div>  
                              <label for="cb_signed_mail" class="col-md-4 text-left control-label col-form-label m-t-10">Sign Email:</label>
                              <div class="custom-control custom-switch m-t-15 col-md-4 row">
                                 <label class="switch">
                                    <input type="checkbox" id="cb_signed_mail">
                                    <span class="slider round"></span>
                                 </label>
                              </div>
                              <div class="col-md-4 text-right m-t-10" id="area_signed_mail_bt">
                                  <button type="button" class="btn btn-success btn-sm" onclick='$("#sign_mail_cert_uploader").trigger("click")' title="Upload signing certificate" data-toggle="tooltip" disabled=""><i class="mdi mdi-certificate"></i></button>
                                  <button type="button" class="btn btn-success btn-sm" onclick='$("#sign_mail_pvk_uploader").trigger("click")' title="Upload private key" data-toggle="tooltip" disabled=""><i class="mdi mdi-key-variant"></i></button>
                               </div>
                               <div class="col-md-12 row text-left">
                                 <div class="col-md-6 form-control-sm text-left" id="area_signed_mail_cert">
                                 </div>
                                 <div class="col-md-6 form-control-sm text-left" id="area_signed_mail_pvk">
                                 </div>                                 
                              </div> 
                           </div>                           
                        </div>
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Encrypted Email</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">When encrypting the message, the entire message (including attachments) is encrypted using a certificate, and the recipient can then decrypt the message using corresponding private key.</i> 
                              </div>  
                              <label for="cb_encrypted_mail" class="col-md-4 text-left control-label col-form-label m-t-10">Encrypt Email:</label>
                              <div class="custom-control custom-switch m-t-15 col-md-4 row">
                                 <label class="switch">
                                    <input type="checkbox" id="cb_encrypted_mail">
                                    <span class="slider round"></span>
                                 </label>
                              </div>
                              <div class="col-md-4 text-right float-right m-t-10" id="area_enc_mail_bt">
                                  <button type="button" class="btn btn-success btn-sm" onclick='$("#enc_mail_pvk_uploader").trigger("click")'  title="Upload encrypting certificate" data-toggle="tooltip" disabled=""><i class="mdi mdi-certificate"></i></button>
                               </div>
                               <div class="col-md-12 row text-left">
                                 <div class="col-md-6 form-control-sm align-items-left text-left" id="area_encrypted_mail">
                                 </div>
                              </div> 
                           </div>                           
                        </div>
                     </div>
                     <div class="form-group row">
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">AntiFlood Control</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">Controls the number of mails send in a single connection. Once the number of mails exceeded, SniperPhish will disconnect and pause some time before it re-connects (default is 50 emails and pause for 30seconds).</i> 
                              </div>  
                              <label for="tb_antiflood_limit" class="col-sm-3 text-left control-label col-form-label m-t-10">Message Limit:</label>
                              <div class="col-sm-3 m-t-10">
                                 <div class="input-group number-spinner">
                                    <span class="input-group-btn">
                                       <button class="btn btn-outline-secondary btn-sm" data-dir="dwn"><span class="fas fa-minus"></span></button>
                                    </span>
                                    <input type="text" class="form-control text-center form-control-sm" value="50" id="tb_antiflood_limit">
                                    <span class="input-group-btn">
                                       <button class="btn btn-outline-secondary btn-sm" data-dir="up"><span class="fas fa-plus"></span></button>
                                    </span>
                                 </div>       
                              </div>     
                              <label for="tb_antiflood_pause" class="col-sm-3 text-left control-label col-form-label m-t-10">Pause for (seconds):</label>
                              <div class="col-sm-3 m-t-10">
                                 <div class="input-group number-spinner">
                                    <span class="input-group-btn">
                                       <button class="btn btn-outline-secondary btn-sm" data-dir="dwn"><span class="fas fa-minus"></span></button>
                                    </span>
                                    <input type="text" class="form-control text-center form-control-sm" value="30" id="tb_antiflood_pause">
                                    <span class="input-group-btn">
                                       <button class="btn btn-outline-secondary btn-sm" data-dir="up"><span class="fas fa-plus"></span></button>
                                    </span>
                                 </div>       
                              </div>           
                           </div>                           
                        </div>                        
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <h6 class="hbar">Message Priority</h6> 
                              </div>   
                              <div class="col-md-12">
                                 <i class="small">Sets the message priority of the message. Setting the priority will not change the way email is sent, it is purely an indicative setting for the recipient.</i> 
                              </div>  
                              <label for="select_msg_priority" class="col-sm-3 text-left control-label col-form-label m-t-10">Message Priority:</label>
                              <div class="col-sm-3 m-t-10">
                                 <select class="select2 form-control custom-select" id="select_msg_priority" style="height: 36px;width: 100%;">
                                    <option value="1">Highest</option>
                                    <option value="2">High</option>
                                    <option value="3" selected="">Normal</option>
                                    <option value="4">Low</option>
                                    <option value="5">Lowest</option>
                                 </select>  
                              </div>  
                           </div>                           
                        </div> 
                     </div>
                     <hr/>                     
                     <input type="file" id="sign_mail_cert_uploader" accept=".pem" onchange="getBase64ofFile(uploadSignMailCert,this.files[0])" hidden="">
                     <input type="file" id="sign_mail_pvk_uploader" accept=".pem" onchange="getBase64ofFile(uploadSignMailPVK,this.files[0])" hidden="">
                     <input type="file" id="enc_mail_pvk_uploader" accept=".pem" onchange="getBase64ofFile(uploadEncMailCert,this.files[0])" hidden="">
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
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <!-- Modal -->
            <div class="modal fade" id="modal_config_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        This will delete campaign configuration!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="deleteConfigAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_new_config" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Save Configuration</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row  m-t-20">
                           <label for="modal_mail_sender_name" class="col-sm-4 control-label col-form-label">Configuration Name</label>
                           <div class="col-sm-8">
                              <input type="text" class="form-control" id="tb_config_name" placeholder="Configuration name">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-info" onclick="saveConfigAction($(this))"><i class="fa fas fa-save"></i> Save</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_prompts" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
      <script src="js/libs/jquery/jquery-3.6.0.min.js"></script>
      <script src="js/libs/js.cookie.min.js"></script> 
      <!-- Bootstrap tether Core JavaScript -->
      <!--Menu sidebar -->
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="js/libs/select2.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/mail_config.js"></script>
      <?php
         echo '<script>';
         if(isset($_GET['config']))
               echo '
               $(document).ready(function() {getMCampConfigFromConfigId("' . doFilter($_GET['config'],'ALPHA_NUM') . '",true);});';
         else
               echo '
               $(document).ready(function() {getMCampConfigFromConfigId("default");});';
         
         echo '</script>';
      ?>
      <script defer src="js/libs/popper.min.js"></script>
      <script defer src="js/libs/bootstrap.min.js"></script>
      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
   </body>
</html>