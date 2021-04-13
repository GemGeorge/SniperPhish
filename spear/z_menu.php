 <header class="topbar" data-navbarbg="skin5">
    <nav class="navbar top-navbar navbar-expand-md navbar-dark">
       <div class="navbar-header" data-logobg="skin5">
          <!-- This is for the sidebar toggle which is visible on mobile only -->
          <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="fa fas fa-bars"></i></a>
          <!-- ============================================================== -->
          <!-- Logo -->
          <!-- ============================================================== -->
          <a class="navbar-brand" href="Home">
             <!-- Logo icon -->
             <b class="logo-icon p-l-10">
                <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                <!-- Dark Logo icon -->
                <img src="images/logo-icon.png" alt="homepage" class="light-logo" />
             </b>
             <!--End Logo icon -->
             <!-- Logo text -->
             <span class="logo-text">
                <!-- dark Logo text -->
                <img src="images/logo-text.png" alt="homepage" class="light-logo" />
             </span>
             <!-- Logo icon -->
             <!-- <b class="logo-icon"> -->
             <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
             <!-- Dark Logo icon -->
             <!-- <img src="images/logo-text.png" alt="homepage" class="light-logo" /> -->
             <!-- </b> -->
             <!--End Logo icon -->
          </a>
          <!-- ============================================================== -->
          <!-- End Logo -->
          <!-- ============================================================== -->
          <!-- ============================================================== -->
          <!-- Toggle which is visible on mobile only -->
          <!-- ============================================================== -->
          <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa fas fa-ellipsis-h"></i></a>
       </div>
       <!-- ============================================================== -->
       <!-- End Logo -->
       <!-- ============================================================== -->
       <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
          <!-- ============================================================== -->
          <!-- toggle and nav items -->
          <!-- ============================================================== -->
          <ul class="navbar-nav float-left mr-auto">
             <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
             <!-- ============================================================== -->
             <!-- create new -->
             <!-- ============================================================== -->
             <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="d-none d-md-block">Create New <i class="fa fa-angle-down"></i></span>
                <span class="d-block d-md-none"><i class="fa fa-plus"></i></span>   
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                   <a class="dropdown-item" href="QuickTracker">Quick Tracker</a>
                   <a class="dropdown-item" href="TrackerGenerator">Web Tracker</a>
                   <a class="dropdown-item" href="MailCampaignList?action=add&campaign=new">Email Campaign</a>
                </div>
             </li>
          </ul>
          <!-- ============================================================== -->
          <!-- Right side toggle and nav items -->
          <!-- ============================================================== -->
          <ul class="navbar-nav float-right">
             <!-- ============================================================== -->
             <!-- Comment -->
             <!-- ============================================================== -->
             <li class="nav-item dropdown" id="top_notifier">
                <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   <i class="mdi mdi-bell font-24"></i>
                </a>
             </li>
             <!-- ============================================================== -->
             <!-- End Comment -->
             <!-- ============================================================== -->
             <!-- ============================================================== -->
             <!-- User profile and search -->
             <!-- ============================================================== -->
             <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="images/users/1.jpg" alt="user" class="rounded-circle" width="31"></a>
                <div class="dropdown-menu dropdown-menu-right user-dd animated">
                   <a class="dropdown-item" href="Settings"><i class="fa far fa-user m-r-5 m-l-5"></i> My Profile</a>
                   <div class="dropdown-divider"></div>
                   <a class="dropdown-item" href="Settings"><i class="fa fas fa-cog m-r-5 m-l-5"></i> Account Setting</a>
                   <div class="dropdown-divider"></div>
                   <a class="dropdown-item" href="logout"><i class="fa fa-power-off m-r-5 m-l-5"></i> Logout</a>
                </div>
             </li>
             <!-- ============================================================== -->
             <!-- User profile and search -->
             <!-- ============================================================== -->
          </ul>
       </div>
    </nav>
 </header>
 <!-- ============================================================== -->
 <!-- End Topbar header -->
 <!-- ============================================================== -->
 <!-- ============================================================== -->
 <!-- Left Sidebar - style you can find in sidebar.scss  -->
 <!-- ============================================================== -->
 <aside class="left-sidebar" data-sidebarbg="skin5">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
       <!-- Sidebar navigation-->
       <nav class="sidebar-nav">
          <ul id="sidebarnav" class="p-t-30">
             <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="Home" aria-expanded="false"><i class="mdi mdi-home"></i><span class="hide-menu">Home</span></a></li>
             <li class="sidebar-item">
                <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-watch-vibrate"></i><span class="hide-menu">Quick Tracker </span></a>
                <ul aria-expanded="false" class="collapse  first-level">
                   <li class="sidebar-item"><a href="QuickTracker" class="sidebar-link"><i class="mdi mdi-playlist-plus"></i><span class="hide-menu"> Tracker List</span></a></li>
                   <li class="sidebar-item"><a href="QuickTrackerReport" class="sidebar-link"><i class="mdi mdi-book-open"></i><span class="hide-menu"> Reports </span></a></li>
                </ul>
             </li>
             <li class="sidebar-item">
                <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-web"></i><span class="hide-menu">Web Tracker </span></a>
                <ul aria-expanded="false" class="collapse  first-level">
                   <li class="sidebar-item"><a href="TrackerList" class="sidebar-link"><i class=" fas fa-th-list"></i><span class="hide-menu"> Tracker List </span></a></li>
                   <li class="sidebar-item"><a href="TrackerGenerator" class="sidebar-link"><i class="fas fa-plus"></i><span class="hide-menu"> New Tracker </span></a></li>
                </ul>
             </li>
             <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="TrackerReport" aria-expanded="false"><i class="mdi mdi-laptop-windows"></i><span class="hide-menu">Web Tracker Report</span></a></li>
             <li class="sidebar-item">
                <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-email"></i><span class="hide-menu">Email Campaign </span></a>
                <ul aria-expanded="false" class="collapse  first-level">
                   <li class="sidebar-item"><a href="MailCampaignList" class="sidebar-link"><i class="mdi mdi-playlist-plus"></i><span class="hide-menu"> Campaign List </span></a></li>
                   <li class="sidebar-item"><a href="MailUserGroup" class="sidebar-link"><i class="fas fa-users"></i><span class="hide-menu"> User Group </span></a></li>
                   <li class="sidebar-item"><a href="MailTemplate" class="sidebar-link"><i class="mdi mdi-credit-card"></i><span class="hide-menu"> Email Template </span></a></li>
                   <li class="sidebar-item"><a href="MailSender" class="sidebar-link"><i class="fas fa-user-secret"></i><span class="hide-menu"> Sender List </span></a></li>
                   <li class="sidebar-item"><a href="MailConfig" class="sidebar-link"><i class="fas fa-cogs"></i><span class="hide-menu"> Configuration</span></a></li>
                </ul>
             </li>
             <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="MailCmpDashboard" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><i class="icon-right-corn mdi mdi-email-outline"></i><span class="hide-menu">Email Campaign Dashboard</span></a></li>
             <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="WebMailCmpDashboard" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><i class="icon-bottom mdi mdi-email-outline"></i><i class="icon-right mdi mdi-web"></i><span class="hide-menu">Web-MailCamp Dashboard</span></a></li>

             <?php
              if(is_dir('payloads'))
                echo '<li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-bomb pcolor"></i><span class="hide-menu pcolor">Payloads</span></a>
                        <ul aria-expanded="false" class="collapse  first-level">
                           <li class="sidebar-item"><a href="payloads/Reflective" class="sidebar-link"><i class="mdi mdi-blender pcolor"></i><span class="hide-menu pcolor"> Reflective PE/DLL </span></a></li>                           
                        </ul>
                     </li>';
              if(is_dir('sniperhost'))
                echo '<li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-cloud pcolor"></i><span class="hide-menu pcolor">SniperHost</span></a>
                        <ul aria-expanded="false" class="collapse  first-level">
                           <li class="sidebar-item"><a href="sniperhost/PlainText" class="sidebar-link"><i class="mdi mdi-format-text pcolor"></i><span class="hide-menu pcolor"> Plain-Text </span></a></li>      
                           <li class="sidebar-item"><a href="sniperhost/FileHost" class="sidebar-link"><i class="mdi mdi-file-multiple pcolor"></i><span class="hide-menu pcolor"> Files </span></a></li>                           
                        </ul>
                     </li>';
              ?>
              
             <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="Settings" aria-expanded="false"><i class="mdi mdi-settings"></i><span class="hide-menu">Settings</span></a></li>
          </ul>
       </nav>
       <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
 </aside>