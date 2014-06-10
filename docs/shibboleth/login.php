<?php
include("../../lib/default_config.php");

$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
$body = "";

if(!empty($_SESSION['blog_id']) && file_exists("../lib/config/blog_{$_SESSION['blog_id']}.php"))
        include("../lib/config/blog_{$_SESSION['blog_id']}.php");

include("../style/{$ct_config['blog_style']}/blogstyle.php");

function escape($thing) {
    return htmlentities($thing);
}

function current_user() {
	if (empty($_SERVER["persistent-id"])) {
		return false;
	}
	$current_user = sha1($_SERVER["persistent-id"], $raw_output = true);
	$current_user = rtrim(strtr(base64_encode($current_user), '+/', '-_'), '=');
	return $current_user;
}

function shibEMail() {
	return $_SERVER['eppn'];
}

function shibDisplayIdentifier() {
	return $_SERVER['persistent-id'];
}

function shibSessionID() {
	return $_SERVER['Shib-Session-ID'];
}

function user_registration_form() {
	global $ct_config;
	$form = "<form name=\"user_registration\" method=\"post\" action=\"\">
  <input type=\"hidden\" name=\"key\" value=\"{$_SESSION['regist_user']['handle']}\"/>
  Please enter the following infomation to complete your registration.
  <table>
    <tr><th>Shibboleth ID:</th><td>{$_SESSION['regist_user']['user_openid']}</td></tr>
    <tr><th>Full Name:</th><td><input type=\"text\" name=\"user_fname\" value=\"";
	if (!empty($_POST['user_fname'])) {
		$form .= stripslashes($_POST['user_fname']);
	}
	elseif (!empty($_SESSION['regist_user']['user_fname'])) {	
        	$form .= $_SESSION['regist_user']['user_fname'];
	}
	$form .= "\"></td></tr>\n";
        $form .= "    <tr><th>Email Address:</th><td><input type=text name=user_email value=\"";
	if (!empty($_POST['user_email'])) {
		$form .= stripslashes($_POST['user_email']);
	}
	elseif (!empty($_SESSION['regist_user']['user_email'])) {
		$form .= $_SESSION['regist_user']['user_email'];
        }
        $form .= "\"></td></tr>\n";
	$form .= "    <tr><th></th><td style=\"text-align: right;\"><input type=\"submit\" name=\"submitinfo\" value=\"Continue\"/></td></tr>
  </table>
</form>";
	return $form;
}
 
function run() {
	global $ct_config;
	$userid = current_user();
	if (!$userid) {
		die($msg = "Shibboleth Failed! Shib-Identity-Provider:{$_SERVER['Shib-Identity-Provider']}");
	}
	else {
		if(!empty($_SESSION['labtrove']['turl'])) {
			$rurl = $_SESSION['labtrove']['turl'];
		}
		else {
			$rurl = $ct_config['blog_path'];
		}
		
		$username =  shibDisplayIdentifier();
		$esc_identity = escape($username);

		$success = sprintf('You have successfully verified ' .
				'<a href="%s">%s</a> as your identity.<br/>',
				$esc_identity, $esc_identity);

		$username_r = array("/([a-zA-Z]+:\/\/)/i" => "","/([a-zA-Z]+:\/\/)/i" => "",
                                        "/\/$/i" => "",
                                        "/\?/i" => "-",
                                        "/\&/i" => "-",
                                        "/\//i" => "-",
                                        "/\s/i" => "");
		$username_sanitised = preg_replace(array_keys($username_r),array_values($username_r), $username);

		$user['user_openid'] = $username;
		$user['handle'] = sha1(shibSessionID() . $username);
		$user['user_email'] = shibEMail();
		$user['user_type'] = (int)$ct_config['openid']['default_user_type'];		

		$sql = "SELECT * FROM  `users` WHERE  `user_name` LIKE '".addslashes($username_sanitised)."' LIMIT 1;";
		$tresult = runQuery($sql,'iGet User Info');
		if (db_get_number_of_rows($tresult)) {
			$user_sql = db_get_next_row($tresult);
			$user = array_merge($user,$user_sql);
			$sql = "UPDATE  `users` SET  `user_fname` =  '".addslashes($user['user_fname'])."', `user_email` =  '".addslashes($user['user_email'])."', `user_image` = '".addslashes($user['user_image'])."' WHERE  `users`.`user_id` ={$user['user_id']} LIMIT 1 ;";	
			if ($user_sql['user_enabled'] == 0) {
				displayError('Account Disabled');
			}
		}
		else {
			if (!empty($user['user_email']) && !empty($user['user_fname'])) { 
				$enabled = 1; 
			}
			else { 
				$enabled = -1; 
				$user['user_fname'] = ""; 
			}
			$user['user_name'] = $username_sanitised;
			$user['user_uid'] = md5($username);
			if (!isset($user['user_image'])) {
				$user['user_image'] = "";
			}
			$sql  = "INSERT INTO  `users` (`user_id` ,`user_name` , `user_openid`, `user_fname` ,`user_email`, `user_image`, `user_type` ,`user_enabled` ,`user_uid` ,`user_notes`)
				VALUES ( NULL ,  '".addslashes($user['user_name'])."', '".addslashes($user['user_openid'])."', '".addslashes($user['user_fname'])."',  '".addslashes($user['user_email'])."', '".addslashes($user['user_image'])."',  '{$user['user_type']}',  '$enabled',  '".addslashes($user['user_uid'])."',  '".date("Y-m-d H:i:s").": Account Added\n' ); ";
			newMsg("Registration Complete","message");
		}
		runQuery($sql,'update user info');
		if (!empty($user['user_email']) && !empty($user['user_fname'])) {
			if($_SESSION['labtrove']['openid']['remember']) {
				setcookie("user_name", $user['user_name'], time()+(3600*24*$ct_config['rememberme']['time']),'/');	
				setcookie("user_hash", md5($ct_config['rememberme']['salt'].$user['user_name'].$user['user_type'].$user['user_email'].$user['user_uid']), time()+(3600*24*$ct_config['rememberme']['time']),'/');
			}

			$_SESSION['user_name'] = $user['user_name'];
			$_SESSION['user_fname'] = $user['user_fname'];
			$_SESSION['user_email'] = $user['user_email'];
			$_SESSION['user_uid'] = $user['user_uid'];
			$_SESSION['user_admin'] = $user['user_type'];
			$_SESSION['sessionID'] = shibSessionID();

			header("Location: $rurl");  // Redirect browser 
			if (isset($_SESSION['labtrove']['turl'])) {
				unset($_SESSION['labtrove']['turl']);
			}
			exit();

		}
		$_SESSION['regist_user'] = $user;

		$blogpost = NULL;	
		$blogpost['title'] = "User Registration";
		$blogpost['post'] = user_registration_form();

		global $body;
		$body .= blog_style_post($blogpost);
	}
	if (isset($msg)) {
		$body .= $msg;	
	}
}


if (!empty($_REQUEST['submitinfo'])) {
	$blogpost['title'] = "User Registration";				
	if($_SESSION['regist_user']['handle'] == $_REQUEST['key']){
		$fail = FALSE;
		if (!$_REQUEST['user_fname']) {
			$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Please Enter a full name.</div></div>";
			$fail = TRUE;
		}
		else {
			$_SESSION['regist_user']['user_fname'] = stripslashes($_REQUEST['user_fname']);
		}
		if (!validate_email($_REQUEST['user_email'])) {
			$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Please Enter a valid email address</div></div>";
			$fail = TRUE;
		}
		else {
			$_SESSION['regist_user']['user_email'] = stripslashes($_REQUEST['user_email']);
		}
		if ($fail) {
			$blogpost['post'] .= user_registration_form();
		}
		else {
			$sql = "UPDATE  `users` SET  `user_fname` =  '".addslashes($_SESSION['regist_user']['user_fname'])."', `user_email` =  '".addslashes($_SESSION['regist_user']['user_email'])."', `user_enabled` = 1 WHERE  `users`.`user_name` = '{$_SESSION['regist_user']['user_name']}' LIMIT 1;";	
			runQuery($sql,'update user info');
			$_SESSION['user_name'] = $_SESSION['regist_user']['user_name'];
			$_SESSION['user_fname'] = $_SESSION['regist_user']['user_fname'];
			$_SESSION['user_email'] = $_SESSION['regist_user']['user_email'];
			$_SESSION['user_uid'] = $_SESSION['regist_user']['user_uid'];
			$_SESSION['user_admin'] = $_SESSION['regist_user']['user_type'];
			if(!empty($_SESSION['turl'])) {
				header("Location: {$_SESSION['turl']}"); 
			} 
			else {
				header("Location: /");
			}
			exit();
		}
	}
	$body .= blog_style_post($blogpost);
}
else{
	run();
}

include '../page.php';
?>
