<?php
function blog_style_post(&$blogpost)
{
	global $ct_config;
	$ret = "<div class=\"containerPost\">\n";
	$uri_parts = explode("/", $_GET['uri']);
	if (in_array('claddier', $ct_config['plugins']) && !empty($blogpost['id']) && sizeof($uri_parts) > 1 && is_numeric($uri_parts[1])) {		
		$sql = "SELECT `bit_timestamp`, `user_fname`, `blog_sname` FROM `blog_bits` INNER JOIN `users` ON `blog_bits`.`bit_user` = `users`.`user_name` INNER JOIN `blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `bit_rid` = '" . $blogpost['id'] . "'";	
		$blogpost_claddier = db_get_next_row(runQuery($sql, "get blog, post and user fields for claddier dc metadata"));
		$ret .= "<!-- " . claddier_dc_metadata($blogpost['url'], $ct_config['blog_url'] . "claddier/" . $blogpost['id'], $blogpost['title'], $blogpost_claddier['bit_timestamp'], $blogpost_claddier['user_fname'], render_link($blogpost_claddier['blog_sname'])) . " -->\n";
	}

	if(isset($blogpost['infohead']) && $blogpost['infohead'])
	{
		$ret .= "\t\t\t	<div style=\"clear:left;\">";
		$ret .= "<div class=\"infoBox\">{$blogpost['infohead']}</div></div>\n";
	}

	if(is_set_not_empty('url', $blogpost))
	{
		$ret .= "\t\t\t<div class=\"postTitle\"><a href=\"{$blogpost['url']}\">{$blogpost['title']}</a></div>\n";
	}
	elseif(is_set_not_empty('title', $blogpost))
	{
		$ret .= "\t\t\t<div class=\"postTitle\">{$blogpost['title']}</div>\n";
	}

	$date = (isset($blogpost['date'])) ? $blogpost['date'] : '';
	$ret .= "\t\t\t<div class=\"timestamp\">{$date}</div>\n";
	if($blogpost['post'])
	{
		$ret .= "\t\t\t	<div class=\"postText\">{$blogpost['post']}</div>\n";
	}

	if(isset($blogpost['data']) && $blogpost['data'])
	{
		if(isset($blogpost['data_hideable']) && $blogpost['data_hideable']){
			$linkstyle = "style=\"cursor:pointer; display:block;\" onclick=\"$('#post_{$blogpost['id']}_dataBox').fadeIn();\"";
			 	$boxstyle = "style=\"display:none;\"";
		}else{
				$linkstyle = "";
				 	$boxstyle = "";
		}
		
		$ret .= "\t\t\t	<div style=\"clear:left;\">";
		if($blogpost['data_title'])
		{
			$ret .= "<span 	$linkstyle class=\"dataTitle\">{$blogpost['data_title']}</span>";
		}
		$ret .= "<div class=\"dataBox\" id=\"post_{$blogpost['id']}_dataBox\" $boxstyle>{$blogpost['data']}</div></div>\n";
	}

	if(is_set_not_empty('footer', $blogpost))
	{
		$ret .= "\t<div class=\"postInfo\" width=\"100%\" style=\"clear:left;\">{$blogpost['footer']}</div>";
	}
	$ret .= "</div>";

	return $ret;
}

function blog_style_comment($comment)
{
	return <<<END
		<div class="containerComment"><div><b><a name="{$comment['com_id']}" href="{$comment['com_url']}">{$comment['com_title']}</a></b>
			by {$comment['com_user']} </div>
		<div class="timestampComment">{$comment['com_rdate']}</div>
			{$comment['com_html']}</div>
END;
}


function blog_style_error($errmsg, $sev = "error", $id = 0)
{
	return <<<END
		<div class="msg {$sev}" id="msg_$id"><a href="#" class="msg_close" onclick="$('#msg_$id').fadeOut('slow'); return false;">x</a>
		$errmsg</div>
END;
}
?>
