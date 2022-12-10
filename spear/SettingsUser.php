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
      <link rel="stylesheet" type="text/css" href="css/toastr.min.css">
      <link rel="stylesheet" type="text/css" href="css/dataTables.foundation.min.css">
      <script src="js/libs/clipboard.min.js"></script>  
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
                     <h4 class="page-title">SniperPhish Settings</h4>
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
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="comment-widgets col-md-12">
                             <!-- Comment Row -->
                             <div class="d-flex flex-row comment-row m-t-0">
                                 <div class="p-2"><img src="/spear/images/users/1.png" alt="user" width="150" class="rounded-circle" id="user_dp"></div>
                                 <div class="comment-text w-200">
                                     <h4 class="font-medium m-b-20" id="lb_name"></h4>
                                     <span class="m-b-10 d-block">User Name: <span class="m-l-5" id="lb_uname"></span></span> 
                                     <span class="m-b-10 d-block">Email: <span class="m-l-5" id="lb_mail"></span></span>  
                                     <span class="m-b-15 d-block">Account Created: <span class="m-l-5" id="lb_created_date"></span></span> 
                                     <div class="comment-footer">
                                         <button type="button" class="btn btn-cyan btn-sm" id="bt_edit_current_user">Edit Details</button>
                                     </div>
                                 </div>
                             </div>
                          </div>
                     </div>
                     <hr/>

                     <div class="row">
                        <div class="col-md-12">
                           <h5 class="card-title text-center m-t-10"><span>SniperPhish Accounts</span></h5>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">  
                           <div class="ml-auto text-right">
                              <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#ModalAddUser"><i class="fas fa-plus"></i> Create New Admin</button>
                           </div>
                        </div>
                     </div>
                     
                     <div class="row">
                        <div class="col-md-12 m-t-20">
                            <div class="row">
                              <div class="table-responsive">
                                 <table id="table_user_list" class="table table-striped table-bordered">
                                    <thead>
                                       <tr>
                                          <th>#</th>
                                          <th>Name</th>
                                          <th>Username</th>
                                          <th>Email</th>
                                          <th>Date Created</th>
                                          <th>Last Login</th>
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
            <?php include_once 'z_footer.php' ?>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
            <!-- Modal -->
            <div class="modal fade" id="ModalUserDelete" tabindex="-1" role="dialog" aria-hidden="true">
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
                        <button type="button" class="btn btn-danger" data-tracker_id="" onclick="deleteAccountAction()">Delete</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->            
            <div class="modal fade" id="ModalAddUser" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
               <div class="modal-dialog modal-large" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Create New Admin User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label for="rb_dp" class="col-sm-3 text-left control-label col-form-label">Avatar:</label>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/1.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_add_dp]').val([1])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbp1" name="rb_add_dp" value="1" checked>
                                   <label class="custom-control-label" for="rbp1"> </label>
                               </div>
                           </div>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/2.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_add_dp]').val([2])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbp2" name="rb_add_dp" value="2">
                                   <label class="custom-control-label" for="rbp2"> </label>
                               </div>
                           </div>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/3.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_add_dp]').val([3])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbp3" name="rb_add_dp" value="3">
                                   <label class="custom-control-label" for="rbp3"> </label>
                               </div>
                           </div>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/4.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_add_dp]').val([4])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbp4" name="rb_add_dp" value="4">
                                   <label class="custom-control-label" for="rbp4"> </label>
                               </div>
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_add_name" class="col-sm-3 text-left control-label col-form-label">Name:</label>
                           <div class="col-sm-9">
                              <input type="text" class="form-control" id="tb_add_name">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_add_uname" class="col-sm-3 text-left control-label col-form-label">Username:</label>
                           <div class="col-sm-9">
                              <input type="text" class="form-control" id="tb_add_uname">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_add_mail" class="col-sm-3 text-left control-label col-form-label">Email:</label>
                           <div class="col-sm-9">
                              <input type="text" class="form-control" id="tb_add_mail">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_add_pwd" class="col-sm-3 text-left control-label col-form-label">Password:</label>
                           <div class="col-sm-9">
                              <input type="password" class="form-control" id="tb_add_pwd" placeholder="Password Here">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_add_confirm_pwd" class="col-sm-3 text-left control-label col-form-label">Confirm Password:</label>
                           <div class="col-sm-9">
                              <input type="password" class="form-control" id="tb_add_confirm_pwd" placeholder="Confirm Password Here">
                           </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                           <label for="tb_update_current_pwd" class="col-sm-3 text-left control-label col-form-label">Your Password:</label>
                           <div class="col-sm-9">
                              <input type="password" class="form-control" id="tb_add_current_pwd" placeholder="Your Password Here">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="addUserAction($(this))"><i class="fa fas fa-plus"></i> Add</button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Modal -->            
            <div class="modal fade" id="ModalModifyUser" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
               <div class="modal-dialog modal-large" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title">Update User Info - <span id="modal_title_name"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                     </div>
                     <div class="modal-body">
                        <div class="form-group row">
                           <label for="rb_dp" class="col-sm-3 text-left control-label col-form-label">Avatar:</label>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/1.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_update_dp]').val([1])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbu1" name="rb_update_dp" value="1" checked>
                                   <label class="custom-control-label" for="rbu1"> </label>
                               </div>
                           </div>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/2.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_update_dp]').val([2])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbu2" name="rb_update_dp" value="2">
                                   <label class="custom-control-label" for="rbu2"> </label>
                               </div>
                           </div>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/3.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_update_dp]').val([3])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbu3" name="rb_update_dp" value="3">
                                   <label class="custom-control-label" for="rbu3"> </label>
                               </div>
                           </div>
                           <div class="col-sm-2">
                              <div class="p-2"><img src="/spear/images/users/4.png" alt="user" width="50" class="rounded-circle" onclick="$('input[name=rb_update_dp]').val([4])"></div>
                              <div class="custom-control custom-radio m-l-25">
                                   <input type="radio" class="custom-control-input" id="rbu4" name="rb_update_dp" value="4">
                                   <label class="custom-control-label" for="rbu4"> </label>
                               </div>
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_update_name" class="col-sm-3 text-left control-label col-form-label">Name:</label>
                           <div class="col-sm-9">
                              <input type="text" class="form-control" id="tb_update_name">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_update_uname" class="col-sm-3 text-left control-label col-form-label">Username:</label>
                           <div class="col-sm-9">
                              <input type="text" class="form-control" id="tb_update_uname" disabled>
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_update_mail" class="col-sm-3 text-left control-label col-form-label">Email:</label>
                           <div class="col-sm-9">
                              <input type="text" class="form-control" id="tb_update_mail">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_update_new_pwd" class="col-sm-3 text-left control-label col-form-label">New Password:</label>
                           <div class="col-sm-9">
                              <input type="password" class="form-control" id="tb_update_new_pwd" placeholder="New Password Here">
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="tb_update_confirm_pwd" class="col-sm-3 text-left control-label col-form-label">Confirm Password:</label>
                           <div class="col-sm-9">
                              <input type="password" class="form-control" id="tb_update_confirm_pwd" placeholder="Confirm Password Here">
                           </div>
                        </div>
                        <hr/>
                        <div class="form-group row">
                           <label for="tb_update_current_pwd" class="col-sm-3 text-left control-label col-form-label">Your Password:</label>
                           <div class="col-sm-9">
                              <input type="password" class="form-control" id="tb_update_current_pwd" placeholder="Your Password Here">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" >
                        <button type="button" class="btn btn-success" onclick="modifyUserAction($(this))"><i class="fa fas fa-save"></i> Update</button>
                     </div>
                  </div>
               </div>
            </div>
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
      <script src="js/libs/popper.min.js"></script>
      <script src="js/libs/bootstrap.min.js"></script>
      <script src="js/libs/perfect-scrollbar.jquery.min.js"></script>
      <!--Custom JavaScript -->
      <script src="js/libs/custom.min.js"></script>
      <script src="js/libs/select2.min.js"></script>
      <script src="js/libs/moment.min.js"></script>
      <script src="js/libs/moment-timezone-with-data.min.js"></script>
      <script src="js/libs/jquery/datatables.js"></script>  
      <script src="js/common_scripts.js"></script>
      <script src="js/settings_user.js"></script>
      <script type="text/javascript">
         var curr_version = "<?php getSniperPhishVersion(); ?>";
         $("#lb_version").text("Version: " + curr_version);
      </script>
      
      <script defer src="js/libs/sidebarmenu.js"></script>
      <script defer src="js/libs/toastr.min.js"></script>
   </body>
</html>