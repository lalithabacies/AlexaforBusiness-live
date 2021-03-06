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

function app_output_buffer() {
    
	ob_start();
	
    $page_viewed = basename( $_SERVER['REQUEST_URI'] );
    if( $page_viewed == "alexa-for-business" ) {
        wp_redirect(get_site_url().'/login');
        exit();
    }
    
    if(!is_user_logged_in()) {
        return get_template_html('login');
        exit();
    } 
    
    //change role name
    global $wp_roles;
    if ( ! isset( $wp_roles ) )
        $wp_roles = new WP_Roles();
    $wp_roles->roles['administrator']['name'] = 'Super Admin';
    $wp_roles->role_names['administrator'] = 'Super Admin';
    
    $wp_roles->roles['subscriber']['name'] = 'Administrator';
    $wp_roles->role_names['subscriber'] = 'Administrator';
    
    $wp_roles->roles['editor']['name'] = 'Users';
    $wp_roles->role_names['editor'] = 'Users';
    
    remove_role( 'contributor' );
    remove_role( 'author' );

} // soi_output_buffer
add_action('init', 'app_output_buffer'); 





function login_check()
{
    if(!is_user_logged_in()) {
      wp_redirect(get_site_url().'/login');
      exit();
    }
}

add_action('wp_enqueue_scripts','add_jquery_plugin');
function add_jquery_plugin(){
     // wp_enqueue_script( 'jquery-1.12.4','https://code.jquery.com/jquery-Notification1.12.4.js');
    //wp_enqueue_script( 'jquery','https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
  
   // wp_enqueue_script( 'jquery-1.12.1','https://code.jquery.com/ui/1.12.1/jquery-ui.js');
    
   // wp_enqueue_style( 'css-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
   
    wp_enqueue_script( 'jquery', get_template_directory_uri() . '/js/libs/UI/jquery.min.js','');
    wp_enqueue_script( 'jquery-1.12.1', get_template_directory_uri() . '/js/libs/UI/jquery-ui.js','');    
    wp_enqueue_style( 'css-ui', get_template_directory_uri() . '/js/libs/UI/jquery-ui.css');
    wp_enqueue_script( 'select-script', get_template_directory_uri() . '/js/libs/select2/select2.min.js','');
    wp_enqueue_script( 'nano', get_template_directory_uri() . '/js/libs/DataTables/jquery.dataTables.js','');
    
    
    
}

add_action('wp_enqueue_scripts','alexa_script');
function alexa_script() {
    wp_enqueue_script( 'a4b', plugins_url( '/js/a4b.js', __FILE__ ));
}


function get_username(){
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'subscriber'){
        $iam_username = $current_user->user_email;
    }else{
        $users = get_user_by('ID',get_userid());
        $iam_username = $users->user_email;
    }
    return $iam_username;
}

function get_userid(){
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'subscriber'){
        $userid = $current_user->ID;
    }else{
        $args=array(
        'meta_key'=>'user_belongs_to'
        );
        $users = get_user_meta(get_current_user_id(),'user_belongs_to');
        $userid = $users[0];
    }
    return $userid;
}

function get_group_users(){

    $all_users = get_users(array(
        'meta_key' => 'user_belongs_to',
        'meta_value'=>get_userid()
        ));
    $user_ids = array();
    if(get_userid()){
        $user_ids[] = get_userid();
    }
    foreach($all_users as $user){
        if($user->ID>0){
            $user_ids[] = $user->ID;
        }
    }
    return $user_ids;    
}

/**
 * Shortcode for create the New User
 * Form has Hotelname and username fields
 * Insertion Form
 * 
 */

add_shortcode('iam_users_form','new_user');
function new_user(){
    login_check();
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
    
    echo '<div class="col-md-6 card card-tiles style-default-light"><form class="col-md-offset-1 col-md-10 form create_user form create_user" action="'.admin_url('admin-ajax.php').'" method="POST">

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

add_action('admin_post_nopriv_update_user','update_user_by_username');
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
add_action('admin_post_nopriv_edit_user','edit_user_form');
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
    login_check();
    $data=json_decode(doCurl_GET('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/list_users'));
    $content='<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="users" action="'.admin_url('admin-post.php').'" method="POST"><table class="table no-margin"><tr><th colspan="5" align="center">';
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
add_action('admin_post_nopriv_create_user','new_users');
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
add_action('admin_post_nopriv_delete_users','delete_users');
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
    login_check();
    //echo '<form class="form create_room form-validate" action="'.admin_url('admin-post.php').'" method="POST"><input type="text" name="test"><input type="hidden" name="action" value="create_room"></form>';
    
    if($_REQUEST['error']){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$_REQUEST['error'].'</div>';
    }
    
    $RoomName = $ProfileName = $readonly = '';
    $purpose  = 'create';
    if(!empty($_REQUEST['RoomName'])){
        //$RoomName       = $_REQUEST['room_value']['edited_room_name'];
        //$ProfileName    = $_REQUEST['room_value']['edited_room_profile_name'];
        
       
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_rooms',json_encode(array('RoomName'=>$_REQUEST['RoomName'],'username'=>get_username(),'userid'=>get_userid()))));
        
        $purpose        = "update";
        //$readonly       = "readonly";
        $RoomName    = $data[0]->RoomName;
        $ProfileName = $data[0]->ProfileName;
        $DeviceName  = $data[0]->DeviceName;
        $SkillGroup  = $data[0]->SkillGroupArn;
        
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
    echo '<div class="col-md-6 card card-tiles style-default-light"><form class="form create_room form-validate" action="'.admin_url('admin-post.php').'" method="POST">

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
    $params = ($DeviceName)?json_encode(array('DeviceName'=>$DeviceName,'group_users'=>get_group_users())):json_encode(array('group_users'=>get_group_users()));
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',$params));
    $options='<option value="">Select Device</option>';
    if(!empty($data)){
       
        foreach($data as $device_name){
            
            if(trim($device_name->DeviceName)){
                $_selected = (trim($DeviceName)==trim($device_name->DeviceName))?"selected":"";
                $_disabled =in_array(trim($device_name->DeviceName),$associated_device)?($_selected=="selected"?"":"disabled"):"";
                $options.='<option value="'.$device_name->DeviceName.'" '.$_selected.' '.$_disabled.'>'.$device_name->DeviceName.'</option>';
            }
        }
    }
    echo $options;
    echo '</select><input type="hidden" name="OldDeviceName" id="OldDeviceName" value="'.$DeviceName.'"><label for="ProfileArn">Devices </label></div>';
    
    
    $params = json_encode(array());
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/skill_groups',$params));
    echo '<div class="form-group">';
    echo '<select class="form-control" name="skill_group" id="skill_group"><option value="">Select Skill Group</option>';
    if(!empty($data)){
        foreach($data as $skill_arn=>$skill_group){
            $_sel = ($SkillGroup==$skill_arn)?"selected":"";
            echo '<option value="'.$skill_arn.'" '.$_sel.'>'.$skill_group.'</option>';
        }
    }
    echo '</select><label for="skill_group">Skill Group</label>';
    echo'</div>';
    
    echo'<input type="hidden" name="action" value="create_room">
    <input type="hidden" name="purpose" value="'.$purpose.'">
    <input type="hidden" name="username" id="username" value="'.get_username().'">
    <input type="hidden" name="userid" id="userid" value="'.get_userid().'">
    <input class="btn btn-raised btn-primary" type="Submit" value="submit"/>
    <input class="btn btn-raised btn-danger button_link" type="button" value="Cancel" data-link="'.get_home_url().'/list-out-rooms"/>
    </form></div>
    ';
}

/**
 * Shortcode for list out the Rooms List
 * 
 */

add_shortcode('list_rooms','list_rooms');
function list_rooms(){
    login_check();
    if($_REQUEST['success']){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Device has been disassociated successfully.</div>';
    } else if($_REQUEST['delete']){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Room has been deleted successfully.</div>';
    }
    
    
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_rooms',json_encode(array('username'=>get_username()))));
    
 
    //print_r($data);
    //Form to display and delete the room name
    $content='<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="delete_rooms" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.='<a href="'.get_home_url().'/create_room/'.'" name="createroom" id="createroom"><input class="btn btn-raised btn-primary" type="button" value="Create Room"></a> <input class="btn btn-raised btn-danger delete_action_rooms" type="button" name="deleterooms" id="deleterooms" value="Delete Rooms">  ';
    $content.='<table class="table no-margin" id="data_table"><thead><tr><th><div class="checkbox checkbox-inline checkbox-styled">
                   <label><input type="checkbox" name="chkall" id="chkall"><span></span></label></div></th><th>Room Name</th><th>Room Type</th><th>Device Name</th><th style="text-align:center;">Remove Device</th><th>Edit</th></tr></thead><tbody>';
    
    $i=0;  
    if(!empty($data)){
        foreach($data as $room){
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_room'.$i.'" class="chkall"><span></span></label></div><input type="hidden" name="room_name'.$i.'" value="'.$room->RoomName.'"></td><td>'.$room->RoomName.'</td><td>'.$room->ProfileName.'</td><td>'.$room->DeviceName.'</td><td style="text-align:center;"><!--<input type="button" class="btn btn-raised btn-primary removed_device" data-devicename="'.$room->DeviceName.'" data-url="'.home_url().'/list-out-rooms/" name="remove_device" id="remove_device" value="Remove Device">--><i class="fa fa-remove fa-2x" data-devicename="'.$room->DeviceName.'" data-url="'.home_url().'/list-out-rooms/" name="remove_device" id="remove_device"></i></td><td><a href="'.get_home_url().'/create_room?RoomName='.$room->RoomName.'" name="edit_room'.$i.'" id="edit_room'.$i.'" class="edit_rooms" data-room_name="'.$room->RoomName.'" data-room_profile_name="'.$room->ProfileName.'">Edit</a></td></tr>';
            $i++;
        }
    }
    
    $content.='</tbody></table>';
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
add_action('admin_post_nopriv_dissync_devices','dissync_devices');
add_action('admin_post_dissync_devices','dissync_devices');
function dissync_devices(){
    $result=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/disassociate_device_from_room',json_encode(array('DeviceName'=>$_POST["disync_device_name"]))));
    //wp_redirect(home_url().'/list-out-rooms?success=1');
    header("Refresh: 0; url= ".get_home_url().'/list-out-rooms?success=1');
    exit;
    
}

add_shortcode('list_room_profile','display_room_profile');
function display_room_profile(){
   
  login_check();
    
    if($_REQUEST['delete']){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Room Profile has been deleted successfully.</div>';
		}
		
    if($_POST){
        
        $no_of_roomprofiles_to_delete = $_POST['no_of_room_profiles'];
        $room_names = array();
        for($i=0;$i<$no_of_roomprofiles_to_delete;$i++){
            $chk_room = 'chk_roomprofile'.$i;
            $roomprofile_name = 'roomprofile_name'.$i;
            if($_POST[$chk_room]){
                $room_names[] = $_POST[$roomprofile_name];
            }
        }
        $req_param = json_encode(array('ProfileName'=>$room_names,'userid'=>get_userid()));
        #print_r($req_param);die();
        $result = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_room_profile',$req_param));
        //wp_redirect(get_home_url().'/list-out-room-profile/');
        if($result->error){
            echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.explode(':',$result->error)[1].'</div>';
        }else{
            header("Refresh: 0; url= ".get_home_url().'/list-out-room-profile?delete=1');
            exit;
        }
    }
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/list_room_profile',json_encode(array('username'=>get_username()))));
    $content='<div class="col-md-6 card card-tiles style-default-light"><form class="form" name="list_room_profile" action="'.home_url().'/list-out-room-profile" method="POST">';
    $content.=' <input class="btn btn-raised btn-primary button_link" type="button" name="create_rp" data-link="'.home_url().'/create-room-profile" value="Create Room Profile"> <input class="btn btn-raised btn-danger delete_rp_action" type="button" name="delete_room_profile" value="Delete Room Profile">';
    $content.='<table class="table no-margin" id="data_table"><thead><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall" class="chkall"></label></div></th><th><b>Room Profile Name</b></th><th></th></tr></thead><tbody>';
    if(!empty($data)){
        $i=0;
        foreach($data as $room_profilename){
            $link = home_url()."/create-room-profile?rp_name=".$room_profilename;
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_roomprofile'.$i.'" class="chkall"><span></span></label></div><input type="hidden" name="roomprofile_name'.$i.'" value="'.$room_profilename.'"></td><td>'.$room_profilename.'</td><td><a href="'.$link.'" data-roomprofile_name="'.$room_profilename.'" class="edit_room_profile">Edit</a></td></tr>';
            $i++;    
        }
    }
    $content.='</tbody></table><!--<input type="hidden" name="action" value="delete_room_profile">--><input type="hidden" name="no_of_room_profiles" value="'.$i.'"></form>';
    /*$content.='<form class="form col-md-12" name="edit_room_profile" action="'.admin_url('admin-post.php').'" method="POST"><input type="hidden" name="edited_roomprofilename" id="edited_roomprofilename" value=""><input type="hidden" name="action" value="edit_roomprofile"></form></div>';*/
    return $content;
}

/**
 * Redirects the Page to Update form for Room profile with POST values
 * 
 */
add_action('admin_post_nopriv_edit_roomprofile','edit_roomprofile');
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

/*add_action('admin_post_delete_room_profile','delete_room_profile');
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
    $req_param = json_encode(array('ProfileName'=>$room_names,'userid'=>get_userid()));
    #print_r($req_param);die();
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_room_profile',$req_param);
    //wp_redirect(get_home_url().'/list-out-room-profile/');
    header("Location: ".get_home_url().'/list-out-room-profile/');
    exit;
}*/

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
add_action('admin_post_nopriv_delete_rooms','delete_rooms');
add_action('admin_post_delete_rooms','delete_rooms');
function delete_rooms(){
    login_check();
    $no_of_rooms_to_delete = $_POST['no_of_rooms'];
    $room_names = array();
    for($i=0;$i<$no_of_rooms_to_delete;$i++){
        $chk_room = 'chk_room'.$i;
        $room_name = 'room_name'.$i;
        if($_POST[$chk_room]){
            $room_names[] = $_POST[$room_name];
        }
    }
    $req_param = json_encode(array('RoomName'=>$room_names,'userid'=>get_userid()));
    //print_r($req_param);die();
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_rooms',$req_param);
    //wp_redirect(get_home_url().'/list-out-rooms/');
    header("Refresh: 0; url=".get_home_url().'/list-out-rooms?delete=1');
    exit;
}

/**
 * Api for create and update the form values to the AWS
 * And redirect the page to list rooms
 */
add_action('admin_post_nopriv_create_room','create_room_api');
add_action('admin_post_create_room','create_room_api');
function create_room_api(){

    if($_POST['purpose'] == 'update'){
        print_r(json_encode($_POST));die();
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
        wp_redirect(home_url().'/create_room/?RoomName='.$_POST['OldRoomName'].'&error='.$error);
    }else{
        //wp_redirect(home_url().'/list-out-rooms/');
        header("Refresh: 1; url=".get_home_url().'/list-out-rooms/');
        //header("Location: ".get_home_url().'/list-out-rooms/');
        exit;
    }
}

/**
 * Shortcode for CREATE and UPDATE the Room Profile
 *
 */

add_shortcode('create_room_profile','create_room_profile');
function create_room_profile(){
    login_check();
    if($_REQUEST['error']){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$_REQUEST['error'].'</div>';
    }
    $ProfileName = $Timezone = $Address = $ClientRequestToken = $MaxVolumeLimit = $readonly = '';
    $SetupModeDisabled   = true;
    $PSTNEnabled         = true;
    $action_room_profile = "create_room_profile";
    if(!empty($_REQUEST['rp_name'])){
        $request = json_encode(array('ProfileName'=>trim($_REQUEST['rp_name']),'userid'=>get_userid()));

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
    
    echo '<div class="col-md-6 card card-tiles style-default-light"><form class="form create_room_profile form-validate" action="'.admin_url('admin-post.php').'" method="POST">

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
    <input type="hidden" name="userid" id="userid" value="'.get_userid().'">
    <input class="btn btn-raised btn-primary create_room_profile_submit" type="Submit" value="submit"/> <input class="btn btn-raised btn-danger button_link" type="button" value="Cancel" data-link="'.get_home_url().'/list-out-room-profile"/>
    </form></div>
    ';
}

/**
 * API for INSERT the Room profile Data to the AWS
 *
 */
add_action('admin_post_nopriv_create_room_profile','create_room_profile_api');
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
        header("Refresh: 0; url=".get_home_url().'/list-out-room-profile/');
        exit;
    }
}

/**
 * API for UPDATE the Room profile Data to the AWS
 *
 */
add_action('admin_post_nopriv_update_room_profile','update_room_profile_api');
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
        header("Refresh: 0; url= ".get_home_url().'/list-out-room-profile/');
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
    login_check();

    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',json_encode(array('user_id'=>get_userid(),'group_users'=>get_group_users()))));

    print'<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="devices_list" method="POST">';
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
add_action('admin_post_nopriv_sync_device','sync_device');
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

    $content='<div class="col-md-6 card card-tiles style-default-light"><form class="form" name="addroom" action="'.admin_url('admin-post.php').'" method="POST">';
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
add_action('admin_post_nopriv_add_device_room','add_device_room');
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
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',json_encode(array('Serial_number'=>$_REQUEST['Serial_number'],'group_users'=>get_group_users()))));
    if($data){
        foreach($data as $devices){
            print'<div class="col-md-6 card card-tiles style-default-light"><form class="form" name="device_form" action="'.admin_url('admin-post.php').'" method="POST">';
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
add_action('admin_post_nopriv_device_form','update_device_form');
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
    login_check();
    $action_for   = "insert";
    $add_request_button_value = "+";
    $noti_display = "none";
    
    if($_POST){
    /*if($_POST['action_for'] == 'update'){
        $data=doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_delete',json_encode(array('request_name'=>[$_POST['RequestName']],'userid'=>get_current_user_id())));
    }*/
    $Form_data['request_name']  = $_POST['RequestName'];
    $Form_data['oldrequest_name']  = $_POST['OldRequestName'];
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
    
    /*$temp=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>$_POST['notification_Temp'],'username'=>get_username(),'userid'=>get_current_user_id()))));
    $Form_data['NotificationTemplate'] = $temp->Items[0]->template;*/
    $Form_data['NotificationTemplate'] = $_POST['notification_Temp'];

    for($i=1;$i<=$_POST['count'];$i++){
        $Form_data['Q'.$i] = addslashes($_POST['guest_request'.$i]);
        $Form_data['A'.$i] = addslashes($_POST['alexa_response'.$i]);
        $Form_data['Conversation'][]=[$Form_data['Q'.$i],'User'];
        if($i == count($_POST['count']) && $_POST['count']!=1){
            $res = 'Alexa';
        }else{
            $res = 'EndConversation';
        }
        $Form_data['Conversation'][]=[$Form_data['A'.$i],$res];
    }
    $Form_data['username']=get_username();
    $Form_data['userid']  =get_userid();
    if($_POST['action_for'] == 'update'){
         $Form_data['last_modified_date']  = $_POST['updatedate'];
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_update',json_encode($Form_data)));
    }else{
       // echo json_encode($Form_data);die();
       $Form_data['last_modified_date']  = date("Y-m-d H:i:s");
       $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_insert',json_encode($Form_data)));
    }
    if($data->error){
        echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$data->error.'</div>';
    }else{
        wp_redirect(get_home_url('').'/requests-list/');
    }
    
    
    $request_name    = $_POST['RequestName'];
    $Status          = $_POST['Status'];
    $RequestType     = $_POST['RequestType'];
    $guest_request1  = $_POST['guest_request1'];
    $alexa_response1 = $_POST['alexa_response1'];
    $guest_request2  = $_POST['guest_request2'];
    $alexa_response2 = $_POST['alexa_response2'];
    $guest_request3  = $_POST['guest_request3'];
    $alexa_response3 = $_POST['alexa_response3'];
    $notification_Temp = $_POST['notification_Temp'];
    $EmailID = $_POST['EmailID'];
    $TextNumber = $_POST['TextNumber'];
    $CallNumber = $_POST['CallNumber'];
    $email_chk_ = !empty($EmailID)?"checked":"";
    $text_chk_  = !empty($TextNumber)?"checked":"";
    $call_chk_  = !empty($CallNumber)?"checked":"";
    $noti_display = ($RequestType=="General Information")?"none":"";
    }
    
    $_Request_Name = ($_GET['RequestName'])?$_GET['RequestName']:$_POST['OldRequestName'];
    
    if($_Request_Name){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/request_info',json_encode(array('request_name'=>$_Request_Name,'userid'=>get_userid()))));
        //print_r($data);
        $request_name = explode('_@_',$data[0]->request_name)[1];
        $Status       = $data[0]->RequestStatus;
        $RequestType  = $data[0]->RequestType;
        $EmailID      = $data[0]->EmailID;
        $TextNumber   = $data[0]->TextNumber;
        $CallNumber   = $data[0]->CallNumber;
        $notification_Temp   = $data[0]->NotificationTemplate;
        #$Level        = $data[0]->Level;
        $Conversation = $data[0]->Conversation;
        $guest_request1 = $Conversation[0][0];  
        $alexa_response1= $Conversation[1][0];
        //$readonly     = "readonly";
        $action_for   = "update";
        $updatedate    = $data[0]->last_modified_date;
        $email_chk_ = !empty($EmailID)?"checked":"";
        $text_chk_  = !empty($TextNumber)?"checked":"";
        $call_chk_  = !empty($CallNumber)?"checked":"";
        $email_dis_ = ($email_chk_ == "checked")?"":"readonly";
        $text_dis_  = ($text_chk_ == "checked")?"":"readonly";
        $call_dis_  = ($call_chk_ == "checked")?"":"readonly";
        $add_request_button_value = (count($Conversation) == 2)?"+":"-";
        $noti_display = ($RequestType=="General Information")?"none":"";
        
    }
    $status_arr = array('active'=>'Active','inactive'=>'InActive');
    echo '<div class="col-md-6 card card-tiles style-default-light"><form class="form create_user create_request form-validate" action="'.home_url().'/create-request" method="POST" name="request">';
    echo '<div class="form-group floating-label requestname">
    <input class="form-control" type="textbox" name="RequestName" id="RequestName" value="'.$request_name.'" required '.$readonly.'><input class="form-control" type="hidden" name="OldRequestName" id="OldRequestName" value="'.$_Request_Name.'">
    <label for="RequestName ">Request Name *</label></div>';
    
    echo '<input class="form-control" type="hidden" name="updatedate" id="updatedate" value="'.$updatedate.'">';
    
    echo '<div class="form-group floating-label status">
    <select class="form-control" name="Status" id="Status">';
    foreach($status_arr as $sta_key=>$sta_val){
        $_sel = ($Status==$sta_key)?"selected":"";
        echo "<option value='".$sta_key."' ".$_sel.">".$sta_val."</option>";
    }
    echo'</select><label for="Status ">Status *</label></div>';
    
    $request_types=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_request_types',json_encode(array('request_type'=>''))));
    echo '<div class="form-group floating-label requesttype">
    <select class="form-control" name="RequestType" id="RequestType"><!--<option value="general_information">General Information</option><option value="service_request">Service Request</option>-->';
    
    
    foreach($request_types->Items as $type){
        $_sel = ($RequestType == $type->request_type)?"selected":"";
        echo'<option value="'.$type->request_type.'" '.$_sel.'>'.$type->request_type.'</option>';
    }
    echo'</select><label for="RequestType">Request Type *</label></div>';

    echo '<div class="form-group floating-label guestrequest guestrequest1">
    <input class="form-control" type="textbox" name="guest_request1" id="guest_request1" value="'.str_replace("\\","",$guest_request1).'" required><label for="guest_request1">Guest Request *</label></div>';
    echo '<div class="form-group floating-label alexaresponse alexaresponse1">
    <textarea class="form-control" name="alexa_response1" id="alexa_response1" style="width:400px;" required>'.str_replace("\\","",$alexa_response1).'</textarea>
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
            <input class="form-control" type="textbox" name="guest_request'.$j.'" id="guest_request'.$j.'" value="'.str_replace("\\","",$result).'" required><label for="guest_request'.$j.'">Guest Request *</label></div>';
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
            <textarea class="form-control" name="alexa_response'.$j.'" id="alexa_response'.$j.'" style="width:400px;" required>'.str_replace("\\","",$result).'</textarea>
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
    
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>'','username'=>get_username()))));
    
    echo '<div class="form-group floating-label notificationtemplate" style="display:'.$noti_display.';"><select name="notification_Temp" id="notification_Temp" class="form-control"><option value=""></option>';
    
    if(!empty($data->Items)){
        foreach($data->Items as $temp){
        $tempName = explode('_@_',$temp->template_name)[1];
        $_sel = ($notification_Temp == $tempName)?"selected":"";    
        echo '<option value="'.$tempName.'" '.$_sel.'>'.$tempName.'</option>';
        }
    }
    echo'</select><label for="notification_Temp ">Notification Template</label></div>';
    echo '<p id="notification_Temp_error" style="color:rgb(169, 68, 66);display:none;">This field is required.</p>';
    echo '<div class="notification" style="display:'.$noti_display.';"><label for="alexa_response ">Notification</label>
    <table class="table no-margin"><tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="email_chk" id="email_chk" value="email" '.$email_chk_.'></label></div></td><td>Email:</td><td><input class="form-control" type="textbox" name="EmailID" id="EmailID" value="'.$EmailID.'" '.$email_dis_.'><p id="EmailID_error" style="color:rgb(169, 68, 66);display:none;">This field is required.</p><p>Multiple email addresses separated by semicolon (;)</p></td></tr>
    <tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="text_chk" id="text_chk" value="text" '.$text_chk_.'></td><td>Text:</td><td><input class="form-control" type="text" name="TextNumber" id="TextNumber" value="'.$TextNumber.'" '.$text_dis_.'></label></div><p id="TextNumber_error" style="color:rgb(169, 68, 66);display:none;">This field is required.</p></td></tr>
    <tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="call_chk" id="call_chk" value="call" '.$call_chk_.'></label></div></td><td>Call:</td><td><input class="form-control numberonly" type="text" name="CallNumber" id="CallNumber" value="'.$CallNumber.'" '.$call_dis_.'><p id="CallNumber_error" style="color:rgb(169, 68, 66);display:none;">This field is required.</p></td></tr>
    <tr><td colspan="3"><div style="color:#a94442;display:none;" id="noti_req">Any one Notification is required.</div></td></tr>
    </table></div>'; 
    /*echo '<div class="form-group floating-label notificationtemplate"><input class="form-control" type="textbox" name="notification_Temp" id="notification_Temp" value="'.$notification_Temp.'" required><label for="notification_template ">Notification Template</label></div>';  */
    
    echo '<input class="btn btn-raised btn-primary" type="submit" name="sub" id="sub" value="Submit"> <input class="btn btn-raised btn-danger button_link" type="button" name="request_can" id="request_can" data-link="'.get_home_url().'/requests-list/" value="Cancel"><!--<input type="hidden" name="action" value="insert_request">--><input type="hidden" name="action_for" id="action_for" value="'.$action_for.'">';                    
    echo '</form></div>';        
}

/**
 * Form to Insert the Request to DynamoDB
 * And Redirects to the List of the Pages
 */

/*add_action('admin_post_insert_request','insert_request');
function insert_request(){
    if($_POST['action_for'] == 'update'){
        $data=doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_delete',json_encode(array('request_name'=>[$_POST['RequestName']],'userid'=>get_userid())));
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
    
    $temp=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>$_POST['notification_Temp'],'username'=>get_username(),'userid'=>get_userid()))));
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
    $Form_data['userid']  =get_userid();
    //print_r(json_encode($Form_data));die();
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_insert',json_encode($Form_data)));
    wp_redirect(get_home_url('').'/requests-list/');
} */

/**
 * Displays the Requests List
 * And Performs the Delete and Edit action
 */

function date_compare($a, $b)
    {
        
         if (is_object($a)) {
            $a = get_object_vars($a);
        }
        if (is_object($b)) {
            $b = get_object_vars($b);
        }
        
        
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t2 - $t1;
    }   



add_shortcode('request_list','request_list');
function request_list(){
    login_check();
    if($_REQUEST['delete']){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Request has been deleted successfully.</div>';
		}
		
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_read',json_encode(array('username'=>get_username()), true)));
    usort($data, 'date_compare'); 
 
    
    print'<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="requests" action="'.admin_url('admin-post.php').'" method="POST">';
    print"<input class='btn btn-raised btn-primary button_link' type='button' name='create_request' id='create_request' data-link='".get_home_url()."/create-request/' value='Create Request'> <input class='btn btn-raised btn-danger delete_action' type='button' name='delete_request' id='delete_request' value='Delete Request'> ";
    print"<table class='table no-margin' id='data_table'><thead><tr><th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th><th>Request Name</th><th>Request Type</th><th>Status</th><th>Notifications</th><th></th></tr></thead><tbody>";
    for($i=0;$i<count($data);$i++){ 
        $RequestName = explode('_@_',$data[$i]->request_name)[1];
        $call_Icon = $text_Icon = $email_Icon = "";
        if($data[$i]->RequestType != 'General Information'){
        $email_Icon = !empty($data[$i]->EmailID)?"<i class='md md-email'></i>":"";
        $text_Icon = !empty($data[$i]->TextNumber)?"<i class='md md-message'></i>":"";
        $call_Icon = !empty($data[$i]->CallNumber)?"<i class='md md-call'></i>":"";
        }
        
        print"<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_request".$i."' id='chk_request".$i."' class='chkall' value='".$RequestName."'></label></div></td>
        <td>".$RequestName."</td>
        <td>".$data[$i]->RequestType."</td>
        <td>".$data[$i]->RequestStatus."</td>
        <td>".$call_Icon." ".$text_Icon." ".$email_Icon."</td>
        <td><a href='".get_home_url()."/create-request?RequestName=".$RequestName."'>Edit</a></td></tr>";
    }
    print"</tbody></table><input type='hidden' name='action' id='action' value='delete_request'><input type='hidden' name='no_of_requests' id='no_of_requests' value='".$i."'></form></div>";
}

/**
 * Deletes the selected requests
 * 
 */
add_action('admin_post_nopriv_delete_request','delete_request');
add_action('admin_post_delete_request','delete_request');
function delete_request(){
    login_check();
    $no_of_requests = $_POST['no_of_requests'];
    $request_names  = array();
    for($i=0;$i<=$no_of_requests;$i++){
        if($_POST['chk_request'.$i]){
            $request_names[] = $_POST['chk_request'.$i];
        }
    }
    $request_text = json_encode(array('request_name'=>$request_names,'userid'=>get_userid()));
    $data=doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_delete',$request_text);
    wp_redirect(get_home_url('').'/requests-list?delete=1');
}

/**
 * Settings Page
 * Has Name , UserName , Password , Account Type Field
 * 
 */

add_shortcode('settings2','settings1');
function settings22(){
    $current_user = wp_get_current_user();

    print'<div class="col-md-6 card card-tiles style-default-light"><form class="form" name="requests" action="'.admin_url('admin-post.php').'" method="POST">';
    //print'<div class="form-group floating-label name"><input class="form-control" type="textbox" name="name" id="name" value="'.$current_user->display_name.'" required><label for="name">Name</label></div>';
     print'<div class="form-group floating-label name"><input class="form-control" type="textbox" name="first_name" id="first_name" value="'.$current_user->first_name.'" required><label for="first_name">First Name</label></div>';
      print'<div class="form-group floating-label name"><input class="form-control" type="textbox" name="last_name" id="last_name" value="'.$current_user->last_name.'" required><label for="last_name">Last Name</label></div>';
    print'<div class="form-group floating-label name"><input class="form-control" type="textbox" name="email" id="email" readonly value="'.$current_user->user_email.'" required><label for="email">Email</label></div>';
    print'<div class="form-group floating-label username"><input class="form-control" type="textbox" name="username" id="username" value="'.$current_user->user_login.'" required><label for="username">User Name</label></div>';
    print'<div class="form-group floating-label passwords"><input class="form-control" type="password" name="passwords" id="passwords" size="7" value="'.$password.'" required style="width:250px;"><label for="password">Password</label></div>';
     print'<div class="form-group floating-label passwords"><input class="form-control" type="password" name="confirm_password" id="confirm_password" size="7" value="'.$password.'" required style="width:250px;"><label for="password">Confirm Password</label></div>';
    print'<div class="form-group floating-label accounttype"><select class="form-control" name="accounttype" id="accounttype"><option name="trial">Trial</option></select><label for="accounttype">Account Type</label></div>';
    print'<input class="btn btn-raised btn-primary" type="submit" name="save" id="save" value="Save"><input type="hidden" name="user_id" id="user_id" value="'.$current_user->ID.'"><input type="hidden" name="action" id="action" value="userupdate">';
    print'</form></div>';
}

add_shortcode('settings','settings');
function settings(){
    login_check();
    $current_user = wp_get_current_user();
 if($_POST){
       
        return get_template_html('my_setting',$attributes);
       
    }else{
        return get_template_html('my_setting',$current_user);
    }
}

/**
 * Update the Users Settings
 */
add_action('admin_post_nopriv_userupdate','usersetting');
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


add_action('admin_post_nopriv_usermenuupdate','usermenusetting');
add_action('admin_post_usermenuupdate','usermenusetting');
function usermenusetting(){
	if($_POST){
	    
			$id = (int) get_current_user_id(); 
			$user_detail  = get_user_by('ID',$id);
			$password = $_POST['password']?$_POST['password']:$user_detail->user_pass;
		
			$user_id = wp_update_user( array(
				'ID' => $id,
				'user_pass'   =>  $password,  
				'display_name'  =>  $_POST['display_name'],
				'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
				'user_login'   =>  $_POST['username']
			));
			 
			 wp_redirect(get_home_url('').'/settings/?result=success');
		} else 
		 {
	             wp_redirect(get_home_url('').'/settings/');
		 }
}



add_shortcode('responses','responses');
function responses(){
    login_check();
    $attributes = array('userid'=>get_userid(),'from'=>'response');
    return get_template_html('responses',$attributes);
}

add_shortcode('audit_log','audit_log_index');
function audit_log_index(){
    login_check();
    $attributes = array('userid'=>get_userid(),'from'=>'audit_log');
    return get_template_html('responses',$attributes);
}

function audit_log_index1(){
    login_check();
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

    print'<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="devices_list" action="'.get_home_url()."/audit-log".'" method="POST">';
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
    
    print"<table class='table no-margin' id='data_table_audit' ><thead><tr><th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th><th>Date Time</th><th>Response Status</th><th>Request Name</th><th>Request Type</th><th>Room No</th><th>View Log</th></tr></thead>";

    print "<tbody>";


    $i=0;
    if($data){
        foreach($data->Items as $response){
            print "<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_device".$i."' id='chk_device".$i."' class='chkall'><span></span></label></div></td><td>".$response->Date."</td><td>Success</td><td>".explode('_@_',$response->RequestName)[1]."</td><td>".$response->RequestType."</td><td>".$response->RoomNumber[1]."</td><td><a href='".get_home_url().'/device-form?RequestName='.$response->RequestName."'>View Log</a></td></tr>";
            $i++;
        }
    }

    print"</tbody></table>";
    print"</form></div>";

}


add_shortcode('reports','report_page');
function report_page(){
    login_check();
   print"<div class='col-md-6 card card-tiles style-default-light reports'>
   <div>
   <!--<a href='".get_home_url()."/activity-report'>Activity Report</a>-->
   <a href='".get_home_url()."/activity_reports'>Activity Report</a>
   </div>";
   print"
   <div>
   <!--<a href='".get_home_url()."/response-by-category'>Response By Category</a>-->
   <a href='".get_home_url()."/responses'>Response By Category</a>
   </div>";
   print"
   <div>
   <!--<a href='".get_home_url()."/activity-by-roomtype'>Response By Room Types</a>-->
   <a href='".get_home_url()."/responses'>Response By Room Types</a>
   </div></div>";
}




add_shortcode('login','login');
function login(){
	if(!is_user_logged_in()) {
		//return get_template_html('login');
		return wp_redirect( wp_login_url() );
	}else if(is_user_logged_in()) {
	    if ( in_array( 'administrator', (array) $user->roles ) ) {
	        return wp_redirect( home_url().'/wp-admin' );
	    }else{
		    return wp_redirect( home_url().'/dashboard' );
	    }
	}else
	{
        //return get_template_html('login');
        return wp_redirect( wp_login_url() );
	}
}


//add_action( 'admin_init', 'redirect_non_admin_users' );
/**
 * Redirect non-admin users to home page
 *
 * This function is attached to the 'admin_init' action hook.
 */
function redirect_non_admin_users() {
	if ( ! current_user_can( 'manage_options' ) && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF'] ) {
	    $current_user   = wp_get_current_user();
        $role_name      = $current_user->roles[0];
        if ( 'administrator' === $role_name ) {
            wp_redirect( home_url().'/wp-admin' );
    		exit;
        }else{
    		wp_redirect( home_url().'/dashboard' );
    		exit;
        }
	}
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

/*function wpse_133647_custom_lost_password_page() {
    return home_url('/lost-password');
} // function wpse_133647_custom_lost_password_page
add_filter('lostpassword_url', 'wpse_133647_custom_lost_password_page');*/
    
function redirect_login_page(){
    global $wpdb,$pagenow;
    // permalink to the custom login page
    if($GLOBALS['pagenow'] === 'wp-login.php' && !is_user_logged_in()){
        $login_page  = get_site_url().'/login';
        wp_redirect( $login_page);
        exit;
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
    
    /*$IAM_User=array();
    $IAM_User['Path']       = $hotel_name;
    $IAM_User['UserName']   = $email;
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($IAM_User));*/
 
    $user_id = wp_insert_user( $user_data );
    update_user_meta($user_id,'business_name',$hotel_name);
    wp_new_user_notification_custom( $user_id, null );
 
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
    login_check();
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
add_action('admin_post_nopriv_user_register','user_register');
add_action('admin_post_user_register','user_register');
function user_register(){
   
        $display_name = ($_POST['first_name'])?$_POST['first_name']:$_POST['email'];
        if(!$_POST['userid']){
            $userdata = array(
                'user_login'  =>  $_POST['user_login'],
                'user_pass'   =>  $_POST['password'],  
                'display_name'=>  $_POST['hotel_name'],
                'first_name'  =>  $_POST['first_name'],  
                'last_name'   =>  $_POST['last_name'],  
                'user_email'  =>  $_POST['email'],  
                'role'        =>  $_POST['role']  
                
            );
            $user_id = wp_insert_user( $userdata ) ;
            if($user_id){
                if($_POST['role'] == 'subscriber'){
                    update_user_meta($user_id,'business_name',$_POST['hotel_name']);
                    update_user_meta( $user_id, 'business_address', $_POST['business_address']);
                    update_user_meta( $user_id, 'business_phone', $_POST['business_phone']);
                    
                    $IAM_User=array();
                    $IAM_User['Path']       = $_POST['hotel_name'];
                    //$IAM_User['UserName'] = $_POST['email'];
                    $IAM_User['UserName']   = $_POST['hotel_name'];
                    
                    if($_POST['hotel_name']){
                        doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($IAM_User));
                    }
                }else if($_POST['role'] == 'editor'){
                    $hotel_names = explode("_@_",$_POST['hotel_names']);
                    //update_user_meta($user_id,'user_belongs_to',get_current_user_id());
                    update_user_meta($user_id,'user_belongs_to',$hotel_names[0]);
                    update_user_meta($user_id,'business_name_belongs',$hotel_names[1]);
                }
                wp_new_user_notification_custom($user_id,null,'both');
                //wp_new_user_notification($user_id, null, 'both');
            }
            if ( is_wp_error( $user_id  ) ) {
                $e = $user_id->get_error_message();
                wp_redirect(add_query_arg( array('errors'=>str_replace(" ","%20",$e),'post'=>$userdata), home_url().'/user-creation' ));
            }else{
                wp_redirect( $_POST['redirect_to'].'?success=1' );
            }
        }else{
            $id = (int) $_POST[ 'userid' ]; 
            $user_detail  = get_user_by('ID',$id);
            $password = $_POST['password']?$_POST['password']:$user_detail->user_pass;
            
            $user_id = wp_update_user( array(
                'ID' => $id,
                'user_login'  => $_POST['user_login'],
                'user_email'  => $_POST['email'],
                'user_pass'   => $password,  
                'display_name'=> $_POST['hotel_name'],
                'first_name'  => $_POST['first_name'],  
                'last_name'   => $_POST['last_name'], 
            ));
             
            if ( is_wp_error( $user_id  ) ) {
            $e = $user_id->get_error_message();
            wp_redirect(add_query_arg( array('errors'=>str_replace(" ","%20",$e),'post'=>$userdata), home_url().'/user-creation?userid='.$id ));
            }else{
                if($_POST['role'] == 'subscriber'){
                    update_user_meta($user_id,'business_name',$_POST['hotel_name']);
                    update_user_meta( $user_id, 'business_address', $_POST['business_address']);
                    update_user_meta( $user_id, 'business_phone', $_POST['business_phone']);
                    
                    /*$IAM_User=array();
                    $IAM_User['Path']       = $_POST['business_name'];
                    //$IAM_User['UserName']   = $_POST['email'];
                    $IAM_User['UserName']   = $_POST['business_name'];
                    
                    if($_POST['business_name']){
                        doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($IAM_User));
                    }*/
                    if($_POST['hotel_name'] != $_POST['old_hotel_name']){
                        $data = array();
                        $data['UserName']   =$_POST['old_hotel_name'];
                        $data['NewPath']    =$_POST['hotel_name'];
                        $data['NewUserName']=$_POST['hotel_name'];
                        doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_users',json_encode($data));
                    }
                    
                }else if($_POST['role'] == 'editor'){
                    $hotel_names = explode("_@_",$_POST['hotel_names']);
                    update_user_meta($user_id,'user_belongs_to',$hotel_names[0]);
                    update_user_meta($user_id,'business_name_belongs',$hotel_names[1]);
                }
                //wp_redirect( esc_url( add_query_arg( 'success', '1', home_url().'/register' ) ) );
                wp_redirect( $_POST['redirect_to'].'?updated=1' );
            }
        }
        
}


add_shortcode('users_list','users_list');
function users_list(){
    login_check();
    if($_REQUEST['success'] == 1){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">User has been created successfully.</div>';
    }
    else if($_REQUEST['updated'] == 1){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">User has been Updated successfully.</div>';
    }
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'administrator'){
        $args=array();
    }else{
        $args=array(
            'meta_key'=>'user_belongs_to',
            'meta_value'=>get_userid()
        );
    }
    $users = get_users($args);
    //print_r($users);
    $content='<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="user_list" action="'.admin_url('admin-post.php').'" method="POST">';
    $content.=' <a href="'.get_home_url().'/user-creation/'.'" name="createuser" id="createuser"><input class="btn btn-raised btn-primary" type="button" value="Create User"></a> <input class="btn btn-raised btn-danger delete_action" type="button" name="deleteusers" id="deleteusers" value="Delete Users">';
    $content.='<table class="table no-margin" id="data_table_userlist"><thead><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>User Name</th><th>E-Mail</th><th>Role</th><th>Edit</th></tr></thead><tbody>';
    
    $i=0;  
    if(count($users)){
        foreach($users as $user){
            if($user->roles[0]!='administrator'){
                if($user->roles[0] == 'editor'){
                    $role = 'Users';
                }else if($user->roles[0] == 'subscriber'){
                    $role = 'Administrator';
                }
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_user'.$i.'" class="chkall"><span></span></label></div><input type="hidden" name="user_id'.$i.'" value="'.$user->ID.'"></td><td>'.$user->user_login.'</td><td>'.$user->user_email.'</td><td>'.$role.'</td><td><a href="'.get_home_url().'/user-creation?userid='.$user->ID.'" name="edit_room'.$i.'" id="edit_room'.$i.'" class="edit_rooms">Edit</a></td></tr>';
            $i++;
            }
        }
    }
    
    $content.='</tbody></table>';
    $content.='<input type="hidden" name="action" value="deleteusers"><input type="hidden" name="no_of_users" value="'.$i.'"></form></div>';
    return $content;
}
add_action('admin_post_nopriv_deleteusers','deleteusers');
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
    echo '<div class="col-md-6 card card-tiles style-default-light"><form class="form request_type_template form-validate" action="'.admin_url('admin-post.php').'" method="POST">

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
add_action('admin_post_nopriv_request_types','request_types');
add_action('admin_post_request_types','request_types');
function request_types(){
    login_check();
    if($_POST['action_for']=='update'){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_request_type',json_encode(array('request_type'=>$_POST['requesttype_name'],'old_request_type'=>$_POST['old_request_type_name']))));
    }else{
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_request_types',json_encode(array('request_type'=>$_POST['requesttype_name']))));
    }
    wp_redirect(get_home_url().'/request-type-temp-list');
}

add_shortcode('request-type-temp-list','request_type_list');
function request_type_list(){
    login_check();
    	if($_REQUEST['delete']){
        echo '<div class="alert alert-callout alert-success col-md-offset-3 col-md-6">Room has been deleted successfully.</div>';
		}
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_request_types',json_encode(array('request_type'=>''))));
    
    $content='<div class="col-md-10 card card-tiles style-default-light"><form class="form" name="delete_template" action="'.admin_url('admin-post.php').'" method="POST">';
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
add_action('admin_post_nopriv_delete_req_templates','delete_req_templates');
add_action('admin_post_delete_req_templates','delete_req_templates');
function delete_req_templates(){
    login_check();
    $temp_to_delete=array();
    for($i=0;$i<=$_POST['no_of_temps'];$i++){
        $chk = 'chk_template'.$i;
        if(!empty($_POST[$chk])){
            $temp_to_delete[]=$_POST[$chk];
        }
    }
    $temp_to_delete = json_encode(array('request_type'=>$temp_to_delete));
    
    
    doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/request_type_delete',$temp_to_delete);
    wp_redirect(get_home_url('').'/request-type-temp-list');
}

add_shortcode('notification_template','notification_template');
function notification_template(){
    login_check();
		
    if($_POST){
        if($_POST['action_for']=='update'){
            $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_notification_template',json_encode(array('template_name'=>$_POST['template_name'],'old_template_name'=>$_POST['old_template_name'],'template'=>$_POST['tempcontent'],'username'=>get_username(),'userid'=>get_userid()))));
        }else{
            $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_notification_template',json_encode(array('template_name'=>$_POST['template_name'],'old_template_name'=>$_POST['old_template_name'],'template'=>$_POST['tempcontent'],'username'=>get_username(),'userid'=>get_userid()))));
        }
        if($data->error){
            echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$data->error.'</div>';
        }else{

            wp_redirect(get_home_url().'/notification-temp-list/');
            exit();
        }
        $template_name = $_POST['template_name'];
        $tempcontent   = $_POST['tempcontent'];
    }
    $temp_name = $_REQUEST['TempName']?$_REQUEST['TempName']:'';
    $old_template_name = ($_GET['TempName'])?$_GET['TempName']:$_POST['old_template_name'];
    if($temp_name){
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>$temp_name,'username'=>get_username(),'userid'=>get_userid()))));
        $template_name       = explode('_@_',$data->Items[0]->template_name)[1];
        $tempcontent= $data->Items[0]->template;
    }
    $action_for = empty($old_template_name)?'insert':'update';
    echo '<div class="col-md-6 card card-tiles style-default-light"><form class="form request_type_template form-validate" action="'.home_url().'/notification_template" method="POST">

    <div class="form-group floating-label name">
    <input class="form-control" type="textbox" name="template_name" id="template_name" value="'.str_replace("\\","",$template_name).'" '.$readonly.' style="width:200px;">
    <label style="height: 50px;" for="template_name">Notification Template Name *</label>
    <div id="errorMessage_noti" style="display: none; color: rgb(169, 68, 66);"></div>
    </div>  

    <div class="form-group floating-label content">
    <textarea class="form-control" name="tempcontent" id="tempcontent" style="width:400px;">'.str_replace("\\","",$tempcontent).'</textarea>
    <label style="height: 65px;" for="tempcontent">Template *</label>
    <div id="errorMessage_noti_content" style="display: none; color: rgb(169, 68, 66);"></div>
    </div>     
    
    <div>
    Note: Dynamic Variables : #roomno
    </div>
    
    <!--<input type="hidden" name="action" value="notification_template">-->
    <input type="hidden" name="action_for" value="'.$action_for.'">
    <input type="hidden" name="old_template_name" value="'.$old_template_name.'">
    <input class="btn btn-raised btn-primary" id="noti_submit" type="button" value="submit"/>
    <input class="btn btn-raised btn-danger button_link" type="button" value="Cancel" data-link="'.get_home_url().'/notification-temp-list"/>
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
    login_check();
    return get_template_html('notification_temp_list');
}

/*add_action('admin_post_delete_noti_templates','delete_noti_templates');
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
}*/


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
	//$message .= __('To set your password, visit the following address:') . "\r\n\r\n";
	//$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";
	
	$message .= __('To set your password, click <a href="'.network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login').'">here</a>') . "\r\n\r\n";
	
	//$message .= '<' . network_site_url("custom-reset-password?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";

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
    login_check();
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



add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );
add_action( 'user_new_form', 'extra_user_profile_fields' );

function extra_user_profile_fields( $user ) { 
    $role = get_user_by('id',$_REQUEST['user_id'])->roles[0];
    if($_REQUST['business_name']){
        $business_name_inp_val = $_REQUST['business_name'];
    }else{
        $business_name_inp_val = esc_attr( get_the_author_meta( 'business_name', $user->ID ) );
    }
    
    ?>
    <div class="profile_information" style="display:<?php echo ($role=='administrator')?"none":""; ?>">
    <h3><?php _e("Extra profile information", "blank"); ?></h3>
    
    <table class="form-table">
    <tr class="form-field form-required business_name_inp" style="display:<?php echo $role?(($role=='subscriber')?"":"none"):""; ?>">
        <th><label for="business_name"><?php _e("Business Name"); ?><span class="description">(required)</span></label></label></th>
        <td>
            <input type="text" name="business_name" id="business_name" value="<?php echo $business_name_inp_val; ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Please enter your Business Name."); ?></span>
            <input type="hidden" name="old_business_name" id="old_business_name" value="<?php echo esc_attr( get_the_author_meta( 'business_name', $user->ID ) ); ?>">
        </td>
    </tr>
    <?php
            $business_names = array();
            $business_name = get_the_author_meta( 'business_name', $user->ID );
            $users = get_users(array(
                'meta_key'     => 'business_name',
            ));
            
            foreach($users as $user){
                if(get_user_meta($user->ID,'business_name',true)){
                    $business_names[$user->ID]=get_user_meta($user->ID,'business_name',true);
                }
            }
            ?>
    <tr class="form-field business_name_sel" style="display:<?php echo $role?(($role=='editor')?"":"none"):"none"; ?>">
        <th><label for="business_name"><?php _e("Business Name"); ?><span class="description">(required)</span></label></label></th>
        <td>
            <select name="business_names" id="business_names" class="regular-text" />
            <option value=""></option>
            <?php
            foreach($business_names as $userid=>$business_name){
                $_sel = trim(get_the_author_meta( 'business_name_belongs', $_REQUEST['user_id'])) == trim($business_name)?"selected":"";
                ?>
                <option value="<?php echo $userid."_@_".$business_name?>" <?=$_sel?>><?php echo $business_name?></option>
                <?php
            }
            ?>
            </select><br />
            <span class="description"><?php _e("Please select your Business Name."); ?></span>
        </td>
    </tr>
    
    <tr class="form-field business_name_inp" style="display:<?php echo $role?(($role=='subscriber')?"":"none"):""; ?>">
        <th><label for="business_address"><?php _e("Business Address"); ?></label></th>
        <td>
            <textarea name="business_address" id="business_address"><?php
            echo trim(get_the_author_meta( 'business_address', $_REQUEST['user_id']))
            ?></textarea>
        </td>
    </tr>
    <tr class="form-field business_name_inp" style="display:<?php echo $role?(($role=='subscriber')?"":"none"):""; ?>">
        <th><label for="business_phone"><?php _e("Business Phone"); ?></label></th>
        <td>
            <input type="text" name="business_phone" id="business_phone" class="regular-text" value="<?php echo get_the_author_meta( 'business_phone', $_REQUEST['user_id'])?>"/>
        </td>
    </tr>
    </table>
    <script>
    jQuery(document).ready(function( $ ) {
    $(document).on('change','#role',function(){
        if($(this).val() == 'subscriber'){
            $('.profile_information').show();
            $('.business_name_inp').show();
            $('.business_name_sel').hide();
        }else if($(this).val() == 'editor'){
            $('.profile_information').show();
            $('.business_name_inp').hide();
            $('.business_name_sel').show();
            $('.business_name_sel').addClass('form-required');
        }else{
            $('.profile_information').hide();
        }
    });
    });
    </script>
    </div>
<?php }
                    
add_action( 'user_profile_update_errors', 'crf_user_profile_update_errors', 10, 3 );
function crf_user_profile_update_errors( $errors, $update, $user ) {
    
    if(trim($_POST['role']) == 'subscriber'){
    	if ( empty( $_POST['business_name'] ) ) {
    		$errors->add( 'business_name_error', __( '<strong>ERROR</strong>: Please enter the business name.', 'crf' ) );
    	}else if(!empty( $_POST['business_name'] )){
    	    if (preg_match('/[^a-z_\-+=,.@0-9]/i', $_POST['business_name'])) {
                $msg = "Invalid Business name. Business names can contain alphanumeric characters, or any of the following: _+=,.@-";
                $errors->add( 'business_name_error', __( '<strong>ERROR</strong>: '.$msg.'.', 'crf' ) );
            }else{
                if($_POST['business_name']!=$_POST['old_business_name']){
                    $users = get_users(array(
                        'meta_key'     => 'business_name',
                        'meta_value'  => $_POST['business_name']
                    ));
                    if(!empty($users) && $_POST['business_name']){
                        $errors->add( 'business_name_error', __( '<strong>ERROR</strong>: The Business Name is already used.', 'crf' ) );
                    }
                }
            }
    	}
    }else if(trim($_POST['role']) == 'editor'){
    	if ( empty( $_POST['business_names'] ) ) {
    		$errors->add( 'business_name_error', __( '<strong>ERROR</strong>: Please select the business name.', 'crf' ) );
    	}
    }
    
    if($update){
        add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
        add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );
        add_action( 'profile_update', 'save_extra_user_profile_fields');
    }
}

add_action( 'user_register', 'save_extra_user_profile_fields_new');
function save_extra_user_profile_fields_new( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    if($_POST['role'] == 'subscriber'){
        $business_name = $_POST['business_name'];
        update_user_meta( $user_id, 'business_name', $business_name );
        update_user_meta( $user_id, 'business_address', $_POST['business_address']);
        update_user_meta( $user_id, 'business_phone', $_POST['business_phone']);
        
    }else if($_POST['role'] == 'editor'){
        $business_names = explode("_@_",$_POST['business_names']);
        update_user_meta( $user_id, 'business_name_belongs', $business_names[1] );
        //update_user_meta( $user_id, 'business_address', $_POST['business_address']);
        //update_user_meta( $user_id, 'business_phone', $_POST['business_phone']);   
        update_user_meta( $user_id, 'user_belongs_to', $business_names[0]);
    }
    
    
    $IAM_User=array();
    $IAM_User['Path']       = $_POST['business_name'];
    //$IAM_User['UserName']   = $_POST['email'];
    $IAM_User['UserName']   = $_POST['business_name'];
    
    if($_POST['business_name']){
        doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/add_new_user',json_encode($IAM_User));
    }
}


function save_extra_user_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    if($_POST['role'] == 'subscriber'){
        $business_name = $_POST['business_name'];
        update_user_meta( $user_id, 'business_name', $business_name );
        update_user_meta( $user_id, 'business_address', $_POST['business_address']);
        update_user_meta( $user_id, 'business_phone', $_POST['business_phone']);
        
        if($_POST['business_name']!=$_POST['old_business_name']){
            $data = array();
            $data['UserName']   =$_POST['old_business_name'];
            $data['NewPath']    =$_POST['business_name'];
            $data['NewUserName']=$_POST['business_name'];
            doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/update_users',json_encode($data));
        }
        
    }else if($_POST['role'] == 'editor'){
        $business_names = explode("_@_",$_POST['business_names']);
        $business_name = $business_names[1];
                    
        update_user_meta( $user_id, 'business_name_belongs', $business_name );
        //update_user_meta( $user_id, 'business_address', $_POST['business_address']);
        //update_user_meta( $user_id, 'business_phone', $_POST['business_phone']);        
        update_user_meta( $user_id, 'user_belongs_to', $business_names[0]);
        
    }
}

add_action('manage_users_columns','kjl_modify_user_columns');
function kjl_modify_user_columns($column_headers) {
    $column_headers['business_name'] = 'Business Name';
    return $column_headers;
}

add_action('manage_users_custom_column', 'kjl_user_posts_count_column_content', 10, 3);
function kjl_user_posts_count_column_content($value, $column_name, $user_id) {
    $user = get_userdata( $user_id );
    if ( 'business_name' == $column_name ) {
        if(get_user_by('id',$user_id)->roles[0] == 'subscriber'){
            return get_user_meta($user_id,'business_name',true);
        }else if(get_user_by('id',$user_id)->roles[0] == 'editor'){
            return get_user_meta($user_id,'business_name_belongs',true);
        }
    }
    return $value;
}

/**
* remove the register link from the wp-login.php script
*/
add_filter('option_users_can_register', function($value) {
$script = basename(parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH));

if ($script == 'wp-login.php') {
$value = false;
}

return $value;
});


function wpse_custom_retrieve_password_message( $message, $key ) {
    $errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
	} elseif ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
		if ( empty( $user_data ) )
			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}
	
    $user_login = $user_data->user_login;
	$user_email = $user_data->user_email;
	$key = get_password_reset_key( $user_data );
	
    if ( is_multisite() ) {
		$site_name = get_network()->site_name;
	} else {
		/*
		 * The blogname option is escaped with esc_html on the way into the database
		 * in sanitize_option we want to reverse this for the plain text arena of emails.
		 */
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	
    $message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
	/* translators: %s: site name */
	$message .= sprintf( __( 'Site Name: %s'), $site_name ) . "\r\n\r\n";
	/* translators: %s: user login */
	$message .= sprintf( __( 'Username: %s'), $user_login ) . "\r\n\r\n";
	$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
	$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
	//$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";
	
	//$message .= __( 'To reset your password, click <a href="'.network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ).'">here</a>' ) . "\r\n\r\n";
	//$message .= __('To reset your password, click <a target="_blank" href="'.network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login').'">here</a>') . "\r\n\r\n";
    //$message .= 'url : '.network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login));
    
    
    $message .= '<' . network_site_url("custom-reset-password?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n\r\n";
    return $message;
}
add_filter( 'retrieve_password_message', 'wpse_custom_retrieve_password_message', 10, 2 );


/**
 * End of Plugin
 */
?>
