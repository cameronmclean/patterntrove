<?php

	$ct_config['hooks']['export_post']['edd'] = array("function"=>"elndd_export", "desc"=>"ELN DD");
	
	function elndd_export($sql){
		
		global $ct_config;
				
		$timemin = time();
		$timemax = 0;

		$xpost = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
		<elnDataDescription unit="package" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="elnDataDescription.xsd"/>');
		

		$tresult = runQuery($sql,'Fetch Page Groups');
		$noofposts = db_get_number_of_rows($tresult);

		while($post = db_get_next_row($tresult)){

		if($post['bit_edit']<0) continue;

		

		if(!$post['bit_cache']) $post['bit_cache'] = makepostcache($post);

		$xpost->addChild('title', $post['bit_title']);
		
		
		$metadata = $xpost->addChild('keywords');
		if($post['bit_meta']){
			$metaxml = readxml($post['bit_meta']);
			if(is_array($metaxml['METADATA']['META'])){
				foreach($metaxml['METADATA']['META'] as $met=>$key)
					$metadata->addChild('keyword',strtolower($met));
			}
		}
		$urllink = render_blog_link($post['bit_id'], true );
		$ids = $xpost->addChild('identifiers');
			$ids->addChild('primaryLocalIdentifier',$post['bit_id']);
			$ids->addChild('accessIdentifier',$urllink);
		
		$user = $xpost->addChild('contact');
		$user->addChild('eMail',get_user_info($post['bit_user'],"email"));
		
		if(isset($ct_config['content_licence']) && strlen($ct_config['content_licence'])){
			$xpost->addChild('licensingBasis',$ct_config['content_licence']);
		}else{
			$xpost->addChild('licensingBasis','not set');
		}
		
		
		$user = $xpost->addChild('contributors');
		$usera = $user->addChild('contributor');
		$usera->addChild('role','Author');
		$usera->addChild('name',get_user_info($post['bit_user'],"name"));
		
		$content = $xpost->addChild('content');
			$content->addChild('description',$post['bit_title']);
			$content->addChild('mimeType','text/plain');	

		$xpost->addChild('source',@file_get_contents("../install/version"));

		$dates = $xpost->addChild('date');
			$dates->addChild('creationDate', date("c",$post['datetime']));

		header ("content-type: text/xml");

		echo "";
		echo $xpost->asXML();
		

		exit();

		}

		/*
		$posts->addAttribute('to', date("c",$timemax));
		$posts->addAttribute('from', date("c",$timemin));*/

	

		class SimpleXMLExtend extends SimpleXMLElement
		{
		  public function addCData($nodename,$cdata_text)
		  {
		    $node = $this->addChild($nodename); //Added a nodename to create inside the function
		    $node = dom_import_simplexml($node);
		    $no = $node->ownerDocument;
		    $node->appendChild($no->createCDATASection($cdata_text));
		  }
		} 


		
	
		return true;
	}

?>