<?php	
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=table.xls");  //File name extension was wrong
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);echo"<table class='table no-margin' id='myTable' ><thead><tr><th>Date Time</th><th>Request Name</th><th>Request Type</th><th>Room No</th></tr></thead><tbody><tr><td>01/06/2018, 11:55:35</td><td>bathrobe</td><td>Service Request</td><td>Room 5225</td></tr><tr><td>18/06/2018, 09:49:57</td><td>coke</td><td>Service Request</td><td>Room_101</td></tr><tr><td>31/05/2018, 11:42:15</td><td>bathrobe</td><td>Service Request</td><td>Room 5225</td></tr><tr><td>31/05/2018, 11:29:18</td><td>towels</td><td>Service Request</td><td>Room 5225</td></tr><tr><td>18/06/2018, 09:54:58</td><td>shopping malls1 edit</td><td>General Information</td><td>Room_101</td></tr><tr><td>01/06/2018, 12:14:50</td><td>bathrobe</td><td>Service Request</td><td>Room 5225</td></tr><tr><td>01/06/2018, 11:54:50</td><td>towels</td><td>Service Request</td><td>Room 5225</td></tr><tr><td>31/05/2018, 06:35:43</td><td>towels</td><td>Service Request</td><td>Room 5225</td></tr><tr><td>01/06/2018, 12:41:35</td><td>local recommendation</td><td>General Information</td><td>Room 5225</td></tr><tr><td>01/07/2018, 11:38:54</td><td>towels</td><td>Service Request</td><td>Room 5225</td></tr></tbody></table>";