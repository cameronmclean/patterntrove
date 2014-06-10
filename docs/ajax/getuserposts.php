<?php

include("../../lib/default_config.php");

$pagesize = 10;

if(!isset($_SESSION['user_name']) && !$_SESSION['user_name']){
	exit();
}

if(!isset($_REQUEST['page']) || !(int)$_REQUEST['page']){
	$page = 1;
}else{
	$page = (int)$_REQUEST['page'];
}
$pagestart = ($page-1)*$pagesize;
$pagestop = $page*$pagesize;

	$sql  = "SELECT  bit_id as uid ,bit_id  ,  bit_user ,  bit_title ,  bit_content , ".db_timestamp("bit_datestamp")." AS datetime, ".db_timestamp( "bit_timestamp" )." AS timestamp, blog_blogs.blog_zone, blog_blogs.blog_name,blog_blogs.blog_sname,blog_blogs.blog_id
FROM  blog_bits
INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id 
WHERE bit_edit = 0 AND blog_user LIKE '{$_SESSION['user_name']}'
ORDER BY bit_datestamp DESC ";

	$tresult = runQuery($sql,'Fetch Users Drafts');
	$count = 0;

	if(!($totnumber = db_get_number_of_rows($tresult))){
		$dashitem .= "You have none";
	}else{
	$totpages = ceil( $totnumber / $pagesize);
		
	$dashitem .= "<ul>";	
	while($row = db_get_next_row($tresult)){
			if($count < $pagestart){
				$count++;	continue;
			}
			if($count >= $pagestop){
				break;
			}

		$row['url'] = render_blog_link($row['bit_id'],1);
		$row['blog_url'] = render_link($row['blog_sname']);
		if(!strlen($row['bit_title'])) $row['bit_title'] = "(No Title)";
			if($row['btype']=='comment') $row['url'].= "#".$row['uid'];
		$dashitem .= "<li><a href=\"{$row['url']}\">{$row['bit_title']}</a>";
		$dashitem .= "<span class=\"timestampComment\"> ".date("jS F Y @ H:i",$row['timestamp'])." in <a href=\"{$row['blog_url']}\" >{$row['blog_name']}</a></span> </li>";
	


		

			$count++;
		}
	$dashitem .= "</ul>";
	
	if(1!=$totpages){
	
	$dashitem .= "<div style=\"text-align: center;\">";
	if($page!=1){
		$dashitem .= "<a href=\"#\" onclick=\"\$('#userrecenposts').load('ajax/getuserposts.php?page=".($page-1)."');return false;\">&lt;</a> ";
		}else{ 	$dashitem .= "&nbsp;&nbsp;";
	}
	 $dashitem .= "Page $page of $totpages";
	if($page!=$totpages){
		$dashitem .= " <a href=\"#\" onclick=\"\$('#userrecenposts').load('ajax/getuserposts.php?page=".($page+1)."');return false;\">&gt;</a>";
			}else{ 	$dashitem .= "&nbsp;&nbsp;";
	} 
	
	$dashitem .= " </div>";
	}
	
	}



echo $dashitem;	
?>
