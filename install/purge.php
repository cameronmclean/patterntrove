<?php

	include("../lib/default_config.php");
	
	if(!in_array("-users",$argv)){
		$tables[] = "blog_users";
		$tables[] = "users";
		$tables[] = "blog_sub";
	}
	



	$tables[] = "blog_bits";
	$tables[] = "blog_blogs";
	$tables[] = "blog_com";
	$tables[] = "blog_data";
	$tables[] = "messages";
	$tables[] = "uri";

	
	if(!in_array("go",$argv)){
		echo "Trial run!\n";
		foreach($tables as $table){
			echo "Would empty $table\n";
		}
	}else{
		foreach($tables as $table){
			echo $note = "Emptying $table\n";
			runQuery("TRUNCATE `{$table}`",$note);
		}	
	}
	
	if(!in_array("go",$argv)){
	
	$sql = "INSERT INTO  `blog_zone` ( `zone_id` , `zone_name` , `zone_type` ,`zone_res` ) VALUES (	'1',  'Logged In',  'user',  'any' );";
	
		echo $note = "Reinserting default zone\n";
		runQuery("$sql",$note);
	}
	if(in_array("+files",$argv)){
		echo "Deleting files from ".$ct_config['uploads_dir']."\n";
		if(in_array("go",$argv)){
			 $exec = "rm -rf {$ct_config['uploads_dir']}/* ";
			`$exec`;
		}
		
	}

?>