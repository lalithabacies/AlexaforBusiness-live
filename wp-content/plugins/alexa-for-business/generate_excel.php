<?php 

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

if(!empty($_POST['startdate']) && !empty( $_POST['enddate']))
{
  
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];		
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array('startdate'=>$startdate,'enddate'=>$enddate))));
} 
else 
{
    $data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array())));
}

$myfile = fopen("excel_data.php", "w") or die("Unable to open file!");
$content = '<?php	
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=table.xls");  //File name extension was wrong
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);';
//$content .= "echo'$_POST['table_data']' ";
$content.='echo"';
if($_POST['from'] == "audit_log")
    $content.="<table class='table no-margin' id='myTable' ><thead><tr><th>Date Time</th><th>Request Name</th><th>Request status</th><th>Request Type</th><th>Room No</th></tr></thead>";
else if($_POST['from'] == "responses")
    $content.="<table class='table no-margin' id='myTable' ><thead><tr><th>Date Time</th><th>Request Name</th><th>Request Type</th><th>Room No</th></tr></thead>";
    
$content.= "<tbody>";

$i=0;
if($data){
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
            
        if($_POST['userid'] == $req_name[0]){
            if(($_POST['from'] == "audit_log") && (!empty($response->RequestStatus)))
                  $content.= "<tr><td>".$response->Date."</td><td>".$resquestname."</td><td>".$response->RequestStatus."</td><td>".$response->RequestType."</td><td>".$resquest_room_no."</td></tr>";
            else if($_POST['from'] == "responses")
                $content.= "<tr><td>".$response->Date."</td><td>".$resquestname."</td><td>".$response->RequestType."</td><td>".$resquest_room_no."</td></tr>";
                
                $i++;
        }
    }
}

$content.="</tbody></table>";
$content.='";';
fwrite($myfile, $content);
fclose($myfile);

	
/**
 * cURL function to perform the POST method
 *
 */
 
