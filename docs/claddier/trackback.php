<?php
include("../../lib/default_config.php");
if(!empty($_SESSION['blog_id']) && file_exists("../lib/config/blog_{$_SESSION['blog_id']}.php"))
        include("../lib/config/blog_{$_SESSION['blog_id']}.php");
include("../style/{$ct_config['blog_style']}/blogstyle.php");

if (isset($_GET['__mode']) && $_GET['__mode'] == 'rss') {
	$rss = claddier_trackback_rss_feed($_GET['tb_id']);
	header('Content-type: application/rss+xml');
	echo $rss;
}
elseif (isset($_GET['__mode']) && $_GET['__mode'] == 'rdf') {
	$sql = "SELECT `blog_bits`.*, `user_fname`, `blog_sname`, `blog_zone` FROM `blog_bits` INNER JOIN `users` ON `blog_bits`.`bit_user` = `users`.`user_name` INNER JOIN `blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `bit_rid` = '" . addslashes($_GET['tb_id'])."'";
        $blogpost = db_get_next_row(runQuery($sql, "get blog, post and user fields for claddier dc metadata"));
	if (empty($blogpost)) {
		set_http_error(404, preg_replace("!{$ct_config['blog_path']}!", "", $_SERVER['REQUEST_URI'], 1));
        	exit();
        }
	if(!checkzone($blogpost['blog_zone'],0,$blogpost['bit_blog']) || !checkzone($ct_config['blog_zone'])){
		set_http_error(403, preg_replace("!{$ct_config['blog_path']}!", "", $_SERVER['REQUEST_URI'], 1));
                exit();
	}
//	$revision = "";
//	if ($blogpost['bit_edit'] > 0) {
	$revision = "?revision={$blogpost['bit_rid']}";
//	}
	header('Content-type: application/rdf+xml');
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo claddier_dc_metadata(render_blog_link($blogpost['bit_id'], true) . $revision, $ct_config['blog_url'] . "claddier/" . $blogpost['bit_rid'], $blogpost['bit_title'], $blogpost['bit_timestamp'], $blogpost['user_fname'], render_link($blogpost['blog_sname']));
}
else {
	foreach ($_POST as $field => $value) {
		$_POST[$field] = stripslashes($value);
	}
	if ($_SERVER['REQUEST_METHOD'] != "POST") {
		claddier_trackback_response(1, "This URL can only be used for POST requests.");
	}
	$repos = runQuery("SELECT * FROM `claddier_whitelist` WHERE `repository_ip_address` = '" . addslashes($_SERVER['REMOTE_ADDR']) . "'", 'find claddier repository with ip address');
	if (db_get_number_of_rows($repos) == 0) {
		claddier_trackback_response(1, "Your IP address ({$_SERVER['REMOTE_ADDR']}) is not present in our white-list.");
	}
	if (empty($_POST['title']) || empty($_POST['url'])) {
		claddier_trackback_response(1, "Trackback message format is not valid.");
	}
	if (empty($_POST['type']) || !in_array($_POST['type'], array('cites', 'cited-by', 'copy'))) {
		claddier_trackback_response(1, "Claddier message format is not valid.");
	}
	$blog_posts = runQuery("SELECT `bit_id` FROM `blog_bits` WHERE `bit_rid` = '" . addslashes($_GET['tb_id']) . "'", 'find blog post with revision id');
        if (db_get_number_of_rows($blog_posts) == 0) {
                claddier_trackback_response(1, "Labtrove blog post not found or yet to be published");
        }
	$citations = runQuery("SELECT `id` FROM `claddier_citations` WHERE `type` = 'cited-by' AND `url` = '" . addslashes($_POST['url']) . "' AND `ping_url` = '" . $ct_config['blog_url'] . addslashes(substr($_SERVER['REQUEST_URI'], 1)) . "'");
	if (db_get_number_of_rows($citations) > 0) {
                claddier_trackback_response(1, "Duplicate Trackback URL detected.");
        }
	$sql = "SELECT * FROM `blog_bits` WHERE `bit_rid` = '" . addslashes($_GET['tb_id'])."'";
	$blogpost = db_get_next_row(runQuery($sql, "get bit_id from bit_rid"));
	$citation = runQuery("INSERT INTO claddier_citations VALUES('','" . addslashes($_POST['title']) . "', '" . addslashes($_POST['url']) . "',  '" . $ct_config['blog_url'] . addslashes(substr($_SERVER['REQUEST_URI'], 1)) . "', '" . addslashes($_POST['excerpt']) . "', '" . addslashes($_POST['blog_name']) . "', 0, NOW(), '" . addslashes($_POST['metadata']) . "', '" . addslashes($_POST['metadata_format']) . "', '" . addslashes($_POST['type']) . "','" . addslashes($_SERVER['REMOTE_ADDR']) . "', '" . $blogpost['bit_id'] . "', '" . addslashes($_GET['tb_id'])."')", "insert claddier backward citation");
	if (empty($citation)) {
		claddier_trackback_response(1, "Unable to update Labtrove blog post metadata.");
	}
	$blogpost['bit_cache'] = makepostcache($blogpost);
	claddier_trackback_response(0, "");
	
}
?>
