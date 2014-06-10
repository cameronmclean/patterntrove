<?php

include("../lib/default_config.php");

//echo $pathinfo = $_REQUEST['uri'];
$pathinfo = $_SERVER['LABTROVE_REQUEST_PATH'];

$ext = pathinfo($pathinfo , PATHINFO_EXTENSION);
$filename = pathinfo($pathinfo , PATHINFO_BASENAME);

if((isset($_REQUEST['uid']) && $_REQUEST['uid'] != false) && ($_REQUEST['uid']!=$_SESSION['user_uid'])){
	login_with_uid($_REQUEST['uid']);
}


if($pathinfo){
	$pathinfo = explode("/",$pathinfo);
	$request['blog_sname'] = array_shift($pathinfo);
	if(count($pathinfo) > 0 && (((string)$pathinfo[0]) === ((string)(int)$pathinfo[0])))
		$request['bit_id'] = array_shift($pathinfo);
	while($request[array_shift($pathinfo)] = addslashes(urldecode(array_shift($pathinfo))));
}
			

if(!isset($request['page'])){
	$request['page'] = 1;
}

if(isset($_REQUEST['aaction']) && $_REQUEST['aaction'] != false){
	$_REQUEST['action']	= $_REQUEST['aaction'];
}


//Load Blog info
if($request['blog_sname']){

	$sql = "SELECT * FROM  blog_blogs WHERE blog_sname = '{$request['blog_sname']}'";
	$result = runQuery($sql,'Blogs');
	$blog = db_get_next_row($result);
	$blog_id = $blog['blog_id'];
	$title = $blog['blog_name'];
	$desc = $blog['blog_desc'];
	$title_url = render_link($blog['blog_sname']);

}
if(!$blog_id){
	set_http_error(404, $_REQUEST['uri']);
	exit();
}

checkblogconfig($blog_id);

include("style/{$ct_config['blog_style']}/blogstyle.php");

$_SESSION['blog_id'] = $blog_id;


$user_can_edit = 0;
if($_SESSION['user_admin']==0){
$user_can_post = 0;
}else{
$user_can_post = 1;
}

if(!checkzone($blog['blog_zone'],0,$blog['blog_id']) || !checkzone($ct_config['blog_zone'])){
	newMsg("Forbidden: You are not allowed to access this blog!", "error");
	$_SESSION['labtrove']['turl'] = $ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'];
	header("Location: {$ct_config['blog_path']}");
	exit();
}





include('../lib/proc_blog.php');

if($_SESSION['user_uid']){
	$auth_uid = array('uid'=>$_SESSION['user_uid']);
}
$rss_feed[] = array("type" => "application/rss+xml", "title" => "".strip_tags($title).": RSS 2.0", "url" => render_link('feeds/'.$blog['blog_sname'],$auth_uid));
$rss_feed[] = array("type" => "application/rss+xml", "title" => "".strip_tags($title)." with comments: RSS 2.0", "url" => render_link('feeds/'.$blog['blog_sname'],$auth_uid)."?withcomments");





$sql = "SELECT  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit , bit_edituser, bit_editwhy , ".db_timestamp( "bit_datestamp" )." AS datetime, sum(  case blog_com.com_edit when 0 then 1 else 0 end ) AS coments , ".db_timestamp( "bit_timestamp" )." AS timestamp, bit_md5 , bit_cache, bit_rid, bit_edituser
FROM  blog_bits 
LEFT OUTER JOIN  blog_com ON  blog_bits.bit_id =  blog_com.com_bit \n";

$sql = "SELECT  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit , bit_edituser, bit_editwhy , ".db_timestamp( "bit_datestamp" )." AS datetime,  ".db_timestamp( "bit_timestamp" )." AS timestamp, bit_md5 , bit_cache, bit_rid, bit_edituser
FROM  blog_bits \n";

$sql_order_by = "bit_datestamp DESC, bit_timestamp DESC";

$postaftersingle = false;

if(!isset($sqlb)) $sqlb = NULL;
if((isset($request['bit_id']) && $request['bit_id'] != false) && (isset($_REQUEST['revision']) && $_REQUEST['revision'])){
	$sqlb .= "WHERE bit_id = ".(int)$request['bit_id']." AND bit_rid = ".(int)$_REQUEST['revision'];
	$postaftersingle = true;
}elseif((isset($request['bit_id']) && $request['bit_id'] != false) && isset($_REQUEST['revisions'])){
	$sqlb .= "WHERE bit_id = ".(int)$request['bit_id'];
	$limitt = 0;
	$postaftersingle = true;
}elseif(isset($request['bit_id']) && $request['bit_id'] != false){
	$sqlb .= "WHERE bit_id = ".(int)$request['bit_id'];
	$sqlb .= " AND bit_edit < 1 ";
	$limitt = 1;
	$sql_order_by = "bit_edit DESC, {$sql_order_by}";
	$postaftersingle = true;
}else if(isset($request['byuser']) && $request['byuser'] != false){
$sqlb .= "WHERE bit_user = '".$request['byuser']."'";
$sqlb .= " AND bit_edit = 0 ";
$pagetitle = "Group: {$request['group']} - {$title}";
}else if(isset($request['group']) && $request['group'] != false){
$sqlb .= "WHERE bit_group = '".$request['group']."'";
$sqlb .= " AND bit_edit = 0 ";
$pagetitle = "Group: {$request['group']} - {$title}";
}else if(isset($request['month']) && $request['month'] != false){
$sqlb .= "WHERE bit_datestamp > ".db_from_timestamp((int)$request['month'])." AND  bit_datestamp < ".db_from_timestamp( strtotime("+1 month",(int)$request['month']) );
$sqlb .= " AND bit_edit = 0 ";
$pagetitle = date("F Y",$request['month'])." - {$title}";
}else if(isset($request['meta']) && $request['meta'] != false){
$sqlb .= "WHERE  (bit_meta LIKE '%<meta>%<".$request['meta'].">".$request['value']."</".$request['meta'].">%</meta>%' 
OR bit_meta LIKE '%<meta>%<".$request['meta'].">".$request['value'].";%</".$request['meta'].">%</meta>%' 
OR bit_meta LIKE '%<meta>%<".$request['meta'].">%;".$request['value'].";%</".$request['meta'].">%</meta>%' 
OR bit_meta LIKE '%<meta>%<".$request['meta'].">%;".$request['value']."</".$request['meta'].">%</meta>%' 
)";
$pagetitle = ucwords(str_replace("_"," ",strtolower($request['meta']))).": {$request['value']} - {$title}";
$sqlb .= " AND bit_edit = 0 ";
}else{
$sqlb .= "WHERE bit_edit = 0 ";
if(isset($ct_config['blog_hide_meta']) && $ct_config['blog_hide_meta'] != false){
foreach($ct_config['blog_hide_meta'] as $key => $value)
	$sqlb .= " AND bit_meta NOT LIKE '%<$key>$value</$key>%' ";
$sqlb .= " AND bit_edit = 0 ";
}
if(isset($ct_config['blog_hide_section']) && $ct_config['blog_hide_section'] != false){
foreach($ct_config['blog_hide_section'] as $value)
	$sqlb .= " AND bit_group NOT LIKE '%$value%' ";
$sqlb .= " AND bit_edit = 0 ";
}
}

//Make sure its from that blog;
$sqlb.=" AND bit_blog = ".$blog['blog_id'];




$sql_where = $sqlb;
$sql .= $sqlb;
//get count
$sqlb = "SELECT count(  bit_id ) as bcount FROM  blog_bits ".$sqlb;




switch($filename){
case "timeline.html":
	include("timeline.php");
break;
case "exhibit.html":
	include("exhibit.php");
break;
}

if($ext && $ext != "html"){
	if(hooks_run_export_post($ext, $sql))
		exit();
	
	if(file_exists("export/{$ext}.php"))
		include("export/{$ext}.php");
}

if(!isset($body)) $body = NULL;
//Side bar
if(isset($_REQUEST['postonly']) && $_REQUEST['postonly'] != false){
	$minipage = true;
}else{
	$contClass = "containerPost";
	$body .= makesidebar();
}

if(isset($errmsg) && $errmsg != false)
$body .= blog_style_error($errmsg);

$tresult = runQuery($sqlb,'Fetch Page Count');
$row = db_get_next_row($tresult);
$countblog = $row['bcount'];
if(!isset($limitt)){
if($countblog > $ct_config['no_blogs_page']){
	if($request['page']){
		//$limitt = $ct_config['no_blogs_page']*($request['page']-1)." OFFSET ".$ct_config['no_blogs_page'];
		//When you use offset you need to swap the numbers round, you need to check with postgres
		$limitt = $ct_config['no_blogs_page']." OFFSET ".$ct_config['no_blogs_page']*($request['page']-1);
		}else{
		$limitt = $ct_config['no_blogs_page'];
	}
}else{
	$limitt = $ct_config['no_blogs_page'];
}
}

if($limitt){
	$limittext = "Limit $limitt";
}

$sql .= "\n ORDER BY  {$sql_order_by}   $limittext ";
//$sql .= "\nGROUP BY  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit ,  bit_editwhy, bit_md5,bit_edituser
//ORDER BY  bit_datestamp DESC, bit_timestamp DESC Limit $limitt";
$postnumb = 0;

$tresult = runQuery($sql,'Fetch Page Groups');
$noofposts = db_get_number_of_rows($tresult);


if(	$postaftersingle && !$noofposts){
	set_http_error(404, $_REQUEST['uri']);
	exit();
}

if(!isset($pagebuts)) $pagebuts = NULL;
if(($countblog/$ct_config['no_blogs_page'])>1 && !isset($_REQUEST['postonly'])){
	$pagebuts = "\t<div class=\"{$contClass}\">
			<div class=\"postText\">";
	$request['page']=$request['page']-1;
	if($request['page'] > 0){
	$pagebuts .= "	<div style=\"float:left\"><a href=\"".render_link($blog['blog_sname'],$request)."\"> << Newer Posts</a></div>";
	}
	$request['page']=$request['page'] +2;
	if(($request['page']-1)<($countblog/$ct_config['no_blogs_page'])){
	$pagebuts .= "	<div style=\"float:right\"><a href=\"".render_link($blog['blog_sname'],$request)."\"> Older Posts >></a></div>";
	}
	$pagebuts .= "	&nbsp;	</div>
	</div> \n";
}elseif((isset($request['bit_id']) && $request['bit_id'] != false) && (!isset($_REQUEST['postonly']) || !$_REQUEST['postonly'])){
	$pagebuts = "\t<div class=\"{$contClass}\">
			<div class=\"postText\">";
			$sql = "SELECT bit_id FROM blog_bits WHERE bit_id >'{$request['bit_id']}' AND bit_blog ={$blog['blog_id']} AND bit_edit =0  ORDER BY bit_id ASC";
			$pageres = runQuery($sql,'Fetch Page Groups');
			if($pageline = db_get_next_row($pageres))
				$pagebuts .= "	<div style=\"float:left\"><a href=\"".render_blog_link($pageline['bit_id'],true)."\"> << Next Post</a></div>";
			$sql = "SELECT bit_id FROM blog_bits WHERE bit_id < '{$request['bit_id']}' AND bit_blog = '{$blog['blog_id']}' AND bit_edit =0  ORDER BY bit_id DESC " . db_limit_1();
			$pageres = runQuery($sql,'Fetch Page Groups');
			if($pageline = db_get_next_row($pageres))
			$pagebuts .= "	<div style=\"float:right\"><a href=\"".render_blog_link($pageline['bit_id'],true)."\">Previous Post >></a></div>";
			
		$pagebuts .= "	&nbsp;	</div>
		</div> \n";
}

$body .= $pagebuts;



while($post = db_get_next_row($tresult)){



	$postnumb++;

	$post['coments'] = get_comment_count($post['bit_id']);

	$blogpost = NULL;	
	$blogpost['title'] = $post['bit_title'];
	$metadata = readxml($post['bit_meta']);
	$blogpost['url'] = render_blog_link($post['bit_id'],true);
	$blogpost['id'] = $post['bit_rid'];
	
	if(isset($_REQUEST['revisions'])){

		if($postnumb==1)
			$pagetitle = "Revisions - {$post['bit_title']} - {$title}";

		if($post['bit_edit']<0){
			if(($_SESSION['user_name']==$post['bit_user']) || $_SESSION['user_admin'] > 1 || $user_can_edit){
				
			}else{
				$postno++;
				continue;
			}
		}
		
		
		$editinfo = geteditinfo($post['bit_rid']);
		if(!$editinfo['bit_edituser']){
			$editinfo['bit_edituser'] = $post['bit_edituser'];
			$editinfo['bit_editwhy'] = "First Post";
		}
		
		
		$blogpost['title'] = "Rev ".($noofposts-($postno++)).": ".$post['bit_title'];
		$blogpost['url'] .= "?revision={$post['bit_rid']}";

		
		switch($post['bit_edit']){
			case -1:
				$blogpost['title'] .= " (Draft)";
				$blogpost['date'] = "Last Edited: ".date("jS F Y @ H:i",$post['timestamp'])."<br/>";
			break;
			case -2;
				$blogpost['title'] .= " (Draft Withdrawn)";
				$blogpost['date'] = "Withdrawn: ".date("jS F Y @ H:i",$post['timestamp'])."<br/>";
				$blogpost['date'] .= "Key:{$post['bit_md5']}";
			break;
			default:
				$blogpost['date'] = date("jS F Y @ H:i",$post['timestamp'])." Edited by ".$editinfo['bit_edituser']."<br/>";
				$blogpost['date'] .= "Edit Reason: ".$editinfo['bit_editwhy']."<br/>";
				$blogpost['date'] .= "Key:{$post['bit_md5']}";
			break;
		}
		

	}else{

		if($postnumb==1 && (isset($request['bit_id'])&& $request['bit_id'] != false))
			$pagetitle = "{$post['bit_title']} - {$title}";
	
	if($post['bit_edit']>0){
		$blogpost['infohead'] = "This is a previous version of the this post. To view the latest <a href=\"{$blogpost['url']}\">click here.</a> or to view all revisions  <a href=\"{$blogpost['url']}?revisions\">click here.</a>";
		$editinfo = geteditinfo($post['bit_rid']);
	}elseif(in_array($post['bit_edit'],array(-1,-2))){
		
		//Check draft is viewable by the owner
		if($_SESSION['user_admin']<2 && $_SESSION['user_name']!=$post['bit_user']){
				newMsg("Forbidden: You are not allowed to access this blog!", "error");
				$_SESSION['labtrove']['turl'] = $ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'];
				header("Location: {$ct_config['blog_path']}");
				exit();
		}
		switch($post['bit_edit']){
			case -1;
				$blogpost['infohead'] = "This is a draft post and is only visible by you until it is published.";
			break;
			case -2;
				$blogpost['infohead'] = "This is a draft post which has been withdrawn.";
			break;
		}
	}

	$blogpost['date'] = date("jS F Y @ H:i",$post['datetime']);
	if(!isset($blogpost['post'])) $blogpost['post'] = NULL;	
	if((!isset($ct_config['blog_hide_content']) || $ct_config['blog_hide_content'] == false ) || $request['bit_id']){
		if(isset($_REQUEST['action']) && $_REQUEST['action']=="viewsrc"){
			$blogpost['post'] = "<textarea class=\"viewSource\" readonly>{$post['bit_content']}</textarea>";
		}elseif(!$post['bit_cache'] || (isset($_REQUEST['nocache'])&& $_REQUEST['nocache'] != false)){
				$blogpost['post'] .= makepostcache($post);
			}else{
			$blogpost['post'] .= $post['bit_cache'];
			}

		if((!isset($request['bit_id']) || $request['bit_id'] == false) && ($cutoff = stripos($blogpost['post'],"<!--page-->"))){
                $blogpost['post'] = substr($blogpost['post'],0,$cutoff);
                $blogpost['post'] .= "<div style=\"float:right\"><a href=\"{$blogpost['url']}\">Read in full</a></div>";
        }
		
		if((isset($metadata['METADATA']['DATA']) && $metadata['METADATA']['DATA'] != false)){
		 	if(!(isset($request['bit_id']) && $request['bit_id'] != false))
				$blogpost['data_hideable'] = true;
			$blogpost['data_title'] = "Attached Files"; 
			$datas = NULL;
			$datas = split(",",$metadata['METADATA']['DATA']);
			foreach($datas as $bit){
				if(is_int($bit))
					$test = checkOverlay($bit);
				if(!isset($blogpost['data'])){ $blogpost['data'] = ''; }
				if ( $test ) { $blogpost['data'] .= "<span class=comment>"; }
				$blogpost['data'] .= getdata($bit);
				if ( $test ) { $blogpost['data'] .= "</span>"; }
			}
			$blogpost['data'] .= "<div style=\"clear:left;\"></div>";
		}
		
		$insuser = NULL;
		if(($_SESSION['user_name']==$post['bit_user']) || $_SESSION['user_admin'] > 1 || $user_can_edit){
			$insuser .= "<a href=\"".$blogpost['url']."?action=edit\" onclick=\"\">Edit Post</a> | ";
		}else{
			$insuser .= "<a href=\"".$blogpost['url']."?action=viewsrc\">View Source</a> | ";
		}
	
		if($_SESSION['user_name'] && $post['bit_group'] == "Templates"){
			$insuser .= "<a href=\"".render_link("template.php?bit_id={$post['bit_id']}")."\">Use Template</a> | ";
		}
		if(!isset($blogpost['footer'])) $blogpost['footer'] = NULL;	
		$blogpost['footer'] .= "\t\t\t<a href=\"".render_link('', array('user' => $post['bit_user']))."\">".get_user_info($post['bit_user'],'name')."</a> | $insuser <a href=\"".render_link($blog['blog_sname'],array('blog_id' => $blog['blog_id'], 'group'=> $post['bit_group']))."\">".$post['bit_group']."</a> | <a class=\"gray\" href=\"".$blogpost['url']."#com\">Comments (".$post['coments'].")</a>\n";
		if(isset($_COOKIE['showkeys'])){
			$uri = "{$ct_config['blog_url']}uri/". dechex(getbituri($post['bit_id']));
			$blogpost['footer'] .= "<br />Uri:<a href=\"$uri\">$uri</a><br/>Key:{$post['bit_md5']} <br /> Last Updated:".date("jS F Y @ H:i",$post['timestamp']);
		}
		}
	
	}
	$uri_parts = explode("/", $_GET['uri']);
        if (in_array('chemspider', $ct_config['plugins']) && !empty($blogpost['id']) && sizeof($uri_parts) > 1 && is_numeric($uri_parts[1])) {
	        $blogpost['post'] .= chemspider_generate_post_info_box($post['bit_rid']);
	}

	$body .= blog_style_post($blogpost);
}

if(isset($request['bit_id']) && $request['bit_id'] != false){

	$body .= "<div class=\"containerComments\">";

	$sql = "SELECT *,".db_timestamp( "com_datetime" )." AS datetime FROM  blog_com 
				WHERE  com_bit = ".(int)$request['bit_id']."   AND com_edit =0 ORDER BY com_datetime ASC";

	$tresult = runQuery($sql,'Fetch Page Comments');
	
	if( db_get_number_of_rows($tresult)) {
		$body .= "<div class=\"infoSection\"><a name=\"com\"></a>Comments</div>\n";
	    while($comment = db_get_next_row($tresult)){


			if(isset($_REQUEST['action']) && $_REQUEST['action']=='editcom' && (($_SESSION['user_name']==$comment['com_user']) || $_SESSION['user_admin'] > 1 || $user_can_edit) && $_REQUEST['comid'] == $comment['com_id']  ){
				$jquery['ui'] = true;
				$jquery['markitup'] = true;
				$jquery['validate'] = true;
				$jquery['function'] .= "$('#commentTextarea').markItUp(mySettings);\n";
				$jquery['function'] .= "$('#commentForm').validate();\n";
				//".buildbuttons()."
				$body .= "\t<a name=\"".$comment['com_id']."\"></a>
<form action=\"".$blogpost['url']."&comid={$comment['com_id']}\" name=\"blog\" id=\"commentForm\" method=\"post\">
Title: <input type=\"text\" name=\"comment_title\" class=\"comment_title\" size=\"80\" value=\"".$comment['com_title']."\"/><br/>
<span class=\"timestampComment\">".date("jS F Y @ H:i",$comment['datetime'])."</span>
<br/>Text: <br/><textarea name=\"text\" id=\"commentTextarea\" class=\"commentTextarea\" cols=\"60\" rows=\"7\" style=\"height:100px;\">".$comment['com_cont']."</textarea><br/>Reason For Edit <input type=\"text\" class=\"required\" name=\"editwhy\" value=\"\"/><br /><br/>
<input type=\"submit\" name=\"action_comedit\" value=\"Save\"/>
<input type=\"button\" name=\"action_comreset\" value=\"Reset\"/ onclick=\"javascript:form.reset();\" style=\"float: right;\"></form>
";
			}else{
				$insuser = "";
				if(($_SESSION['user_name']==$comment['com_user']) || $_SESSION['user_admin'] > 1 || $user_can_edit){
					$insuser = "(<a href=\"".$blogpost['url']."&action=editcom&comid=".$comment['com_id']."#".$comment['com_id']."\">Edit Comment</a>) ";
				}	
	
				$comment['com_url'] = $blogpost['url'].'#'.$comment['com_id'];
				$comment['com_user'] = "<span><a href=\"".render_link('',array('user' => $comment['com_user']))."\">".get_user_info($comment['com_user'],'name')."</a> $insuser</span><br/>\n";
				$comment['com_rdate'] = date("jS F Y @ H:i",$comment['datetime']);
				$comment['com_html'] = bbcode($comment['com_cont']);

				$body .= blog_style_comment(&$comment);

			}
		}

	}

	$body.="<div class=\"commentInfo\">";
	if( $_SESSION['user_name']
	  && (!isset($_REQUEST['action']) || $_REQUEST['action']!='editcom')
	  && (!isset($_REQUEST['postonly']) || !$_REQUEST['postonly']) )
	{
		if(!isset($_REQUEST['comment_title']) || !$_REQUEST['comment_title']){
			$commentt = "Re: ".$blogpost['title'];
		}else{
			$commentt = stripslashes($_REQUEST['comment_title']);
		}

		
		$jquery['markitup'] = true;
		$jquery['validate'] = true;
		$jquery['function'] .= "$('#commentTextarea').markItUp(mySettings);\n";
		$jquery['function'] .= "$('#comment_form').validate();\n";
		
		$text = isset($_REQUEST['text']) ? stripslashes($_REQUEST['text']) : '';
		$body .= "\t<div class=\"infoSection\">Add comment to Post</div>\n
			<form action=\"".$blogpost['url']."\" name=\"blog\" method=\"post\" id=\"comment_form\">
			Title<span class=\"formreq\">*</span>  <br/><input type=\"text\" name=\"comment_title\" class=\"comment_title required\" size=\"80\" value=\"".$commentt."\"/><br/>
			<br/>Text<span class=\"formreq\">*</span>  <br/><textarea name=\"text\" class=\"commentTextarea required\"  id=\"commentTextarea\" cols=\"60\" rows=\"7\" style=\"height:100px;\">".$text."</textarea><br/><br/>
			<input type=\"submit\" name=\"action_com\" value=\"Submit\"/>
			<input type=\"button\" name=\"action_reset\" value=\"Reset\"/ style=\"float: right;\" onclick='javascript:form.reset()'></form>";

	}
	$body .= "</div>";
	$body .= "</div>";
}

$body .= $pagebuts;

//	$body .= "\t</div>\n";

include('page.php');
?>
