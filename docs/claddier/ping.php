<?php
include("../../lib/default_config.php");
if ($_SESSION['user_admin'] < 3) {
	exit;
}
if (filter_var($_GET['ip_address'], FILTER_VALIDATE_IP)) {
	if (filter_var($_GET['ip_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
		exec("ping6 -c 4 {$_GET['ip_address']} 2>&1", $output, $retval);
	}
	else {
		exec("ping -c 4 {$_GET['ip_address']} 2>&1", $output, $retval);
	}
	if ($retval != 0) { 
		echo "FAILURE: This IP Address does not respond to ping."; 
	} 
	else { 
		echo "SUCCESS: This IP Address responds to ping.";
	}
}
else {
	echo "ERROR: This is not a valid IP address.";
}
?>
