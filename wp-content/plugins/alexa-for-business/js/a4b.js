
jQuery(document).ready(function( $ ) {
$('#chkall').on('click',function(){
    if($(this).is(':checked') === true){
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
    contents += '<div style="margin-left:'+count+'0px;" class="form-group floating-label guestrequest guestrequest'+count+'"><input class="form-control get_dirty" type="textbox" name="guest_request'+count+'" id="guest_request'+count+'" value="" required><label for="guest_request'+count+'">Guest Request *</label></div><div style="margin-left:'+count+'0px;" class="form-group floating-label alexaresponse alexaresponse'+count+'"><textarea class="form-control get_dirty" name="alexa_response'+count+'" id="alexa_response'+count+'" required ></textarea><label for="alexa_response'+count+'">Alexa Response *</label></div><div style="margin-left:'+count+'0px;" class="form-group floating-label alexaresponse alexaresponse'+count+'">';
    if(count<3){
    contents += '<input type="button" name="add_request'+count+'" id="add_request'+count+'" class="btn btn-block ink-reaction btn-info add_request" data-level="'+count+'" value="+"></div>';
    }
    
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
        //$('#EmailID').attr('required',true);
    }else{
        $('#EmailID').attr('readonly',true);
        $('#EmailID').attr('required',false);
    }
}

function textchk(){
    if($('#text_chk').is(':checked') == true){
        $('#TextNumber').attr('readonly',false);
        //$('#TextNumber').attr('required',true);
    }else{
        $('#TextNumber').attr('readonly',true);
        $('#TextNumber').attr('required',false);
    }
}

function callchk(){
    if($('#call_chk').is(':checked') == true){
        $('#CallNumber').attr('readonly',false);
        //$('#CallNumber').attr('required',true);
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
    dateFormat: 'mm/dd/yy',
    onSelect: function (selected) {
        var dt = new Date(selected);
        dt.setDate(dt.getDate() + 1);
        $("#enddate").datepicker("option", "minDate", dt);
    }
});
$("#enddate").datepicker({
    numberOfMonths: 1,
    dateFormat: 'mm/dd/yy',
    onSelect: function (selected) {
        var dt = new Date(selected);
        dt.setDate(dt.getDate() - 1);
        $("#startdate").datepicker("option", "maxDate", dt);
    }
}); 

$("#data_table").DataTable();
$("#data_table_response").DataTable({
    "order": [[ 1 , "asc" ]],
   "searching": false 
});

$("#data_table_audit").DataTable({
    "order": [[ 1 , "asc" ]]
});

$("#data_table_userlist").DataTable();

$('.downloadactions').on('click',function(){
    var str = window.location.href;
    var res = "";
    var form = "";
    if (str.indexOf("audit_log") >= 0)
    {
       res = str.split("audit_log");
       form = "audit_log";
    }
    else if (str.indexOf("responses") >= 0)
    {
       res = str.split("responses");
        form = "responses";
    }
    
    sdate = $("#startdate").val();
    edate   = $("#enddate").val();
    userid    = $("#userid").val();
    var startdate = "";
    var enddate = "";
    if(sdate !=="")
    {
    var sparts = sdate.split('/');
    startdate = sparts[1] + '/' + sparts[0] + '/' + sparts[2];
    }
    
    if(edate !=="")
    {
     var eparts = edate.split('/');
     enddate = eparts[1] + '/' + eparts[0] + '/' + eparts[2];
    }
    
   
    $.ajax({
            url: res[0]+"/wp-content/plugins/alexa-for-business/generate_excel.php", 
            type: "POST",  
            data: { 'startdate': startdate, 'enddate': enddate ,'userid':userid,'from':form },
            success: function(data){
            
            document.location.href =(res[0]+'/wp-content/plugins/alexa-for-business/excel_data.php');
    }});
});

$('#search_date').on('click',function(){
    $('#action_for').val('search');
});

$('.js-example-basic-multiple').select2();


$(document).on('submit','form[name=request]',function(e){
    var count = 0;
    if($('#notification_Temp').val() == "" && $('#RequestType').val() =='Service Request'){
        count++;
        $('#notification_Temp_error').show();
    }else{
        $('#notification_Temp_error').hide();
    }
    if($('#RequestType').val() =='Service Request' && $('#email_chk').is(':checked')===false && $('#text_chk').is(':checked')===false && $('#call_chk').is(':checked')===false){
        count++;
        $('#noti_req').show();
    }else{
        $('#noti_req').hide();
    }
    if($('#email_chk').is(':checked')== true && $('#EmailID').val()==""){
        count++;
        $('#EmailID_error').html('This field is required.');
        $("#EmailID_error").show();
    }else{
        $("#EmailID_error").hide();
    }
    if($('#email_chk').is(':checked')== true && $('#EmailID').val()!=""){
        if(!multiple_validateEmail($('#EmailID').val())){
            count++;
            $('#EmailID_error').html('Please enter a valid email address.');
            $("#EmailID_error").show();
        }else{
            $("#EmailID_error").hide();
        }
    }
    if($('#text_chk').is(':checked')== true && $('#TextNumber').val()==""){
        count++;
        $('#TextNumber_error').html('This field is required.');
        $("#TextNumber_error").show();
    }else{
        $("#TextNumber_error").hide();
    }
    if($('#call_chk').is(':checked')== true && $('#CallNumber').val()==""){
        count++;
        $('#CallNumber_error').html('This field is required.');
        $("#CallNumber_error").show();
    }else{
        $("#CallNumber_error").hide();
    }
    if(count ==0){
        $('#notification_Temp_error').hide();
        $("#EmailID_error").hide();
        $("#TextNumber_error").hide();
        $("#CallNumber_error").hide();
        $('#noti_req').hide();
        $("#EmailID_error").hide();
        $('form[name=request]').submit();
    }else{
        e.preventDefault();
        return false;
    }
});

$('#RequestType').on('change',function(){
    if($(this).val() == 'General Information'){
        $('.notification').hide();
        $('.notificationtemplate').hide();
    }else{
        $('.notification').show();
        $('.notificationtemplate').show();
    }
});

$('.delete_action').on('click',function(){
    chk_length = $('.chkall:checked').length;
    if(chk_length==0){
        alert('Please Select Atleast One Record');
        return false;
    }else{
        var conf = confirm('Are you sure want to delete?');
        if(conf == true){
            $('form').submit();
        }
    }
});

$('.delete_action_rooms').on('click',function(){
    chk_length = $('.chkall:checked').length;
    if(chk_length==0){
        alert('Please Select Atleast One Record');
        return false;
    }else{
        var conf = confirm('Are you sure want to delete?');
        if(conf == true){
            $('form[name=delete_rooms]').submit();
        }
    }
});



$('.delete_rp_action').on('click',function(){
    chk_length = $('.chkall:checked').length;
    if(chk_length==0){
        alert('Please Select Atleast One Record');
        return false;
    }else{
        var conf = confirm('Are you sure want to delete?');
        if(conf == true){
            $('form[name=list_room_profile]').submit();
        }
    }
});


$(document).on('click','#remove_device',function(){
    device_name = $(this).data('devicename');
    if(device_name){
        conf = confirm("Are you sure want to disassociate the Device?");
        if(conf == true){
            $('#disync_device_name').val(device_name);
            $('form[name=dissync_devices]').submit();
        }
    }else{
        alert('This Room is not syncronized with any Devices');
        return false;
    }
});

$('#noti_submit').on('click',function(){
    var template = $('#template_name').val()
    var temp_content = $('#tempcontent').val();
    var count = 0;
    
    if(template==""){
        $('#errorMessage_noti').html('This field is required');
        $('#errorMessage_noti').show();
        count++;
    }else{
        $('#errorMessage_noti').hide();
    }

    if(temp_content){
        if(temp_content.indexOf('#roomno')>-1){
            $('#errorMessage_noti_content').hide();
            if(count == 0){
                $('form').submit();
            }
        }else{
            $('#errorMessage_noti_content').html('Template content should include the #roomno variable');
            $('#errorMessage_noti_content').show();
            count++;
        }
    }else{
        $('#errorMessage_noti_content').html('This field is required');
        $('#errorMessage_noti_content').show();
        count++;
    }
    
});


 $(document).on('blur','.get_dirty',function(){
        if ($(this).val().length > 0) {
            $(this).addClass('dirty');
        }
 });
 
$('#RequestName,#template_name,#RoomName,#ProfileName').on('input', function() {
  var c = this.selectionStart,
      r = /[^a-z0-9 ]/gi,
      v = $(this).val();
  if(r.test(v)) {
    $(this).val(v.replace(r, ''));
    c--;
  }
  this.setSelectionRange(c, c);
});


/*$('#alexa_response1').on('input', function() {
  var c = this.selectionStart,
      r = /[^"]/gi,
      v = $(this).val();
  if(r.test(v)) {
    $(this).val(v.replace(r, ''));
    c--;
  }
  this.setSelectionRange(c, c);
});*/
 
$('#alexa_response1').bind('keypress', function(e) {
    console.log( e.which );
        var k = e.which;
        if (k==34){
            e.preventDefault();
        }
}); 

$(".numberonly").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });


 $(document).on('click','.modelidresponseclick',function(){
       
       alert( $(this).data("myvalue"));
       
        var textval = $(this).data("myvalue")+' request name: RequestName <br> call status: callstatus <br> email status: emailstatus <br> message: messageview <br> content of the sms: messagecontent <br> sms status: smsatatus <br> message: messageview <br> content of the mail: messagecontent ';
        
        $("#modelidresponseview").html(textval);
 });
 
function validateEmail(sEmail) {
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    if (filter.test(sEmail)) {
        return true;
    }
    else {
        return false;
    }
}


function multiple_validateEmail(sEmail) {
     var emails = sEmail.split(/[,]+/); // split element by , and ;
     var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
     
         for (var i in emails) {
             var value = $.trim(emails[i]);
             if (filter.test(value)) {
                     
                }
                else {
                    return false;
                }
         }
         return true;
    }
    

/*$('#data').after('<div id="nav"></div>');
var rowsShown = 4;
var rowsTotal = $('#data tbody tr').length;
var numPages = rowsTotal/rowsShown;
for(i = 0;i < numPages;i++) {
    var pageNum = i + 1;
    $('#nav').append('<a href="#" rel="'+i+'">'+pageNum+'</a> ');
}
$('#data tbody tr').hide();
$('#data tbody tr').slice(0, rowsShown).show();
$('#nav a:first').addClass('active');
$('#nav a').bind('click', function(){

    $('#nav a').removeClass('active');
    $(this).addClass('active');
    var currPage = $(this).attr('rel');
    var startItem = currPage * rowsShown;
    var endItem = startItem + rowsShown;
    $('#data tbody tr').css('opacity','0.0').hide().slice(startItem, endItem).
    css('display','table-row').animate({opacity:1}, 300);
});*/
});












