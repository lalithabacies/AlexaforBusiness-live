<?php

    if($_POST){
        global $wpdb;
        $username = trim($_POST['user_login']);
        $user_exists = false;
        // First check by username
        if ( username_exists( $username ) ){
            $user_exists = true;
            $user = get_user_by('login', $username);
        }
        // Then, by e-mail address
        elseif( email_exists($username) ){
                $user_exists = true;
                $user = get_user_by_email($username);
        }else{
            $error[] = '<p>'.__('Email was not found, try again!').'</p>';
        }
        if ($user_exists){
            $user_login = $user->user_login;
            $user_email = $user->user_email;
        
            $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
            if ( empty($key) ) {
                // Generate something random for a key...
                $key = wp_generate_password(20, false);
                do_action('retrieve_password_key', $user_login, $key);
                // Now insert the new md5 key into the db
                $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
            }
        
            //create email message
            $message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
            $message .= get_option('siteurl') . "/login/ \r\n\r\n";
            $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
            $message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
            //$message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "&redirect_to=".urlencode(get_option('siteurl'))."\r\n";
            $message .= '<' . network_site_url("custom-reset-password?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";
            //send email meassage
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
              $htmlmessage = '
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
		<tr style="display: grid;">
		
			<td width="100%" valign="top">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="font-size: 24px; font-weight: bold; color: #393768; padding-top: 25px; padding-bottom: 25px;">
							Hello '.$user_login.',
						</td>
					</tr>

					<tr>
						<td style="padding-top: 15px; padding-bottom: 15px;">
							Someone has asked to reset the password for the following site and username.
						</td>
					</tr>
					<tr>
						<td style="padding-top: 15px; padding-bottom: 15px;">
							To reset your password visit the following address, otherwise just ignore this email and nothing will happen.
						</td>
					</tr>
				</table>
			</td>
			<td width="100%" valign="top">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td align="center"  style="padding: 20px 0px">
							<a href="'.network_site_url("custom-reset-password?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login').'" style="background: #00ADD8;border: 0;border-radius: 2px;text-decoration: none;color: #fff;padding: 10px;font-size: 18px;font-weight: 600;">Click Here</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
';

            if (FALSE == wp_mail($user_email, sprintf(__('[%s] Password Reset'), get_option('blogname')),$htmlmessage,$headers))
            $error[] = '<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>';
        }
        if (count($error) > 0 ){
            foreach($error as $e){
                        //echo $e . "<br/>";
                        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$e.'</div>';
                    }
        }else{
            echo '<p>'.__('A message will be sent to your email address.').'</p>';
            /*$user = get_user_by('email', $_POST['user_login']);
            $password = wp_generate_password( 12, false );
            //wp_new_user_notification( $user->ID, $password );
            wp_reset_pwd_notification( $user->ID, $password );*/
            wp_redirect( home_url( '/login?reset_pwd=1' ) );
            exit;
        }
    }
    ?>
    <div class="col-md-offset-3 col-md-5 card card-tiles style-default-light">
	<form class="form floating-label form-validate" name="lostpasswordform" id="lostpasswordform" action="<?php echo get_home_url()."/lost-password"?>" method="post">
	    <fieldset>
			<p>Please enter your email address. You will receive a link to create a new password via email.</p>
	<div class="form-group">
	    		<input type="text" name="user_login" id="user_login" class="input form-control" value="" size="20" />
	    		<span id="errorMessage" >Please enter a valid email address</span>
             <label style="height: 50px;" for="email">Email *</label>
	</div>
		<input class="form-control" type="hidden" name="redirect_to" value="<?php echo get_home_url()."/login"; ?>" />
		<div class="col-md-6 text-left submit" style="margin-top:10px;"><input type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-raised" value="Get New Password" /></div>
		<div class="col-md-6 text-center submit" style="margin-top:10px;">
                <input class="btn btn-primary btn-raised button_link" type="button" name="wp-cancel" id="wp-cancel" data-link="<?php echo home_url()."/login"; ?>" value="<?php _e( 'Cancel', 'personalize-login' ); ?>">
        </div>
	</fieldset>
    </form>
    </div>
    
    <script>
    $(document).ready(function(e) {
        
        $("#errorMessage").css({'display':'none', 'color':'#a94442'});
        $("#lostpasswordform").submit(function(){
           var sEmail = $('#user_login').val();
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

        });
    
        $("#user_login").blur(function(e){
            Email_Validate(e);
        });
        
        $("#user_login").keyup(function(e){
            Email_Validate(e);
        });
    
    
    function Email_Validate(e){
        var sEmail = $('#user_login').val();
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