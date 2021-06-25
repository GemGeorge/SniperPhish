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
                     <h4 class="page-title">File Hosting</h4>
                     <div class="ml-auto text-right">
                        <span class="badge badge-dark" data-toggle="tooltip" title="File host ID" id="lb_hf_id"></span>                          
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
                                    <input type="text" class="form-control" id="tb_hf_name" placeholder="Text file name">
                                 </div>

                                 <div class="align-items-right ml-auto">
                                    <div class="row">
                                       <button type="button" class="btn btn-info" onclick="saveFile($(this))"><i class="fa fas fa-save"></i> Save</button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="row m-t-30">
                              <div class="form-group col-md-8 hints-class">
                                 <div class="form-group">
                                    <div class="text-center dropzone align-items-center box" ondrop="uploadFile(event,getFileData,this)" onclick="$('#file-uploader').trigger('click');" >
                                       <i class="fas fa-download fa-2x"></i><br/><div id="upload_msg">Drop the file here or click to upload</div>    
                                    </div>                             
                                    <input type="file" id="file-uploader" onchange="getBase64ofFile(getFileData,this.files[0],this)" hidden="">
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <label for="tb_pl_name" class="col-md-2 text-left control-label col-form-label">Content-Type header:</label>
                              <div class="col-md-3 align-items-left">                               
                                 <select class="select2 form-control custom-select" id="file_header_selector" style="height: 36px;width: 100%;">
                                 </select>
                              </div>
                              <div class="col-md-3">                                          
                                 <input type="text" class="form-control" id="tb_header_name" placeholder="Custom Content-Type header" disabled="">
                              </div>
                           </div>

                           <div class="m-b-20">
                              <div class="text-center m-t-30"><i class='fas fa-arrow-down fa-lg text-success'></i></div>
                           </div>

                           <div class="row">
                              <div class="col-md-12">
                                 <div class="col-md-12 prism_side-top">
                                   <span><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-content-copy btn_copy" data-toggle="tooltip" title="Copy" onclick="copyCode($(this),'code_class_link')"/><button type="button" class="btn waves-effect waves-light btn-xs btn-dark mdi mdi-reload" data-toggle="tooltip" title="Re-generate access link" onClick="generateDownloadLink()"/></span>
                                 </div>
                                 <pre class="code_class_link"><code class="language-shell" id="link_output"> </code></pre>
                              </div>
                          </div>

                           <div class="row">
                              <div class="col-md-12 m-t-20">
                                 <div class="table-responsive">
                                    <table id="table_file_list" class="table table-striped table-bordered">
                                       <thead>
                                          <tr>
                                             <th>#</th>
                                             <th>Link Name</th>
                                             <th>File Name</th>
                                             <th>Content-Type</th>
                                             <th>Date Uploaded</th>
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
            <div class="modal fade" id="modal_hf_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        This will delete file hosting and the action can't be undone!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" onclick="fileDeletionAction()">Delete</button>
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
      <script src="../js/libs/js.cookie.min.js"></script>
      <script src="../js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="../js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="../js/common_scripts.js"></script>  
      <script src="js/sniper_host_file.js"></script>
      <?php
         echo '<script>';
         if(isset($_GET['hf']))
               echo '$("#section_view_list").hide();
                     viewFileDetailsFromId("' . doFilter($_GET['hf'],'ALPHA_NUM') . '",true);';
         
         echo '</script>';
      ?>
      <script defer src="../js/libs/select2.min.js"></script>
      <script defer src="../js/libs/clipboard.min.js"></script>   
      <script defer src="../js/libs/toastr.min.js"></script>
      <script defer src="../js/libs/jquery/datatables.js"></script>   
      <script defer src="../js/libs/prism.js"></script>
      <script defer src="../js/libs/moment.min.js"></script>
      <script defer src="../js/libs/moment-timezone-with-data.min.js"></script>
      <script defer src="../js/libs/popper.min.js"></script>
      <script defer src="../js/libs/bootstrap.min.js"></script>
      <script defer src="../js/libs/sidebarmenu.js"></script>
   </body>
</html>