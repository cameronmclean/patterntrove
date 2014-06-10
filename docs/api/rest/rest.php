<?php

$nosession = true;
$ct_config['skip_magic_quotes'] = true;

include("../../../lib/default_config.php");


$pathinfo = $_SERVER['LABTROVE_REQUEST_PATH'];

if($pathinfo){
	$pathinfo = explode("/",$pathinfo);
	array_shift($pathinfo); array_shift($pathinfo);
	 $request['action'] = strtolower(array_shift($pathinfo));
	if(((string)$pathinfo[0]) === ((string)(int)$pathinfo[0]))
		$request['bit_id'] = array_shift($pathinfo);
	while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));
}

$actions = array("addpost","adddata", "editpost", "appendpost");

if(!in_array($request['action'], $actions)){
	proc_error(404, "{$request['action']}: Action Not Found");
}

login_with_uid($request['uid']);
if(!$_SESSION['user_name'] && ($_SESSION['user_admin'] > 0)){
		proc_error(401, "Unauthorized");
}

if(($req = @simplexml_load_string(utf8_encode($_REQUEST['request']))) === false ){
		proc_error(500, "Received XML did not parse");
}

switch($request['action']){

	case "adddata":
	
		if(isset($req->data)){
			foreach($req->data->dataitem as $bla){
				$data_attr = $bla->attributes();
				$metapart = NULL;
				if( isset( $data_attr['filename'] ) ){
					$metapart['name']= (string)$data_attr['filename'];
				}
				
				if( isset( $data_attr['main'] ) ){
					$main=(string)$data_attr['ext'];
				}
				switch($data_attr['type']){
					case "file":
						if(!isset($_FILES[(string)$bla]['error']) || $_FILES[(string)$bla]['error'] != 0)
								proc_error(500, "File didn't upload");
						$metapart['type'] = "local";
						$metapart['id'] = add_data_by_filename((string)$data_attr['ext'], $_FILES[(string)$bla]['tmp_name']);
					break;
					case "local":
						$metapart['type'] = "local";
						$metapart['id'] = (int)$bla;
					break;
					case "url":
						$metapart['type'] = "url";
						$metapart['url'] = (string)$bla;
					break;
					case "inline":
						$metapart['id'] = add_data((string)$data_attr['ext'], base64_decode((string)$bla));
						$metapart['type'] = "local";
					break;
				}
				$data[(string)$data_attr['ext']] = $metapart;

			}
			
			$id = add_data_meta($req->title, $data, $main);
			
			header("HTTP/1.0 200 OK");
			$error = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><result/>");
			$error->addChild('success', 'true');
			$error->addChild('status_code', 200);
			$error->addChild('data_id', $id );
			header("content-type: text/xml");
			echo "";
			echo $error->asXML();
			exit();

			
		}else{
			proc_error(500, "Received XML incomplete");
		}

	break;
	
	case "addpost":
	case "editpost":
	case "appendpost":
		
			if($req->blog){
				$sql = "SELECT * FROM  blog_blogs WHERE blog_id = '".(int)$req->blog."'";
			}else if($req->blog_sname){
				$sql = "SELECT * FROM  blog_blogs WHERE blog_sname = '".addslashes($req->blog_sname)."'";
			}else{
				proc_error(500, "blog or blog_sname not set");
			}

			$result = runQuery($sql,'Blogs');
			if(($blog = db_get_next_row($result))===false){
				proc_error(500, "blog not found");
			}

				$blog_id = $blog['blog_id'];

		if($request['action']=="editpost" || $request['action']=="appendpost"){

			$sql = "SELECT  * FROM  blog_bits WHERE  bit_id = '".(int)$req->id."' AND  bit_edit = 0";
			$result = runQuery($sql,'Blogs');
			if(($post = db_get_next_row($result))===false){
				proc_error(500, "blog post not found");
			}
			if(($_SESSION['user_admin'] < 2) && ($post['bit_user']!=$_SESSION['user_name'])){
				proc_error(401, "Unauthorized to edit someone else's post");
			}	

			if(!$req->edit_reason) proc_error(500, "no edit reason");
		
		}

		$user_can_edit = 0;
		if($_SESSION['user_admin']==0){
			$user_can_post = 0;
		}else{
			$user_can_post = 1;
		}
		if((!checkzone($blog['blog_zone'],0,$blog['blog_id']) || !checkzone($ct_config['blog_zone'])) && ($_SESSION['user_admin'] < 2)){
			proc_error(401, "Unauthorized For that blog");
		}
	
		if(($_SESSION['user_admin'] < 2) && ($req->author->username!=$_SESSION['user_name'])){
			proc_error(401, "Unauthorized to post as someone else");
		}
		if("Error" == get_user_info("{$req->author->username}")){
			proc_error(500, "Unknown author");
		}	
		//Not be obligatory for appendpost
		if(($request['action']) != "appendpost")
		{
			if(!$req->title) proc_error(500, "no title set");
			if(!$req->section) proc_error(500, "no section set");
			if(!$req->author->username) proc_error(500, "no author set");
			if(!$req->content) proc_error(500, "no content set");
		}
		$datastamp = strtotime($req->datestamp);
		if(!$datastamp) $datastamp = time();

		if(($request['action']) != "appendpost")
		{		
			if(isset($req->metadata)){
				$oldkey = "";
				foreach($req->metadata->children() as $key=>$value){					
					if($key != $oldkey)
					{
						$metadata['METADATA']['META'][$key] = (String) $value;						
					}
					else $metadata['METADATA']['META'][$key]  .=";".(String) $value;
					$oldkey = $key; 
				}
			}
			if(isset($req->attached_data)){
				foreach($req->attached_data->data as $bla){
					$data_attr = $bla->attributes();
					switch($data_attr['type']){
						case "local":
							if(isset($metadata['METADATA']['DATA'])){
								$metadata['METADATA']['DATA'] .= ",{$bla}";
							}else{
								$metadata['METADATA']['DATA'] = "{$bla}";
							}

							setposttodata((int)$bla,(int)$req->id);
						break;
					}
				}
			}			
		}//if		
		if(isset($metadata)) $meta = writexml($metadata);
		if($request['action']=="appendpost")		{
			
			append_blog((int)$req->id, addslashes($req->edit_reason), addslashes($req->title), addslashes($req->content), $req->metadata, $req->attached_data, addslashes($req->section));
			$id = (int)$req->id;
		}
		
		elseif($request['action']=="editpost"){
			edit_blog((int)$req->id, addslashes($req->edit_reason), addslashes($req->title), addslashes($req->content),  $meta, addslashes($req->section));
			$id = (int)$req->id;
		}else{
				$id = add_blog($blog_id, addslashes($req->title), addslashes($req->content),  $meta, addslashes($req->section), db_from_timestamp($datastamp), "{$req->author->username}");				
		}
		header("HTTP/1.0 200 OK");
		$error = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><result/>");
		$error->addChild('success', 'true');
		$error->addChild('status_code', 200);
		$error->addChild('post_id', $id );
		$error->addChild('post_info', render_blog_link($id,true, ".xml") );
		header("content-type: text/xml");
		echo "";
		echo $error->asXML();
		exit();
	break;
	}


function proc_error($code, $reason){

	
	header("HTTP/1.0 $code $reason");
	$error = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><result/>");
	$error->addChild('success', 'false');
	$error->addChild('status_code', $code);
	$error->addChild('reason', $reason);

	header("content-type: text/xml");
	echo "";
	echo $error->asXML();
	exit();
}

?>
