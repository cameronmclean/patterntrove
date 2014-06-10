<?php

include("../lib/default_config.php");

if(!isset($_REQUEST['allblogs']) && !isset($_REQUEST['allnotebooks']) && is_set_not_empty('user_name', $_SESSION)){
	header("Location: ".$ct_config['blog_path']."dashboard");
	exit();
}


$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
$body = "";


include("style/{$ct_config['blog_style']}/blogstyle.php");


if( is_set_not_empty('msg', $_REQUEST) )
	newMsg($_REQUEST['msg']);


if(!checkzone($ct_config['blog_zone']) ){

$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:blue;\">Please log in to view this trove.</div></div>";
include('page.php');
exit();
}

	$dashboad_col1 = $dashboad_col2 = "";
	
	
	
	//search
	$jquery['function'] .= <<<JQU
			$('.searchBox').focus(function()
			{
				var q = $(this).val();
				if(q=='Search All')	$(this).val('');
			});
			$('.searchBox').blur(function()
			{
				var q = $(this).val();
				if(q=='') $(this).val('Search All');
			});
JQU;
	
	$dashitem = <<<ITEM
	<div class="dialog dashboard_item">
		<form action="{$ct_config['blog_path']}search" method="get">
		<input class="searchBox" type="text" value="Search All" name="q"/></form>
	</div>
ITEM;


	$dashboad_col1 .= $dashitem;
	
	

//get Blogs Type
$sql = "SELECT * FROM  blog_types ORDER BY  blog_types.type_order ASC ";
$tresult = runQuery($sql,'Fetch Page Groups');
$dashitem = "<div class=\"dialog dashboard_item\">"; 
//$dashitem .= "<h2>All Blogs</h2>"; 
$lockhasbeenused=0;

    while($row = db_get_next_row($tresult)){
		$haspart = 0;
		$part = "\t\t<h3>".$row['type_name']."</h3>\n";
	
		$part .= "\t\t<span class=\"timestamp\"><small>".$row['type_desc']."</small></span><div class=\"postText\">\n<ul>";

		$sql = "SELECT * FROM  blog_blogs WHERE blog_type = ".$row['type_id']." AND blog_del != 1;";
		$result = runQuery($sql,'Blogs');
		
		while($rowb = db_get_next_row($result)){
				
				if(checkzone($rowb['blog_zone'],0,$rowb['blog_id'])){
					if($rowb['blog_redirect'])
						$part .= "\t\t\t<li><a href=\"{$rowb['blog_redirect']}\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>";
					else
						$part .= "\t\t\t<li><a href=\"".render_link($rowb['blog_sname'])."\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>";
					
					$blog_owner = db_get_user($rowb['blog_user']);
					$part .= " (".$blog_owner['user_fname'].")";
					if($rowb['blog_zone']!=0){
						$part .= " <img src=\"inc/lock.gif\" alt=\"Padlock icon\"/>"; 
						$lockhasbeenused=1;
					}
					$part .= "</li>\n";
					$haspart = 1;
				}
		}
		$part .= "</ul>\t\t\n\t</div>\n";

	
		if($haspart) $dashitem .= $part;
	}

	if( is_set_not_empty('user_uid', $_SESSION) ){
		$auth_uid = array('uid'=>$_SESSION['user_uid']);
	}else{ $auth_uid = NULL; }
	
$dashitem .= "<div style=\"float: right;\" class=\"rss_note\"> Posts on this server as a <a href=\"".render_link('feeds',$auth_uid)."\">RSS Feed</a> (<a href=\"".render_link('feeds',$auth_uid)."?withcomments\">With Comments</a>)</div><div class=\"clear\"></div>";

	
$dashitem .= "</div>";
if($lockhasbeenused)
	$dashitem .= "<div><img src=\"inc/lock.gif\" alt=\"Padlock icon\"/> Notebook protected by a security policy.  You may not be able to view this notebook if not logged in.</div>"; 


$dashboad_col2 .= $dashitem;

$body .= <<<BODY


	<div class="dashboard">
		<div class="dashboard_col1">
			{$dashboad_col1}
		</div>
		<div class="dashboard_col2">
			{$dashboad_col2}
		</div>
	</div>


BODY;
	

//set RSS!

$rss_feed[] = array("type" => "application/rss+xml", "title" => "".strip_tags($ct_config['blog_title']).": RSS 2.0", "url" => render_link('feeds',$auth_uid));
$rss_feed[] = array("type" => "application/rss+xml", "title" => "".strip_tags($ct_config['blog_title'])." with comments: RSS 2.0", "url" => render_link('feeds',$auth_uid)."?withcomments");

include('page.php');
?>