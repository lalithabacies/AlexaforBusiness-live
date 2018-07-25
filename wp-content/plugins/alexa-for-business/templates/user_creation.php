<?php
if($_REQUEST['errors']){
    echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$_REQUEST['errors'].'</div>';
}
if($_REQUEST['userid']){
    $users = get_userdata($_REQUEST['userid']);
    $business_name = get_user_option('business_name', $users->id);

    $button= 'Update';
    $pwd_mandatory="";
    $action_for = "update";
    $title = "Update User";
    $readonly = "readonly";
    $role = $users->roles[0];
}else{
    $button= 'Register';
    $pwd_mandatory="*";
    $action_for = "insert";
    $title = "Create User";
    $readonly="";
    $role = 'subscriber';
}
?>
<section class="section-account">

    <div class="card-body" style="padding-left: 0;">
     <div class="row">
      <div class="col-md-6 card card-tiles style-default-light">
       <br/>
       <span class="text-center text-lg text-bold text-primary"><?php echo $title; ?></span>
       <br/><br/>
       <!--<form class="form floating-label" action="../../html/dashboards/dashboard.html" accept-charset="utf-8" method="post">-->
        <form id="registerform" class="form floating-label form-validate" name="registerform" action="<?php echo admin_url('admin-post.php') ?>" method="post">
         <div class="form-group">
             <input type="text" class="form-control" id="user_login" name="user_login" value="<?php echo $users->user_login?>" <?php echo $readonly; ?> required>
             <label for="user_login">Username *</label>
         </div>
         <div class="form-group">
             <input type="text" class="form-control" id="email" name="email" value="<?php echo $users->user_email?>" <?php echo $readonly; ?>>
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
         
         <!--<div class="form-group">
             <input type="password" class="form-control" id="password" name="password" value="" >
                <label style="height: 50px;" for="password">Password <?php echo $pwd_mandatory; ?></label>
             <div id="errorMessage_password" style="display: none; color: rgb(169, 68, 66);">Please enter the password</div>
         </div>
         <div class="form-group">
             <input type="password" class="form-control" id="confirm_password" name="confirm_password" value="" >
            <label style="height: 50px;" for="password">Confirm Password <?php echo $pwd_mandatory; ?></label>
             <div id="errorMessage_con_password" style="display: none; color: rgb(169, 68, 66);">Please enter the same password</div>
         </div>-->
         <div class="form-group">
            <?php
            $user = wp_get_current_user();
            $current_user_role = $user->roles[0];
            $roles = wp_roles()->get_names();
            $busi_=$busi_sel="";
            $business_names = array();
            
            if($current_user_role == 'administrator'){
                unset($roles['administrator']);
                $busi_="";
                $busi_sel="none";
                
                $users_business = get_users(array(
                    'meta_key'     => 'business_name',
                ));
                
                foreach($users_business as $user){
                    if(get_user_meta($user->ID,'business_name',true)){
                        $business_names[$user->ID]=get_user_meta($user->ID,'business_name',true);
                    }
                }
            
            }else if($current_user_role == 'subscriber'){
                unset($roles['administrator']);
                unset($roles['subscriber']);
                $busi_="none";
                $busi_sel="";
                
                if(get_user_meta(get_current_user_id(),'business_name',true)){
                    $business_names[get_current_user_id()]=get_user_meta(get_current_user_id(),'business_name',true);
                }
            }
            ?>
            <select name="role" class="form-control"  id="role">
                <?php
                foreach($roles as $key=>$_role){
                    $_sel = ($key == $role)?"selected":"";
                    $_dis = (!empty($_REQUEST['userid']) && $key != $role)?"disabled":"";
                    print"<option value='".$key."' ".$_sel." ".$_dis.">".$_role."</option>";
                }
                ?>
            </select>
            <label for="role">Role *</label>
        </div>
        <div class="form-group hotel_field" id="hotel_name_field" style='display:<?php echo $busi_?>' >
            <input type="text" class="form-control" id="hotel_name" name="hotel_name" required value="<?php echo  $business_name; ?>" <?php echo $readonly; ?>>
            <label for="hotel_name">Business Name *</label>
            <input type="hidden" class="form-control" id="old_hotel_name" name="old_hotel_name" value="<?php echo  $business_name; ?>">
        </div>
        <div class="form-group" id="hotel_names_field" style='display:<?php echo $busi_sel?>' >
            <select class="form-control" id="hotel_names" name="hotel_names">
                <?php
                foreach($business_names as $userid=>$business_name){
                    $_sel = trim(get_the_author_meta( 'business_name_belongs', $_REQUEST['userid'])) == trim($business_name)?"selected":"";
                    ?>
                    <option value="<?php echo $userid."_@_".$business_name?>" <?=$_sel?>><?php echo $business_name?></option>
                    <?php
                }
                ?>
            </select>    
            <label for="hotel_name">Hotel Name *</label>
        </div>
        <div class="form-group hotel_field" style='display:<?php echo $busi_?>' >
            <textarea class="form-control" name="business_address" id="business_address"><?php echo trim(get_user_meta($_REQUEST['userid'],'business_address',true)); ?></textarea>
            <label for="business_address">Business Address</label>
        </div>
        <div class="form-group hotel_field" style='display:<?php echo $busi_?>' >
            <input type="number" class="form-control" id="business_phone" name="business_phone" value="<?php echo trim(get_the_author_meta( 'business_phone', $_REQUEST['userid'])); ?>">
            <label for="business_phone">Business Phone</label>
        </div>
        
        <br/>
        <input type="hidden" name="redirect_to" value="<?php echo get_home_url().'/user-list/'?>" />
        <input type="hidden" name="action" value="user_register" />
        <input type="hidden" name="action_for" value="<?php echo $action_for; ?>" />
        <input type="hidden" name="userid" value="<?php echo $_REQUEST['userid'];?>">

        <input class="btn btn-primary btn-raised" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $button?>">
        <input class="btn btn-danger btn-raised button_link" data-link="<?php echo home_url().'/user-list' ?>" type="button" name="wp-cancel" id="wp-cancel" value="Cancel">

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
            $("#errorMessage").html('Please enter a valid email address12');
            $("#errorMessage").css({'display':'block'});
            $('.email>label:after').css({'background-color': '#0aa89e'})
            return false;
            e.preventDefault();
        }
        else {
              $("#errorMessage").css({'display':'none'});
        }
        
        /*length = $('#password').val().length;
        if(length==0 && !$('#userid').val()){
            $('#errorMessage_password').html('This field is required.');
            $('#errorMessage_password').show();
            return false;
            e.preventDefault();
        }else if(length<=5){
            $('#errorMessage_password').html('Password length should be atleast six characters');
            $('#errorMessage_password').show();
            return false;
            e.preventDefault();
        }else if(length>6){
            $('#errorMessage_password').hide();
        }
        
        confirm_length = $('#confirm_password').val().length;
        if(confirm_length==0 && !$('#userid').val()){
            $('#errorMessage_con_password').html('This field is required.');
            $('#errorMessage_con_password').show();
            return false;
            e.preventDefault();
        }else{
            $('#errorMessage').hide();
        }
    
        if ($('#password').val() != $('#confirm_password').val()) {
            $("#errorMessage_con_password").html('Please enter the same password');
            $("#errorMessage_con_password").show();
            return false;
            e.preventDefault();
        }else {
            $("#errorMessage_con_password").hide();
            return true; 
        }*/
            
        });

        $("#email").blur(function(e){
            Email_Validate(e);
        });
        
        /*$("#email").keyup(function(e){
            Email_Validate(e);
        });
        
        $('#password').on('blur',function(e){
            validate(e);
        });*/
    
    
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
    
    function validate(e){
        length = $('#password').val().length;
        if(length==0 && !$('#userid').val()){
            $('#errorMessage_password').html('This field is required.');
            $('#errorMessage_password').show();
            return false;
            e.preventDefault();
        }else if(length<=5){
            $('#errorMessage_password').html('Password length should be atleast six characters');
            $('#errorMessage_password').show();
            return false;
            e.preventDefault();
        }else if(length>6){
            $('#errorMessage_password').hide();
        }
        
        confirm_length = $('#confirm_password').val().length;
        if(confirm_length==0 && !$('#userid').val()){
            $('#errorMessage_con_password').html('This field is required.');
            $('#errorMessage_con_password').show();
            return false;
            e.preventDefault();
        }else{
            $('#errorMessage').hide();
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
    
    $( "#role" ).change(function() {
         var role_val = $(this).val();
            if(role_val == "subscriber")
            {
                //$("#hotel_name_field").show();
                $(".hotel_field").show();
                $("#hotel_names_field").hide();
            }
            else if(role_val == "editor")
            {
                $(".hotel_field").hide();
                $("#hotel_names_field").show();
            }
    });
    <?php 
    if($_REQUEST['userid']){
        ?>
        $( "#role" ).trigger( "change" );
        <?php
    }
    ?>

});
</script>