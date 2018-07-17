<?php
$users = get_userdata(get_current_user_id());

if($_GET['result'] == 'success'){
     echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6"> Profile Setting Is Updated Successfully </div>';
}

?>

<section class="section-account">

    <div class="card-body">
     <div class="row">
      <div class="col-md-offset-3 col-md-6 card card-tiles style-default-light">
       <br/>
       <span class="text-center text-lg text-bold text-primary">Setting</span>
       <br/><br/>
        <form id="registerform" class="form floating-label form-validate" name="registerform" action="<?php echo admin_url('admin-post.php') ?>" method="post">
         <div class="form-group">
             <input type="text" class="form-control" id="email" name="email" value="<?php echo $users->user_email?>" readonly>
             <span id="errorMessage" >Please enter a valid email address</span>
             <label style="height: 50px;" for="email">Email *</label>
         </div>
         <div class="form-group">
             <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $users->first_name?>" required>
             <label for="first_name">First Name *</label>
         </div>
         <div class="form-group">
             <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $users->last_name?>" required>
             <label for="last_name">Last Name *</label>
         </div>
         <div class="form-group">
             <input class="form-control" type="textbox" name="username" id="username" value="<?php echo $users->user_login ?> " required>
             <label for="username">Display Name</label>
        </div>
         <div class="form-group">
             <input type="password" class="form-control" id="password" name="password" value="" >
                <label style="height: 50px;" for="password">Password </label>
             <div id="errorMessage_password" style="display: none; color: rgb(169, 68, 66);">Please enter the password</div>
         </div>
         <div class="form-group">
             <input type="password" class="form-control" id="confirm_password" name="confirm_password" value="" >
            <label style="height: 50px;" for="password">Confirm Password </label>
             <div id="errorMessage_con_password" style="display: none; color: rgb(169, 68, 66);">Please enter the same password</div>
         </div>
         
          <div class="form-group">
              <select class="form-control" name="accounttype" id="accounttype"><option name="trial">Trial</option></select>
              <label for="accounttype">Account Type</label>
        </div>

        <input class="btn btn-primary btn-raised" type="submit" name="wp-submit" id="wp-submit" value="Update">
        <input class="btn btn-raised btn-danger button_link" type="button" name="wp-cancel" id="wp-cancel" value="Cancel" data-link="<?php echo home_url().'/dashboard'?>">
        <input type="hidden" name="user_id" id="user_id" value="<?php echo $users->ID ?>">
        <input type="hidden" name="action" id="action" value="usermenuupdate">
</form>
</div><!--end .col -->
</div><!--end .row -->
</div><!--end .card-body -->

</section>

<script>
$(document).ready(function(e) {
  $("#errorMessage").css({'display':'none', 'color':'#a94442'});
    $("#registerform").submit(function(){
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
            
            
        length = $('#password').val().length;
        
        if(length<=5 && length>0){
            $('#errorMessage_password').html('Password length should be atleast six characters');
            $('#errorMessage_password').show();
            return false;
            e.preventDefault();
        }else if(length>6){
            $('#errorMessage_password').hide();
        }
        
        
        if ($('#password').val() != $('#confirm_password').val()) {
            $("#errorMessage_con_password").html('Please enter the same password');
            $("#errorMessage_con_password").show();
            return false;
            e.preventDefault();
        }else {
            $("#errorMessage_con_password").hide();
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
        
        length = $('#password').val().length;
        
        if(length<=5 && length>0){
            $('#errorMessage_password').html('Password length should be atleast six characters');
            $('#errorMessage_password').show();
            return false;
            e.preventDefault();
        }else if(length>6){
            $('#errorMessage_password').hide();
        }
        
        confirm_length = $('#confirm_password').val().length;
      
    
        if ($('#password').val() != $('#confirm_password').val()) {
            $("#errorMessage_con_password").html('Please enter the same password');
            $("#errorMessage_con_password").show();
            return false;
            e.preventDefault();
        }else {
            $("#errorMessage_con_password").hide();
            return true; 
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