<?php
include("../default_config.php");
$bit_rid = $argv[1];
$sql = "SELECT `bit_id`, `bit_rid`, `bit_user`, `bit_title`, `bit_content`, `bit_timestamp`, `bit_blog` FROM `blog_bits` WHERE `bit_rid` = '" . addslashes($bit_rid) . "'";
$post = db_get_next_row(runQuery($sql, "get blog post from revision id"));
if (!empty($post)) {
        claddier_generate_citations($post['bit_id'], $post['bit_rid'], $post['bit_user'], $post['bit_title'], $post['bit_content'], $post['bit_timestamp'], $post['bit_blog']);
}

?>
