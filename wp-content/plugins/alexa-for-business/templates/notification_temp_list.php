<?php

    if($_POST){
        $temp_to_delete=array();
        for($i=0;$i<=$_POST['no_of_temps'];$i++){
            $chk = 'chk_template'.$i;
            if(!empty($_POST[$chk])){
                $temp_to_delete[]=$_POST[$chk];
            }
        }
        $temp_to_delete = json_encode(array('Notification_Temp'=>$temp_to_delete,'userid'=>get_userid()));
        
        $result = json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/notification_temp_delete',$temp_to_delete));
        if($result->error){
            echo '<div class="alert alert-callout alert-danger col-md-offset-3 col-md-6">'.$result->error.'</div>';
        }else{
            wp_redirect(get_home_url('').'/notification-temp-list/');
        }
    }

    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_notification_template',json_encode(array('template_name'=>'','username'=>get_username(),'userid'=>get_userid()))));
    
    $content='<div class="col-md-offset-1 col-md-10 card card-tiles style-default-light"><form class="form" name="delete_template" action="'.home_url().'/notification-temp-list" method="POST">';
    $content.='<input class="btn btn-raised btn-danger delete_action" type="button" name="deletetemplate" id="deletetemplate" value="Delete Template"> <a href="'.get_home_url().'/notification_template/'.'" name="createtemplate" id="createtemplate"><input class="btn btn-raised btn-primary" type="button" value="Create Template"></a>';
    $content.='<table class="table no-margin" id="data_table"><thead><tr><th><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chkall" id="chkall"></label></div></th><th>Template Names</th><th>Templates</th><th>Edit</th></tr></thead><tbody>';
    
    $i=0;  
    
    if(!empty($data->Items)){
        foreach($data->Items as $temp){
            $TemplateName = explode('_@_',$temp->template_name)[1];
            $content.='<tr><td><div class="checkbox checkbox-inline checkbox-styled"><label><input type="checkbox" name="chk_template'.$i.'" class="chkall" value="'.str_replace("\\","",$TemplateName).'"><span></span></label></div></td><td>'.str_replace("\\","",$TemplateName).'</td><td>'.str_replace("\\","",$temp->template).'</td><td><a href="'.get_home_url().'/notification_template?TempName='.$TemplateName.'" name="edit_temp'.$i.'" id="edit_temp'.$i.'" class="edit_temps" data-template_name="'.$TemplateName.'">Edit</a></td></tr>';
            $i++;
        }
    }
    
    $content.='</tbody></table>';
    $content.='<!--<input type="hidden" name="action" value="delete_noti_templates">--><input type="hidden" name="no_of_temps" value="'.$i.'"></form></div>';
    
    echo $content;
    
?>    