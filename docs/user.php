<?php


include("../lib/default_config.php");

if(isset($_REQUEST['uri']))
	$pathinfo = $_REQUEST['uri'];
else
$pathinfo = substr($_SERVER['LABTROVE_REQUEST_PATH'],5);

if($pathinfo){
	$pathinfo = explode("/",$pathinfo);
	$request['user'] = array_shift($pathinfo);
	while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));

}

$_REQUEST['uri'] = "user/".$_REQUEST['uri'];


checkblogconfig($_SESSION['blog_id']);

include("style/{$ct_config['blog_style']}/blogstyle.php");



if(!$request['user']){
	header("Location: {$ct_config['blog_path']}?msg=Forbidden!");
}

$title = get_user_info($request['user'],'name');
$title_url = render_link('',array('user' => $request['user']));

if($_REQUEST['save'] && ($_SESSION['user_name'] == $request['user'])){

	$sql = "SELECT * FROM  blog_users WHERE u_name = '{$_SESSION['user_name']}'";

	$result = runQuery($sql,'Blogs');

	if($_REQUEST['proflocate'])
		$_REQUEST['proflocate'] = 1;

	if(db_get_number_of_rows($result)){
		$sql = "UPDATE  blog_users SET  u_emailsub =  '".(int)$_REQUEST['emailset']."', u_sortsub =  '".(int)$_REQUEST['emailsort']."', u_proflocate =  '".(int)$_REQUEST['proflocate']."' WHERE blog_users.u_name =  '{$_SESSION['user_name']}' " . db_limit_1();

	}else{
		$sql = "INSERT INTO  blog_users ( u_name , u_emailsub , u_sortsub , u_proflocate ) VALUES ( '{$_SESSION['user_name']}',  '".(int)$_REQUEST['emailset']."',  '".(int)$_REQUEST['emailsort']."',  '".(int)$_REQUEST['proflocate']."');";
	}

	runQuery($sql,'Blogs');



	$sql = "DELETE FROM  blog_sub WHERE  blog_sub.sub_username =  '{$_SESSION['user_name']}'";
	
	runQuery($sql,'Blogs');
	if(isset($_REQUEST['blogs_sub'])){
	foreach($_REQUEST['blogs_sub'] as $key => $value){
		$sql = "INSERT INTO  blog_sub ( sub_username , sub_blog ) VALUES ( '{$_SESSION['user_name']}',  '".(int)$key."' );
"; 
		runQuery($sql,'Blogs');
	}
	}
}

$desc = "";

$body = "";
if(!checkzone($ct_config['blog_zone']) ){

header("Location: {$ct_config['blog_path']}?msg=Forbidden!");

}else{


if($_REQUEST['msg'])
$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: {$_REQUEST['msg']} </div></div>";




if($request['user'] == $_SESSION['user_name']){

$body .= "<div class=\"info\">
	<div class=\"infoSection\">Your Subscription Settings</div>";

$body .= "<form action=\"".render_link('',array('user' => $request['user']))."\" method=post>";

$body .= "<b>Blogs: </b><br />";

$sql = "SELECT * FROM  blog_blogs INNER JOIN blog_types ON blog_type = type_id LEFT OUTER JOIN  blog_sub ON  blog_id =  sub_blog AND sub_username =  '{$_SESSION['user_name']}' WHERE blog_del = 0 AND blog_redirect = '' ORDER BY  blog_types.type_order ASC  ";
$result = runQuery($sql,'Blogs');

while($rowb = db_get_next_row($result)){
	
	if(($rowb['blog_zone']==0) || (checkzone($rowb['blog_zone'],0,$rowb['blog_id'])) || ($_SESSION['user_admin'] > 1)){
		$body .= "<input type=\"checkbox\" name=\"blogs_sub[{$rowb['blog_id']}]\"";
		if($rowb['sub_blog']){
			$body .= " checked";
			$have_something = 1;
		}
		$body .= "> {$rowb['blog_name']} <br />";
	}
}	

$body .= "<br />";

$sql = "SELECT * FROM  blog_users WHERE u_name = '{$_SESSION['user_name']}'";

	$result = runQuery($sql,'Blogs');

	$rowb = @db_get_next_row($result);
if($ct_config['blog_enmsg']){
$body .= "<b>Email Settings: </b>";
$body .= "<select name=\"emailset\">";
foreach($config['blog_sub'] as $key => $value){	
$body .= "<option value=\"$key\"";
if($key == $rowb['u_emailsub']) $body .= " selected";
$body .= ">$value</option>";
}
$body .= "</select><br />";
/*
$body .= "<b>Email Sorting Settings: </b>";
$body .= "<select name=\"emailsort\">";
foreach($config['blog_sub_sort'] as $key => $value)	{	
$body .= "<option value=\"$key\"";
if($key == $rowb['u_sortsub']) $body .= " selected";
$body .= ">$value</option>";
}
$body .= "</select><br /><br />";
*/
}

//$body .= "<b>ProfLocate<sup>TM</sup> Follow </b>";
//$body .= "<input type=\"checkbox\" name=\"proflocate\"";
//if($rowb['u_proflocate']) $body .= " checked";
//$body .= ">";

$body .= "<input type=\"submit\" name=\"save\" value=\"Save\">";

$body .= "</form>";


$body .= "</div>";

}















if(isset($request['allcoms']) && $request['allcoms'] != 1){
	$body .= "\t<div class=\"containerPost\">\n";

	$body .= "\t\t<div class=\"postTitle\">Blog Posts</div>\n";

	$body .= "\t\t<span class=\"timestamp\"><small>".$row['type_desc']."</small></span><div class=\"postText\">\n<UL>";

		$sql = "SELECT bit_id ,  bit_rid ,  bit_user ,  bit_title ,  ".db_timestamp("bit_datestamp")." AS datetime ,  bit_blog, blog_name, blog_zone FROM  blog_bits INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id  WHERE  bit_user LIKE  '{$request['user']}' AND  bit_edit = 0  ORDER BY  bit_datestamp DESC ";
if($request['allposts']!=1){
	$limit = 10;
}
		$result = runQuery($sql,'Blogs');
			$bcount = 0;
			while($rowb = db_get_next_row($result)){
			if(checkzone($rowb['blog_zone'],0,$rowb['bit_blog'])){
				if($limit && ($bcount > $limit)) break;
				$body .= "\t\t\t<li style=\"margin-top:4px;margin-left:-10px;\">".render_blog_link($rowb['bit_id'])." from ".$rowb['blog_name']."<br />";	
				$body .= "\t\t\t<span class=\"timestampComment\">".date("jS F Y @ H:i",$rowb['datetime'])."</span>";
				$body .= "\n";
				$bcount++;
			}
			}


	$body .= "</UL>\t\t";
if($request['allposts']!=1)
	$body .= "\t\t\t<span style=\"float:right;\"><a href=\"".render_link('',array('user' => $request['user'], 'allposts' => 1))."\">See All</a></span></div>\n\t</div>\n";
else
	$body .= "\t\t\t<span style=\"float:right;\"><a href=\"".render_link('',array('user' => $request['user']))."\">Show Recent</a></span></div>\n\t</div>\n";
} //end Posts


if($request['allposts']!=1){
	$body .= "\t<div class=\"containerPost\">\n";

	$body .= "\t\t<div class=\"postTitle\">Blog Comments</div>\n";

	$body .= "\t\t<span class=\"timestamp\"><small>".$row['type_desc']."</small></span><div class=\"postText\">\n<UL>";

		$sql = "SELECT  blog_com. * , ".db_timestamp( "com_datetime" )." AS datetime,  blog_name ,  blog_zone, blog_id FROM  blog_com INNER JOIN  blog_bits ON  blog_com.com_bit =  blog_bits.bit_id INNER JOIN  blog_blogs ON  blog_bits.bit_blog =  blog_blogs.blog_id WHERE  com_user LIKE  '{$request['user']}' AND  com_edit =0 AND  bit_edit =0 ORDER BY  blog_com.com_datetime DESC";
if(isset($request['allcoms']) && $request['allcoms'] != 1){
$limit = 10;
}
		$result = runQuery($sql,'Blogs');
	
			$bcount = 0;
			while($rowb = db_get_next_row($result)){
			if(checkzone($rowb['blog_zone'],0,$rowb['blog_id'])){
				if($limit && ($bcount > $limit)) break;
			$body .= "\t\t\t<li style=\"margin-top:4px;margin-left:-10px;\"><a href=\"". render_blog_link($rowb['com_bit'],1)."#{$rowb['com_id']}\">".$rowb['com_title']."</a> from ".$rowb['blog_name']."<br />";

				$body .= "\t\t\t<span class=\"timestampComment\">".date("jS F Y @ H:i",$rowb['datetime'])."</span>";
				$body .= "\n";
				$bcount++;
			}
			}
	$body .= "</UL>\t\t";
if(isset($request['allcoms']) && $request['allcoms'] != 1)
	$body .= "\t\t\t<span style=\"float:right;\"><a href=\"".render_link('',array('user' => $request['user'], 'allcoms' => 1))."\">See All</a></span></div>\n\t</div>\n";
else
	$body .= "\t\t\t<span style=\"float:right;\"><a href=\"".render_link('',array('user' => $request['user']))."\">Show Recent</a></span></div>\n\t</div>\n";

} //end Posts

	}

if($request['user'] == $_SESSION['user_name'])
{
	$body .= user_info_display();
}
else
{
	if( function_exists(user_info_display_by_user_name) && $_SESSION['user_admin'] > 1)
 	{
		$body .= user_info_display_by_user_name($request['user']);
	}
}


include('page.php');
?>
