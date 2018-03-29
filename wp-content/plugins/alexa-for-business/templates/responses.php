<?php

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
        $resquestname="";
        $resquest_room_no="";
         $newval = explode("_@_",$response->RequestName);
        if(isset($newval[1]))
            $resquestname= $newval[1];
        else 
            $resquestname= $newval[0];
            
        if(is_array($response->RoomNumber))
        {
            $str = $response->RoomNumber;
            $resquest_room_no = $str[1];
        }
        else
            $resquest_room_no = $response->RoomNumber;
            
        print "<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_device".$i."' id='chk_device".$i."' class='chkall'></label></div></td><td>".$response->Date."</td><td>".$resquestname."</td><td>".$response->RequestType."</td><td>".$resquest_room_no."</td><td><a href='".get_home_url().'/device-form?RequestName='.$response->RequestName."'>View Log</a></td></tr>";
            $i++;
        }
    }
    print"</tbody></table>";
    print"</form></div>";

?>