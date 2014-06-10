<?php

include("../lib/default_config.php");


$jquery['fieldselection'] = true;

$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tiny_mce/tiny_mce_popup.js\" ></script>\n";
$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tiny_mce/plugins/ltpostlink/js/dialog.js\" ></script>\n";


checkblogconfig($_SESSION['blog_id']);

$pathinfo = $_SERVER['PATH_INFO'];
if($pathinfo{0} == '/'){
	$pathinfo = substr($pathinfo,1);
	$pathinfo = explode("/",$pathinfo);

	while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));

}

//Load Blog info
if($request['blog_id']){
	$blog_id = (int)$request['blog_id'];
}else if($request['bit_id']){
	$sql = "SELECT * FROM  blog_bits WHERE bit_id = ".(int)$request['bit_id'];
$result = runQuery($sql,'Get blog Id');
$rowb = db_get_next_row($result);
	$_SESSION['blog_id'] = (int)$rowb['bit_blog'];
	$blog_id = (int)$rowb['bit_blog'];
}else if($_REQUEST['blog_id']){
	$blog_id = (int)$_REQUEST['blog_id'];
}else if($_SESSION['link_blog_id']){
	$blog_id = $_SESSION['link_blog_id'];
}else if($_SESSION['blog_id']){
	$blog_id = $_SESSION['blog_id'];
}else{
	header('Location: /');
	exit();
}

	$_SESSION['link_blog_id'] = $blog_id;

$body .= "\t<div style=\"background: white; padding:10px;\">\n";


$body .= "\t<div style=\"float:right; width:120px; font-size: 80%;\">\n";
$body .= "\t<div class=\"infoSection\">Blog</div>\n";

$sql = "SELECT * FROM  blog_blogs INNER JOIN blog_types ON blog_type = type_id  WHERE blog_del = 0 AND blog_redirect = '' ORDER BY  blog_types.type_order ASC  ";
$result = runQuery($sql,'Blogs');
$body .= "<form name=blog_id action=\"".render_link(basename($_SERVER['SCRIPT_NAME']))."\">
			<select name=\"blog_id\" style=\"width:120px;\" onChange=\"document.blog_id.submit();\">";
		while($rowb = db_get_next_row($result)){
	if(($rowb['blog_zone']==0) || (checkzone($rowb['blog_zone'],0,$rowb['blog_id'])) || ($_SESSION['user_admin'] > 1)){
		$body .= "<option value={$rowb['blog_id']}";
		if($blog_id == $rowb['blog_id']){
			$body .= " selected";
		}
		$body .= "> {$rowb['blog_name']} </option>";
	}
	

}
$body .= "</select>";

//Archives
$body .= "\t<div class=\"infoSection\">Archives</div>\n";
$sql = "SELECT count(  bit_id ) AS count, month(  bit_datestamp ) AS month , year(  bit_datestamp ) AS year FROM  blog_bits WHERE bit_blog = '".$blog_id."'  AND bit_edit = 0 GROUP BY MONTH , year ORDER BY year DESC, MONTH DESC";
$tresult = runQuery($sql,'Fetch Page Groups');
    while($row = db_get_next_row($tresult)){
		$mtime = mktime(0,0,0,$row['month'],1,$row['year']);
		$body .= "\t\t<a href=\"".render_link(basename($_SERVER['SCRIPT_NAME']),array('blog_id' => $rowb['blog_id'], 'month'=> $mtime ))."\">".date('F Y',$mtime)."</a> <span class=\"num_posts\">(".$row['count'].")</span><br/>\n";
	}



//Sections
$body .= "\t<div class=\"infoSection\">Sections</div>\n";
$sql = "SELECT count(  bit_id ) AS count,bit_group FROM  blog_bits WHERE bit_blog = ".$blog_id." AND bit_edit = 0 GROUP BY bit_group ";
$tresult = runQuery($sql,'Fetch Page Groups');
    while($row = db_get_next_row($tresult)){
		$bloggroups[] = $row['bit_group'];
		$body .= "\t\t<a href=\"".render_link(basename($_SERVER['SCRIPT_NAME']),array('blog_id' => $rowb['blog_id'], 'group'=> $row['bit_group']))."\">".$row['bit_group']."</a> <span class=\"num_posts\">(".$row['count'].")</span><br/>\n";
	}


$body .= meta_meta($blog_id ,basename($_SERVER['SCRIPT_NAME']));



$body .= "<br/></div>";


	$body .= "\t\t<div class=\"postTitle\">Link to a Post</div><br/>\n";


$sql = "SELECT  bit_id ,  bit_user ,  bit_title ,  bit_content ,  bit_meta ,  bit_datestamp ,  bit_timestamp ,  bit_group ,  bit_blog ,  bit_edit , bit_edituser, bit_editwhy , ".db_timestamp( "bit_datestamp" )." AS datetime , ".db_timestamp( "bit_timestamp" )." AS timestamp, bit_md5 
FROM  blog_bits 
 \n";

if($request['group']){
$sqlb .= "WHERE bit_group = '".$request['group']."'   AND bit_blog = ".$blog_id ;
}else if($request['month']){
$sqlb .= "WHERE bit_datestamp > ".db_from_timestamp($request['month'])." AND  bit_datestamp < ".db_from_timestamp( strtotime("+1 month",$request['month']) )." AND bit_blog = ".$blog_id ;
}else if($request['meta']){
$sqlb .= "WHERE  bit_meta LIKE '%<meta>%<".$request['meta'].">".$request['value']."</".$request['meta'].">%</meta>%' AND bit_blog = ".$blog_id ;
}else{
$sqlb .= "WHERE bit_blog = ".$blog_id;
}
$sqlb .= " AND bit_edit = 0 ";

$sql .= $sqlb;


$sql .= "\nORDER BY  bit_datestamp DESC ";

$tresult = runQuery($sql,'Fetch Page Groups');
    
    while($row = db_get_next_row($tresult)){

	$body .= "<li><a href=\"javascript: InsertDialog.insert('{$row['bit_id']}', '".addslashes($row['bit_title'])."'); window.close();\">{$row['bit_title']}</a><br/>";
}

$body .= "<div class=\"clear\"></div>";
$body .= "</div>";


$minipage = 1;
include('page.php');
?>
