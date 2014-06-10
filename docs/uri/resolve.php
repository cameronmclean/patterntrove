<?php

include("../../lib/default_config.php");

//include('functions.php');

$id = hexdec($_REQUEST['id']);

$sql = "SELECT * FROM  uri WHERE  uri_id = {$id} " . db_limit_1();
					$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line = db_get_next_row($tresulta);
	if($line['uri_id']){
		header("Location: {$line['uri_url']}");
		exit();
	}else{
		echo "Not Found";
	}

?>