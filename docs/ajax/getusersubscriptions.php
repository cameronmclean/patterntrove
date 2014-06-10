<?php

include("../../lib/default_config.php");

if(!isset($_SESSION['user_name']) && !$_SESSION['user_name']){
	exit();
}

$pagesize = 10;

if(!isset($_REQUEST['page']) || !(int)$_REQUEST['page']){
	$page = 1;
}else{
	$page = (int)$_REQUEST['page'];
}

$pagestart = ($page-1)*$pagesize;
$pagestop = $page*$pagesize;

$rcount = 0;
$sql = "SELECT * FROM blog_sub WHERE sub_username LIKE '{$_SESSION['user_name']}';";
$result = runQuery($sql,'Blogs');

if(!db_get_number_of_rows($result)){
	$dashitem .= "You have no subscriptions yet,";
}else{
	$bits = array();	
	
	$sql  = "SELECT  bit_id as uid ,bit_id  ,  bit_user ,  bit_title ,  bit_content , ".db_timestamp("bit_datestamp")." AS datetime, blog_blogs.blog_zone, blog_blogs.blog_name,blog_blogs.blog_sname,blog_blogs.blog_id
FROM  blog_bits
INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id 
INNER JOIN  blog_sub ON  blog_bits.bit_blog =  blog_sub.sub_blog 
WHERE bit_edit = 0 AND sub_username LIKE '{$_SESSION['user_name']}'
ORDER BY bit_datestamp DESC ";
	$tresult = runQuery($sql,'Fetch Page Groups');
	$count = 0;
	while($row = db_get_next_row($tresult)){
		if(checkzone($row['blog_zone'],1,$row['blog_id'])){
			$bits[$row['datetime']] = $row;
			$rcount++;
		}
	}

	$sql = "SELECT  blog_com.com_id as uid,  blog_bits.bit_id ,  blog_com.com_user AS  bit_user ,  blog_com.com_title AS  bit_title ,  blog_com.com_cont AS  bit_content , ".db_timestamp( "blog_com.com_datetime" )." AS datetime , 'comment' AS btype , blog_com.com_edit, blog_blogs.blog_zone, blog_blogs.blog_name,blog_blogs.blog_sname,blog_blogs.blog_id
FROM  blog_bits 
INNER JOIN  blog_com ON  blog_bits.bit_id =  blog_com.com_bit 
INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id
INNER JOIN  blog_sub ON  blog_bits.bit_blog =  blog_sub.sub_blog 
WHERE bit_edit = 0 AND sub_username LIKE '{$_SESSION['user_name']}' AND blog_com.com_edit = 0
ORDER BY  com_datetime DESC ";	

	$tresult = runQuery($sql,'Fetch Page Groups');
	$count = 0;
	while($row = db_get_next_row($tresult)){
		if(checkzone($row['blog_zone'],1,$row['blog_id'])){
			if($row['datetime'] > 1){
				$row['bit_title'] = "Comment: ".$row['bit_title'];
				$bits[$row['datetime']] = $row;
			}
			$rcount++;
	}
	}

	$totpages = ceil( $rcount / $pagesize);
	

	krsort($bits);
	$dashitem .= "<ul>";
	$count = 0;
	foreach($bits as $row){
		
			if($count < $pagestart){
				$count++;	continue;
			}
			if($count >= $pagestop){
				break;
			}
		
	$row['url'] = render_blog_link($row['bit_id'],1);
	$row['blog_url'] = render_link($row['blog_sname']);
		if($row['btype']=='comment') $row['url'].= "#".$row['uid'];
	$dashitem .= "<li><a href=\"{$row['url']}\">{$row['bit_title']}</a> by <a href=\"user/{$row['bit_user']}\">".get_user_info($row['bit_user'],'name')."</a> ";
	$dashitem .= "<span class=\"timestampComment\">".date("jS F Y @ H:i",$row['datetime'])." in <a href=\"{$row['blog_url']}\" >{$row['blog_name']}</a></span></li>";
	$count++;
		

	}

	$dashitem .= "</ul>";

	$auth_uid = array('uid'=>$_SESSION['user_uid'], 'subscription' => $_SESSION['user_name']);
	$dashitem .= "<div style=\"float: right;\" class=\"rss_note\">This as a <a href=\"".render_link('feeds',$auth_uid)."\">RSS Feed</a> (<a href=\"".render_link('feeds',$auth_uid)."?withcomments\">With Comments</a>)</div>";


	if(1!=$totpages){
	
	$dashitem .= "<div style=\"text-align: center;\">";
	if($page!=1){
		$dashitem .= "<a href=\"#\" onclick=\"\$('#usersubscriptions').load('ajax/getusersubscriptions.php?page=".($page-1)."');return false;\">&lt;</a> ";
		}else{ 	$dashitem .= "&nbsp;&nbsp;";
	}
	 $dashitem .= "Page $page of $totpages";
	if($page!=$totpages){
		$dashitem .= " <a href=\"#\" onclick=\"\$('#usersubscriptions').load('ajax/getusersubscriptions.php?page=".($page+1)."');return false;\">&gt;</a>";
			}else{ 	$dashitem .= "&nbsp;&nbsp;";
	} 
	
	$dashitem .= " </div>";
	}else{
			$dashitem .= "<div style=\"text-align: center;\">&nbsp;</div>";
	}
	
	

	



}


echo $dashitem;
?>
