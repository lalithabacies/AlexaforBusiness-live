<?php	
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=table.xls");  //File name extension was wrong
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);echo"<table class='table no-margin' id='myTable' ><thead><tr><th><input type='checkbox' name='chkall' id='chkall'></th><th>Date Time</th><th>Response Status</th><th>Request Name</th><th>Request Type</th><th>Room No</th></tr></thead><tbody><tr><td><input type='checkbox' name='chk_device0' id='chk_device0' class='chkall'></td><td>02/02/2018, 01:35:05</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device1' id='chk_device1' class='chkall'></td><td>02/02/2018, 01:36:19</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device2' id='chk_device2' class='chkall'></td><td>02/02/2018, 01:38:33</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device3' id='chk_device3' class='chkall'></td><td>02/02/2018, 01:22:23</td><td>Success</td><td>butler</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device4' id='chk_device4' class='chkall'></td><td>02/02/2018, 01:17:27</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device5' id='chk_device5' class='chkall'></td><td>02/02/2018, 01:48:27</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device6' id='chk_device6' class='chkall'></td><td>02/02/2018, 01:44:03</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device7' id='chk_device7' class='chkall'></td><td>02/02/2018, 01:45:31</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device8' id='chk_device8' class='chkall'></td><td>02/02/2018, 01:40:29</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device9' id='chk_device9' class='chkall'></td><td>02/02/2018, 01:36:59</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device10' id='chk_device10' class='chkall'></td><td>02/02/2018, 01:32:56</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device11' id='chk_device11' class='chkall'></td><td>02/02/2018, 01:40:40</td><td>Success</td><td>towels</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device12' id='chk_device12' class='chkall'></td><td>02/02/2018, 01:45:04</td><td>Success</td><td>towels</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device13' id='chk_device13' class='chkall'></td><td>02/02/2018, 01:45:12</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device14' id='chk_device14' class='chkall'></td><td>02/02/2018, 01:34:54</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device15' id='chk_device15' class='chkall'></td><td>02/02/2018, 01:48:19</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device16' id='chk_device16' class='chkall'></td><td>02/02/2018, 01:47:43</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device17' id='chk_device17' class='chkall'></td><td>02/02/2018, 01:08:43</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device18' id='chk_device18' class='chkall'></td><td>02/02/2018, 01:34:27</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device19' id='chk_device19' class='chkall'></td><td>02/02/2018, 01:48:56</td><td>Success</td><td>dining</td><td>General Information</td><td>General Information</td></tr><tr><td><input type='checkbox' name='chk_device20' id='chk_device20' class='chkall'></td><td>02/02/2018, 01:44:57</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr><tr><td><input type='checkbox' name='chk_device21' id='chk_device21' class='chkall'></td><td>02/02/2018, 01:39:37</td><td>Success</td><td>butler</td><td>Service Request</td><td>Service Request</td></tr></tbody></table>";