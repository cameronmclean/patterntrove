<?php
include("../default_config.php");
$bit_rid = $argv[1];
$sql = "SELECT `bit_rid`, `bit_content` FROM `blog_bits` WHERE `bit_rid` = '" . addslashes($bit_rid) . "'";
$post = db_get_next_row(runQuery($sql, "get content from blog post revision id"));
if (!empty($post)) {
	chemspider_generate_info($post['bit_rid'], $post['bit_content']);
}
?>
