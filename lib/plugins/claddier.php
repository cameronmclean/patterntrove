<?php

$ct_config['protected_paths'][] = "claddier";
$ct_config['hooks']['on_post_new'][] = array("function"=>"claddier_generate_citations_async", "params"=>array("bit_rid", "bit_edit"));
$ct_config['hooks']['on_post_edit'][] = array("function"=>"claddier_generate_citations_async", "params"=>array("bit_rid", "bit_edit"));

function claddier_discover($url) {
	$lines = @file($url);
	if (empty($lines)) {
		return;
	}
	$r=0;
	$rdfs = array();
	for ($l = 0; $l < sizeof($lines); $l++) {
		if (preg_match("/<rdf:RDF.*/", $lines[$l], $matches)) {
			$rdfs[$r] = $matches[0];
			while (!preg_match("!</rdf:RDF>!", $lines[$l+1])) {
				$rdfs[$r] .= $lines[++$l];
			}
			preg_match("!</rdf:RDF>!", $lines[++$l], $matches);
			$rdfs[$r++] .= $matches[0];
		}
	}
	foreach ($rdfs as $r) {
		if (preg_match("/[^:\n]+:ping.+/", $r, $matches)) {
			if (preg_match("!https?://[^'\"]+!", $matches[0], $matches2)) {
				$ping_url = $matches2[0];	
				$rdf = $r;
			}
		}
	}
	if (empty($ping_url)) {
		return;
	}
	$final_url = claddier_redirects($url);
	if (empty($final_url)) {
		return;
	}
	return array($ping_url, $rdf, $final_url);

}

function claddier_ping($ping_url, $fields) {
	$data = "";
	foreach ($fields as $field => $value) {
		$data .= urlencode($field) . "=" . urlencode($value) . "&";
	}
	$data = substr($data, 0, -1);
	$params = array('http' => array('method' => 'POST', 'content' => $data));
	$ctx = stream_context_create($params);
	$fp = @fopen($ping_url, 'rb', false, $ctx);
  	if (!$fp) {
    		return;
	}
	
  	$xml = @stream_get_contents($fp);
	if ($xml === false) {
    		return;
  	}	
	$xml_obj = simplexml_load_string($xml);
	if (isset($xml_obj->message)) {
		return array($xml_obj->error, $xml_obj->message, $xml);
	}
	return array($xml_obj->error, "", $xml);
}

function claddier_redirects($url, $redirect_urls = array()) {
	$redirect_urls[] = $url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
    	$out = curl_exec($ch);
    	$out = str_replace("\r", "", $out);
    	$headers_end = strpos($out, "\n\n");
    	if ($headers_end !== false) { 
       		$out = substr($out, 0, $headers_end);
    	}   

    	$headers = explode("\n", $out);
    	foreach($headers as $header) {
        	if (substr($header, 0, 10) == "Location: ") {
			$target = substr($header, 10);
			if (in_array($target, $redirect_urls)) {
				return "";
			}
			else {
	            		return claddier_redirects(substr($header, 10), $redirect_urls);
			}
		}
	}
        return $url;
}

function claddier_trackback_response($error, $message) {
	header('Content-type: application/xml');
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<response>
  <error>$error</error>\n";
	if (!empty($message)) {
		$xml .= "  <message>$message</message>\n";
	}
	$xml .= "</response>";
	echo $xml;
	exit();
}

function claddier_trackback_ip_address($ping_url) {
	preg_match('!https?://([^/]+)!', $ping_url, $matches);
	if (isset($matches[1])) {
		return gethostbyname($matches[1]);
	}
}

function claddier_whitelisted_ip_address($remote_ip) {
	if (!filter_var($remote_ip, FILTER_VALIDATE_IP)) {
		return false;
	}
	if (filter_var($remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$sql = "SELECT `repository_ip_address` FROM `claddier_whitelist` WHERE `repository_ip_address` NOT LIKE '%:%'";
		$ips = runQuery($sql, "find ipv4 whitelisted ips");
		while ($row = db_get_next_row($ips)) {
			$ip = $row['repository_ip_address'];
			if (substr($ip, -1) != '*') {
				$ip .= '*';
			}
			if (preg_match("/^$ip/", $remote_ip)) {
				return true;
			}
		}
	}
	elseif (filter_var($remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
		$sql = "SELECT `repository_ip_address` FROM `claddier_whitelist` WHERE `repository_ip_address` NOT LIKE '%.%'";
		$ips = runQuery($sql, "find ipv6 whitelisted ips");
                while ($row = db_get_next_row($ips)) {
			$ip = $row['repository_ip_address'];
			if (substr($ip, -2) == '::') {
				$ip = substr($ip, 0, -1) . '*';
			}
                        if (substr($ip, -1) != '*') {
                                $ip .= '*';
                        }
                        if (preg_match("/^$ip/", $remote_ip)) {
                                return true;
                        }
                }
	}
	return false;
}

function claddier_generate_citations_async($bit_rid, $bit_edit) {
        if ($bit_edit == 0) {
		$cwd = explode("/", getcwd());
		$cwd_last = $cwd[sizeof($cwd)-1];
		$rel_path = ".";
		if ($cwd_last == "docs") {
			$rel_path = "..";
		}
		elseif (in_array($cwd_last, array("rest", "soap"))) {
			$rel_path = "../../..";
		}
		pclose(popen('cd ' . $rel_path . '/lib/scripts/; `which php` ./claddier_generate_citations.php ' . escapeshellarg(addslashes($bit_rid)) . ' > /dev/null &', 'r'));
	}
}
	
function claddier_generate_citations($bit_id, $bit_rid, $bit_user, $bit_title, $bit_content, $bit_timestamp, $bit_blog) {
	global $ct_config;
	$bit_url = render_blog_link($bit_id, true) . "?revision=$bit_rid";
	$bit_ping_url = $ct_config['blog_url'] . "claddier/" . $bit_rid;
	$sql = "SELECT `user_fname` FROM users WHERE `user_name` = '$bit_user'";
	$user = db_get_next_row(runQuery($sql, 'get user name from id'));
	$sql = "SELECT `blog_sname` FROM blog_blogs WHERE `blog_id` = '$bit_blog'";
	$blog = db_get_next_row(runQuery($sql, "get blog short name from blog id"));
	$bit_blog_url = render_link($blog['blog_sname']);
	preg_match_all("/\[blog=([0-9]+)\]/", $bit_content, $matches);
	if (isset($matches[0])) {
	        for ($m = 0; $m < sizeof($matches[0]); $m++) {
			$cited_url = render_blog_link($matches[1][$m], true);
			claddier_generate_citation($bit_id, $bit_rid, $bit_url, $user['user_fname'], $bit_title, $bit_content, $bit_timestamp, $bit_blog_url, $bit_ping_url, $cited_url, $matches[0][$m]);	
        	}
	}
	preg_match_all('!https?://[^" <]+!', $bit_content, $matches);
	$matches = array_unique($matches[0]);
	if (isset($matches)) {
		foreach ($matches as $cited_url) {
			claddier_generate_citation($bit_id, $bit_rid, $bit_url, $user['user_fname'], $bit_title, $bit_content, $bit_timestamp, $bit_blog_url, $bit_ping_url, $cited_url, $cited_url);
		}
	}       
}

function claddier_generate_citation_excerpt($content, $cited_string_match, $context_length = 100) {
	$content = trim(stripslashes($content));
        $citation_position = strpos($cited_string_match, $content);
        $excerpt_start = $citation_position - $context_length;
        $excerpt_length = $context_length * 2;
        if ($excerpt_start < 0) {
                $excerpt_length += $excerpt_start;
                $excerpt_start = 0;
        }
	return addslashes(substr($content, $excerpt_start, $excerpt_length));
}

function claddier_generate_citation($bit_id, $bit_rid, $bit_url, $bit_user_fname, $bit_title, $bit_content, $bit_timestamp, $bit_blog_url, $bit_ping_url, $cited_url, $cited_string_match) {
	list($ping_url, $rdf, $final_url) = claddier_discover($cited_url);
        $ping_url_ip = claddier_trackback_ip_address($ping_url);
	$excerpt = claddier_generate_citation_excerpt($bit_content, $cited_string_match);
        $bit_metadata = claddier_dc_metadata($bit_url, $bit_ping_url, $bit_title, $bit_timestamp, $bit_user_fname, $bit_blog_url);
	list($error, $message, $xml) = claddier_ping($ping_url, array("title" => trim($bit_title), "url" => $bit_url, "excerpt" => $excerpt, "blog_name" => $bit_blog_url, "metadata" => $bit_metadata, "metadata_format" => "application/rdf+xml", "type" => "cited-by"));
	if (isset($error)) {
		if ($error > 0) {
			newMsg("Backward citation failed for $ping_url: {$message}");
		}
		else { 	
			$rdf_obj = new SimpleXMLElement($rdf);
			$rdf_obj->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
			$rdf_obj->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
			$rdf_obj->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');
			$cited_title = trim(array_pop($rdf_obj->xpath('rdf:Description/dc:title')));
			$cited_blog_url = trim(array_pop($rdf_obj->xpath('rdf:Description/dcterms:isPartOf')));
			runQuery("INSERT INTO `claddier_citations` VALUES('', '" . addslashes($cited_title) . "', '" . addslashes($final_url) . "', '" . addslashes($ping_url) . "', '$excerpt', '" . addslashes($cited_blog_url) . "', NOW(), 0, '" . addslashes($rdf) . "', 'application/rdf+xml', 'cites', '" . addslashes($ping_url_ip) . "', '$bit_id', '$bit_rid')", 'insert claddier forward citation');
		}
	}

}

function claddier_dc_metadata($bit_url, $ping_url, $bit_title, $bit_timestamp, $bit_user_fname, $bit_blog_url) {
	global $ct_config;
	return "<rdf:RDF 
 xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"
 xmlns:dc=\"http://purl.org/dc/elements/1.1/\"
 xmlns:dcterms=\"http://purl.org/dc/terms/\"
 xmlns:trackback=\"http://madskills.com/public/xml/rss/module/trackback/\"
 xmlns:trackbackx=\"http://epubs.cclrc.ac.uk/vocab/trackback/\">
    <rdf:Description
     rdf:about=\"$bit_url#rdf\"
     trackback:ping=\"$ping_url\"
     dc:identifier=\"$bit_url\">
        <dc:title>$bit_title</dc:title>
        <dcterms:issued>$bit_timestamp</dcterms:issued>
        <dc:creator>$bit_user_fname</dc:creator>
        <dcterms:isPartOf>$bit_blog_url</dcterms:isPartOf>
    </rdf:Description>

    <rdf:Description 
     rdf:about=\"{$ct_config['blog_url']}/claddier\"
     dc:type=\"http://purl.org/dc/dcmitype/Service\"
     dc:title=\"{$ct_config['blog_title']} trackback receiver service\">
        <trackbackx:preferredSchemas>
            <rdf:Seq>
                <rdf:li>
                    <trackbackx:MetadataSchema rdf:about=\"http://purl.org/dc/elements/1.1/\" />
                </rdf:li>
            </rdf:Seq>
            <rdf:Seq>
                <rdf:li>
                    <trackbackx:MetadataSchema rdf:about=\"http://purl.org/eprint/epdcx\" />
                </rdf:li>
            </rdf:Seq>
        </trackbackx:preferredSchemas>
    </rdf:Description>

</rdf:RDF>";
}

function claddier_trackback_rss_feed($bit_rid) {
	global $ct_config;
	$sql = "SELECT `blog_bits`.*, `blog_name`, `blog_zone` FROM `blog_bits` INNER JOIN `blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `bit_edit` >= 0 AND `bit_rid` = '" . addslashes($bit_rid) . "'";
	$blogpost = db_get_next_row(runQuery($sql, "get blog post from blog post revision id"));
	if (empty($blogpost)) {
		set_http_error(404, preg_replace("!{$ct_config['blog_path']}!", "", $_SERVER['REQUEST_URI'], 1));
        	exit();
	}
	if(!checkzone($blogpost['blog_zone'],0,$blogpost['bit_blog']) || !checkzone($ct_config['blog_zone'])){
                set_http_error(403, preg_replace("!{$ct_config['blog_path']}!", "", $_SERVER['REQUEST_URI'], 1));
                exit();
        }
	if ($blogpost['bit_edit'] > 0) $revision = " (revision $bit_rid)";
	$rss = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<rss version=\"2.0\">
    <channel>
        <title>Cited By List: " . htmlspecialchars($blogpost['blog_name']) ." - " . htmlspecialchars($blogpost['bit_title']) . "$revision</title>
        <description>This is a list of all publications that have cited " . htmlspecialchars($blogpost['bit_title']) . " in " . htmlspecialchars($blogpost['blog_name']) ."$revision.</description>
        <link>{$ct_config['blog_url']}claddier/{$bit_rid}</link>
        <lastBuildDate>" . date('r') . "</lastBuildDate>
        <pubDate>" . date('r') . "</pubDate>
        <ttl>1800</ttl>\n\n";

	$sql = "SELECT * FROM `claddier_citations` WHERE `type` = 'cited-by' AND `blog_bit_rid` = '" . addslashes($bit_rid) . "'";
        $result = runQuery($sql, "find backward citations for a blog post revision");
	while ($citation = db_get_next_row($result)) {
        	$rss .= "        <item>
            <title>" . htmlspecialchars($citation['title']) ."</title>
            <description>" . htmlspecialchars($citation['excerpt']) ."</description>
            <link>{$citation['url']}</link>
            <guid>" . sha1($citation['url']) ."</guid>
            <pubDate>". date('r', strtotime($citation['received_at'])) . "</pubDate>
        </item>\n\n";
	}	
	$rss .= "    </channel>
</rss>";
	return $rss;
}

function claddier_linked_from($bit_id) {
	$sql = "SELECT * FROM `claddier_citations` WHERE type = 'cited-by' AND `blog_bit_id` = '" . addslashes($bit_id)  ."'";
	$result = runQuery($sql, "find backward citations for a blog post");
	$citations = array();
	while ($row = db_get_next_row($result)) {
        	$citations[] = $row;
        }
	return $citations;
}

function claddier_list_citations($citations, &$linked, $post_id) {
	global $ct_config;
	if (sizeof($citations) > 0) {
		$sql = "SELECT DISTINCT `blog_sname` FROM `blog_bits` INNER JOIN `blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `bit_id` = '" . addslashes($post_id) . "'";
                $this_blog = db_get_next_row(runQuery($sql, "get blog short name from bit_id"));
		$linked = "<div class=\"postLinkedItems\" id=\"postLinked_{$post_id}\"><b>This post is linked by:</b><ul>\n";
        	foreach($citations as $citation){
			$suffix = "";
			if (!preg_match("!^{$ct_config['blog_url']}!", $citation['url'])) {
				$suffix = " (external)";
			}
			else {
				if (!preg_match("!^{$ct_config['blog_url']}{$this_blog['blog_sname']}/!", $citation['url'])) {
					preg_match("!^{$ct_config['blog_url']}([^/]+)!", $citation['url'], $matches);
					$sql = "SELECT DISTINCT `blog_name` FROM `blog_blogs` WHERE `blog_sname` = '" . addslashes($matches[1]) . "'";
					$citing_blog = db_get_next_row(runQuery($sql, "get blog name from short name"));
					$suffix = " (<a href=\"{$matches[0]}\">{$citing_blog['blog_name']}</a>)";
				}
			}
                	$linked .= "<li><a href=\"{$citation['url']}\">{$citation['title']}</a>$suffix</li>";
	        }
        	$linked .= "</ul></div>\n";
        	return "<div class=\"postLinkedBut\" onclick=\"$('#postLinked_{$post_id}').fadeIn();\">Linked Posts</div>";
	}
}
?>
