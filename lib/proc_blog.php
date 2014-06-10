<?php

if(!isset($body)){ $body = ''; }

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "data_box_items")
{
	
	if( $_SESSION['user_name'] && ($_SESSION['user_admin'] > 1 || $user_can_post)){
			if(isset($_REQUEST['delete_data_item']) && (int)$_REQUEST['delete_data_item'])
			{
				data_item_delete($request['bit_id'], (int)$_REQUEST['delete_data_item']);
			}
	}
	
	
	print data_box_items_from_bit_id($request['bit_id']);
	exit;
}



if(isset($_REQUEST['flip_keys']) && $_REQUEST['flip_keys']==1){
	if($_COOKIE['showkeys']){
		$_COOKIE['showkeys'] = 0;
		setcookie("showkeys", 0, time()+(3600*24*30),'/');
	}else{
		$_COOKIE['showkeys'] = 1;
		setcookie("showkeys", 1, time()+(3600*24*30),'/');
	}
}
if(isset($_REQUEST['flip_qr']) && $_REQUEST['flip_qr']==1){
	if($_COOKIE['showqr']){
$_COOKIE['showqr'] = 0;
		setcookie("showqr", 0, time()+(3600*24*30),'/');
	}else{
		$_COOKIE['showqr'] = 1;
		setcookie("showqr", 1, time()+(3600*24*30),'/');
	}
}


if((isset ($_REQUEST['add_blog']) && $_REQUEST['add_blog'] != false) && $user_can_post){
	$postid = add_blog_new($blog_id);
	header("Location: ".render_blog_link($postid,true)."?action=edit" );
	exit();	
}


if((isset ($_REQUEST['action']) && $_REQUEST['action'] == 'deledit') && $user_can_post){

	edit_blog_deledit($request['bit_id']);
	header("Location: ".render_blog_link($request['bit_id'],true));
	exit();
}


if(isset($_REQUEST['action_com']) && $_REQUEST['action_com']=="Submit" && isset($_SESSION['user_name']) && $_SESSION['user_name'] && isset($_SESSION['user_admin'])){

	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text'])){

		$id = add_com($request['bit_id'], $_REQUEST['comment_title'], $_REQUEST['text']);
		header("Location: ".render_link($blog['blog_sname'],array('bit_id' => $request['bit_id']))."#$id" );
		exit();

	}else{
		$errmsg = "Check all Fields, could be title";
	}
}


if(isset($_REQUEST['action_comedit']) && $_REQUEST['action_comedit']=="Save" && isset($_SESSION['user_name']) && $_SESSION['user_name'] && isset($_SESSION['user_admin'])){

	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text']) && strlen($_REQUEST['editwhy'])){

			$new_id = edit_com($_REQUEST['comid'],$_REQUEST['editwhy'],$_REQUEST['comment_title'],$_REQUEST['text']);
		header("Location: ".render_link($blog['blog_sname'],array('bit_id' => $request['bit_id'])).'#'.$_REQUEST['comid']);
		exit();
	}else{
		$errmsg = "Check all Fields, could be title or edit reason.";
	}
}

if(isset($_REQUEST['jsact']) && $_REQUEST['jsact']=="action_metaa"){
	$_REQUEST['metat_key'][] = $_REQUEST['meta_keyn'];
	$_REQUEST['metat_value'][] = $_REQUEST['meta_valuen'];
}
if(isset($_REQUEST['jsact']) && $_REQUEST['jsact']=="action_metad"){
		
	unset($_REQUEST['metat_key'][($_REQUEST['jsval'])]);
	unset($_REQUEST['metat_value'][($_REQUEST['jsval'])]);

}
if(isset($_REQUEST['jsact']) && $_REQUEST['jsact']=="action_metaod"){
	$_SESSION['delmetakeys'][$_REQUEST['jsval']] = 1;
}


//Post Submit
if((isset($_REQUEST['action_post']) && $_REQUEST['action_post']=="Submit") && $_SESSION['user_name'] && ($_SESSION['user_admin'] > 1 || $user_can_post)){


	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text']) && strlen($_REQUEST['section'])){
		$metad = null;	
		$metadata = "";
		
			if($_REQUEST['meta_key']){
				
				$metadata['METADATA']['META'] = array();
				foreach($_REQUEST['meta_key'] as $key => $keyn){
					if($keyn && $_REQUEST['meta_value'][$key]){
							$keyname = strtoupper(str_replace(" ","_",$keyn));
							if(isset($metadata['METADATA']['META'][$keyname]))
								$metadata['METADATA']['META'][$keyname] .= ";".addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
							else
							$metadata['METADATA']['META'][$keyname] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
					}
				}
				$metad = writexml($metadata);
			}

		$id	= add_blog($blog_id, $_REQUEST['comment_title'], $_REQUEST['text'], $metad, $_REQUEST['section']);
		header("Location: ".render_blog_link($id,true));
		exit();
	}else{
		$_REQUEST['add_blog']=1;
		$errmsg = "Check Fields;";
		if(!$_REQUEST['comment_title'])
			$errmsg .= " Try Title,"; 
		if(!$_REQUEST['text'])
			$errmsg .= " Try Content Text,"; 
		if(!$_REQUEST['section'])
			$errmsg .= " Try Selecting A Section"; 
		
	}
}


//Post Edit
if((isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="Submit") && $_SESSION['user_name'] && ($_SESSION['user_admin'] > 1 || $user_can_post) && (isset($_REQUEST['itemloop']) && $_REQUEST['itemloop']==0)){

		$sql =	"SELECT  bit_meta, bit_user FROM  blog_bits ";
		$sql .= "WHERE bit_id = ".(int)$request['bit_id']." AND bit_edit in (-1,0) order by `bit_edit` ASC";

		$tresult = runQuery($sql,'Blogs');

		$row = db_get_next_row($tresult);

		if( $_SESSION['user_name']!=$row['bit_user'] && !$_SESSION['user_admin'] > 1 && !$user_can_edit){
			header("Location: ".$blogpost['furl']);
			exit();
		}


		$metadata = readxml($row['bit_meta']);
		
		$metadata['METADATA']['META'] = NULL;
		if(is_array($_REQUEST['meta_key'])){
		foreach($_REQUEST['meta_key'] as $key => $keyn){
			if($keyn && $_REQUEST['meta_value'][$key]){
				$keyname = strtoupper(str_replace(" ","_",$keyn));
			
				if(isset($metadata['METADATA']['META'][$keyname]))
					$metadata['METADATA']['META'][$keyname] .= ";".addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
				else
				$metadata['METADATA']['META'][$keyname] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
				
			}
		}
		}
		
		$metad = null;
		$metad = writexml($metadata);



		if(isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="Submit" && isset($_REQUEST['action_edit_save']) && $_REQUEST['action_edit_save']=="save"){

				edit_blog_new($request['bit_id'],'save',stripslashes($_REQUEST['editwhy']), stripslashes($_REQUEST['comment_title']), stripslashes($_REQUEST['text']), $metad, stripslashes($_REQUEST['section']));

				newMsg("Post saved for later", "message");
				header("Location: ".render_blog_link($request['bit_id'],true));
				

				exit();
		}
		
		if(isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="Submit" && isset($_REQUEST['action_edit_save']) && $_REQUEST['action_edit_save']=="publish"){

				edit_blog_new($request['bit_id'],'publish',stripslashes($_REQUEST['editwhy']), stripslashes($_REQUEST['comment_title']), stripslashes($_REQUEST['text']), $metad, stripslashes($_REQUEST['section']));

				newMsg("Post published", "message");
				header("Location: ".render_blog_link($request['bit_id'],true));
				

				exit();
		}



	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text']) && strlen($_REQUEST['editwhy'])){
	$new_id = edit_blog($request['bit_id'],$_REQUEST['editwhy'], $_REQUEST['comment_title'], $_REQUEST['text'], $metad, $_REQUEST['section']);
	unset($_SESSION['delmetakeys']);
	header("Location: ".render_link($blog['blog_sname'],array('bit_id' => $request['bit_id'])) );
	exit();	
	}else{
	    $_REQUEST['action'] = "edit";	
		$errmsg = "Check all Fields, could be title or reason for the edit";
	}
}


if((isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="Submit") && (isset($_REQUEST['itemloop']) && $_REQUEST['itemloop']==1) ){
	$_REQUEST['action'] = "edit";
}

///Add/Edit Blog Post Form
if(((isset ($_REQUEST['add_blog']) && $_REQUEST['add_blog'] != false) && $user_can_post)  || (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit")){

$body .= makesidebar();

if(isset($errmsg))
{
	$body .= blog_style_error($errmsg);
}
$edittext  = isset($_REQUEST['text'])          ? stripslashes($_REQUEST['text'])          : '';
$edittitle = isset($_REQUEST['comment_title']) ? stripslashes($_REQUEST['comment_title']) : '';
$editgroup = isset($_REQUEST['section'])       ? stripslashes($_REQUEST['section'])       : '';
$blogpost = NULL;


if($_REQUEST['action'] == "edit"){
	$post = db_get_post_by_id($request['bit_id'],'edit');
	if(!isset($post['bit_id'])){
		newMsg("Error: this post can't be edited", "error");
		header("Location: ".render_blog_link($request['bit_id'],true));
		exit();
	}

	if($post['bit_edit']==0){
		edit_blog_draft($request['bit_id']);
		$post = db_get_post_by_id($request['bit_id'],'edit');
	}

	if($post['bit_id'] == $post['bit_rid'])
		$blogpost['title'] = "New Post";
	else
		$blogpost['title'] = "Edit Post";

if(!$edittext)
$edittext = $post['bit_content'];
if(!$edittitle)
$edittitle =   $post['bit_title'];
if(!$editgroup)
$editgroup =   $post['bit_group']; //Section
	$metadata = NULL;
	$metadata = readxml($post['bit_meta']);


	$blogpost['furl'] = render_blog_link($post['bit_id'],true);
	if( $_SESSION['user_name']!=$post['bit_user'] && !($_SESSION['user_admin'] > 1) && !$user_can_edit){
		newMsg("Forbidden: You are not allowed to edit this post", "error");
		header("Location: ".$blogpost['furl']);
		exit();
	}

	
$blogpost['hiddenform'] = "<input type=\"hidden\" name=\"aaction\" value=\"edit\" />";
}else{
$blogpost['title'] = "Add Post";
$blogpost['furl'] = render_link($blog['blog_sname']);
$blogpost['hiddenform'] = "<input type=\"hidden\" name=\"add_blog\" value=\"1\" />";
}

$blogpost['post'] = "

<script language=\"JavaScript\" type=\"text/javascript\">

function refresh_data_box_items()
{
  var url = window.location.href;
  url = url.replace('action=edit', 'action=data_box_items');
  $('#dataBoxItems').load(url);
}

</script>
";

$blogpost['post'] .= "
<form action=\"".$blogpost['furl']."\" name=\"blog\" id=\"post_form\" method=\"post\" target=\"_self\">";

$blogpost['post'] .= $blogpost['hiddenform'];

$blogpost['post'] .= "<input type=\"hidden\" name=\"itemloop\" value=\"0\" />";
$blogpost['post'] .= "<input type=\"hidden\" name=\"jsact\" value=\"\" />";
$blogpost['post'] .= "<input type=\"hidden\" name=\"jsval\" value=\"\" />";
$blogpost['post'] .= "<input type=\"hidden\" name=\"blog_id\" value=\"$blog_id\" />";

//markitup
$jquery['ui'] = true;
$jquery['tinymce'] = true;
$jquery['validate'] = true;
$jquery['fieldselection'] = true;

$jquery['edit-post'] = true;

//$jquery['function'] .= "$('#bbcode').markItUp(mySettings);\n";

add_tiny_mce();

if(!isset($jquery['code'])) { $jquery['code'] = ''; }
$jquery['code'] .= "var blog_id = {$blog_id};\n";

$blogpost['post'] .="Title<span class=\"formreq\">*</span>  <br/><input type=\"text\" name=\"comment_title\" class=\"comment_title required\" size=\"80\" value=\"".$edittitle."\"/><br/>

<br/>Text<span class=\"formreq\">*</span>  <br/><textarea name=\"text\" id=\"bbcode\" cols=\"60\" rows=\"7\" class=\"required\">".htmlentities($edittext)."</textarea><br/>
<table style='border: 1px solid darkgrey; width: 556px; padding: 10px; margin-bottom: 10px;'><tr><td>
Section<span class=\"formreq\">*</span><br/>
<select id='section_select' style='width:150px' name=\"section\" class=\"required\">
	<option value=''></option>";
$found = 0;
$templates_found = 0;
if(isset($bloggroups)){
ksort($bloggroups);
foreach($bloggroups as $group){
	if( $editgroup == $group || (isset($_REQUEST['section']) && $group == stripslashes($_REQUEST['section'])) ){
	$blogpost['post'] .= "<option value=\"$group\" selected='selected'>$group</option>\n";
	$found = 1;
		}else{
	$blogpost['post'] .= "<option value=\"$group\">$group</option>\n";
	}
	if($group == "Templates") $templates_found = 1;
}

	if(!$found && is_set_not_empty('section', $_REQUEST))
		$blogpost['post'] .= "<option value=\"".stripslashes($_REQUEST['section'])."\" selected='selected'>".stripslashes($_REQUEST['section'])."</option>\n";

	if(!$templates_found)
	{
		$bloggroups[] = "Templates";
		$blogpost['post'] .= "<option value=\"Templates\">Templates</option>\n";
	}
}
$blogpost['post'] .= "</select><br/><br/>";
$ii = 0;
$blogpost['post'] .= "Metadata<table style='margin: 10px' id='metadata_table'>";
$blogpost['post'] .= "<tr><td></td><td align='center'>Key</td><td align='center'>Value</td></tr>";

// $blogpost['post'] .= "<table id=\"metadata_table\">";
// $blogpost['post'] .= "<tr><td>Extra Metadata:</td><td align=center>key</td><td align=center>value</td></tr>";
if(is_array($metadata['METADATA']['META'])){
		foreach($metadata['METADATA']['META'] as $key => $val){
			$keysvals = explode(";",$val);
			foreach($keysvals as $value){
				$blogpost['post'] .= "<tr id=\"meta_row_{$ii}\"><td></td>";
				$blogpost['post'] .= "<td><input type=\"text\" name=\"meta_key[]\" id=\"meta_key_{$ii}\" value=\"".strtotitle(str_replace("_"," ",$key))."\" class=\"required\" style=\"width: 180px;\" /> </td>";
				$blogpost['post'] .= "<td><input type=\"text\" name=\"meta_value[]\" id=\"meta_value_{$ii}\" value=\"$value\" class=\"meta_box_auto required\"  style=\"width: 180px\" /></td>";
				$blogpost['post'] .= "<td>".mkButton("table_delete","",array("onclick"=>"removeMetadata({$ii})", "title"=>"Remove"))."</td>";
				$blogpost['post'] .= "</tr>";
				$ii++;
			}
		}
	}

$blogpost['post'] .= "<tr><td></td>";
$blogpost['post'] .= "<td><select id='metadata_key_new_select'><option value=''></option>";

$metas = meta_metas($blog_id);
$metas_vals = array();
if(isset($metas)){
	ksort($metas);
	foreach($metas as $key => $value){
		$key = strtotitle(str_replace("_"," ",$key));
		$blogpost['post'] .= "<option value='$key'>".$key."</option>\n";
		ksort($value);
		foreach($value as $keyv => $value){
			$metas_vals[] = array("label"=> str_replace("_"," ",$keyv), "category"=>"$key");
		}		
	}
}
$blogpost['post'] .= "</select>";
$metas_vals = json_encode($metas_vals);

$jquery['code'] .= <<<JQ

$(function() {	
	$('#metadata_key_new').blur(function(){
	var selval = $(this).val();
	var data = {$metas_vals};
	populateMetaValueNew(data, selval);
	});	
});

JQ;

$blogpost['post'] .= "</td>";
$blogpost['post'] .= "<td><select id='metadata_value_new_select'><option/></select></td><td>".mkButton("table_add","",array("id"=>"metadata_add_button", "title"=>"Add"))."</td>";	
$blogpost['post'] .= "</tr>";
$blogpost['post'] .= "</table><br/>";

if($_REQUEST['action'] == "edit"  && $post['bit_rid'] != $post['bit_id']){
  $request_editwhy = isset($_REQUEST['editwhy']) ? $_REQUEST['editwhy'] : '';
  $blogpost['post'] .= "Reason For Edit<span class=\"formreq\">*</span><br/><textarea name=\"editwhy\" rows=\"2\" cols=\"50\" style='width: 520px' class=\"required expand\">".$request_editwhy."</textarea><br />";
}

$blogpost['post'] .= "</td></tr></table>";
$blogpost['post'] .= "<center style=\" padding-top: 10px; padding-bottom: 15px; margin:auto;\">";

if($_REQUEST['action'] == "edit"){
	if($post['bit_edit']==-1){
		$blogpost['post'] .= "<input type=\"hidden\" name=\"action_edit\" value=\"Submit\" />";
		$blogpost['post'] .= "<input type=\"hidden\" id=\"action_edit_save\" name=\"action_edit_save\" value=\"\" />";
		$blogpost['post'] .= mkButton("page_go","Publish", array("class"=>"withbox", "onclick"=>"javascript: if(!validateMetadata()) return false; $('#action_edit_save').val('publish');$('#post_form').submit();"));
		$blogpost['post'] .= mkButton("disk","Save for later", array("class"=>"withbox", "onclick"=>"javascript: $('#action_edit_save').val('save'); $('#post_form').validate().cancelSubmit = true; $('#post_form').submit();"));
	}else{
			$blogpost['post'] .= "<input type=\"hidden\" name=\"action_edit\" value=\"Submit\" />";
			$blogpost['post'] .= mkButton("disk","Save", array("class"=>"withbox", "onclick"=>"javascript: $('#post_form').submit();"));
	}
}else{
	$blogpost['post'] .= "<input type=\"hidden\" name=\"action_post\" value=\"Submit\" />";
	$blogpost['post'] .= mkButton("disk","Save", array("class"=>"withbox", "onclick"=>"javascript: $('#post_form').submit();"));
}

$blogpost['post'] .= mkButton("page_white_magnify","Preview", array("class"=>"withbox", "onclick"=>"javascript: document.blog.target = 'previewpopup'; tempurl = document.blog.action; document.blog.action = '".render_link('preview.php')."'; previewpopupvariable = window.open('', 'previewpopup', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0, resizable=0,width=600,height=450,left = 50,top = 50'); document.blog.submit(); document.blog.target = '_self'; document.blog.action = '{$blogpost['furl']}'; "));
$blogpost['post'] .= mkButton("delete","Cancel", array("class"=>"withbox",  "onclick"=>"if(!confirm('Are you sure you want to cancel? (All changes will be lost)')) return false;", "href"=>"{$blogpost['furl']}"));
$blogpost['post'] .= mkButton("bin","Delete this draft", array("class"=>"withbox",  "onclick"=>"if(!confirm('Are you sure you want to delete this draft? (All changes will be lost)')) return false;", "href"=>"{$blogpost['furl']}?action=deledit"));
$blogpost['post'] .= "</center>";

if($_REQUEST['action'] == "edit")
{
	$blogpost['data_title'] = "Attached Files"; 
	$blogpost['data']  = "<a href=\"javascript:window.open('{$ct_config['blog_path']}{$ct_config['upload_php']}?post_id={$request['bit_id']}&amp;blog_id={$blog['blog_id']}','upload', 'left=400,top=20,width=342,height=400,toolbar=0,resizable=0,location=0,directories=0,scrollbars=0,menubar=0,status=0'); void(0)\" style=\"float:right; margin: 2px;\" id=\"link_upload\">Upload data</a>";
	$blogpost['data'] .= "&nbsp;<a href=\"javascript:window.open('{$ct_config['blog_path']}sketch.php?id={$request['bit_id']}&amp;blog_id={$blog['blog_id']}','upload', 'left=100,top=20,width=700,height=700,toolbar=0,resizable=0,location=0,directories=0,scrollbars=0,menubar=0,status=0'); void(0)\" style=\"float:right; margin: 2px; \" id=\"link_sketch\">Add sketch</a>";
	$blogpost['data'] .= data_box_items($metadata['METADATA']['DATA'],$post);	
}
$blogpost['post'] .= "</form>";
$body .= blog_style_post($blogpost);

//listblogs
include('page.php');
exit();
}

// utility functions for rendering the list of attached files
function data_box_items_from_bit_id($bit_id)
{
	global $ct_config;
	$row = db_get_blog_metadata_by_bit($bit_id, 'edit');

	if( $_SESSION['user_name']!=$row['bit_user'] && !$_SESSION['user_admin'] > 1 && !$user_can_edit)
	{
		return '<!-- user not permitted -->';
	}

	$local_metadata = readxml($row['bit_meta']);
	$array = array("bit_id"=>$bit_id);
	return data_box_items($local_metadata['METADATA']['DATA'],	$array );
}

function data_box_items($metadata, &$blogpost)
{
	$dbi = '';
	$furl = render_blog_link($blogpost['bit_id'],true);
	$dbi .= "<div id='dataBoxItems'>\n";
	if($metadata)
	{		
		$datas = split(",", $metadata);
		foreach($datas as $bit)
		{
			$data_type = array("type" => "data", "id" => (int)($bit));
			$dbi .= "<div class=\"data_edit_row\"><div style=\"float:left;\">";
			$dbi .= getdata($bit);
			$dbi .= "</div>\n";
			$dbi .= "<div style=\"padding-top:4px;\">Data Item: $bit </div>";
			$dbi .= "<a href=\"#\" class=\"button withbox data_link\" onclick=\"$('#bbcode').tinymce().execCommand('mceInsertContent',false,'[data]{$data_type['id']}[/data]');return false\">Add link to text</a><br/>";			
			$dbi .= mkButton("bin","Delete data item", array("class"=>"withbox data_delete",  "onclick"=>"if(confirm('Are you sure you want to delete this item?')) $('#dataBoxItems').load('{$furl}?action=data_box_items&amp;delete_data_item=$bit'); return false;", "href"=>"#"));			
			$dbi .= "<div style=\"clear:left;\"></div>";
			$dbi .= "</div>\n";
		}	
	}
	$dbi .= "</div>\n";
	$dbi .= "<div style=\"clear:left\"></div>";
	return $dbi;
}
?>
