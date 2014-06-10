<?php
include("../../lib/default_config.php");

if (!isset($_SESSION['user_admin'])) {
        header("Location: {$ct_config['blog_path']}?msg=You must be an authenticated admin user to administer the Claddier whitelist!&turl=".urlencode($_SERVER['PHP_SELF']));
        exit();
}

$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
$body = "";
$head .= '<script type="text/javascript" src="' . $ct_config['blog_path'] . 'inc/jquery/js/jquery-1.7.1.min.js"></script>' . "\n";
$head .= "<style type=\"text/css\"><!--
table.borders { border: 1px solid black; margin: 0.5em 0 0 0; }
.borders th { border: 1px solid black; background-color: #f5f5f5; text-align: center; padding: 0.5em;}
.borders td { border: 1px solid black; padding: 0.5em;}
--></style>";

if(!empty($_SESSION['blog_id']) && file_exists("../lib/config/blog_{$_SESSION['blog_id']}.php"))
        include("../lib/config/blog_{$_SESSION['blog_id']}.php");

include("../style/{$ct_config['blog_style']}/blogstyle.php");

function manage_claddier_whitelist_form() {
        global $ct_config;
	$repos = runQuery("SELECT * FROM claddier_whitelist", 'get claddier whitelist');
	$fields = array("repository_name" => "Name", "repository_url" => "URL", "repository_ip_address" => "IP Address");
        $form = "<form name=\"claddier_whitelist\" method=\"post\" action=\"\">";
	if (db_get_number_of_rows($repos) > 0) {
		$form .= "  <table class=\"borders\">
    <tr><th>Repository Name</th><th>URL</th><th>IP Address</th><th>Registered</th><th>Delete</th></tr>";
		while ($repo = db_get_next_row($repos)) {	
			$form .= "    <tr><td>{$repo['repository_name']}</td><td>{$repo['repository_url']}</td><td>{$repo['repository_ip_address']}</td><td>{$repo['registered_at']}</td><td class=\"noborder\"><input type=\"submit\" name=\"delete_{$repo['id']}\" value=\"Delete\" /></td></tr>";
		}
  		$form .= "  </table>";
	}
	else {
		$form .= "<p style=\"color: gray;\"><i>There are currently no repositories in the Claddier whitelist.</i></p>";
	}
	$form .= "  <br/>
  <h2>Add New Repository</h3>
  <p>If you wish to register a repository or repositories that are spread across a subnet of IP addresses, you can use a regular expression to register the whole subnet.  (E.g. Use <b>152.78.*</b> to register all IP address in the IP range 152.78.0.0 - 152.78.255.255).</p>
  <table>";
	foreach ($fields as $name => $label) {
		$form .= "    <tr><th style=\"text-align: right;\">$label:</th><td><input type=\"text\" name=\"$name\" value=\"";
	        if (!empty($_POST[$name])) {
        	        $form .= stripslashes($_POST[$name]);
        	}
        	$form .= "\">";
		if ($label == "URL") {
			$form .= "&nbsp;<font style=\"color: gray; font-weight: lighter; font-style: italic;\">For multiple repositories this may be left blank.</font>";
		}
		$form .= "</td></tr>\n";
	}
  	$form .= "  </table>
  <input type=\"submit\" name=\"add\" value=\"Add\" />
  <br/>
  <br/>
  <h3>Test IP Address</h3>
  <input type=\"text\" name=\"ip_address\" id=\"ip_address\"/>
  <input type=\"button\" value=\"Test\" name=\"test_ip\" onClick=\"ping_ip_address(document.getElementById('ip_address').value);\" />
  <div id=\"ping_result\">&nbsp;</div>
  <script type=\"text/javascript\"> 
    function ping_ip_address(ip_address){
      if (ip_address.replace(/[^0-9a-fA-F:\.]/g, '').length == ip_address.length) { 
        $('#ping_result').html(\"Pinging...\");
        $.get(
          \"claddier/ping.php?ip_address=\"+ip_address,
          \"{}\",
	  function(data) { $('#ping_result').html(data); },
	  \"html\"
        );
      }
      else {
        $('#ping_result').html(\"ERROR: This is not a valid IP address.\");
      }
    }
  </script>
</form>";
        return $form;
}

$blogpost['title'] = "Manage Claddier Whitelist";
if (isset($_POST['add'])) {
	$validate_ip = $_POST['repository_ip_address'];
	if (!strpos($validate_ip, ":") && (substr_count($validate_ip, ".") < 3 || !is_numeric(substr($validate_ip, -1)))) {
		$validate_ip = str_replace('*', '', $validate_ip);
		if (!is_numeric(substr($validate_ip, -1))) {
			$validate_ip .= "123";
		}
		while (substr_count($validate_ip, ".") < 3) {
			$validate_ip .= ".123";
		}
	}	
	if (empty($_POST['repository_name']) || empty($_POST['repository_ip_address'])) {
		$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Cannot add repository. One or more fields are empty.</div></div>";			
	}
	elseif (!filter_var($_POST['repository_url'], FILTER_VALIDATE_URL) && !empty($_POST['repository_url'])) {
		$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Cannot add repository. URL for repository is invalid.</div></div>";
	}
	elseif (!filter_var($validate_ip, FILTER_VALIDATE_IP)) {
                $body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Cannot add repository. IP address for repository is invalid.</div></div>";	
        }
	else {
		if (db_get_number_of_rows(runQuery("SELECT id FROM claddier_whitelist WHERE repository_name = '" . addslashes($_POST['repository_name']) . "'", 'get name matching claddier whitelist repos')) > 0) {
			$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Cannot add repository. Repository name has already been registered.</div></div>";
		}
		elseif (db_get_number_of_rows(runQuery("SELECT id FROM claddier_whitelist WHERE repository_url = '" . addslashes($_POST['repository_url']) . "'", 'get url matching claddier whitelist repos')) > 0) {
			$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Cannot add repository. URL for repository has already been registered.</div></div>";
		}
		else {
			runQuery("INSERT INTO `claddier_whitelist` (`repository_name`, `repository_url`, `repository_ip_address`, `registered_at`) VALUES('" . addslashes($_POST['repository_name']) . "', '" . addslashes($_POST['repository_url']) . "', '" . addslashes($_POST['repository_ip_address']) . "', NOW())", 'add repository to claddier_whitelist');
			unset($_POST);
			newMsg("Repository added to whitelist.", "message");
		}
			
	}
}
else {
	foreach ($_POST as $name => $value) {
		$name_bits = explode("_", $name);
		if ($name_bits[0] == "delete" && is_numeric($name_bits[1])) {
			runQuery("DELETE FROM `claddier_whitelist` WHERE id = '" . addslashes($name_bits[1]) . "'", 'delete claddier whitelist repo');
			newMsg("Repository deleted from whitelist.", "message");
		}
	}
}
if ($_SESSION['user_admin'] >= 3) {
	$blogpost['post'] = manage_claddier_whitelist_form();
	$body .= blog_style_post($blogpost);
}
else{
	$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">You must be an authenticated admin user to administer the Claddier whitelist!</div></div>";
}
include('../page.php');
