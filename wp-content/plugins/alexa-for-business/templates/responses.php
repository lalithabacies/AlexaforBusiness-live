<?php

    if($_POST)
    {
        if($_POST['action_for'] == 'search'){
            $startdate =date("d/m/Y", strtotime($_POST['startdate']));
            $enddate = date("d/m/Y", strtotime($_POST['enddate']));
          
            if(!empty($_POST['startdate']) && !empty( $_POST['enddate']))
                $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array('startdate'=>$startdate,'enddate'=>$enddate))));
            else
               $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array())));
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate']; 
        }else if($_POST['action_for'] == 'delete_response'){
            $responses=array();
            for($i=0;$i<=count($_POST['no_of_responses']);$i++){
                $responses[]=$_POST['chk_resp'.$i];
            }
            $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/delete_response',json_encode(array('responses'=>$responses))));
            wp_redirect(home_url().'/responses');
        }
    } 
    else 
    {
        $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array())));
      
        
    }
    if($attributes['from'] == 'response')
    {
        echo '<div class="col-md-10 card card-tiles style-default-light"><form class="form response_form form-validate" action="'.get_home_url().'/responses" name="response_form" method="POST">';
    }    
    else
    {
        echo '<div class="col-md-10 card card-tiles style-default-light"><form class="form response_form form-validate" action="'.get_home_url().'/audit_log" name="response_form" method="POST">';
    }
    
    echo '<div class="sesearch">
    <div class="form-group dstartdate">
    <input class="form-control" type="textbox" name="startdate" id="startdate" value="'.$startdate.'">
    <label for="startdate">Start Date</label>
    </div>

    <div class="form-group denddate">
    <input class="form-control" type="textbox" name="enddate" id="enddate" value="'.$enddate.'">
    <label for="enddate">End Date</label>
    </div>';
    
    echo '<input class="btn btn-raised btn-primary" type="submit" name="search_date" id="search_date" value="Search" />';
    echo '<div class="dropdown input-group-btn" style="padding-top: 16px;">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1" aria-expanded="false">Action   <span class="caret"></span></button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li><a href="#" class="downloadactions">Export To Excel</a></li>
        <li><a href="#" class="delete_action">Delete</a></li>
      </ul>
    </div>';
    echo '</div>';
    
    ?>
    <div class="container">
    
    </div>
    <?php

    //print"<div class='form-group'><select class='form-control' name='downloadactions' id='downloadactions' ><option value='actions' >Actions </option><option value='download'>Export to Excel</option></select></div>";
    
    print"<table class='no-margin table' id='data_table_response' ><thead><tr><th><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chkall' id='chkall'></label></div></th><th>Date Time</th><th>Request Name</th>";
    if($attributes['from'] == "audit_log"){
        print"<th>Request Status</th>";
    }
    print"<th>Request Type</th><th>Room No</th><th>View Log</th></tr></thead>";

    print "<tbody>";
    $i=0;
    if($data){
        $k = 1;
        foreach($data->Items as $response){
    
        $resquestname="";
        $resquest_room_no="";
        $req_name = explode("_@_",$response->RequestName);
        if(isset($req_name[1]))
            $resquestname= $req_name[1];
        else 
            $resquestname= $req_name[0];
            
        if(is_array($response->RoomNumber))
        {
            $str = $response->RoomNumber;
            $resquest_room_no = $str[1];
        }
        else
            $resquest_room_no = $response->RoomNumber;
            
        if($attributes['userid'] == $req_name[0]){
            $statusprint = true;
            $con_date = $response->Date;
            $date = DateTime::createFromFormat("d/m/Y, H:i:s" , $con_date);
            $new_date = $date->format('m/d/Y, H:i:s');
            if($attributes['from'] == "audit_log"){
                if(!empty($response->RequestStatus))
                     $statusprint = true;
                else
                     $statusprint = false;
            }
            if( $statusprint == true)
            {
            print "<tr><td><div class='checkbox checkbox-inline checkbox-styled'><label><input type='checkbox' name='chk_resp".$i."' id='chk_resp".$i."' class='chkall' value='".$response->ResponseID."'><span></span></label></div></td><td>".$new_date."</td><td>".$resquestname."</td>";
            if($attributes['from'] == "audit_log"){
                print"<td>".$response->RequestStatus."</td>";
            }
            
            $messagedata=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/response_view_log',json_encode(array('request_name'=>$response->RequestName))));
		
	        
		    
		    if($messagedata->message)
		    {
		        $messagedata = str_replace('#roomno', $resquest_room_no, $messagedata->message);
		        
		    }  
		    else if($messagedata->error)
		    {
		         $messagedata = $messagedata->error;
		    }else
		    {
		     $messagedata = "";
		    }
		
            print"<td>".$response->RequestType."</td><td>".$resquest_room_no."</td>
            
                 
                <td><a class='btn btn-default-bright btn-raised' data-json='".$json_data."'  data-toggle='modal' data-target='#simpleModal_".$k."'>View Log
                </a>
                
                 <div class='modal fade' id='simpleModal_".$k."' tabindex='-1' role='dialog' aria-labelledby='simpleModalLabel' aria-hidden='true'>
            	<div class='modal-dialog'>
        		<div class='modal-content'>
        			<div class='modal-header'>
        				<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
        				<h4 class='modal-title' id='simpleModalLabel'>Log details</h4>
        				<hr>
        				<p>
        				    Request Name: ".$resquestname;
                        
                    if(($response->Call_Response) && ($attributes['from'] == 'audit_log'))
                    {
					  if($response->Call_Response == "Success")
					  {
						print " <br> Call Status : ".$response->Call_Response;
					  }
					 else
					 {
					   print " <br> Call Status : Failure";
					   print " <br> Call Message : ".str_replace('Failure: ', '', $response->Call_Response) ;
					 }				
                    }
                    
                    if(($response->Email_Response) && ($attributes['from'] == 'audit_log'))
                    {
                      if($response->Email_Response == "Success")
                      {
                        print " <br> Email Status : ".$response->Email_Response;
                        print " <br> Email Content : ".$messagedata;
                      }
                      else
                      {
                        print " <br> Email Status: Failure";
                        print " <br> Email Message: ".str_replace('Failure: ', '', $response->Email_Response);
                      }
                    }else if ($attributes['from'] == 'response')
					 {
						  print " <br> Email Content : ".$messagedata;
					 }	  
                      
                      
                     if(($response->Sms_Response) && ($attributes['from'] == 'audit_log'))
                     {
                      if($response->Sms_Response == "Success")
                      {
                        print " <br> SMS Status : ".$response->Sms_Response;
                        print " <br> SMS Content : ".$messagedata;
                      }
                      else
                      {
                        print " <br> SMS Status : Failure";
                        print " <br> SMS Message : ".str_replace('Failure: ', '', $response->Sms_Response);
                      }
                     } else if ($attributes['from'] == 'response')
					 {
						  print " <br> SMS Content : ".$messagedata;
					 }						 
                      
            	print "</p>
        			</div>
        		</div>
        	</div>
            </div>
    </td>
                
                </tr>";
            }
                $i++;
                 //echo $resquestname."<br>";
        }
        $k++;
        }
    }
    print"</tbody></table>";
    print"<input type='hidden' name='userid' id='userid' value='".$attributes['userid']."'><input type='hidden' name='no_of_responses' value='".$i."'><input type='hidden' name='action_for' id='action_for' value=''></form></div>";
    
    
    /* print '<div class="modal fade" id="simpleModal" tabindex="-1" role="dialog" aria-labelledby="simpleModalLabel" aria-hidden="true">
    	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="simpleModalLabel">Log details</h4>
				<hr>
				<p id="modelidresponseview">Test page in response</p>
			</div>
		</div>
	</div>
    </div>';

*/

?>