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

if(($_POST['startdate'] !="") && ($_POST['enddate'] != ""))
{
    echo $_POST['startdate'];
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
$content.="<table class='table no-margin' id='myTable' ><thead><tr><th><input type='checkbox' name='chkall' id='chkall'></th><th>Date Time</th><th>Response Status</th><th>Request Name</th><th>Request Type</th><th>Room No</th></tr></thead>";

$content.= "<tbody>";

$i=0;
if($data){
    foreach($data->Items as $response){
        $content.= "<tr><td><input type='checkbox' name='chk_device".$i."' id='chk_device".$i."' class='chkall'></td><td>".$response->Date."</td><td>Success</td><td>".$response->RequestName."</td><td>".$response->RequestType."</td><td>".$response->RequestType."</td></tr>";
        $i++;
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
 
