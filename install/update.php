<?php


	include("../lib/default_config.php");
	
	$lt_url = "https://www.mylabnotebook.ac.uk/labtrove/update.json";
	
	
	//depricate rsync
	if(count($argv)==1)
	$argv[] = "web";
	
	$dirup = $ct_config['pwd'];
	
	chdir( $ct_config['pwd'] );

	$current_version = trim(file_get_contents("install/version"));
	
	preg_match('/([\d]+)$/', "{$current_version}", $versions);
	$version = $versions[1];

	//Check curl is installed
		$mods = get_loaded_extensions();
		if(!in_array("curl", $mods)){
			die("Update requires php_curl\n");
		}
		
		$ch = curl_init( $lt_url );
		// Configuring curl options
		$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_SSLVERSION => 3
		);
		
	// Setting curl options
	curl_setopt_array( $ch, $options );

		// Getting results
	$result =  curl_exec($ch);
		
	if(curl_errno($ch)!=0)  die("Error:".curl_errno($ch)." ".curl_error($ch)."\nURL: $lt_url\nCould not get a validated update response from our server!\n");
	
	$info = json_decode($result);

if(!in_array("skip", $argv) && !in_array("test", $argv)){

	if( $current_version == "LabTrove ".$info->version){
		echo "Same version no need to update\n	";
		exit(); //Same version no need to update
	}
	
	if(in_array("web", $argv)){
		
		$temp_dir = "{$ct_config['tmp_dir']}/update-{$ct_config['this_server']}";
		
		
		@mkdir($temp_dir, 0777, true);
						
		$tar = basename($info->latest_tar);
				
		
		copy($info->latest_tar, "{$temp_dir}/$tar");
		
		if(hash_file("sha256", "{$temp_dir}/$tar")!=(string)$info->latest_tar_hash) die("Error Hashes don't match\n");
		
		`tar zxf {$temp_dir}/$tar -C {$temp_dir}`;
		
		copy("{$temp_dir}/labtrove/install/updatefilter.txt",  "{$ct_config['pwd']}/install/updatefilter.txt");
	
		passthru("rsync --exclude-from \"{$ct_config['pwd']}/install/updatefilter.txt\" -ac --delete \"{$temp_dir}/labtrove/\" \"{$ct_config['pwd']}\"");

		`rm -r {$temp_dir}`;

	}else{
		
		
		`rsync -ac rsync.labtrove.org::labtrove-{$ct_config['core_version']}/install/updatefilter.txt $dirup/install/.`;

    	`rsync --exclude-from updatefilter.txt -ac --delete rsync.labtrove.org::labtrove-{$ct_config['core_version']} $dirup`;


	}

}


include("update_tasks.php");

if(isset($argv[1]) && $argv[1] == "test"){

	if(isset($argv[2]) && $taskid = (int)$argv[2]){
		if(isset($update_tasks[$taskid])){
			echo "Update for version: $taskid \n";
				foreach($update_tasks[$taskid] as $update){
					dotask($update);
				}
		}else{
			echo "Task: $task not found\n";
		}
		
	}
	exit();
}



for($v = $version; $v <= $info->version_minor; $v++){

	if(isset($update_tasks[$v])){
		echo "Update for version: $v \n";
		foreach($update_tasks[$v] as $update){
			dotask($update);
		}
		
	}
	
}

function dotask(&$update){
	echo " Running {$update['type']}: {$update['desc']}\n";
	switch($update['type']){
		case "sql":
			$res = _db_call($update['sql'], false);
			echo "  Number of rows selected: ".db_get_number_of_rows($res).", Number of rows affected: ".db_affected_rows()."\n";
		break;
		case "cmd":
			`{$update['cmd']}`;
		break;
	}
	
}

	

?>