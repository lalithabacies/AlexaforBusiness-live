<div class="col-md-offset-4 col-md-5 card card-tiles style-default-light">
<!--<form name="resetpassform" id="resetpassform" class="form-validate" action="http://www.vegeofit.com/alexa-for-business/wp-login.php?action=resetpass" method="post" autocomplete="off">-->

<form name="resetpassform" id="resetpassform" class="form floating-label form-validate" action="<?php echo home_url().'/custom-reset-password';?>" method="post" autocomplete="off">
	<input type="hidden" name="user_login" id="user_login" value="<?php echo $_REQUEST['login']; ?>" autocomplete="off" />

	<div class="user-pass1-wrap">

		<div class="wp-pwd">
			<div class="password-input-wrapper form-group">
				<input type="password" data-reveal="1" data-pw="<?php echo $_REQUEST['key']; ?>" name="pass1" id="pass1" class="input password-input form-control" size="24" value="" autocomplete="off" aria-describedby="pass-strength-result" />
					<label style="height: 50px;" for="pass1">New password *</label>
					<div id="errorMessage" style="display: none; color: rgb(169, 68, 66);"></div>
				<!--<span class="button button-secondary wp-hide-pw hide-if-no-js">-->
				<!--	<span class="dashicons dashicons-hidden"></span>-->
				</span>
			</div>
			<div class="form-group">
             <input type="password" class="form-control" id="confirm_password" name="confirm_password" value="" >
            <label style="height: 50px;" for="password">Confirm Password *</label>
             <div id="errorMessage_con_password" style="display: none; color: rgb(169, 68, 66);"></div>
            </div>
		</div>
	</div>

	<p class="description indicator-hint">Hint: The password should be at least six characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).</p>
	<br class="clear" />

		<input type="hidden" name="rp_key" value="<?php echo $_REQUEST['key']; ?>" />
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="btn btn-raised btn-primary" value="Reset Password" /></p>
</form>
</div>

<script>
    $(function(){
        $('#resetpassform').submit(function(e){
            length = $('#pass1').val().length;
            if(length==0){
                $('#errorMessage').html('This field is required.');
                $('#errorMessage').show();
                return false;
                e.preventDefault();
            }else if(length<=5){
                $('#errorMessage').html('Password length should be atleast six characters');
                $('#errorMessage').show();
                return false;
                e.preventDefault();
            }else if(length>6){
                $('#errorMessage').hide();
            }
            
            confirm_length = $('#confirm_password').val().length;
            if(confirm_length==0){
                $('#errorMessage_con_password').html('This field is required.');
                $('#errorMessage_con_password').show();
                return false;
                e.preventDefault();
            }else{
                $('#errorMessage').hide();
            }
        
            if ($('#pass1').val() != $('#confirm_password').val()) {
                $("#errorMessage_con_password").html('Please enter the same password');
                $("#errorMessage_con_password").show();
                return false;
                e.preventDefault();
            }else {
                $("#errorMessage_con_password").hide();
                return true; 
            }
        });
        $('#pass1,#confirm_password').blur(function(e){
            validate(e);
        });
        
        function validate(e){
            length = $('#pass1').val().length;
            if(length==0){
                $('#errorMessage').html('This field is required.');
                $('#errorMessage').show();
                return false;
                e.preventDefault();
            }else if(length<=5){
                $('#errorMessage').html('Password length should be atleast six characters');
                $('#errorMessage').show();
                return false;
                e.preventDefault();
            }else if(length>6){
                $('#errorMessage').hide();
            }
            
            confirm_length = $('#confirm_password').val().length;
            if(confirm_length==0){
                $('#errorMessage_con_password').html('This field is required.');
                $('#errorMessage_con_password').show();
                return false;
                e.preventDefault();
            }else{
                $('#errorMessage').hide();
            }
        
            if ($('#pass1').val() != $('#confirm_password').val()) {
                $("#errorMessage_con_password").html('Please enter the same password');
                $("#errorMessage_con_password").show();
                return false;
                e.preventDefault();
            }else {
                $("#errorMessage_con_password").hide();
                return true; 
            }
        }
    });
</script>
