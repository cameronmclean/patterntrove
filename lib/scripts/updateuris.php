<?php

include("../default_config.php");


$sql = "SELECT * FROM  blog_bits WHERE bit_id= bit_rid AND bit_uri = '' ";
$tresult = runQuery($sql,'Fetch Page Groups');
    while($row = db_get_next_row($tresult)){
		$url = "tmp.html";
		$uri = uri_geturi($url);
		$sql = "UPDATE  blog_bits SET  bit_uri =  '$uri' WHERE bit_id = ".$row['bit_id'].";";
		runQuery($sql,'Fetch Page2  Groups');
	}










$sql = "SELECT blog_bits.*,blog_blogs.blog_sname  FROM blog_bits INNER JOIN blog_blogs ON blog_bits.bit_blog = blog_blogs.blog_id WHERE bit_edit = 0";		
$result = runQuery($sql,'Fetch Page Groups');
while($post = db_get_next_row($result)){

			$name = ereg_replace( "[^A-Za-z0-9\ \_]", "", strip_tags($post['bit_title']));
			$name = str_replace(" ","_",$name);	
			$url = "http://{$ct_config['this_server']}/{$post['blog_sname']}/{$post['bit_id']}/{$name}.html";

			$sql = "UPDATE ". db_uri_db("uri") . " SET  uri_url =  '$url' WHERE  uri.uri_id = {$post['bit_uri']} " . db_limit_1();
					 runQuery($sql,'Fetch Page Groups');
			echo "{$post['bit_id']} {$post['bit_uri']} {$url} \n";
}

?>
