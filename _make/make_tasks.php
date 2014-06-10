<?php

//


//Tiny MCE
$plugings = array("insertlatex","labtrove","ltcode","ltpostlink");
foreach($plugings as $plug){
	$tasks[] = array("task"=>"copy", "in"=>"/docs/inc/tiny_mce/plugins/{$plug}/editor_plugin.js", "out"=> "/docs/inc/tiny_mce/plugins/{$plug}/editor_plugin_src.js");
	$tasks[] = array("task"=>"js", "in"=>"/docs/inc/tiny_mce/plugins/{$plug}/editor_plugin_src.js", "out"=> "/docs/inc/tiny_mce/plugins/{$plug}/editor_plugin.js");
}

//openid

$tasks[] = array("task"=>"copy", "in"=>"/docs/openid/openid.js", "out"=> "/docs/openid/openid_src.js");
$tasks[] = array("task"=>"js", "in"=>"/docs/openid/openid_src.js", "out"=> "/docs/openid/openid.js");
$tasks[] = array("task"=>"copy", "in"=>"/docs/openid/openid.css", "out"=> "/docs/openid/openid_src.css");
$tasks[] = array("task"=>"css", "out"=>"/docs/openid/openid.css", "in"=> "/docs/openid/openid_src.css");

//CSS

$tasks[] = array("task"=>"copy", "in"=>"/docs/style/default/style.css", "out"=> "/docs/style/default/style_src.css");
$tasks[] = array("task"=>"css", "out"=>"/docs/style/default/style.css", "in"=> "/docs/style/default/style_src.css");


$tasks[] = array("task"=>"copy", "in"=>"/docs/style/post.css", "out"=> "/docs/style/post_src.css");
$tasks[] = array("task"=>"css", "out"=>"/docs/style/post.css", "in"=> "/docs/style/post_src.css");
$tasks[] = array("task"=>"copy", "in"=>"/docs/style/style.css", "out"=> "/docs/style/style_src.css");
$tasks[] = array("task"=>"css", "out"=>"/docs/style/style.css", "in"=> "/docs/style/style_src.css");
?>