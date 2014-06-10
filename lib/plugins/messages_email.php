<?php
/*
	default config for messages_email
	Add the following to your config.php
	$ct_config['messages']['enable'] = true;
	$ct_config['messages']['mode'] = "sync"; //Send the email at during script running, change to 'async' if you want to send them in background.
	$ct_config['messages']['smpt_server'] = "smtp.example.com"; //the name of the server you are connecting to
	$ct_config['messages']['smpt_port'] = 25; //the port number to use (typically 25, 465 or 587)
	$ct_config['messages']['smpt_user'] = null; // your username needed to log into the server
	$ct_config['messages']['smpt_pass'] = null; //the password needed to log into the server
	$ct_config['messages']['smpt_security'] = null; //tls, ssl or none
	$ct_config['messages']['from'] = "LabTrove <bob@example.com>" ; // messages will be sent from.


*/




if(isset($ct_config['messages']['enable']) && $ct_config['messages']['enable']){
	//$config['blog_sub'][2] = "A Daily Digest";
	//$config['blog_sub'][3] = "A Hourly Digest";
	$config['blog_sub'][4] = "1 Email/Post";
	$config['blog_sub'][5] = "1 Email/Post (+Comments)";

	$config['blog_sub_sort'][1] = "Sort By Date";
	$config['blog_sub_sort'][2] = "Sort By Blog";

	$ct_config['hooks']['on_post_new'][] = array("function"=>"messages_post_new", "params"=>array("bit_id","bit_blog"));
	
}

function messages_post_new($bit_id, $blog_id){
global $ct_config;

$sql = "SELECT * FROM  blog_users INNER JOIN  blog_sub ON u_name = sub_username WHERE sub_blog = $blog_id AND u_emailsub in (3,4)";

$result = runQuery($sql,'Sub user for blog');

if(db_get_number_of_rows($result)){

	$sql = "SELECT *, ".db_timestamp("bit_datestamp")." as datetime FROM  blog_bits INNER JOIN  blog_blogs ON bit_blog = blog_id WHERE  bit_id = $bit_id AND bit_edit = 0";

	$tresult = runQuery($sql,'Sub user for blog');
	$row = db_get_next_row($tresult);
	$subject = strip_tags("[{$ct_config['blog_title']}] New Post - {$row['bit_title']}");

	$content_text = "New Post: {$row['bit_title']}\n";
	$content_text .= "by ".get_user_info($row['bit_user'],'name')." as part of the {$row['blog_name']} blog.\n";
	$content_text .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])." \n";
	$content_text .= "\n ". render_blog_link($row['bit_id'],1)." \n";
	$content_text .= "\n\n LabTrove: {$ct_config['blog_title']} \n\n\n To adjust your email settings please edit your user setting in the blog.";


	$content_html = "<h2>New Post: <a href=\"".render_blog_link($row['bit_id'],1)."\">{$row['bit_title']}</a></h2>\n";
	$content_html .= "by ".get_user_info($row['bit_user'],'name')." as part of the <a href=\"".render_link($row['blog_sname'])."/\">{$row['blog_name']}</a> blog.<br />\n";
	$content_html .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])."<br /> \n";
	$content_html .= "\n ".render_blog_link($row['bit_id'],1)."<br /> \n";
	$content_html .= "<br />\n LabTrove: {$ct_config['blog_title']} <br /><br /><br />\n\n\n To adjust your email settings please edit your user setting in the blog.";

	$key = $ct_config['blog_db']."_post_".$row['bit_id'];

while($row = db_get_next_row($result)){

	messages_message_new(addslashes($subject), addslashes($content_text),  addslashes($content_html),  $row['u_name'],  1, $row['u_proflocate'],  $key, 1);

}

}

}


function messages_message_new($subject, $body,  $html,  $to,  $email,  $prof,  $key, $pri = 1){
global $ct_config;
$sql = "INSERT INTO  {$ct_config['blog_msgdb']}.messages ( mess_id ,mess_subject ,mess_body ,mess_html ,mess_to ,mess_email ,mess_proflocate ,mess_key ,mess_pri ,mess_datetime ) VALUES ( NULL ,  '$subject',  '$body',  '$html',  '$to',  '1',  '1',  '$key',  '1', NOW( ) );";

 runQuery($sql,'insert uri');

if($ct_config['messages']['mode']=="sync")
 messages_message_send(db_insert_id());
}



function messages_message_send($id = 0){
	global $ct_config;
	
	if($id)
	$sql = "SELECT * FROM  {$ct_config['blog_msgdb']}.messages WHERE  mess_id = '{$id}'";
	else
	$sql = "SELECT * FROM  {$ct_config['blog_msgdb']}.messages WHERE  mess_email =1";

	$tresult = runQuery($sql,'Sub user for blog');
	while($row = db_get_next_row($tresult)){

		 $to = get_user_info($row['mess_to'],"name");
			if( $to == 'Error'){
					$sql = "UPDATE  {$ct_config['blog_msgdb']}.messages SET  mess_email =  '3' WHERE  messages.mess_id = {$row['mess_id']} " . db_limit_1();					runQuery($sql,'Sub user for blog');
					//echo "error";
				}else{
					$to .= " <".get_user_info($row['mess_to'],"email").">";
						messages_message_mailto($to, $row['mess_subject'], $row['mess_html']);
				
				$sql = "UPDATE  {$ct_config['blog_msgdb']}.messages SET  mess_email =  '2' WHERE  messages.mess_id = {$row['mess_id']} " . db_limit_1();
						runQuery($sql,'Sub user for blog');
				}
		}
		
}


function messages_message_mailto($to, $subject, $body){
	global $ct_config;
	include("{$ct_config['pwd']}/lib/plugins/messages_email/km_smtp_class.php");
	
	$mail = new KM_Mailer($ct_config['messages']['smpt_server'], $ct_config['messages']['smpt_port'],$ct_config['messages']['smpt_user'], $ct_config['messages']['smpt_pass'],$ct_config['messages']['smpt_security']);
	
	
 	$mail->send($ct_config['messages']['from'], $to, $subject, $body);
}
	

?>
