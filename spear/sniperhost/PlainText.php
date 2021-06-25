<?php
   require_once(dirname(__FILE__,2) . '/session_manager.php');
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
      <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
      <title>SniperPhish - The Web-Email Spear Phishing Toolkit</title>
      <!-- Custom CSS -->      
      <link rel="stylesheet" type="text/css" href="../css/select2.min.css">
      <link rel="stylesheet" type="text/css" href="../css/style.min.css">
      <link rel="stylesheet" type="text/css" href="../css/dataTables.foundation.min.css">
      <style> 
         .tab-header{ list-style-type: none; }
      </style>
      <link rel="stylesheet" type="text/css" href="../css/toastr.min.css">
      <link rel="stylesheet" type="text/css" href="css/sniperhoststyle.min.css">
      <link rel="stylesheet" type="text/css" href="../css/codemirror.min.css">
      <link rel="stylesheet" type="text/css" href="../css/prism.css"/>
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
         <?php include_once(dirname(__FILE__,2) . '/z_menu.php'); ?>
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
                     <h4 class="page-title">Plain-Text Hosting</h4>
                     <div class="ml-auto text-right">
                        <span class="badge badge-dark" data-toggle="tooltip" title="File host ID" id="lb_ht_id"></span>                          
                        <button type="button" class="btn btn-info btn-sm" onclick="window.location = window.location.pathname;"><i class="fa fas fa-plus" title="New hosting" data-toggle="tooltip" data-placement="bottom"></i></button>
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
            <div class="container-fluid" >
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="row">
                  <div class="col-12">
                     <div class="card">
                        <div class="card-body">
                           <div class="row">
                              <div class="align-items-left col-12 d-flex no-block row">
                                 <label for="tb_pl_name" class="col-md-1 text-left control-label col-form-label">Name:</label>
                                 <div class="col-md-5">
                                    <input type="text" class="form-control" id="tb_ht_name" placeholder="Text file name">
                                 </div>

                                 <div class="align-items-right ml-auto">
                                    <div class="row">
                                       <button type="button" class="btn btn-info" onclick="savePlainText($(this))"><i class="fa fas fa-save"></i> Save</button>
                                    </div>
                                 </div>
                              </div>
                           </div>

                           <div class="row m-t-10">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <div class="row">
                                       <h5 class="col-md-7 card-title">Step 1: Add plain-text data</h5>
                                    </div>
                                    <div>
                                       <textarea id="editor_input"></textarea>
                                    </div>
                                    <div class="text-center m-t-20"><i class='fas fa-arrow-down fa-lg text-success'></i></div>
                                 </div>
                              </div>
                           </div>

                           <div class="row m-t-10">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <div class="row">
                                       <h5 class="col-md-7 card-title">Step 2: Select encryption/encoding algorithm</h5>
                                       <label class="col-md-2 text-left control-label col-form-label">Output:</label>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-2 align-items-left vcenter">
                                          <select class="select2 form-control custom-select" id="alg_type_selector" style="height: 36px;width: 100%;">
                                             <option></option>
                                          </select>
                                       </div>
                                       <div class="col-md-1">           
                                          <div class="vertical-center text-center">                               
                                             <i class="mdi mdi-arrow-right-bold"></i>
                                          </div>
                                       </div>
                                       <div class="col-md-3 vcenter">  
                                          <table id="tb_algo" class="table table-borderless tb_algo">
                                              <tbody>                                                  
                                              </tbody>
                                          </table>
                                       </div>
                                       <div class="col-md-1">           
                                          <div class="vertical-center text-center">                               
                                             <i class="mdi mdi-arrow-right-bold"></i>
                                          </div>
                                       </div>
                                       <div class="col-md-5">           
                                          <div class="col-md-12 prism_side-top">
                                            <span><button type="button" class="btn waves-effect waves-light btn-xs btn-light mdi mdi-download" data-toggle="tooltip" title="Download" onClick="downloadCode()"/><button type="button" class="btn waves-effect waves-light btn-xs btn-light mdi mdi-content-copy btn_copy_codeOutput" data-toggle="tooltip" title="Copy" onclick="copyCodeOutput($(this),'code_class_output')"/><button type="button" class="btn waves-effect waves-light btn-xs btn-light mdi mdi-reload" data-toggle="tooltip" title="Reload result" onclick="generateResult($(this),false)"/></span>
                                          </div>                       
                                          <textarea id="editor_output" class="code_class_output"></textarea>                                          
                                       </div>
                                    </div>
                                    <div class="text-center m-t-30"><i class='fas fa-arrow-down fa-lg text-success'></i></div>
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <div class="row">
                                       <div class="align-items-left col-12 row">
                                          <h5 class="col-md-7 card-title">Step 3: Select output style</h5>  
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-3 align-items-left">
                                          <label class="text-left control-label col-form-label">File extension:</label>
                                          <select class="select2 form-control custom-select" id="file_extension_selector" style="height: 36px;width: 100%;">
                                          </select>
                                       </div>
                                       <div class="col-md-3">
                                          <label class="text-left control-label col-form-label">&nbsp;</label>
                                          <input type="text" class="form-control" id="tb_extension_name" placeholder="Custom file extension" disabled="">
                                       </div>
                                       <div class="col-md-3 align-items-left">                                          
                                          <label class="text-left control-label col-form-label">Content-Type header:</label>
                                          <select class="select2 form-control custom-select" id="file_header_selector" style="height: 36px;width: 100%;">
                                          </select>
                                       </div>
                                       <div class="col-md-3">                                          
                                          <label class="text-left control-label col-form-label">&nbsp;</label>
                                          <input type="text" class="form-control" id="tb_header_name" placeholder="Custom Content-Type header" disabled="">
                                       </div>
                                    </div>
                                    <div class="text-center m-t-30"><i class='fas fa-arrow-down fa-lg text-success'></i></div>
                                 </div>
                              </div>                              
                           </div>

                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <div class="row">
                                       <div class="align-items-left col-12 row">
                                          <h5 class="col-md-7 card-title">Step 4: Get direct access link</h5>  
                                       </div>
                                    </div>
                                    
                                    <div class="row">
                                       <div class="col-md-12">
                                          <div class="col-md-12 prism_side-top">
                                            <span><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-content-copy btn_copy" data-toggle="tooltip" title="Copy" onclick="copyCode($(this),'code_class_link')"/><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-reload" data-toggle="tooltip" title="Re-generate access link" onClick="generateDownloadLink($('#file_extension_selector').val())"/></span>
                                          </div>
                                          <pre class="code_class_link"><code class="language-shell" id="link_output"> </code></pre>
                                       </div>
                                   </div>
                                 </div>
                              </div>                              
                           </div>

                           <div class="row">
                              <div class="col-md-12 m-t-20">
                                 <div class="table-responsive">
                                    <table id="table_plaintext_list" class="table table-striped table-bordered">
                                       <thead>
                                          <tr>
                                             <th>#</th>
                                             <th>Link Name</th>
                                             <th>Enc Algorithms</th>
                                             <th>File extension</th>
                                             <th>Content-Type</th>
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
            <div class="modal fade" id="modal_ht_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        This will delete plaintext hosting and the action can't be undone!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" onclick="plainTextDeletionAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <?php include_once '../z_footer.php' ?>
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
      <script src="../js/libs/jquery/jquery-3.6.0.min.js"></script> 
      <script src="../js/libs/jquery/jquery-ui.min.js"></script>
      <script src="../js/libs/js.cookie.min.js"></script>
      <script src="../js/libs/perfect-scrollbar.jquery.min.js"></script>
      <script src="../js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="../js/libs/clipboard.min.js"></script> 
      <script src="../js/libs/codemirror.min.js"></script>
      <script src="../js/common_scripts.js"></script>  
      <script src="js/sniper_host_plain-text.js"></script>
      <?php
         echo '<script>';
         if(isset($_GET['ht']))
               echo '$("#section_view_list").hide();
                     viewPlainTextDetailsFromId("' . doFilter($_GET['ht'],'ALPHA_NUM') . '",true);';
         
         echo '</script>';
      ?>   
      <script defer src="../js/libs/select2.min.js"></script>
      <script defer src="../js/libs/jquery/datatables.js"></script>   
      <script defer src="../js/libs/prism.js"></script>
      <script defer src="../js/libs/moment.min.js"></script>
      <script defer src="../js/libs/moment-timezone-with-data.min.js"></script>
      <script defer src="js/shell.min.js"></script>
      <script defer src="../js/libs/sidebarmenu.js"></script>
      <script defer src="../js/libs/popper.min.js"></script>
      <script defer src="../js/libs/bootstrap.min.js"></script>
      <script defer src="../js/libs/toastr.min.js"></script>
   </body>
</html>