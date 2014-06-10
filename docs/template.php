<?php

include("../lib/default_config.php");



//Load Blog info
if($_REQUEST['bit_id']){
	$sql = "SELECT * FROM  blog_bits WHERE bit_edit = 0 AND bit_id = ".(int)$_REQUEST['bit_id'];
	$result = runQuery($sql,'Get blog Id');
	$blog = db_get_next_row($result);
		if((int)$_REQUEST['blog_id']){
	$_SESSION['blog_id'] = (int)$_REQUEST['blog_id'];
	$blog_id = (int)$_REQUEST['blog_id'];
		}else{
	$_SESSION['blog_id'] = (int)$blog['bit_blog'];
	$blog_id = (int)$blog['bit_blog'];
		}
}else{
	header("Location: {$ct_config['blog_path']}?msg=forbidden");
	exit();
}

checkblogconfig($blog_id);

$blog = db_get_blog_by_id($blog_id);

$title = $blog['blog_name'];
$desc = $blog['blog_desc'];

$sql = "SELECT bit_group FROM  blog_bits WHERE bit_blog = ".$blog_id." AND bit_edit = 0 GROUP BY bit_group ";
$tresult = runQuery($sql,'Fetch Page Groups');
while($row = db_get_next_row($tresult)){
		$bloggroups[] = $row['bit_group'];


}

function listoptions($blog_id,$type,$val){
global $ct_config;
	if($blog_id) $blog_dit = "AND bit_blog = ".$blog_id.""; 
	if($type=="%"&& $val=="%"){
		$sqlb = "SELECT  bit_id ,  bit_user ,  bit_title FROM  blog_bits  WHERE    bit_edit = 0 $blog_dit ORDER BY  bit_datestamp DESC " ;
	}else
		$sqlb = "SELECT  bit_id ,  bit_user ,  bit_title FROM  blog_bits  WHERE  bit_meta LIKE '%<meta>%<".$type.">".$val."</".$type.">%</meta>%' $blog_dit AND bit_edit = 0 ORDER BY  bit_datestamp DESC " ;
	$tresult = runQuery($sqlb,'Fetch Page Groups');
    
	$ret .= "<option value=\"\"></option>";
    while($row = db_get_next_row($tresult)){

	$ret .= "<option value={$row['bit_id']}>{$row['bit_title']}</option>";
	}	
	return  $ret;
}


function srcblog($blog_name){
global $ct_config,$src_blog;
	if($blog_name=="all"){
		return $src_blog = 0;	
	}
	$sqlb = "SELECT * FROM  blog_blogs WHERE  blog_sname LIKE  '{$blog_name}'" ;
	$tresult = runQuery($sqlb,'Fetch Page Groups');
    
	if($row = db_get_next_row($tresult)){
		$src_blog = $row['blog_id'];
	}else{
		return " >>[b]Source Blog Not Found[/b]<< ";
	}

	return  $src_blog;
}

function srcblogid($blog_name){
global $ct_config;
	if($blog_name=="all"){
		return 0;	
	}
	$sqlb = "SELECT * FROM  blog_blogs WHERE  blog_sname LIKE  '{$blog_name}'" ;
	$tresult = runQuery($sqlb,'Fetch Page Groups');
    	if($row = db_get_next_row($tresult)){
		return $row['blog_id'];
	}else{
		return -1;
	}
}

function callback_srcblog($matches) {
	return srcblog($matches[1]);
}
function callback_box($matches) {
	global $preg_index;
	if (sizeof($matches) > 1) {
		return '<input type="text" size="' . $matches[1] . '" name="templateval_' . $preg_index++ .'" />';
	}
	return '<input type="text" name="templateval_' . $preg_index++ .'" />';
}
function callback_checkbox($matches) {
	global $preg_index;
	return '<input type="checkbox" name="templateval_'.$preg_index++.'" />';
}
function callback_blog($matches) {
	global $preg_index;
	return 'Post Id:<input type="text" name="templateval_' . $preg_index++ . '" size="6" />';
}
function callback_select($matches) {
	global $preg_index, $src_blog;
	if (sizeof($matches) > 3) {
		return '<select name="templateval_' . $preg_index++ . '">' . listoptions(srcblogid($matches[1], $matches[2], $matches[3])) . '</select>';
	}
	return '<select name="templateval_' . $preg_index++ . '">' . listoptions(srcblogid($src_blog, $matches[1], $matches[2])) . '</select>';
	
}
function callback_section($matches) {
	$_REQUEST['section'] = $matches[1];
	return "";
}
function callback_metadata($matches) {
	$_REQUEST['metat_key'][] = $matches[1];
	$_REQUEST['metat_value'][] = $matches[2];
}
function callback_remove_match_string($matches) {
        return "";
}

$sql = "SELECT * FROM  blog_bits WHERE  bit_id = {$_REQUEST['bit_id']} AND  bit_edit =0";
$result = runQuery($sql,'Blogs');
$rowd = db_get_next_row($result);

if(isset($_REQUEST['add_blog'])){

$posttxt = addslashes($rowd['bit_content']);
$i = 0;
 $preg = array(
   
	
//[data]
	
'/\[\[box\]\]/sie'=> " \$_REQUEST['templateval_'.\$i++] ",
'/\[\[box=([^\]]*?)\]\]/sie'=>" \$_REQUEST['templateval_'.\$i++] ",
	
'/\[\[checkbox\]\]/sie'=> " isset(\$_REQUEST['templateval_'.\$i++]) ? '&#9745;' : '&#9744;' ",
		

'/\[\[blog\]\]/sie'=>" '[blog]'.\$_REQUEST['templateval_'.\$i++].'[/blog]' ",
'/\[\[([^\]]*?)\:([^\]]*?)\]\]/sie'=>" '[blog]'.\$_REQUEST['templateval_'.\$i++].'[/blog]' ",

'/\[\[([^\]]*?)>([^\]]*?)\]\](\s*)/sie'=>"",


'/\<\<([^\]]*?)\>\>/si' => "",
'/\[\[([^\]]*?)\]\]/si' => ""
	
	

	//	'/\[\[box\]\](.*?)\[\/data(?::\w+)?\]/sie'=>"'<div style=\"float:left;\">'.getdata(\\1).'</div>'"
	 
  );
	$posttxt = preg_replace(array_keys($preg), array_values($preg), $posttxt);

	$metad = null;	
		$metadata = "";
		//check for new value
		if($_REQUEST['meta_keyn'] && $_REQUEST['meta_valuen']){
		$_REQUEST['metat_key'][] = $_REQUEST['meta_keyn'];
		$_REQUEST['metat_value'][] = $_REQUEST['meta_valuen'];
		}

			if($_REQUEST['metat_key']){
				
				foreach($_REQUEST['metat_key'] as $key => $keyn){
					if($keyn && $_REQUEST['metat_value'][$key]){
							$metadata['METADATA']['META'][strtoupper(str_replace(" ","_",$keyn))] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['metat_value'][$key])));
					}
				}
				
				$metad = writexml($metadata);
			}

		$id	= add_blog($blog_id, $_REQUEST['comment_title'], $posttxt, $metad, $_REQUEST['section']);
		header("Location: ".render_blog_link($id,1)."?action=edit" );
		exit();


}else{

	$src_blog = $blog_id;

	$posttxt = $rowd['bit_content'];
	$preg_index = 0;

	$preg = array(
		'/\[\[srcblog\=([^\]]*?)\]\]/si' => 'callback_srcblog',
		'/\[\[box\]\]/si' => 'callback_box',
		'/\[\[box=([^\]]*?)\]\]/si' => 'callback_box',
		'/\[\[checkbox\]\]/si' => 'callback_checkbox',
		'/\[\[blog\]\]/si' => 'callback_blog',
		'/\[\[([^\]]*?)\:([^\]]*?)\:([^\]]*?)\]\]/si' => 'callback_select',
		'/\[\[([^\]]*?)\:([^\]]*?)\]\]/si' => 'callback_select',
		'/\[\[Section&gt;([^\]]*?)\]\](\s*)/si' => 'callback_section',
		'/\[\[Section>([^\]]*?)\]\](\s*)/si' => 'callback_section',
		'/\[\[([^\]]*?)&gt;([^\]]*?)\]\](\s*)/si' => 'callback_metadata',
		'/\[\[([^\]]*?)>([^\]]*?)\]\](\s*)/si' => 'callback_metadata',
		'/\<\<(.*?)\>\>/si' => 'callback_remove_match_string',
		'/\[\[([^\]]*?)\]\]/si' => 'callback_remove_match_string',
	);
	foreach ($preg as $pattern => $callback) {
		$posttxt = preg_replace_callback($pattern, $callback, $posttxt);
	}
	
$posttxt =  bbcode($posttxt);
}
$body .= "\t<div class=\"containerPost\">\n";

$body .= "\t<div class=\"postTitle\">Add Post From Template</div>\n";


$body .= "\t<div class=\"postText\">\n

<script language=\"JavaScript\" type=\"text/javascript\">

function NewSection() {

if (document.blog.section.value == '- New section -') {

var new_section = prompt (\"New section name:\",\"\")

document.blog.section.options[2] = new Option(new_section,new_section);
document.blog.section.options[2].selected = true;


}

}
function NewMeta() {
if (document.blog.meta_keyn.value == '- New Field -') {

var new_section = prompt (\"New meta field name:\",\"\")

document.blog.meta_keyn.options[2] = new Option(new_section,new_section);
document.blog.meta_keyn.options[2].selected = true;


}

}


</script>
";

$body .= "
<form action=\"template.php?bit_id={$_REQUEST['bit_id']}\" name=\"blog\" id=\"post_form\" method=\"post\" target=\"_self\">";

$body .= "<input type=\"hidden\" name=\"add_blog\" value=\"1\" />";
$body .= "<input type=\"hidden\" name=\"jsact\" value=\"\" />";
$body .= "<input type=\"hidden\" name=\"jsval\" value=\"\" />";
$body .= "<input type=\"hidden\" name=\"blog_id\" value=\"$blog_id\" />";

$body .="Title<span class=\"formreq\">*</span> <br/><input type=\"text\" name=\"comment_title\" class=\"comment_title required\" size=\"80\" value=\"".$rowd['bit_title']."\" /><br/><br/>";

$body .= "\t\tText<span class=\"formreq\">*</span><br><div class=\"dataBox\">$posttxt</div>";

$body .= "<br/>Section<span class=\"formreq\">*</span> 
<select name=\"section\" onchange=\"javascript:NewSection();\" class=\"required\"><option value=''></option>
";

$found = 0;
if(isset($bloggroups)){
foreach($bloggroups as $group){
	if($group == stripslashes($_REQUEST['section'])){
	$body .= "<option value=\"$group\" selected='selected'>$group</option>\n";
	$found = 1;
		}else{
	$body .= "<option value=\"$group\">$group</option>\n";
	}
}
}

if(!$found && $_REQUEST['section'])
	$body .= "<option value=\"".stripslashes($_REQUEST['section'])."\" selected='selected'>".stripslashes($_REQUEST['section'])."</option>\n";
	
$body .= "
<option value='- New section -'>- New section -</option></select><br/>";

$body .= "Metadata: <br />";

if($_REQUEST['metat_key']){

foreach($_REQUEST['metat_key'] as $key => $keyn){
			if($keyn && $_REQUEST['metat_value'][$key]){
				//$metadata['METADATA']['META'][strtoupper(str_replace(" ","_",$keyn))] = addslashes(str_replace(array(' ','/'),array('_','-'),$_REQUEST['meta_value'][$key])) ;
			$body .= "key:<input type=\"text\" name=\"metat_key[]\" value=\"".strtotitle(str_replace("_"," ",$keyn))."\" /> value:<input type=\"text\" name=\"metat_value[]\" value=\"".$_REQUEST['metat_value'][$key]."\" /><br>";		
	}
	}
}
else if(!empty($rowd['bit_meta'])){
        $metadata = readxml($rowd['bit_meta']);
        if(isset($metadata['METADATA']) && isset($metadata['METADATA']['META']) && is_array($metadata['METADATA']['META'])) {
                foreach($metadata['METADATA']['META'] as $key => $value){
                        $body .= "key:<input type=\"text\" name=\"metat_key[]\" value=\"".strtotitle(str_replace("_"," ",$key))."\" /> value:<input type=\"text\" name=\"metat_value[]\" value=\"".$value."\" /><br>";
                }
        }
}


$body .="You can add/delete metadata at the next step";

$body .= "<br/><br/>Blog: ";

$sql = "SELECT * FROM  blog_blogs INNER JOIN blog_types ON blog_type = type_id  WHERE blog_del = 0 AND blog_redirect = '' ORDER BY  blog_types.type_order ASC  ";
$result = runQuery($sql,'Blogs');
$body .= "<select name=\"blog_id\" id=\"select_blog_id\" style=\"width:120px;\" onChange=\"document.blog_id.submit();\">";
		while($rowb = db_get_next_row($result)){
	if(($rowb['blog_zone']==0) || (checkzone($rowb['blog_zone'],0,$rowb['blog_id'])) || ($_SESSION['user_admin'] > 1)){
		$body .= "<option value={$rowb['blog_id']}";
		if($blog_id == $rowb['blog_id']){
			$body .= " selected";
		}
		$body .= ">{$rowb['blog_name']}</option>";
	}
	

}
$body .= "</select>";


$jquery['validate'] = true;
$jquery['function'] .= "$('#post_form').validate();\n";

$body .= "<center style=\" padding-top: 10px; padding-bottom: 15px; margin:auto;\">";
$body .= mkButton("disk","Save", array("class"=>"withbox", "onClick"=>"javascript: if(confirm('Are you sure you want to post this entry into the Notebook \'' + $('#select_blog_id option:selected').text() + '\' ?'))$('#post_form').submit();else return false;"));
$body .= mkButton("delete","Cancel", array("class"=>"withbox",  "onClick"=>"if(!confirm('Are you sure you want to cancel?  (All changes will be lost)')) return false;", "href"=>render_blog_link($rowd['bit_id'],1)));
$body .= "</center>";




$body.= "</form>";

	

$body.= "</div></div>";



include('page.php');

?>
