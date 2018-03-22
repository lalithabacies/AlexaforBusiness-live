
jQuery(document).ready(function( $ ) {
$('#chkall').on('click',function(){
    if($(this).is(':checked') == true){
        $('.chkall').prop('checked',true);
    }else{
        $('.chkall').prop('checked',false);
    }
});

$('.delete_users').on('click',function(){
    $('#actioncall').val('D');
    if($('.chkall:checked').length >0){
        
    }
    $('form[name="users"]').submit();
});

$('.edit_users').on('click',function(){
    $('#edited_user_name').val($(this).data('user_name'));
    $('form[name="edit_user"]').submit();
});

/*$('.edit_room').on('click',function(){
    //e.preventDefault();
    $('#edited_room_name').val($(this).data('room_name'));
    $('#edited_room_profile_name').val($(this).data('room_profile_name'));
    $('form[name="update_room"]').submit();
});*/

$('.edit_room_profile').on('click',function(){
    $('#edited_roomprofilename').val($(this).data('roomprofile_name'));
    $('form[name="edit_room_profile"]').submit();
});

$('#chkall,.chkall').on('click',function(){
    make_room_dis();
});

$('#actions').on('change',function(){
    $('form[name="devices_list"]').attr('action',$('#action_url').val());
    $('form[name="devices_list"]').submit();
});

$('#add_room').on('click',function(){
    $('form[name="devices_list"]').attr('action',$('#addroom_url').val());
    $('form[name="devices_list"]').submit();
});

$('.button_link').on('click',function(){
    window.top.location.href=$(this).data('link');
});

$(document).on('click','.add_request',function(){
    /*if($(this).val() == '+'){
    var choice = confirm("If you want to add two request (e.g Yes or No) hit ok otherwise click on the Cancel");
    }*/
    
    count = parseInt($('#count').val())+1;
    $('#count').val(count);
    
    var contents = "";
    
    /*if(choice===true){
        contents += '<div style="margin-left:'+count+'0px;" class="form-group floating-label guestrequest guestrequest'+count+'"><input class="form-control" type="textbox" name="guest_request'+count+'" id="guest_request'+count+'" value="" required><label for="guest_request'+count+'">Guest Request *</label></div><div style="margin-left:'+count+'0px;" class="form-group floating-label alexaresponse alexaresponse'+count+'"><textarea class="form-control" name="alexa_response'+count+'" id="alexa_response'+count+'" required ></textarea><label for="alexa_response'+count+'">Alexa Response *</label></div><div style="margin-left:'+count+'0px;" class="form-group floating-label alexaresponse alexaresponse'+count+'"></div>';
    }*/
    contents += '<div style="margin-left:'+count+'0px;" class="form-group floating-label guestrequest guestrequest'+count+'"><input class="form-control" type="textbox" name="guest_request'+count+'" id="guest_request'+count+'" value="" required><label for="guest_request'+count+'">Guest Request *</label></div><div style="margin-left:'+count+'0px;" class="form-group floating-label alexaresponse alexaresponse'+count+'"><textarea class="form-control" name="alexa_response'+count+'" id="alexa_response'+count+'" required ></textarea><label for="alexa_response'+count+'">Alexa Response *</label></div><div style="margin-left:'+count+'0px;" class="form-group floating-label alexaresponse alexaresponse'+count+'"><input type="button" name="add_request'+count+'" id="add_request'+count+'" class="btn btn-block ink-reaction btn-info add_request" data-level="'+count+'" value="+"></div>';
    
    $('.forlevel2').before(contents);
    
    
    if($(this).val() == '+'){
        $(this).val('-');
        level = parseInt($(this).data('level'))+1;
        $('#count').val(level);
    }else if($(this).val() == '-'){
        level = parseInt($(this).data('level'))+1;
        for(i=level;i<=$('#count').val();i++){
            $('div.guestrequest'+i).remove();
            $('div.alexaresponse'+i).remove();
        }
        $(this).val('+');
        level = $(this).data('level');
        $('#count').val(level);
    }
});

$('#email_chk').on('click',function(){
    emailchk();
});

$('#text_chk').on('click',function(){
    textchk();
});

$('#call_chk').on('click',function(){
    callchk();
});


function make_room_dis(){
    if($('.chkall:checked').length == 1){
        $('#actions').prop('disabled',false);
        $('#add_room').prop('disabled',false);
    }else{
        $('#actions').prop('disabled',true);
        $('#add_room').prop('disabled',true);
    }
}

function emailchk(){
    if($('#email_chk').is(':checked') == true){
        $('#EmailID').attr('readonly',false);
        $('#EmailID').attr('required',true);
    }else{
        $('#EmailID').attr('readonly',true);
        $('#EmailID').attr('required',false);
    }
}

function textchk(){
    if($('#text_chk').is(':checked') == true){
        $('#TextNumber').attr('readonly',false);
        $('#TextNumber').attr('required',true);
    }else{
        $('#TextNumber').attr('readonly',true);
        $('#TextNumber').attr('required',false);
    }
}

function callchk(){
    if($('#call_chk').is(':checked') == true){
        $('#CallNumber').attr('readonly',false);
        $('#CallNumber').attr('required',true);
    }else{
        $('#CallNumber').attr('readonly',true);
        $('#CallNumber').attr('required',false);
    }
}

emailchk();
textchk();
callchk();


$("#startdate").datepicker({
    numberOfMonths: 1,
    dateFormat: 'dd/mm/yy',
    onSelect: function (selected) {
        var dt = new Date(selected);
        dt.setDate(dt.getDate() + 1);
        $("#enddate").datepicker("option", "minDate", dt);
    }
});
$("#enddate").datepicker({
    numberOfMonths: 1,
    dateFormat: 'dd/mm/yy',
    onSelect: function (selected) {
        var dt = new Date(selected);
        dt.setDate(dt.getDate() - 1);
        $("#startdate").datepicker("option", "maxDate", dt);
    }
}); 

$("#myTable").DataTable();

$('#downloadactions').on('change',function(){
    if($(this).val() == "download")
    {
        startdate = $("#startdate").val();
        enddate = $("#enddate").val();

        $.ajax({
                url: "../wp-content/plugins/alexa-for-business/generate_excel.php", 
                type: "POST",  
                data: { 'startdate': startdate, 'enddate': enddate  },
                success: function(data){
                
                document.location.href =('../wp-content/plugins/alexa-for-business/excel_data.php');
        }});

    } 
});


$('.js-example-basic-multiple').select2();


$(document).on('submit','form[name=request]',function(e){
    if($('#RequestType').val() =='service_request' && $('#email_chk').is(':checked')===false && $('#text_chk').is(':checked')===false && $('#call_chk').is(':checked')===false){
        $('#noti_req').show();
        e.preventDefault();
        return false;
    }else{
        $('#noti_req').hide();
        $('form[name=request]').submit();
    }
});

$('.delete_action').on('click',function(){
    chk_length = $('.chkall:checked').length;
    if(chk_length==0){
        alert('Please Select Atleast One Record');
        return false;
    }else{
        var conf = confirm('Are you sure want to delete the selected users');
        if(conf == true){
            $('form').submit();
        }
    }
});

});












