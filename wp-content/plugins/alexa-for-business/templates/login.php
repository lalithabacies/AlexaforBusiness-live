<?php

if($_POST){
    
   
    
        //custom_login($_POST);
        if ( !is_user_logged_in() ) {
    	$creds = array();
    	$creds['user_login'] = $_POST['username'];
    	$creds['user_password'] = $_POST['password'];
    	if(!empty($_POST['remember_me']))
    	{
    	    $hour = time() + 3600 * 24 * 30;
            setcookie('username', $creds['user_login'], $hour);
            setcookie('password', $creds['user_password'], $hour);
			$creds['remember'] = true;
    	}
		else
		{
		    unset($_COOKIE['username']);
		    unset($_COOKIE['password']);
		     $hour = time() + 3600 * 24 * 30;
            setcookie('username', "", $hour);
            setcookie('password', "", $hour);
			$creds['remember'] = false;
		}
		
    	$creds['redirect_to'] = get_home_url().'/dashboard';
    	
    	if ( ! empty( $_POST['username'] ) ) {
        //if the username doesn't contain a @ set username to blank string
        //causes authenticate to fail
        if(strpos($_POST['username'], '@') == TRUE){
                $user = get_user_by( 'email', $_POST['username'] );
                $creds['user_login'] = $user->user_login;
            }
        
        }
        
    	$auth =wp_authenticate_username_password('',$creds['user_login'],$creds['user_password']);
    	if(!empty($auth->errors)){
    	    foreach($auth->errors as $key=>$value){
    	        if(is_array($value)){
    	        $error = $value[0];
    	        //wp_redirect(home_url().'/login?errors='.urlencode($error));
    	        $attributes['errors']     = 1;
    	        $attributes['errors_content'] = $error;
    	        }
    	    }
    	}else{
        	$user = wp_signon( $creds, false );
        	$errors = new WP_Error();
        	if ( is_wp_error($user) ){
        		echo $user->get_error_message();
            }
            else{
                wp_redirect(get_home_url().'/dashboard');
            }
        }
        }
    }
   else if(isset($_COOKIE['username']) && isset($_COOKIE['password']))
	{
		 if ( !is_user_logged_in() ) {
			$c_username = $_COOKIE['username'];
			$c_password = $_COOKIE['password'];
			$c_remember = true;		
		 } 
	}	
	
    //else{
        $attributes['registered'] = isset( $_REQUEST['registered'] );
        $attributes['reset_pwd']  = isset( $_REQUEST['reset_pwd'] );
        $attributes['rp_success'] = $_REQUEST['rp_success'];
        
   
    
    ?>
    <?php if ( $attributes['registered']) : ?>
    <p class="login-info"><div class="alert alert-callout alert-success col-md-offset-3 col-md-6">
        <?php
            printf(
                __( 'You have successfully registered to <strong>%s</strong>. We have emailed your password to the email address you entered.', 'personalize-login' ),
                get_bloginfo( 'name' )
            );
        ?></div>
    </p>
    <?php elseif($attributes['reset_pwd']): ?>
    <p class="login-info" style="z-index:999";><div class="alert alert-callout alert-success col-md-offset-3 col-md-6">The Password Reset link has been sent to entered email address.</div>
    </p>
    <?php elseif($attributes['rp_success']): ?>
    <p class="login-info" style="z-index:999";><div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Your password has been reset.</div>
    </p>
    <?php elseif($attributes['errors']): ?>
    <div class="login-info col-md-offset-3 col-md-6 alert alert-callout alert-danger" style="z-index:999";><?php echo $attributes['errors_content']; ?>
    </div>
   
    
    <?php endif; ?>
    
    <!-- BEGIN LOGIN SECTION -->
    <section class="section-account">

        <div class="card-body">
         <div class="row">
            <div class="col-md-offset-3 col-md-5 card card-tiles style-default-light">
               <br/>
               <span class="text-lg text-bold text-primary">NEXTER ADMIN</span>
               <br/><br/>
               
               <form class="form floating-label form-validate" id="login" action="<?php echo get_home_url().'/login/'?>" accept-charset="utf-8" method="post">
                <div class="form-group">
                 <input type="text" class="form-control" id="username" name="username" value="<?php  echo $c_username;?>" >
                 <span id="errorMessage" >Please enter a valid username</span>
                 <label style="height: 50px;" for="username">Username <strong>*</strong></label>
             </div>
             <div class="form-group">
                 <input type="password" class="form-control" id="password" name="password" required value="<?php  echo $c_password;?>">
                 <label for="password">Password <strong>*</strong></label>
                 <p class="help-block"><a href="<?php echo get_home_url().'/lost-password?action=lostpassword'?>">Forgot Password?</a></p>
                 
                 <!--<a href="<?php echo wp_lostpassword_url( get_bloginfo('url') ); ?>" title="Lost Password">Lost Password</a>-->
             </div>
             <br/>
               <div class="form-group">
              <label>
                <input type="checkbox" name="remember_me" id="remember_me" <?php  if(!empty($c_username)){ echo "checked"; }?>>
                Remember me 
              </label>
              </div>
             <div class="row">
                 <div class="col-xs-6 text-left">
                  <!--<div class="checkbox checkbox-inline checkbox-styled">
                   <label>
                    <input type="checkbox" name="rememberme"> <span>Remember me</span>
                   </label>
                  </div>-->
                 </div><!--end .col -->
        <div class="col-xs-6 text-right">
          <input class="btn btn-primary btn-raised" type="submit" value="Login">
      </div><!--end .col -->
  </div><!--end .row -->
 <!--<div class="">
    <h4 class="text-light text-center">
        Don't Have Account yet?
    </h4>
    <a class="btn btn-block btn-raised btn-primary" href="<?php echo get_home_url()."/register" ?>">Register Here</a>
</div> -->
</form>
</div><!--end .col -->

</div><!--end .row -->
</div><!--end .card-body -->

</section>
<!-- END LOGIN SECTION -->

    <script>
    $(document).ready(function(e) {
        
        $("#errorMessage").css({'display':'none', 'color':'#a94442'});
        $("#login").submit(function(){
           var sEmail = $('#username').val();
            if ($.trim(sEmail).length == 0) {
                $("#errorMessage").html('This field is required');
                $("#errorMessage").css({'display':'block'});
                return false;
                e.preventDefault();
            }
            /*else if (!validateEmail(sEmail)) {
                $("#errorMessage").html('Please enter a valid email address');
                $("#errorMessage").css({'display':'block'});
                $('.email>label:after').css({'background-color': '#0aa89e'})
                return false;
                e.preventDefault();
            }*/
            else {
                  $("#errorMessage").css({'display':'none'});
            }

        });
    
        /*$("#username").blur(function(e){
            Email_Validate(e);
        });
        
        $("#username").keyup(function(e){
            Email_Validate(e);
        });*/
    
    
    function Email_Validate(e){
        var sEmail = $('#username').val();
        if ($.trim(sEmail).length == 0) {
            $("#errorMessage").html('This field is required');
            $("#errorMessage").css({'display':'block'});
            return false;
            e.preventDefault();
        }
        else if (!validateEmail(sEmail)) {
            $("#errorMessage").html('Please enter a valid email address');
            $("#errorMessage").css({'display':'block'});
            $('.email>label:after').css({'background-color': '#0aa89e'})
            return false;
            e.preventDefault();
        }
        else {
              $("#errorMessage").css({'display':'none'});
        }
    }

    function validateEmail(sEmail) {
        var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if (filter.test(sEmail)) {
            return true;
        }
        else {
            return false;
        }
    }
    
    
    });
    </script>