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
            <div class="page-breadcrumb">
               <div class="row">
                  <div class="col-12 d-flex no-block align-items-center">
                     <h4 class="page-title">Email User Groups</h4>
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
                           <button type="button" class="btn btn-info btn-sm" onclick="document.location='MailUserGroup?action=add&user=new'"><i class="fas fa-plus"></i> New User Group</button>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12 m-t-20">
                           <div class="row">
                              <div class="table-responsive">
                                 <table id="table_user_group_list" class="table table-striped table-bordered">
                                    <thead>
                                       <tr>
                                          <th>#</th>
                                          <th>Group Name</th>
                                          <th>Total Users</th>
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
            <div class="container-fluid" id="section_adduser">
               <!-- ============================================================== -->
               <!-- Start Page Content -->
               <!-- ============================================================== -->
               <div class="card">
                  <div class="card-body">
                     <!--<h5 class="card-title">Tracker Templates</h5>-->
                     <div class="row">
                        <div class="col-md-12">
                           <div class="row">       
                              <div class="col-md-6">
                                 <div class="form-group row">                                        
                                    <label for="user_group_name" class="col-md-3 text-left control-label col-form-label">User Group Name: </label>
                                    <div class="col-md-9">              
                                       <input type="text" class="form-control" id="user_group_name">
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6 text-right">
                                 <button type="button" class="btn btn-info" onclick="saveUserGroup($(this))"><i class="fa fas fa-save"></i> Save</button>
                              </div>                             
                           </div>                    
                        </div>
                     </div>
                     <hr/>
                     <div class="row">
                        <div class="col-md-4">
                           <div class="form-group">
                                 <label>First Name:</label>
                                 <input type="text" class="form-control date-inputmask" id="tablevalue_fname">
                             </div>
                             <div class="form-group">
                                 <label>Email:</label>
                                 <input type="text" class="form-control phone-inputmask" id="tablevalue_email">
                             </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                                 <label>Last Name:</label>
                                 <input type="text" class="form-control date-inputmask" id="tablevalue_lname">
                             </div>
                             <div class="form-group">
                                 <label>Notes:</label>
                                 <input type="text" class="form-control phone-inputmask" id="tablevalue_notes">
                             </div>
                        </div>
                        <div class="col-md-2">
                           <div class="form-group m-t-25">
                              <button type="button" class="btn btn-success" id="bt_add_email_tracker" onclick="addUserToTable($(this))"><i class="fa fas fa-plus"></i> Add</button>
                           </div>
                        </div>
                        <div class="col-md-2 m-t-25 text-right">
                           <div class="form-group">
                              <div class="btn-group"  id="bt_save_config" >
                                  <button type="button" class="btn btn-success" onclick="addUserFromFile()" title="Import email list" data-toggle="tooltip">Import</button>
                                  <input type="file" id="fileinput" accept=".txt, .csv, .lst, .rtf" hidden />
                                  <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                  <div class="dropdown-menu">
                                      <a class="dropdown-item" href="#" onclick="exportUserAction()">Export as CSV</a>
                                  </div>
                              </div>   
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="table-responsive">
                           <table id="table_user_list" class="table table-striped table-bordered">
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Notes</th>
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
            <div class="modal fade" id="modal_modify_row" tabindex="-1" role="dialog"  aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content" style="width: 610px;">
                     <div class="modal-header">
                        <h5 class="modal-title">Modify user</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row  m-t-20">
                           <label for="modal_tablevalue_name" class="col-sm-2 text-left control-label col-form-label">First Name: </label>
                           <div class="col-sm-8">
                              <input type="text" class="form-control" id="modal_tablevalue_fname">
                           </div>
                        </div>
                        <div class="form-group row  m-t-20">
                           <label for="modal_tablevalue_name" class="col-sm-2 text-left control-label col-form-label">Last Name: </label>
                           <div class="col-sm-8">
                              <input type="text" class="form-control" id="modal_tablevalue_lname">
                           </div>
                        </div>
                        <div class="form-group row  m-t-20">
                           <label for="modal_tablevalue_email" class="col-sm-2 text-left control-label col-form-label">Email: </label>
                           <div class="col-sm-8">
                              <input type="email" class="form-control" id="modal_tablevalue_email">
                           </div>
                        </div>
                        <div class="form-group row  m-t-20">
                           <label for="modal_tablevalue_notes" class="col-sm-2 text-left control-label col-form-label">Notes: </label>
                           <div class="col-sm-8">
                              <input type="text" class="form-control" id="modal_tablevalue_notes">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-info" id="bt_add_email_tracker" onclick="editRowAction($(this))"><i class="fa fas fa-save"></i> Save</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_user_group_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        This will delete user group and the action can't be undone!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="userGroupDeletionAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_row_delete" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        This will delete user and the action can't be undone!
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="deleteRowAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="modal_user_group_copy" tabindex="-1" role="dialog" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Enter new user group name</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row  m-t-20">
                           <label for="modal_new_user_group_name" class="col-sm-3  control-label col-form-label">Group Name</label>
                           <div class="col-sm-7">
                           <input type="text" class="form-control" id="modal_new_user_group_name" placeholder="Group Name Here">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="UserGroupCopy()"><i class="mdi mdi-content-copy"></i> Copy</button>
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
      <script src="js/libs/js.cookie.min.js"></script>
      <!--Menu sidebar -->
      <script src="js/libs/sidebarmenu.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <!-- this page js -->
      <script src="js/libs/jquery/datatables.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/common_scripts.js"></script>
      <script src="js/mail_user_group.js"></script>
      <?php
         echo '<script>';
         if(isset($_GET['action'])){
            if(isset($_GET['action']) && isset($_GET['user'])){ 
               if($_GET['action'] == 'add' || $_GET['action'] == 'edit'){
                  echo '$("#section_view_list").hide();
                        getUserGroupFromGroupId("' . doFilter($_GET['user'],'ALPHA_NUM') . '");';
               }
            }
         }
         else
            echo '$("#section_adduser").hide();
                  loadTableUserGroupList();';
         echo '</script>';
      ?>     
      <script defer src="js/libs/popper.min.js"></script>
      <script defer src="js/libs/bootstrap.min.js"></script>
      <script defer src="js/libs/select2.min.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
   </body>
</html>