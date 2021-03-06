<?php

include("../lib/default_config.php");

if($_SESSION['user_admin']<1){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=/".urlencode($_REQUEST['uri']));
		exit();
	}

if(!isset($body)) { $body = ''; }
if(!isset($head)) { $head = ''; }

if($_REQUEST['blog']){
	$sql = "SELECT * FROM  blog_blogs WHERE blog_sname = '{$_REQUEST['blog']}'";	
	$result = runQuery($sql,'Blogs');
	$blog = db_get_next_row($result);

	if(!checkzone(-1,0,$blog['blog_id'])){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!");
		exit();
	}
}

checkblogconfig($blog['blog_id']);

include("style/{$ct_config['blog_style']}/blogstyle.php");

if($_REQUEST['blog'] && is_set_not_empty('zone', $_REQUEST) && $_REQUEST['auser'] ){
	$sql = "SELECT *  FROM blog_zone WHERE zone_id = {$_REQUEST['zone']} AND zone_type LIKE 'user'";
	$result = runQuery($sql,'Get zone Id');
	if($mzone = db_get_next_row($result)){
		$array = split(";",$mzone['zone_res']);
		if(!in_array($_REQUEST['auser'],$array)){
			$array[] = $_REQUEST['auser'];
			$sql = "UPDATE   blog_zone SET  zone_res =  '".join(";",$array)."' WHERE  zone_id = {$_REQUEST['zone']} AND zone_type =  'user' " . db_limit_1();
			runQuery($sql,'');
		}
		header('Location: settings.php?blog='.$_REQUEST['blog']);
		exit();
	}
	
}
if($_REQUEST['blog'] && is_set_not_empty('zone', $_REQUEST) && $_REQUEST['duser'] ){
	$sql = "SELECT *  FROM blog_zone WHERE zone_id = {$_REQUEST['zone']} AND zone_type LIKE 'user'";
	$result = runQuery($sql,'Get zone Id');
	if($mzone = db_get_next_row($result)){
		$array = split(";",$mzone['zone_res']);
		unset($array[array_search($_REQUEST['duser'],$array)]);
		if(count($array)){
			$sql = "UPDATE   blog_zone SET  zone_res =  '".join(";",$array)."' WHERE  zone_id = {$_REQUEST['zone']} AND zone_type =  'user' " . db_limit_1();
		}else{
			$sql = "UPDATE   blog_blogs SET  blog_zone =  '-1' WHERE  blog_sname ='{$_REQUEST['blog']}' " . db_limit_1();
			runQuery($sql,'Get zone Id');
			$sql = "DELETE FROM  blog_zone WHERE  zone_id = {$_REQUEST['zone']} AND zone_type =  'user' " . db_limit_1();
		}
		runQuery($sql);
		header('Location: settings.php?blog='.$_REQUEST['blog']);
		exit();
	}
	
}
if($_REQUEST['blog'] && is_set_not_empty('newzone', $_REQUEST)){
	$sql = "SELECT MAX(  zone_id ) AS max FROM   blog_zone ";
	$result = runQuery($sql,'Get zone Id');
	$mzone = db_get_next_row($result);
	$maxid = $mzone['max']+1;
	$sql = "INSERT INTO   blog_zone (  zone_id ,  zone_name ,  zone_type ,  zone_res ) VALUES ( '{$maxid}',  '{$_REQUEST['newzone']}',  'user',  '{$_SESSION['user_name']}' );";
	runQuery($sql,'Get zone Id');
	$sql = "UPDATE   blog_blogs SET  blog_zone =  '{$maxid}' WHERE  blog_sname ='{$_REQUEST['blog']}' " . db_limit_1();
	runQuery($sql,'Get zone Id');
	header('Location: settings.php?blog='.$_REQUEST['blog']);
	exit();
}
if($_REQUEST['blog'] && isset($_REQUEST['savezone'])){
	$sql = "UPDATE   blog_blogs SET  blog_zone =  '".(int)$_REQUEST['savezone']."' WHERE  blog_sname ='{$_REQUEST['blog']}' " . db_limit_1();
	runQuery($sql,'Get zone Id');
	header('Location: settings.php?blog='.$_REQUEST['blog']);
	exit();
}

if(isset($_REQUEST['addblog']) || isset($_REQUEST['saveblog'])){
	if(strlen($_REQUEST['blog_name'])==0){
		$formerr['blog_name'] = " <span style=\"color:red;\">Please Enter a Title</span>";
	}
	if(isset($_REQUEST['addblog'])){
	if(strlen($_REQUEST['blog_sname'])==0){
		$formerr['blog_sname'] = " <span style=\"color:red;\">Please Enter a Short Name</span>";
	}
	$_REQUEST['blog_sname'] = strtolower($_REQUEST['blog_sname']);
	$_REQUEST['blog_sname'] = ereg_replace( "[\ \-]", "_", $_REQUEST['blog_sname']);
	$snamecmp = $_REQUEST['blog_sname'];
	$_REQUEST['blog_sname'] = ereg_replace( "[^a-z0-9\_]", "", $_REQUEST['blog_sname']);
	if($_REQUEST['blog_sname'] != $snamecmp){
		$formerr['blog_sname'] = " <span style=\"color:red;\">Invalid Short Name</span>";
		$_REQUEST['blog_sname'] = $snamecmp;
	}
	if(in_array($_REQUEST['blog_sname'],$ct_config['protected_paths'])){
		$formerr['blog_sname'] = " <span style=\"color:red;\">This name is reserved.</span>";
	}
	$sql = "SELECT * FROM  blog_blogs WHERE  blog_sname LIKE '{$_REQUEST['blog_sname']}';";
	$result = runQuery($sql,'Get blog Id');
	if(db_get_number_of_rows($result)){
			$formerr['blog_sname'] = " <span style=\"color:red;\">This name is has been taken</span>";
	}
	}

	if(!isset($formerr)){
		if(isset($_REQUEST['addblog'])){
		$sql = "INSERT INTO   blog_blogs (  blog_id ,  blog_name ,  blog_sname ,  blog_desc ,  blog_user ,  blog_zone ,  blog_del ,  blog_type ,  blog_redirect ,  blog_infocache ,  blog_about ) 
VALUES (NULL ,  '{$_REQUEST['blog_name']}',  '{$_REQUEST['blog_sname']}',  '{$_REQUEST['blog_desc']}',  '{$_SESSION['user_name']}',  '-1',  '0',  '{$_REQUEST['blog_type']}',  '',  '',  '');";
		runQuery($sql,'Get blog Id');
		header("Location: settings.php?blog={$_REQUEST['blog_sname']}&msg=New+Notebook+created");

		}else{
		$sql = "UPDATE   blog_blogs SET  blog_name =  '{$_REQUEST['blog_name']}', blog_desc =  '{$_REQUEST['blog_desc']}', blog_type =  '{$_REQUEST['blog_type']}' WHERE  blog_sname = '{$_REQUEST['blog']}' " . db_limit_1();
		runQuery($sql,'Get blog Id');
		unset($_REQUEST['saveblog']);
		//$body .= "<div class=\"msg\">Details Saved</div>";
		newMsg("Details updated", "message");
		}
	}
}





	$blogpost = NULL;
	$blogpost['title'] = "Notebook Settings";

//Load Blog info
if($_REQUEST['blog']){
	$sql = "SELECT * FROM  blog_blogs WHERE blog_sname = '".$_REQUEST['blog']."'";
	$result = runQuery($sql,'Get blog Id');
	if($blog = db_get_next_row($result)){
		
		
		$title = $blog['blog_name'];
		$desc = $blog['blog_desc'];
		$title_url = render_link($blog['blog_sname']);
		
		if(!isset($_REQUEST['saveblog'])){
			foreach(array("blog_name","blog_desc","blog_type") as $val){
				$_REQUEST[$val] = $blog[$val];
			}
		}
	}else{
		set_http_error(404, "settings.php?blog={$_REQUEST['blog']}");
		exit();
	}
}else{
		$title = "New Notebook";
}
	$formerr_blog_name = (isset($formerr) && isset($formerr['blog_name'])) ? $formerr['blog_name'] : '';
	$formerr_blog_sname = (isset($formerr) && isset($formerr['blog_sname'])) ? $formerr['blog_sname'] : '';
	
	$head .= <<< BBJ
	<script type='text/javascript'>
	function buildValidShortName()
	{
		var name = document.forms['settings']['blog_name'].value.replace(/[\`\']/g,'');
		name = name.toLowerCase().replace(/[^a-z0-9]/g,'_');
		document.forms['settings']['blog_sname'].value = name.substring(0,19);		
	}
	function validateSettings()
	{
		var valid = true;
		if(document.forms['settings']['blog_sname'].value.length > 20) valid = false;
		if(document.forms['settings']['blog_sname'].value.match(/[^a-z0-9_]/)) valid = false;
		if(valid == false) alert('Notebook Short Name must be 20 chars max and can contain only letters, numbers and \'_\'');
		return valid;
	}
	</script>	
BBJ;
	
	$blogpost['post'] .= "<div class=\"postSubTitle\">Basic Details</div>";
	$blogpost['post'] .= "<fieldset><table>";
	
	$blogpost['post'] .= "<form name=\"settings\" id='settings' method=\"POST\" onsubmit='return validateSettings()' action=\"settings.php?blog={$blog['blog_sname']}\" >";
	$blogpost['post'] .= "<tr><th>Notebook Title*</th><td><input size=\"30\" name=\"blog_name\" id='blog_name' value=\"".stripslashes($_REQUEST['blog_name'])."\" onkeyup='buildValidShortName()' onkeypress=\"if (typeof(settings.saveblog) != 'undefined') settings.saveblog.disabled=false; \"/>{$formerr_blog_name}</td></tr>";
	if(!$blog['blog_id']){
	$blogpost['post'] .= "<tr><th>Short Name*</th><td><input size=\"20\" name=\"blog_sname\" id='blog_sname' value=\"".stripslashes($_REQUEST['blog_sname'])."\" onkeypress=\"if (typeof(settings.saveblog) != 'undefined') settings.saveblog.disabled=false\"/>{$formerr_blog_sname}</td></tr>";
	$blogpost['post'] .= "<tr><th></th><td><small>The short name is the unique name for the blog, it can only be 20 chars max and can contain only letters, numbers and '_'. For example the short name for the 'My Notebook' would be my_notebook. Also when the short name can not be changed once the blog has been created</small></td></tr>";
	}else{
	$blogpost['post'] .= "<tr><th>Short Name*</th><td>{$blog['blog_sname']}</td></tr>";
	}
	$blogpost['post'] .= "<tr><th>Notebook Description</th><td><input size=\"50\" name=\"blog_desc\" value=\"".stripslashes($_REQUEST['blog_desc'])."\" onkeypress=\"if (typeof(settings.saveblog) != 'undefined') settings.saveblog.disabled=false\"/></td></tr>";
	
	$sql = "SELECT * FROM blog_types ORDER BY  blog_types.type_order ASC ";
	$result = runQuery($sql,'Get blog Id');
	$btypes = '';
	while($blogtype = db_get_next_row($result)){
		if($_REQUEST['blog_type']==$blogtype['type_id']) $select = " selected"; else $select = "";
		$btypes .= "<option value=\"{$blogtype['type_id']}\"{$select}>{$blogtype['type_name']}</option>";
	}

	$blogpost['post'] .= "<tr><th>Notebook Type</th><td><select name=\"blog_type\" onkeypress=\"if (typeof(settings.saveblog) != 'undefined') settings.saveblog.disabled=false\">{$btypes}</select></td></tr>";
	/*
	foreach($ct_config['styles'] as $style){
		if($_REQUEST['blog_style']==$style['sname']) $select = " selected"; else $select = "";
		$styles .= "<option value=\"{$style['sname']}\"{$select}>{$style['lname']}</option>";
	}
	$blogpost['post'] .= "<tr><th>Style</th><td><select name=\"blog_style\">{$styles}</select></td></tr>";
	*/
	if(!$blog['blog_id']){
	$blogpost['post'] .= "<tr><th></th><td align=\"right\"><input type=\"submit\" name=\"addblog\" value=\"Add New Notebook\" /></td></tr>";
	}else{
	$blogpost['post'] .= "<tr><th></th><td align=\"right\"><input type=\"submit\" name=\"saveblog\" value=\"Save Details\" disabled/></td></tr>";
	}
	$blogpost['post'] .= "</form>";
	$blogpost['post'] .= "</table></fieldset>";
	
	$body .= blog_style_post($blogpost);


	if($blog['blog_id']){

		$head .= '<script type="text/javascript" src="' . $ct_config['blog_path'] . 'inc/jquery/js/jquery-1.7.1.min.js"></script>' . "\n";

		if(isset($_REQUEST['msg']))
		{
			newMsg($_REQUEST['msg'], "message");
			unset($_REQUEST['msg']);
		}

		$head .= "<script language=\"JavaScript\" type=\"text/javascript\">

function NewZone() {

if (document.getElementById('blog_zone_sel').value == -2) {

	var new_section = prompt (\"New zone name:\",\"\");
	if(new_section.length){
		location.href = location.href + '&newzone=' + escape(new_section);
	}else{
		document.getElementById('blog_zone_sel').options[0].selected = true;
	}
}else{
	location.href = location.href + '&savezone=' + document.getElementById('blog_zone_sel').value;
}
}

</script>
";
		$blogpost = NULL;
		$blogpost['post']  = "<div class=\"postSubTitle\">Security</div>";
		$blogpost['post'] .= "<fieldset><form method=\"POST\" name=secur>";
		$blogpost['post'] .= "<strong>Access</strong> <br/>";
		foreach(array("0"=>"Public (Googleable)", "1"=>"Notebook Users only","-1"=>"Private (Just For You)") as $key=>$value){
			if($blog['blog_zone']==$key){$checked ="checked";}else{$checked="";}
			$blogpost['post'] .= "<input name=\"blog_zone\" value=\"{$key}\" type=\"radio\" {$checked} onclick=\"location.href = location.href + '&savezone=' + {$key}\">{$value}<br/>";
		}
		if($blog['blog_zone']>1){$checked ="checked";}else{$checked="";}
		$blogpost['post'] .= "<input name=\"blog_zone\" value=\"*\" type=\"radio\" {$checked} onclick=\"getElementById('blog_zone_sel').disabled=false;\">Custom (Select:";

		if($blog['blog_zone']<2){$checked ="disabled";}else{$checked="";}
		$blogpost['post'] .= "<select id=\"blog_zone_sel\" name=\"blog_zone_sel\" {$checked}  onchange=\"javascript:NewZone();\">";
		$blogpost['post'] .= "<option value=\"-1\">Select Zone</option>";

		$sql = "SELECT * FROM  blog_zone WHERE  (zone_res LIKE '{$_SESSION['user_name']}' OR zone_res LIKE '{$_SESSION['user_name']};%' OR zone_res LIKE '%;{$_SESSION['user_name']}' OR zone_res LIKE '%;{$_SESSION['user_name']};%' OR zone_res LIKE 'any') AND zone_type = 'user'";
		
		$result = runQuery($sql,'Get blog Id');
		while($zone = db_get_next_row($result)){
			if($blog['blog_zone']==$zone['zone_id']){$checked ="selected"; $zoneinfo = $zone;}else{$checked="";}

			$blogpost['post'] .= "<option value=\"{$zone['zone_id']}\" $checked>{$zone['zone_name']}</option>";
		}
		$blogpost['post'] .= "<option value=\"-2\">- New zone -</option>";
		$blogpost['post'] .= "</select>)";
		$blogpost['post'] .= "<br/>";
		$blogpost['post'] .= "</form></fieldset>";
		
		if($blog['blog_zone']>1){
		$blogpost['post'] .= "<table width=500>";
		$blogpost['post'] .= "<tr><th>User:</th></tr>";
		foreach(split(";",$zoneinfo['zone_res']) as $user)
		if($user == 'any'){
			$blogpost['post'] .= "<tr><td>Any logged in approved user</td></tr>";
		}else{
			$blogpost['post'] .= "<tr><td>".get_user_info($user,'name')." ({$user})</td><td><a href=\"settings.php?blog={$blog['blog_sname']}&zone={$blog['blog_zone']}&duser={$user}\">x</a></td></tr>";
		}
		$blogpost['post'] .= "</table>";
		$blogpost['post'] .= "<a href=\"#\" onclick=\"javascript:var blob = window.open('settings_adduser.php?zone={$blog['blog_zone']}','popup','scrollbars=auto;menubar=no,height=500,width=550,resizable=yes,toolbar=no,location=no,status=no');return false;\">Add User</a>";
		}
		

		$body .= blog_style_post($blogpost);
	}

include('page.php');

?>
