<?php
get_header();
global $wpdb;

if(is_user_logged_in()){
    $dashboard_url = home_url().'/dashboard';
}else{
    $dashboard_url = home_url().'/login';
}
?>

<!-- BEGIN HEADER-->
<header id="header" >
  <div class="headerbar">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="headerbar-left">
      <ul class="header-nav header-nav-options">
        <li class="header-nav-brand" >
          <div class="brand-holder">
            <a href="<?php echo $dashboard_url; ?>">
              <span class="text-lg text-bold text-primary">Alexa</span>
            </a>
          </div>
        </li>
        <?php
          if(is_user_logged_in())
          {
          ?>
        <li>
          <a class="btn btn-icon-toggle menubar-toggle" data-toggle="menubar" href="javascript:void(0);">
            <i class="fa fa-bars"></i>
          </a>
        </li>
        <?php
          }
        ?>
      </ul>
    </div>
    <!--Headerbar-left end-->
    <?php 
    if(is_user_logged_in()){
    ?>
	<div class="headerbar-right">
	<ul class="header-nav header-nav-profile">
		<li class="dropdown">
			<a href="javascript:void(0);" class="dropdown-toggle ink-reaction" data-toggle="dropdown">
			    <?php 
			    $current_user = wp_get_current_user();
			    ?>
				<span class="profile-info">
					<?php echo $current_user->user_login ?>
				</span>
			</a>
			<ul class="dropdown-menu animation-dock">
			    <li><a href="<?php echo home_url().'/my_profile' ?>">My profile</a></li>
				<li><a href="<?php echo wp_logout_url('/login') ?>"><i class="fa fa-fw fa-power-off text-danger"></i> Logout</a></li>
			</ul>
		</li>
	</ul>
</div>
<?php
}
?>
<!--Headerbar-right ends-->

  </div>
</header>
<!-- END HEADER-->

<!-- BEGIN BASE-->
<div id="base">

  <!-- BEGIN OFFCANVAS LEFT -->
  <div class="offcanvas">
  </div><!--end .offcanvas-->
  <!-- END OFFCANVAS LEFT -->
  <?php
  if(is_user_logged_in())
  {
  ?>
  <!-- BEGIN MENUBAR-->
  <div id="menubar" class="menubar-inverse ">
    <div class="menubar-fixed-panel">
      <div>
        <a class="btn btn-icon-toggle btn-default menubar-toggle" data-toggle="menubar" href="javascript:void(0);">
          <i class="fa fa-bars"></i>
        </a>
      </div>
      <div class="expanded">
        <a href="../../html/dashboards/dashboard.html">
          <span class="text-lg text-bold text-primary ">MATERIAL&nbsp;ADMIN</span>
        </a>
      </div>
    </div>
    <div class="menubar-scroll-panel">

      <!-- BEGIN MAIN MENU -->
      <ul id="main-menu" class="gui-controls">

        <!-- BEGIN DASHBOARD -->
        <li>
          <a href="<?php echo get_home_url().'/dashboard'?>" class="active">
            <div class="gui-icon"><i class="md md-home"></i></div>
            <span class="title">Dashboard</span>
          </a>
        </li><!--end /menu-li -->
        <!-- END DASHBOARD -->

        <!-- BEGIN EMAIL -->
        <!--<li class="gui-folder">
          <a>
            <div class="gui-icon"><i class="md md-account-circle"></i></div>
            <span class="title">User</span>
          </a>
          <ul>
            <li><a href="<?php echo get_home_url().'/iam_users_form'?>" ><span class="title">Add New</span></a></li>
            <li><a href="<?php echo get_home_url().'/view_iamusers'?>" ><span class="title">All Users</span></a></li>
          </ul>
        </li>-->
        <?php
        $user = wp_get_current_user();
        if($user->roles[0]=='administrator'){
        ?>
        <li class="gui-folder">
          <a>
            <div class="gui-icon"><i class="md md-account-circle"></i></div>
            <span class="title">User</span>
          </a>
          <ul>
            <li><a href="<?php echo get_home_url().'/user-creation'?>" ><span class="title">Add New</span></a></li>
            <li><a href="<?php echo get_home_url().'/user-list'?>" ><span class="title">All Users</span></a></li>
          </ul>
        </li>
        <?php
        }
        ?>

        <li>
          <a href="<?php echo get_home_url().'/devices'?>" >
            <div class="gui-icon"><i class="md md-devices"></i></div>
            <span class="title">Devices</span>
          </a>
        </li>

        <li class="gui-folder">
          <a>
            <div class="gui-icon"><i class="md md-web"></i></div>
            <span class="title">Room Profile</span>
          </a>
          <ul>
            <li><a href="<?php echo get_home_url().'/create-room-profile'?>" ><span class="title">Add New</span></a></li>
            <li><a href="<?php echo get_home_url().'/list-out-room-profile'?>" ><span class="title">All Room Profiles</span></a></li>
          </ul>
        </li>
        
        <li class="gui-folder">
          <a>
            <div class="gui-icon"><i class="md md-room"></i></div>
            <span class="title">Room</span>
          </a>
          <ul>
            <li><a href="<?php echo get_home_url().'/create_room'?>" ><span class="title">Add New</span></a></li>
            <li><a href="<?php echo get_home_url().'/list-out-rooms'?>" ><span class="title">All Rooms</span></a></li>
          </ul>
        </li>
        
        <!--<li class="gui-folder">
          <a>
            <div class="gui-icon"><span class="glyphicon glyphicon-list-alt"></span></div>
            <span class="title">Templates</span>
          </a>
          <ul>
            <--<li><a href="<?php echo get_home_url().'/request-type'?>" ><span class="title">Request Type</span></a></li> ->
            <li><a href="<?php echo get_home_url().'/notification'?>" ><span class="title">Notification</span></a></li>
          </ul>
        </li>-->
        
        <li class="gui-folder">
          <a>
            <div class="gui-icon"><i class="md md-assignment"></i></div>
            <span class="title">Templates</span>
          </a>
          <ul>
            <li><a href="<?php echo get_home_url().'/notification_template'?>" ><span class="title">Add New</span></a></li>
            <li><a href="<?php echo get_home_url().'/notification-temp-list'?>" ><span class="title">All Notification Template</span></a></li>
          </ul>
        </li>

        <!-- BEGIN FORMS -->
        <li class="gui-folder">
          <a>
            <div class="gui-icon"><span class="glyphicon glyphicon-list-alt"></span></div>
            <span class="title">Requests</span>
          </a>
          <!--start submenu -->
          <ul>
            <li><a href="<?php echo get_home_url().'/create-request'?>" ><span class="title">Add New</span></a></li>
            <li><a href="<?php echo get_home_url().'/requests-list'?>" ><span class="title">All Requests</span></a></li>
          </ul><!--end /submenu -->
        </li><!--end /menu-li -->
        <!-- END FORMS -->
        
        <li>
          <a href="<?php echo get_home_url().'/responses'?>" >
            <div class="gui-icon"><i class="md md-call-received"></i></div>
            <span class="title">Responses</span>
          </a>
        </li>
        
        
        <li>
          <a href="<?php echo get_home_url().'/audit_log'?>" >
            <div class="gui-icon"><i class="md md-subject"></i></div>
            <span class="title">Audit Log</span>
          </a>
        </li>
        
        <li>
          <a href="<?php echo get_home_url().'/reports'?>" >
            <div class="gui-icon"><i class="md md-bug-report"></i></div>
            <span class="title">Reports</span>
          </a>
        </li>
        
        <li>
          <a href="<?php echo get_home_url().'/activity_reports'?>" >
            <div class="gui-icon"><i class="md md-bug-report"></i></div>
            <span class="title">Activity Reports</span>
          </a>
        </li>
        
        <li>
          <a href="<?php echo get_home_url().'/settings'?>" >
            <div class="gui-icon"><i class="md md-settings"></i></div>
            <span class="title">Settings</span>
          </a>
        </li>

        <!-- END LEVELS -->

      </ul><!--end .main-menu -->
      <!-- END MAIN MENU -->

      <div class="menubar-foot-panel">
        <small class="no-linebreak hidden-folded">
          <span class="opacity-75">Copyright &copy; 2018</span> <strong>CodeCovers</strong>
        </small>
      </div>
    </div><!--end .menubar-scroll-panel-->
  </div><!--end #menubar-->
  <!-- END MENUBAR -->
  <?php
  }
  ?>
  <div id="content">
    <section>
      <!--<div class="section-header">
        <ol class="breadcrumb">
          <li class="active"><?php echo get_the_title()?></li>
        </ol>
      </div>-->
      <div class="section-body contain-lg">
        <div class="row">
          <!-- <div class="col-lg-12"> -->
           <!--  <div class="card card-tiles style-default-light"> -->
              <?php
              // Start the loop.
              while ( have_posts() ) : the_post();

                /*
                 * Include the Post-Format-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                 */
                the_content();

              // End the loop.
              endwhile;
              ?>
        <!--     </div> -->
        <!--   </div> -->
        </div>
      </div>
    </section>
  </div>
</div><!--end #base-->
<!-- END BASE -->



<?php get_footer(); ?>