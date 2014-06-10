#!/usr/bin/php
<?php
include("../default_config.php");
$sql = "SELECT COUNT(*) AS `no_citations` FROM `claddier_citations`";
$res = runQuery($sql, 'count existing claddier citations');
if (empty($res)) {
	die("ERROR: Claddier tables have not been added to the database or cannot connect to database.\n");
}
if (!in_array("claddier", $ct_config['plugins'])) {
	die('ERROR: Claddier plugin has not been added to $ct_config[\'plugins\'] in config.php.' . "\n");
}
$row = db_get_next_row($res);
if ($row['no_citations'] > 0) {
	die("ERROR: There are already Claddier citations in the database.  Please truncate the claddier_citations table and try again.  Any local citations removed by this will be regenerated when this script is run.\n");
}
$sql = "SELECT `blog_bits`.*, `users`.`user_fname`, `blog_blogs`.`blog_sname` FROM `blog_bits` INNER JOIN `users` ON `blog_bits`.`bit_user` = `users`.`user_name` INNER JOIN `blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `blog_bits`.`bit_content` LIKE '%[blog%'  ";
$posts = runQuery($sql, 'get posts with local citations');
$cited_posts = array();
echo "Adding Claddier citations to database.";
while ($cites_post = db_get_next_row($posts)) {
	$cites_url = render_blog_link($cites_post['bit_id'], true) . "?revision=" . $cites_post['bit_rid'];
        $cites_ping_url = $ct_config['blog_url'] . "claddier/" . $cites_post['bit_rid'];
	$cites_blog_url = render_link($cites_post['blog_sname']);
	$cites_metadata = claddier_dc_metadata($cites_url, $cites_ping_url, $cites_post['bit_title'], $cites_post['bit_timestamp'], $cites_post['user_fname'], $cites_blog_url);
	preg_match_all("/\[blog=([0-9]+)\]/", $cites_post['bit_content'], $matches);
        for ($m = 0; $m < sizeof($matches[0]); $m++) {
		$excerpt = claddier_generate_citation_excerpt($cites_post['bit_content'], $matches[0][$m]);
		$cited_sql = "SELECT `blog_bits`.*, `users`.`user_fname`, `blog_blogs`.`blog_sname` FROM `blog_bits` INNER JOIN `users` ON `blog_bits`.`bit_user` = `users`.`user_name` INNER JOIN `blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `blog_bits`.`bit_id` = '" . addslashes($matches[1][$m]) . "' AND `blog_bits`.`bit_timestamp` < '" . $cites_post['bit_timestamp'] . "' AND `blog_bits`.`bit_edit` >= 0 ORDER BY `blog_bits`.`bit_timestamp` DESC LIMIT 1";
		$cited_post = db_get_next_row(runQuery($cited_sql, "get last published revision of post before a timestamp"));
                $cited_url = render_blog_link($cited_post['bit_id'], true) . "?revision=" . $cited_post['bit_rid'];
		$cited_ping_url = $ct_config['blog_url'] . "claddier/" . $cited_post['bit_rid'];
		$cited_blog_url = render_link($cited_post['blog_sname']);
		$cited_metadata = claddier_dc_metadata($cited_url, $cited_ping_url, $cited_post['bit_title'], $cited_post['bit_timestamp'], $cited_post['user_fname'], $cited_blog_url);
		runQuery("INSERT INTO claddier_citations VALUES('','" . addslashes($cites_post['bit_title']) . "', '" . addslashes($cites_url) . "',  '" . $cited_ping_url . "', '$excerpt', '" . addslashes($cites_blog_url) . "', 0, '" . $cites_post['bit_timestamp'] . "', '" . addslashes($cites_metadata) . "', 'application/rdf+xml', 'cited-by', '127.0.1.1', '" . $cited_post['bit_id'] . "', '" . $cited_post['bit_rid'] . "')", "insert claddier backward citation");
		runQuery("INSERT INTO claddier_citations VALUES('','" . addslashes($cited_post['bit_title']) . "', '" . addslashes($cited_url) . "',  '" . $cited_ping_url . "', '$excerpt', '" . addslashes($cited_blog_url) . "', '" . $cites_post['bit_timestamp'] . "', 0, '" . addslashes($cited_metadata) . "', 'application/rdf+xml', 'cites', '127.0.1.1', '" . $cites_post['bit_id'] . "', '" . $cites_post['bit_rid'] . "')", "insert claddier forward citation");
		echo ".";
		$cited_posts[$cited_post['bit_rid']] = $cited_post;
        }
}
echo "Done\n";
echo "Updating cached content for cited blog posts.";
foreach ($cited_posts as $cited_post) {
	makepostcache($cited_post);
	echo ".";
}
echo "Done\n";
	
?>
