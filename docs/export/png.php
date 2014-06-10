<?php

//$posts->addChild('sql', $sql);

$tresult = runQuery($sql,'Fetch Page Groups');
$noofposts = db_get_number_of_rows($tresult);
if($noofposts!=1){
echo "Error";
exit(1);
}

$post = db_get_next_row($tresult);


$fname = "cache/blogimgs/{$post['bit_rid']}";
if($_SESSION['user_uid']){
	$fname .= "_{$_SESSION['user_uid']}";
}
$fname .= ".png";



if(!file_exists($fname)){

$url = render_blog_link($post['bit_id'],ture );

if($_SESSION['user_uid']){
	$url .= "?uid={$_SESSION['user_uid']}&postonly=true";
}else{
		$url .= "?postonly=true";
}

header ("content-type: text/plain");
$pic = file_get_contents("http://screen.labtrove.org/?uid=cb9a82cac8b84bdf2301f873b6743419&url=".rawurlencode($url) );

$cache = "{$ct_config['cache_dir']}/blogimgs/";

if(!file_exists("cache/blogimgs")){
	mkdir('cache/blogimgs');
}

file_put_contents($fname, file_get_contents($pic));
}
if(!$fxml){
header ("content-type: image/png");
echo file_get_contents($fname);

exit();
}
?>