<?php
//edit a comment
function add_com($bit_id, $com_title, $com_content){

global $ct_config;		
		$sql = "INSERT INTO  blog_com (  com_id ,  com_bit ,  com_user ,  com_title ,  com_cont ,  com_datetime ,  com_del ) 
VALUES ( 0 ,  '".$bit_id."',  '".$_SESSION['user_name']."',  '".trim($com_title)."',  '".trim($com_content)."', NOW( ) ,  '0'
);";	runQuery($sql,'Blogs');

$new_id = db_insert_id();

		$sql = "UPDATE  blog_com SET com_id =  $new_id WHERE  com_rid = ".$new_id." " . db_limit_1();	runQuery($sql,'Blogs');

global $blog_id;
if($ct_config['blog_enmsg'])
	new_com_post($new_id, $blog_id);		

updatesidecache();

return $new_id;


}

//edit a comment
function edit_com($com_id,$com_editwhy, $com_title = NULL, $com_content = NULL){

global $ct_config;


$sql = "SELECT * FROM  blog_com WHERE com_id = $com_id and com_edit=0";
$tresult = runQuery($sql,'Fetch old edit');
$row = db_get_next_row($tresult);

if(!$com_title){
	$com_title = addslashes($row['com_title']);
}
if(!$com_content){
	$com_content = addslashes($row['com_cont']);
}

$sql = "INSERT INTO  blog_com (  com_id ,  com_bit ,  com_user ,  com_title ,  com_cont ,  com_datetime ,  com_del, com_edit, com_edituser, com_editwhy ) 
VALUES ( $com_id ,  '".$row['com_bit']."',  '".$row['com_user']."',  '". trim($com_title)."',  '".trim($com_content)."', '".$row['com_datetime']."' ,  '0' , '0' , '', '' );";

runQuery($sql,'Insert ');
$new_id = db_insert_id();

		$sql = "UPDATE  blog_com SET  com_edit =  $new_id, com_edituser =  '".$_SESSION['user_name']."' , com_editwhy = '".trim($com_editwhy)."'  WHERE  com_rid = ".$row['com_rid']." " . db_limit_1();
	runQuery($sql,'Blogs');

updatesidecache();

return $new_id;

}

function edit_blog_deledit($post_id){
	global $ct_config;
	$row = db_get_post_by_id($post_id, -1);

	if( $_SESSION['user_name']==$row['bit_user'] || $_SESSION['user_admin'] > 1 || $user_can_edit){
			$sql = sprintf("UPDATE  blog_bits SET  bit_edit = '%s'  WHERE  bit_rid = %s ",
			    	-2,
				(int)$row['bit_rid']
			  );

			_db_call($sql);
	}
	
}


function edit_blog_new($bit_id,$save_task,$bit_editwhy, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL){

global $ct_config;

	$row = db_get_post_by_id($bit_id, '-1');

	$sql_set = "";
	if(strlen($bit_title)){
		$sql_set .= sprintf("bit_title = '%s', ", db_escape_string($bit_title));
	}
	if(strlen($bit_content)){
		$sql_set .= sprintf("bit_content = '%s', ", db_escape_string($bit_content));
	}
	if(strlen($bit_meta)){
		$sql_set .= sprintf("bit_meta = '%s', ", db_escape_string($bit_meta));
	}
	if(strlen($bit_meta)){
		$sql_set .= sprintf("bit_group = '%s', ", db_escape_string($bit_group));
	}
	 	$sql = sprintf("UPDATE  blog_bits SET %s bit_timestamp = NOW(), bit_cache = '' WHERE bit_rid = '%s' ;",
	   	$sql_set,
	    (int)$row['bit_rid']
	  );
	_db_call($sql);
	
	
	$rowa = db_get_post_by_id($bit_id, -1);

		$key = md5($rowa['bit_rid'].$rowa['bit_rid'].$rowa['bit_user'].$rowa['bit_title'].$rowa['bit_content'].$rowa['bit_meta'].$rowa['bit_datestamp'].$rowa['bit_timestamp'].$rowa['bit_group'].$rowa['bit_blog']);
	

		if($save_task=='publish'){
			$bit_edit = 0;
			
			if($rowa['bit_rid']!=$rowa['bit_id']){
				
					$sql = sprintf("UPDATE  blog_bits SET  bit_edit = '%s',bit_edituser = '%s', bit_editwhy = '%s'  WHERE  bit_id = %s AND bit_edit = 0 ",
					$rowa['bit_rid'],
					db_escape_string($_SESSION['user_name']),
				    db_escape_string($bit_editwhy),
					
					(int)$rowa['bit_id']
				  );
				_db_call($sql);
			}
			
			
		}else{
			$bit_edit = -1;
		}

	 	$sql = sprintf("UPDATE  blog_bits SET  bit_md5 = '%s', bit_edit = '%s'  WHERE  bit_rid = %s ",
		    db_escape_string($key),
			$bit_edit,
			(int)$rowa['bit_rid']
		  );

		_db_call($sql);
		$rowa['bit_edit'] = $bit_edit;
		
$preg = array(
//[data]
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)",		
	'/\[blog=(\d+)\](.*?)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)"
);
$message = @preg_replace(array_keys($preg), array_values($preg), $bit_content);

if($rowa['bit_rid']==$rowa['bit_id'])
	hooks_run("on_post_new", $rowa);
else
	hooks_run("on_post_edit", $rowa);

//$sql = "UPDATE  blog_com SET  com_bit = $new_id WHERE  com_bit = $bit_id";
//runQuery($sql,'Blogs');

updatesidecache();
}

function edit_blog_draft($bit_id){

global $ct_config;
$sql = "SELECT * FROM  blog_bits WHERE bit_id = $bit_id AND bit_edit = 0";
$tresult = runQuery($sql,'Fetch old edit');
$row = db_get_next_row($tresult);

if(!$bit_title){
	$bit_title = addslashes($row['bit_title']);
}
if(!$bit_content){
	$bit_content = addslashes ($row['bit_content']);
}
if(!$bit_meta){
	$bit_meta = addslashes($row['bit_meta']);
}
if(!$bit_group){
	$bit_group = addslashes($row['bit_group']);
}
	$uri = $row['bit_uri'];


 $sql = "INSERT INTO  blog_bits (  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit , bit_edituser,  bit_editwhy, bit_uri ) 
VALUES (
$bit_id ,  '".$row['bit_user']."',  '".trim($bit_title)."',  '".trim($bit_content)."',  '".trim($bit_meta)."', '".$row['bit_datestamp']."', NOW( ) ,  '".trim($bit_group)."',  '".$row['bit_blog']."',  '-1',  '' , '' , $uri);";

runQuery($sql,'Insert ');

}

function edit_blog($bit_id,$bit_editwhy, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL){

global $ct_config;
$sql = "SELECT * FROM  blog_bits WHERE bit_id = $bit_id AND bit_edit = 0";
$tresult = runQuery($sql,'Fetch old edit');
$row = db_get_next_row($tresult);

if(!$bit_title){
	$bit_title = addslashes($row['bit_title']);
}
if(!$bit_content){
	$bit_content = addslashes ($row['bit_content']);
}
if(!$bit_meta){
	$bit_meta = addslashes($row['bit_meta']);
}
if(!$bit_group){
	$bit_group = addslashes($row['bit_group']);
}
	$uri = $row['bit_uri'];


 $sql = "INSERT INTO  blog_bits (  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit , bit_edituser,  bit_editwhy, bit_uri ) 
VALUES (
$bit_id ,  '".$row['bit_user']."',  '".trim($bit_title)."',  '".trim($bit_content)."',  '".trim($bit_meta)."', '".$row['bit_datestamp']."', NOW( ) ,  '".trim($bit_group)."',  '".$row['bit_blog']."',  '0',  '' , '' , $uri);";

runQuery($sql,'Insert ');
$new_id = db_insert_id();

$sql = "SELECT * FROM  blog_bits WHERE  bit_rid = $new_id";
		$tresult = runQuery($sql,'Fetch md5 Groups');
    	$rowa = db_get_next_row($tresult);

		$key = md5($rowa['bit_rid'].$rowa['bit_rid'].$rowa['bit_user'].$rowa['bit_title'].$rowa['bit_content'].$rowa['bit_meta'].$rowa['bit_datestamp'].$rowa['bit_timestamp'].$rowa['bit_group'].$rowa['bit_blog']);
	
		$sql = "UPDATE  blog_bits SET  bit_md5 = '$key' WHERE  bit_rid = $new_id";
		runQuery($sql,'Blogs');

		$sql = "UPDATE  blog_bits SET  bit_edit =  $new_id, bit_edituser =  '".$_SESSION['user_name']."' , bit_editwhy = '".trim($bit_editwhy)."'  WHERE  bit_rid = ".$row['bit_rid']." ".db_limit_1();
	runQuery($sql,'Blogs');

$preg = array(
//[data]
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)"
);
$message = @preg_replace(array_keys($preg), array_values($preg), $bit_content);

hooks_run("on_post_edit", $rowa);

//$sql = "UPDATE  blog_com SET  com_bit = $new_id WHERE  com_bit = $bit_id";
//runQuery($sql,'Blogs');

updatesidecache();
return $new_id;


}

/**
 * Append to an existing blog entry
 * @param type $bit_id
 * @param type $bit_editwhy
 * @param string $bit_title
 * @param string $bit_content
 * @param type $new_metadata
 * @param type $new_attacheddata
 * @param string $bit_group
 * @return type
 */
function append_blog($bit_id, $bit_editwhy, $bit_title = NULL, $bit_content = NULL, $new_metadata = NULL, $new_attacheddata = null, $bit_group = NULL)
{
	$sql = "SELECT * FROM  blog_bits WHERE bit_id = $bit_id AND bit_edit = 0";
	$tresult = runQuery($sql,'Fetch current blog state');
	$row = db_get_next_row($tresult);

	//Append new content to old
	$bit_title = addslashes($row['bit_title']).$bit_title;
	$bit_content = addslashes($row['bit_content']).$bit_content;
	$bit_group = addslashes($row['bit_group']).$bit_group;
	
	//Metadata	
	$current_metadata = simplexml_load_string($row['bit_meta']);	
	foreach($current_metadata->META[0] as $key=>$value){					
		if(empty($metadata['METADATA']['META'][$key]))
		{
			$metadata['METADATA']['META'][$key] = (String) $value;						
		}
		else $metadata['METADATA']['META'][$key]  .=";".(String) $value;
	}

	foreach($new_metadata->children() as $key=>$value){					
		if(empty($metadata['METADATA']['META'][$key]))
		{
			$metadata['METADATA']['META'][$key] = (String) $value;						
		}
		else $metadata['METADATA']['META'][$key]  .=";".(String) $value;
	}
	
	//Attached data			
	$metadata['METADATA']['DATA'] = (string) $current_metadata->DATA[0];		
	if(!empty($new_attacheddata))
	{
		foreach($new_attacheddata->data as $bla)
		{
			$data_attr = $bla->attributes();
			switch($data_attr['type'])
			{
				case "local":
					if(!empty($metadata['METADATA']['DATA']))
					{
						$metadata['METADATA']['DATA'] .= ",{$bla}";
					}
					else
					{
						$metadata['METADATA']['DATA'] = "{$bla}";
						
					}
					setposttodata((int) $bla, (int) $req->id);
					break;
			}//switch
		}//foreach
	}//if
	
	$bit_meta = writexml($metadata);
	$uri = $row['bit_uri'];
	$sql = "INSERT INTO  blog_bits (  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit , bit_edituser,  bit_editwhy, bit_uri ) 
	VALUES (
	$bit_id ,  '".$row['bit_user']."',  '".trim($bit_title)."',  '".trim($bit_content)."',  '".trim($bit_meta)."', '".$row['bit_datestamp']."', NOW( ) ,  '".trim($bit_group)."',  '".$row['bit_blog']."',  '0',  '' , '' , $uri);";

	runQuery($sql,'Insert ');
	$new_id = db_insert_id();

	$sql = "SELECT * FROM  blog_bits WHERE  bit_rid = $new_id";
			$tresult = runQuery($sql,'Fetch md5 Groups');
			$rowa = db_get_next_row($tresult);

			$key = md5($rowa['bit_rid'].$rowa['bit_rid'].$rowa['bit_user'].$rowa['bit_title'].$rowa['bit_content'].$rowa['bit_meta'].$rowa['bit_datestamp'].$rowa['bit_timestamp'].$rowa['bit_group'].$rowa['bit_blog']);

			$sql = "UPDATE  blog_bits SET  bit_md5 = '$key' WHERE  bit_rid = $new_id";
			runQuery($sql,'Blogs');

			$sql = "UPDATE  blog_bits SET  bit_edit =  $new_id, bit_edituser =  '".$_SESSION['user_name']."' , bit_editwhy = '".trim($bit_editwhy)."'  WHERE  bit_rid = ".$row['bit_rid']." ".db_limit_1();
		runQuery($sql,'Blogs');

	$preg = array(
	//[data]
		'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)"
	);
	$message = @preg_replace(array_keys($preg), array_values($preg), $bit_content);

	hooks_run("on_post_edit", $rowa);
	updatesidecache();
	return $new_id;
}//append_blog

function add_blog_new($bit_blog, $fdate = NULL, $fuser = NULL){
	global $ct_config;
	if(strlen($fdate)){
		$fdate = ereg_replace( "\;", "", $fdate);
	}else{
		$fdate = "NOW()";
	}
	if(!$fuser)	$fuser = $_SESSION['user_name'];

	if(!isset($bit_group)) { $bit_group = ''; }

	// added (bit_title, bit_content, bit_meta, bit_cache) as they are not allowed to be null under postgres as they have a non-null constraint - justin
	$sql = "INSERT INTO blog_bits (bit_id, bit_user, bit_datestamp, bit_timestamp, bit_group, bit_blog, bit_edit, bit_editwhy, bit_md5, bit_title, bit_content, bit_meta, bit_cache) "
	     . "VALUES (0 ,  '".$fuser."',  $fdate , NOW() ,  '".$bit_group."',  '".$bit_blog."',  '-1',  '', 'not-set', '', '', '', '');";
	runQuery($sql,'Blogs');

	$id = db_insert_id();
	$sql = "UPDATE  blog_bits SET  bit_id =  '$id' WHERE  bit_rid = $id ";
	runQuery($sql,'Blogs');

	$url = render_blog_link($id,true);
	$uri = uri_geturi($url);

	$sql = "UPDATE  blog_bits SET   bit_uri = $uri WHERE  bit_rid = $id";
	runQuery($sql,'Blogs');

	return $id;
}


//edit a blog
function add_blog($bit_blog, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL, $fdate = NULL, $fuser = NULL){

global $ct_config;

	if(strlen($fdate)){
		$fdate = ereg_replace( "\;", "", $fdate);
	}else{
		$fdate = "NOW()";
	}
	if(!$fuser)	$fuser = $_SESSION['user_name'];

	$sql = "INSERT INTO  blog_bits (  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit ,  bit_editwhy ) 
VALUES (
0 ,  '".$fuser."',  '".$bit_title."',  '".$bit_content."',  '$bit_meta', $fdate , NOW( ) ,  '".$bit_group."',  '".$bit_blog."',  '0',  '');";	

		runQuery($sql,'Blogs');
		$id = db_insert_id();
		$sql = "UPDATE  blog_bits SET  bit_id =  '$id' WHERE  bit_rid = $id ";
		runQuery($sql,'Blogs');

		$sql = "SELECT * FROM  blog_bits WHERE  bit_rid = $id";
		$tresult = runQuery($sql,'Fetch Page Groups');
    	$row = db_get_next_row($tresult);

		$key = md5($row['bit_rid'].$row['bit_rid'].$row['bit_user'].$row['bit_title'].$row['bit_content'].$row['bit_meta'].$row['bit_datestamp'].$row['bit_timestamp'].$row['bit_group'].$row['bit_blog']);

		$url = render_blog_link($id,true);

		$uri = uri_geturi($url);
	
		$sql = "UPDATE  blog_bits SET  bit_md5 = '$key' , bit_uri = $uri WHERE  bit_rid = $id";
		runQuery($sql,'Blogs');
		
		
//if($ct_config['blog_enmsg'])
//	 	new_item_post($id, $bit_blog);

	$preg = array(
//[data]
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)"
);

$message = @preg_replace(array_keys($preg), array_values($preg), $bit_content);
updatesidecache();


hooks_run("on_post_new", $row);



return $id;

}


function makepostcache(&$post){
		global $ct_config;
			$blogpost = "";
			$metadata = readxml($post['bit_meta']);
			if(isset($metadata['METADATA']) && isset($metadata['METADATA']['META']) && is_array($metadata['METADATA']['META'])) {
				foreach($metadata['METADATA']['META'] as $key => $value){
					$blogpost .= "<b>".strtotitle(str_replace("_"," ",$key)).":</b> $value<br />\n";
				}
			}
			$blogpost .= bbcode($post['bit_content']);
			if (in_array('claddier', $ct_config['plugins'])) {
				$blogpost .=  "<div class=\"postTools\">" . claddier_list_citations(claddier_linked_from($post['bit_id']), $linkeddiv, $post['bit_id']) . "</div>\n";
			}
			else {
				$blogpost .=  "<div class=\"postTools\">" . list_posts(linked_from($post['bit_id']), $linkeddiv, $post['bit_id']) . "</div>\n";
			}
			$blogpost .=  "$linkeddiv\n";
			$post['bit_cache'] = $blogpost;
			hooks_run_active("on_post_render", $post, $blogpost);
			
			$post['bit_cache'] = $blogpost;
				$tsql = "UPDATE  blog_bits SET  bit_cache =  ".db_escape_sentinel_template()."'".db_escape_string($blogpost)."' WHERE  blog_bits.bit_rid = {$post['bit_rid']};";
				runQuery($tsql,'Update Cache');
	
			return 	$blogpost;

}


function linked_from($bit_id){
global $ct_config;

$sql = "SELECT bit_id 
FROM   blog_bits 
WHERE  (bit_content LIKE '%[blog]{$bit_id}[/blog]%' OR bit_content LIKE '%[blog={$bit_id}]%' ) AND  bit_edit =0";
	
		$tresult = runQuery($sql,'Fetch Page Groups');
    	while($row = db_get_next_row($tresult)){
			$ids[] = $row['bit_id'];
		}

	return $ids;
}

function list_posts($bit_id, &$linked, $post_id){
if(count($bit_id)){
	$linked = "<div class=\"postLinkedItems\" id=\"postLinked_{$post_id}\"><b>This post is linked by:</b><ul>\n";
	foreach($bit_id as $id){
		$linked .= "<li>".render_blog_link($id)."</li>";
	}
	$linked .= "</ul></div>\n";
	return "<div class=\"postLinkedBut\" onclick=\"$('#postLinked_{$post_id}').fadeIn();\">Linked Posts</div>";
}
}








function new_com_post($com_id, $blog_id){
global $ct_config;

$sql = "SELECT * FROM  blog_users INNER JOIN  blog_sub ON u_name = sub_username WHERE sub_blog = $blog_id AND u_emailsub > 4";

$result = runQuery($sql,'Sub user for blog');

if(db_get_number_of_rows($result)){

	$sql = "SELECT *, ".db_timestamp("com_datetime")." as datetime FROM  blog_bits INNER JOIN  blog_blogs ON bit_blog = blog_id INNER JOIN blog_com ON com_bit = bit_id WHERE  com_id = $com_id AND com_edit =0";

	$tresult = runQuery($sql,'Sub user for blog');
	$row = db_get_next_row($tresult);
	$subject = strip_tags("[{$ct_config['blog_title']}] New Comment - {$row['com_title']}");

	$content_text = "New Comment: {$row['com_title']}\n";
	$content_text .= "For Post: {$row['bit_title']}\n";
	$content_text .= "by ".get_user_info($row['com_user'],'name')." as part of the {$row['blog_name']} blog.\n";
	$content_text .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])." \n";
	$content_text .= "\n ".render_blog_link($row['bit_id'],1)."#{$row['com_id']} \n";
	$content_text .= "\n\n The Blog Server \n\n\n To adjust your email settings please edit your user setting in the blog.";


	$content_html = "<h2>New Post: <a href=\"".render_blog_link($row['bit_id'],1)."#{$row['com_id']}\">{$row['com_title']}</a></h2>\n";
	$content_html .= "For Post: {$row['bit_title']}<br/>\n";
	$content_html .= "by ".get_user_info($row['com_user'],'name')." as part of the <a href=\"".render_link($row['blog_sname']).">{$row['blog_name']}</a> blog.<br />\n";
	$content_html .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])."<br /> \n";
	$content_html .= "\n ".render_blog_link($row['bit_id'],1)."#{$row['com_id']} <br /> \n";
	$content_html .= "<br />\n The Blog Server <br /><br /><br />\n\n\n To adjust your email settings please edit your user setting in the blog.";

	$key = $ct_config['blog_db']."_comment_".$row['com_id'];

while($row = db_get_next_row($result)){

	new_message(addslashes($subject), addslashes($content_text),  addslashes($content_html),  $row['u_name'],  1, $row['u_proflocate'],  $key, 1);

}

}

}


function buildbuttons(){

global $blog_id;

return "<input type=\"button\" value=\"b\" style=\"width:50px;font-weight:bold\" onclick=\"tag('b');\"/>
<input type=\"button\" value=\"i\" style=\"width:50px;font-style:italic\" onclick=\"tag('i');\"/>
<input type=\"button\" value=\"u\" style=\"width:50px;text-decoration:underline\" onclick=\"tag('u');\"/>
<input type=\"button\" value=\"size\" style=\"width:50px\" onclick=\"tag('size');\"/>
<input type=\"button\" value=\"quote\" style=\"width:50px\" onclick=\"tag('quote');\"/>
<input type=\"button\" value=\"code\" style=\"width:50px\" onclick=\"tag('code');\"/>
<input type=\"button\" value=\"url\" style=\"width:50px\" onclick=\"tag('url');\"/><br />
<input type=\"button\" value=\"img\" style=\"width:50px\" onclick=\"tag('img');\"/>
<input type=\"button\" value=\"link to post\" style=\"width:105px\" onclick=\"javascript:window.open('".render_link('linkblog.php',array("blog_id" => $blog_id))."','barcode', 'left=400,top=400,width=450,height=500,toolbar=0,resizable=0,location=0,directories=0,scrollbars=1,menubar=0,status=0'); void(0)\"/>";


}
function get_comment_count($id){

global $ct_config;

$sql = "SELECT * FROM  blog_com WHERE  com_bit = {$id} AND  com_edit =0;";

$result = runQuery($sql,'Sub user for blog');

return db_get_number_of_rows($result);
}





 $ct_config['bbcode_preg'] = array(
    // Font and text manipulation ( [color] [size] [font] [align] )
    '/\[color=(.*?)(?::\w+)?\](.*?)\[\/color(?::\w+)?\]/si'   => "<span style=\"color:\\1\">\\2</span>",
    '/\[size=(.*?)(?::\w+)?\](.*?)\[\/size(?::\w+)?\]/si'     => "<span style=\"font-size:\\1px\">\\2</span>",
    '/\[font=(.*?)(?::\w+)?\](.*?)\[\/font(?::\w+)?\]/si'     => "<span style=\"font-family:\\1\">\\2</span>",
    '/\[align=(.*?)(?::\w+)?\](.*?)\[\/align(?::\w+)?\]/si'   => "<div style=\"text-align:\\1\">\\2</div>",
    '/\[b(?::\w+)?\](.*?)\[\/b(?::\w+)?\]/si'                 => "<b>\\1</b>",
    '/\[i(?::\w+)?\](.*?)\[\/i(?::\w+)?\]/si'                 => "<i>\\1</i>",
    '/\[u(?::\w+)?\](.*?)\[\/u(?::\w+)?\]/si'                 => "<u>\\1</u>",
    '/\[s(?::\w+)?\](.*?)\[\/s(?::\w+)?\]/si'                 => "<span style=\"text-decoration: line-through;\">\\1</span>",
    '/\[center(?::\w+)?\](.*?)\[\/center(?::\w+)?\]/si'       => "<div style=\"text-align:center\">\\1</div>",
    '/\[code(?::\w+)?\](.*?)\[\/code(?::\w+)?\]/si'           => "<span class=\"code\"><pre>\\1</pre></span>",
    '/\[code=(.*?)(?::\w+)?\](.*?)\[\/code(?::\w+)?\]/sie'    => "code_render_inline('\\2',\"\\1\")",
	// [email]
    '/\[email(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si'         => "<a href=\"mailto:\\1\" class=\"ng_email\">\\1</a>",
    '/\[email=(.*?)(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si'   => "<a href=\"mailto:\\1\" class=\"ng_email\">\\2</a>",
    // [url]
    '/\[url(?::\w+)?\]www\.(.*?)\[\/url(?::\w+)?\]/si'        => "<a href=\"http://www.\\1\" class=\"ng_url\">\\1</a>",
    '/\[url(?::\w+)?\]((?:http|https|news|ftp)\:\/\/.*?)\[\/url(?::\w+)?\]/si'             => "<a href=\"\\1\" class=\"ng_url\">\\1</a>",
    '/\[url=((?:http|https|news|ftp)\:\/\/.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"\\1\" class=\"ng_url\">\\2</a>",
	'/\[url=(.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"http://\\1\" class=\"ng_url\">\\2</a>",
	'/\[url(?::\w+)?\](mailto\:.*?)\[\/url(?::\w+)?\]/si'             => "<a href=\"\\1\" class=\"ng_url\">\\1</a>",
    '/\[url=(mailto\:.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"\\1\" class=\"ng_url\">\\2</a>",
    // [img]
    '/\[img(?::\w+)?\]((?:http|https|ftp)\:\/\/.*?)\[\/img(?::\w+)?\]/si'             => "<img src=\"\\1\" border=\"0\" alt=\"image\"/>",
    '/\[img=(.*?)x(.*?)(?::\w+)?\]((?:http|https|ftp)\:\/\/.*?)\[\/img(?::\w+)?\]/si' => "<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=\"0\" alt=\"image\"/>",
   '/\[img=center(?::\w+)?\]((?:http|https|ftp)\:\/\/.*?)\[\/img(?::\w+)?\]/si' => "<center><img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=\"0\" alt=\"image\"/></center>",
    // [quote]
    '/\[quote(?::\w+)?\](.*?)\[\/quote(?::\w+)?\]/si'         => "<blockquote>\\1</blockquote>",
    '/\[quote=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/quote(?::\w+)?\]/si'   => "<div class=\"ng_quote\">Quote \\1:<div class=\"ng_quote_body\">\\2</div></div>",
    // [list]
    '/\[\*(?::\w+)?\]\s*([^\[]*)/si'                          => "<li class=\"ng_list_item\">\\1</li>",
    '/\[list(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/si'           => "<ul class=\"ng_list\">\\1</ul>",
    '/\[list(?::\w+)?\](.*?)\[\/list:u(?::\w+)?\]/s'          => "<ul class=\"ng_list\">\\1</ul>",
    '/\[list=1(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/si'         => "<ol class=\"ng_list\" style=\"list-style-type: decimal;\">\\1</ol>",
    '/\[list=i(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: lower-roman;\">\\1</ol>",
    '/\[list=I(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: upper-roman;\">\\1</ol>",
    '/\[list=a(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: lower-alpha;\">\\1</ol>",
    '/\[list=A(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: upper-alpha;\">\\1</ol>",
    '/\[list(?::\w+)?\](.*?)\[\/list:o(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: decimal;\">\\1</ol>",
    // the following lines clean up our output a bit
    '/<ol(.*?)>(?:.*?)<li(.*?)>/si'         => "<ol\\1><li\\2>",
    '/<ul(.*?)>(?:.*?)<li(.*?)>/si'         => "<ul\\1><li\\2>",

	'/\[pb\]/si' => "<!--page-->",
        
	//[data]
	
	'/\[data(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie'=>"''.getdata(\\1).''",
'/\[data=text(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "getdata(\\1,1)",
	'/\[data=text(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "getdata(\\1,1)",
        '/\[data=size\:(.*?)(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "getdata(\\2,2,'\\1')",
        '/\[data=(.*?)(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "'<div style=\"float:\\1;\">'.getdata(\\2).'</div>'",
	'/\[blog=(\d+)\](.*?)\[\/blog(?::\w+)?\]/sie'=>"render_blog_link('\\1',false,'.html','\\2')",
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"render_blog_link('\\1')",
	'/\[blog(?::\w+)?\]\[\/blog(?::\w+)?\]/si'=>"",
//pubmed
	'/\[pubmed(?::\w+)?\](.*?)\[\/pubmed(?::\w+)?\]/si'=>"<a href=\"http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=\\1\" target=_blank class=ext_link>PMID: \\1</a>",

	//[table]
	'/\[table(?::\w+)?\](.*?)\[\/table(?::\w+)?\]/si'=>"<table class=\"table_st\" cellspacing=\"0\">\\1</table>",
	'/\[row(?::\w+)?\](.*?)\[\/row(?::\w+)?\]\s*/si'=>"<tr><td class=\"table_st\">\\1</td></tr>",
	'/\[col(?::\w+)?\]/si'=>"</td><td class=\"table_st\">",	
	'/\[col=(.*?)(?::\w+)?\]/si'     => "</td><td class=\"table_st\" align=\"\\1\">",
	'/\[mrow(?::\w+)?\](.*?)\[\/mrow(?::\w+)?\]\s*/si'=>"<tr class=\"table_title\"><td class=\"table_st\">\\1</td></tr>",
	'/\[mcol(?::\w+)?\]/si'=>"</td><td class=\"table_st\">",



  );

function add_tiny_mce(){
	global $jquery,$ct_config;
	


$mcestyles = "{$ct_config['blog_path']}style/style_tinymce.css";

$extraplugins = "";
if(isset($ct_config['tinymce']['plugins']) && is_array($ct_config['tinymce']['plugins']))
	$extraplugins = join(".",$ct_config['tinymce']['plugins']);

$jquery['function'] .= <<<END
	$('#bbcode').tinymce({
			debug: true,
			// Location of TinyMCE script
			script_url : '{$ct_config['blog_path']}inc/tinymce/tinymce.min.js',
			plugins : "{$extraplugins},preelementfix,ltpostlink,ltcode,labtrove,autolink,lists,spellchecker,pagebreak,layer,table,save,advlink,insertdatetime,media,searchreplace,preview,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,RDFaCE,image,link,emoticons,textcolor",
			theme : "modern",
			
			content_css : "{$ct_config['blog_path']}/style/post.css",
			
	        relative_urls : false,
	
			// Example content CSS (should be your site CSS)
			//content_css : "{$mcestyles}",
	});
END;

}

?>
