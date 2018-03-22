<div id="register-form" class="widecolumn">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php _e( 'Register', 'personalize-login' ); ?></h3>
    <?php endif; ?>
    
    <?php if ( count( $attributes['errors'] ) > 0 ) : ?>
    <?php foreach ( $attributes['errors'] as $error ) : ?>
        <p>
            <div class="alert alert-callout alert-danger col-md-offset-3 col-md-6"><?php echo urldecode($error); ?></div>
        </p>
    <?php endforeach; ?>
    <?php endif; ?>
    
    <section class="section-account">

    <div class="card-body">
    <div class="row">
    <div class="col-md-offset-3 col-md-6 card card-tiles style-default-light">
    <br/>
    <span class="text-center text-lg text-bold text-primary">REGISTER</span>
    <br/><br/>
    
    <!--<form id="signupform" class="form floating-label form-validate" action="<?php echo wp_registration_url(); ?>" method="post">-->
        <form id="signupform" class="form floating-label form-validate" action="<?php echo home_url().'/register'; ?>" method="post">
        <div class="form-group">
            <input type="text" name="email" id="email" class="form-control">
            <label style="height: 50px;" for="email"><?php _e( 'Email', 'personalize-login' ); ?> <strong>*</strong></label>
            <span id="errorMessage" >Please enter a valid email address</span>
        </div>
        
        <div class="form-group">
            <input type="text" class="form-control" id="hotel_name" name="hotel_name" class="form-control" required>
            <label for="hotel_name"><?php _e( 'Business Name', 'personalize-login' ); ?> <strong>*</strong></label>
        </div>
        
        <div class="form-group">
            <input type="text" name="first_name" id="first-name" class="form-control">
            <label for="first_name"><?php _e( 'First name', 'personalize-login' ); ?> </label>
        </div>
        
        <div class="form-group">
            <input type="text" name="last_name" id="last-name" class="form-control">
            <label for="last_name"><?php _e( 'Last name', 'personalize-login' ); ?></label>
        </div>
 
        <p class="form-row">
            <?php _e( 'Note: The link will be sent to your email address to set the password.', 'personalize-login' ); ?>
        </p>
 
        <div class="row">
            <div class="col-xs-3 text-right">
                <input class="btn btn-primary btn-raised register-button" type="submit" name="wp-submit" id="wp-submit" value="<?php _e( 'Register', 'personalize-login' ); ?>">
            </div><!--end .col -->
            <div class="col-xs-3 text-right">
                <input class="btn btn-primary btn-raised button_link" type="button" name="wp-cancel" id="wp-cancel" data-link="<?php echo home_url()."/login"; ?>" value="<?php _e( 'Cancel', 'personalize-login' ); ?>">
            </div>
        </div>
    </form>
    </div><!--end .col -->
    </div><!--end .row -->
    </div><!--end .card-body -->
    
    </section>
</div>
<script>
$(document).ready(function(e) {
  $("#errorMessage").css({'display':'none', 'color':'#a94442'});
    $("#signupform").submit(function(){
       var sEmail = $('#email').val();
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
        
        if ($('#password').val() =="" ) {
            $("#errorMessage_password").css({'display':'block'});
            return false;
            }
        else {
           $("#errorMessage_password").css({'display':'none'});
            }    
         if($('#confirm_password').val() =="" ) 
            {
             $("#errorMessage_con_password").css({'display':'block'});
            return false; 
            }else {
           $("#errorMessage_con_password").css({'display':'none'});
            } 
        
            if ($('#password').attr('value') != $('#confirm_password').attr('value')) {
            $("#errorMessage_con_password").css({'display':'block'});
            return false;
            }
            else {
            return true; 
            }
        
    });

    $("#email").blur(function(e){
            Email_Validate(e);
        });
        
        $("#email").keyup(function(e){
            Email_Validate(e);
        });
    
    
    function Email_Validate(e){
        var sEmail = $('#email').val();
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
