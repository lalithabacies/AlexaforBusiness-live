<?php
/**
 * Plugin Name:Alexa-for-business
 * Plugin URI: http://abacies.com
 * Description: Adds the functionality and api's of alexa for business.
 * Version: 1.1.0
 * Author: Abacies
 * Author URI: http://abacies.com
 * License: Abacies
 */


/**
 * Including the JS files to this plugin
 *
 */
show_admin_bar(false);
add_action('wp_enqueue_scripts','add_jquery_plugin');
function add_jquery_plugin(){
    wp_enqueue_script( 'jquery','https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
    wp_enqueue_script( 'jquery-1.12.4','https://code.jquery.com/jquery-Notification1.12.4.js');
    wp_enqueue_script( 'jquery-1.12.1','https://code.jquery.com/ui/1.12.1/jquery-ui.js');
    
    wp_enqueue_style( 'css-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script( 'jquery-dataTables', 'https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js');
    wp_enqueue_style( 'css-dataTables', 'https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css');
    wp_enqueue_script( 'select-script', get_template_directory_uri() . '/js/libs/select2/select2.min.js','');
}

add_action('wp_enqueue_scripts','alexa_script');
function alexa_script() {
    wp_enqueue_script( 'a4b', plugins_url( '/js/a4b.js', __FILE__ ));
}


function get_username(){
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'administrator'){
        $iam_username = $current_user->user_email;
    }else{
        $args=array(
        'meta_key'=>'user_belongs_to',
        'meta_value'=>get_current_user_id()
        );
        $users = get_users($args);
        $iam_username = $users->user_email;
    }
    return $iam_username;
}

/**
 * Shortcode for create the New User
 * Form has Hotelname and username fields
 * Insertion Form
 * 
 */

add_shortcode('iam_users_form','new_user');
function new_user(){

    if($_REQUEST['UserName']){
        $user = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_users',json_encode(array('UserName'=>$_REQUEST['UserName']))));

        $OldUserName = $_REQUEST['UserName'];
        $UserName    = $user->User->UserName;
        $Path        = substr($user->User->Path,1,-1);
        $action_usersubmit = 'update_user';
    }else{
        $OldUserName = '';
        $UserName    = '';
        $Path        = '';
        $action_usersubmit = 'create_user';
    }
    
    echo '<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="col-md-offset-1 col-md-10 form create_user form create_user" action="'.admin_url('admin-ajax.php').'" method="POST">

    <div class="form-group floating-label uhotelname">
    <input class="form-control textbox" type="textbox" name="Path" id="Path" value="'.$Path.'">
    <label for="Path">Business Name</label>
    </div>

    <div class="form-group floating-label uusername">
    <input class="form-control" type="textbox" name="UserName" id="UserName" required value="'.$UserName.'"><input type="hidden" name="OldUserName" id="OldUserName" value="'.$OldUserName.'">
    <label for="UserName">User Name</label>
    </div>
    <input type="hidden" name="action" value="'.$action_usersubmit.'">
    <input class="btn btn-raised btn-primary" type="Submit" value="submit"/>
    </form></div>
    ';
}

/**
 * Api For update the user to AWS
 * After that redirecting to the List Users page
 */

add_action('admin_post_update_user','update_user_by_username');
function update_user_by_username(){
    $data['UserName']   =$_POST['OldUserName'];
    $data['NewPath']    =$_POST['Path'];
    $data['NewUserName']=$_POST['UserName'];
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_users',json_encode($data));
    wp_redirect(get_home_url().'/view_iamusers/');
}

/**
 * Redirecting to the Update User Form with POST values
 * 
 */

add_action('admin_post_edit_user','edit_user_form');
function edit_user_form(){
    wp_redirect(add_query_arg('user_value',$_POST,get_home_url().'/iam_users_form/'));
}

/**
 * ShortCode for list the users 
 *
 */

add_shortcode('all_iamusers','view_iamusers');
function view_iamusers(){

    $data=json_decode(doCurl_GET('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/list_users'));
    $content='<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="users" action="'.admin_url('admin-post.php').'" method="POST"><table class="table no-margin"><tr><th colspan="5" align="center">';
    $content.='<input type="button" name="delete_user" value="Delete Users" class="btn btn-raised btn-primary delete_users">';
    $content.='</th></tr>';
    $content.='<tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>UserId</th><th>UserName</th><th>Path</th><th>Arn</th><th>Edit</th></tr>';
    $i=0;
    if(!empty($data->Users)){
        foreach($data->Users as $users){
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk'.$i.'" id="chk'.$i.'" class="chkall" value="'.$users->UserName.'"></label></div></td><td>'.$users->UserId.'</td><td>'.$users->UserName.'</td><td>'.substr($users->Path,1,-1).'</td><td>'.$users->Arn.'</td><td><a href="'.get_home_url().'/iam_users_form?UserName='.$users->UserName.'" class="edit_users" data-user_name='.$users->UserName.'>Edit</a></td></tr>';
            $i++;
        }
    }
    $content.='</table>';
    $content.='<input type="hidden" name="action" value="delete_users"><input type="hidden" name="no_of_users" value="'.$i.'"></form>';
    $content.='<form class="form" name="edit_user" action="'.admin_url('admin-post.php').'" method="POST"><input type="hidden" name="edited_user_name" id="edited_user_name" value=""><input type="hidden" name="action" value="edit_user"></form></div>';
    return $content;
}

/**
 * API for create New IAM user's
 *
 */
add_action('admin_post_create_user','new_users');
function new_users(){
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($_POST));
    wp_redirect(get_home_url('').'/view_iamusers/');
}


/* add_action('user_register', 'wp_registration_make_iam', 10, 1 ); 
function wp_registration_make_iam(){
    $userdata=array();
    $userdata['Path'] = '';
    $userdata['UserName'] = $_POST['first_name'];
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($userdata));
} */

/**
 * Deletes the users 
 * 
 */

add_action('admin_post_delete_users','delete_users');
function delete_users(){
    $usernames_to_delete=array();
    for($i=0;$i<=$_POST['no_of_users'];$i++){
        $chk = 'chk'.$i;
        if(!empty($_POST[$chk])){
            $usernames_to_delete[]=$_POST[$chk];
        }
    }
    $usernames_to_delete = json_encode(array('UserName'=>$usernames_to_delete));
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_users',$usernames_to_delete);
    wp_redirect(get_home_url('').'/view_iamusers/');
}

/**
 * This function is being used for both Creation and Updation
 * It has Room Name and Profile Name fields
 * 
 */

add_shortcode('create_room','create_room');
function create_room(){
    //echo '<form class="form create_room form-validate" action="'.admin_url('admin-post.php').'" method="POST"><input type="text" name="test"><input type="hidden" name="action" value="create_room"></form>';
    
    if($_REQUEST['error']){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$_REQUEST['error'].'</div>';
    }
    
    $RoomName = $ProfileName = $readonly = '';
    $purpose  = 'create';
    if(!empty($_REQUEST['RoomName'])){
        //$RoomName       = $_REQUEST['room_value']['edited_room_name'];
        //$ProfileName    = $_REQUEST['room_value']['edited_room_profile_name'];
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_rooms',json_encode(array('RoomName'=>$_REQUEST['RoomName'],'username'=>get_username(),'userid'=>get_current_user_id()))));
        
        $purpose        = "update";
        //$readonly       = "readonly";
        $RoomName    = $data[0]->RoomName;
        $ProfileName = $data[0]->ProfileName;
        $DeviceName  = $data[0]->DeviceName;
    }
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_rooms',json_encode(array('username'=>get_username()))));
    $associated_device = array();
    if($data){
        foreach($data as $room){
            if($room->DeviceName){
            $associated_device[]=$room->DeviceName;
            }
        }
    }
    echo '<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form create_room form-validate" action="'.admin_url('admin-post.php').'" method="POST">

    <div class="form-group floating-label roomname">
    <input class="form-control" type="textbox" name="RoomName" id="RoomName" value="'.$RoomName.'" required style="width:200px;"><input type="hidden" name="OldRoomName" id="OldRoomName" value="'.$RoomName.'">
    <label for="RoomName">Room Name *</label>
    </div>

    <div class="form-group floating-label roomprofile">
    <select class="form-control" name="ProfileName" id="ProfileName" style="width:200px;">';

    //$data=json_decode(doCurl_GET('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/list_room_profile'));
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/list_room_profile',json_encode(array('username'=>get_username()))));
    $options='';
    if(!empty($data)){
        foreach($data as $room_profile_name){
            $_selected = (trim($ProfileName)==trim($room_profile_name))?"selected":"";
            $options.='<option value="'.$room_profile_name.'" '.$_selected.'>'.$room_profile_name.'</option>';
        }
    }
    echo $options;
    
    echo '</select>';
    echo'<label for="ProfileArn">Room Profile *</label></div>';
    echo '<div class="form-group roomdevices">';
    /*echo '<select class="form-control select2-list js-example-basic-multiple" name="DeviceName[]" id="DeviceName" data-placeholder="Select an item" tabindex="-1"  multiple="multiple" required>';*/
    echo '<select class="form-control" name="DeviceName" id="DeviceName" data-placeholder="Select an item" tabindex="-1">';
    $params = ($DeviceName)?json_encode(array('DeviceName'=>$DeviceName)):json_encode(array());
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',$params));
    $options='';
    if(!empty($data)){
        foreach($data as $device_name){
            
            if(trim($device_name->DeviceName)){
                $_selected = (trim($DeviceName)==trim($device_name->DeviceName))?"selected":"";
                $_disabled =in_array(trim($device_name->DeviceName),$associated_device)?($_selected=="selected"?"":"disabled"):"";
                $options.='<option value="">Select Device</option><option value="'.$device_name->DeviceName.'" '.$_selected.' '.$_disabled.'>'.$device_name->DeviceName.'</option>';
            }
        }
    }
    echo $options;
    echo '</select><input type="hidden" name="OldDeviceName" id="OldDeviceName" value="'.$DeviceName.'"><label for="ProfileArn">Devices </label></div>';
    
    echo'<input type="hidden" name="action" value="create_room">
    <input type="hidden" name="purpose" value="'.$purpose.'">
    <input type="hidden" name="username" id="username" value="'.get_username().'">
    <input type="hidden" name="userid" id="userid" value="'.get_current_user_id().'">
    <input class="btn btn-raised btn-primary" type="Submit" value="submit"/>
    <input class="btn btn-raised btn-primary button_link" type="button" value="Cancel" data-link="'.get_home_url().'/list-out-rooms"/>
    </form></div>
    ';
}

/**
 * Shortcode for list out the Rooms List
 * 
 */

add_shortcode('list_rooms','list_rooms');
function list_rooms(){
    if($_REQUEST['success']){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Device has been disassociated successfully.</div>';
    }
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_rooms',json_encode(array('username'=>get_username()))));
    //print_r($data);
    //Form to display and delete the room name
    $content='<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="delete_rooms" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<input class="btn btn-raised btn-primary delete_action_rooms" type="button" name="deleterooms" id="deleterooms" value="Delete Rooms"> <a href="'.get_home_url().'/create_room/'.'" name="createroom" id="createroom"><input class="btn btn-raised btn-primary" type="button" value="Create Room"></a>';
    $content.='<table class="table no-margin"><tr><th><div class="checkbox checkbox-inline checkbox-styled">
                   <label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>Room Name</th><th>Room Type</th><th>Device Name</th><th style="text-align:center;">Remove Device</th><th>Edit</th></tr>';
    
    $i=0;  
    if(!empty($data)){
        foreach($data as $room){
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_room'.$i.'" class="chkall"></label></div><input type="hidden" name="room_name'.$i.'" value="'.$room->RoomName.'"></td><td>'.$room->RoomName.'</td><td>'.$room->ProfileName.'</td><td>'.$room->DeviceName.'</td><td style="text-align:center;"><!--<input type="button" class="btn btn-raised btn-primary removed_device" data-devicename="'.$room->DeviceName.'" data-url="'.home_url().'/list-out-rooms/" name="remove_device" id="remove_device" value="Remove Device">--><i class="fa fa-remove fa-2x" data-devicename="'.$room->DeviceName.'" data-url="'.home_url().'/list-out-rooms/" name="remove_device" id="remove_device"></i></td><td><a href="'.get_home_url().'/create_room?RoomName='.$room->RoomName.'" name="edit_room'.$i.'" id="edit_room'.$i.'" class="edit_rooms" data-room_name="'.$room->RoomName.'" data-room_profile_name="'.$room->ProfileName.'">Edit</a></td></tr>';
            $i++;
        }
    }
    
    $content.='<tr></tr></table>';
    $content.='<input type="hidden" name="action" value="delete_rooms"><input type="hidden" name="no_of_rooms" value="'.$i.'"></form></div>';
    
    $content.='<form class="form" name="dissync_devices" action="'.admin_url('admin-post.php').'" method="POST"><input type="hidden" name="action" value="dissync_devices"><input type="hidden" name="disync_device_name" id="disync_device_name" value=""></form>';
    //Form to update the room name
    /*$content.='<form name="update_room" action="'.admin_url('admin-post.php').'" method="POST"><input type="hidden" name="edited_room_name" id="edited_room_name" value=""><input type="hidden" name="edited_room_profile_name" id="edited_room_profile_name" value=""><input type="hidden" name="action" value="update_room"><input type="hidden" name="purpose" value="update"></form>';*/
    return $content;
}

/**
 * Shortcode for list out the Rooms Profile List
 * 
 */

add_action('admin_post_dissync_devices','dissync_devices');
function dissync_devices(){
    $result=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/disassociate_device_from_room',json_encode(array('DeviceName'=>$_POST["disync_device_name"]))));
    //wp_redirect(home_url().'/list-out-rooms?success=1');
    header("Location: ".get_home_url().'/list-out-rooms?success=1');
    exit;
    
}

add_shortcode('list_room_profile','display_room_profile');
function display_room_profile(){
    
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/list_room_profile',json_encode(array('username'=>get_username()))));
    $content='<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form" name="list_room_profile" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<input class="btn btn-raised btn-primary delete_rp_action" type="button" name="delete_room_profile" value="Delete Room Profile"> <input class="btn btn-raised btn-primary button_link" type="button" name="create_rp" data-link="'.home_url().'/create-room-profile" value="Create Room Profile">';
    $content.='<table class="table no-margin"><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall" class="chkall"></label></div></th><th>Room Profile Name</th><th></th></tr>';
    if(!empty($data)){
        $i=0;
        foreach($data as $room_profilename){
            $link = home_url()."/create-room-profile?rp_name=".$room_profilename;
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_roomprofile'.$i.'" class="chkall"></label></div><input type="hidden" name="roomprofile_name'.$i.'" value="'.$room_profilename.'"></td><td>'.$room_profilename.'</td><td><a href="'.$link.'" data-roomprofile_name="'.$room_profilename.'" class="edit_room_profile">Edit</a></td></tr>';
            $i++;    
        }
    }
    $content.='</table><input type="hidden" name="action" value="delete_room_profile"><input type="hidden" name="no_of_room_profiles" value="'.$i.'"></form>';
    /*$content.='<form class="form col-md-12" name="edit_room_profile" action="'.admin_url('admin-post.php').'" method="POST"><input type="hidden" name="edited_roomprofilename" id="edited_roomprofilename" value=""><input type="hidden" name="action" value="edit_roomprofile"></form></div>';*/
    return $content;
}

/**
 * Redirects the Page to Update form for Room profile with POST values
 * 
 */

add_action('admin_post_edit_roomprofile','edit_roomprofile');
function edit_roomprofile(){
    $room_ = array();
    foreach($_POST as $post_key=>$post_val){
        $room_[$post_key]=str_replace(' ','%20',$post_val);
    }
    wp_redirect(add_query_arg('room_profile',$room_,get_home_url().'/create-room-profile/'));
}

/**
 * Deletes the Room Profile
 * And redirects the page to lists of Room Profile
 */

add_action('admin_post_delete_room_profile','delete_room_profile');
function delete_room_profile(){
    $no_of_roomprofiles_to_delete = $_POST['no_of_room_profiles'];
    $room_names = array();
    for($i=0;$i<$no_of_roomprofiles_to_delete;$i++){
        $chk_room = 'chk_roomprofile'.$i;
        $roomprofile_name = 'roomprofile_name'.$i;
        if($_POST[$chk_room]){
            $room_names[] = $_POST[$roomprofile_name];
        }
    }
    $req_param = json_encode(array('ProfileName'=>$room_names,'userid'=>get_current_user_id()));
    #print_r($req_param);die();
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_room_profile',$req_param);
    //wp_redirect(get_home_url().'/list-out-room-profile/');
    header("Location: ".get_home_url().'/list-out-room-profile/');
    exit;
}

/**
 * Function for redirect the page to update form with POST values
 */

/* add_action('admin_post_update_room','update_room');
function update_room(){
    wp_redirect(add_query_arg('room_value',$_POST,get_home_url().'/create_room/'));
} */

/**
 * Deletes the Room
 * Performs multiple deletion
 */

add_action('admin_post_delete_rooms','delete_rooms');
function delete_rooms(){
    $no_of_rooms_to_delete = $_POST['no_of_rooms'];
    $room_names = array();
    for($i=0;$i<$no_of_rooms_to_delete;$i++){
        $chk_room = 'chk_room'.$i;
        $room_name = 'room_name'.$i;
        if($_POST[$chk_room]){
            $room_names[] = $_POST[$room_name];
        }
    }
    $req_param = json_encode(array('RoomName'=>$room_names,'userid'=>get_current_user_id()));
    //print_r($req_param);die();
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_rooms',$req_param);
    //wp_redirect(get_home_url().'/list-out-rooms/');
    header("Location: ".get_home_url().'/list-out-rooms');
    exit;
}

/**
 * Api for create and update the form values to the AWS
 * And redirect the page to list rooms
 */

add_action('admin_post_create_room','create_room_api');
function create_room_api(){
    if($_POST['purpose'] == 'update'){
        $result = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_rooms',json_encode($_POST)));
    }
    else{
        $result = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_rooms',json_encode($_POST)));
    }
    
    if($result->error){
        if (strpos($result->error, ':') !== false) {
            $error = urlencode(explode(':',$result->error)[1]);
        }else{
            $error = urlencode($result->error);
        }
        wp_redirect(get_home_url().'/create_room/?RoomName='.$_POST['OldRoomName'].'&error='.$error);
    }else{
        //wp_redirect(get_home_url().'/list-out-rooms/');
        header("Location: ".get_home_url().'/list-out-rooms/');
        exit;
    }
}

/**
 * Shortcode for CREATE and UPDATE the Room Profile
 *
 */

add_shortcode('create_room_profile','create_room_profile');
function create_room_profile(){
    if($_REQUEST['error']){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$_REQUEST['error'].'</div>';
    }
    $ProfileName = $Timezone = $Address = $ClientRequestToken = $MaxVolumeLimit = $readonly = '';
    $SetupModeDisabled   = true;
    $PSTNEnabled         = true;
    $action_room_profile = "create_room_profile";
    if(!empty($_REQUEST['rp_name'])){
        $request = json_encode(array('ProfileName'=>trim($_REQUEST['rp_name']),'userid'=>get_current_user_id()));

        $room_profile_data = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_room_profile_info',$request));

        $ProfileName        = $room_profile_data->ProfileName;
        $Timezone           = $room_profile_data->Timezone;
        $Address            = $room_profile_data->Address;
        $DistanceUnit       = $room_profile_data->DistanceUnit;
        $ProfileArn         = $room_profile_data->ProfileArn;
        $TemperatureUnit    = $room_profile_data->TemperatureUnit;
        $WakeWord           = $room_profile_data->WakeWord;
        //$ClientRequestToken = $room_profile_data->ClientRequestToken;
        //$SetupModeDisabled  = $room_profile_data->SetupModeDisabled;
        $MaxVolumeLimit     = $room_profile_data->MaxVolumeLimit;
        //$PSTNEnabled        = $room_profile_data->PSTNEnabled;
        //$readonly           = "readonly";
        $action_room_profile= "update_room_profile";
    }
    $maximumValues = array(60=>6,70=>7,80=>8,90=>9,100=>10);
    
    echo '<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form create_room_profile form-validate" action="'.admin_url('admin-post.php').'" method="POST">

    <div class="form-group floating-label roomprofilename">
    <input class="form-control" class="textbox" type="textbox" name="ProfileName" id="ProfileName" value="'.$ProfileName.'" required '.$readonly.'><input class="form-control" type="hidden" name="OldProfileName" id="OldProfileName" value="'.$ProfileName.'" required>
    <label for="ProfileName ">RoomProfileName *</label>
    </div>';

    /*if(!empty($_REQUEST['rp_name'])){
        echo'<div class="form-group floating-label newroomprofilename"><label for="NewProfileName">New Room Profile Name</label>
        <input class="form-control" type="textbox" name="NewProfileName" id="NewProfileName" value="" required></div>';
    }*/
    $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    echo'<div class="form-group floating-label timezone">
    <select class="form-control" name="Timezone" id="Timezone" required>';
    foreach($tzlist as $tz){
        $_i = ($Timezone == $tz)?"selected":"";
        echo '<option value="'.$tz.'" '.$_i.'>'.$tz.'</option>';
    }
    echo'</select>
    <label for="Timezone">Timezone</label></div>

    <div class="form-group floating-label addres">
    <input class="form-control" type="textbox" name="Address" id="Address" value="'.$Address.'" required><label for="Address">Address * (Format: 123 Sample Street, Seattle, WA 98101)</label></div>  <!-- value="10210 FM-1021, Eagle Pass, KA, IN, 570036" -->';

    $distance_unit_list=array('METRIC','IMPERIAL');
    echo'<div class="form-group floating-label distanceunit">
    <select class="form-control" name="DistanceUnit" style="display:block;">';
    foreach($distance_unit_list as $distance){
        $_i = ($DistanceUnit==$distance)?"selected":"";
        echo '<option value='.$distance.' '.$_i.'>'.$distance.'</option>';
    }
    echo'</select>
    <label for="DistanceUnit">Distance Unit</label></div>';

    $temperature_unit_list = array('FAHRENHEIT','CELSIUS');
    echo'<div class="form-group floating-label temperatureunit">
    <select class="form-control" name="TemperatureUnit">';
    foreach($temperature_unit_list as $temp){
        $_i = ($temp==$TemperatureUnit)?"selected":"";
        echo '<option value="'.$temp.'" '.$_i.'>'.$temp.'</option>';
    }
    echo'</select>
    <label for="TemperatureUnit">Temperature Unit</label></div>';

    $WakeWord_list = array('ALEXA','AMAZON','ECHO','COMPUTER');
    echo'<div class="form-group floating-label wakeword">
    <select class="form-control" name="WakeWord">';
    foreach($WakeWord_list as $wake_word){
        $_i = ($wake_word==$WakeWord)?"selected":"";
        echo '<option value="'.$wake_word.'" '.$_i.'>'.$wake_word.'</option>';
    }
    echo'</select>
    <label for="WakeWord">Wake Word</label></div>';

    /*echo'<label for="ClientRequestToken">Room Profile ID</label>
    <input style="display:block;" class="textbox" type="textbox" name="ClientRequestToken" id="ClientRequestToken" value="'.$ClientRequestToken.'" required><br/>';*/
    
    $boolean_list = array('True','False');
    echo'<div class="form-group floating-label disablesetupmode" style="display:none;">
    <select class="form-control" name="SetupModeDisabled">';
    foreach($boolean_list as $val){
        $_i = ($val==$SetupModeDisabled)?"selected":"";
        echo '<option value="'.$val.'">'.$val.'</option>';
    }
    echo'</select>
    <label for="SetupModeDisabled">Disable Setup Mode</label></div>

    <div class="form-group floating-label maxvolume" >
    <select class="form-control" name="MaxVolumeLimit" id="MaxVolumeLimit">';
    foreach($maximumValues as $max_key=>$max_vol){
        $max_vol_sel = ($MaxVolumeLimit==$max_key)?"selected":"";
        echo '<option value="'.$max_key.'" '.$max_vol_sel.'>'.$max_vol.'</option>';
    }
    echo'</select>
    <label for="MaxVolumeLimit">Maximum Volume *</label></div>

    <div class="form-group floating-label enablecalling" style="display:none;">
    <select class="form-control" name="PSTNEnabled" style="display:block;">';
    foreach($boolean_list as $val){
        $_i = ($val==$PSTNEnabled)?"selected":"";
        echo '<option value="'.$val.'">'.$val.'</option>';
    }
    echo'</select>
    <label for="PSTNEnabled">Enable Calling</label></div>
    <input type="hidden" name="action" value="'.$action_room_profile.'">
    <input type="hidden" name="username" id="username" value="'.get_username().'">
    <input type="hidden" name="userid" id="userid" value="'.get_current_user_id().'">
    <input class="btn btn-raised btn-primary create_room_profile_submit" type="Submit" value="submit"/> <input class="btn btn-raised btn-primary button_link" type="button" value="Cancel" data-link="'.get_home_url().'/list-out-room-profile"/>
    </form></div>
    ';
}

/**
 * API for INSERT the Room profile Data to the AWS
 *
 */

add_action('admin_post_create_room_profile','create_room_profile_api');
function create_room_profile_api(){
    $result = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_room_profile',json_encode($_POST)));
    
     
    if($result->error){
        if (strpos($result->error, ':') !== false) {
            $error = urlencode(explode(':',$result->error)[1]);
        }else{
            $error = urlencode($result->error);
        }
        wp_redirect(get_home_url().'/create-room-profile/?error='.$error);
    }else{
        //wp_redirect(get_home_url().'/list-out-room-profile/');
        header("Location: ".get_home_url().'/list-out-room-profile/');
        exit;
    }
    
}

/**
 * API for UPDATE the Room profile Data to the AWS
 *
 */

add_action('admin_post_update_room_profile','update_room_profile_api');
function update_room_profile_api(){
    $result = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_room_profile',json_encode($_POST)));
    
    if($result->error){
        if (strpos($result->error, ':') !== false) {
            $error = urlencode(explode(':',$result->error)[1]);
        }else{
            $error = urlencode($result->error);
        }
        wp_redirect(get_home_url().'/create-room-profile/?rp_name='.$_POST["OldProfileName"].'&error='.$error);
    }else{
        //wp_redirect(get_home_url().'/list-out-room-profile/');
        header("Location: ".get_home_url().'/list-out-room-profile/');
        exit;
    }
}



/**
 * cURL function to perform the POST method
 *
 */

function doCurl_POST($end_url,$params)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $end_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER =>array('Content-Type: application/json'),
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $params,
  ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
      echo "cURL Error #:" . $err;
  } else {
      return $response;
  }
}

/**
 * cURL function to perform the GET method
 *
 */

function doCurl_GET($end_url){

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $end_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
  ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
      echo "cURL Error #:" . $err;
  } else {
      return $response;
  }
}




/**
 * ShortCode to List out the devices
 * 
 */
add_shortcode('list_devices','list_devices_page');
function list_devices_page(){
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',json_encode(array())));

    print'<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="devices_list" method="POST">';
    /*print'<div class="addtoroom">';
    print"<input class='btn btn-raised btn-primary' type='button' name='add_room' id='add_room' value='Add to Room' disabled>  ";
    print"<select class='form-control' name='actions' id='actions' disabled><option value='' style='display:none;'>Actions</option><option value='remove_room'>Remove from room</option><option value='sync'>Sync devices</option></select>";
    print'</div>';*/
    print"<table class='table no-margin'><tr><!--<th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th>--><th>Serial Number</th><th>Device Name</th><th>Room</th><th>Status</th><th></th></tr>";
    $i=0;
    if($data){
        foreach($data as $devices){
            print"<tr><!--<td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_device".$i."' id='chk_device".$i."' class='chkall' value='".$devices->DeviceName."'></label></div></td>--><td>".$devices->DeviceSerialNumber."</td><td>".$devices->DeviceName."</td><td>".$devices->RoomName."</td><td>".$devices->DeviceStatus."</td><td><a href='".get_home_url('').'/device-form?Serial_number='.$devices->DeviceSerialNumber."'>Edit</a></td></tr>";
            $i++;
        }
    }
    print"<input type='hidden' name='no_of_devices' id='no_of_devices' value='".$i."'></table><input type='hidden' name='action' value='sync_device'><input type='hidden' name='action_url' id='action_url' value='".admin_url('admin-post.php')."'><input type='hidden' name='addroom_url' id='addroom_url' value='".get_home_url()."/add_room_device'></form></div>";
}

/**
 * API to synchronize the particular devices 
 * 
 */ 

add_action('admin_post_sync_device','sync_device');
function sync_device(){
    if(!empty($_POST)){
        $no_of_devices = $_POST['no_of_devices'];
        for($i=0;$i<$no_of_devices;$i++){
            $device_name = $_POST['chk_device'.$i];
        }
    }
    if($_POST['actions'] == 'sync'){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/start_device_sync',json_encode(array('DeviceName'=>$device_name))));
    }else if($_POST['actions'] == 'remove_room'){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/disassociate_device_from_room',json_encode(array('DeviceName'=>$device_name))));
    }

    wp_redirect(get_home_url('').'/devices/');
}

/**
 * Redirects this page from Devices List
 * This form Links device and room 
 * 
 */ 

add_shortcode('add_room_device','room_list_to_assiciate_device');
function room_list_to_assiciate_device(){
    if(!empty($_POST)){
        $no_of_devices = $_POST['no_of_devices'];
        for($i=0;$i<$no_of_devices;$i++){
            $device_name = $_POST['chk_device'.$i];
        }
    }
    
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_rooms',json_encode(array())));

    $content='<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form" name="addroom" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<table class="table no-margin"><tr><th></th><th>Room Name</th><th>Room Type</th></tr>';
    
    $i=0;  
    if(!empty($data)){
        foreach($data as $room){
            $check = ($device_name==$room->DeviceName)?"checked":"";
            $content.='<tr><td><div class="radio radio-styled"><label><input type="radio" name="sel_room" '.$check.' value="'.$room->RoomName.'"></label></div></td><td>'.$room->RoomName.'</td><td>'.$room->ProfileName.'</td></tr>';
            $i++;
        }
    }
    
    $content.='<input type="hidden" name="DeviceName" value="'.$device_name.'"><input type="hidden" name="action" value="add_device_room"></table><div class="cansub-div"><div class="can-btn"><a href="'.get_home_url().'/devices/'.'"><button class="btn btn-block ink-reaction btn-danger" name="can">Cancel</button></a></div><div class="sub-btn"><button class="btn btn-block ink-reaction btn-info" type="submit">Add</button></div></div>';
    $content.='</form></div>'; 

    return $content;
}

/**
 * Associate device with Room using API
 * 
 */ 

add_action('admin_post_add_device_room','add_device_room'); 
function add_device_room(){
    $object = ['RoomName'=>$_POST['sel_room'],'DeviceName'=>$_POST['DeviceName']];
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_room_to_device',json_encode($object)));
    wp_redirect(get_home_url('').'/devices/');
}


/**
 * ShortCode to have the form for Update the Device Name
 * 
 */ 
add_shortcode('device_form','device_form_page'); 
function device_form_page(){
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',json_encode(array('Serial_number'=>$_REQUEST['Serial_number']))));
    if($data){
        foreach($data as $devices){
            print'<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form" name="device_form" action="'.admin_url('admin-post.php').'" method="POST">';
            print"<div class='form-group floating-label'><input class='form-control' type='textbox' name='serial_number' id='serial_number' value='".$devices->DeviceSerialNumber."' required readonly><label for='serial_number'>Serial Number *</label></div>";
            print"<div class='form-group floating-label'><input class='form-control' type='textbox' name='DeviceName_New' id='DeviceName_New' value='".$devices->DeviceName."' required><label for='DeviceName_New'>Device Name *</label><input type='hidden' name='DeviceName_Old' id='DeviceName_Old' value='".$devices->DeviceName."' ></div>";
            print"<div class='form-group floating-label'><input class='form-control' type='textbox' name='room' id='room' value='".$devices->RoomName."' readonly><label for='room'>Room</label></div>";
            print"<div class='form-group floating-label'><input class='form-control' type='textbox' name='status' id='status' value='".$devices->DeviceStatus."' readonly><label for='status'>Status</label></div>";
            print'<input class="btn btn-block ink-reaction btn-info" type="submit" name="device_submit" id="device_submit" value="submit"><input type="hidden" name="action" value="device_form"></form></div>';
        }
    }
}

/**
 * Action for UPDATE the Device name to the AWS
 * 
 */

add_action('admin_post_device_form','update_device_form'); 
function update_device_form(){
    //$data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_device',json_encode(array('DeviceName_New'=>$_POST['DeviceName_New'],'DeviceName_Old'=>$_POST['DeviceName_Old']))));
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_device',json_encode(array('DeviceName_New'=>$_POST['DeviceName_New'],'Serial_Number'=>$_POST['serial_number']))));
    wp_redirect(get_home_url('').'/devices/');
}

/**
 * Form to create the Request
 * Page
 */

add_shortcode('create_request','create_request');
function create_request(){
    if($_POST){
    if($_POST['action_for'] == 'update'){
        $data=doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_delete',json_encode(array('request_name'=>[$_POST['RequestName']],'userid'=>get_current_user_id())));
    }
    $Form_data['request_name']   = $_POST['RequestName'];
    $Form_data['Status']        = $_POST['Status'];
    $Form_data['RequestType']   = $_POST['RequestType'];
    $Form_data['Check_Email']   = (string)!empty($_POST['email_chk'])?'1':'0';
    $Form_data['Check_Text']    = (string)!empty($_POST['text_chk'])?'1':'0';
    $Form_data['Check_Call']    = (string)!empty($_POST['call_chk'])?'1':'0';
    $Form_data['EmailID']       = $_POST['EmailID'];
    $Form_data['TextNumber']    = $_POST['TextNumber'];
    $Form_data['CallNumber']    = $_POST['CallNumber'];
    //$Form_data['NotificationTemplate']    =$_POST['notification_Temp'];
    //$Form_data['Level']         = $_POST['count'];
    $Form_data['Conversation'] = array();
    
    $temp=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>$_POST['notification_Temp'],'username'=>get_username(),'userid'=>get_current_user_id()))));
    $Form_data['NotificationTemplate'] = $temp->Items[0]->template;

    for($i=1;$i<=$_POST['count'];$i++){
        $Form_data['Q'.$i] = addslashes($_POST['guest_request'.$i]);
        $Form_data['A'.$i] = addslashes($_POST['alexa_response'.$i]);
        $Form_data['Conversation'][]=[$Form_data['Q'.$i],'User'];
        if($i == count($_POST['count'])){
            $res = 'Alexa';
        }else{
            $res = 'EndConversation';
        }
        $Form_data['Conversation'][]=[$Form_data['A'.$i],$res];
    }
    $Form_data['username']=get_username();
    $Form_data['userid']  =get_current_user_id();
    //print_r(json_encode($Form_data));die();
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_insert',json_encode($Form_data)));
    if($data->error){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$data->error.'</div>';
    }else{
        wp_redirect(get_home_url('').'/requests-list/');
    }
    
    }
    
    $action_for   = "insert";
    $add_request_button_value = "+";
    $noti_display = "none";
    if($_REQUEST['RequestName']){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/request_info',json_encode(array('request_name'=>$_REQUEST['RequestName'],'userid'=>get_current_user_id()))));
        //print_r($data);
        $request_name = $data[0]->request_name;
        $Status       = $data[0]->Status;
        $RequestType  = $data[0]->RequestType;
        $request_name = $data[0]->request_name;
        $EmailID      = $data[0]->EmailID;
        $TextNumber   = $data[0]->TextNumber;
        $CallNumber   = $data[0]->CallNumber;
        $notification_Temp   = $data[0]->NotificationTemplate;
        #$Level        = $data[0]->Level;
        $Conversation = $data[0]->Conversation;
        $guest_request1 = $Conversation[0][0];  
        $alexa_response1= $Conversation[1][0];
        $readonly     = "readonly";
        $action_for   = "update";
        $email_chk_ = !empty($EmailID)?"checked":"";
        $text_chk_  = !empty($TextNumber)?"checked":"";
        $call_chk_  = !empty($CallNumber)?"checked":"";
        $add_request_button_value = (count($Conversation) == 2)?"+":"-";
        $noti_display = ($RequestType=="General Information")?"none":"";
        
    }
    echo '<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form create_user create_request form-validate" action="'.home_url().'/create-request" method="POST" name="request">';
    echo '<div class="form-group floating-label requestname">
    <input class="form-control" type="textbox" name="RequestName" id="RequestName" value="'.$request_name.'" required '.$readonly.'>
    <label for="RequestName ">Request Name *</label></div>';
    echo '<div class="form-group floating-label status">
    <select class="form-control" name="Status" id="Status"><option value="active">Active</option><option value="inactive">InActive</option></select><label for="Status ">Status *</label></div>';
    
    $request_types=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_request_types',json_encode(array('request_type'=>''))));
    echo '<div class="form-group floating-label requesttype">
    <select class="form-control" name="RequestType" id="RequestType"><!--<option value="general_information">General Information</option><option value="service_request">Service Request</option>-->';
    
    
    foreach($request_types->Items as $type){
        $_sel = ($RequestType == $type->request_type)?"selected":"";
        echo'<option value="'.$type->request_type.'" '.$_sel.'>'.$type->request_type.'</option>';
    }
    echo'</select><label for="RequestType">Request Type *</label></div>';

    echo '<div class="form-group floating-label guestrequest guestrequest1">
    <input class="form-control" type="textbox" name="guest_request1" id="guest_request1" value="'.$guest_request1.'" required><label for="guest_request1">Guest Request *</label></div>';
    echo '<div class="form-group floating-label alexaresponse alexaresponse1">
    <textarea class="form-control" name="alexa_response1" id="alexa_response1" style="width:400px;" required>'.$alexa_response1.'</textarea>
    <label for="alexa_response1">Alexa Response *</label>
    </div>';
    echo '<div class="form-group floating-label">
    <input type="button" name="add_request1" id="add_request1" class="btn btn-block ink-reaction btn-info add_request" data-level="1" value="'.$add_request_button_value.'"></div>';
    if($Conversation){
        $j=2;
        for($i=2;$i<=count($Conversation);$i++){
            //$add_request_button_value = ($i == $Level)?"+":"-";
            //$Q = 'Q'.$i;
            
            
            $result  = $Conversation[$i][0];
            $who     = $Conversation[$i][1];
            if($who == 'User'){
                $style= 'style="margin-left:'.($j-1).'0px;"';
                echo '<div class="form-group floating-label guestrequest guestrequest'.$j.'" '.$style.'>
            <input class="form-control" type="textbox" name="guest_request'.$j.'" id="guest_request'.$j.'" value="'.$result.'" required><label for="guest_request'.$j.'">Guest Request *</label></div>';
            }
            
            //$A = 'A'.$i;
            
            /*echo '<div class="form-group floating-label guestrequest guestrequest'.$i.'" style="margin-left:'.($i-1).'0px;">
            <input class="form-control" type="textbox" name="guest_request'.$i.'" id="guest_request'.$i.'" value="'.$guest_request.'" required><label for="guest_request'.$i.'">Guest Request *</label></div>';
            echo '<div class="form-group floating-label alexaresponse alexaresponse'.$i.'" style="margin-left:'.($i-1).'0px;">
            <textarea class="form-control" name="alexa_response'.$i.'" id="alexa_response'.$i.'" style="width:400px;" >'.$alexa_response.'</textarea>
            <input type="button" name="add_request'.$i.'" id="add_request'.$i.'" class="btn btn-block ink-reaction btn-info add_request" data-level="'.$i.'" value="'.$add_request_button_value.'"><label for="alexa_response'.$i.'">Alexa Response *</label></div>';  */
            
            
            if($who == 'Alexa' || $who == 'EndConversation'){
            #$add_request_button_value = ($who == 'EndConversation')?"+":"-";
            $add_request_button_value = count($Conversation)/2==$j?"+":"-";
            echo '<div class="form-group floating-label alexaresponse alexaresponse'.$j.'" '.$style.'>
            <textarea class="form-control" name="alexa_response'.$j.'" id="alexa_response'.$j.'" style="width:400px;" required>'.$result.'</textarea>
            <label for="alexa_response'.$j.'">Alexa Response *</label>
            </div>';
            if($j<3){
            echo '<div class="form-group floating-label">
            <input type="button" name="add_request'.$j.'" id="add_request'.$j.'" class="btn btn-block ink-reaction btn-info add_request" data-level="'.$j.'" value="'.$add_request_button_value.'" '.$style.'></div>';
            }
            $j++;
            }
            
    
        }
        echo '<input type="hidden" name="count" id="count" value="'.($j-1).'">';
    }else{
        echo '<input type="hidden" name="count" id="count" value="1">';
    }

    
    echo '<div class="forlevel2"></div></br>';                    
    
    echo '<div class="notification" style="display:'.$noti_display.';"><label for="alexa_response ">Notification</label>
    <table class="table no-margin"><tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="email_chk" id="email_chk" value="email" '.$email_chk_.'></label></div></td><td>Email:</td><td><input class="form-control" type="textbox" name="EmailID" id="EmailID" value="'.$EmailID.'" required></td></tr>
    <tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="text_chk" id="text_chk" value="text" '.$text_chk_.'></td><td>Text:</td><td><input class="form-control" type="textbox" name="TextNumber" id="TextNumber" value="'.$TextNumber.'" required></label></div></td></tr>
    <tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="call_chk" id="call_chk" value="call" '.$call_chk_.'></label></div></td><td>Call:</td><td><input class="form-control" type="textbox" name="CallNumber" id="CallNumber" value="'.$CallNumber.'" required></td></tr>
    <tr><td colspan="3"><div style="color:#a94442;display:none;" id="noti_req">Any one Notification is required.</div></td></tr>
    </table></div>'; 
    /*echo '<div class="form-group floating-label notificationtemplate"><input class="form-control" type="textbox" name="notification_Temp" id="notification_Temp" value="'.$notification_Temp.'" required><label for="notification_template ">Notification Template</label></div>';  */
    echo '<div class="form-group floating-label notificationtemplate" style="display:'.$noti_display.';"><select name="notification_Temp" id="notification_Temp" class="form-control"><option>Select Template</option>';
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>'','username'=>get_username()))));
    if(!empty($data->Items)){
        foreach($data->Items as $temp){
        $_sel = ($notification_Temp == $temp->template)?"selected":"";    
        $tempName = explode('_@_',$temp->template_name)[1];
        echo '<option value="'.$tempName.'" '.$_sel.'>'.$tempName.'</option>';
        }
    }
    echo'</select><label for="notification_Temp ">Notification Template</label></div>';
    
    echo '<input class="btn btn-raised btn-primary button_link" type="button" name="request_can" id="request_can" data-link="'.get_home_url().'/requests-list/" value="Cancel"> <input class="btn btn-raised btn-primary" type="submit" name="sub" id="sub" value="Submit"><!--<input type="hidden" name="action" value="insert_request">--><input type="hidden" name="action_for" id="action_for" value="'.$action_for.'">';                    
    echo '</form></div>';        
}

/**
 * Form to Insert the Request to DynamoDB
 * And Redirects to the List of the Pages
 */

/*add_action('admin_post_insert_request','insert_request');
function insert_request(){
    if($_POST['action_for'] == 'update'){
        $data=doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_delete',json_encode(array('request_name'=>[$_POST['RequestName']],'userid'=>get_current_user_id())));
    }
    $Form_data['request_name']   = $_POST['RequestName'];
    $Form_data['Status']        = $_POST['Status'];
    $Form_data['RequestType']   = $_POST['RequestType'];
    $Form_data['Check_Email']   = (string)!empty($_POST['email_chk'])?'1':'0';
    $Form_data['Check_Text']    = (string)!empty($_POST['text_chk'])?'1':'0';
    $Form_data['Check_Call']    = (string)!empty($_POST['call_chk'])?'1':'0';
    $Form_data['EmailID']       = $_POST['EmailID'];
    $Form_data['TextNumber']    = $_POST['TextNumber'];
    $Form_data['CallNumber']    = $_POST['CallNumber'];
    //$Form_data['NotificationTemplate']    =$_POST['notification_Temp'];
    //$Form_data['Level']         = $_POST['count'];
    $Form_data['Conversation'] = array();
    
    $temp=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>$_POST['notification_Temp'],'username'=>get_username(),'userid'=>get_current_user_id()))));
    $Form_data['NotificationTemplate'] = $temp->Items[0]->template;

    for($i=1;$i<=$_POST['count'];$i++){
        $Form_data['Q'.$i] = addslashes($_POST['guest_request'.$i]);
        $Form_data['A'.$i] = addslashes($_POST['alexa_response'.$i]);
        $Form_data['Conversation'][]=[$Form_data['Q'.$i],'User'];
        if($i == count($_POST['count'])){
            $res = 'Alexa';
        }else{
            $res = 'EndConversation';
        }
        $Form_data['Conversation'][]=[$Form_data['A'.$i],$res];
    }
    $Form_data['username']=get_username();
    $Form_data['userid']  =get_current_user_id();
    //print_r(json_encode($Form_data));die();
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_insert',json_encode($Form_data)));
    wp_redirect(get_home_url('').'/requests-list/');
} */

/**
 * Displays the Requests List
 * And Performs the Delete and Edit action
 */

add_shortcode('request_list','request_list');
function request_list(){
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_read',json_encode(array('username'=>get_username()))));
    
    print'<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="requests" action="'.admin_url('admin-post.php').'" method="POST">';
    print"<input class='btn btn-raised btn-primary delete_action' type='button' name='delete_request' id='delete_request' value='Delete Request'> <input class='btn btn-raised btn-primary button_link' type='button' name='create_request' id='create_request' data-link='".get_home_url()."/create-request/' value='Create Request'>";
    print"<table class='table no-margin'><tr><th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th><th>Request Name</th><th>Request Type</th><th>Status</th><th>Notifications</th><th></th></tr>";
    for($i=0;$i<count($data);$i++){ 
        $RequestName = explode('_@_',$data[$i]->request_name)[1];
        $email_Icon = !empty($data[$i]->EmailID)?"<span class='dashicons dashicons-email'></span>":"";
        $text_Icon = !empty($data[$i]->TextNumber)?"<span class='dashicons dashicons-media-text'></span>":"";
        $call_Icon = !empty($data[$i]->CallNumber)?"<span class='dashicons dashicons-phone'></span>":"";
        
        print"<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_request".$i."' id='chk_request".$i."' class='chkall' value='".$data[$i]->request_name."'></label></div></td>
        <td>".$RequestName."</td>
        <td>".$data[$i]->RequestType."</td>
        <td>".$data[$i]->Status."</td>
        <td>".$call_Icon." ".$text_Icon." ".$email_Icon."</td>
        <td><a href='".get_home_url()."/create-request?RequestName=".$RequestName."'>Edit</a></td></tr>";
    }
    print"</table><input type='hidden' name='action' id='action' value='delete_request'><input type='hidden' name='no_of_requests' id='no_of_requests' value='".$i."'></form></div>";
}

/**
 * Deletes the selected requests
 * 
 */

add_action('admin_post_delete_request','delete_request');
function delete_request(){
    $no_of_requests = $_POST['no_of_requests'];
    $request_names  = array();
    for($i=0;$i<=$no_of_requests;$i++){
        if($_POST['chk_request'.$i]){
            $request_names[] = $_POST['chk_request'.$i];
        }
    }
    $data=doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_delete',json_encode(array('request_name'=>$request_names,'userid'=>get_current_user_id())));
    wp_redirect(get_home_url('').'/requests-list/');
}

/**
 * Settings Page
 * Has Name , UserName , Password , Account Type Field
 * 
 */

add_shortcode('settings','settings');
function settings(){
    $current_user = wp_get_current_user();

    print'<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form" name="requests" action="'.admin_url('admin-post.php').'" method="POST">';
    print'<div class="form-group floating-label name"><input class="form-control" type="textbox" name="name" id="name" value="'.$current_user->display_name.'" required><label for="name">Name</label></div>';
    print'<div class="form-group floating-label username"><input class="form-control" type="textbox" name="username" id="username" value="'.$current_user->user_login.'" required><label for="username">User Name</label></div>';
    print'<div class="form-group floating-label passwords"><input class="form-control" type="password" name="passwords" id="passwords" size="7" value="'.$password.'" required style="width:250px;"><label for="password">Password</label></div>';
    print'<div class="form-group floating-label accounttype"><select class="form-control" name="accounttype" id="accounttype"><option name="trial">Trial</option></select><label for="accounttype">Account Type</label></div>';
    print'<input class="btn btn-raised btn-primary" type="submit" name="save" id="save" value="Save"><input type="hidden" name="user_id" id="user_id" value="'.$current_user->ID.'"><input type="hidden" name="action" id="action" value="userupdate">';
    print'</form></div>';
}

/**
 * Update the Users Settings
 */

add_action('admin_post_userupdate','usersetting');
function usersetting(){
    $setting = wp_update_user(array(
        'ID' => $_POST['user_id'],
        'display_name' => $_POST['name'],
        'user_login' => $_POST['username']
    ));
   //echo $setting;
    wp_redirect(get_home_url('').'/settings/');
}


add_shortcode('responses','responses');
function responses(){
    if($_POST)
    {
        $startdate = $_POST['startdate'];
        $enddate = $_POST['enddate'];
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array('startdate'=>$startdate,'enddate'=>$enddate))));
    } 
    else 
    {
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array())));
    }
    echo '<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form response_form form-validate" action="'.get_home_url().'/responses" method="POST">

    <div class="sesearch">
    <div class="form-group dstartdate">
    <input class="form-control" type="textbox" name="startdate" id="startdate" value="'.$startdate.'">
    <label for="startdate">Start Date</label>
    </div>

    <div class="form-group denddate">
    <input class="form-control" type="textbox" name="enddate" id="enddate" required value="'.$enddate.'">
    <label for="enddate">End Date</label>
    </div>';
    
    echo '<input class="btn btn-raised btn-primary" type="submit" name="search_date" id="search_date" value="Search" />';

    echo '</div>';

    print"<div class='form-group'><select class='form-control' name='downloadactions' id='downloadactions' ><option value='actions' >Actions </option><option value='download'>Export to Excel</option></select></div>";
    
    print"<table class='table no-margin' id='myTable' ><thead><tr><th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th><th>Date Time</th><th>Request Name</th><th>Request Type</th><th>Room No</th><th>View Log</th></tr></thead>";

    print "<tbody>";
    $i=0;
    if($data){
        foreach($data->Items as $response){
            print "<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_device".$i."' id='chk_device".$i."' class='chkall'></label></div></td><td>".$response->Date."</td><td>".$response->RequestName."</td><td>".$response->RequestType."</td><td>".$response->RoomNumber."</td><td><a href='".get_home_url().'/device-form?RequestName='.$response->RequestName."'>View Log</a></td></tr>";
            $i++;
        }
    }
    print"</tbody></table>";
    print"</form></div>";
}

add_shortcode('audit_log','audit_log_index');
function audit_log_index(){
    if($_POST)
    {
        $startdate = $_POST['startdate'];
        $enddate = $_POST['enddate'];

        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array('startdate'=>$startdate,'enddate'=>$enddate))));
    } 
    else 
    {
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array())));
    }

    print'<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="devices_list" action="'.get_home_url()."/audit-log".'" method="POST">';
    print'<div class="seauditlog">';
    print'<div class="form-group dstartdate">
    <input class="form-control" type="textbox" name="startdate" id="startdate" value="'.$startdate.'">
    <label for="startdate">Start Date</label>
    </div>

    <div class="form-group denddate">
    <input class="form-control" type="textbox" name="enddate" id="enddate" required value="'.$enddate.'">
    <label for="enddate">End Date</label>
    </div>';
    print"<input class='btn btn-raised btn-primary' type='submit' name='search_date' id='search_date' value='Search' />  ";
    print'</div>';


    print"<div class='form-group floating-label'><select class='form-control' name='downloadactions' id='downloadactions' ><option>Select </option><option value='download'>Export to Excel</option></select>
    <label for='downloadactions'>Actions</label></div>";
    
    print"<table class='table no-margin' id='myTable' ><thead><tr><th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th><th>Date Time</th><th>Response Status</th><th>Request Name</th><th>Request Type</th><th>Room No</th><th>View Log</th></tr></thead>";

    print "<tbody>";

    $i=0;
    if($data){
        foreach($data->Items as $response){
            print "<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_device".$i."' id='chk_device".$i."' class='chkall'></label></div></td><td>".$response->Date."</td><td>Success</td><td>".$response->RequestName."</td><td>".$response->RequestType."</td><td>".$response->RoomNumber."</td><td><a href='".get_home_url().'/device-form?RequestName='.$response->RequestName."'>View Log</a></td></tr>";
            $i++;
        }
    }

    print"</tbody></table>";
    print"</form></div>";

}


add_shortcode('reports','report_page');
function report_page(){
   print"<div class='col-md-offset-3 col-md-6 card card-tiles style-default-light reports'>
   <div>
   <a href='".get_home_url()."/activity-report'>Activity Report</a>
   </div>";
   print"
   <div>
   <a href='".get_home_url()."/response-by-category'>Response By Category</a>
   </div>";
   print"
   <div>
   <a href='".get_home_url()."/activity-by-roomtype'>Response By Room Types</a>
   </div></div>";
}

function loginredirect(){
    wp_redirect( home_url().'/dashboard' );
    exit();
}

add_action('wp_login', 'loginredirect');

function logoutredirect(){
    wp_redirect( home_url().'/login' );
    exit();
}

add_action('wp_logout','logoutredirect');


/*function custom_login() {
    if ( !is_user_logged_in() ) {
	$creds = array();
	$creds['user_login'] = $_POST['username'];
	$creds['user_password'] = $_POST['password'];
	$creds['remember'] = true;
	$creds['redirect_to'] = get_home_url().'/dashboard';
	$user = wp_signon( $creds, false );
	if ( is_wp_error($user) ){
		echo $user->get_error_message();
    }
    else{
        wp_redirect(get_home_url().'/dashboard');
    }
    }
}

// run it before the headers and cookies are sent
//add_action( 'after_setup_theme', 'custom_login' );*/


add_shortcode('login','login');
function login(){
    return get_template_html('login');
}

/**
*
*  Has link of lost-password
**/

add_shortcode('password_forgot','password_forgot');
function password_forgot(){
    ?>
    <!--<div class="col-md-offset-3 col-md-5 card card-tiles style-default-light">
	<form class="form floating-label form-validate" name="lostpasswordform" id="lostpasswordform" action="<?php echo get_home_url()."/wp-login.php?action=lostpassword"?>" method="post">
	    <fieldset>
			<p>Please enter your username or email address. You will receive a link to create a new password via email.</p>
	<div class="form-group">
	    		<input type="text" name="user_login" id="user_login" class="input form-control" value="" size="20" />
		<label for="user_login" >Username or Email Address</label>
	</div>
		<input class="form-control" type="hidden" name="redirect_to" value="<?php echo get_home_url()."/login"; ?>" />
		<div class="col-xs-3 text-right submit"><input type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-raised" value="Get New Password" /></div>
	</fieldset>
    </form>
    </div>-->
    <?php
    return get_template_html('password_lost_form');
}

add_shortcode('custom_reset_password','custom_reset_password');
function custom_reset_password(){
    if($_POST){
        $user = get_user_by('login', $_POST['user_login']);
        wp_set_password($_POST['pass1'],$user->ID);
        wp_redirect(home_url().'/login?rp_success=1');
    }else{
        return get_template_html('reset_password');
    }
}

function wpse_133647_custom_lost_password_page() {
    return home_url('/lost-password');
} // function wpse_133647_custom_lost_password_page
add_filter('lostpassword_url', 'wpse_133647_custom_lost_password_page');
    
function redirect_login_page(){
    global $wpdb,$pagenow;
    // permalink to the custom login page
    if($GLOBALS['pagenow'] === 'wp-login.php' && !is_user_logged_in()){
        $login_page  = get_site_url().'/login';
        wp_redirect( $login_page);
    } 
}
//add_action( 'init','redirect_login_page' );

function redirect_after_lost_password() {
    if( isset($_GET['checkemail']) && $_GET['checkemail'] === 'confirm') {
        wp_redirect( home_url( '/login' ) );
        exit;
    }
}
add_action( 'login_head', 'redirect_after_lost_password' ); 

add_shortcode( 'custom-register-form', 'render_register_form' );

function render_register_form( $attributes, $content = null ) {
    global $wpdb;
    $default_attributes = array( 'show_title' => false );
    $attributes = shortcode_atts( $default_attributes, $attributes );
    
    $attributes['errors'] = array();
    
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $redirect_url = home_url( 'register' );
 
        if ( ! get_option( 'users_can_register' ) ) {
            // Registration closed, display error
            //$redirect_url = add_query_arg( 'register-errors', 'closed';, $redirect_url );
            $attributes['errors'][]='closed';
        } else {
            $email = $_POST['email'];
            $first_name = sanitize_text_field( $_POST['first_name'] );
            $last_name = sanitize_text_field( $_POST['last_name'] );
            $hotel_name = sanitize_text_field( $_POST['hotel_name'] );
            
            $query = "select count(*) as cnt from wp_usermeta where meta_key='business_name' and meta_value='".$hotel_name."' ";
            $result = $wpdb->get_results($query);
            if($result[0]->cnt>0){
                $attributes['errors'][]=urlencode('Business Name Already Exist');
                return get_template_html( 'register_form', $attributes );
            }
            $result = register_user( $email, $first_name, $last_name, $hotel_name  );
 
            if ( is_wp_error( $result ) ) {
                foreach ( $result->get_error_codes() as $error_code ) {
                    $attributes['errors'] []= get_error_message( $error_code );
                }
            } else {
                // Success, redirect to login page.
                $redirect_url = home_url( 'login' );
                $redirect_url = add_query_arg( 'registered', $email, $redirect_url );
                wp_redirect( $redirect_url );
                exit;
            }
        }
 
        //wp_redirect( $redirect_url );
        //exit;
    }
    
    
    
    /*if ( isset( $_REQUEST['register-errors'] ) ) {
        $error_codes = explode( ',', $_REQUEST['register-errors'] );
     
        foreach ( $error_codes as $error_code ) {
            $attributes['errors'] []= get_error_message( $error_code );
        }
    }*/
 
    if ( is_user_logged_in() ) {
        return __( 'You are already signed in.', 'personalize-login' );
    } elseif ( ! get_option( 'users_can_register' ) ) {
        return __( 'Registering new users is currently not allowed.', 'personalize-login' );
    } else {
        return get_template_html( 'register_form', $attributes );
    }
}

function get_template_html( $template_name, $attributes = null ) {
    if ( ! $attributes ) {
        $attributes = array();
    }
 
    ob_start();
 
    do_action( 'personalize_login_before_' . $template_name );
 
    require( 'templates/' . $template_name . '.php');
 
    do_action( 'personalize_login_after_' . $template_name );
 
    $html = ob_get_contents();
    ob_end_clean();
 
    return $html;
}

function register_user( $email, $first_name, $last_name ,$hotel_name ) {
    $errors = new WP_Error();
 
    // Email address is used as both username and email. It is also the only
    // parameter we need to validate
    if ( ! is_email( $email ) ) {
        $errors->add( 'email', get_error_message( 'email' ) );
        return $errors;
    }
 
    if ( username_exists( $email ) || email_exists( $email ) ) {
        $errors->add( 'email_exists', get_error_message( 'email_exists') );
        return $errors;
    }
 
    // Generate the password so that the subscriber will have to check email...
    $password = wp_generate_password( 12, false );
    $user_data = array(
        'user_login'    => $hotel_name,
        'user_email'    => $email,
        'user_pass'     => $password,
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'nickname'      => $first_name,
        'display_name'  => $hotel_name,
        'role'          => 'administrator'
    );
    
    $IAM_User=array();
    $IAM_User['Path']       = $hotel_name;
    $IAM_User['UserName']   = $email;
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($IAM_User));
 
    $user_id = wp_insert_user( $user_data );
    update_user_meta($user_id,'business_name',$hotel_name);
    wp_new_user_notification_custom( $user_id, $password );
 
    return $user_id;
}

/*add_action( 'login_form_register','redirect_to_custom_register' );
function redirect_to_custom_register() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $redirect_url = home_url( 'register' );
 
        if ( ! get_option( 'users_can_register' ) ) {
            // Registration closed, display error
            $redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );
        } else {
            $email = $_POST['email'];
            $first_name = sanitize_text_field( $_POST['first_name'] );
            $last_name = sanitize_text_field( $_POST['last_name'] );
            $hotel_name = sanitize_text_field( $_POST['hotel_name'] );
 
            $result = register_user( $email, $first_name, $last_name, $hotel_name  );
 
            if ( is_wp_error( $result ) ) {
                // Parse errors into a string and append as parameter to redirect
                $errors = join( ',', $result->get_error_codes() );
                $redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );
            } else {
                // Success, redirect to login page.
                $redirect_url = home_url( 'login' );
                $redirect_url = add_query_arg( 'registered', $email, $redirect_url );
            }
        }
 
        wp_redirect( $redirect_url );
        exit;
    }
}
*/

add_action( 'template_redirect', 'redirect_to_specific_page' );

function redirect_to_specific_page() {
    if ((!is_page('login') && !is_page('register') && !is_page('custom-reset-password') && !is_page('lost-password')) && ! is_user_logged_in() ) {
        $login_page  = get_site_url().'/login';
        wp_safe_redirect( $login_page,301 );
        exit;
    }elseif(is_page('login') && is_user_logged_in()){
        wp_safe_redirect( get_site_url().'/dashboard' ,301 );
        exit;
    }
}

function get_error_message( $error_code ) {
    switch ( $error_code ) {
        case 'empty_username':
            return __( 'You do have an email address, right?', 'personalize-login' );
 
        case 'empty_password':
            return __( 'You need to enter a password to login.', 'personalize-login' );
 
        case 'invalid_username':
            return __(
                "We don't have any users with that email address. Maybe you used a different one when signing up?",
                'personalize-login'
            );
 
        case 'incorrect_password':
            $err = __(
                "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
                'personalize-login'
            );
            return sprintf( $err, wp_lostpassword_url() );
            
        case 'email':
            return __( 'The email address you entered is not valid.', 'personalize-login' );
         
        case 'email_exists':
            return __( 'An account exists with this email address.', 'personalize-login' );
         
        case 'closed':
            return __( 'Registering new users is currently not allowed.', 'personalize-login' );    
 
        default:
            break;
    }
     
    return __( 'An unknown error occurred. Please try again later.', 'personalize-login' );
}


add_shortcode('user_creation','user_creation');
function user_creation(){
    return get_template_html('user_creation');
}


function user_creation_old(){
    if($_REQUEST['errors']){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$_REQUEST['errors'].'</div>';
    }
    if($_REQUEST['userid']){
        $users = get_userdata($_REQUEST['userid']);
        $button= 'Update';
    }else{
        $button= 'Register';
    }
    ?>
    <section class="section-account">

        <div class="card-body">
         <div class="row">
          <div class="col-md-offset-3 col-md-6 card card-tiles style-default-light">
           <br/>
           <span class="text-center text-lg text-bold text-primary">User Creation</span>
           <br/><br/>
           <!--<form class="form floating-label" action="../../html/dashboards/dashboard.html" accept-charset="utf-8" method="post">-->
            <form class="form floating-label form-validate" name="registerform" action="<?php echo admin_url('admin-post.php') ?>" method="post">
                <div class="form-group">
                 <input type="text" class="form-control" id="user_login" name="user_login" value="<?php echo $users->user_login?>" required>
                 <label for="user_login">Username *</label>
             </div>
             <div class="form-group">
                 <input type="text" class="form-control" id="email" name="email" value="<?php echo $users->user_email?>" required>
                 <label for="email">Email *</label>
             </div>
             <div class="form-group">
                 <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $users->first_name?>" required>
                 <label for="first_name">First Name *</label>
             </div>
             <div class="form-group">
                 <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $users->last_name?>">
                 <label for="last_name">Last Name</label>
             </div>
             <div class="form-group">
                 <input type="password" class="form-control" id="password" name="password" value="" required>
                 <label for="password">Password *</label>
             </div>
             <!--<div class="form-group">
                <?php
                $roles = array('subscriber'=>'Subscriber','editor'=>'Editor');
                $role = $users->roles[0];
                ?>
                <select name="role" class="form-control"  id="role">
                    <?php
                    foreach($roles as $key=>$_role){
                        $_sel = ($key == $role)?"selected":"";
                        print"<option value='".$key."' ".$_sel.">".$_role."</option>";
                    }
                    ?>
                </select>
                <label for="role">Role *</label>
            </div>-->
            <br/>
            <input type="hidden" name="redirect_to" value="<?php echo get_home_url().'/user-list/'?>" />
            <input type="hidden" name="action" value="user_register" />
            <div class="row">
             <div class="col-xs-3 text-right">
                <input class="btn btn-primary btn-raised" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $button?>"
            </div><!--end .col -->
        </div><!--end .row -->
    </form>
</div><!--end .col -->
</div><!--end .row -->
</div><!--end .card-body -->

</section>
<?php
}

add_action('admin_post_user_register','user_register');
function user_register(){
        $display_name = ($_POST['first_name'])?$_POST['first_name']:$_POST['email'];
        
        if(!$_POST['userid']){
            $userdata = array(
                //'user_login'  =>  $_POST['user_login'],
                'user_login'  =>  $_POST['email'],
                'user_pass'   =>  $_POST['password'],  
                'display_name'=>  $display_name,  
                'first_name'  =>  $_POST['first_name'],  
                'last_name'   =>  $_POST['last_name'],  
                'user_email'  =>  $_POST['email'],  
                //'role'      =>  $_POST['role']  
                'role'        =>  'subscriber'
            );
    
            $user_id = wp_insert_user( $userdata ) ;
            if($user_id){
                update_user_meta($user_id,'user_belongs_to',get_current_user_id());
                wp_new_user_notification_custom($user_id,$_POST['password']);
            }
            if ( is_wp_error( $user_id  ) ) {
                $e = $user_id->get_error_message();
                wp_redirect(add_query_arg( array('errors'=>str_replace(" ","%20",$e),'post'=>$userdata), home_url().'/user-creation' ));
            }else{
                #wp_redirect( esc_url( add_query_arg( 'success', '1', home_url().'/register' ) ) );
                wp_redirect( $_POST['redirect_to'].'?success=1' );
            }
        }else{
            $id = (int) $_POST[ 'userid' ]; 
            $user_detail  = get_user_by('ID',$id);
            $password = $_POST['password']?$_POST['password']:$user_detail->user_pass;
            
            $user_id = wp_update_user( array(
                'ID' => $id,
                'user_login'  => $_POST['email'],
                'user_email'  => $_POST['email'],
                'user_pass'   => $password,  
                'display_name'=> $display_name,  
                'first_name'  => $_POST['first_name'],  
                'last_name'   => $_POST['last_name'], 
            ));
            if ( is_wp_error( $user_id  ) ) {
            $e = $user_id->get_error_message();
            wp_redirect(add_query_arg( array('errors'=>str_replace(" ","%20",$e),'post'=>$userdata), home_url().'/user-creation?userid='.$id ));
            }else{
                //wp_redirect( esc_url( add_query_arg( 'success', '1', home_url().'/register' ) ) );
                wp_redirect( $_POST['redirect_to'].'?updated=1' );
            }
        }
        
}


add_shortcode('users_list','users_list');
function users_list(){
    if($_REQUEST['success'] == 1){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">User has been created successfully.</div>';
    }
    else if($_REQUEST['updated'] == 1){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">User has been Updated successfully.</div>';
    }
    $args=array(
        'meta_key'=>'user_belongs_to',
        'meta_value'=>get_current_user_id()
        );
    $users = get_users($args);
    //print_r($users);
    $content='<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="user_list" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<input class="btn btn-raised btn-primary delete_action" type="button" name="deleteusers" id="deleteusers" value="Delete Users"> <a href="'.get_home_url().'/user-creation/'.'" name="createuser" id="createuser"><input class="btn btn-raised btn-primary" type="button" value="Create User"></a>';
    $content.='<table class="table no-margin"><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>User Name</th><th>E-Mail</th><th>Role</th><th>Edit</th></tr>';
    
    $i=0;  
    if(count($users)){
        foreach($users as $user){
            if($user->roles[0]!='administrator'){
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_user'.$i.'" class="chkall"></label></div><input type="hidden" name="user_id'.$i.'" value="'.$user->ID.'"></td><td>'.$user->user_login.'</td><td>'.$user->user_email.'</td><td>'.$user->roles[0].'</td><td><a href="'.get_home_url().'/user-creation?userid='.$user->ID.'" name="edit_room'.$i.'" id="edit_room'.$i.'" class="edit_rooms">Edit</a></td></tr>';
            $i++;
            }
        }
    }
    
    $content.='<tr></tr></table>';
    $content.='<input type="hidden" name="action" value="deleteusers"><input type="hidden" name="no_of_users" value="'.$i.'"></form></div>';
    return $content;
}

add_action('admin_post_deleteusers','deleteusers');
function deleteusers(){
    for($i=0;$i<=$_POST['no_of_users'];$i++){
        $chk = 'chk_user'.$i;
        if(!empty($_POST[$chk])){
            wp_delete_user($_POST["user_id".$i]);
        }
    }
    wp_redirect(get_home_url().'/user-list');
}



add_shortcode('request_type_template','request_type_template');
function request_type_template(){
    if($_REQUEST['RequestTypeName']){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_request_types',json_encode(array('request_type'=>$_REQUEST['RequestTypeName']))));
        $requesttype_name       = $data->Items[0]->request_type;
    }
    $action_for = empty($_REQUEST['RequestTypeName'])?'insert':'update';
    echo '<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form request_type_template form-validate" action="'.admin_url('admin-post.php').'" method="POST">

    <div class="form-group floating-label name">
    <input class="form-control" type="textbox" name="requesttype_name" id="requesttype_name" value="'.$requesttype_name.'" required '.$readonly.' style="width:200px;">
    <label for="requesttype_name">Request Type Name *</label>
    </div>  

    
    <input type="hidden" name="action" value="request_types">
    <input type="hidden" name="action_for" value="'.$action_for.'">
    <input type="hidden" name="old_request_type_name" value="'.$requesttype_name.'">
    <input class="btn btn-raised btn-primary" type="Submit" value="submit"/>
    <input class="btn btn-raised btn-primary button_link" type="button" value="Cancel" data-link="'.get_home_url().'/request-type-temp-list"/>
    </form></div>
    ';
}

add_action('admin_post_request_types','request_types');
function request_types(){
    if($_POST['action_for']=='update'){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_request_type',json_encode(array('request_type'=>$_POST['requesttype_name'],'old_request_type'=>$_POST['old_request_type_name']))));
    }else{
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_request_types',json_encode(array('request_type'=>$_POST['requesttype_name']))));
    }
    wp_redirect(get_home_url().'/request-type-temp-list');
}

add_shortcode('request-type-temp-list','request_type_list');
function request_type_list(){
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_request_types',json_encode(array('request_type'=>''))));
    
    $content='<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="delete_template" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<input class="btn btn-raised btn-primary" type="submit" name="deletetemplate" id="deletetemplate" value="Delete Request Type"> <a href="'.get_home_url().'/request-type/'.'" name="createtemplate" id="createtemplate"><input class="btn btn-raised btn-primary" type="button" value="Create Request Type"></a>';
    $content.='<table class="table no-margin"><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>Request Type Names</th><th>Edit</th></tr>';
    
    $i=0;  
    
    if(!empty($data->Items)){
        foreach($data->Items as $temp){
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_template'.$i.'" class="chkall" value="'.$temp->request_type.'"></label></div></td><td>'.$temp->request_type.'</td><td><a href="'.get_home_url().'/request-type?RequestTypeName='.$temp->request_type.'" name="edit_temp'.$i.'" id="edit_temp'.$i.'" class="edit_temps" data-template_name="'.$temp->template_name.'">Edit</a></td></tr>';
            $i++;
        }
    }
    
    $content.='<tr></tr></table>';
    $content.='<input type="hidden" name="action" value="delete_req_templates"><input type="hidden" name="no_of_temps" value="'.$i.'"></form></div>';
    
    return $content;
}

add_action('admin_post_delete_req_templates','delete_req_templates');
function delete_req_templates(){
    $temp_to_delete=array();
    for($i=0;$i<=$_POST['no_of_temps'];$i++){
        $chk = 'chk_template'.$i;
        if(!empty($_POST[$chk])){
            $temp_to_delete[]=$_POST[$chk];
        }
    }
    $temp_to_delete = json_encode(array('request_type'=>$temp_to_delete));
    
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/request_type_delete',$temp_to_delete);
    wp_redirect(get_home_url('').'/request-type-temp-list/');
}

add_shortcode('notification_template','notification_template');
function notification_template(){
    if($_POST){
        if($_POST['action_for']=='update'){
            $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_notification_template',json_encode(array('template_name'=>$_POST['template_name'],'old_template_name'=>$_POST['old_template_name'],'template'=>$_POST['tempcontent'],'username'=>get_username(),'userid'=>get_current_user_id()))));
        }else{
            $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_notification_template',json_encode(array('template_name'=>$_POST['template_name'],'old_template_name'=>$_POST['old_template_name'],'template'=>$_POST['tempcontent'],'username'=>get_username(),'userid'=>get_current_user_id()))));
        }
        if($data->error){
            echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$data->error.'</div>';
        }else{
            wp_redirect(get_home_url().'/notification-temp-list');
        }
        $template_name = $_POST['template_name'];
        $tempcontent   = $_POST['tempcontent'];
    }
    $temp_name = $_REQUEST['TempName']?$_REQUEST['TempName']:'';
    if($temp_name){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>$temp_name,'username'=>get_username(),'userid'=>get_current_user_id()))));
        $template_name       = explode('_@_',$data->Items[0]->template_name)[1];
        $tempcontent= $data->Items[0]->template;
    }
    $action_for = empty($_REQUEST['TempName'])?'insert':'update';
    echo '<div class="col-md-offset-3 col-md-6 card card-tiles style-default-light"><form class="form request_type_template form-validate" action="'.home_url().'/notification_template" method="POST">

    <div class="form-group floating-label name">
    <input class="form-control" type="textbox" name="template_name" id="template_name" value="'.$template_name.'" '.$readonly.' style="width:200px;">
    <label style="height: 50px;" for="template_name">Notification Template Name *</label>
    <div id="errorMessage_noti" style="display: none; color: rgb(169, 68, 66);"></div>
    </div>  

    <div class="form-group floating-label content">
    <textarea class="form-control" name="tempcontent" id="tempcontent" style="width:400px;">'.$tempcontent.'</textarea>
    <label style="height: 65px;" for="tempcontent">Template *</label>
    <div id="errorMessage_noti_content" style="display: none; color: rgb(169, 68, 66);"></div>
    </div>     
    
    <div>
    Note: Dynamic Variables : #roomno
    </div>
    
    <!--<input type="hidden" name="action" value="notification_template">-->
    <input type="hidden" name="action_for" value="'.$action_for.'">
    <input type="hidden" name="old_template_name" value="'.$template_name.'">
    <input class="btn btn-raised btn-primary" id="noti_submit" type="button" value="submit"/>
    <input class="btn btn-raised btn-primary button_link" type="button" value="Cancel" data-link="'.get_home_url().'/notification-temp-list"/>
    </form></div>
    ';
}

/*add_action('admin_post_notification_template_insert','notification_template_insert');
function notification_template_insert(){
    if($_POST['action_for']=='update'){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_notification_template',json_encode(array('template_name'=>$_POST['template_name'],'old_template_name'=>$_POST['old_template_name'],'template'=>$_POST['tempcontent'],'username'=>get_username()))));
    }else{
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_notification_template',json_encode(array('template_name'=>$_POST['template_name'],'old_template_name'=>$_POST['old_template_name'],'template'=>$_POST['tempcontent'],'username'=>get_username()))));
    }
    wp_redirect(get_home_url().'/notification-temp-list');
}*/

add_shortcode('notification-temp-list','notification_temp_list');
function notification_temp_list(){
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>'','username'=>get_username(),'userid'=>get_current_user_id()))));
    
    $content='<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="delete_template" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<input class="btn btn-raised btn-primary delete_action" type="button" name="deletetemplate" id="deletetemplate" value="Delete Template"> <a href="'.get_home_url().'/notification_template/'.'" name="createtemplate" id="createtemplate"><input class="btn btn-raised btn-primary" type="button" value="Create Template"></a>';
    $content.='<table class="table no-margin"><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>Template Names</th><th>Templates</th><th>Edit</th></tr>';
    
    $i=0;  
    
    if(!empty($data->Items)){
        foreach($data->Items as $temp){
            $TemplateName = explode('_@_',$temp->template_name)[1];
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_template'.$i.'" class="chkall" value="'.$TemplateName.'"></label></div></td><td>'.$TemplateName.'</td><td>'.$temp->template.'</td><td><a href="'.get_home_url().'/notification_template?TempName='.$TemplateName.'" name="edit_temp'.$i.'" id="edit_temp'.$i.'" class="edit_temps" data-template_name="'.$TemplateName.'">Edit</a></td></tr>';
            $i++;
        }
    }
    
    $content.='<tr></tr></table>';
    $content.='<input type="hidden" name="action" value="delete_noti_templates"><input type="hidden" name="no_of_temps" value="'.$i.'"></form></div>';
    
    return $content;
}

add_action('admin_post_delete_noti_templates','delete_noti_templates');
function delete_noti_templates(){
    $temp_to_delete=array();
    for($i=0;$i<=$_POST['no_of_temps'];$i++){
        $chk = 'chk_template'.$i;
        if(!empty($_POST[$chk])){
            $temp_to_delete[]=$_POST[$chk];
        }
    }
    $temp_to_delete = json_encode(array('Notification_Temp'=>$temp_to_delete,'userid'=>get_current_user_id()));
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/notification_temp_delete',$temp_to_delete);
    wp_redirect(get_home_url('').'/notification-temp-list/');
}


function wp_new_user_notification_custom( $user_id, $deprecated = null, $notify = '' ) {
	if ( $deprecated !== null ) {
		_deprecated_argument( __FUNCTION__, '4.3.1' );
	}

	global $wpdb, $wp_hasher;
	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	if ( 'user' !== $notify ) {
		$switched_locale = switch_to_locale( get_locale() );

		/* translators: %s: site title */
		$message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		/* translators: %s: user email address */
		$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

		$wp_new_user_notification_email_admin = array(
			'to'      => get_option( 'admin_email' ),
			/* translators: Password change notification email subject. %s: Site title */
			'subject' => __( '[%s] New User Registration' ),
			'message' => $message,
			'headers' => '',
		);

		/**
		 * Filters the contents of the new user notification email sent to the site admin.
		 *
		 * @since 4.9.0
		 *
		 * @param array   $wp_new_user_notification_email {
		 *     Used to build wp_mail().
		 *
		 *     @type string $to      The intended recipient - site admin email address.
		 *     @type string $subject The subject of the email.
		 *     @type string $message The body of the email.
		 *     @type string $headers The headers of the email.
		 * }
		 * @param WP_User $user     User object for new user.
		 * @param string  $blogname The site title.
		 */
		$wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );

		@wp_mail(
			$wp_new_user_notification_email_admin['to'],
			wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
			$wp_new_user_notification_email_admin['message'],
			$wp_new_user_notification_email_admin['headers']
		);

		if ( $switched_locale ) {
			restore_previous_locale();
		}
	}

	// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
	if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
		//return;
	}

	// Generate something random for a password reset key.
	$key = wp_generate_password( 20, false );

	/** This action is documented in wp-login.php */
	do_action( 'retrieve_password_key', $user->user_login, $key );

	// Now insert the key, hashed, into the DB.
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

	$switched_locale = switch_to_locale( get_user_locale( $user ) );

	/* translators: %s: user login */
	$message = sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= __('To set your password, visit the following address:') . "\r\n\r\n";
	//$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";
	
	$message .= '<' . network_site_url("custom-reset-password?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";

	$message .= wp_login_url() . "\r\n";

	$wp_new_user_notification_email = array(
		'to'      => $user->user_email,
		/* translators: Password change notification email subject. %s: Site title */
		'subject' => __( '[%s] Your username and password info' ),
		'message' => $message,
		'headers' => '',
	);

	/**
	 * Filters the contents of the new user notification email sent to the new user.
	 *
	 * @since 4.9.0
	 *
	 * @param array   $wp_new_user_notification_email {
	 *     Used to build wp_mail().
	 *
	 *     @type string $to      The intended recipient - New user email address.
	 *     @type string $subject The subject of the email.
	 *     @type string $message The body of the email.
	 *     @type string $headers The headers of the email.
	 * }
	 * @param WP_User $user     User object for new user.
	 * @param string  $blogname The site title.
	 */
	$wp_new_user_notification_email = apply_filters( 'wp_new_user_notification_email', $wp_new_user_notification_email, $user, $blogname );

	wp_mail(
		$wp_new_user_notification_email['to'],
		wp_specialchars_decode( sprintf( $wp_new_user_notification_email['subject'], $blogname ) ),
		$wp_new_user_notification_email['message'],
		$wp_new_user_notification_email['headers']
	);

	if ( $switched_locale ) {
		restore_previous_locale();
	}
}


add_shortcode('my_profile','my_profile');
function my_profile(){
    if($_POST){
        $id = (int) get_current_user_id(); 
        $user_detail  = get_user_by('ID',$id);
        $password = $_POST['password']?$_POST['password']:$user_detail->user_pass;
        $user_id = wp_update_user( array(
            'ID' => $id,
            'user_pass'   =>  $password,  
            'first_name'  =>  $_POST['first_name'],  
            'last_name'   =>  $_POST['last_name'], 
        ));
        if ( is_wp_error( $user_id  ) ) {
        $e = $user_id->get_error_message();
        //wp_redirect(add_query_arg( array('errors'=>str_replace(" ","%20",$e)), home_url().'/my_profile' ));
        $attributes['errors'] = $e;
        return get_template_html('my_profile',$attributes);
        }else{
            //wp_redirect( esc_url( add_query_arg( 'success', '1', home_url().'/register' ) ) );
            //wp_redirect( $_POST['redirect_to'] );
            $attributes['success'] = "Successfully Updated!";
            return get_template_html('my_profile',$attributes);
        }
    }else{
        return get_template_html('my_profile');
    }
}


/**
 * End of Plugin
 */
?>
