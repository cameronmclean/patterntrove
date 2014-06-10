<?php
include("../lib/default_config.php");


$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
$body = "";

include("style/{$ct_config['blog_style']}/blogstyle.php");

if(!is_set_not_empty('user_name', $_SESSION) ){
	newMsg("You need to be logged in", "error");
	header("Location: ".$ct_config['blog_path']);
	exit();
}else{
	
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

	//Users Blogs
	if($_SESSION['user_admin']>0){ //Needs to be validated user.
		$dashitem = "<div class=\" dialog dashboard_item\">";
		$dashitem .= "<h2>Your Notebooks</h2>";
		$sql = "SELECT * FROM blog_blogs WHERE blog_user LIKE '{$_SESSION['user_name']}' AND blog_del != 1;";
		$dashitem .= "<UL>";
			$result = runQuery($sql,'Blogs');
	
				while($rowb = db_get_next_row($result)){
					if(checkzone($rowb['blog_zone'],0,$rowb['blog_id'])){
					if($rowb['blog_redirect'])
					$dashitem .= "\t\t\t<li><a href=\"{$rowb['blog_redirect']}\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>\n";
					else
					$dashitem .= "\t\t\t<li><a href=\"".render_link($rowb['blog_sname'])."\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>\n";
					}
				}
		$dashitem .= "</UL>";
		
		$dashitem .= "<div style=\"text-align:right;\"><a href=\"settings.php\" id=\"link_newpost\">New Notebook</a></div>";
		
		$dashitem .= "</div>";
	
		$dashboad_col1 .= $dashitem;
	}
	
	
	//Users Suscription Blogs
	if($_SESSION['user_admin']>0){ //Needs to be validated user.
		$dashitem = "<div class=\" dialog dashboard_item\">";
		$dashitem .= "<h2>Your Subscribed Notebooks</h2>";
		$sql = "SELECT * FROM blog_blogs INNER JOIN  blog_sub ON  blog_id =  blog_sub.sub_blog 
		WHERE sub_username LIKE '{$_SESSION['user_name']}' AND blog_del != 1;";
		$dashitem .= "<UL>";
			$result = runQuery($sql,'Blogs');
	
				while($rowb = db_get_next_row($result)){
					if(checkzone($rowb['blog_zone'],0,$rowb['blog_id'])){
					if($rowb['blog_redirect'])
					$dashitem .= "\t\t\t<li><a href=\"{$rowb['blog_redirect']}\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>\n";
					else
					$dashitem .= "\t\t\t<li><a href=\"".render_link($rowb['blog_sname'])."\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>\n";
					}
				}
		$dashitem .= "</UL>";
		$dashitem .= "<div style=\"text-align:right;\"><a href=\"user/{$_SESSION['user_name']}\" id=\"link_setting\">settings</a></div>";
		$dashitem .= "</div>";
		
		$dashboad_col1 .= $dashitem;
	}
	
	//users draft
	if($_SESSION['user_admin']>0){
		$dashitem = "<div class=\"dialog dashboard_item\">";
		$dashitem .= "<h2>Your Draft Posts</h2>";
		$sql  = "SELECT  bit_id as uid ,bit_id  ,  bit_user ,  bit_title ,  bit_content , ".db_timestamp( "bit_datestamp")." AS datetime, ".db_timestamp( "bit_timestamp" )." AS timestamp, blog_blogs.blog_zone, blog_blogs.blog_name,blog_blogs.blog_sname,blog_blogs.blog_id
	FROM  blog_bits
	INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id 
	WHERE bit_edit = -1 AND bit_user LIKE '{$_SESSION['user_name']}'
	ORDER BY bit_datestamp DESC";

		$tresult = runQuery($sql,'Fetch Users Drafts');
		$count = 0;
	
		if(!db_get_number_of_rows($tresult)){
			$dashitem .= "You have none";
		}else{
		$dashitem .= "<ul>";	
		while($row = db_get_next_row($tresult)){
	
			$row['url'] = render_blog_link($row['bit_id'],1);
			$row['blog_url'] = render_link($row['blog_sname']);
			if(!strlen($row['bit_title'])) $row['bit_title'] = "(No Title)";
				if($row['btype']=='comment') $row['url'].= "#".$row['uid'];
			$dashitem .= "<li><a href=\"{$row['url']}\">{$row['bit_title']}</a>";
			$dashitem .= "<span class=\"timestampComment\"> Last Edited: ".date("jS F Y @ H:i",$row['timestamp'])." in <a href=\"{$row['blog_url']}\" >{$row['blog_name']}</a></span> (<a href=\"{$row['url']}?action=edit\">edit post</a>) </li>";
			$count++;
		

			}
		$dashitem .= "</ul>";
		}
		$dashitem .= "</div>";
		$dashboad_col2 .= $dashitem;
	
		$dashitem = "<div class=\"dialog dashboard_item\">";
		$dashitem .= "<h2>Your Recent Posts</h2>";
		$dashitem .= "<div id=\"userrecenposts\"> Loading....</div> ";
		$jquery['function'] .= "\n $('#userrecenposts').load('ajax/getuserposts.php');\n";
		$dashitem .= "</div>";
		$dashboad_col2 .= $dashitem;
	
	
	}
	
	
	
	//subscriptions
	$dashitem = "<div class=\"dialog dashboard_item\">";
	$dashitem .= "<h2><small style=\"float:right;\"><a href=\"user/{$_SESSION['user_name']}\" id=\"link_setting\">settings</a></small>Your Subscriptions</h2>";
	$dashitem .= "<div id=\"usersubscriptions\"> Loading....</div> ";
	$jquery['function'] .= "\n $('#usersubscriptions').load('ajax/getusersubscriptions.php');\n";
	$dashitem .= "</div>";
	$dashboad_col2 .= $dashitem;

	
	
	


	
	$body .= <<<BODY


<div class="dashboard">
<h2>Dashboard</h2>
	<div class="dashboard_col1">
		{$dashboad_col1}
	</div>
	<div class="dashboard_col2">
		{$dashboad_col2}
	</div>
</div>

	
BODY;
	
	
}	


include('page.php');
?>
