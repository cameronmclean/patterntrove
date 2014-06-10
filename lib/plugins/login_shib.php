<?php
ini_set("soap.wsdl_cache_enabled", "0");
$ct_config['protected_paths'][] = "shibboleth";

if (!empty($_REQUEST['logout'])) {
	foreach(array("user_name","user_fname","user_admin","user_email","user_uid","sessionID") as $v) {
		unset($_SESSION[$v]);
	}
	setcookie("user_name", 0, 0, '/');
	setcookie("user_hash", '', 0, '/');
	foreach($_COOKIE as $k => $v) {
		if (substr($k, 0, 5)!="_shib") {
 			continue;
		}
		setcookie($k, '', 0, '/');
	}
	header('Location: '.$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH']);
	newMsg("Logout successful. As you are using Shibboleth, to complete the logout process you must close your browser.  Otherwise, you or others may be able to log back in without a password.", "message");
	exit();
}

if (isset($_REQUEST['turl'])) {
	$_SESSION['labtrove']['turl'] = $_REQUEST['turl'];
}

function chkperm($perm) {
	return 1;
}


function check_remembered() {
	global $ct_config;
	if(!empty($_COOKIE['user_name'])){
		$user = get_user_info($_COOKIE['user_name']);
		if($user != 'Error' && md5($ct_config['rememberme']['salt'].$user['user'].$user['access'].$user['email'].$user['uid'])==$_COOKIE['user_hash']) {
			$_SESSION['user_name'] = $user['user'];
                	$_SESSION['user_fname'] = $user['name'];
                	$_SESSION['user_admin'] = $user['access'];
                	$_SESSION['user_email'] = $user['email'];
                	$_SESSION['user_uid'] = $user['uid'];
		}
		else {
			return 'Error';
		}
	}
}

function get_user_info($usern, $field = 0) {
	global $ct_config;
	if ($_SESSION['user_info'][$usern]['set'] < (time()-3600)) {
		$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$usern}'";
		$tresult = runQuery($sql,'iGet User Info');
		if (mysql_num_rows($tresult)){
			$user = mysql_fetch_array($tresult);
	        	$_SESSION['user_info'][$usern] = array("user" => $user['user_name'], "access" => $user['user_type'], "name" => $user['user_fname'], "email" => $user['user_email'], "uid" => $user['user_uid'], "image" => $user['user_image'], "result" => 1, "set" => time());
		}
		else {
			return 'Error';
		}
	}
	if ($field) {
		return $_SESSION['user_info'][$usern][$field];
	}
	else {
		return $_SESSION['user_info'][$usern];
	}
}

function renlogin_blog(){
	global $ct_config;
	if (!empty($_SESSION['user_name'])) {
		$uri = (isset($_REQUEST['uri'])) ? $_REQUEST['uri'] : '';
        	return '<span class="with_user">  Current user: <a href="'.render_link('',array('user' => $_SESSION['user_name'])).'">'.$_SESSION['user_fname'].'</a> | <a href="'.$ct_config['blog_path'].$uri.'?logout=1">Log Out</a> </span>';
	}
	return '<img src="/inc/user.gif" height=11> <a href="' . $ct_config['blog_path'] . 'shibboleth/login.php">Login</a>';
}


function do_login() {
 	return 0;
}

function login_with_uid($uid) {
	global $ct_config;
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_uid` LIKE  '{$uid}'";
	$tresult = runQuery($sql,'iGet User Info');
	if(mysql_num_rows($tresult)) {
	$user = mysql_fetch_array($tresult);
  		$_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_fname'] = $user['user_fname'];
                $_SESSION['user_admin'] = $user['user_type'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['user_uid'] = $user['user_uid'];
		return true;
	}
	else{
		return false;
	}
}

function user_info_display() {
	return user_info_display_by_user_name($_SESSION['user_name']);
}

function user_info_display_by_user_name($user_name) {
	global $ct_config;
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$user_name}'";
	$tresult = runQuery($sql, 'iGet User Info');
	$user = mysql_fetch_array($tresult);
	$blogpost['title'] = "User Information";
	$blogpost['post'] = "<table>";
	$blogpost['post'] .= "<tr><th>Username:</th><td>{$user_name}</td></tr>";
	$blogpost['post'] .= "<tr><th>Shibboleth ID:</th><td>{$user['user_openid']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Full Name:</th><td>{$user['user_fname']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Email:</th><td>{$user['user_email']}</td></tr>";
	$blogpost['post'] .= "<tr><th>User Type:</th><td>{$ct_config['perm_access'][$user['user_type']]}</td></tr>";
	$blogpost['post'] .= "<tr><th>Master UID:</th><td>{$user['user_uid']}</td></tr>";
	$blogpost['post'] .= "</table>";
	return blog_style_post($blogpost);
}

function getUsers() {
	global $ct_config;
	$sql = "SELECT user_name, user_fname, user_type FROM `{$ct_config['blog_db']}`.`users`";
	$tresult = runQuery($sql,'iGet User Info');
	// $blogpost['title'] = "Users";
	$blogpost['post'] = "<table>";
	$blogpost['post'] .= "<tr><th>Name</th><th>User type</th></tr>\n";
	while($row = mysql_fetch_array($tresult)){
		$user_type = "user";
		if($row['user_type'] == 2) $user_type = "user+"; // not sure if '2' is used
		if($row['user_type'] == 3) $user_type = "admin";
		$blogpost['post'] .= "<tr><td><a href='user/{$row['user_name']}'>{$row['user_fname']}</a></td><td>{$user_type}</td></tr>\n";
        }

	$blogpost['post'] .= "</table>";
	return blog_style_post($blogpost);
}

?>
